<?php
session_start();
include "config.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

// Get order_id dari parameter
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!$order_id) {
    http_response_code(400);
    die("Order ID required");
}

// Get order data - pastikan milik user yang login dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($orderResult) === 0) {
    http_response_code(404);
    die("Order not found");
}

$order = mysqli_fetch_assoc($orderResult);

// Get order items dengan prepared statement
$stmtItems = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmtItems, "i", $order_id);
mysqli_stmt_execute($stmtItems);
$itemsResult = mysqli_stmt_get_result($stmtItems);

$items = [];
while ($row = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $row;
}

// Status mapping
$statusText = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Sedang Diproses',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$statusBadge = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled'
];

// Payment method icons
$paymentIcons = [
    'QRIS' => 'fa-qrcode',
    'COD' => 'fa-money-bill-wave',
    'Transfer' => 'fa-building-columns'
];
$paymentIcon = $paymentIcons[$order['payment_method']] ?? 'fa-credit-card';

// Generate QRIS data
$qrisData = "00020101021126660014ID.CO.QRIS.WWW0116ID1234567890123450214" . $order['invoice_number'] . "0303UMI5204581153033605802ID5909TAKI ID6007JAKARTA61051234062070703A01630445BB";
?>

<style>
    .order-detail-modal {
        max-width: 600px;
        text-align: left;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .modal-header h3 {
        font-size: 1.3rem;
        color: #bf0f0f;
        font-weight: 700;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
        transition: 0.3s;
    }

    .close-modal:hover {
        color: #bf0f0f;
        transform: rotate(90deg);
    }

    .detail-section {
        margin-bottom: 1.5rem;
    }

    .detail-section h4 {
        font-size: 1rem;
        margin-bottom: 0.8rem;
        color: #555;
        font-weight: 600;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.7rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #666;
        font-weight: 500;
    }

    .detail-value {
        font-weight: 600;
        color: #222;
    }

    .status-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-pending {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-processing {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-completed {
        background: #e8f5e9;
        color: #388e3c;
    }

    .status-cancelled {
        background: #ffebee;
        color: #c62828;
    }

    .item-detail {
        display: flex;
        justify-content: space-between;
        padding: 1rem;
        background: #f9f9f9;
        border-radius: 10px;
        margin-bottom: 0.8rem;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.4rem;
    }

    .item-qty {
        font-size: 0.85rem;
        color: #666;
    }

    .item-price {
        font-size: 1rem;
        font-weight: 600;
        color: #bf0f0f;
    }

    .total-section {
        background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
        padding: 1.2rem;
        border-radius: 12px;
        margin-top: 1rem;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 1.3rem;
        font-weight: 700;
        color: #bf0f0f;
    }

    /* Simple QRIS QR Code Only */
    .qris-qrcode-only {
        text-align: center;
        padding: 1.5rem 0;
        margin: 1.5rem 0;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .qris-qrcode-only h5 {
        font-size: 0.95rem;
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

    #qrcode-detail {
        width: 200px;
        height: 200px;
    }

    .payment-info {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .payment-info h5 {
        color: #1976d2;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .payment-info p {
        color: #555;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        #qrcode-detail {
            width: 180px;
            height: 180px;
        }

        #qrcode-detail img{
            width: 180px;
            height: 180px;
        }
    }
</style>

<div class="order-detail-modal">
    <div class="modal-header">
        <div>
            <h3><?= htmlspecialchars($order['invoice_number']) ?></h3>
            <small style="color: #666;"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></small>
        </div>
        <button class="close-modal" onclick="document.getElementById('orderDetailPopup').classList.remove('active')">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <div class="detail-section">
        <h4>Status Pesanan</h4>
        <div style="margin-bottom: 1rem;">
            <span class="status-badge <?= $statusBadge[$order['status']] ?>">
                <?= $statusText[$order['status']] ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Metode Pembayaran</span>
            <span class="detail-value">
                <i class="fa-solid <?= $paymentIcon ?>"></i>
                <?= htmlspecialchars($order['payment_method']) ?>
            </span>
        </div>
    </div>

    <?php if ($order['payment_method'] == 'QRIS'): ?>
        <!-- QR Code Only -->
        <div class="qris-qrcode-only">
            <h5><i class="fa-solid fa-qrcode"></i> Scan QR Code untuk Pembayaran</h5>
            <div class="qrcode-wrapper">
                <div id="qrcode-detail"><img src="img/qrcode.jpg" alt="" width="200px"></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="detail-section">
        <h4>Informasi Penerima</h4>
        <div class="detail-row">
            <span class="detail-label">Nama</span>
            <span class="detail-value"><?= htmlspecialchars($order['nama_penerima']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">No. HP</span>
            <span class="detail-value"><?= htmlspecialchars($order['no_hp']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Alamat</span>
            <span class="detail-value" style="text-align: right; max-width: 60%; word-wrap: break-word;">
                <?= htmlspecialchars($order['alamat_lengkap']) ?>
            </span>
        </div>
        <?php if (!empty($order['catatan'])): ?>
            <div class="detail-row">
                <span class="detail-label">Catatan</span>
                <span class="detail-value" style="text-align: right; max-width: 60%;">
                    <?= htmlspecialchars($order['catatan']) ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-section">
        <h4>Detail Pesanan</h4>
        <?php foreach ($items as $item): ?>
            <div class="item-detail">
                <div class="item-info">
                    <div class="item-name">
                        <?= htmlspecialchars($item['nama_menu']) ?>
                        <?php if (isset($item['level']) && $item['level'] > 0): ?>
                            <span style="color: #ff4444;">🌶️ Lvl. <?= $item['level'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="item-qty">
                        <?= $item['quantity'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                    </div>
                </div>
                <div class="item-price">
                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="total-section">
        <div class="total-row">
            <span>Total Pembayaran:</span>
            <span>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
        </div>
    </div>

    <?php if ($order['payment_method'] == 'QRIS'): ?>
        <div class="payment-info">
            <h5><i class="fa-solid fa-info-circle"></i> Instruksi Pembayaran QRIS</h5>
            <p>
                1. Buka aplikasi pembayaran digital Anda (GoPay, OVO, Dana, ShopeePay, dll)<br>
                2. Scan QR Code di atas<br>
                3. Konfirmasi pembayaran<br>
                4. Pesanan akan diproses setelah pembayaran diterima
            </p>
        </div>
    <?php elseif ($order['payment_method'] == 'Transfer'): ?>
        <div class="payment-info">
            <h5><i class="fa-solid fa-info-circle"></i> Instruksi Pembayaran Transfer</h5>
            <p>
                Transfer ke rekening:<br>
                <strong>BCA 1234567890 a/n Taki ID</strong><br>
                Nominal: <strong>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></strong><br>
                Kirim bukti transfer melalui WhatsApp kami
            </p>
        </div>
    <?php else: ?>
        <div class="payment-info">
            <h5><i class="fa-solid fa-info-circle"></i> Informasi Pesanan</h5>
            <p>
                Pesanan Anda akan segera diproses. Silahkan siapkan uang pas saat pesanan tiba.<br>
                Estimasi: 30-45 menit
            </p>
        </div>
    <?php endif; ?>
</div>

<?php if ($order['payment_method'] == 'QRIS'): ?>
<script>
// Function to load QRCode library
function loadQRCodeLibrary(callback) {
    if (typeof QRCode !== 'undefined') {
        callback();
        return;
    }
    
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    script.onload = callback;
    script.onerror = function() {
        console.error('Failed to load QRCode library');
    };
    document.head.appendChild(script);
}

// Generate QR Code
function generateQRCode() {
    var qrElement = document.getElementById('qrcode-detail');
    if (!qrElement) return;
    
    // Clear existing content
    qrElement.innerHTML = '';
    
    try {
        new QRCode(qrElement, {
            text: "<?= $qrisData ?>",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    } catch (error) {
        console.error('Error generating QR Code:', error);
        qrElement.innerHTML = '<p style="color: #999; font-size: 0.9rem;">Gagal generate QR Code</p>';
    }
}

// Load library and generate QR Code
loadQRCodeLibrary(generateQRCode);
</script>
<?php endif; ?>