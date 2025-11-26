<?php
session_start();
include "config.php";

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: mobilelogin.php");
    exit;
}

// Validasi POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari form
$nama_penerima = mysqli_real_escape_string($conn, trim($_POST['nama_penerima']));
$no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
$alamat_lengkap = mysqli_real_escape_string($conn, trim($_POST['alamat_lengkap']));
$catatan = mysqli_real_escape_string($conn, trim($_POST['catatan']));
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

// Validasi data
if (empty($nama_penerima) || empty($no_hp) || empty($alamat_lengkap) || empty($payment_method)) {
    $_SESSION['error'] = "Semua data wajib diisi!";
    header("Location: checkout.php");
    exit;
}

// Validasi nomor HP
if (!preg_match("/^08\d{8,11}$/", $no_hp)) {
    $_SESSION['error'] = "Format nomor HP tidak valid!";
    header("Location: checkout.php");
    exit;
}

// Validasi metode pembayaran
$valid_methods = ['QRIS', 'COD', 'Transfer'];
if (!in_array($payment_method, $valid_methods)) {
    $_SESSION['error'] = "Metode pembayaran tidak valid!";
    header("Location: checkout.php");
    exit;
}

// BEGIN TRANSACTION
mysqli_begin_transaction($conn);

try {
    // 1. Ambil data cart user
    $cartQuery = mysqli_query($conn, "
        SELECT 
            c.id as cart_id,
            c.quantity,
            c.level,
            m.id as menu_id,
            m.nama_menu,
            m.harga,
            m.stock,
            (m.harga * c.quantity) as subtotal
        FROM cart c
        JOIN menu m ON c.menu_id = m.id
        WHERE c.user_id = '$user_id'
        FOR UPDATE
    ");

    if (mysqli_num_rows($cartQuery) == 0) {
        throw new Exception("Keranjang kosong!");
    }

    $cartItems = [];
    $totalPrice = 0;

    while ($row = mysqli_fetch_assoc($cartQuery)) {
        // Validasi stock
        if ($row['stock'] < $row['quantity']) {
            throw new Exception("Stok {$row['nama_menu']} tidak mencukupi! Tersisa: {$row['stock']}");
        }

        $cartItems[] = $row;
        $totalPrice += $row['subtotal'];
    }

    // 2. Generate Invoice Number (format: INV/YYYYMMDD/XXXX)
    $date = date('Ymd');
    $lastInvoice = mysqli_query($conn, "
        SELECT invoice_number 
        FROM orders 
        WHERE invoice_number LIKE 'INV/$date/%' 
        ORDER BY id DESC 
        LIMIT 1
    ");

    if (mysqli_num_rows($lastInvoice) > 0) {
        $row = mysqli_fetch_assoc($lastInvoice);
        $lastNumber = intval(substr($row['invoice_number'], -4));
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '0001';
    }

    $invoice_number = "INV/$date/$newNumber";

    // 3. Insert ke table orders
    $insertOrder = mysqli_query($conn, "
        INSERT INTO orders (
            user_id, 
            invoice_number, 
            total_price, 
            nama_penerima, 
            no_hp, 
            alamat_lengkap, 
            catatan, 
            payment_method, 
            status
        ) VALUES (
            '$user_id',
            '$invoice_number',
            '$totalPrice',
            '$nama_penerima',
            '$no_hp',
            '$alamat_lengkap',
            '$catatan',
            '$payment_method',
            'pending'
        )
    ");

    if (!$insertOrder) {
        throw new Exception("Gagal membuat order: " . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);

    // 4. Insert ke table order_items & update stock
    foreach ($cartItems as $item) {
        // Insert order item
        $insertItem = mysqli_query($conn, "
            INSERT INTO order_items (
                order_id, 
                menu_id, 
                nama_menu, 
                harga, 
                quantity,
                level,
                subtotal
            ) VALUES (
                '$order_id',
                '{$item['menu_id']}',
                '{$item['nama_menu']}',
                '{$item['harga']}',
                '{$item['quantity']}',
                '{$item['level']}',
                '{$item['subtotal']}'
            )
        ");

        if (!$insertItem) {
            throw new Exception("Gagal menyimpan detail item: " . mysqli_error($conn));
        }

        // Update stock menu
        $updateStock = mysqli_query($conn, "
            UPDATE menu 
            SET stock = stock - {$item['quantity']} 
            WHERE id = {$item['menu_id']}
        ");

        if (!$updateStock) {
            throw new Exception("Gagal update stock: " . mysqli_error($conn));
        }
    }

    // 5. Hapus cart user
    $deleteCart = mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

    if (!$deleteCart) {
        throw new Exception("Gagal menghapus keranjang: " . mysqli_error($conn));
    }

    // COMMIT TRANSACTION
    mysqli_commit($conn);

    // Set session success
    $_SESSION['success'] = true;
    $_SESSION['order_id'] = $order_id;
    $_SESSION['invoice_number'] = $invoice_number;

    // Redirect ke halaman sukses
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    // ROLLBACK jika ada error
    mysqli_rollback($conn);

    $_SESSION['error'] = $e->getMessage();
    header("Location: checkout.php");
    exit;
}