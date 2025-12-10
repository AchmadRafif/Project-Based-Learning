-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 01:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taki_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'AdminTaki', 'admin@taki.com', '12345', '2025-11-14 04:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `level` int(11) DEFAULT NULL COMMENT 'Level kepedasan untuk Seblak (0-5)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Seblak'),
(2, 'Nasi'),
(3, 'Berkuah'),
(4, 'Snack'),
(5, 'Sate'),
(6, 'Minuman');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nama_menu` varchar(200) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `foto_menu` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `nama_menu`, `kategori_id`, `harga`, `stock`, `foto_menu`, `created_at`) VALUES
(8, 'Seblak Rafael', 1, 10000, 47, '1763514795_seblak-rafael.png', '2025-11-19 01:13:15'),
(9, 'Mie Kuah Seblak', 1, 10000, 45, '1763514820_mie-kuahseblak.png', '2025-11-19 01:13:40'),
(10, 'Seblak Aci', 1, 15000, 48, '1763514859_SeblakAci.png', '2025-11-19 01:14:19'),
(11, 'Seblak Ceker', 1, 15000, 43, '1763514895_seblak-ceker.png', '2025-11-19 01:14:55'),
(12, 'Seblak Komplit', 1, 20000, 39, '1763514917_seblak-komplit.png', '2025-11-19 01:15:17'),
(13, 'Seblak Original', 1, 10000, 45, '1763514938_seblak-original.png', '2025-11-19 01:15:38'),
(14, 'Nasi Sayap Saos Merah', 2, 15000, 48, '1763515117_Nasi_Sayap-S.Merah.png', '2025-11-19 01:18:37'),
(15, 'Nasi Gila Oklo', 2, 15000, 49, '1763515239_Nasi-GilaOklo.png', '2025-11-19 01:20:39'),
(16, 'Nasi Gila Odeng', 2, 15000, 47, '1763515272_Nasi-GilaScallop.png', '2025-11-19 01:20:53'),
(17, 'Nasi Gila Scallop', 2, 15000, 48, '1763515295_Nasi-GilaScallop.png', '2025-11-19 01:21:35'),
(18, 'Nasi GIla Otak-Otak', 2, 15000, 49, '1763515313_Nasi_GilaOtak-otak.png', '2025-11-19 01:21:53'),
(19, 'Nasi Gila Sosis', 2, 15000, 49, '1763515333_Nasi-GilaSosis.png', '2025-11-19 01:22:13'),
(20, 'Nasi Gila Siomay', 2, 15000, 49, '1763515351_Nasi-GilaSiomay.png', '2025-11-19 01:22:31'),
(21, 'Nasi Gila Original', 2, 10000, 49, '1763515369_NasiGilaOri-taki.jpg', '2025-11-19 01:22:49'),
(23, 'Cireng Kuah Keju', 3, 15000, 50, '1763515858_cireng-kuah-real.jpg', '2025-11-19 01:30:58'),
(24, 'Suki Kuah Tomyum (Jumbo)', 3, 20000, 50, '1763515900_Suki-KuahTomyum.png', '2025-11-19 01:31:40'),
(25, 'Suki Kuah Tomyum (Reguler)', 3, 15000, 49, '1763515918_Suki-KuahTomyum.png', '2025-11-19 01:31:58'),
(26, 'Suki Kuah Baso (Jumbo)', 3, 15000, 50, '1763515936_Suki-KuahBaso.png', '2025-11-19 01:32:16'),
(27, 'Suki Kuah Baso (Reguler)', 3, 10000, 50, '1763515958_Suki-KuahBaso.png', '2025-11-19 01:32:38'),
(28, 'Baso Tulang Rangu', 3, 18000, 50, '1763515989_Baso-TulangRangu.png', '2025-11-19 01:33:09'),
(29, 'Sotong Bojot', 4, 10000, 50, '1763516288_Sotong-bojot.png', '2025-11-19 01:38:08'),
(30, 'Gohyong Ayam', 4, 15000, 50, '1763516303_Gohyong-Ayam.png', '2025-11-19 01:38:23'),
(31, 'Basreng Bojot', 4, 10000, 50, '1763516322_Basreng-Bojot.png', '2025-11-19 01:38:42'),
(32, 'Bakpao Coklat', 4, 5000, 50, '1763516354_Bakpo-Coklat.png', '2025-11-19 01:39:14'),
(33, 'Cireng Bojot', 4, 5000, 49, '1763516497_cireng-bojot_taki.png', '2025-11-19 01:41:37'),
(34, 'Cirawang', 4, 10000, 49, '1763516520_cirawang-taki.jpg', '2025-11-19 01:42:00'),
(35, 'Cireng Isi (3 Varian)', 4, 10000, 50, '1763516572_cireng-isi_taki.png', '2025-11-19 01:42:52'),
(36, 'Air Mineral', 6, 3000, 50, '1763516846_air-mineral.jpeg', '2025-11-19 01:47:26'),
(37, 'Es Teh Jumbo', 6, 5000, 50, '1763516880_es-tehjumbo.jpeg', '2025-11-19 01:48:00'),
(38, 'Es Teh', 6, 3000, 50, '1763516906_es-teh-reguler_taki.png', '2025-11-19 01:48:26'),
(39, 'Iced Milk Tea', 6, 13000, 49, '1763516928_MilkTea-taki.jpg', '2025-11-19 01:48:48'),
(40, 'Coffee Latte', 6, 13000, 49, '1763516999_coffee-latte.jpeg', '2025-11-19 01:49:59'),
(41, 'Choco Hazelnut', 6, 12000, 50, '1763517021_choco-latte_taki.png', '2025-11-19 01:50:21'),
(42, 'Hazelnut Latte', 6, 12000, 50, '1763517044_Hazelnut-Latte.jpeg', '2025-11-19 01:50:44');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `total_price` int(11) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `invoice_number`, `total_price`, `nama_penerima`, `no_hp`, `alamat_lengkap`, `catatan`, `payment_method`, `status`, `created_at`) VALUES
(6, 7, 'INV/20251120/0001', 30000, 'rapip', '08123456789', 'saaws', 'aws', 'COD', 'cancelled', '2025-11-20 04:40:54'),
(7, 7, 'INV/20251120/0002', 45000, 'rapip', '08123456789', 'aws', 'aws', 'QRIS', 'completed', '2025-11-20 04:42:02'),
(8, 7, 'INV/20251120/0003', 60000, 'dupan', '08123456789', 'aws', 'aws', 'COD', 'completed', '2025-11-20 04:43:18'),
(9, 7, 'INV/20251120/0004', 30000, 'nickge', '08123456789', 'aws', 'aws', 'COD', 'completed', '2025-11-20 04:44:19'),
(10, 7, 'INV/20251120/0005', 45000, 'yahahahah', '08123456789', 'sewa', 'sawe', 'QRIS', 'completed', '2025-11-20 04:48:01'),
(11, 7, 'INV/20251121/0001', 105000, 'rapip', '08123456789', 'Jl. dhefano', 'nego 100 pls', 'COD', 'completed', '2025-11-21 04:09:24'),
(12, 7, 'INV/20251122/0001', 30000, 'rapip', '08123456789', 'Jl. Semesa', 'seas', 'QRIS', 'completed', '2025-11-22 10:04:07'),
(13, 7, 'INV/20251122/0002', 25000, 'dupan', '08123456789', 'aura', 'aura', 'QRIS', 'completed', '2025-11-22 10:15:19'),
(14, 7, 'INV/20251122/0003', 30000, 'rapip', '08123456789', 'na', 'ya', 'QRIS', 'completed', '2025-11-22 10:19:29'),
(15, 7, 'INV/20251122/0004', 45000, 'rapip', '08123456789', 'na', 'ya', 'QRIS', 'pending', '2025-11-22 14:05:52'),
(16, 11, 'INV/20251126/0001', 25000, 'Charentyo', '08123456789', 'rentyo', 'ya', 'QRIS', 'pending', '2025-11-26 12:10:13'),
(17, 7, 'INV/20251130/0001', 30000, 'rapip', '08123456789', 'sawas', 'saswa', 'QRIS', 'pending', '2025-11-30 04:26:25'),
(18, 7, 'INV/20251201/0001', 15000, 'rapip', '08123456789', 'awsa', 'swas', 'QRIS', 'pending', '2025-12-01 15:22:31'),
(19, 7, 'INV/20251201/0002', 20000, 'dupan', '08123456789', 'sawsss', 'sassss', 'COD', 'pending', '2025-12-01 15:24:06'),
(20, 12, 'INV/20251203/0001', 28000, 'jokiwi', '08123456789', 'Rt 2 Rw 1 istana negara solo', 'Pake spesial dari pak wowo', 'COD', 'processing', '2025-12-03 04:09:04'),
(21, 14, 'INV/20251204/0001', 45000, 'nasiful', '08123456789', 'vvfdvfvv', 'vcvfvfvfvfv', 'COD', 'pending', '2025-12-04 01:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `nama_menu` varchar(200) NOT NULL,
  `harga` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `level` int(11) DEFAULT NULL COMMENT 'Level kepedasan untuk Seblak (0-5)',
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `nama_menu`, `harga`, `quantity`, `level`, `subtotal`) VALUES
(12, 6, 12, 'Seblak Komplit', 20000, 1, NULL, 20000),
(13, 6, 34, 'Cirawang', 10000, 1, NULL, 10000),
(14, 7, 10, 'Seblak Aci', 15000, 1, 5, 15000),
(15, 7, 11, 'Seblak Ceker', 15000, 1, 4, 15000),
(16, 7, 14, 'Nasi Sayap Saos Merah', 15000, 1, 0, 15000),
(17, 8, 12, 'Seblak Komplit', 20000, 1, 4, 20000),
(18, 8, 12, 'Seblak Komplit', 20000, 1, 1, 20000),
(19, 8, 12, 'Seblak Komplit', 20000, 1, 5, 20000),
(20, 9, 9, 'Mie Kuah Seblak', 10000, 1, 2, 10000),
(21, 9, 9, 'Mie Kuah Seblak', 10000, 1, 3, 10000),
(22, 9, 9, 'Mie Kuah Seblak', 10000, 1, 5, 10000),
(23, 10, 13, 'Seblak Original', 10000, 1, 5, 10000),
(24, 10, 12, 'Seblak Komplit', 20000, 1, 4, 20000),
(25, 10, 11, 'Seblak Ceker', 15000, 1, 0, 15000),
(26, 11, 16, 'Nasi Gila Odeng', 15000, 7, 0, 105000),
(27, 12, 13, 'Seblak Original', 10000, 1, 5, 10000),
(28, 12, 12, 'Seblak Komplit', 20000, 1, 4, 20000),
(29, 13, 9, 'Mie Kuah Seblak', 10000, 1, 2, 10000),
(30, 13, 11, 'Seblak Ceker', 15000, 1, 2, 15000),
(31, 14, 16, 'Nasi Gila Odeng', 15000, 2, 0, 30000),
(32, 15, 16, 'Nasi Gila Odeng', 15000, 1, 0, 15000),
(33, 15, 17, 'Nasi Gila Scallop', 15000, 2, 0, 30000),
(34, 16, 13, 'Seblak Original', 10000, 1, 4, 10000),
(35, 16, 11, 'Seblak Ceker', 15000, 1, 4, 15000),
(36, 17, 12, 'Seblak Komplit', 20000, 1, 5, 20000),
(37, 17, 13, 'Seblak Original', 10000, 1, 4, 10000),
(38, 18, 11, 'Seblak Ceker', 15000, 1, 5, 15000),
(39, 19, 12, 'Seblak Komplit', 20000, 1, 4, 20000),
(40, 20, 14, 'Nasi Sayap Saos Merah', 15000, 1, 0, 15000),
(41, 20, 40, 'Coffee Latte', 13000, 1, 0, 13000),
(42, 21, 12, 'Seblak Komplit', 20000, 1, 4, 20000),
(43, 21, 11, 'Seblak Ceker', 15000, 1, 4, 15000),
(44, 21, 8, 'Seblak Rafael', 10000, 1, 4, 10000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `created_at`) VALUES
(7, 'snickerdoodle', 'niggatron@gmail.com', '08123456789', '$2y$10$D0RkyL0q9fDkK1zwbvF64u6g0Sn4yViePqXwHplRNvqZW4pMtrAj.', '2025-11-13 12:56:55'),
(8, 'snickerdoodle', 'niggatron1@gmail.com', '08123456789', '$2y$10$W6JFrKOOK6WhyZlJJbYKcuM3YcNxcs9xfH2qHJgt1ubms2efVYilq', '2025-11-13 13:04:23'),
(10, 'fahri', 'nigger12534@gmail.com', '08123456789', '$2y$10$smXLIR92EAz8JQt9Sjj8qe6f5zsytlM/BU2wXRkJZf.4mxT/pHmnC', '2025-11-20 01:25:19'),
(11, 'BearAqua', 'bearaqua99@gmail.com', '08123456789', '$2y$10$0t8vTR/1AiWoNjagQWP8LuntZyHigUR3U1qIc0tG3Q22MyY4jz.IK', '2025-11-26 12:06:47'),
(12, 'Jokiwi', 'Jokiwibowo@gmail.com', '089123456789', '$2y$10$SpeHsjbzNih6/ovUS9HVTecayF9Nr1iFIjRH2mDOW/i/6WcRrwPRa', '2025-12-03 04:06:47'),
(13, 'nasiful', 'nasifulhaq@gmail.com', '00986544', '$2y$10$rpfIvWBQ7hEAfGdbUBHXWOO5JiAayfM7g44p.YQ8250O9gi9ADMlK', '2025-12-04 01:09:22'),
(14, 'nana', 'nasiful@gmail.com', '6527626', '$2y$10$JXQ9oOt8l8.Qq7xQN2TQU.DnSaOHMHNpquVAVeFqL/lkVG7GnQZe.', '2025-12-04 01:10:11');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_cart_details`
-- (See below for the actual view)
--
CREATE TABLE `view_cart_details` (
`cart_id` int(11)
,`user_id` int(11)
,`quantity` int(11)
,`level` int(11)
,`menu_id` int(11)
,`nama_menu` varchar(200)
,`harga` int(11)
,`foto_menu` varchar(255)
,`stock` int(11)
,`nama_kategori` varchar(100)
,`subtotal` bigint(21)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_cart_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_cart_summary` (
`user_id` int(11)
,`total_items` bigint(21)
,`total_quantity` decimal(32,0)
,`total_price` decimal(42,0)
);

-- --------------------------------------------------------

--
-- Structure for view `view_cart_details`
--
DROP TABLE IF EXISTS `view_cart_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_cart_details`  AS SELECT `c`.`id` AS `cart_id`, `c`.`user_id` AS `user_id`, `c`.`quantity` AS `quantity`, `c`.`level` AS `level`, `m`.`id` AS `menu_id`, `m`.`nama_menu` AS `nama_menu`, `m`.`harga` AS `harga`, `m`.`foto_menu` AS `foto_menu`, `m`.`stock` AS `stock`, `k`.`nama_kategori` AS `nama_kategori`, `m`.`harga`* `c`.`quantity` AS `subtotal`, `c`.`created_at` AS `created_at`, `c`.`updated_at` AS `updated_at` FROM ((`cart` `c` join `menu` `m` on(`c`.`menu_id` = `m`.`id`)) join `kategori` `k` on(`m`.`kategori_id` = `k`.`id`)) ORDER BY `c`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_cart_summary`
--
DROP TABLE IF EXISTS `view_cart_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_cart_summary`  AS SELECT `c`.`user_id` AS `user_id`, count(0) AS `total_items`, sum(`c`.`quantity`) AS `total_quantity`, sum(`m`.`harga` * `c`.`quantity`) AS `total_price` FROM (`cart` `c` join `menu` `m` on(`c`.`menu_id` = `m`.`id`)) GROUP BY `c`.`user_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_menu_level` (`user_id`,`menu_id`,`level`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `idx_cart_user` (`user_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_kategori` (`kategori_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_orders_user_status` (`user_id`,`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `address` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
