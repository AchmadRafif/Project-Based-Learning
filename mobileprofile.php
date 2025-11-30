<?php
session_start();
include "config.php";

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Hitung jumlah pesanan
$orderStmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
mysqli_stmt_bind_param($orderStmt, "i", $user_id);
mysqli_stmt_execute($orderStmt);
$orderCountResult = mysqli_stmt_get_result($orderStmt);
$orderCount = mysqli_fetch_assoc($orderCountResult)['total'];

// Handle Update Profile - HANYA NAMA YANG BISA DIUBAH
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['name']));

    // Validasi nama tidak kosong
    if (empty($nama)) {
        $error = "Nama tidak boleh kosong!";
    } else {
        // Update hanya nama
        $updateStmt = mysqli_prepare($conn, "UPDATE users SET name = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $nama, $user_id);

        if (mysqli_stmt_execute($updateStmt)) {
            $success = "Profil berhasil diperbarui!";
            // Refresh data
            $refreshStmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
            mysqli_stmt_bind_param($refreshStmt, "i", $user_id);
            mysqli_stmt_execute($refreshStmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($refreshStmt));
        } else {
            $error = "Gagal memperbarui profil!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Saya - Taki ID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />

    <style>
        :root {
            --redcolor: #bf0f0f;
            --yellow: #f49a24;
            --green: #89c946;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: #f5f5f5;
            padding-bottom: 80px;
        }

        .profile-header {
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .profile-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: white;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: #222;
        }

        .profile-email {
            font-size: 0.9rem;
            opacity: 0.95;
            color: #222;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
        }

        .stat-label {
            font-size: 0.75rem;
            opacity: 0.8;
            color: #222;
        }

        .section {
            background: white;
            margin: 1rem;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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

        /* COLLAPSIBLE HEADER */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 0.5rem;
            margin: -0.5rem -0.5rem 0 -0.5rem;
            border-radius: 8px;
            transition: background 0.3s;
            user-select: none;
        }

        .section-header:hover {
            background: #f5f5f5;
        }

        .collapse-icon {
            font-size: 1.2rem;
            transition: transform 0.3s;
            color: var(--redcolor);
        }

        .collapse-icon.collapsed {
            transform: rotate(-90deg);
        }

        .section-content {
            margin-top: 10px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            opacity: 1;
        }

        .section-content.collapsed {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--redcolor);
        }

        .form-group input[readonly] {
            background: #f5f5f5;
            cursor: not-allowed;
            color: #666;
        }

        .edit-icon {
            position: absolute;
            right: 1rem;
            top: 1rem;
            width: 40px;
            height: 40px;
            background: #fff;
            color: var(--redcolor);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1.2rem;
        }

        .edit-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn {
            width: 100%;
            padding: 0.9rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--redcolor);
            color: white;
        }

        .btn-primary:hover {
            background: #a00d0d;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #e0e0e0;
            margin-top: 0.5rem;
        }

        .btn-secondary:hover {
            border-color: var(--redcolor);
            color: var(--redcolor);
        }

        .btn-danger {
            background: var(--redcolor);
            color: white;
            margin-top: 1rem;
        }

        .btn-danger:hover {
            background: #8a0b0b;
            transform: translateY(-2px);
        }

        .order-item {
            padding: 1rem;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .order-item:hover {
            border-color: var(--redcolor);
            background: #fff5f5;
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .order-number {
            font-weight: 700;
            color: var(--redcolor);
        }

        .order-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-details {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .order-total {
            font-weight: 700;
            color: #222;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .bottomnav {
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.08);
            z-index: 9999;
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
            padding-bottom: env(safe-area-inset-bottom);
            /* iPhone notch safe area */
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

        #editForm {
            display: none;
        }

        #editForm.active {
            display: block;
        }

        #viewMode.hidden {
            display: none;
        }

        .section.relative {
            position: relative;
        }

        /* Modal/Popup Styles */
        .popupoverlay {
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
            padding: 1rem;
        }

        .popupoverlay.active {
            display: flex;
        }

        .popupbox {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-height: 90vh;
            overflow-y: auto;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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

        .logout-modal {
            text-align: center;
        }

        .logout-modal h3 {
            font-size: 1.3rem;
            color: #222;
            margin-bottom: 1rem;
        }

        .logout-modal i {
            font-size: 3rem;
            color: var(--yellow);
            margin-bottom: 1rem;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-btn {
            flex: 1;
            padding: 0.8rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-btn-yes {
            background: var(--redcolor);
            color: white;
        }

        .modal-btn-yes:hover {
            background: #a00d0d;
        }

        .modal-btn-no {
            background: #e0e0e0;
            color: #333;
        }

        .modal-btn-no:hover {
            background: #d0d0d0;
        }
    </style>
</head>

<body>
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-logo">
            <img src="img/LogoTaki.png" alt="Taki Logo" />
        </div>
        <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?= $orderCount ?></div>
                <div class="stat-label">Pesanan</div>
            </div>
        </div>
    </div>

    <!-- Data Profil Section -->
    <div class="section relative">
        <div class="section-title">
            <i class="fa-solid fa-user"></i> Data Profil
        </div>

        <button class="edit-icon" onclick="toggleEditMode()">
            <i class="fa-solid fa-pen" id="editIcon"></i>
        </button>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- View Mode -->
        <div id="viewMode">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" value="<?= htmlspecialchars($user['name'] ?? '-') ?>" readonly />
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly />
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" value="<?= htmlspecialchars($user['phone'] ?? '-') ?>" readonly />
            </div>
        </div>

        <!-- Edit Mode -->
        <form method="POST" id="editForm">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required autofocus />
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly />
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" value="<?= htmlspecialchars($user['phone'] ?? '-') ?>" readonly />
            </div>

            <button type="submit" name="update_profile" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
            <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">
                <i class="fa-solid fa-times"></i> Batal
            </button>
        </form>
    </div>

    <!-- Riwayat Pesanan Section (COLLAPSIBLE) -->
    <div class="section">
        <!-- COLLAPSIBLE HEADER -->
        <div class="section-header" onclick="toggleOrderSection()">
            <div class="section-title" style="margin-bottom: 0; cursor: pointer;">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pesanan (<?= $orderCount ?>)
            </div>
            <i class="fa-solid fa-chevron-down collapse-icon" id="collapseIcon"></i>
        </div>

        <!-- COLLAPSIBLE CONTENT -->
        <div class="section-content" id="orderContent">
            <?php
            $ordersStmt = mysqli_prepare($conn, "
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            mysqli_stmt_bind_param($ordersStmt, "i", $user_id);
            mysqli_stmt_execute($ordersStmt);
            $ordersQuery = mysqli_stmt_get_result($ordersStmt);

            if (mysqli_num_rows($ordersQuery) > 0):
                while ($order = mysqli_fetch_assoc($ordersQuery)):
            ?>
                    <div class="order-item" onclick="showOrderDetail(<?= (int)$order['id'] ?>)">
                        <div class="order-header">
                            <span class="order-number"><?= htmlspecialchars($order['invoice_number']) ?></span>
                            <span class="order-status status-<?= $order['status'] ?>">
                                <?php
                                $statusText = [
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                echo $statusText[$order['status']] ?? 'Unknown';
                                ?>
                            </span>
                        </div>
                        <div class="order-details">
                            <div><i class="fa-regular fa-calendar"></i> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                            <div><i class="fa-solid fa-money-bill-wave"></i> <?= htmlspecialchars($order['payment_method']) ?></div>
                        </div>
                        <div class="order-total">
                            Rp <?= number_format($order['total_price'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="empty-state">
                    <i class="fa-solid fa-receipt"></i>
                    <p>Belum ada riwayat pesanan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="section">
        <button class="btn btn-danger" onclick="openLogoutConfirm()">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
        </button>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottomnav">
        <a href="index.php"><i class="fa-solid fa-house"></i>Home</a>
        <a href="menu.php"><i class="fa-solid fa-book-open"></i>Menu</a>
        <a href="cart.php"><i class="fa-solid fa-shopping-cart"></i>Pesanan</a>
        <a href="#" id="profileLink" class="active"><i class="fa-solid fa-user"></i>Profil</a>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="popupoverlay" id="logoutModal">
        <div class="popupbox logout-modal">
            <i class="fa-solid fa-sign-out-alt"></i>
            <h3>Yakin ingin keluar?</h3>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-yes" onclick="confirmLogout()">Ya, Keluar</button>
                <button class="modal-btn modal-btn-no" onclick="closeLogoutConfirm()">Batal</button>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="popupoverlay" id="orderDetailPopup">
        <div class="popupbox" id="orderDetailContent">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>

    <script>
        // Toggle Edit Mode
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editForm = document.getElementById('editForm');
            const editIcon = document.getElementById('editIcon');

            if (editForm.classList.contains('active')) {
                editForm.classList.remove('active');
                viewMode.classList.remove('hidden');
                editIcon.className = 'fa-solid fa-pen';
            } else {
                editForm.classList.add('active');
                viewMode.classList.add('hidden');
                editIcon.className = 'fa-solid fa-times';
            }
        }

        // Toggle Order Section (COLLAPSE/EXPAND)
        function toggleOrderSection() {
            const orderContent = document.getElementById('orderContent');
            const collapseIcon = document.getElementById('collapseIcon');

            orderContent.classList.toggle('collapsed');
            collapseIcon.classList.toggle('collapsed');
        }

        // Logout Functions
        function openLogoutConfirm() {
            document.getElementById('logoutModal').classList.add('active');
        }

        function closeLogoutConfirm() {
            document.getElementById('logoutModal').classList.remove('active');
        }

        function confirmLogout() {
            window.location.href = 'logout.php';
        }

        // Auto hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);

        // Show order detail
        function showOrderDetail(orderId) {
            const popup = document.getElementById('orderDetailPopup');
            const content = document.getElementById('orderDetailContent');

            // Show loading
            content.innerHTML = '<p style="text-align: center; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</p>';
            popup.classList.add('active');

            // Fetch order detail via AJAX
            fetch('get_order_detail.php?order_id=' + orderId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load order detail');
                    }
                    return response.text();
                })
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<p style="text-align: center; padding: 20px; color: #c62828;"><i class="fa-solid fa-exclamation-triangle"></i> Gagal memuat detail pesanan</p>';
                });
        }

        // Close order detail popup
        document.getElementById('orderDetailPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });

        // Close logout modal when clicking outside
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutConfirm();
            }
        });
    </script>
</body>

</html>