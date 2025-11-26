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


    