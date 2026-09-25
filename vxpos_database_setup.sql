-- =======================================================
-- VX-POS ENTERPRISE MULTI-STORE DATABASE SETUP
-- Sistem: Point of Sale & Multi-Store ERP Modern
-- Database Target: cpmeyhn5897_vxpos
-- =======================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- -------------------------------------------------------
-- 1. TABEL: toko (Multi-Tenant Management)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `toko` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `paket` varchar(50) NOT NULL DEFAULT 'pro',
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `expired_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `toko_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. TABEL: users (Karyawan, Admin, Superadmin)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'kasir',
  `is_platform_admin` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `hak_akses` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_toko_id_index` (`toko_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. TABEL: pengaturan_toko (Profil Toko, Logo, Kontak)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan_toko` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_toko` varchar(255) NOT NULL DEFAULT 'Vx-Pos',
  `logo` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pengaturan_toko_toko_id_index` (`toko_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. TABEL: barang (Master Data Produk/Barang)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `barang` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_barang` varchar(255) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL DEFAULT 'Umum',
  `satuan` varchar(50) NOT NULL DEFAULT 'Pcs',
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `barang_toko_id_index` (`toko_id`),
  KEY `barang_kode_barang_index` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. TABEL: stok (Inventori & Safety Stock)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stok` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `stok_tersedia` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 0,
  `status_warning` varchar(20) NOT NULL DEFAULT 'aman',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stok_barang_id_index` (`barang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 6. TABEL: harga (Margin Proteksi & Anti-Rugi)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `harga` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `harga_modal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_minimum` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon_rupiah` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harga_barang_id_index` (`barang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 7. TABEL: transaksi (Induk Penjualan)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `no_invoice` varchar(255) NOT NULL,
  `sales_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pelanggan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_pelanggan` varchar(255) NOT NULL DEFAULT 'Umum',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `total_transaksi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dp` decimal(15,2) NOT NULL DEFAULT 0.00,
  `piutang` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaksi_toko_id_index` (`toko_id`),
  KEY `transaksi_no_invoice_index` (`no_invoice`),
  KEY `transaksi_sales_id_index` (`sales_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 8. TABEL: detail_transaksi (Item Detail & COGS Historis)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaksi_id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `harga_modal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon_item` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_transaksi_toko_id_index` (`toko_id`),
  KEY `detail_transaksi_transaksi_id_index` (`transaksi_id`),
  KEY `detail_transaksi_barang_id_index` (`barang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 9. TABEL: permintaan_gudang (Pengambilan & Validasi Stok)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permintaan_gudang` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaksi_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu',
  `diperbarui_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permintaan_gudang_toko_id_index` (`toko_id`),
  KEY `permintaan_gudang_transaksi_id_index` (`transaksi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 10. TABEL: riwayat_cicilan (Kartu Piutang & Pembayaran)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `riwayat_cicilan` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaksi_id` bigint(20) UNSIGNED NOT NULL,
  `nominal_bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riwayat_cicilan_toko_id_index` (`toko_id`),
  KEY `riwayat_cicilan_transaksi_id_index` (`transaksi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 11. TABEL: pencairan_bonus (Komisi Sales)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pencairan_bonus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sales_id` bigint(20) UNSIGNED NOT NULL,
  `total_bonus` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pencairan_bonus_toko_id_index` (`toko_id`),
  KEY `pencairan_bonus_sales_id_index` (`sales_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 12. TABEL: penggajian (Payroll Karyawan)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `penggajian` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `toko_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `periode_bulan` varchar(2) NOT NULL,
  `periode_tahun` varchar(4) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(15,2) NOT NULL DEFAULT 0.00,
  `potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan_potongan` varchar(255) DEFAULT NULL,
  `total_gaji` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tanggal_cair` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penggajian_toko_id_index` (`toko_id`),
  KEY `penggajian_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 13. TABEL: migrations (Laravel Schema Tracking)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- SEED DATA AWAL: TOKO UTAMA, PENGATURAN, & SUPERADMIN
-- -------------------------------------------------------

-- 1. Toko Utama
INSERT INTO `toko` (`id`, `nama_toko`, `slug`, `alamat`, `no_telp`, `logo`, `paket`, `status`, `expired_at`, `created_at`, `updated_at`)
VALUES
(1, 'Vx-Pos', 'vx-pos', 'Jl. Sistem Modern No. 1', '08123456789', NULL, 'enterprise', 'aktif', '2036-12-31', NOW(), NOW())
ON DUPLICATE KEY UPDATE `nama_toko` = 'Vx-Pos';

-- 2. Pengaturan Toko Utama
INSERT INTO `pengaturan_toko` (`id`, `toko_id`, `nama_toko`, `logo`, `alamat`, `telepon`, `email`, `created_at`, `updated_at`)
VALUES
(1, 1, 'Vx-Pos', NULL, 'Jl. Sistem Modern No. 1', '08123456789', 'admin@vxpos.id', NOW(), NOW())
ON DUPLICATE KEY UPDATE `nama_toko` = 'Vx-Pos';

-- 3. Akun Super Administrator (Username: admin | Password: admin123)
-- Hash bcrypt '$2y$12$cLEVpnJwIrzVQtpeBjj4g.a.W1zMquiAfRkdOymCHJI68IWJXrT9e' = admin123
INSERT INTO `users` (`id`, `toko_id`, `nama`, `username`, `password`, `role`, `is_platform_admin`, `status`, `hak_akses`, `remember_token`, `created_at`, `updated_at`)
VALUES
(1, 1, 'Super Admin Vx-Pos', 'admin', '$2y$12$cLEVpnJwIrzVQtpeBjj4g.a.W1zMquiAfRkdOymCHJI68IWJXrT9e', 'superadmin', 1, 'aktif', '["master_barang","manajemen_harga","transaksi_sales","stok_gudang","validasi_kasir","laporan_penjualan","kelola_bonus","manajemen_user"]', NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE `username` = 'admin';

-- 4. Catat Riwayat Migrasi Laravel
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_09_25_000000_create_all_vxpos_tables', 1),
('2026_09_25_000001_create_multi_toko_and_tenant_support', 1)
ON DUPLICATE KEY UPDATE `batch` = 1;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
