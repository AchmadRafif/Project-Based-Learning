<?php
session_start();
include "config.php";

// // Check login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: mobilelogin.php");
//     exit;
// }

$user_id = $_SESSION['user_id'];

// Ambil data user
$userStmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$user = mysqli_fetch_assoc($userResult);

// Handle POST checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $nama_penerima = mysqli_real_escape_string($conn, trim($_POST['nama_penerima']));
    $no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
    $alamat_lengkap = mysqli_real_escape_string($conn, trim($_POST['alamat_lengkap']));
    $catatan = mysqli_real_escape_string($conn, trim($_POST['catatan'] ?? ''));
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Validasi form
    if (empty($nama_penerima) || empty($no_hp) || empty($alamat_lengkap)) {
        $error = "Semua field harus diisi!";
    } else {
        // Mulai transaction
        mysqli_begin_transaction($conn);

        try {
            // 1. Ambil cart items dengan LEVEL
            $cartStmt = mysqli_prepare($conn, "
                SELECT c.id as cart_id, c.menu_id, c.quantity, c.level, m.nama_menu, m.harga
                FROM cart c
                JOIN menu m ON c.menu_id = m.id
                WHERE c.user_id = ?
            ");
            mysqli_stmt_bind_param($cartStmt, "i", $user_id);
            mysqli_stmt_execute($cartStmt);
            $cartResult = mysqli_stmt_get_result($cartStmt);

            if (mysqli_num_rows($cartResult) === 0) {
                throw new Exception("Keranjang kosong!");
            }

            $cartItems = [];
            $totalPrice = 0;

            while ($row = mysqli_fetch_assoc($cartResult)) {
                $cartItems[] = $row;
                $totalPrice += $row['harga'] * $row['quantity'];
            }

            // 2. Generate invoice number
            $invoice_number = "INV-" . date('YmdHis') . "-" . rand(1000, 9999);

            // 3. Insert ke table orders
            $orderStmt = mysqli_prepare($conn, "
                INSERT INTO orders (user_id, invoice_number, total_price, nama_penerima, no_hp, alamat_lengkap, catatan, payment_method, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            mysqli_stmt_bind_param(
                $orderStmt,
                "isisssss",
                $user_id,
                $invoice_number,
                $totalPrice,
                $nama_penerima,
                $no_hp,
                $alamat_lengkap,
                $catatan,
                $payment_method
            );

            if (!mysqli_stmt_execute($orderStmt)) {
                throw new Exception("Gagal membuat order!");
            }

            $order_id = mysqli_insert_id($conn);

            // 4. Insert order items dengan LEVEL
            $itemStmt = mysqli_prepare($conn, "
                INSERT INTO order_items (order_id, menu_id, nama_menu, harga, quantity, level, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $subtotal = $item['harga'] * $item['quantity'];
                // level bisa NULL untuk menu non-Seblak
                $level = $item['level'] ?? null;

                mysqli_stmt_bind_param(
                    $itemStmt,
                    "iisiiis",
                    $order_id,
                    $item['menu_id'],
                    $item['nama_menu'],
                    $item['harga'],
                    $item['quantity'],
                    $level,
                    $subtotal
                );

                if (!mysqli_stmt_execute($itemStmt)) {
                    throw new Exception("Gagal menambahkan item ke order!");
                }
            }

            // 5. Delete cart items setelah berhasil
            $deleteStmt = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($deleteStmt, "i", $user_id);

            if (!mysqli_stmt_execute($deleteStmt)) {
                throw new Exception("Gagal menghapus cart!");
            }

            // Commit transaction
            mysqli_commit($conn);

            // Set session success dan redirect
            $_SESSION['success'] = true;
            $_SESSION['order_id'] = $order_id;
            $_SESSION['invoice_number'] = $invoice_number;

            header("Location: order_success.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaction jika ada error
            mysqli_rollback($conn);
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Ambil cart items untuk ditampilkan
$cartStmt = mysqli_prepare($conn, "
    SELECT 
        c.id as cart_id,
        c.quantity,
        c.level,
        m.id as menu_id,
        m.nama_menu,
        m.harga,
        m.foto_menu,
        k.nama_kategori,
        (m.harga * c.quantity) as subtotal
    FROM cart c
    JOIN menu m ON c.menu_id = m.id
    JOIN kategori k ON m.kategori_id = k.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
mysqli_stmt_bind_param($cartStmt, "i", $user_id);
mysqli_stmt_execute($cartStmt);
$cartResult = mysqli_stmt_get_result($cartStmt);

$cartItems = [];
$totalPrice = 0;

while ($row = mysqli_fetch_assoc($cartResult)) {
    $cartItems[] = $row;
    $totalPrice += $row['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - Taki ID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />

    <style>
        :root {
            --redcolor: #bf0f0f;
            --yellow: #f49a24;
            --blacknav: #383838;
            --gray: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: #f9f9f9;
            color: #222;
            padding-bottom: 100px;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .backbtn {
            position: fixed;
            top: 1.5rem;
            left: 2rem;
            background-color: var(--redcolor);
            color: #fff;
            text-decoration: none;
            padding: 0.6rem 1.4rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.25s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .backbtn:hover {
            background-color: #c30000;
            transform: translateY(-2px);
        }

        h1 {
            text-align: center;
            font-weight: 700;
            font-size: 1.8rem;
            margin: 5rem 0 2rem;
        }

        .section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--redcolor);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        label span {
            color: var(--redcolor);
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            background: var(--gray);
            transition: 0.2s;
            font-family: "Poppins", sans-serif;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--redcolor);
            background: #fff;
            outline: none;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .order-summary {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
            border: 2px solid var(--yellow);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0e6d2;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-name {
            flex: 1;
            font-weight: 500;
        }

        .order-item-qty {
            color: #666;
            margin: 0 1rem;
        }

        .order-item-price {
            font-weight: 600;
            color: var(--redcolor);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            margin-top: 1rem;
            border-top: 2px solid var(--yellow);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--redcolor);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .payment-option {
            background: white;
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-option:hover {
            border-color: var(--yellow);
            transform: translateY(-2px);
        }

        .payment-option.selected {
            border-color: var(--yellow);
            background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
        }

        .payment-option i {
            font-size: 2rem;
            color: var(--yellow);
            margin-bottom: 0.5rem;
        }

        .payment-option label {
            font-weight: 600;
            cursor: pointer;
            margin: 0;
        }

        .checkout-btn {
            width: 100%;
            background: var(--redcolor);
            border: none;
            padding: 1rem;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            margin-top: 2rem;
        }

        .checkout-btn:hover {
            background: #a00d0d;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(191, 15, 15, 0.3);
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 768px) {
            .backbtn {
                display: none;
            }

            h1 {
                font-size: 1.5rem;
                margin-top: 3rem;
            }

            .container {
                padding: 0 1rem;
            }

            .section {
                padding: 1.2rem;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--redcolor);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <a href="cart.php" class="backbtn">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <div class="container">
        <h1>Checkout Pesanan</h1>

        <form id="checkoutForm" method="POST" action="process_checkout.php">
            <!-- Informasi Penerima -->
            <div class="section">
                <div class="section-title">
                    <i class="fa-solid fa-user"></i>
                    Informasi Penerima
                </div>

                <div class="form-group">
                    <label for="nama_penerima">Nama Penerima <span>*</span></label>
                    <input type="text" id="nama_penerima" name="nama_penerima"
                        value="<?= htmlspecialchars($userData['nama_lengkap'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP <span>*</span></label>
                    <input type="tel" id="no_hp" name="no_hp"
                        value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>"
                        placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="form-group">
                    <label for="alamat_lengkap">Alamat Lengkap Pengiriman <span>*</span></label>
                    <textarea id="alamat_lengkap" name="alamat_lengkap"
                        placeholder="Masukkan alamat lengkap termasuk RT/RW, Kelurahan, Kecamatan"
                        required><?= htmlspecialchars($userData['alamat'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan (Opsional)</label>
                    <textarea id="catatan" name="catatan"
                        placeholder="Contoh: Tolong es di es teh jumbo dikurangin ya"></textarea>
                </div>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="section order-summary">
                <div class="section-title">
                    <i class="fa-solid fa-receipt"></i>
                    Ringkasan Pesanan
                </div>

                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="order-item-name">
                            <?= htmlspecialchars($item['nama_menu']) ?>
                            <?php if ($item['level'] !== null): ?>
                                <span style="color: #ff4b4b; font-size: 0.85rem;">(Lvl.<?= $item['level'] ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="order-item-qty">x<?= $item['quantity'] ?></div>
                        <div class="order-item-price">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </div>

                    </div>
                <?php endforeach; ?>

                <div class="total-row">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="section">
                <div class="section-title">
                    <i class="fa-solid fa-credit-card"></i>
                    Metode Pembayaran
                </div>

                <div class="payment-methods">
                    <div class="payment-option" onclick="selectPayment(this, 'QRIS')">
                        <input type="radio" name="payment_method" value="QRIS" id="qris" required>
                        <i class="fa-solid fa-qrcode"></i>
                        <label for="qris">QRIS</label>
                    </div>

                    <div class="payment-option" onclick="selectPayment(this, 'COD')">
                        <input type="radio" name="payment_method" value="COD" id="cod" required>
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <label for="cod">Bayar di Tempat</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="checkout-btn">
                <i class="fa-solid fa-check-circle"></i>
                Konfirmasi Pesanan
            </button>
        </form>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3>Memproses Pesanan...</h3>
            <p>Mohon tunggu sebentar</p>
        </div>
    </div>

    <script>
        function selectPayment(element, method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selected class to clicked option
            element.classList.add('selected');

            // Check the radio button
            element.querySelector('input[type="radio"]').checked = true;
        }

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validation
            const nama = document.getElementById('nama_penerima').value.trim();
            const hp = document.getElementById('no_hp').value.trim();
            const alamat = document.getElementById('alamat_lengkap').value.trim();
            const payment = document.querySelector('input[name="payment_method"]:checked');

            if (!nama || !hp || !alamat) {
                alert('Mohon lengkapi semua data yang wajib diisi!');
                return;
            }

            if (!payment) {
                alert('Mohon pilih metode pembayaran!');
                return;
            }

            // Validate phone number
            if (!/^08\d{8,11}$/.test(hp)) {
                alert('Format nomor HP tidak valid! Harus diawali 08 dan 10-13 digit');
                return;
            }

            // Show loading
            document.getElementById('loadingOverlay').classList.add('active');

            // Submit form
            this.submit();
        });
    </script>
</body>

</html>