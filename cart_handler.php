<?php
session_start();
include "config.php";

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Tambah item ke cart
            $menu_id = intval($_POST['menu_id']);
            $quantity = intval($_POST['quantity'] ?? 1);
            $level = isset($_POST['level']) ? intval($_POST['level']) : null;

            // Validasi menu exists
            $checkMenu = mysqli_prepare($conn, "SELECT stock FROM menu WHERE id = ?");
            mysqli_stmt_bind_param($checkMenu, "i", $menu_id);
            mysqli_stmt_execute($checkMenu);
            $menuResult = mysqli_stmt_get_result($checkMenu);
            $menu = mysqli_fetch_assoc($menuResult);

            if (!$menu) {
                echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
                exit;
            }

            if ($menu['stock'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
                exit;
            }

            // Cek apakah item sudah ada di cart
            $checkCart = mysqli_prepare($conn, "
                SELECT id, quantity FROM cart 
                WHERE user_id = ? AND menu_id = ? AND (level = ? OR (level IS NULL AND ? IS NULL))
            ");
            mysqli_stmt_bind_param($checkCart, "iiii", $user_id, $menu_id, $level, $level);
            mysqli_stmt_execute($checkCart);
            $cartResult = mysqli_stmt_get_result($checkCart);
            $existingCart = mysqli_fetch_assoc($cartResult);

            if ($existingCart) {
                // Update quantity
                $newQty = $existingCart['quantity'] + $quantity;
                
                if ($newQty > $menu['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Jumlah melebihi stok tersedia']);
                    exit;
                }

                if ($newQty > 10) {
                    echo json_encode(['success' => false, 'message' => 'Maksimal 10 item per menu']);
                    exit;
                }

                $updateCart = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ?");
                mysqli_stmt_bind_param($updateCart, "ii", $newQty, $existingCart['id']);
                mysqli_stmt_execute($updateCart);
            } else {
                // Insert new
                if ($level !== null) {
                    $insertCart = mysqli_prepare($conn, "
                        INSERT INTO cart (user_id, menu_id, quantity, level) 
                        VALUES (?, ?, ?, ?)
                    ");
                    mysqli_stmt_bind_param($insertCart, "iiii", $user_id, $menu_id, $quantity, $level);
                } else {
                    $insertCart = mysqli_prepare($conn, "
                        INSERT INTO cart (user_id, menu_id, quantity) 
                        VALUES (?, ?, ?)
                    ");
                    mysqli_stmt_bind_param($insertCart, "iii", $user_id, $menu_id, $quantity);
                }
                mysqli_stmt_execute($insertCart);
            }

            echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke keranjang']);
            break;

        case 'update':
            // Update quantity
            $cart_id = intval($_POST['cart_id']);
            $quantity = intval($_POST['quantity']);

            if ($quantity < 1 || $quantity > 10) {
                echo json_encode(['success' => false, 'message' => 'Jumlah tidak valid']);
                exit;
            }

            // Get menu price for subtotal
            $getPrice = mysqli_prepare($conn, "
                SELECT m.harga 
                FROM cart c 
                JOIN menu m ON c.menu_id = m.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            mysqli_stmt_bind_param($getPrice, "ii", $cart_id, $user_id);
            mysqli_stmt_execute($getPrice);
            $priceResult = mysqli_stmt_get_result($getPrice);
            $priceData = mysqli_fetch_assoc($priceResult);

            if (!$priceData) {
                echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
                exit;
            }

            $updateCart = mysqli_prepare($conn, "
                UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?
            ");
            mysqli_stmt_bind_param($updateCart, "iii", $quantity, $cart_id, $user_id);
            mysqli_stmt_execute($updateCart);

            $subtotal = $priceData['harga'] * $quantity;

            echo json_encode([
                'success' => true,
                'subtotal' => $subtotal
            ]);
            break;

        case 'remove':
            // Hapus item dari cart
            $cart_id = intval($_POST['cart_id']);

            $deleteCart = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($deleteCart, "ii", $cart_id, $user_id);
            mysqli_stmt_execute($deleteCart);

            // Hitung sisa cart
            $countCart = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($countCart, "i", $user_id);
            mysqli_stmt_execute($countCart);
            $countResult = mysqli_stmt_get_result($countCart);
            $countData = mysqli_fetch_assoc($countResult);

            echo json_encode([
                'success' => true,
                'cart_count' => $countData['count']
            ]);
            break;

        case 'clear':
            // Kosongkan cart
            $clearCart = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($clearCart, "i", $user_id);
            mysqli_stmt_execute($clearCart);

            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in cart_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>