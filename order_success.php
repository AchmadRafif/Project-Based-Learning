<?php
// PASTIKAN TIDAK ADA SPASI ATAU KARAKTER SEBELUM <?php
session_start();
include "config.php";

// Redirect jika tidak ada order_id di session
if (!isset($_SESSION['success']) || !isset($_SESSION['order_id'])) {
    header("Location: cart.php");
    exit;
}

$order_id = $_SESSION['order_id'];
$invoice_number = $_SESSION['invoice_number'];

// Clear session success
unset($_SESSION['success']);

// Ambil detail order dengan prepared statement (SECURITY FIX)
$orderQuery = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
mysqli_stmt_bind_param($orderQuery, "i", $order_id);
mysqli_stmt_execute($orderQuery);
$orderResult = mysqli_stmt_get_result($orderQuery);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
    header("Location: cart.php");
    exit;
}

// Ambil items order dengan prepared statement (SECURITY FIX)
$itemsQuery = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($itemsQuery, "i", $order_id);
mysqli_stmt_execute($itemsQuery);
$itemsResult = mysqli_stmt_get_result($itemsQuery);

$items = [];
while ($row = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $row;
}

// Generate QRIS data
$qrisData = "00020101021126660014ID.CO.QRIS.WWW0116ID1234567890123450214" . $invoice_number . "0303UMI5204581153033605802ID5909TAKI ID6007JAKARTA61051234062070703A01630445BB";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pesanan Berhasil - Taki ID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="stylefooter.css">

    <style>
        :root {
            --redcolor: #bf0f0f;
            --yellow: #f49a24;
            --greencolor: #43a047;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .success-container {
            background: white;
            border-radius: 24px;
            max-width: 600px;
            width: 100%;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #43a047 0%, #66bb6a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .invoice-box {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
            border: 2px dashed var(--yellow);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .invoice-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.3rem;
        }

        .invoice-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--redcolor);
            letter-spacing: 1px;
        }

        /* Simple QR Code Section */
        .qris-qrcode-only {
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-top: 2px dashed #e0e0e0;
            border-bottom: 2px dashed #e0e0e0;
        }

        .qris-qrcode-only h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .qrcode-wrapper {
            display: inline-block;
            padding: 1rem;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
        }

        #qrcode-success {
            width: 220px;
            height: 220px;
        }

        #qrcode-success img {
            width: 220px;
            height: 220px;
        }

        .order-details {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .detail-value {
            color: #222;
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }

        .total-row {
            background: var(--yellow);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .items-list {
            margin-top: 1rem;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .payment-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .payment-info h4 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .payment-info p {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 0.9rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--redcolor);
            color: white;
        }

        .btn-primary:hover {
            background: #a00d0d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(191, 15, 15, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--redcolor);
            border: 2px solid var(--redcolor);
        }

        .btn-secondary:hover {
            background: var(--redcolor);
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .success-container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .invoice-number {
                font-size: 1.2rem;
            }

            #qrcode-success {
                width: 200px;
                height: 200px;
            }

            #qrcode-success img{
                width: 200px;
                height: 200px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1>Pesanan Berhasil!</h1>
        <p class="subtitle">Terima kasih telah memesan di Taki ID</p>

        <div class="invoice-box">
            <div class="invoice-label">Nomor Invoice</div>
            <div class="invoice-number"><?= htmlspecialchars($invoice_number) ?></div>
        </div>

        <?php if ($order['payment_method'] == 'QRIS'): ?>
            <!-- QR Code Only -->
            <div class="qris-qrcode-only">
                <h3><i class="fa-solid fa-qrcode"></i> Scan QR Code untuk Pembayaran</h3>
                <div class="qrcode-wrapper">
                    <div id="qrcode-success"><img src="img/qrcode.jpg" alt=""></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Nama Penerima</span>
                <span class="detail-value"><?= htmlspecialchars($order['nama_penerima']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">No. HP</span>
                <span class="detail-value"><?= htmlspecialchars($order['no_hp']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Alamat</span>
                <span class="detail-value"><?= htmlspecialchars($order['alamat_lengkap']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Metode Pembayaran</span>
                <span class="detail-value">
                    <?php
                    $paymentIcons = [
                        'QRIS' => 'fa-qrcode',
                        'COD' => 'fa-money-bill-wave',
                        'Transfer' => 'fa-building-columns'
                    ];
                    $icon = $paymentIcons[$order['payment_method']] ?? 'fa-credit-card';
                    ?>
                    <i class="fa-solid <?= $icon ?>"></i> <?= htmlspecialchars($order['payment_method']) ?>
                </span>
            </div>

            <?php if (!empty($order['catatan'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Catatan</span>
                    <span class="detail-value"><?= htmlspecialchars($order['catatan']) ?></span>
                </div>
            <?php endif; ?>

            <div class="items-list">
                <strong>Pesanan:</strong>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <span>
                            <?= htmlspecialchars($item['nama_menu']) ?>
                            <?php if (isset($item['level']) && $item['level'] > 0): ?>
                                <span>Lvl. <?= $item['level'] ?></span>
                            <?php endif; ?>
                            x<?= $item['quantity'] ?>
                        </span>
                        <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-row">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <?php if ($order['payment_method'] == 'QRIS'): ?>
            <div class="payment-info">
                <h4><i class="fa-solid fa-info-circle"></i> Instruksi Pembayaran</h4>
                <p>
                    1. Buka aplikasi pembayaran digital Anda (GoPay, OVO, Dana, ShopeePay, dll)<br>
                    2. Scan QR Code di atas<br>
                    3. Konfirmasi pembayaran<br>
                    4. Pesanan akan diproses setelah pembayaran diterima
                </p>
            </div>
        <?php elseif ($order['payment_method'] == 'Transfer'): ?>
            <div class="payment-info">
                <h4><i class="fa-solid fa-info-circle"></i> Instruksi Pembayaran</h4>
                <p>
                    Transfer ke rekening:<br>
                    <strong>BCA 1234567890 a/n Taki ID</strong><br>
                    Nominal: <strong>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></strong><br>
                    Kirim bukti transfer melalui WhatsApp kami
                </p>
            </div>
        <?php else: ?>
            <div class="payment-info">
                <h4><i class="fa-solid fa-info-circle"></i> Informasi Pesanan</h4>
                <p>
                    Pesanan Anda akan segera diproses. Silahkan siapkan uang pas saat pesanan tiba.<br>
                    Estimasi: 30-45 menit
                </p>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fa-solid fa-house"></i> Kembali ke Home
            </a>
            <!-- <a href="orders_history.php" class="btn btn-secondary">
                <i class="fa-solid fa-clock-rotate-left"></i> Lihat Pesanan
            </a> -->
        </div>
    </div>

    <?php if ($order['payment_method'] == 'QRIS'): ?>
        <!-- QRCode.js Library -->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script> -->
        <script>
            // Generate QR Code when library is loaded
            window.addEventListener('load', function() {
                try {
                    new QRCode(document.getElementById("qrcode-success"), {
                        text: "<?= $qrisData ?>",
                        width: 220,
                        height: 220,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } catch (error) {
                    console.error('Error generating QR Code:', error);
                }
            });
        </script>
    <?php endif; ?>

    <script>
        // Auto scroll to top
        window.scrollTo(0, 0);

        // Prevent back button after success
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
    </script>
</body>

</html>