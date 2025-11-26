<?php
session_start();
include "config.php";

// Ambil 5 menu favorit berdasarkan STOCK PALING SEDIKIT (hampir habis)
$favMenuQuery = mysqli_query($conn, "
    SELECT m.*, k.nama_kategori 
    FROM menu m
    JOIN kategori k ON m.kategori_id = k.id
    WHERE m.stock > 0 AND m.stock < 50
    ORDER BY m.stock ASC
    LIMIT 4
");

$favMenus = [];
while ($row = mysqli_fetch_assoc($favMenuQuery)) {
    $favMenus[] = $row;
}

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Taki ID</title>
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
    <link rel="stylesheet" href="style.css">

    <style>
        /* ============================= */
        /* MENU FAVORIT SECTION */
        /* ============================= */
        .menu-favorit {
            padding: 4rem 3rem;
            background: #ffffff;
        }

        .menu-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .menu-header h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .menu-header h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            /* background: linear-gradient(90deg, #bf0f0f 0%, #ff4b4b 100%); */
            border-radius: 2px;
        }

        .menu-subtitle {
            color: #666;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .menu-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .menucard {
            width: 100%;
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
            text-align: left;
        }

        .menucard h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #222;
            margin: 0 0 0.5rem;
            line-height: 1.3;
            text-align: left;
        }

        .menucard-price {
            color: #bf0f0f;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0.3rem 0;
            text-align: left;
        }

        .menucard-stock {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
            text-align: left;
        }

        .menucard-stock.low {
            color: #ff4b4b;
            font-weight: 600;
        }

        .menucard button {
            background-color: #bf0f0f;
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
            background-color: #8f1313;
        }

        .menucard button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .empty-menu {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-menu i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .lihat-lainnya {
            text-align: center;
            margin-top: 2rem;
        }

        .lihat-lainnya a {
            display: inline-block;
            background: linear-gradient(135deg, #f49a24 0%, #ff9800 100%);
            color: white;
            padding: 0.9rem 2.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(244, 154, 36, 0.3);
        }

        .lihat-lainnya a:hover {
            background: linear-gradient(135deg, #d48a20 0%, #e68a00 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(244, 154, 36, 0.4);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .menu-favorit {
                padding: 3rem 1rem;
            }

            .menu-header h2 {
                font-size: 1.6rem;
            }

            .menu-container {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 1rem;
            }

            .menu-card img,
            .no-image-fav {
                height: 140px;
            }

            .menu-card-content {
                padding: 1rem;
            }

            .menu-card-content h3 {
                font-size: 0.95rem;
            }

            .menu-price {
                font-size: 1rem;
            }

            .menu-card .btn-add,
            .menu-card .btn-disabled {
                padding: 0.7rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Tablet */
        @media (min-width: 769px) and (max-width: 1024px) {
            .menu-container {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 1.5rem;
            }
        }
    </style>


    <!-- Feather Icon -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navlogo">
            <a href="story.html"><img src="img/LogoTaki.png" alt="" /></a>
        </div>
        <div class="navsection">
            <div class="navmenu">
                <a href="#">Home</a>
                <a href="menu.php">Menu</a>
                <a href="story.html">Brand Story</a>
            </div>
            <div class="navicon">
                <a href="" class="carticon"><i data-feather="shopping-cart"></i></a>
                <a href="" class="usericon"><i data-feather="user"></i></a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Popup Login Start -->
    <div class="popuplogin" id="popuplogin">
        <div class="logcontent">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>Masuk ke Taki ID</h2>
            <p>Login untuk menikmati fitur lengkap Taki!</p>

            <form id="loginform">
                <label>Email<span class="req">*</span></label>
                <input
                    type="email"
                    id="email"
                    placeholder="Masukkan email anda"
                    required />

                <label>Password<span class="req">*</span></label>
                <div class="passwrapper">
                    <input
                        type="password"
                        id="password"
                        placeholder="Masukkan password anda" />
                    <span class="toggle" onclick="togglePassword()">
                        <i data-feather="eye"></i>
                    </span>
                </div>

                <a href="forgotpassword.php" class="forgot">Lupa password?</a>
                <button type="submit" class="submitbtn">Masuk</button>
            </form>

            <div class="divider"><span>atau</span></div>

            <button class="googlelogin">
                <img
                    src="https://www.svgrepo.com/show/355037/google.svg"
                    alt="Google" />
                Masuk dengan Google
            </button>

            <p class="register">
                Belum punya akun? <a href="register.php">Buat akun sekarang</a>
            </p>
        </div>
    </div>
    <!-- Popup Login End -->

    <!-- Home Section Start -->
    <section class="home" id="home">
        <main class="content">
            <div class="textbox">
                <h1>KEDAI <span>TAKI</span></h1>
                <h2><span>SATAY</span> & SUKI <span>STEAMBOAT</span></h2>
                <p>
                    Destinasi Ngiler Terbaru: Dari Pedas Gila Seblak Sampai Kuah Hangat
                    Suki. Wajib Coba!
                </p>
                <a href="menu.php" class="btn">Beli Sekarang</a>
            </div>
        </main>
    </section>
    <!-- Home Section End -->

    <!-- Filler Section Start -->
    <?php if (!$isLoggedIn): ?>
        <!-- Sign Up Banner -->
        <section class="promotionsign signup-banner">
            <div class="promotionup">
                <img src="img/LogoTaki.png" alt="Taki ID" />
                <p>
                    Sign up agar kamu dapat memesan langsung melalui <strong>Website!</strong>
                </p>
                <a href="register.php" class="btn-signup">Sign Up</a>
            </div>
        </section>
    <?php else: ?>
        <!-- Member Promo Banner -->
        <section class="promotionsign promo-banner">
            <div class="promotionup">
                <i class="fa-solid fa-user" style="font-size: 3rem; color: #393939;"></i>
                <p style="color: #393939; margin: 1rem 0;">
                    <strong style="font-size: 1.3rem;">Selamat! Anda Member Taki ID!</strong><br>
                    Nikmati pembelian melalui website setiap harinya!
                </p>
                <a href="menu.php" class="btn-promo">Pesan Sekarang</a>
            </div>
        </section>
    <?php endif; ?>
    <!-- Filler Section End -->

    <!-- Promo Section start -->
    <section id="promo" class="promo">
        <div class="promotext">
            <h1>Dapatkan Promo Kami</h1>
            <a href="promo.html">
                <h3>Selengkapnya</h3>
            </a>
        </div>

        <div class="promocontainer">
            <div class="promoimg">
                <a href="promodisplay1.html"><img src="img/Promo/promo1.png" alt="" /></a>
            </div>
            <div class="promoimg">
                <a href="promodisplay2.html"><img src="img/Promo/promo2.png" alt="" /></a>
            </div>
        </div>
    </section>
    <!-- Promo Section end -->

    <!-- Menu Section Start -->
    <section class="menu-favorit" id="menufav">
        <div class="menu-header">
            <h2>Menu Favorit</h2>
            <!-- <p class="menu-subtitle">Menu yang paling banyak dipesan minggu ini!</p> -->
        </div>

        <div class="menu-container">
            <?php if (count($favMenus) > 0): ?>
                <?php foreach ($favMenus as $menu):
                    $stock = $menu['stock'];
                    $outOfStock = $stock <= 0;
                    $lowStock = $stock > 0 && $stock < 10;

                    // Ambil nama kategori dari database
                    $kategoriQuery = $conn->query("SELECT nama_kategori FROM kategori WHERE id = " . $menu['kategori_id']);
                    $kategoriData = $kategoriQuery->fetch_assoc();
                    $kategoriNama = $kategoriData ? $kategoriData['nama_kategori'] : 'unknown';
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
                                <button class="menucard-btn" disabled>
                                    <i class="fa-solid fa-ban"></i> Habis
                                </button>
                            <?php elseif ($lowStock): ?>
                                <p class="menucard-stock low">Stok Terbatas: <?= $stock ?> pcs</p>
                                <button class="menucard-btn"
                                    onclick="handleAddToCart(
                                    <?= $menu['id'] ?>, 
                                    '<?= htmlspecialchars($menu['nama_menu'], ENT_QUOTES) ?>', 
                                    <?= $menu['harga'] ?>, 
                                    '<?= htmlspecialchars($kategoriNama, ENT_QUOTES) ?>'
                                )">
                                    <i class="fa-solid fa-plus"></i> Tambah
                                </button>
                            <?php else: ?>
                                <p class="menucard-stock">Tersedia: <?= $stock ?> porsi</p>
                                <button class="menucard-btn"
                                    onclick="handleAddToCart(
                                    <?= $menu['id'] ?>, 
                                    '<?= htmlspecialchars($menu['nama_menu'], ENT_QUOTES) ?>', 
                                    <?= $menu['harga'] ?>, 
                                    '<?= htmlspecialchars($kategoriNama, ENT_QUOTES) ?>'
                                )">
                                    <i class="fa-solid fa-plus"></i> Tambah
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-menu">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Belum ada menu favorit tersedia</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="lihat-lainnya">
            <a href="menu.php">Lihat Menu Lainnya</a>
        </div>
    </section>
    <!-- Menu Section End -->

    <!-- Tambahkan Script di bagian bawah sebelum </body> -->
    <script src="menufav.js"></script>
    <!-- ATAU embed langsung scriptnya -->

    <!-- MenuGerak Start -->
    <section class="menugerak">
        <a href="menu.php#seblak">
            <div class="movedtext left">
                <div class="track">
                    <span class="text">SEBLAK TAKI & PRASMANAN • SEBLAK TAKI & PRASMANAN • SEBLAK TAKI &
                        PRASMANAN • SEBLAK TAKI & PRASMANAN • SEBLAK TAKI & PRASMANAN •
                        SEBLAK TAKI & PRASMANAN •
                    </span>
                </div>
            </div>
        </a>

        <a href="menu.php#menunasi">
            <div class="movedtext right">
                <div class="track">
                    <span class="text">MENU NASI GILA • MENU NASI GILA • MENU NASI GILA • MENU NASI GILA
                        • MENU NASI GILA • MENU NASI GILA •
                    </span>
                </div>
            </div>
        </a>

        <a href="menu.php#sateloklok">
            <div class="movedtext left">
                <div class="track">
                    <span class="text">SATE LOK-LOK BAKAR/GORENG • SATE LOK-LOK BAKAR/GORENG • SATE
                        LOK-LOK BAKAR/GORENG • SATE LOK-LOK BAKAR/GORENG • SATE LOK-LOK
                        BAKAR/GORENG • SATE LOK-LOK BAKAR/GORENG •
                    </span>
                </div>
            </div>
        </a>

        <a href="menu.php#snack">
            <div class="movedtext right">
                <div class="track">
                    <span class="text">CEMILAN SNACK TAKI • CEMILAN SNACK TAKI • CEMILAN SNACK TAKI •
                        CEMILAN SNACK TAKI • CEMILAN SNACK TAKI • CEMILAN SNACK TAKI •
                    </span>
                </div>
            </div>
        </a>

        <a href="menu.php#minuman">
            <div class="movedtext left">
                <div class="track">
                    <span class="text">ANEKA MINUMAN SEGAR • ANEKA MINUMAN SEGAR • ANEKA MINUMAN SEGAR •
                        ANEKA MINUMAN SEGAR • ANEKA MINUMAN SEGAR • ANEKA MINUMAN SEGAR •
                    </span>
                </div>
            </div>
        </a>
    </section>
    <!-- MenuGerak End -->

    <!-- Testimoni Section Start -->
    <section class="testimoni">
        <h2 class="testimoni-title">Apa Kata Mereka?</h2>
        <div class="testimoni-container">
            <a href="https://share.google/7za3KSmYxFveXMuQx">
                <div class="testimoni-card">
                    <img
                        src="img/Tester/DindaRobiatulChusanah.png"
                        alt=""
                        class="testi-img" />
                    <h3>Dinda Robiatul Chasanah</h3>
                    <p>
                        “Rekomended buat pecinta seblak kayak akuuu..😍. Bumbunya gapernah
                        berubah selaluu enakk banget. Harganya juga ramah banget buat
                        dompet akuu. Dia juga banyak varian pilihanya. Mulai harga 2 rebu
                        bisa njajan disini”
                    </p>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </a>

            <a href="https://share.google/0pQMHYoKywip8batz">
                <div class="testimoni-card">
                    <img src="img/Tester/AstiRahayu.png" alt="" class="testi-img" />
                    <h3>Asti Rahayu</h3>
                    <p>
                        “High recommended ya, bumbu seblaknya pas banget, ngga kurang ngga
                        lebih, isiannya jg bervariasi. Ini salah satu seblak yg ngangeni
                        siiih 🥰”
                    </p>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </a>

            <a href="https://share.google/M44hVyBUncaMIextJ">
                <div class="testimoni-card">
                    <img src="img/Tester/Nusaibah.png" alt="" class="testi-img" />
                    <h3>Nusaibah</h3>
                    <p>
                        “Suki nya comfort food banget, biasanya beli lewat aplikasi
                        online, kali ini nyoba dateng langsung, menunya macem² mulai dari
                        suki sampe berbagai macem seblak ada. Tempatnya nyaman, bersih.
                        Harganya juga murah murah banget. Pembayaran bisa pake QRIS.”
                    </p>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </a>
        </div>
    </section>
    <!-- Testimoni Section End -->

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

    <!-- Bottom Navigation -->
    <div class="bottomnav">
        <a href="#" class="active"><i class="fa-solid fa-house"></i>Home</a>
        <a href="menu.php"><i class="fa-solid fa-book-open"></i>Menu</a>
        <a href="cart.php"><i class="fa-solid fa-shopping-cart"></i>Pesanan</a>
        <a href="#" id="profileLink"><i class="fa-solid fa-user"></i>Profil</a>
    </div>
    <script>
        // Cek status login dari localStorage
        const profileLink = document.getElementById("profileLink");
        // const isLoggedIn = localStorage.getItem("isLoggedIn");

        profileLink.addEventListener("click", (e) => {
            e.preventDefault();

            if (isLoggedIn) {
                // Jika sudah login, arahkan ke halaman profil
                window.location.href = "mobileprofile.php";
            } else {
                // Jika belum login, arahkan ke halaman register
                window.location.href = "mobilelogin.php";
            }
        });
    </script>

    <!-- Popup Login -->
    <div class="popuplogin" id="popuplogin">
        <div class="logcontent">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>Masuk ke Taki ID</h2>
            <p>Login untuk menikmati fitur lengkap Taki!</p>

            <form id="loginform">
                <label>Email<span class="req">*</span></label>
                <input
                    type="email"
                    id="email"
                    placeholder="Masukkan email anda"
                    required />

                <label>Password<span class="req">*</span></label>
                <div class="passwrapper">
                    <input
                        type="password"
                        id="password"
                        placeholder="Masukkan password anda"
                        required />
                    <span class="toggle" onclick="togglePassword()">
                        <i data-feather="eye"></i>
                    </span>
                </div>

                <a href="forgotpassword.php" class="forgot">Lupa password?</a>
                <button type="submit" class="submitbtn">Masuk</button>
            </form>

            <div class="divider"><span>atau</span></div>

            <button class="googlelogin">
                <img
                    src="https://www.svgrepo.com/show/355037/google.svg"
                    alt="Google" />
                Masuk dengan Google
            </button>

            <p class="register">
                Belum punya akun? <a href="register.php">Buat akun sekarang</a>
            </p>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // === POPUP CONTROL ===
        const popup = document.getElementById("popuplogin");
        const usericons = document.querySelectorAll(".usericon");
        const carticons = document.querySelectorAll(".carticon");
        const toast = document.getElementById("toast");

        // === CEK STATUS LOGIN DARI SERVER ===
        let isLoggedIn = false;

        async function checkLoginStatus() {
            try {
                const res = await fetch("checksession.php");
                const data = await res.json();
                isLoggedIn = data.loggedIn;
            } catch (err) {
                console.error("Gagal cek status login:", err);
            }
        }

        // Jalankan saat halaman dimuat
        checkLoginStatus();

        // === EVENT KLIK ICON USER ===
        usericons.forEach((icon) => {
            icon.addEventListener("click", (e) => {
                e.preventDefault();
                if (isLoggedIn) {
                    window.location.href = "profile.php";
                } else {
                    popup.style.display = "block";
                }
            });
        });

        // === EVENT KLIK ICON USER ===
        carticons.forEach((icon) => {
            icon.addEventListener("click", (e) => {
                e.preventDefault();
                if (isLoggedIn) {
                    window.location.href = "cart.php";
                } else {
                    popup.style.display = "block";
                }
            });
        });

        function closePopup() {
            popup.style.display = "none";
        }

        function togglePassword() {
            const pass = document.getElementById("password");
            pass.type = pass.type === "password" ? "text" : "password";
        }

        // === TOAST NOTIF (SAMA PERSIS KAYA REGISTER) ===
        function showToast(message, type = "success") {
            toast.textContent = message;
            toast.className = `toast show ${type}`;
            setTimeout(() => {
                toast.classList.remove("show");
            }, 2500);
        }

        // === LOGIN KE BACKEND PHP ===
        document
            .getElementById("loginform")
            .addEventListener("submit", async (e) => {
                e.preventDefault();

                const email = document.getElementById("email").value.trim();
                const password = document.getElementById("password").value.trim();

                if (!email || !password) {
                    showToast("Harap isi semua data!", "error");
                    return;
                }

                try {
                    const res = await fetch("login.php", {
                        method: "POST",
                        body: new URLSearchParams({
                            email,
                            password
                        }),
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                    });

                    const data = await res.json();

                    if (data.status === "success") {
                        showToast(
                            "Login berhasil! Selamat datang, " + data.name + ".",
                            "success"
                        );
                        setTimeout(() => {
                            closePopup();
                            window.location.href = "profile.php";
                        }, 2200);
                    } else {
                        showToast(data.message || "Email atau password salah!", "error");
                    }
                } catch (err) {
                    showToast("Gagal menghubungi server!", "error");
                }
            });
    </script>

    <!-- Feather Icons -->
    <script>
        feather.replace();
    </script>
</body>

</html>