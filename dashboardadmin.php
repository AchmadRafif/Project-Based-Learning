<?php
session_start();

// ✅ CEK PHP SESSION DULU (primary authentication)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: loginadmin.php");
    exit;
}

include "config.php";

// ======================
// HAPUS MENU
// ======================
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);

    // Ambil foto lama untuk dihapus
    $result = mysqli_query($conn, "SELECT foto_menu FROM menu WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);

    if ($data && $data['foto_menu'] && file_exists("img/MenuTaki/" . $data['foto_menu'])) {
        unlink("img/MenuTaki/" . $data['foto_menu']);
    }

    mysqli_query($conn, "DELETE FROM menu WHERE id = '$id'");

    header("Location: dashboardadmin.php");
    exit;
}

// ======================
// UPDATE MENU
// ======================
if (isset($_POST['update'])) {
    $id          = mysqli_real_escape_string($conn, $_POST['id']);
    $nama_menu   = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $harga       = mysqli_real_escape_string($conn, $_POST['harga']);
    $stock       = mysqli_real_escape_string($conn, $_POST['stock']);
    $foto_lama   = mysqli_real_escape_string($conn, $_POST['foto_lama']);
    $foto_menu   = $foto_lama;

    // Upload foto baru jika ada
    if (!empty($_FILES['foto_menu']['name'])) {
        // Hapus foto lama
        if ($foto_lama && file_exists("img/MenuTaki/" . $foto_lama)) {
            unlink("img/MenuTaki/" . $foto_lama);
        }

        $foto_menu = time() . "_" . $_FILES['foto_menu']['name'];
        $tujuan = "img/MenuTaki/" . $foto_menu;

        if (!is_dir("img/MenuTaki")) {
            mkdir("img/MenuTaki", 0777, true);
        }

        move_uploaded_file($_FILES['foto_menu']['tmp_name'], $tujuan);
    }

    mysqli_query($conn, "
        UPDATE menu SET 
            nama_menu = '$nama_menu',
            kategori_id = '$kategori_id',
            harga = '$harga',
            stock = '$stock',
            foto_menu = '$foto_menu'
        WHERE id = '$id'
    ");

    header("Location: dashboardadmin.php");
    exit;
}

// ======================
// INSERT MENU BARU
// ======================
if (isset($_POST['tambah'])) {
    $nama_menu   = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $harga       = mysqli_real_escape_string($conn, $_POST['harga']);
    $stock       = mysqli_real_escape_string($conn, $_POST['stock']);
    $foto_menu   = null;

    // Upload foto jika ada
    if (!empty($_FILES['foto_menu']['name'])) {
        $foto_menu = time() . "_" . $_FILES['foto_menu']['name'];
        $tujuan = "img/MenuTaki/" . $foto_menu;

        if (!is_dir("img/MenuTaki")) {
            mkdir("img/MenuTaki", 0777, true);
        }

        move_uploaded_file($_FILES['foto_menu']['tmp_name'], $tujuan);
    }

    mysqli_query($conn, "
        INSERT INTO menu (nama_menu, kategori_id, harga, stock, foto_menu)
        VALUES ('$nama_menu', '$kategori_id', '$harga', '$stock', '$foto_menu')
    ");

    header("Location: dashboardadmin.php");
    exit;
}

// ======================
// AMBIL KATEGORI
// ======================
$kategori = mysqli_query($conn, "SELECT * FROM kategori");

// ======================
// AMBIL MENU LIST
// ======================
$menu = mysqli_query($conn, "
    SELECT menu.*, kategori.nama_kategori 
    FROM menu 
    JOIN kategori ON kategori.id = menu.kategori_id
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="stylesheet" href="dashboardadmin.css">

    <style>
        /* ============================= */
        /* RINCIAN MODAL - CLEAN DESIGN */
        /* ============================= */

        .rincian-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 1rem;
            animation: fadeIn 0.3s ease;
        }

        .rincian-modal {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
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

        /* Header */
        .rincian-header {
            background: #ededed;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            color: white;
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: #222 ;
        }

        .rincian-header h2 {
            color: #222;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Body */
        .rincian-body {
            padding: 1.5rem;
            background-color: #ededed;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 1rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row.full {
            grid-template-columns: 140px 1fr;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
        }

        .info-value {
            color: #222;
            font-weight: 500;
        }

        /* Menu Section */
        .menu-section {
            margin-bottom: 1.5rem;
        }

        .menu-section h3 {
            font-size: 1rem;
            color: #222;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .menu-item {
            display: grid;
            grid-template-columns: 40px 1fr 60px;
            gap: 1rem;
            padding: 0.8rem;
            background: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            align-items: center;
        }

        .menu-number {
            background: #bf0f0f;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .menu-name {
            color: #222;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .level-small {
            background: #ff4b4b;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .menu-qty {
            text-align: right;
            color: #666;
            font-weight: 500;
        }

        /* Payment Section */
        .payment-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .payment-label {
            color: #666;
            font-size: 0.9rem;
        }

        .payment-method {
            color: #222;
            font-weight: 600;
            background: white;
            padding: 0.4rem 1rem;
            border-radius: 8px;
        }

        /* Total Section */
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .total-label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }

        .total-value {
            color: #bf0f0f;
            font-size: 1.3rem;
            font-weight: 700;
        }

        /* Status Update Section */
        .status-update-section {
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .status-update-section label {
            display: block;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .status-select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: "Poppins", sans-serif;
            margin-bottom: 0.8rem;
            background: white;
            cursor: pointer;
        }

        .status-select:focus {
            outline: none;
            border-color: #bf0f0f;
        }

        .btn-update-status {
            width: 100%;
            padding: 0.8rem;
            background: #bf0f0f;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-update-status:hover {
            background: #a00d0d;
            transform: translateY(-2px);
        }

        /* Status Badge di Table */
        .status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status.pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status.proses {
            background: #e3f2fd;
            color: #1976d2;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .empty-state p {
            font-size: 1rem;
            color: #999;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #43a047;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 10001;
            font-weight: 600;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast-notification.error {
            background: #f44336;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rincian-modal {
                max-width: 95%;
                margin: 1rem;
            }

            .rincian-header {
                padding: 1rem;
            }

            .rincian-header h2 {
                font-size: 1.2rem;
            }

            .rincian-body {
                padding: 1rem;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: 0.3rem;
            }

            .info-label {
                font-weight: 600;
            }

            .menu-item {
                grid-template-columns: 35px 1fr 50px;
                gap: 0.8rem;
                padding: 0.6rem;
            }

            .menu-number {
                width: 25px;
                height: 25px;
                font-size: 0.8rem;
            }

            .menu-name {
                font-size: 0.9rem;
            }

            .payment-section,
            .total-section {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .total-value {
                font-size: 1.2rem;
            }
        }

        /* Custom Scrollbar untuk Modal */
        .rincian-modal::-webkit-scrollbar {
            width: 8px;
        }

        .rincian-modal::-webkit-scrollbar-track {
            background: #ededed;
            border-radius: 10px;
        }

        .rincian-modal::-webkit-scrollbar-thumb {
            background: #ededed;
            border-radius: 10px;
        }

        .rincian-modal::-webkit-scrollbar-thumb:hover {
            background: #ededed;
        }
    </style>

</head>

<body>
    <div class="sidebar">
        <div class="logo-section">
            <img src="img/LogoTaki.png" alt="Logo" />
            <div class="title">
                <h2>Dashboard</h2>
                <p>Admin</p>
            </div>
        </div>

        <ul class="menu">
            <li class="active" data-page="pesanan">
                <i class="fa-solid fa-cart-shopping"></i><span>Pesanan</span>
            </li>
            <li data-page="menu">
                <i class="fa-solid fa-box"></i><span>Menu</span>
            </li>
            <li data-page="analisa">
                <i class="fa-solid fa-chart-line"></i><span>Analisa</span>
            </li>
        </ul>

        <div class="bottom-menu">
            <hr />
            <li id="logoutbtn">
                <i class="fa-solid fa-arrow-left"></i><span>Keluar</span>
            </li>
        </div>
    </div>

    <main class="content" id="content-area"></main>

    <div class="logout" id="logoutpopup">
        <div class="popup">
            <h3>Apakah kamu yakin ingin keluar?</h3>
            <div class="popup-buttons">
                <button class="btn-yes" id="confirmlogout">Ya</button>
                <button class="btn-no" id="cancellogout">Tidak</button>
            </div>
        </div>
    </div>

    <!-- Popup Hapus Menu -->
    <div class="logout" id="deletepopup">
        <div class="popup">
            <i class="fa-solid fa-trash" style="font-size: 3rem; color: #c62828; margin-bottom: 1rem;"></i>
            <h3>Hapus Menu</h3>
            <p style="color: #666; margin: 1rem 0;">Yakin ingin menghapus menu <strong id="menuNameDelete"></strong>?</p>
            <p style="color: #999; font-size: 0.85rem;">Data yang dihapus tidak dapat dikembalikan.</p>
            <div class="popup-buttons">
                <button class="btn-yes" id="confirmdelete">Ya, Hapus</button>
                <button class="btn-no" id="canceldelete">Batal</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const navItems = document.querySelectorAll(".menu li");
            const contentArea = document.getElementById("content-area");
            const logoutBtn = document.getElementById("logoutbtn");
            const popup = document.getElementById("logoutpopup");
            let menuToDelete = {
                id: null,
                nama: ''
            };

            const pages = {
                pesanan: `
    <?php
    // Ambil pesanan yang belum selesai (pending & processing)
    $pesananQuery = mysqli_query($conn, "
        SELECT o.*, u.name as customer_name,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.status IN ('pending', 'processing')
        ORDER BY o.created_at DESC
    ");

    $pesananList = [];
    while ($row = mysqli_fetch_assoc($pesananQuery)) {
        $pesananList[] = $row;
    }

    // Statistik
    $today = date('Y-m-d');
    $todayStats = mysqli_query($conn, "
        SELECT COUNT(*) as total_orders
        FROM orders 
        WHERE DATE(created_at) = '$today'
    ");
    $statsData = mysqli_fetch_assoc($todayStats);

    $pendingCount = mysqli_query($conn, "
        SELECT COUNT(*) as count 
        FROM orders 
        WHERE status IN ('pending', 'processing')
    ");
    $pendingData = mysqli_fetch_assoc($pendingCount);
    ?>

    <header>
        <h1>Manajemen Pesanan</h1>
        <p>Kelola dan proses pesanan pelanggan</p>
    </header>

    <section class="headcontent">
        <div class="headtext green">
            <h3>Pesanan Hari Ini</h3>
            <p><?= $statsData['total_orders'] ?></p>
        </div>
        <div class="headtext yellow">
            <h3>Perlu Diproses</h3>
            <p><?= $pendingData['count'] ?></p>
        </div>
    </section>

    <section class="tabledata">
        <h2>Semua Pesanan</h2>

        <?php if (count($pesananList) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Waktu</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($pesananList as $order):
                ?>
                <tr>
                    <td><?= str_pad($no++, 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($order['nama_penerima']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($order['status'] == 'pending'): ?>
                            <span class="status pending">Pending</span>
                        <?php else: ?>
                            <span class="status proses">Proses</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="detail" onclick="showRincian(<?= $order['id'] ?>)">
                            Lihat Rincian
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-clipboard-check"></i>
            <h3>Tidak Ada Pesanan Aktif</h3>
            <p>Semua pesanan sudah diproses</p>
        </div>
        <?php endif; ?>
    </section>
    `,
                menu: `
<header>
    <h1>Tambah Menu Baru</h1>
    <p>Kelola menu makanan dan minuman restoran.</p>
</header>

<?php if (isset($_GET['success'])) { ?>
<div class="alert alert-success">✓ Menu berhasil ditambahkan!</div>
<?php } ?>

<?php if (isset($_GET['updated'])) { ?>
<div class="alert alert-success">✓ Menu berhasil diupdate!</div>
<?php } ?>

<?php if (isset($_GET['deleted'])) { ?>
<div class="alert alert-success">✓ Menu berhasil dihapus!</div>
<?php } ?>

<section class="tambah-menu-container">
  <form class="tambah-menu-form" action="" method="POST" enctype="multipart/form-data" id="menuForm">
    
    <input type="hidden" name="id" id="menuId" value="">
    <input type="hidden" name="foto_lama" id="fotoLama" value="">

    <label>Nama Menu</label>
    <input type="text" name="nama_menu" id="namaMenu" placeholder="Seblak Ceker" required>

    <label>Kategori</label>
    <select name="kategori_id" id="kategoriId" required>
      <option value="">Pilih Kategori</option>
      <?php
        mysqli_data_seek($kategori, 0);
        while ($row = mysqli_fetch_assoc($kategori)) { ?>
        <option value="<?= $row['id'] ?>"><?= $row['nama_kategori'] ?></option>
      <?php } ?>
    </select>

    <label>Harga Menu</label>
    <input type="number" name="harga" id="hargaMenu" placeholder="15000" required>

    <label>Stock</label>
    <input type="number" name="stock" id="stockMenu" placeholder="100" required min="0">

    <label>Foto Menu</label>
    <div class="upload-wrapper">
      <i class="fa-solid fa-plus upload-icon"></i>
      <input type="file" name="foto_menu" id="fotoMenu" accept="image/*">
      <span id="currentFoto" style="font-size: 0.85rem; color: #666;"></span>
    </div>

    <div style="display: flex; gap: 10px;">
      <button type="submit" name="tambah" id="btnTambah">Tambahkan</button>
      <button type="submit" name="update" id="btnUpdate" style="display: none; background: #43a047;">Update Menu</button>
      <button type="button" id="btnBatal" style="display: none; background: #777;">Batal</button>
    </div>

  </form>
</section>

<section class="menu-table-section">
  <header class="tambah-menu-header">
    <h1>Daftar Menu</h1>
  </header>

  <table class="menu-table">
    <thead>
      <tr>
        <th>Gambar</th>
        <th>Nama Menu</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stock</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>

      <?php
        mysqli_data_seek($menu, 0);
        while ($m = mysqli_fetch_assoc($menu)) { ?>
      <tr>
        <td>
          <?php if ($m['foto_menu']) { ?>
            <img src="img/MenuTaki/<?= $m['foto_menu'] ?>" width="70">
          <?php } else { ?>
            <div style="width:50px;height:50px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;">
              <i class="fa-solid fa-image" style="color:#ccc;"></i>
            </div>
          <?php } ?>
        </td>

        <td><?= htmlspecialchars($m['nama_menu']) ?></td>
        <td><?= htmlspecialchars($m['nama_kategori']) ?></td>
        <td>Rp <?= number_format($m['harga'], 0, ',', '.') ?></td>
        <td>
          <span class="badge-stock <?= $m['stock'] < 10 ? 'low' : '' ?>">
            <?= $m['stock'] ?> pcs
          </span>
        </td>

        <td class="aksi">
          <div class="aksi-content">
            <i class="fa-solid fa-pen-to-square edit" 
               data-id="<?= $m['id'] ?>"
               data-nama="<?= htmlspecialchars($m['nama_menu']) ?>"
               data-kategori="<?= $m['kategori_id'] ?>"
               data-harga="<?= $m['harga'] ?>"
               data-stock="<?= $m['stock'] ?>"
               data-foto="<?= $m['foto_menu'] ?>"
               title="Edit Menu"></i>
            
            <i class="fa-solid fa-trash delete" 
               data-id="<?= $m['id'] ?>"
               data-nama="<?= htmlspecialchars($m['nama_menu']) ?>"
               title="Hapus Menu"></i>
          </div>
        </td>
      </tr>
      <?php } ?>

    </tbody>
  </table>
</section>
                `,
                analisa: `
    <?php
    // Query untuk analisis (hanya completed orders)
    $analisaQuery = mysqli_query($conn, "
        SELECT o.*,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
        FROM orders o
        WHERE o.status = 'completed'
        ORDER BY o.created_at DESC
        LIMIT 100
    ");

    $analisaList = [];
    while ($row = mysqli_fetch_assoc($analisaQuery)) {
        $analisaList[] = $row;
    }

    // Statistik MINGGU INI (Terjual Minggu Ini)
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
    $weekSales = mysqli_query($conn, "
        SELECT COUNT(*) as count
        FROM orders 
        WHERE DATE(created_at) BETWEEN '$weekStart' AND '$weekEnd' 
        AND status = 'completed'
    ");
    $weekData = mysqli_fetch_assoc($weekSales);

    // Total PENDAPATAN 1 Minggu Terakhir (7 hari ke belakang dari hari ini)
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
    $today = date('Y-m-d');
    $weekRevenue = mysqli_query($conn, "
        SELECT 
            COUNT(*) as count,
            COALESCE(SUM(total_price), 0) as revenue
        FROM orders 
        WHERE DATE(created_at) BETWEEN '$sevenDaysAgo' AND '$today'
        AND status = 'completed'
    ");
    $revenueData = mysqli_fetch_assoc($weekRevenue);
    ?>

    <header>
        <h1>Analisa Kedai Taki</h1>
        <p>Keseluruhan Pembelian Produk Taki</p>
    </header>

    <section class="headcontent">
        <div class="headtext green">
            <h3>Jumlah Terjual</h3>
            <p><?= $weekData['count'] ?></p>
            <span>Minggu Ini</span>
        </div>
        <div class="headtext yellow">
            <h3>Total Pendapatan</h3>
            <p>Rp <?= number_format($revenueData['revenue'], 0, ',', '.') ?></p>
            <span>1 Minggu Terakhir</span>
        </div>
    </section>

    <section class="tabledata">
        <div class="analisissearch">
            <h2>Riwayat Pesanan</h2>
            <div class="inputbar">
                <input type="text" 
                       id="searchInput"
                       placeholder="Cari no invoice atau nama pelanggan" 
                       class="searchbaranalys" 
                       onkeyup="searchTable()" />
                <input type="date" 
                       id="filterDate"
                       class="searchbaranalys" 
                       onchange="filterByDate()" />
            </div>
        </div>

        <?php if (count($analisaList) > 0): ?>
        <table id="analisaTable">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Waktu Pemesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($analisaList as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['invoice_number']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td><?= htmlspecialchars($order['nama_penerima']) ?></td>
                    <td>
                        <button class="detail" onclick="showRincian(<?= $order['id'] ?>)">
                            Lihat
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-chart-line"></i>
            <h3>Belum Ada Data</h3>
            <p>Belum ada pesanan selesai</p>
        </div>
        <?php endif; ?>
    </section>
    `
            };

            // Event Delegation untuk SEMUA button clicks
            document.addEventListener('click', function(e) {
                // Handle Edit Button
                if (e.target.classList.contains('edit')) {
                    const id = e.target.getAttribute('data-id');
                    const nama = e.target.getAttribute('data-nama');
                    const kategori = e.target.getAttribute('data-kategori');
                    const harga = e.target.getAttribute('data-harga');
                    const stock = e.target.getAttribute('data-stock');
                    const foto = e.target.getAttribute('data-foto');

                    document.getElementById('menuId').value = id;
                    document.getElementById('namaMenu').value = nama;
                    document.getElementById('kategoriId').value = kategori;
                    document.getElementById('hargaMenu').value = harga;
                    document.getElementById('stockMenu').value = stock;
                    document.getElementById('fotoLama').value = foto;

                    if (foto) {
                        document.getElementById('currentFoto').textContent = '📎 ' + foto;
                    }

                    document.getElementById('btnTambah').style.display = 'none';
                    document.getElementById('btnUpdate').style.display = 'block';
                    document.getElementById('btnBatal').style.display = 'block';

                    document.querySelector('.tambah-menu-container').scrollIntoView({
                        behavior: 'smooth'
                    });
                }

                // Handle Delete Button
                if (e.target.classList.contains('delete')) {
                    const id = e.target.getAttribute('data-id');
                    const nama = e.target.getAttribute('data-nama');

                    menuToDelete.id = id;
                    menuToDelete.nama = nama;

                    document.getElementById('menuNameDelete').textContent = nama;
                    document.getElementById('deletepopup').style.display = 'flex';
                }

                // Handle Confirm Delete
                if (e.target.id === 'confirmdelete') {
                    if (menuToDelete.id) {
                        window.location.href = 'dashboardadmin.php?hapus=' + menuToDelete.id;
                    }
                }

                // Handle Cancel Delete
                if (e.target.id === 'canceldelete') {
                    document.getElementById('deletepopup').style.display = 'none';
                    menuToDelete = {
                        id: null,
                        nama: ''
                    };
                }

                // Handle Logout Button
                if (e.target.id === 'confirmlogout') {
                    popup.style.display = "none";

                    // ✅ Hapus localStorage
                    localStorage.removeItem("isAdminLoggedIn");

                    // ✅ Redirect ke logout.php untuk destroy session
                    window.location.href = "logout.php";
                }

                // Handle Cancel Logout
                if (e.target.id === 'cancellogout') {
                    popup.style.display = "none";
                }
            });

            const changePage = (page) => {
                contentArea.classList.add("fade");
                setTimeout(() => {
                    contentArea.innerHTML = pages[page];
                    contentArea.classList.remove("fade");

                    // Re-attach event listeners untuk tombol batal
                    const btnBatal = document.getElementById('btnBatal');
                    if (btnBatal) {
                        btnBatal.addEventListener('click', function() {
                            document.getElementById('menuForm').reset();
                            document.getElementById('menuId').value = '';
                            document.getElementById('currentFoto').textContent = '';

                            document.getElementById('btnTambah').style.display = 'block';
                            document.getElementById('btnUpdate').style.display = 'none';
                            document.getElementById('btnBatal').style.display = 'none';
                        });
                    }

                    // Auto hide alert
                    setTimeout(function() {
                        const alerts = document.querySelectorAll('.alert');
                        alerts.forEach(alert => {
                            alert.style.opacity = '0';
                            alert.style.transition = 'opacity 0.5s';
                            setTimeout(() => alert.remove(), 500);
                        });
                    }, 3000);
                }, 200);
            };

            navItems.forEach((item) => {
                item.addEventListener("click", () => {
                    navItems.forEach((i) => i.classList.remove("active"));
                    item.classList.add("active");
                    changePage(item.dataset.page);
                });
            });

            logoutBtn.addEventListener("click", () => {
                popup.style.display = "flex";
            });

            // Load default page IMMEDIATELY (tanpa setTimeout)
            contentArea.innerHTML = pages.pesanan;
        });
    </script>

    <script>
        // ===========================
        // SHOW RINCIAN MODAL - WITH DEBUG
        // ===========================
        async function showRincian(orderId) {
            console.log('Fetching order detail for ID:', orderId); // Debug log

            // Show loading
            const loadingHTML = `
        <div class="rincian-overlay">
            <div class="rincian-modal" style="text-align: center; padding: 3rem;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 3rem; color: #bf0f0f;"></i>
                <p style="margin-top: 1rem; color: #666;">Memuat data...</p>
            </div>
        </div>
    `;
            document.body.insertAdjacentHTML('beforeend', loadingHTML);
            document.body.style.overflow = 'hidden';

            try {
                const url = `order_handler.php?action=get_order_detail&order_id=${orderId}`;
                console.log('Fetching URL:', url); // Debug log

                const response = await fetch(url);
                console.log('Response status:', response.status); // Debug log

                const text = await response.text();
                console.log('Response text:', text); // Debug log

                let result;
                try {
                    result = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response was:', text);
                    throw new Error('Invalid JSON response from server');
                }

                console.log('Parsed result:', result); // Debug log

                // Remove loading
                document.querySelector('.rincian-overlay').remove();

                if (result.success) {
                    console.log('Order data:', result.order);
                    console.log('Items:', result.items);
                    displayRincianModal(result.order, result.items);
                } else {
                    console.error('Server returned error:', result.message);
                    alert('Gagal memuat detail: ' + (result.message || 'Unknown error'));
                    document.body.style.overflow = '';
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                // Remove loading if exists
                const overlay = document.querySelector('.rincian-overlay');
                if (overlay) overlay.remove();
                document.body.style.overflow = '';

                alert('Terjadi kesalahan: ' + error.message + '\n\nPeriksa console untuk detail.');
            }
        }

        function displayRincianModal(order, items) {
            console.log('Displaying modal for order:', order.id); // Debug log

            // Build items HTML
            let itemsHTML = '';
            items.forEach((item, index) => {
                itemsHTML += `
            <div class="menu-item">
                <div class="menu-number">${index + 1}</div>
                <div class="menu-name">
                    ${escapeHtml(item.nama_menu)}
                    ${item.level !== null ? `<span class="level-small">Lvl.${item.level}</span>` : ''}
                </div>
                <div class="menu-qty">${item.quantity}</div>
            </div>
        `;
            });

            const currentStatus = order.status;
            let statusSelectHTML = '';

            // Only show status select if not completed or cancelled
            if (currentStatus !== 'completed' && currentStatus !== 'cancelled') {
                statusSelectHTML = `
            <div class="status-update-section">
                <label>Perbarui Status :</label>
                <select id="statusSelect" class="status-select">
                    <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="processing" ${currentStatus === 'processing' ? 'selected' : ''}>Proses</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <button class="btn-update-status" onclick="updateOrderStatus(${order.id})">
                    Perbarui Status
                </button>
            </div>
        `;
            }

            const modalHTML = `
        <div class="rincian-overlay" onclick="closeRincian(event)">
            <div class="rincian-modal" onclick="event.stopPropagation()">
                <div class="rincian-header">
                    <h2>Rincian</h2>
                    <button class="close-btn" onclick="closeRincian()">×</button>
                </div>

                <div class="rincian-body">
                    <!-- Info Pesanan -->
                    <div class="info-section">
                        <div class="info-row">
                            <span class="info-label">Nama Pemesan</span>
                            <span class="info-value">${escapeHtml(order.nama_penerima)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Waktu Pembelian</span>
                            <span class="info-value">${formatDateTime(order.created_at)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nomor Telepon</span>
                            <span class="info-value">${escapeHtml(order.no_hp)}</span>
                        </div>
                        <div class="info-row full">
                            <span class="info-label">Alamat Pemesanan</span>
                            <span class="info-value">${escapeHtml(order.alamat_lengkap)}</span>
                        </div>
                        ${order.catatan ? `
                        <div class="info-row full">
                            <span class="info-label">Catatan</span>
                            <span class="info-value">${escapeHtml(order.catatan)}</span>
                        </div>
                        ` : ''}
                    </div>

                    <!-- Menu Yang Dipesan -->
                    <div class="menu-section">
                        <h3>Menu Yang Dipesan :</h3>
                        ${itemsHTML}
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="payment-section">
                        <span class="payment-label">Metode Pembayaran :</span>
                        <span class="payment-method">${escapeHtml(order.payment_method)}</span>
                    </div>

                    <!-- Total -->
                    <div class="total-section">
                        <span class="total-label">Total Harga</span>
                        <span class="total-value">Rp ${Number(order.total_price).toLocaleString('id-ID')}</span>
                    </div>

                    <!-- Status Update -->
                    ${statusSelectHTML}
                </div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);
            document.body.style.overflow = 'hidden';
        }

        function closeRincian(event) {
            if (event && event.target !== event.currentTarget) return;

            const modal = document.querySelector('.rincian-overlay');
            if (modal) {
                modal.remove();
                document.body.style.overflow = '';
            }
        }

        // ===========================
        // UPDATE STATUS - WITH DEBUG
        // ===========================
        async function updateOrderStatus(orderId) {
            const statusSelect = document.getElementById('statusSelect');
            const newStatus = statusSelect.value;

            console.log('Updating order', orderId, 'to status:', newStatus); // Debug log

            if (!confirm(`Yakin ingin mengubah status menjadi "${statusSelect.options[statusSelect.selectedIndex].text}"?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'update_status');
                formData.append('order_id', orderId);
                formData.append('status', newStatus);

                console.log('Sending update request...'); // Debug log

                const response = await fetch('order_handler.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Update response status:', response.status); // Debug log

                const text = await response.text();
                console.log('Update response text:', text); // Debug log

                let result;
                try {
                    result = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response was:', text);
                    throw new Error('Invalid JSON response from server');
                }

                console.log('Update result:', result); // Debug log

                if (result.success) {
                    showToast('✓ Status berhasil diperbarui!', 'success');
                    closeRincian();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('❌ ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Update Error:', error);
                showToast('❌ Gagal update status: ' + error.message, 'error');
            }
        }

        // ===========================
        // UTILITY FUNCTIONS
        // ===========================
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDateTime(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                console.error('Date format error:', e);
                return dateString;
            }
        }

        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>

</html>