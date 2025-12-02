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
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />

    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="stylefooter.css">
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

    <!-- Footer Start -->
    <footer class="footer">
        <div class="footercontainer">
            <!-- Kolom 1 -->
            <div class="footerleft">
                <a href="#" class="logo"><img src="img/LogoTaki.png" alt="Kedai TaKi ID" /></a>
                <p>
                    <strong>Jam Operasional :</strong><br />
                    Setiap hari<br />
                    14:00 - 22:00
                </p>
                <div class="socialicons">
                    <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Kolom 2 -->
            <div class="footercolumn">
                <h3>Navigasi</h3>
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="menu.html">Menu</a></li>
                    <li><a href="story.html">Tentang Kami</a></li>
                </ul>
            </div>

            <!-- Kolom 3 -->
            <div class="footercolumn">
                <h3>Lokasi Outlet</h3>

                <!-- Map responsive -->
                <div
                    class="footermap-container"
                    style="
              position: relative;
              width: 100%;
              padding-bottom: 56.25%;
              height: 0;
              overflow: hidden;
              border-radius: 8px;
              margin-bottom: 10px;
            ">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3256.777651913909!2d112.79764207400173!3d-7.285904092721404!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fb57d44c77f5%3A0x2585e1deb7a1c3b1!2sSeblak%20Prasmanan%20Taki%20Id!5e1!3m2!1sen!2sid!4v1762998867292!5m2!1sen!2sid"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        style="
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border: 0;
              ">
                    </iframe>
                </div>

                <p style="color: #ededed; font-size: 0.9rem; line-height: 1.5">
                    Kejawan Gebang III No.22,<br />
                    RT.002/RW.04, Gebang Putih, Kec. Sukolilo,<br />
                    Surabaya, Jawa Timur 60117
                </p>
            </div>
        </div>

        <div class="footerbottom">© Copyright 2025, All Right Reserved</div>
    </footer>
    <!-- Footer End -->

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