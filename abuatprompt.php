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
</style>