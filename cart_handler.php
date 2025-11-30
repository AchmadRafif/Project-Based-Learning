<?php
session_start();
include "config.php";

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu',
        'redirect' => 'mobileregister.html'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ============================================
// ADD TO CART
// ============================================
if ($action === 'add') {
    $menu_id = mysqli_real_escape_string($conn, $_POST['menu_id']);
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $level = isset($_POST['level']) ? (int)$_POST['level'] : null; // Level untuk Seblak
    
    // Validasi menu exists dan stock cukup
    $checkMenu = mysqli_query($conn, "SELECT * FROM menu WHERE id = '$menu_id'");
    
    if (mysqli_num_rows($checkMenu) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Menu tidak ditemukan'
        ]);
        exit;
    }
    
    $menu = mysqli_fetch_assoc($checkMenu);
    
    // Maksimal quantity adalah 10 atau stock (mana yang lebih kecil)
    $maxQuantity = min(10, $menu['stock']);
    
    if ($quantity > $maxQuantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Maksimal ' . $maxQuantity . ' item per menu'
        ]);
        exit;
    }
    
    if ($menu['stock'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $menu['stock']
        ]);
        exit;
    }
    
    // Cek apakah menu dengan level yang sama sudah ada di cart
    $levelCondition = $level !== null ? "AND level = $level" : "AND level IS NULL";
    $checkCart = mysqli_query($conn, "
        SELECT * FROM cart 
        WHERE user_id = '$user_id' AND menu_id = '$menu_id' $levelCondition
    ");
    
    if (mysqli_num_rows($checkCart) > 0) {
        // Update quantity jika sudah ada
        $cart = mysqli_fetch_assoc($checkCart);
        $newQuantity = $cart['quantity'] + $quantity;
        
        // Validasi total quantity tidak melebihi 10 atau stock
        if ($newQuantity > $maxQuantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Maksimal ' . $maxQuantity . ' item per menu'
            ]);
            exit;
        }
        
        if ($newQuantity > $menu['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Jumlah pesanan melebihi stok yang tersedia'
            ]);
            exit;
        }
        
        mysqli_query($conn, "
            UPDATE cart 
            SET quantity = '$newQuantity',
                updated_at = NOW()
            WHERE id = '{$cart['id']}'
        ");
        
        $message = 'Jumlah menu berhasil diperbarui';
    } else {
        // Insert baru
        $levelValue = $level !== null ? "'$level'" : "NULL";
        mysqli_query($conn, "
            INSERT INTO cart (user_id, menu_id, quantity, level)
            VALUES ('$user_id', '$menu_id', '$quantity', $levelValue)
        ");
        
        $message = 'Menu berhasil ditambahkan ke keranjang';
    }
    
    // Get total cart items
    $countQuery = mysqli_query($conn, "
        SELECT COUNT(*) as total FROM cart WHERE user_id = '$user_id'
    ");
    $totalItems = mysqli_fetch_assoc($countQuery)['total'];
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $totalItems
    ]);
    exit;
}

// ============================================
// UPDATE QUANTITY
// ============================================
if ($action === 'update') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity < 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Jumlah minimal adalah 1'
        ]);
        exit;
    }
    
    // Cek stock dan hitung max quantity (10 atau stock, mana yang lebih kecil)
    $checkStock = mysqli_query($conn, "
        SELECT m.stock, m.harga, c.menu_id
        FROM cart c
        JOIN menu m ON c.menu_id = m.id
        WHERE c.id = '$cart_id' AND c.user_id = '$user_id'
    ");
    
    if (mysqli_num_rows($checkStock) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ]);
        exit;
    }
    
    $data = mysqli_fetch_assoc($checkStock);
    $maxQuantity = min(10, $data['stock']);
    
    if ($quantity > $maxQuantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Maksimal ' . $maxQuantity . ' item per menu'
        ]);
        exit;
    }
    
    mysqli_query($conn, "
        UPDATE cart 
        SET quantity = '$quantity',
            updated_at = NOW()
        WHERE id = '$cart_id' AND user_id = '$user_id'
    ");
    
    $subtotal = $data['harga'] * $quantity;
    
    echo json_encode([
        'success' => true,
        'message' => 'Jumlah berhasil diperbarui',
        'subtotal' => $subtotal
    ]);
    exit;
}

// ============================================
// REMOVE FROM CART
// ============================================
if ($action === 'remove') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    
    mysqli_query($conn, "
        DELETE FROM cart 
        WHERE id = '$cart_id' AND user_id = '$user_id'
    ");
    
    // Get total cart items
    $countQuery = mysqli_query($conn, "
        SELECT COUNT(*) as total FROM cart WHERE user_id = '$user_id'
    ");
    $totalItems = mysqli_fetch_assoc($countQuery)['total'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Item berhasil dihapus dari keranjang',
        'cart_count' => $totalItems
    ]);
    exit;
}

// ============================================
// CLEAR CART
// ============================================
if ($action === 'clear') {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");
    
    echo json_encode([
        'success' => true,
        'message' => 'Keranjang berhasil dikosongkan'
    ]);
    exit;
}

// ============================================
// GET CART COUNT
// ============================================
if ($action === 'count') {
    $countQuery = mysqli_query($conn, "
        SELECT COUNT(*) as total FROM cart WHERE user_id = '$user_id'
    ");
    $totalItems = mysqli_fetch_assoc($countQuery)['total'];
    
    echo json_encode([
        'success' => true,
        'count' => $totalItems
    ]);
    exit;
}

// Default response jika action tidak valid
echo json_encode([
    'success' => false,
    'message' => 'Action tidak valid'
]);
?>