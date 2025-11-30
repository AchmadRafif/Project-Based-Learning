<?php
session_start();
include "config.php";

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: mobileregister.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil history orders
$ordersQuery = mysqli_query($conn, "
    SELECT 
        o.*,
        COUNT(oi.id) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = '$user_id'
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

$orders = [];
while ($row = mysqli_fetch_assoc($ordersQuery)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History Pesanan - Taki ID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />

    <style>
        :root {
            --redcolor: #bf0f0f;
            --yellow: #f49a24;
            --greencolor: #43a047;
            --orange: #ff9800;
            --gray: #757575;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: #f9f9f9;
            padding-bottom: 100px;
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        h1 {
            text-align: center;
            font-weight: 700;
            font-size: 2rem;
            margin: 5rem 0 2rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 2rem;
        }

        .btn-order {
            display: inline-block;
            background: var(--yellow);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-order:hover {
            background: #d48a20;
            transform: translateY(-2px);
        }

        .order-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .invoice {
            font-weight: 700;
            color: var(--redcolor);
            font-size: 1.1rem;
        }

        .status-badge {
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

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.3rem;
        }

        .info-value {
            font-weight: 600;
            color: #222;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }

        .total-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--redcolor);
        }

        .btn-detail {
            background: var(--yellow);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-detail:hover {
            background: #d48a20;
            transform: translateY(-2px);
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f5f5f5;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        /* Bottom Navigation */
        .bottomnav {
            display: none;
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
                padding: 1rem;
            }

            .order-card {
                padding: 1.2rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .order-info {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }

            .order-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .btn-detail {
                width: 100%;
                text-align: center;
            }

            /* Bottomnav Mobile */
        .bottomnav {
          display: flex;
          justify-content: space-between;
          align-items: center;
          position: fixed;
          bottom: 0.5rem;
          left: 1rem;
          right: 1rem;
          height: 70px;
          background-color: #fff;
          border-top: 1px solid #ddd;
          box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.08);
          border-radius: 25px;
          z-index: 9999;
          padding-bottom: env(
            safe-area-inset-bottom
          ); /* iPhone notch safe area */
        }

        .bottomnav a {
          flex: 1;
          text-align: center;
          color: #777;
          font-size: 0.8rem;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          text-decoration: none;
          line-height: 1.2;
          transition: all 0.3s ease;
        }

        .bottomnav a i {
          font-size: 1.4rem;
          margin-bottom: 4px;
          display: block;
        }

        .bottomnav a.active {
          color: var(--redcolor);
          font-weight: 600;
        }

        .bottomnav a:hover {
          color: var(--redcolor);
          transform: scale(1.05);
        }

        .bottomnav a span {
          margin-top: 2px;
          font-size: 0.75rem;
        }

        /* Pastikan ikon font-awesome atau remixicon tidak terpotong */
        .bottomnav i,
        .bottomnav svg {
          vertical-align: middle;
        }
        }

        /* Modal Detail */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
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
            color: var(--redcolor);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        .close-modal:hover {
            color: var(--redcolor);
        }

        .detail-section {
            margin-bottom: 1.5rem;
        }

        .detail-section h4 {
            font-size: 1rem;
            margin-bottom: 0.8rem;
            color: #555;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem;
            background: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <a href="index.php" class="backbtn">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <div class="container">
        <h1>History Pesanan</h1>

        <?php if (count($orders) === 0): ?>
            <div class="empty-state">
                <i class="fa-solid fa-receipt"></i>
                <h2>Belum Ada Pesanan</h2>
                <p>Anda belum pernah melakukan pemesanan</p>
                <a href="menu.php" class="btn-order">Pesan Sekarang</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                $statusClass = 'status-' . $order['status'];
                $statusText = [
                    'pending' => 'Menunggu',
                    'processing' => 'Diproses',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan'
                ];
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="invoice"><?= $order['invoice_number'] ?></div>
                    <div class="status-badge <?= $statusClass ?>">
                        <?= $statusText[$order['status']] ?>
                    </div>
                </div>

                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">Tanggal Order</span>
                        <span class="info-value">
                            <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Total Item</span>
                        <span class="info-value"><?= $order['total_items'] ?> item</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Metode Pembayaran</span>
                        <div class="payment-method">
                            <?php 
                                $icons = [
                                    'QRIS' => 'fa-qrcode',
                                    'COD' => 'fa-money-bill-wave',
                                    'Transfer' => 'fa-building-columns'
                                ];
                                $icon = $icons[$order['payment_method']] ?? 'fa-credit-card';
                            ?>
                            <i class="fa-solid <?= $icon ?>"></i>
                            <?= $order['payment_method'] ?>
                        </div>
                    </div>
                </div>

                <div class="order-footer">
                    <div>
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.3rem;">Total Pembayaran</div>
                        <div class="total-price">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                    </div>
                    <button class="btn-detail" onclick="showDetail(<?= $order['id'] ?>)">
                        <i class="fa-solid fa-eye"></i> Lihat Detail
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottomnav">
      <a href="index.php"><i class="fa-solid fa-house"></i>Home</a>
      <a href="menu.php"><i class="fa-solid fa-book-open"></i>Menu</a>
      <a href="cart.php"><i class="fa-solid fa-shopping-cart"></i>Pesanan</a>
      <a href="#" id="profileLink"><i class="fa-solid fa-user"></i>Profil</a>
    </div>

    <!-- Modal Detail -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pesanan</h3>
                <button class="close-modal" onclick="closeModal()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div id="modalBody">
                <!-- Content akan diisi via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        async function showDetail(orderId) {
            try {
                const response = await fetch(`get_order_detail1.php?order_id=${orderId}`);
                const data = await response.json();

                if (data.success) {
                    const order = data.order;
                    const items = data.items;

                    let itemsHtml = '';
                    items.forEach(item => {
                        itemsHtml += `
                            <div class="item-detail">
                                <div>
                                    <strong>${item.nama_menu}</strong><br>
                                    <span style="color: #666; font-size: 0.9rem;">
                                        ${item.quantity} x Rp ${parseInt(item.harga).toLocaleString('id-ID')}
                                    </span>
                                </div>
                                <div style="font-weight: 700; color: var(--redcolor);">
                                    Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}
                                </div>
                            </div>
                        `;
                    });

                    document.getElementById('modalBody').innerHTML = `
                        <div class="detail-section">
                            <h4>Informasi Penerima</h4>
                            <div class="detail-row">
                                <span>Nama:</span>
                                <strong>${order.nama_penerima}</strong>
                            </div>
                            <div class="detail-row">
                                <span>No. HP:</span>
                                <strong>${order.no_hp}</strong>
                            </div>
                            <div class="detail-row">
                                <span>Alamat:</span>
                                <strong style="text-align: right; max-width: 60%;">${order.alamat_lengkap}</strong>
                            </div>
                            ${order.catatan ? `
                            <div class="detail-row">
                                <span>Catatan:</span>
                                <em>${order.catatan}</em>
                            </div>
                            ` : ''}
                        </div>

                        <div class="detail-section">
                            <h4>Detail Pesanan</h4>
                            ${itemsHtml}
                            <div class="detail-row" style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #f0f0f0;">
                                <strong style="font-size: 1.2rem;">Total:</strong>
                                <strong style="font-size: 1.3rem; color: var(--redcolor);">
                                    Rp ${parseInt(order.total_price).toLocaleString('id-ID')}
                                </strong>
                            </div>
                        </div>
                    `;

                    document.getElementById('detailModal').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memuat detail pesanan');
            }
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal saat klik overlay
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>