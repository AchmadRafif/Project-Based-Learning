<?php
session_start();
require_once "config.php"; // koneksi database

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$query = $conn->prepare("SELECT name, email, phone, password FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Ambil riwayat pesanan user
$orderQuery = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items,
           SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orderQuery->bind_param("i", $user_id);
$orderQuery->execute();
$orders = $orderQuery->get_result();

// Update nama
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_name"])) {
    $newName = trim($_POST["new_name"]);
    if (!empty($newName)) {
        $update = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $update->bind_param("si", $newName, $user_id);
        $update->execute();
        $_SESSION['update_success'] = "Nama berhasil diperbarui!";
        header("Location: profile.php");
        exit();
    }
}

// Hapus akun
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_account"])) {
    $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete->bind_param("i", $user_id);
    $delete->execute();

    session_destroy();
    header("Location: register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile - Taki ID</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
    <style>
        :root {
            --redcolor: #bf0f0f;
            --blacknav: #383838;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #ffffff;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            position: relative;
        }

        .backbtn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            color: var(--redcolor);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            position: absolute;
            top: 0;
            left: 0;
            transition: 0.2s;
        }

        .backbtn:hover {
            color: #a50e0e;
            transform: translateX(-3px);
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 600;
            text-align: center;
        }

        .profile-banner {
            background-color: var(--redcolor);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin: 60px 0 20px;
            text-align: center;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .profile-content {
            display: flex;
            gap: 30px;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 250px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            background: #eee;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
        }

        .sidebar-item.active {
            background: #111;
            color: #fff;
        }

        .sidebar-item:hover {
            background: #ddd;
        }

        .main-profile {
            flex: 1;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-row h2 {
            font-size: 1.2rem;
        }

        .icons .icon-btn {
            cursor: pointer;
            margin-left: 10px;
            color: #333;
        }

        .icon-btn.delete {
            color: #c8102e;
        }

        .profile-form {
            margin-top: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .form-group span {
            color: #c8102e;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #e6e6e6;
            font-size: 0.95rem;
        }

        .phone-input {
            display: flex;
            align-items: center;
            background: #e6e6e6;
            border-radius: 8px;
            padding: 0 10px;
        }

        .flag {
            margin-right: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .popupoverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            visibility: hidden;
            opacity: 0;
            transition: 0.3s ease;
        }

        .popupoverlay.active {
            visibility: visible;
            opacity: 1;
        }

        .popupbox {
            background: #fff;
            border-radius: 14px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: fadeIn 0.25s ease;
        }

        .popupbox h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #222;
            margin-bottom: 20px;
        }

        .popup-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .popup-buttons .btn-yes,
        .popup-buttons .btn-no {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-yes {
            min-width: 95px;
            background: #c8102e;
            color: #fff;
        }

        .btn-yes:hover {
            background: #a90e25;
        }

        .btn-no {
            min-width: 95px;
            background: #ddd;
            color: #333;
        }

        .btn-no:hover {
            background: #bbb;
        }

        .save-btn {
            background: var(--redcolor);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            display: none;
        }

        .save-btn:hover {
            background: #a50e0e;
        }

        @keyframes fadeIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-message {
            background: #e6ffe6;
            color: #0a8a0a;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        /* Order History Styles */
        .order-history-section {
            display: none;
        }

        .order-history-section.active {
            display: block;
        }

        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: 0.2s;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-invoice {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
        }

        .order-date {
            font-size: 0.85rem;
            color: #666;
        }

        .order-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .order-body {
            margin-bottom: 15px;
        }

        .order-info {
            display: flex;
            gap: 30px;
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 10px;
        }

        .order-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .order-total {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--redcolor);
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-detail {
            padding: 8px 16px;
            background: #fff;
            border: 1px solid var(--redcolor);
            color: var(--redcolor);
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-detail:hover {
            background: var(--redcolor);
            color: #fff;
        }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-orders i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-orders h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #666;
        }

        .empty-orders p {
            font-size: 0.95rem;
        }

        /* Profile Section */
        .profile-info-section {
            display: none;
        }

        .profile-info-section.active {
            display: block;
        }

        /* Detail Order Popup */
        .order-detail-popup {
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            text-align: left;
        }

        .order-detail-header {
            margin-bottom: 20px;
        }

        .order-detail-header h3 {
            font-size: 1.3rem;
            color: #222;
            margin-bottom: 5px;
        }

        .order-detail-section {
            margin-bottom: 20px;
        }

        .order-detail-section h4 {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .order-detail-section p {
            font-size: 0.9rem;
            color: #333;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        .order-items-list {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 0.95rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }

        .item-qty {
            font-size: 0.85rem;
            color: #666;
        }

        .item-price {
            font-size: 0.95rem;
            font-weight: 500;
            color: #333;
        }

        .order-total-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--redcolor);
        }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>
    <div class="container">
        <button class="backbtn" onclick="window.location.href='index.php'">
            <i data-feather="arrow-left"></i> Kembali
        </button>

        <h1 class="page-title">Profile</h1>

        <div class="profile-header">
            <div class="profile-banner">
                <div id="profileName" class="profile-name"><?= htmlspecialchars($user['name']); ?></div>
            </div>
        </div>

        <div class="profile-content">
            <div class="sidebar">
                <button class="sidebar-item active" data-section="profile"><i data-feather="user"></i> Profile Information</button>
                <button class="sidebar-item" data-section="orders"><i data-feather="shopping-bag"></i> Riwayat Pesanan</button>
                <button id="logoutBtn" class="sidebar-item"><i data-feather="log-out"></i> Log Out</button>
            </div>

            <div class="main-profile">
                <!-- Profile Information Section -->
                <div class="profile-info-section active" id="profileSection">
                    <div class="header-row">
                        <h2>My Profile</h2>
                        <div class="icons">
                            <i data-feather="edit-2" id="editBtn" class="icon-btn"></i>
                            <i data-feather="trash-2" id="deleteBtn" class="icon-btn delete"></i>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['update_success'])): ?>
                        <div class="success-message"><?= $_SESSION['update_success'];
                                                        unset($_SESSION['update_success']); ?></div>
                    <?php endif; ?>

                    <form class="profile-form" method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap <span>*</span></label>
                            <input type="text" name="new_name" id="nameField" value="<?= htmlspecialchars($user['name']); ?>" readonly />
                        </div>

                        <div class="form-group">
                            <label>Email <span>*</span></label>
                            <input type="email" readonly value="<?= htmlspecialchars($user['email']); ?>" />
                        </div>

                        <div class="form-group">
                            <label>Nomor Telepon <span>*</span></label>
                            <div class="phone-input">
                                <span class="flag">🇮🇩 +62</span>
                                <input type="text" readonly value="<?= htmlspecialchars($user['phone']); ?>" />
                            </div>
                        </div>

                        <button type="submit" name="update_name" class="save-btn" id="saveBtn">Simpan Perubahan</button>
                    </form>
                </div>

                <!-- Order History Section -->
                <div class="order-history-section" id="ordersSection">
                    <div class="header-row">
                        <h2>Riwayat Pesanan</h2>
                    </div>

                    <div style="margin-top: 20px;">
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div>
                                            <div class="order-invoice"><?= htmlspecialchars($order['invoice_number']); ?></div>
                                            <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])); ?></div>
                                        </div>
                                        <span class="order-status status-<?= $order['status']; ?>">
                                            <?= ucfirst($order['status']); ?>
                                        </span>
                                    </div>

                                    <div class="order-body">
                                        <div class="order-info">
                                            <div class="order-info-item">
                                                <i data-feather="package" style="width: 16px; height: 16px;"></i>
                                                <span><?= $order['total_items']; ?> Item (<?= $order['total_quantity']; ?> Produk)</span>
                                            </div>
                                            <div class="order-info-item">
                                                <i data-feather="credit-card" style="width: 16px; height: 16px;"></i>
                                                <span><?= htmlspecialchars($order['payment_method']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="order-footer">
                                        <div class="order-total">
                                            Total: Rp <?= number_format($order['total_price'], 0, ',', '.'); ?>
                                        </div>
                                        <div class="order-actions">
                                            <button class="btn-detail" onclick="showOrderDetail(<?= $order['id']; ?>)">
                                                Lihat Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-orders">
                                <i data-feather="shopping-bag"></i>
                                <h3>Belum Ada Pesanan</h3>
                                <p>Anda belum memiliki riwayat pesanan. Mulai belanja sekarang!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Logout -->
    <div id="logoutPopup" class="popupoverlay">
        <div class="popupbox">
            <h3>Apakah kamu yakin ingin keluar?</h3>
            <div class="popup-buttons">
                <button id="confirmLogout" class="btn-yes">Ya</button>
                <button id="cancelLogout" class="btn-no">Tidak</button>
            </div>
        </div>
    </div>

    <!-- Popup Hapus Akun -->
    <div id="deletePopup" class="popupoverlay">
        <div class="popupbox">
            <h3>Apakah kamu yakin ingin menghapus akun ini?</h3>
            <form method="POST" class="popup-buttons">
                <button type="submit" name="delete_account" class="btn-yes">Ya</button>
                <button type="button" id="cancelDelete" class="btn-no">Tidak</button>
            </form>
        </div>
    </div>

    <!-- Popup Detail Order -->
    <div id="orderDetailPopup" class="popupoverlay">
        <div class="popupbox order-detail-popup" id="orderDetailContent">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>

    <script>
        feather.replace();

        // Toggle between sections
        const sidebarItems = document.querySelectorAll('.sidebar-item[data-section]');
        const profileSection = document.getElementById('profileSection');
        const ordersSection = document.getElementById('ordersSection');

        sidebarItems.forEach(item => {
            item.addEventListener('click', () => {
                const section = item.getAttribute('data-section');
                
                // Remove active class from all items
                sidebarItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                // Hide all sections
                profileSection.classList.remove('active');
                ordersSection.classList.remove('active');

                // Show selected section
                if (section === 'profile') {
                    profileSection.classList.add('active');
                } else if (section === 'orders') {
                    ordersSection.classList.add('active');
                }

                // Replace icons after section change
                setTimeout(() => feather.replace(), 50);
            });
        });

        // Edit profile
        const editBtn = document.getElementById("editBtn");
        const nameField = document.getElementById("nameField");
        const saveBtn = document.getElementById("saveBtn");

        editBtn.addEventListener("click", () => {
            nameField.removeAttribute("readonly");
            nameField.focus();
            nameField.style.background = "#fff";
            saveBtn.style.display = "inline-block";
        });

        // Logout popup
        const logoutBtn = document.getElementById("logoutBtn");
        const logoutPopup = document.getElementById("logoutPopup");
        const confirmLogout = document.getElementById("confirmLogout");
        const cancelLogout = document.getElementById("cancelLogout");

        logoutBtn.addEventListener("click", () => logoutPopup.classList.add("active"));
        cancelLogout.addEventListener("click", () => logoutPopup.classList.remove("active"));
        confirmLogout.addEventListener("click", () => {
            window.location.href = "logout.php";
        });

        // Delete account popup
        const deleteBtn = document.getElementById("deleteBtn");
        const deletePopup = document.getElementById("deletePopup");
        const cancelDelete = document.getElementById("cancelDelete");

        deleteBtn.addEventListener("click", () => deletePopup.classList.add("active"));
        cancelDelete.addEventListener("click", () => deletePopup.classList.remove("active"));

        // Show order detail
        function showOrderDetail(orderId) {
            const popup = document.getElementById('orderDetailPopup');
            const content = document.getElementById('orderDetailContent');
            
            // Show loading
            content.innerHTML = '<p style="text-align: center; padding: 20px;">Loading...</p>';
            popup.classList.add('active');
            
            // Fetch order detail via AJAX
            fetch('get_order_detail.php?id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    feather.replace();
                })
                .catch(error => {
                    content.innerHTML = '<p style="text-align: center; padding: 20px; color: red;">Error loading order detail</p>';
                });
        }

        // Close order detail popup
        document.getElementById('orderDetailPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    </script>
</body>

</html>