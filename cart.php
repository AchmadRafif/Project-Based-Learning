<?php
session_start();
include "config.php";

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: mobilecartlog.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data cart dari database
$cartQuery = mysqli_query($conn, "
    SELECT 
        c.id as cart_id,
        c.quantity,
        c.level,
        m.id as menu_id,
        m.nama_menu,
        m.harga,
        m.foto_menu,
        m.stock,
        k.nama_kategori,
        (m.harga * c.quantity) as subtotal
    FROM cart c
    JOIN menu m ON c.menu_id = m.id
    JOIN kategori k ON m.kategori_id = k.id
    WHERE c.user_id = '$user_id'
    ORDER BY c.created_at DESC
");

$cartItems = [];
$totalPrice = 0;

while ($row = mysqli_fetch_assoc($cartQuery)) {
    $cartItems[] = $row;
    $totalPrice += $row['subtotal'];
}

$isEmpty = count($cartItems) === 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Keranjang Belanja - Taki ID</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />

    <style>
        :root {
            --redcolor: #bf0f0f;
            --blacknav: #383838;
            --greencolor: #89c946;
            --yellow: #f49a24;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: #f9f9f9;
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

        .cart-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        .cart-title {
            font-size: 2rem;
            margin: 5rem 0 2rem 0;
            font-weight: 700;
            text-align: center;
        }

        .cart-empty {
            text-align: center;
            margin: 5rem auto;
            padding: 3rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .cart-empty i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .cart-empty h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .cart-empty p {
            color: #666;
            margin-bottom: 2rem;
        }

        .btn-lihat-menu {
            display: inline-block;
            background: var(--yellow);
            padding: 0.8rem 2rem;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-lihat-menu:hover {
            background: #d48a20;
            transform: translateY(-2px);
        }

        .cart-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 1fr 0.5fr;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            color: #222;
            margin-bottom: 0.5rem;
        }

        .cart-items {
            background: white;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 1fr 0.5fr;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            align-items: center;
            transition: background 0.3s;
        }

        .cart-item:hover {
            background: #f9f9f9;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }

        .no-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 2rem;
        }

        .item-details h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .item-details p {
            font-size: 0.85rem;
            color: #666;
        }

        .item-price {
            font-weight: 600;
            color: var(--redcolor);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-weight: 600;
        }

        .qty-btn:hover {
            background: var(--redcolor);
            color: white;
            border-color: var(--redcolor);
        }

        .qty-input {
            width: 60px;
            text-align: center;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-weight: 600;
        }

        .item-subtotal {
            font-weight: 700;
            color: #222;
            font-size: 1.1rem;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.2s;
        }

        .remove-btn:hover {
            color: var(--redcolor);
        }

        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--redcolor);
            margin-top: 1rem;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: white;
            color: var(--redcolor);
            border: 2px solid var(--redcolor);
        }

        .btn-cancel:hover {
            background: var(--redcolor);
            color: white;
        }

        .btn-checkout {
            background: var(--yellow);
            color: white;
        }

        .btn-checkout:hover {
            background: #d48a20;
            transform: translateY(-2px);
        }

        .bottomnav {
            display: none;
        }

        @media (max-width: 768px) {
            .backbtn {
                display: none;
            }

            .cart-section {
                padding: 1rem;
                margin-bottom: 5rem;
            }

            .cart-title {
                font-size: 1.5rem;
                margin: 3rem 0 1.5rem;
            }

            .cart-header {
                display: none;
            }

            .cart-items {
                border-radius: 12px;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1rem;
            }

            .item-info {
                flex-direction: row;
                gap: 0.8rem;
            }

            .item-image,
            .no-image {
                width: 60px;
                height: 60px;
            }

            .item-details h3 {
                font-size: 0.9rem;
            }

            .quantity-controls {
                justify-content: space-between;
            }

            .cart-actions {
                flex-direction: column-reverse;
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

        /* ============================= */
        /* Popup Konfirmasi */
        /* ============================= */
        .confirm-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .confirm-popup-overlay.active {
            display: flex;
        }

        .confirm-popup {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.3s ease;
        }

        .confirm-popup-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .confirm-popup-icon i {
            font-size: 2.5rem;
            color: var(--redcolor);
        }

        .confirm-popup-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.8rem;
        }

        .confirm-popup-text {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 2rem;
        }

        .confirm-popup-buttons {
            display: flex;
            gap: 1rem;
        }

        .confirm-popup-buttons button {
            flex: 1;
            padding: 0.9rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .confirm-btn-cancel {
            background: white;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .confirm-btn-cancel:hover {
            border-color: var(--redcolor);
            color: var(--redcolor);
        }

        .confirm-btn-yes {
            background: var(--redcolor);
            color: white;
        }

        .confirm-btn-yes:hover {
            background: #a00d0d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(191, 15, 15, 0.3);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .confirm-popup {
                max-width: 90%;
                padding: 1.5rem;
            }

            .confirm-popup-icon {
                width: 70px;
                height: 70px;
            }

            .confirm-popup-icon i {
                font-size: 2rem;
            }

            .confirm-popup-title {
                font-size: 1.3rem;
            }

            .confirm-popup-buttons {
                flex-direction: column-reverse;
            }

            .confirm-popup-buttons button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <a href="index.php" class="backbtn">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <section class="cart-section">
        <h1 class="cart-title">Keranjang Belanja Anda</h1>

        <?php if ($isEmpty): ?>
            <!-- Empty State -->
            <div class="cart-empty">
                <i class="fa-solid fa-shopping-cart"></i>
                <h2>Keranjang Anda Masih Kosong</h2>
                <p>Berbagai menu kami siap untuk anda pesan</p>
                <a href="menu.php" class="btn-lihat-menu">Lihat Menu</a>
            </div>
        <?php else: ?>
            <!-- Cart Header -->
            <div class="cart-header">
                <span>Produk</span>
                <span>Harga</span>
                <span>Jumlah</span>
                <span>Subtotal</span>
                <span></span>
            </div>

            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-cart-id="<?= $item['cart_id'] ?>">
                        <div class="item-info">
                            <?php if (!empty($item['foto_menu']) && file_exists("img/MenuTaki/" . $item['foto_menu'])): ?>
                                <img src="img/MenuTaki/<?= $item['foto_menu'] ?>" alt="<?= $item['nama_menu'] ?>" class="item-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                            <?php endif; ?>

                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['nama_menu']) ?> <?php if ($item['level'] > 0): ?>
                                        Lvl. <?= $item['level'] ?><?php endif; ?> </h3>
                                <p><?= htmlspecialchars($item['nama_kategori']) ?></p>
                                <?php if ($item['stock'] < 10): ?>
                                    <p style="color: #ff4b4b; font-weight: 600; font-size: 0.8rem;">
                                        Stok terbatas: <?= $item['stock'] ?> pcs
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="item-price">
                            Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                        </div>

                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="decreaseQuantity(<?= $item['cart_id'] ?>)">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= min(10, $item['stock']) ?>"
                                onchange="manualUpdateQuantity(<?= $item['cart_id'] ?>)"
                                data-max="<?= min(10, $item['stock']) ?>"
                                data-cart-id="<?= $item['cart_id'] ?>">
                            <button class="qty-btn" onclick="increaseQuantity(<?= $item['cart_id'] ?>)">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <div class="item-subtotal" data-subtotal="<?= $item['subtotal'] ?>">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </div>

                        <button class="remove-btn" onclick="removeItem(<?= $item['cart_id'] ?>)" title="Hapus item">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Total Item:</span>
                    <span id="totalItems"><?= count($cartItems) ?> item</span>
                </div>
                <div class="summary-row total">
                    <span>Total Harga:</span>
                    <span id="totalPrice">Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="cart-actions">
                <button class="btn btn-cancel" onclick="clearCart()">
                    <i class="fa-solid fa-trash"></i> Kosongkan Keranjang
                </button>
                <a href="checkout.php" class="btn btn-checkout">
                    <i class="fa-solid fa-credit-card"></i> Lanjut ke Pembayaran
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Bottom Navigation -->
    <div class="bottomnav">
        <a href="index.php">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="menu.php">
            <i class="fa-solid fa-book-open"></i>
            <span>Menu</span>
        </a>
        <a href="cart.php" class="active">
            <i class="fa-solid fa-shopping-cart"></i>
            <span>Pesanan</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="mobileprofile.php">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>
        <?php else: ?>
            <a href="mobileregister.php">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>
        <?php endif; ?>
    </div>
    </div>

    <script>
        // Increase Quantity
        function increaseQuantity(cartId) {
            const itemRow = document.querySelector(`[data-cart-id="${cartId}"]`);
            const input = itemRow.querySelector('.qty-input');
            const currentQty = parseInt(input.value);
            const maxQty = parseInt(input.dataset.max);

            if (currentQty >= maxQty) {
                showNotification(`Maksimal ${maxQty} item per menu`, 'error');
                return;
            }

            const newQty = currentQty + 1;
            updateQuantity(cartId, newQty);
        }

        // Decrease Quantity
        function decreaseQuantity(cartId) {
            const itemRow = document.querySelector(`[data-cart-id="${cartId}"]`);
            const input = itemRow.querySelector('.qty-input');
            const currentQty = parseInt(input.value);

            if (currentQty <= 1) {
                // Langsung hapus jika quantity = 1
                removeItem(cartId);
                return;
            }

            const newQty = currentQty - 1;
            updateQuantity(cartId, newQty);
        }

        // Manual Update (ketika user ketik langsung di input)
        function manualUpdateQuantity(cartId) {
            const itemRow = document.querySelector(`[data-cart-id="${cartId}"]`);
            const input = itemRow.querySelector('.qty-input');
            let newQty = parseInt(input.value);
            const maxQty = parseInt(input.dataset.max);

            // Validasi input
            if (isNaN(newQty) || newQty < 1) {
                input.value = 1;
                newQty = 1;
            }

            if (newQty > maxQty) {
                showNotification(`Maksimal ${maxQty} item per menu`, 'error');
                input.value = maxQty;
                newQty = maxQty;
            }

            updateQuantity(cartId, newQty);
        }

        // Update Quantity - FIXED VERSION
        async function updateQuantity(cartId, newQuantity) {
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('cart_id', cartId);
                formData.append('quantity', newQuantity);

                const response = await fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Update UI
                    const itemRow = document.querySelector(`[data-cart-id="${cartId}"]`);
                    itemRow.querySelector('.qty-input').value = newQuantity;
                    itemRow.querySelector('.item-subtotal').textContent =
                        'Rp ' + result.subtotal.toLocaleString('id-ID');
                    itemRow.querySelector('.item-subtotal').dataset.subtotal = result.subtotal;

                    updateCartTotal();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Gagal memperbarui jumlah', 'error');
            }
        }

        // Remove Item - TANPA KONFIRMASI
        async function removeItem(cartId) {
            try {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('cart_id', cartId);

                const response = await fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Remove from UI dengan animasi
                    const itemRow = document.querySelector(`[data-cart-id="${cartId}"]`);
                    itemRow.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        itemRow.remove();
                        updateCartTotal();

                        // Reload jika cart kosong
                        if (result.cart_count === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Gagal menghapus item', 'error');
            }
        }

        // Clear Cart - Buka Popup Konfirmasi
        function clearCart() {
            const popup = document.getElementById('confirmPopup');
            popup.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        }

        // Close Popup Konfirmasi
        function closeConfirmPopup() {
            const popup = document.getElementById('confirmPopup');
            popup.classList.remove('active');
            document.body.style.overflow = ''; // Enable scroll
        }

        // Confirm Clear Cart - Eksekusi Hapus
        async function confirmClearCart() {
            closeConfirmPopup(); // Tutup popup dulu

            try {
                const formData = new FormData();
                formData.append('action', 'clear');

                const response = await fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('✓ Keranjang berhasil dikosongkan', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('❌ Gagal mengosongkan keranjang', 'error');
            }
        }

        // Close popup saat klik overlay
        document.addEventListener('DOMContentLoaded', function() {
            const confirmPopup = document.getElementById('confirmPopup');
            if (confirmPopup) {
                confirmPopup.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeConfirmPopup();
                    }
                });
            }
        });

        // Update Cart Total
        function updateCartTotal() {
            const subtotals = document.querySelectorAll('.item-subtotal');
            let total = 0;
            let count = 0;

            subtotals.forEach(el => {
                total += parseInt(el.dataset.subtotal);
                count++;
            });

            document.getElementById('totalItems').textContent = count + ' item';
            document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Notification Function
        function showNotification(message, type = 'success') {
            const oldNotif = document.querySelector('.custom-notification');
            if (oldNotif) oldNotif.remove();

            const notification = document.createElement('div');
            notification.className = 'custom-notification';
            notification.textContent = message;

            const bgColor = type === 'success' ? '#43a047' : '#ff4b4b';

            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: ${bgColor};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: 600;
                animation: slideIn 0.3s ease;
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Profile Link Handler
        const profileLink = document.getElementById("profileLink");
        const isLoggedIn = localStorage.getItem("isLoggedIn");
        // let isLoggedIn = false;

        profileLink.addEventListener("click", (e) => {
            e.preventDefault();
            if (isLoggedIn === "true") {
                window.location.href = "mobileprofile.php";
            } else {
                window.location.href = "mobileregister.php";
            }
        });

        async function checkLoginStatus() {
            try {
                const res = await fetch("checksession.php");
                const data = await res.json();
                isLoggedIn = data.loggedIn;
            } catch (err) {
                console.error("Gagal cek status login:", err);
            }
        }
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>


    <!-- Popup Konfirmasi Hapus Semua -->
    <div class="confirm-popup-overlay" id="confirmPopup">
        <div class="confirm-popup">
            <div class="confirm-popup-icon">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="confirm-popup-title">Kosongkan Keranjang?</h3>
            <p class="confirm-popup-text">Semua item di keranjang akan dihapus dan tidak dapat dikembalikan.</p>
            <div class="confirm-popup-buttons">
                <button class="confirm-btn-cancel" onclick="closeConfirmPopup()">
                    Batal
                </button>
                <button class="confirm-btn-yes" onclick="confirmClearCart()">
                    Ya, Kosongkan
                </button>
            </div>
        </div>
    </div>
</body>

</html>