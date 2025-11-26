<?php include "config.php";


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Menu - Taki ID</title>
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
            background-color: #fff;
            font-family: "Poppins", sans-serif;
            color: #222;
        }

        /* ===== Tombol Ke Beranda ===== */
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

        header {
            text-align: center;
            padding: 4rem 0 2rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #222;
            margin: 0;
        }

        /* ===== Search Bar ===== */
        .searchbar {
            display: flex;
            justify-content: center;
            margin: 1.5rem auto 3rem;
            max-width: 700px;
            padding: 0 2rem;
        }

        .searchbar input {
            width: 80%;
            padding: 0.8rem 1.2rem;
            border: 1px solid #ccc;
            border-radius: 25px 0 0 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .searchbar input:focus {
            border-color: var(--redcolor);
        }

        .searchbar button {
            background-color: var(--redcolor);
            color: white;
            border: none;
            border-radius: 0 25px 25px 0;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .searchbar button:hover {
            background-color: #c80000;
        }

        /* MenuSection */
        .menusection {
            margin: 0 auto;
            padding: 1rem 3rem;
            max-width: 1300px;
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 3rem 0 1.5rem;
            text-align: left;
            color: #222;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--redcolor);
            border-radius: 2px;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .menu-count {
            background-color: #ff4b4b;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.3rem 0.8rem;
            border-radius: 10px;
        }

        /* MenuGrid */
        .menugrid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .menucard {
            background-color: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.25s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }

        .menucard:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .menucard-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .menucard-content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .menucard h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #222;
            margin: 0 0 0.5rem;
            line-height: 1.3;
        }

        .menucard-price {
            color: var(--redcolor);
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0.3rem 0;
        }

        .menucard-stock {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .menucard-stock.low {
            color: #ff4b4b;
            font-weight: 600;
        }

        .menucard button {
            background-color: var(--redcolor);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .menucard button:hover {
            background-color: #8f1313ff;
        }

        .menucard button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .empty-category {
            text-align: center;
            padding: 3rem 0;
            color: #999;
            font-size: 1rem;
        }

        .empty-category i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .no-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 3rem;
        }

        /* Bottom Navigation */
        .bottomnav {
            display: none;
        }

        /* ============================= */
        /* 🌶️ POPUP LEVEL PEDAS */
        /* ============================= */
        .level-popup-overlay {
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
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .level-popup-overlay.active {
            display: flex;
        }

        .level-popup {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            max-width: 420px;
            width: 90%;
            max-height: 85vh;
            /* Batasi tinggi maksimal */
            overflow-y: auto;
            /* Scroll jika konten terlalu panjang */
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideUp 0.3s ease;
        }

        .level-popup-header {
            text-align: center;
            margin-bottom: 1rem;
            /* Dikurangi dari 1.5rem */
        }

        .level-popup-icon {
            font-size: 2.5rem;
            /* Dikurangi dari 3rem */
            margin-bottom: 0.3rem;
            /* Dikurangi dari 0.5rem */
        }

        .level-popup-title {
            font-size: 1.3rem;
            /* Dikurangi dari 1.5rem */
            font-weight: 700;
            color: #222;
            margin-bottom: 0.2rem;
            /* Dikurangi dari 0.3rem */
        }

        .level-popup-subtitle {
            color: #666;
            font-size: 0.9rem;
            /* Dikurangi dari 0.95rem */
        }

        .level-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.7rem;
            margin-bottom: 1rem;
        }

        .level-option {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            padding: 0.7rem 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            position: relative;
        }

        .level-option:hover {
            border-color: var(--redcolor);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(191, 15, 15, 0.2);
        }

        .level-option.selected {
            border-color: var(--redcolor);
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
        }

        .level-option.selected::after {
            content: '✓';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--greencolor);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .level-option-icon {
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
            display: block;
        }

        .level-option-name {
            font-weight: 600;
            color: #222;
            margin-bottom: 0.1rem;
            font-size: 0.85rem;
        }

        .level-option-desc {
            font-size: 0.7rem;
            color: #999;
        }

        .level-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
        }

        .level-option.disabled:hover {
            border-color: #e0e0e0;
            transform: none;
            box-shadow: none;
        }

        .level-popup-footer {
            display: flex;
            gap: 0.8rem;
        }

        .level-btn {
            flex: 1;
            padding: 0.75rem;
            /* Dikurangi dari 0.9rem */
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            /* Dikurangi dari 1rem */
        }


        .level-btn-cancel {
            background: white;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .level-btn-cancel:hover {
            border-color: var(--redcolor);
            color: var(--redcolor);
        }

        .level-btn-confirm {
            background: var(--redcolor);
            color: white;
        }

        .level-btn-confirm:hover {
            background: #8f1313ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(137, 201, 70, 0.3);
        }

        .level-btn-confirm:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
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

        /* ============================= */
        /* 📱 Responsive Mobile */
        /* ============================= */
        @media (max-width: 768px) {
            .backbtn {
                display: none;
            }

            header {
                padding: 5rem 0 1.5rem;
            }

            h1 {
                font-size: 1.6rem;
            }

            .searchbar {
                padding: 0 1rem;
                margin: 1rem auto 2rem;
            }

            .menusection {
                padding: 0.5rem 1rem;
                margin-bottom: 5rem;
            }

            h2 {
                font-size: 1.4rem;
                margin: 2rem 0 1rem;
            }

            .menugrid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 1rem;
            }

            .menucard-image,
            .no-image {
                height: 140px;
            }

            .menucard-content {
                padding: 0.8rem;
            }

            .menucard h3 {
                font-size: 0.9rem;
            }

            .menucard-price {
                font-size: 1rem;
            }

            .menucard button {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            /* Bottom Navigation */
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
                padding-bottom: env(safe-area-inset-bottom);
            }

            .bottomnav a {
                flex: 1;
                text-align: center;
                color: #777;
                font-size: 0.75rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                gap: 4px;
                transition: color 0.3s;
            }

            .bottomnav a i {
                font-size: 1.4rem;
            }

            .bottomnav a.active {
                color: var(--redcolor);
                font-weight: 600;
            }
        }

        /* ============================= */
        /* 💻 Tablet */
        /* ============================= */
        @media (min-width: 769px) and (max-width: 1024px) {
            .menusection {
                padding: 1rem 2rem;
            }

            .menugrid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        /* Footer */
      .footer {
        margin-top: 20vh;
        padding: 8rem 7% 30px;
        background-color: var(--blacknav);
        color: #fff;
        font-family: "Poppins", sans-serif;
      }

      .footercontainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 40px;
        max-width: 1300px;
        margin: 0 auto;
      }

      /* Kiri */
      .footerleft {
        flex: 1 1 250px;
        max-width: 400px;
      }

      .footerleft .logo {
        background: linear-gradient(90deg, var(--redcolor), var(--yellow));
        background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .footerleft .logo img {
        width: 60px;
      }

      .footerleft p {
        margin: 15px 0;
        color: #d4d4d4;
        line-height: 1.5;
      }

      .socialicons {
        display: flex;
        gap: 18px;
        margin-top: 10px;
      }

      .socialicons a {
        color: #ededed;
        font-size: 1.3rem;
        transition: color 0.3s ease;
      }

      .socialicons a:hover {
        color: var(--redcolor);
      }

      /* Kolom kanan */
      .footercolumn {
        flex: 1 1 200px;
        min-width: 160px;
      }

      .footercolumn h3 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-transform: uppercase;
        color: #d4d4d4;
      }

      .footercolumn ul {
        list-style: none;
        padding: 0;
        margin: 0;
      }

      .footercolumn ul li {
        margin-bottom: 10px;
      }

      .footercolumn ul li a {
        text-decoration: none;
        color: #ededed;
        transition: color 0.3s;
      }

      .footercolumn ul li a:hover {
        color: var(--redcolor);
      }

      .footerbottom {
        text-align: center;
        font-size: 0.9rem;
        color: #ededed;
        margin-top: 40px;
        padding-top: 20px;
      }
    </style>
</head>

<body>
    <a href="index.php#menufav" class="backbtn">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <header>
        <h1>Menu Kami</h1>
    </header>

    <!-- Search -->
    <div class="searchbar">
        <input type="text" id="searchInput" placeholder="Telusuri menu yang anda cari..." />
        <button onclick="searchMenu()">
            <i class="fa-solid fa-search"></i>
        </button>
    </div>

    <?php
    // Ambil semua kategori
    $sqlKategori = "SELECT * FROM kategori ORDER BY id ASC";
    $resultKategori = mysqli_query($conn, $sqlKategori);

    if (!$resultKategori) {
        die("Query Error: " . mysqli_error($conn));
    }

    // Loop setiap kategori
    while ($kategori = mysqli_fetch_assoc($resultKategori)) :
        $kategoriId = $kategori['id'];
        $kategoriNama = $kategori['nama_kategori'];
        $kategoriSlug = strtolower(trim($kategoriNama)); // Buat slug untuk JS

        // Hitung jumlah menu per kategori
        $sqlCount = "SELECT COUNT(*) as total FROM menu WHERE kategori_id = $kategoriId";
        $resultCount = mysqli_query($conn, $sqlCount);
        $count = mysqli_fetch_assoc($resultCount)['total'];
    ?>

        <!-- SECTION TIAP KATEGORI -->
        <section class="menusection" id="kategori-<?= $kategoriId ?>">
            <div class="category-header">
                <h2><?= htmlspecialchars($kategoriNama) ?></h2>
                <span class="menu-count"><?= $count ?> Menu</span>
            </div>

            <div class="menugrid">
                <?php
                // Ambil menu berdasarkan kategori
                $sqlMenu = "SELECT * FROM menu WHERE kategori_id = $kategoriId ORDER BY id DESC";
                $resultMenu = mysqli_query($conn, $sqlMenu);

                if (mysqli_num_rows($resultMenu) > 0) :
                    while ($menu = mysqli_fetch_assoc($resultMenu)) :
                        $stock = $menu['stock'];
                        $outOfStock = $stock <= 0;
                        $lowStock = $stock > 0 && $stock < 10;
                ?>

                        <div class="menucard" data-name="<?= strtolower($menu['nama_menu']) ?>" data-category="<?= strtolower($kategoriNama) ?>">
                            <?php if (!empty($menu['foto_menu']) && file_exists("img/MenuTaki/" . $menu['foto_menu'])): ?>
                                <img src="img/MenuTaki/<?= htmlspecialchars($menu['foto_menu']) ?>"
                                    alt="<?= htmlspecialchars($menu['nama_menu']) ?>"
                                    class="menucard-image" />
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                            <?php endif; ?>

                            <div class="menucard-content">
                                <h3><?= htmlspecialchars($menu['nama_menu']) ?></h3>
                                <p class="menucard-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></p>

                                <?php if ($outOfStock): ?>
                                    <p class="menucard-stock low">Stok Habis</p>
                                    <button disabled>
                                        <i class="fa-solid fa-ban"></i> Habis
                                    </button>
                                <?php elseif ($lowStock): ?>
                                    <p class="menucard-stock low">Stok Terbatas: <?= $stock ?> pcs</p>
                                    <button onclick="handleAddToCart(<?= $menu['id'] ?>, '<?= htmlspecialchars($menu['nama_menu'], ENT_QUOTES) ?>', <?= $menu['harga'] ?>, '<?= $kategoriSlug ?>')">
                                        <i class="fa-solid fa-plus"></i> Tambah
                                    </button>
                                <?php else: ?>
                                    <p class="menucard-stock">Tersedia: <?= $stock ?> porsi</p>
                                    <button onclick="handleAddToCart(<?= $menu['id'] ?>, '<?= htmlspecialchars($menu['nama_menu'], ENT_QUOTES) ?>', <?= $menu['harga'] ?>, '<?= $kategoriSlug ?>')">
                                        <i class="fa-solid fa-plus"></i> Tambah
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php
                    endwhile;
                else:
                    ?>
                    <div class="empty-category">
                        <i class="fa-solid fa-box-open"></i>
                        <p>Belum ada menu di kategori ini</p>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </section>

    <?php endwhile; ?>

    <!-- Bottom Navigation -->
    <div class="bottomnav">
        <a href="index.php">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="menu.php" class="active">
            <i class="fa-solid fa-book-open"></i>
            <span>Menu</span>
        </a>
        <a href="cart.php">
            <i class="fa-solid fa-shopping-cart"></i>
            <span>Pesanan</span>
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'mobileprofile.php' : 'mobilelogin.php'; ?>">
            <i class="fa-solid fa-user"></i>
            <span>Profil</span>
        </a>
    </div>

    <!-- Popup Level Pedas - HARUS ADA DI SINI -->
    <div class="level-popup-overlay" id="levelPopup">
        <div class="level-popup">
            <button class="level-popup-close" onclick="closeLevelPopup()">
                <i class="fa-solid fa-times"></i>
            </button>

            <div class="level-popup-header">
                <div class="level-popup-icon">🌶️</div>
                <h2 class="level-popup-title">Pilih Tingkat Kepedasan</h2>
                <p class="level-popup-subtitle">Seberapa pedas kamu mau?</p>
            </div>

            <div class="level-options">
                <div class="level-option" onclick="selectLevel(0)" data-level="0">
                    <span class="level-option-icon">😊</span>
                    <div class="level-option-name">Level 0</div>
                    <div class="level-option-desc">(tidak pedas)</div>
                </div>

                <div class="level-option" onclick="selectLevel(1)" data-level="1">
                    <span class="level-option-icon">😋</span>
                    <div class="level-option-name">Level 1</div>
                    <div class="level-option-desc">(sedikit pedas)</div>
                </div>

                <div class="level-option" onclick="selectLevel(2)" data-level="2">
                    <span class="level-option-icon">🙂</span>
                    <div class="level-option-name">Level 2</div>
                    <div class="level-option-desc">(sedang)</div>
                </div>

                <div class="level-option" onclick="selectLevel(3)" data-level="3">
                    <span class="level-option-icon">😅</span>
                    <div class="level-option-name">Level 3</div>
                    <div class="level-option-desc">(pedas)</div>
                </div>

                <div class="level-option" onclick="selectLevel(4)" data-level="4">
                    <span class="level-option-icon">🥵</span>
                    <div class="level-option-name">Level 4</div>
                    <div class="level-option-desc">(sangat pedas)</div>
                </div>

                <div class="level-option" onclick="selectLevel(5)" data-level="5">
                    <span class="level-option-icon">💀</span>
                    <div class="level-option-name">Level 5</div>
                    <div class="level-option-desc">(extra pedas)</div>
                </div>
            </div>

            <div class="level-popup-footer">
                <button class="level-btn level-btn-cancel" onclick="closeLevelPopup()">
                    Batal
                </button>
                <button class="level-btn level-btn-confirm" id="confirmLevelBtn" onclick="confirmLevel()" disabled>
                    <i class="fa-solid fa-check"></i> Tambahkan
                </button>
            </div>
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
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="story.html">Tentang Kami</a></li>
                </ul>
            </div>

            <!-- Kolom 3 -->
            <div class="footercolumn">
                <h3>Lokasi Outlet</h3>
                <a href="https://maps.app.goo.gl/LTvoLGByuV2yoX4r5"><img
                        src="img/footermap.png"
                        alt="Peta Lokasi Kedai TaKi ID"
                        style="width: 200px; border-radius: 8px; margin-bottom: 10px"
                        class="footermaps" /></a>
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
        // ===== LEVEL POPUP STATE =====
        let currentMenuItem = {
            id: null,
            name: '',
            price: 0,
            selectedLevel: null
        };

        // ===== Handle Add to Cart =====
        function handleAddToCart(id, name, price, category) {
            console.log('Category:', category); // Debug log

            // Cek apakah kategori Seblak (case-insensitive)
            if (category && category.toLowerCase().trim() === 'seblak') {
                // Buka popup level
                currentMenuItem = {
                    id,
                    name,
                    price,
                    selectedLevel: null
                };
                openLevelPopup();
                console.log('Opening popup for Seblak'); // Debug log
            } else {
                // Langsung add to cart tanpa level
                console.log('Direct add to cart'); // Debug log
                addToCart(id, name, price, null);
            }
        }

        // ===== Open Level Popup =====
        function openLevelPopup() {
            console.log('openLevelPopup called'); // Debug
            const popup = document.getElementById('levelPopup');

            if (!popup) {
                console.error('Popup element not found!');
                return;
            }

            popup.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scroll

            // Reset selection
            document.querySelectorAll('.level-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            const confirmBtn = document.getElementById('confirmLevelBtn');
            if (confirmBtn) {
                confirmBtn.disabled = true;
            }

            console.log('Popup opened successfully'); // Debug
        }

        // ===== Close Level Popup =====
        function closeLevelPopup() {
            const popup = document.getElementById('levelPopup');
            popup.classList.remove('active');
            document.body.style.overflow = ''; // Enable scroll
            currentMenuItem = {
                id: null,
                name: '',
                price: 0,
                selectedLevel: null
            };
        }

        // ===== Select Level =====
        function selectLevel(level) {
            // Remove previous selection
            document.querySelectorAll('.level-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selected class
            const selectedOption = document.querySelector(`[data-level="${level}"]`);
            selectedOption.classList.add('selected');

            // Store selected level
            currentMenuItem.selectedLevel = level;

            // Enable confirm button
            document.getElementById('confirmLevelBtn').disabled = false;
        }

        // ===== Confirm Level =====
        function confirmLevel() {
            if (currentMenuItem.selectedLevel === null) {
                showNotification('Silakan pilih level kepedasan', 'error');
                return;
            }

            // Add to cart dengan level
            addToCart(
                currentMenuItem.id,
                currentMenuItem.name,
                currentMenuItem.price,
                currentMenuItem.selectedLevel
            );

            // Close popup
            closeLevelPopup();
        }

        // ===== Close popup when clicking overlay =====
        document.getElementById('levelPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLevelPopup();
            }
        });

        // ===== Search Function =====
        function searchMenu() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const menuCards = document.querySelectorAll('.menucard');
            const categories = document.querySelectorAll('.menusection');

            // Hapus pesan pencarian sebelumnya
            document.querySelectorAll('.search-empty').forEach(el => el.remove());

            let totalFound = 0;

            // Loop setiap menu card
            menuCards.forEach(card => {
                const menuName = card.getAttribute('data-name');
                if (searchTerm === '' || menuName.includes(searchTerm)) {
                    card.style.display = 'flex';
                    totalFound++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Cek setiap kategori untuk tampilkan pesan empty
            categories.forEach(cat => {
                const grid = cat.querySelector('.menugrid');
                const visibleCards = Array.from(cat.querySelectorAll('.menucard')).filter(
                    card => card.style.display !== 'none'
                );

                // Hapus pesan empty lama di kategori ini
                const oldEmpty = cat.querySelector('.search-empty');
                if (oldEmpty) oldEmpty.remove();

                // Jika tidak ada card yang visible dan ada searchTerm
                if (visibleCards.length === 0 && searchTerm !== '') {
                    const newEmpty = document.createElement('div');
                    newEmpty.className = 'empty-category search-empty';
                    newEmpty.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i><p>Tidak ada menu yang ditemukan di kategori ini</p>';
                    grid.appendChild(newEmpty);
                }
            });

            // Tampilkan notifikasi hasil pencarian
            if (searchTerm !== '') {
                if (totalFound === 0) {
                    showNotification('❌ Tidak ada menu yang ditemukan', 'error');
                } else {
                    showNotification(`✓ Ditemukan ${totalFound} menu`, 'success');
                }
            }
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchMenu();
            }
        });

        // Real-time search (opsional, bisa diaktifkan)
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const menuCards = document.querySelectorAll('.menucard');

            // Hapus pesan pencarian sebelumnya
            document.querySelectorAll('.search-empty').forEach(el => el.remove());

            if (searchTerm === '') {
                // Reset semua card jadi visible
                menuCards.forEach(card => card.style.display = 'flex');
                return;
            }

            // Real-time filter (aktif saat mengetik)
            menuCards.forEach(card => {
                const menuName = card.getAttribute('data-name');
                if (menuName.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Cek kategori kosong
            const categories = document.querySelectorAll('.menusection');
            categories.forEach(cat => {
                const grid = cat.querySelector('.menugrid');
                const visibleCards = Array.from(cat.querySelectorAll('.menucard')).filter(
                    card => card.style.display !== 'none'
                );

                const oldEmpty = cat.querySelector('.search-empty');
                if (oldEmpty) oldEmpty.remove();

                if (visibleCards.length === 0) {
                    const newEmpty = document.createElement('div');
                    newEmpty.className = 'empty-category search-empty';
                    newEmpty.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i><p>Tidak ada menu yang ditemukan di kategori ini</p>';
                    grid.appendChild(newEmpty);
                }
            });
        });

        // ===== Add to Cart Function =====
        async function addToCart(id, name, price, level = null) {
            try {
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('menu_id', id);
                formData.append('quantity', 1);

                // Tambahkan level jika ada (untuk Seblak)
                if (level !== null) {
                    formData.append('level', level);
                }

                const response = await fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Tampilkan pesan dengan info level jika ada
                    let message = `✓ ${name} ditambahkan ke keranjang!`;
                    if (level !== null) {
                        message = `✓ ${name} (Level ${level}) ditambahkan ke keranjang!`;
                    }
                    showNotification(message, 'success');
                    updateCartBadge(result.cart_count);
                } else {
                    if (result.redirect) {
                        // User belum login, redirect ke halaman login/register
                        if (confirm(result.message + '\n\nApakah Anda ingin login sekarang?')) {
                            window.location.href = result.redirect;
                        }
                    } else {
                        showNotification(result.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('❌ Gagal menambahkan ke keranjang', 'error');
            }
        }

        // ===== Update Cart Badge =====
        function updateCartBadge(count) {
            let badge = document.querySelector('.cart-badge');

            if (!badge && count > 0) {
                // Create badge if not exists
                const cartLink = document.querySelector('a[href="cart.html"], a[href="cart.php"]');
                if (cartLink) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    badge.style.cssText = `
                        position: absolute;
                        top: -8px;
                        right: -8px;
                        background: #ff4b4b;
                        color: white;
                        border-radius: 50%;
                        width: 20px;
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 0.7rem;
                        font-weight: 700;
                    `;
                    cartLink.style.position = 'relative';
                    cartLink.appendChild(badge);
                }
            }

            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        }

        // ===== Load Cart Count on Page Load =====
        async function loadCartCount() {
            try {
                const formData = new FormData();
                formData.append('action', 'count');

                const response = await fetch('cart_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    updateCartBadge(result.count);
                }
            } catch (error) {
                console.error('Error loading cart count:', error);
            }
        }

        // Load cart count when page loads
        loadCartCount();

        // ===== Notification Function =====
        function showNotification(message, type = 'success') {
            // Hapus notifikasi lama jika ada
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

        // ===== Profile Link Handler =====
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

    <!-- Add animations -->
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
</body>

</html>