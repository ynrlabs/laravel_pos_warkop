-- ============================================================================
-- WARKOP POS - DATABASE SCHEMA (MySQL 8.0+)
-- ============================================================================
-- Generated: 22 Juli 2026
-- Version: 1.0
-- Engine: InnoDB
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- CORE TABLES: OUTLETS, USERS, ROLES, PERMISSIONS
-- ============================================================================

-- Outlets / Cabang
DROP TABLE IF EXISTS `outlets`;
CREATE TABLE `outlets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL COMMENT 'Kode outlet unik (ex: WPK001)',
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama outlet',
    `address` TEXT NULL COMMENT 'Alamat lengkap',
    `phone` VARCHAR(20) NULL COMMENT 'Nomor telepon/WA',
    `email` VARCHAR(100) NULL,
    `npwp` VARCHAR(30) NULL COMMENT 'NPWP untuk struk',
    `logo_path` VARCHAR(255) NULL COMMENT 'Path logo untuk struk',
    `footer_text` TEXT NULL COMMENT 'Teks footer struk (promo, IG, dll)',
    `timezone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Jakarta',
    `currency` CHAR(3) NOT NULL DEFAULT 'IDR',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `settings` JSON NULL COMMENT 'Pengaturan outlet-specific (JSON)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_outlets_code` (`code`),
    KEY `idx_outlets_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master data outlet/cabang';

-- Users (Kasir, Manager, Admin, Barista, Koki, Gudang)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL COMMENT 'Outlet default user',
    `employee_id` VARCHAR(20) NULL COMMENT 'NIK/ID Karyawan',
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NULL COMMENT 'Optional, untuk admin/manager',
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password (bcrypt/argon2)',
    `pin` VARCHAR(255) NULL COMMENT 'Hashed 4-6 digit PIN untuk aksi sensitif',
    `avatar_path` VARCHAR(255) NULL,
    `role` ENUM('super_admin','manager','kasir','barista','koki','gudang') NOT NULL DEFAULT 'kasir',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `two_factor_secret` TEXT NULL COMMENT 'Untuk 2FA manager/admin',
    `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `preferences` JSON NULL COMMENT 'Preferensi UI (theme, sound, dll)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    UNIQUE KEY `uk_users_employee_id` (`employee_id`),
    KEY `idx_users_outlet_id` (`outlet_id`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`),
    CONSTRAINT `fk_users_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users: kasir, manager, barista, koki, gudang, admin';

-- Roles & Permissions (Spatie Laravel Permission compatible)
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `guard_name` VARCHAR(50) NOT NULL DEFAULT 'web',
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name_guard` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `guard_name` VARCHAR(50) NOT NULL DEFAULT 'web',
    `group` VARCHAR(50) NULL COMMENT 'Grouping: pos, kds, inventory, reports, settings',
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_name_guard` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `model_type` VARCHAR(255) NOT NULL,
    `model_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
    KEY `idx_model_has_permissions_model` (`model_id`, `model_type`),
    CONSTRAINT `fk_model_has_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `model_type` VARCHAR(255) NOT NULL,
    `model_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `model_id`, `model_type`),
    KEY `idx_model_has_roles_model` (`model_id`, `model_type`),
    CONSTRAINT `fk_model_has_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    CONSTRAINT `fk_role_has_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_has_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SHIFT MANAGEMENT
-- ============================================================================

-- Shift Definitions (Pagi, Siang, Malam, Custom)
DROP TABLE IF EXISTS `shift_definitions`;
CREATE TABLE `shift_definitions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'Pagi, Siang, Malam, Full Day',
    `code` VARCHAR(10) NOT NULL COMMENT 'PG, SG, ML, FD',
    `start_time` TIME NOT NULL COMMENT 'Jam buka shift',
    `end_time` TIME NOT NULL COMMENT 'Jam tutup shift',
    `is_overnight` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Shift melewati tengah malam',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shift_def_outlet_code` (`outlet_id`, `code`),
    KEY `idx_shift_def_outlet` (`outlet_id`),
    CONSTRAINT `fk_shift_def_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Definisi shift per outlet';

-- Shifts (Actual shift sessions)
DROP TABLE IF EXISTS `shifts`;
CREATE TABLE `shifts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `shift_definition_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Kasir yang buka shift',
    `shift_date` DATE NOT NULL COMMENT 'Tanggal shift',
    `status` ENUM('opened','closed','reconciled') NOT NULL DEFAULT 'opened',
    `opened_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu buka shift aktual',
    `closed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu tutup shift aktual',
    `reconciled_at` TIMESTAMP NULL DEFAULT NULL,
    `reconciled_by` BIGINT UNSIGNED NULL,
    `notes` TEXT NULL COMMENT 'Catatan shift',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shifts_outlet_def_date_user` (`outlet_id`, `shift_definition_id`, `shift_date`, `user_id`),
    KEY `idx_shifts_outlet_date` (`outlet_id`, `shift_date`),
    KEY `idx_shifts_status` (`status`),
    KEY `idx_shifts_user` (`user_id`),
    CONSTRAINT `fk_shifts_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_shifts_definition` FOREIGN KEY (`shift_definition_id`) REFERENCES `shift_definitions` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_shifts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_shifts_reconciled_by` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesi shift kasir harian';

-- Shift Cash Details (Denominasi buka/tutup kasir)
DROP TABLE IF EXISTS `shift_cash_details`;
CREATE TABLE `shift_cash_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('opening','closing') NOT NULL COMMENT 'Buka kasir / Tutup kasir',
    `denomination` DECIMAL(15,2) NOT NULL COMMENT 'Nominal pecahan (100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100)',
    `quantity` INT NOT NULL DEFAULT 0 COMMENT 'Jumlah lembar/koin',
    `subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`denomination` * `quantity`) STORED,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shift_cash_shift_type_denom` (`shift_id`, `type`, `denomination`),
    KEY `idx_shift_cash_shift` (`shift_id`),
    CONSTRAINT `fk_shift_cash_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail denominasi buka/tutup kasir per shift';

-- Shift Summary (Auto-calculated saat tutup shift)
DROP TABLE IF EXISTS `shift_summaries`;
CREATE TABLE `shift_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `opening_cash` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Modal awal dari shift_cash_details',
    `cash_sales` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total penjualan tunai',
    `non_cash_sales` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total non-tunai (QRIS, e-wallet, dll)',
    `refund_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total refund/void',
    `expected_cash` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Opening + Cash Sales - Refund',
    `actual_cash` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Hitung fisik dari closing details',
    `variance` DECIMAL(15,2) GENERATED ALWAYS AS (`actual_cash` - `expected_cash`) STORED COMMENT 'Selisih (plus/minus)',
    `variance_notes` TEXT NULL COMMENT 'Wajib diisi jika variance != 0',
    `transaction_count` INT NOT NULL DEFAULT 0,
    `void_count` INT NOT NULL DEFAULT 0,
    `refund_count` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shift_summary_shift` (`shift_id`),
    CONSTRAINT `fk_shift_summary_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ringkasan perhitungan shift (auto-generated)';

-- ============================================================================
-- MENU MANAGEMENT: CATEGORIES, PRODUCTS, VARIANTS, MODIFIERS
-- ============================================================================

-- Categories (Kopi, Non-Kopi, Makanan Berat, Camilan, Indomie, Rokok)
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `parent_id` BIGINT UNSIGNED NULL COMMENT 'Untuk sub-kategori (opsional)',
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(50) NULL COMMENT 'Icon name (heroicon, lucide, dll)',
    `image_path` VARCHAR(255) NULL,
    `color` VARCHAR(7) NULL COMMENT 'Hex color untuk UI',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_pos` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_kds` TINYINT(1) NOT NULL DEFAULT 1,
    `kds_station` ENUM('barista','kitchen','both') NOT NULL DEFAULT 'both' COMMENT 'Routing KDS default',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_outlet_slug` (`outlet_id`, `slug`),
    KEY `idx_categories_outlet` (`outlet_id`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_active` (`is_active`),
    CONSTRAINT `fk_categories_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori menu';

-- Products (Menu items)
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `sku` VARCHAR(50) NULL COMMENT 'SKU internal',
    `barcode` VARCHAR(50) NULL COMMENT 'Barcode untuk scan (rokok, minuman kemasan)',
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `image_path` VARCHAR(255) NULL,
    
    -- Pricing
    `base_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Harga dasar (untuk produk tanpa varian)',
    `cost_price` DECIMAL(15,2) NULL COMMENT 'HPP manual override (jika tidak pakai recipe)',
    
    -- Stock Type
    `stock_type` ENUM('finished','raw','service') NOT NULL DEFAULT 'finished' COMMENT 'finished=barang jadi, raw=bahan baku, service=jasa',
    `track_stock` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Track stok untuk produk jadi',
    `current_stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok tersedia (produk jadi)',
    `min_stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Minimum stok untuk alert',
    `unit_id` BIGINT UNSIGNED NULL COMMENT 'Satuan stok (pcs, kg, liter)',
    
    -- Flags
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_best_seller` TINYINT(1) NOT NULL DEFAULT 0,
    `is_available_pos` TINYINT(1) NOT NULL DEFAULT 1,
    `is_available_kds` TINYINT(1) NOT NULL DEFAULT 1,
    `require_modifier` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Wajib pilih modifier',
    `auto_print_kds` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Auto kirim ke KDS',
    
    -- Display
    `sort_order` INT NOT NULL DEFAULT 0,
    `preparation_time` INT NOT NULL DEFAULT 0 COMMENT 'Estimasi prep time (detik) untuk KDS',
    `calories` INT NULL,
    `allergen_info` JSON NULL COMMENT 'Info alergen: nuts, dairy, gluten, dll',
    
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_products_outlet_sku` (`outlet_id`, `sku`),
    UNIQUE KEY `uk_products_outlet_slug` (`outlet_id`, `slug`),
    KEY `idx_products_outlet` (`outlet_id`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_active` (`is_active`),
    KEY `idx_products_stock` (`track_stock`, `current_stock`),
    CONSTRAINT `fk_products_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master produk/menu';

-- Product Variants (Varian wajib: Indomie Goreng/Rebus, Kopi Panas/Dingin/Blended)
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama varian: Goreng, Rebus, Panas, Dingin, Blended',
    `code` VARCHAR(30) NULL COMMENT 'Kode singkat',
    `price_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Penyesuaian harga dari base_price (+/-)',
    `price_override` DECIMAL(15,2) NULL COMMENT 'Harga override (jika null, pakai base_price + adjustment)',
    `sku` VARCHAR(50) NULL,
    `barcode` VARCHAR(50) NULL,
    `image_path` VARCHAR(255) NULL,
    `stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok per varian (optional)',
    `track_stock` TINYINT(1) NOT NULL DEFAULT 0,
    `min_stock` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Varian default saat produk dipilih',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `kds_station` ENUM('barista','kitchen','both') NULL COMMENT 'Override routing KDS',
    `preparation_time` INT NULL COMMENT 'Override prep time',
    `recipe_id` BIGINT UNSIGNED NULL COMMENT 'Link ke recipe khusus varian ini',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_variants_product_code` (`product_id`, `code`),
    KEY `idx_product_variants_product` (`product_id`),
    KEY `idx_product_variants_active` (`is_active`),
    CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Varian wajib per produk (single select)';

-- Modifier Groups (Grup modifier: Level Gula, Es, Level Pedas, Tambah Telur)
DROP TABLE IF EXISTS `modifier_groups`;
CREATE TABLE `modifier_groups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama grup: Level Gula, Es, Topping, Level Pedas',
    `code` VARCHAR(30) NOT NULL,
    `description` TEXT NULL,
    `selection_type` ENUM('single','multiple') NOT NULL DEFAULT 'single' COMMENT 'Radio atau Checkbox',
    `is_required` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Wajib dipilih minimal 1',
    `min_selection` INT NOT NULL DEFAULT 0 COMMENT 'Minimal pilihan (untuk multiple)',
    `max_selection` INT NULL COMMENT 'Maksimal pilihan (null = unlimited)',
    `allow_custom` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bisa input custom (ex: custom sugar %)',
    `custom_label` VARCHAR(100) NULL COMMENT 'Label untuk custom input',
    `custom_price` DECIMAL(15,2) NULL COMMENT 'Harga tambahan custom',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_modifier_groups_outlet_code` (`outlet_id`, `code`),
    KEY `idx_modifier_groups_outlet` (`outlet_id`),
    CONSTRAINT `fk_modifier_groups_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Grup modifier (Level Gula, Es, Pedas, Telur, dll)';

-- Modifiers (Item dalam grup: Less Sugar, Normal, Extra Ice, Pedas 1-5, Mata Sapi, Dadar)
DROP TABLE IF EXISTS `modifiers`;
CREATE TABLE `modifiers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `modifier_group_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama modifier: Less Sugar, Normal, Extra Ice, Pedas Level 1',
    `code` VARCHAR(30) NULL,
    `price_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Harga tambahan (bisa 0)',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default terpilih',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `calories` INT NULL,
    `allergen_info` JSON NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_modifiers_group` (`modifier_group_id`),
    KEY `idx_modifiers_active` (`is_active`),
    CONSTRAINT `fk_modifiers_group` FOREIGN KEY (`modifier_group_id`) REFERENCES `modifier_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Item modifier dalam grup';

-- Product Modifier Groups (Mapping produk ke grup modifier)
DROP TABLE IF EXISTS `product_modifier_groups`;
CREATE TABLE `product_modifier_groups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `modifier_group_id` BIGINT UNSIGNED NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Override required dari grup',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_modifier_groups` (`product_id`, `modifier_group_id`),
    KEY `idx_pmg_product` (`product_id`),
    KEY `idx_pmg_group` (`modifier_group_id`),
    CONSTRAINT `fk_pmg_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pmg_group` FOREIGN KEY (`modifier_group_id`) REFERENCES `modifier_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Mapping produk ke grup modifier';

-- Toppings/Extras (Bisa qty > 1: Boba 2x, Jelly 1x, Extra Shot)
DROP TABLE IF EXISTS `toppings`;
CREATE TABLE `toppings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Boba, Jelly, Extra Shot, Whipped Cream, Keju, Sosis',
    `code` VARCHAR(30) NOT NULL,
    `description` TEXT NULL,
    `image_path` VARCHAR(255) NULL,
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Harga per porsi',
    `stock` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `track_stock` TINYINT(1) NOT NULL DEFAULT 1,
    `min_stock` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `unit_id` BIGINT UNSIGNED NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `max_qty_per_order` INT NULL COMMENT 'Max qty per order item (default 5)',
    `recipe_id` BIGINT UNSIGNED NULL COMMENT 'Recipe untuk topping ini',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_toppings_outlet_code` (`outlet_id`, `code`),
    KEY `idx_toppings_outlet` (`outlet_id`),
    KEY `idx_toppings_active` (`is_active`),
    CONSTRAINT `fk_toppings_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Topping/Extra dengan qty (multi-select)';

-- Product Toppings (Mapping produk ke topping yang tersedia)
DROP TABLE IF EXISTS `product_toppings`;
CREATE TABLE `product_toppings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `topping_id` BIGINT UNSIGNED NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `default_qty` INT NOT NULL DEFAULT 0,
    `max_qty` INT NULL COMMENT 'Override max qty untuk produk ini',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_toppings` (`product_id`, `topping_id`),
    KEY `idx_pt_product` (`product_id`),
    KEY `idx_pt_topping` (`topping_id`),
    CONSTRAINT `fk_pt_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Mapping produk ke topping yang tersedia';

-- ============================================================================
-- INVENTORY: INGREDIENTS, UNITS, RECIPES (BOM)
-- ============================================================================

-- Units of Measurement (Satuan: kg, gram, liter, ml, pcs, pack, box, sachet)
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'Kilogram, Gram, Liter, Milliliter, Piece, Pack, Box',
    `code` VARCHAR(20) NOT NULL COMMENT 'kg, g, L, ml, pcs, pack, box',
    `type` ENUM('weight','volume','count') NOT NULL DEFAULT 'count',
    `base_unit_id` BIGINT UNSIGNED NULL COMMENT 'Unit dasar untuk konversi (gram untuk weight, ml untuk volume)',
    `conversion_factor` DECIMAL(15,6) NOT NULL DEFAULT 1 COMMENT '1 kg = 1000 g (factor 1000)',
    `precision` INT NOT NULL DEFAULT 2 COMMENT 'Desimal precision',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_units_outlet_code` (`outlet_id`, `code`),
    KEY `idx_units_outlet` (`outlet_id`),
    KEY `idx_units_base` (`base_unit_id`),
    CONSTRAINT `fk_units_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_units_base` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Satuan ukuran dengan konversi';

-- Ingredients / Raw Materials (Bahan baku: Biji Kopi, Susu UHT, Gula, Indomie, Telur, Bumbu)
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(30) NOT NULL COMMENT 'Kode internal: BK001, SU001, GP001',
    `name` VARCHAR(150) NOT NULL COMMENT 'Biji Kopi Arabika, Susu UHT Full Cream, Gula Pasir',
    `category` VARCHAR(50) NULL COMMENT 'Kopi, Susu, Gula, Mie, Telur, Bumbu, Kemasan',
    `unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Satuan stok (kg, liter, pcs)',
    `purchase_unit_id` BIGINT UNSIGNED NULL COMMENT 'Satuan beli (bisa beda: beli kg, pakai gram)',
    
    -- Stock
    `current_stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok saat ini (dalam unit_id)',
    `min_stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Minimum stok untuk alert',
    `max_stock` DECIMAL(15,4) NULL COMMENT 'Maximum stok (optional)',
    `reserved_stock` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok reserved untuk PO/Production',
    
    -- Costing (Moving Average)
    `avg_cost` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Harga rata-rata per unit_id (Moving Average)',
    `last_purchase_price` DECIMAL(15,4) NULL COMMENT 'Harga beli terakhir per purchase_unit',
    `last_purchase_at` TIMESTAMP NULL,
    
    -- Supplier
    `default_supplier_id` BIGINT UNSIGNED NULL,
    `lead_time_days` INT NOT NULL DEFAULT 0 COMMENT 'Lead time untuk PO',
    
    -- Tracking
    `track_batch` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Track batch/expired date',
    `shelf_life_days` INT NULL COMMENT 'Masa simpan (hari)',
    `storage_location` VARCHAR(100) NULL COMMENT 'Lokasi gudang: Rak A1, Kulkas 1, dll',
    
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ingredients_outlet_code` (`outlet_id`, `code`),
    KEY `idx_ingredients_outlet` (`outlet_id`),
    KEY `idx_ingredients_category` (`category`),
    KEY `idx_ingredients_stock` (`current_stock`, `min_stock`),
    KEY `idx_ingredients_unit` (`unit_id`),
    CONSTRAINT `fk_ingredients_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ingredients_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_ingredients_purchase_unit` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bahan baku (Raw Materials) dengan moving average cost';

-- Recipes / Bill of Materials (BOM) - Header
DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NULL COMMENT 'Produk utama (null jika recipe untuk topping/varian)',
    `product_variant_id` BIGINT UNSIGNED NULL COMMENT 'Varian spesifik (opsional)',
    `topping_id` BIGINT UNSIGNED NULL COMMENT 'Topping (opsional)',
    `name` VARCHAR(150) NOT NULL COMMENT 'Nama recipe: Resep Kopi Susu (1 porsi)',
    `version` INT NOT NULL DEFAULT 1 COMMENT 'Versi resep',
    `yield_quantity` DECIMAL(15,4) NOT NULL DEFAULT 1 COMMENT 'Hasil produksi (1 porsi, 10 porsi, dll)',
    `yield_unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Satuan hasil (pcs, porsi)',
    `overhead_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Overhead % (listrik, gas, air, tenaga)',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_current` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Versi aktif',
    `effective_from` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `effective_to` DATE NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_recipes_outlet` (`outlet_id`),
    KEY `idx_recipes_product` (`product_id`),
    KEY `idx_recipes_variant` (`product_variant_id`),
    KEY `idx_recipes_topping` (`topping_id`),
    KEY `idx_recipes_active_current` (`is_active`, `is_current`),
    CONSTRAINT `fk_recipes_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipes_yield_unit` FOREIGN KEY (`yield_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_recipes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_recipes_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header Recipe/BOM';

-- Recipe Details (Item bahan baku dalam recipe)
DROP TABLE IF EXISTS `recipe_details`;
CREATE TABLE `recipe_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_id` BIGINT UNSIGNED NOT NULL,
    `ingredient_id` BIGINT UNSIGNED NOT NULL,
    `quantity` DECIMAL(15,4) NOT NULL COMMENT 'Qty per yield (18 gram, 150 ml, 1 pcs)',
    `unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Satuan qty (gram, ml, pcs)',
    `is_optional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bahan opsional (es batu, garnish)',
    `waste_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Persentase waste (pembuangan, 5%)',
    `notes` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_recipe_details_recipe_ingredient` (`recipe_id`, `ingredient_id`),
    KEY `idx_recipe_details_recipe` (`recipe_id`),
    KEY `idx_recipe_details_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_recipe_details_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_recipe_details_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_recipe_details_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail bahan baku per recipe';

-- Stock Movements (Log semua pergerakan stok)
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `reference_type` VARCHAR(50) NOT NULL COMMENT 'order, recipe_deduction, po_receive, opname_adjustment, waste, transfer, manual',
    `reference_id` BIGINT UNSIGNED NULL COMMENT 'ID referensi (order_id, po_id, opname_id)',
    `ingredient_id` BIGINT UNSIGNED NULL COMMENT 'Bahan baku (null jika produk jadi)',
    `product_id` BIGINT UNSIGNED NULL COMMENT 'Produk jadi (null jika bahan baku)',
    `product_variant_id` BIGINT UNSIGNED NULL,
    `movement_type` ENUM('in','out','adjustment') NOT NULL,
    `quantity` DECIMAL(15,4) NOT NULL COMMENT 'Qty dalam unit ingredient/product',
    `unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Satuan qty',
    `unit_cost` DECIMAL(15,4) NULL COMMENT 'Cost per unit saat movement',
    `total_cost` DECIMAL(15,4) GENERATED ALWAYS AS (`quantity` * COALESCE(`unit_cost`, 0)) STORED,
    `stock_before` DECIMAL(15,4) NOT NULL COMMENT 'Stok sebelum movement',
    `stock_after` DECIMAL(15,4) NOT NULL COMMENT 'Stok sesudah movement',
    `batch_number` VARCHAR(50) NULL COMMENT 'Nomor batch (jika track batch)',
    `expired_at` DATE NULL COMMENT 'Tanggal expired (jika track batch)',
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock_mov_outlet` (`outlet_id`),
    KEY `idx_stock_mov_reference` (`reference_type`, `reference_id`),
    KEY `idx_stock_mov_ingredient` (`ingredient_id`),
    KEY `idx_stock_mov_product` (`product_id`),
    KEY `idx_stock_mov_type` (`movement_type`),
    KEY `idx_stock_mov_created` (`created_at`),
    CONSTRAINT `fk_stock_mov_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_stock_mov_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_stock_mov_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_stock_mov_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_stock_mov_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_stock_mov_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log pergerakan stok (bahan baku & produk jadi)';

-- ============================================================================
-- PURCHASING: SUPPLIERS, PURCHASE ORDERS
-- ============================================================================

-- Suppliers
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `address` TEXT NULL,
    `npwp` VARCHAR(30) NULL,
    `payment_terms` INT NOT NULL DEFAULT 0 COMMENT 'Hari tempo pembayaran (0 = COD)',
    `bank_account` VARCHAR(100) NULL,
    `bank_name` VARCHAR(50) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_suppliers_outlet_code` (`outlet_id`, `code`),
    KEY `idx_suppliers_outlet` (`outlet_id`),
    CONSTRAINT `fk_suppliers_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master supplier';

-- Purchase Orders
DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE `purchase_orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    `po_number` VARCHAR(30) NOT NULL COMMENT 'PO-20260722-001',
    `status` ENUM('draft','sent','confirmed','partial_received','received','cancelled') NOT NULL DEFAULT 'draft',
    `order_date` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `expected_date` DATE NULL,
    `received_date` DATE NULL,
    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `shipping_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `received_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_po_outlet_number` (`outlet_id`, `po_number`),
    KEY `idx_po_outlet` (`outlet_id`),
    KEY `idx_po_supplier` (`supplier_id`),
    KEY `idx_po_status` (`status`),
    KEY `idx_po_date` (`order_date`),
    CONSTRAINT `fk_po_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_po_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_po_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_po_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Purchase Order ke supplier';

-- PO Items
DROP TABLE IF EXISTS `po_items`;
CREATE TABLE `po_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `po_id` BIGINT UNSIGNED NOT NULL,
    `ingredient_id` BIGINT UNSIGNED NOT NULL,
    `quantity_ordered` DECIMAL(15,4) NOT NULL COMMENT 'Qty dalam purchase_unit',
    `quantity_received` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Satuan purchase_unit',
    `unit_price` DECIMAL(15,4) NOT NULL COMMENT 'Harga per unit',
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `line_total` DECIMAL(15,2) GENERATED ALWAYS AS (
        (`quantity_ordered` * `unit_price`) - `discount_amount` + `tax_amount`
    ) STORED,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_po_items_po` (`po_id`),
    KEY `idx_po_items_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_po_items_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_po_items_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_po_items_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Item dalam Purchase Order';

-- PO Receipts (Penerimaan barang)
DROP TABLE IF EXISTS `po_receipts`;
CREATE TABLE `po_receipts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `po_id` BIGINT UNSIGNED NOT NULL,
    `receipt_number` VARCHAR(30) NOT NULL,
    `receipt_date` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `received_by` BIGINT UNSIGNED NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_po_receipts_number` (`receipt_number`),
    KEY `idx_po_receipts_po` (`po_id`),
    KEY `idx_po_receipts_date` (`receipt_date`),
    CONSTRAINT `fk_po_receipts_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_po_receipts_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Penerimaan barang PO';

-- PO Receipt Items
DROP TABLE IF EXISTS `po_receipt_items`;
CREATE TABLE `po_receipt_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `receipt_id` BIGINT UNSIGNED NOT NULL,
    `po_item_id` BIGINT UNSIGNED NOT NULL,
    `ingredient_id` BIGINT UNSIGNED NOT NULL,
    `quantity_received` DECIMAL(15,4) NOT NULL,
    `unit_id` BIGINT UNSIGNED NOT NULL,
    `unit_cost` DECIMAL(15,4) NOT NULL COMMENT 'Actual cost per unit (untuk moving average)',
    `batch_number` VARCHAR(50) NULL,
    `expired_at` DATE NULL,
    `quality_status` ENUM('good','damaged','expired','return') NOT NULL DEFAULT 'good',
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pri_receipt` (`receipt_id`),
    KEY `idx_pri_po_item` (`po_item_id`),
    KEY `idx_pri_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_pri_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `po_receipts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pri_po_item` FOREIGN KEY (`po_item_id`) REFERENCES `po_items` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pri_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pri_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Detail penerimaan per item';

-- ============================================================================
-- STOCK OPNAME
-- ============================================================================

DROP TABLE IF EXISTS `stock_opnames`;
CREATE TABLE `stock_opnames` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `opname_number` VARCHAR(30) NOT NULL COMMENT 'OPN-20260722-001',
    `type` ENUM('full','partial','cycle') NOT NULL DEFAULT 'partial' COMMENT 'Full opname, partial, cycle count',
    `status` ENUM('draft','in_progress','completed','approved') NOT NULL DEFAULT 'draft',
    `opname_date` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `completed_at` TIMESTAMP NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_opname_outlet_number` (`outlet_id`, `opname_number`),
    KEY `idx_opname_outlet` (`outlet_id`),
    KEY `idx_opname_status` (`status`),
    KEY `idx_opname_date` (`opname_date`),
    CONSTRAINT `fk_opname_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_opname_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_opname_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stock Opname header';

DROP TABLE IF EXISTS `stock_opname_items`;
CREATE TABLE `stock_opname_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `opname_id` BIGINT UNSIGNED NOT NULL,
    `ingredient_id` BIGINT UNSIGNED NULL,
    `product_id` BIGINT UNSIGNED NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `system_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok sistem saat opname',
    `physical_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Stok fisik hasil hitung',
    `variance_qty` DECIMAL(15,4) GENERATED ALWAYS AS (`physical_qty` - `system_qty`) STORED,
    `unit_id` BIGINT UNSIGNED NOT NULL,
    `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Cost per unit untuk nilai variance',
    `variance_value` DECIMAL(15,2) GENERATED ALWAYS AS (`variance_qty` * `unit_cost`) STORED,
    `batch_number` VARCHAR(50) NULL,
    `expired_at` DATE NULL,
    `notes` VARCHAR(255) NULL,
    `counted_by` BIGINT UNSIGNED NULL,
    `counted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_opname_items_opname` (`opname_id`),
    KEY `idx_opname_items_ingredient` (`ingredient_id`),
    KEY `idx_opname_items_product` (`product_id`),
    CONSTRAINT `fk_opname_items_opname` FOREIGN KEY (`opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_opname_items_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_opname_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_opname_items_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_opname_items_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_opname_items_counted_by` FOREIGN KEY (`counted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item stock opname';

-- ============================================================================
-- WASTE / WRITE-OFF
-- ============================================================================

DROP TABLE IF EXISTS `waste_logs`;
CREATE TABLE `waste_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `waste_number` VARCHAR(30) NOT NULL COMMENT 'WST-20260722-001',
    `waste_date` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `waste_time` TIME NOT NULL DEFAULT (CURRENT_TIME),
    `ingredient_id` BIGINT UNSIGNED NULL,
    `product_id` BIGINT UNSIGNED NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `quantity` DECIMAL(15,4) NOT NULL,
    `unit_id` BIGINT UNSIGNED NOT NULL,
    `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `total_cost` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED,
    `reason` ENUM('expired','spoiled','burnt','dropped','overcooked','wrong_order','sample','other') NOT NULL,
    `reason_detail` TEXT NULL,
    `photo_path` VARCHAR(255) NULL COMMENT 'Foto bukti waste (wajib untuk high value)',
    `reported_by` BIGINT UNSIGNED NOT NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_waste_outlet_number` (`outlet_id`, `waste_number`),
    KEY `idx_waste_outlet` (`outlet_id`),
    KEY `idx_waste_date` (`waste_date`),
    KEY `idx_waste_ingredient` (`ingredient_id`),
    KEY `idx_waste_product` (`product_id`),
    KEY `idx_waste_status` (`status`),
    CONSTRAINT `fk_waste_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_waste_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_waste_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_waste_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_waste_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_waste_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_waste_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log waste/kerugihan bahan baku & produk jadi';

-- ============================================================================
-- TRANSACTIONS: ORDERS, ORDER ITEMS, PAYMENTS
-- ============================================================================

-- Tables (Meja fisik)
DROP TABLE IF EXISTS `tables`;
CREATE TABLE `tables` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(20) NOT NULL COMMENT 'M1, M2, VIP1, Teras1',
    `name` VARCHAR(50) NULL COMMENT 'Meja 1, Meja VIP, Teras Depan',
    `capacity` INT NOT NULL DEFAULT 4 COMMENT 'Kapasitas orang',
    `zone` VARCHAR(50) NULL COMMENT 'Indoor, Outdoor, VIP, Rooftop',
    `position_x` INT NULL COMMENT 'Koordinat X untuk floor plan',
    `position_y` INT NULL COMMENT 'Koordinat Y untuk floor plan',
    `qr_code` VARCHAR(255) NULL COMMENT 'QR code untuk order dari meja',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tables_outlet_code` (`outlet_id`, `code`),
    KEY `idx_tables_outlet` (`outlet_id`),
    KEY `idx_tables_zone` (`zone`),
    CONSTRAINT `fk_tables_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master meja';

-- Queue Numbers (Antrian takeaway/delivery)
DROP TABLE IF EXISTS `queue_numbers`;
CREATE TABLE `queue_numbers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NULL,
    `prefix` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A, B, C, D',
    `number` INT NOT NULL COMMENT 'Running number per shift/hari',
    `full_code` VARCHAR(10) GENERATED ALWAYS AS (CONCAT(`prefix`, LPAD(`number`, 3, '0'))) STORED COMMENT 'A001, A002',
    `status` ENUM('waiting','called','served','cancelled') NOT NULL DEFAULT 'waiting',
    `called_at` TIMESTAMP NULL,
    `served_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_queue_outlet_shift_code` (`outlet_id`, `shift_id`, `full_code`),
    KEY `idx_queue_outlet` (`outlet_id`),
    KEY `idx_queue_shift` (`shift_id`),
    KEY `idx_queue_status` (`status`),
    CONSTRAINT `fk_queue_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_queue_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nomor antrian per shift';

-- Tabs / Open Bills (Simpan bil untuk dine-in)
DROP TABLE IF EXISTS `tabs`;
CREATE TABLE `tabs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `table_id` BIGINT UNSIGNED NULL COMMENT 'Meja (null jika antrian)',
    `queue_id` BIGINT UNSIGNED NULL COMMENT 'Nomor antrian (null jika meja)',
    `customer_name` VARCHAR(100) NULL COMMENT 'Nama pelanggan (optional)',
    `customer_phone` VARCHAR(20) NULL,
    `guest_count` INT NOT NULL DEFAULT 1,
    `status` ENUM('open','hold','paid','void','merged') NOT NULL DEFAULT 'open',
    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `service_charge_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `change_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `opened_by` BIGINT UNSIGNED NOT NULL,
    `opened_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `closed_at` TIMESTAMP NULL,
    `closed_by` BIGINT UNSIGNED NULL,
    `merged_from_tab_id` BIGINT UNSIGNED NULL COMMENT 'Jika tab digabung',
    `merged_to_tab_id` BIGINT UNSIGNED NULL COMMENT 'Tab tujuan penggabungan',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tabs_outlet` (`outlet_id`),
    KEY `idx_tabs_shift` (`shift_id`),
    KEY `idx_tabs_table` (`table_id`),
    KEY `idx_tabs_queue` (`queue_id`),
    KEY `idx_tabs_status` (`status`),
    KEY `idx_tabs_opened_at` (`opened_at`),
    CONSTRAINT `fk_tabs_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tabs_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tabs_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tabs_queue` FOREIGN KEY (`queue_id`) REFERENCES `queue_numbers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tabs_opened_by` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tabs_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tabs_merged_from` FOREIGN KEY (`merged_from_tab_id`) REFERENCES `tabs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tabs_merged_to` FOREIGN KEY (`merged_to_tab_id`) REFERENCES `tabs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Open Tab / Simpan Bil untuk dine-in';

-- Tab Items (Item dalam tab)
DROP TABLE IF EXISTS `tab_items`;
CREATE TABLE `tab_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tab_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL COMMENT 'Harga saat order',
    `line_subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
    `notes` VARCHAR(255) NULL COMMENT 'Catatan khusus item',
    `status` ENUM('pending','sent_to_kds','making','ready','served','void') NOT NULL DEFAULT 'pending',
    `kds_ticket_id` BIGINT UNSIGNED NULL,
    `added_by` BIGINT UNSIGNED NOT NULL,
    `added_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `served_at` TIMESTAMP NULL,
    `void_reason` VARCHAR(255) NULL,
    `voided_by` BIGINT UNSIGNED NULL,
    `voided_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tab_items_tab` (`tab_id`),
    KEY `idx_tab_items_product` (`product_id`),
    KEY `idx_tab_items_status` (`status`),
    KEY `idx_tab_items_kds_ticket` (`kds_ticket_id`),
    CONSTRAINT `fk_tab_items_tab` FOREIGN KEY (`tab_id`) REFERENCES `tabs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tab_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tab_items_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tab_items_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tab_items_voided_by` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Item dalam tab';

-- Tab Item Modifiers (Modifier yang dipilih untuk item tab)
DROP TABLE IF EXISTS `tab_item_modifiers`;
CREATE TABLE `tab_item_modifiers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tab_item_id` BIGINT UNSIGNED NOT NULL,
    `modifier_id` BIGINT UNSIGNED NOT NULL,
    `price_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tab_item_modifiers` (`tab_item_id`, `modifier_id`),
    KEY `idx_tim_tab_item` (`tab_item_id`),
    KEY `idx_tim_modifier` (`modifier_id`),
    CONSTRAINT `fk_tim_tab_item` FOREIGN KEY (`tab_item_id`) REFERENCES `tab_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tim_modifier` FOREIGN KEY (`modifier_id`) REFERENCES `modifiers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Modifier terpilih per tab item';

-- Tab Item Toppings (Topping dengan qty)
DROP TABLE IF EXISTS `tab_item_toppings`;
CREATE TABLE `tab_item_toppings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tab_item_id` BIGINT UNSIGNED NOT NULL,
    `topping_id` BIGINT UNSIGNED NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL COMMENT 'Harga topping saat order',
    `line_subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tab_item_toppings` (`tab_item_id`, `topping_id`),
    KEY `idx_tit_tab_item` (`tab_item_id`),
    KEY `idx_tit_topping` (`topping_id`),
    CONSTRAINT `fk_tit_tab_item` FOREIGN KEY (`tab_item_id`) REFERENCES `tab_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tit_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Topping terpilih per tab item (dengan qty)';

-- Orders (Transaksi final - bisa dari tab yang dibayar atau walk-in)
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `order_number` VARCHAR(30) NOT NULL COMMENT 'ORD-20260722-0001',
    `tab_id` BIGINT UNSIGNED NULL COMMENT 'Jika dari tab yang dibayar',
    `queue_id` BIGINT UNSIGNED NULL COMMENT 'Jika takeaway dengan antrian',
    `table_id` BIGINT UNSIGNED NULL COMMENT 'Jika dine-in langsung bayar',
    `customer_name` VARCHAR(100) NULL,
    `customer_phone` VARCHAR(20) NULL,
    `order_type` ENUM('dine_in','takeaway','delivery','drive_thru') NOT NULL DEFAULT 'dine_in',
    `status` ENUM('pending','paid','partial_paid','void','refunded') NOT NULL DEFAULT 'pending',
    
    -- Pricing
    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `discount_reason` VARCHAR(255) NULL,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 11.00 COMMENT 'PPN 11%',
    `service_charge_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `service_charge_rate` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `rounding_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Pembulatan',
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `change_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `outstanding_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Hutang/piutang',
    
    -- COGS & Profit
    `total_cogs` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total HPP dari recipe',
    `gross_profit` DECIMAL(15,2) GENERATED ALWAYS AS (`total_amount` - `total_cogs`) STORED,
    
    -- Metadata
    `notes` TEXT NULL,
    `cashier_id` BIGINT UNSIGNED NOT NULL,
    `voided_by` BIGINT UNSIGNED NULL,
    `voided_at` TIMESTAMP NULL,
    `void_reason` VARCHAR(255) NULL,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_orders_outlet_number` (`outlet_id`, `order_number`),
    KEY `idx_orders_outlet` (`outlet_id`),
    KEY `idx_orders_shift` (`shift_id`),
    KEY `idx_orders_tab` (`tab_id`),
    KEY `idx_orders_queue` (`queue_id`),
    KEY `idx_orders_status` (`status`),
    KEY `idx_orders_type` (`order_type`),
    KEY `idx_orders_cashier` (`cashier_id`),
    KEY `idx_orders_created` (`created_at`),
    KEY `idx_orders_completed` (`completed_at`),
    CONSTRAINT `fk_orders_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_orders_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_tab` FOREIGN KEY (`tab_id`) REFERENCES `tabs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_queue` FOREIGN KEY (`queue_id`) REFERENCES `queue_numbers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_voided_by` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Transaksi order final (paid/void/refunded)';

-- Order Items (Detail item dalam order)
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `tab_item_id` BIGINT UNSIGNED NULL COMMENT 'Referensi ke tab_item asal',
    `product_id` BIGINT UNSIGNED NOT NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `product_name` VARCHAR(150) NOT NULL COMMENT 'Snapshot nama produk',
    `variant_name` VARCHAR(100) NULL COMMENT 'Snapshot nama varian',
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL COMMENT 'Harga per unit (termasuk varian adjustment)',
    `line_subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
    `cogs_per_unit` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'HPP per unit dari recipe',
    `total_cogs` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `cogs_per_unit`) STORED,
    `notes` VARCHAR(255) NULL,
    `status` ENUM('pending','sent_to_kds','making','ready','served','void') NOT NULL DEFAULT 'served',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order` (`order_id`),
    KEY `idx_order_items_product` (`product_id`),
    KEY `idx_order_items_tab_item` (`tab_item_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_tab_item` FOREIGN KEY (`tab_item_id`) REFERENCES `tab_items` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Item dalam order transaksi';

-- Order Item Modifiers
DROP TABLE IF EXISTS `order_item_modifiers`;
CREATE TABLE `order_item_modifiers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_item_id` BIGINT UNSIGNED NOT NULL,
    `modifier_id` BIGINT UNSIGNED NOT NULL,
    `modifier_name` VARCHAR(100) NOT NULL COMMENT 'Snapshot nama modifier',
    `price_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_oim_order_item` (`order_item_id`),
    KEY `idx_oim_modifier` (`modifier_id`),
    CONSTRAINT `fk_oim_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oim_modifier` FOREIGN KEY (`modifier_id`) REFERENCES `modifiers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Modifier per order item';

-- Order Item Toppings
DROP TABLE IF EXISTS `order_item_toppings`;
CREATE TABLE `order_item_toppings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_item_id` BIGINT UNSIGNED NOT NULL,
    `topping_id` BIGINT UNSIGNED NOT NULL,
    `topping_name` VARCHAR(100) NOT NULL COMMENT 'Snapshot nama topping',
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL COMMENT 'Harga topping saat order',
    `line_subtotal` DECIMAL(15,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_oit_order_item` (`order_item_id`),
    KEY `idx_oit_topping` (`topping_id`),
    CONSTRAINT `fk_oit_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oit_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Topping per order item';

-- Payments (Pembayaran per order - bisa multiple method)
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `payment_number` VARCHAR(30) NOT NULL COMMENT 'PAY-20260722-0001',
    `method` ENUM('cash','qris_dynamic','qris_static','ewallet_gopay','ewallet_shopeepay','ewallet_dana','ewallet_ovo','edc_debit','edc_credit','hutang','voucher','other') NOT NULL,
    `provider` VARCHAR(50) NULL COMMENT 'midtrans, xendit, manual',
    `provider_transaction_id` VARCHAR(100) NULL COMMENT 'ID transaksi dari gateway',
    `provider_reference` VARCHAR(100) NULL COMMENT 'Reference number dari gateway',
    `amount` DECIMAL(15,2) NOT NULL COMMENT 'Jumlah pembayaran',
    `status` ENUM('pending','paid','failed','expired','refunded','cancelled') NOT NULL DEFAULT 'pending',
    `paid_at` TIMESTAMP NULL,
    `expired_at` TIMESTAMP NULL COMMENT 'Untuk QRIS dynamic (5 menit)',
    `qr_code_data` TEXT NULL COMMENT 'QR code string untuk QRIS dynamic',
    `qr_image_path` VARCHAR(255) NULL,
    `cash_received` DECIMAL(15,2) NULL COMMENT 'Uang diterima (untuk cash)',
    `cash_change` DECIMAL(15,2) NULL COMMENT 'Kembalian (untuk cash)',
    `ewallet_phone` VARCHAR(20) NULL COMMENT 'Nomor HP untuk e-wallet',
    `notes` TEXT NULL,
    `processed_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payments_outlet_number` (`outlet_id`, `payment_number`),
    KEY `idx_payments_outlet` (`outlet_id`),
    KEY `idx_payments_order` (`order_id`),
    KEY `idx_payments_method` (`method`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_provider_ref` (`provider_transaction_id`),
    KEY `idx_payments_created` (`created_at`),
    CONSTRAINT `fk_payments_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payments_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Pembayaran per order (multi-method support)';

-- Payment Details (Detail tambahan per payment method)
DROP TABLE IF EXISTS `payment_details`;
CREATE TABLE `payment_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_id` BIGINT UNSIGNED NOT NULL,
    `key` VARCHAR(50) NOT NULL,
    `value` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payment_details_payment_key` (`payment_id`, `key`),
    KEY `idx_payment_details_payment` (`payment_id`),
    CONSTRAINT `fk_payment_details_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Detail tambahan pembayaran (flexible key-value)';

-- ============================================================================
-- KDS (KITCHEN DISPLAY SYSTEM)
-- ============================================================================

-- KDS Stations (Barista, Kitchen, Dessert, dll)
DROP TABLE IF EXISTS `kds_stations`;
CREATE TABLE `kds_stations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'Barista, Kitchen, Dessert, Snack',
    `code` VARCHAR(20) NOT NULL COMMENT 'BAR, KIT, DES, SNK',
    `description` TEXT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `sound_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sound_new_order` VARCHAR(100) NULL COMMENT 'Path sound file order baru',
    `sound_ready` VARCHAR(100) NULL COMMENT 'Path sound file ready',
    `auto_print` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Auto print tiket thermal',
    `printer_id` BIGINT UNSIGNED NULL COMMENT 'Printer thermal untuk tiket dapur',
    `filter_categories` JSON NULL COMMENT 'Array category_id yang ditampilkan',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_kds_stations_outlet_code` (`outlet_id`, `code`),
    KEY `idx_kds_stations_outlet` (`outlet_id`),
    CONSTRAINT `fk_kds_stations_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Station KDS (Barista, Kitchen, dll)';

-- KDS Tickets (Order ticket di KDS)
DROP TABLE IF EXISTS `kds_tickets`;
CREATE TABLE `kds_tickets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `station_id` BIGINT UNSIGNED NOT NULL,
    `tab_id` BIGINT UNSIGNED NULL,
    `order_id` BIGINT UNSIGNED NULL COMMENT 'Jika sudah dibayar (takeaway)',
    `queue_id` BIGINT UNSIGNED NULL,
    `table_id` BIGINT UNSIGNED NULL,
    `ticket_number` VARCHAR(30) NOT NULL COMMENT 'TKT-20260722-0001',
    `customer_name` VARCHAR(100) NULL,
    `status` ENUM('pending','making','ready','served','void') NOT NULL DEFAULT 'pending',
    `priority` INT NOT NULL DEFAULT 0 COMMENT 'Priority urutan (0=normal, >0=urgent)',
    `received_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu terima di KDS',
    `started_at` TIMESTAMP NULL COMMENT 'Waktu mulai dibuat',
    `completed_at` TIMESTAMP NULL COMMENT 'Waktu selesai (ready)',
    `served_at` TIMESTAMP NULL COMMENT 'Waktu disajikan ke meja',
    `voided_at` TIMESTAMP NULL,
    `void_reason` VARCHAR(255) NULL,
    `prep_time_seconds` INT NULL COMMENT 'Actual prep time (completed - received)',
    `target_prep_time` INT NULL COMMENT 'Target prep time dari produk',
    `notes` TEXT NULL,
    `printed_at` TIMESTAMP NULL COMMENT 'Waktu print tiket thermal',
    `printed_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_kds_tickets_ticket_number` (`ticket_number`),
    KEY `idx_kds_tickets_outlet` (`outlet_id`),
    KEY `idx_kds_tickets_station` (`station_id`),
    KEY `idx_kds_tickets_status` (`status`),
    KEY `idx_kds_tickets_tab` (`tab_id`),
    KEY `idx_kds_tickets_order` (`order_id`),
    KEY `idx_kds_tickets_queue` (`queue_id`),
    KEY `idx_kds_tickets_received` (`received_at`),
    CONSTRAINT `fk_kds_tickets_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kds_tickets_station` FOREIGN KEY (`station_id`) REFERENCES `kds_stations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_kds_tickets_tab` FOREIGN KEY (`tab_id`) REFERENCES `tabs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_tickets_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_tickets_queue` FOREIGN KEY (`queue_id`) REFERENCES `queue_numbers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_tickets_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_tickets_printed_by` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Ticket order di KDS per station';

-- KDS Ticket Items (Item dalam ticket KDS)
DROP TABLE IF EXISTS `kds_ticket_items`;
CREATE TABLE `kds_ticket_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id` BIGINT UNSIGNED NOT NULL,
    `tab_item_id` BIGINT UNSIGNED NULL COMMENT 'Ref ke tab_item',
    `order_item_id` BIGINT UNSIGNED NULL COMMENT 'Ref ke order_item',
    `product_id` BIGINT UNSIGNED NOT NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `variant_name` VARCHAR(100) NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `status` ENUM('pending','making','ready','served','void') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL COMMENT 'Modifier + topping + catatan gabungan',
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `served_at` TIMESTAMP NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_kds_ti_ticket` (`ticket_id`),
    KEY `idx_kds_ti_status` (`status`),
    KEY `idx_kds_ti_tab_item` (`tab_item_id`),
    KEY `idx_kds_ti_order_item` (`order_item_id`),
    CONSTRAINT `fk_kds_ti_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `kds_tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kds_ti_tab_item` FOREIGN KEY (`tab_item_id`) REFERENCES `tab_items` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_ti_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_kds_ti_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_kds_ti_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Item detail dalam ticket KDS';

-- KDS Ticket Item Modifiers (Modifier di ticket KDS - denormalized untuk display cepat)
DROP TABLE IF EXISTS `kds_ticket_item_modifiers`;
CREATE TABLE `kds_ticket_item_modifiers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_item_id` BIGINT UNSIGNED NOT NULL,
    `modifier_id` BIGINT UNSIGNED NOT NULL,
    `modifier_name` VARCHAR(100) NOT NULL,
    `price_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `display_text` VARCHAR(255) NOT NULL COMMENT 'Text untuk tampil di KDS: "Gula: Normal"',
    `sort_order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_kds_tim_ticket_item` (`ticket_item_id`),
    CONSTRAINT `fk_kds_tim_ticket_item` FOREIGN KEY (`ticket_item_id`) REFERENCES `kds_ticket_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Modifier di KDS ticket item (denormalized)';

-- KDS Ticket Item Toppings
DROP TABLE IF EXISTS `kds_ticket_item_toppings`;
CREATE TABLE `kds_ticket_item_toppings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_item_id` BIGINT UNSIGNED NOT NULL,
    `topping_id` BIGINT UNSIGNED NOT NULL,
    `topping_name` VARCHAR(100) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `display_text` VARCHAR(255) NOT NULL COMMENT 'Text untuk tampil: "+ Boba (2x)"',
    `sort_order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_kds_tit_ticket_item` (`ticket_item_id`),
    CONSTRAINT `fk_kds_tit_ticket_item` FOREIGN KEY (`ticket_item_id`) REFERENCES `kds_ticket_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Topping di KDS ticket item (denormalized)';

-- ============================================================================
-- PRINTERS & PRINTING
-- ============================================================================

DROP TABLE IF EXISTS `printers`;
CREATE TABLE `printers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Kasir Utama, Barista, Kitchen, Antrian',
    `code` VARCHAR(30) NOT NULL COMMENT 'PRN-KSR, PRN-BAR, PRN-KIT, PRN-ANT',
    `type` ENUM('thermal_bluetooth','thermal_usb','thermal_network','thermal_cups','a4','label') NOT NULL,
    `connection` JSON NULL COMMENT 'Bluetooth: {mac_address, service_uuid}, USB: {vendor_id, product_id}, Network: {ip, port}',
    `paper_width` ENUM('58mm','80mm') NOT NULL DEFAULT '58mm',
    `paper_width_dots` INT NOT NULL DEFAULT 384 COMMENT '384 untuk 58mm, 576 untuk 80mm',
    `character_per_line` INT NOT NULL DEFAULT 32 COMMENT 'CPL untuk 58mm',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default printer per type',
    `print_job_type` ENUM('receipt','kitchen_ticket','queue_ticket','shift_report','label','all') NOT NULL DEFAULT 'all',
    `assigned_station_id` BIGINT UNSIGNED NULL COMMENT 'KDS station jika kitchen ticket',
    `copies` INT NOT NULL DEFAULT 1,
    `cut_paper` TINYINT(1) NOT NULL DEFAULT 1,
    `drawer_pin` INT NULL COMMENT 'Pin cash drawer (0-7)',
    `status` ENUM('online','offline','error') NOT NULL DEFAULT 'offline',
    `last_ping_at` TIMESTAMP NULL,
    `error_message` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_printers_outlet_code` (`outlet_id`, `code`),
    KEY `idx_printers_outlet` (`outlet_id`),
    KEY `idx_printers_type` (`type`),
    KEY `idx_printers_station` (`assigned_station_id`),
    CONSTRAINT `fk_printers_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_printers_station` FOREIGN KEY (`assigned_station_id`) REFERENCES `kds_stations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Konfigurasi printer thermal/Bluetooth/USB/Network';

-- Print Jobs Queue (Antrian print)
DROP TABLE IF EXISTS `print_jobs`;
CREATE TABLE `print_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `printer_id` BIGINT UNSIGNED NOT NULL,
    `job_type` ENUM('receipt','kitchen_ticket','queue_ticket','shift_report','label','test') NOT NULL,
    `reference_type` VARCHAR(50) NULL COMMENT 'order, tab, shift, queue',
    `reference_id` BIGINT UNSIGNED NULL,
    `content` LONGTEXT NOT NULL COMMENT 'ESC/POS commands atau HTML untuk PDF',
    `status` ENUM('queued','printing','completed','failed','cancelled') NOT NULL DEFAULT 'queued',
    `attempts` INT NOT NULL DEFAULT 0,
    `max_attempts` INT NOT NULL DEFAULT 3,
    `error_message` TEXT NULL,
    `printed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_print_jobs_outlet` (`outlet_id`),
    KEY `idx_print_jobs_printer` (`printer_id`),
    KEY `idx_print_jobs_status` (`status`),
    KEY `idx_print_jobs_reference` (`reference_type`, `reference_id`),
    KEY `idx_print_jobs_created` (`created_at`),
    CONSTRAINT `fk_print_jobs_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_print_jobs_printer` FOREIGN KEY (`printer_id`) REFERENCES `printers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Antrian print job (server-side fallback)';

-- Print Templates (Template struk/tiket - Blade based)
DROP TABLE IF EXISTS `print_templates`;
CREATE TABLE `print_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Struk Pelanggan, Tiket Dapur, Tiket Antrian, Laporan Shift',
    `code` VARCHAR(50) NOT NULL COMMENT 'receipt_customer, receipt_kitchen, ticket_queue, report_shift',
    `type` ENUM('receipt','kitchen_ticket','queue_ticket','shift_report','label') NOT NULL,
    `paper_width` ENUM('58mm','80mm','a4') NOT NULL DEFAULT '58mm',
    `template_content` LONGTEXT NOT NULL COMMENT 'Blade template content',
    `variables` JSON NULL COMMENT 'Daftar variable yang tersedia: {{order.order_number}}, {{item.name}}, dll',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_print_templates_outlet_code` (`outlet_id`, `code`),
    KEY `idx_print_templates_outlet` (`outlet_id`),
    KEY `idx_print_templates_type` (`type`),
    CONSTRAINT `fk_print_templates_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Template print (Blade-based) untuk struk/tiket';

-- ============================================================================
-- SETTINGS & CONFIGURATION
-- ============================================================================

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NULL COMMENT 'Null = global setting',
    `group` VARCHAR(50) NOT NULL COMMENT 'general, tax, rounding, payment, printer, kds, inventory, pos',
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL COMMENT 'JSON encoded value',
    `type` ENUM('string','integer','float','boolean','json','array') NOT NULL DEFAULT 'string',
    `label` VARCHAR(150) NULL COMMENT 'Label untuk UI admin',
    `description` TEXT NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bisa diakses via API publik',
    `validation_rules` VARCHAR(255) NULL COMMENT 'Laravel validation rules',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_outlet_group_key` (`outlet_id`, `group`, `key`),
    KEY `idx_settings_outlet` (`outlet_id`),
    KEY `idx_settings_group` (`group`),
    CONSTRAINT `fk_settings_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Pengaturan sistem (global & per outlet)';

-- Tax Rates
DROP TABLE IF EXISTS `tax_rates`;
CREATE TABLE `tax_rates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'PPN, PPh 22, Service Charge',
    `code` VARCHAR(20) NOT NULL COMMENT 'PPN, PPh22, SC',
    `rate` DECIMAL(5,2) NOT NULL COMMENT '11.00, 2.00, 5.00',
    `type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `calculation_base` ENUM('subtotal','subtotal_after_discount') NOT NULL DEFAULT 'subtotal_after_discount',
    `is_inclusive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Harga sudah termasuk pajak',
    `apply_to` ENUM('all','dine_in','takeaway','delivery') NOT NULL DEFAULT 'all',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `effective_from` DATE NOT NULL DEFAULT (CURRENT_DATE),
    `effective_to` DATE NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tax_rates_outlet_code` (`outlet_id`, `code`),
    KEY `idx_tax_rates_outlet` (`outlet_id`),
    KEY `idx_tax_rates_active` (`is_active`, `effective_from`, `effective_to`),
    CONSTRAINT `fk_tax_rates_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Tarif pajak & service charge';

-- Rounding Rules
DROP TABLE IF EXISTS `rounding_rules`;
CREATE TABLE `rounding_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'Pembulatan Rupiah, Pembulatan 100, Pembulatan 500',
    `code` VARCHAR(20) NOT NULL COMMENT 'RUP, 100, 500, 1000',
    `rounding_unit` DECIMAL(15,2) NOT NULL COMMENT '1, 100, 500, 1000',
    `method` ENUM('nearest','up','down','banker') NOT NULL DEFAULT 'nearest' COMMENT 'Bulatkan ke terdekat, ke atas, ke bawah',
    `apply_to` ENUM('total','cash_payment','change') NOT NULL DEFAULT 'total',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_rounding_rules_outlet_code` (`outlet_id`, `code`),
    KEY `idx_rounding_rules_outlet` (`outlet_id`),
    CONSTRAINT `fk_rounding_rules_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Aturan pembulatan';

-- ============================================================================
-- DISCOUNTS & PROMOTIONS
-- ============================================================================

DROP TABLE IF EXISTS `discounts`;
CREATE TABLE `discounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Diskon Member, Happy Hour, Promo Opening',
    `code` VARCHAR(30) NULL COMMENT 'Kode voucher: HAPPY20, MEMBER10',
    `type` ENUM('percentage','nominal','buy_x_get_y','combo') NOT NULL,
    `value` DECIMAL(15,2) NOT NULL COMMENT 'Persen (10) atau Nominal (5000)',
    `max_discount` DECIMAL(15,2) NULL COMMENT 'Maksimal potongan',
    `min_purchase` DECIMAL(15,2) NULL COMMENT 'Minimum beli',
    `buy_quantity` INT NULL COMMENT 'Beli X (untuk BxGy)',
    `get_quantity` INT NULL COMMENT 'Gratis Y (untuk BxGy)',
    `get_product_id` BIGINT UNSIGNED NULL COMMENT 'Produk gratis (untuk BxGy)',
    `combo_products` JSON NULL COMMENT 'Array product_id untuk combo package',
    `combo_price` DECIMAL(15,2) NULL COMMENT 'Harga combo',
    `scope` ENUM('item','order','category','product') NOT NULL DEFAULT 'order',
    `target_category_ids` JSON NULL COMMENT 'Kategori yang berlaku',
    `target_product_ids` JSON NULL COMMENT 'Produk yang berlaku',
    `requires_approval` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Butuh PIN manager',
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `start_time` TIME NULL COMMENT 'Jam mulai (untuk happy hour)',
    `end_time` TIME NULL COMMENT 'Jam selesai',
    `days_of_week` JSON NULL COMMENT '[1,2,3,4,5] = Senin-Jumat',
    `usage_limit` INT NULL COMMENT 'Limit total penggunaan',
    `usage_count` INT NOT NULL DEFAULT 0,
    `per_customer_limit` INT NULL COMMENT 'Limit per pelanggan',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_discounts_outlet_code` (`outlet_id`, `code`),
    KEY `idx_discounts_outlet` (`outlet_id`),
    KEY `idx_discounts_active` (`is_active`, `start_date`, `end_date`),
    KEY `idx_discounts_code` (`code`),
    CONSTRAINT `fk_discounts_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_discounts_get_product` FOREIGN KEY (`get_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Diskon & Promosi (Voucher, Happy Hour, BxGy, Combo)';

-- Discount Usage (Tracking penggunaan diskon)
DROP TABLE IF EXISTS `discount_usages`;
CREATE TABLE `discount_usages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `discount_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `customer_phone` VARCHAR(20) NULL,
    `discount_amount` DECIMAL(15,2) NOT NULL,
    `approved_by` BIGINT UNSIGNED NULL COMMENT 'Manager yang approve (jika butuh approval)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_discount_usages_discount` (`discount_id`),
    KEY `idx_discount_usages_order` (`order_id`),
    KEY `idx_discount_usages_customer` (`customer_phone`),
    CONSTRAINT `fk_discount_usages_discount` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_discount_usages_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_discount_usages_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Log penggunaan diskon';

-- ============================================================================
-- CUSTOMERS & LOYALTY (Optional - untuk fase lanjutan)
-- ============================================================================

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(20) NOT NULL COMMENT 'CUST-0001',
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(100) NULL,
    `birth_date` DATE NULL,
    `gender` ENUM('male','female','other') NULL,
    `address` TEXT NULL,
    `loyalty_tier` ENUM('bronze','silver','gold','platinum') NOT NULL DEFAULT 'bronze',
    `loyalty_points` INT NOT NULL DEFAULT 0,
    `total_spent` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `visit_count` INT NOT NULL DEFAULT 0,
    `last_visit_at` TIMESTAMP NULL,
    `preferred_payment` ENUM('cash','qris','ewallet','card') NULL,
    `notes` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `source` VARCHAR(50) NULL COMMENT 'walk_in, online, referral, qr_code',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_customers_outlet_code` (`outlet_id`, `code`),
    UNIQUE KEY `uk_customers_outlet_phone` (`outlet_id`, `phone`),
    KEY `idx_customers_outlet` (`outlet_id`),
    KEY `idx_customers_phone` (`phone`),
    KEY `idx_customers_tier` (`loyalty_tier`),
    CONSTRAINT `fk_customers_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Data pelanggan & loyalty';

DROP TABLE IF EXISTS `customer_visits`;
CREATE TABLE `customer_visits` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `points_earned` INT NOT NULL DEFAULT 0,
    `points_redeemed` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cust_visits_customer` (`customer_id`),
    KEY `idx_cust_visits_order` (`order_id`),
    CONSTRAINT `fk_cust_visits_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cust_visits_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Riwayat kunjungan pelanggan';

-- ============================================================================
-- AUDIT & ACTIVITY LOG
-- ============================================================================

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NULL,
    `log_name` VARCHAR(100) NULL COMMENT 'default, auth, pos, inventory, settings',
    `description` TEXT NOT NULL COMMENT 'Deskripsi aktivitas',
    `subject_type` VARCHAR(255) NULL COMMENT 'Model class: App\\Models\\Order',
    `subject_id` BIGINT UNSIGNED NULL,
    `causer_type` VARCHAR(255) NULL COMMENT 'User class',
    `causer_id` BIGINT UNSIGNED NULL COMMENT 'User ID yang melakukan aksi',
    `properties` JSON NULL COMMENT 'Detail perubahan (old/new values)',
    `batch_uuid` CHAR(36) NULL COMMENT 'Grouping untuk bulk actions',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activity_logs_outlet` (`outlet_id`),
    KEY `idx_activity_logs_log_name` (`log_name`),
    KEY `idx_activity_logs_subject` (`subject_type`, `subject_id`),
    KEY `idx_activity_logs_causer` (`causer_type`, `causer_id`),
    KEY `idx_activity_logs_batch` (`batch_uuid`),
    KEY `idx_activity_logs_created` (`created_at`),
    CONSTRAINT `fk_activity_logs_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Activity log (Spatie Laravel Activitylog compatible)';

-- ============================================================================
-- NOTIFICATIONS & ALERTS
-- ============================================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NULL,
    `type` VARCHAR(100) NOT NULL COMMENT 'low_stock, waste_alert, shift_reminder, payment_failed, new_order',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON NULL COMMENT 'Additional data: {ingredient_id: 1, current_stock: 5}',
    `priority` ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
    `channels` JSON NOT NULL COMMENT '["database","whatsapp","email","push"]',
    `status` ENUM('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
    `sent_at` TIMESTAMP NULL,
    `read_at` TIMESTAMP NULL,
    `recipient_type` ENUM('all','role','user') NOT NULL DEFAULT 'all',
    `recipient_roles` JSON NULL COMMENT '["manager","gudang"]',
    `recipient_users` JSON NULL COMMENT '[1,2,3]',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_outlet` (`outlet_id`),
    KEY `idx_notifications_type` (`type`),
    KEY `idx_notifications_status` (`status`),
    KEY `idx_notifications_priority` (`priority`),
    KEY `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Notifikasi sistem';

-- ============================================================================
-- INTEGRATIONS (Midtrans, Xendit, WhatsApp Gateway, dll)
-- ============================================================================

DROP TABLE IF EXISTS `integrations`;
CREATE TABLE `integrations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'midtrans, xendit, fonnte, wablas, whatsapp_business',
    `type` ENUM('payment_gateway','whatsapp_gateway','shipping','accounting','pos_sync') NOT NULL,
    `config` JSON NOT NULL COMMENT 'Encrypted sensitive data: {client_key, server_key, is_production: true}',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Primary untuk tipe tersebut',
    `test_mode` TINYINT(1) NOT NULL DEFAULT 1,
    `last_sync_at` TIMESTAMP NULL,
    `last_error` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_integrations_outlet_name` (`outlet_id`, `name`),
    KEY `idx_integrations_outlet` (`outlet_id`),
    KEY `idx_integrations_type` (`type`),
    CONSTRAINT `fk_integrations_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Konfigurasi integrasi eksternal (encrypted)';

-- ============================================================================
-- REPORTS & ANALYTICS (Materialized Views / Summary Tables)
-- ============================================================================

-- Daily Sales Summary (Pre-aggregated untuk laporan cepat)
DROP TABLE IF EXISTS `daily_sales_summaries`;
CREATE TABLE `daily_sales_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NULL,
    `summary_date` DATE NOT NULL,
    `period_type` ENUM('daily','shift') NOT NULL DEFAULT 'daily',
    
    -- Transaction counts
    `total_orders` INT NOT NULL DEFAULT 0,
    `paid_orders` INT NOT NULL DEFAULT 0,
    `void_orders` INT NOT NULL DEFAULT 0,
    `refunded_orders` INT NOT NULL DEFAULT 0,
    `dine_in_orders` INT NOT NULL DEFAULT 0,
    `takeaway_orders` INT NOT NULL DEFAULT 0,
    `delivery_orders` INT NOT NULL DEFAULT 0,
    
    -- Revenue
    `gross_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `net_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_discount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_tax` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_service_charge` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_rounding` DECIMAL(15,2) NOT NULL DEFAULT 0,
    
    -- Payment breakdown
    `cash_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `qris_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `ewallet_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `edc_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `hutang_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `voucher_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    
    -- COGS & Profit
    `total_cogs` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `gross_profit` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `gross_margin_pct` DECIMAL(5,2) GENERATED ALWAYS AS (
        CASE WHEN `net_revenue` > 0 THEN (`gross_profit` / `net_revenue`) * 100 ELSE 0 END
    ) STORED,
    
    -- Average
    `avg_ticket_size` DECIMAL(15,2) GENERATED ALWAYS AS (
        CASE WHEN `paid_orders` > 0 THEN `net_revenue` / `paid_orders` ELSE 0 END
    ) STORED,
    
    -- Items
    `total_items_sold` INT NOT NULL DEFAULT 0,
    `unique_products_sold` INT NOT NULL DEFAULT 0,
    
    `computed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_daily_sales_outlet_date_shift` (`outlet_id`, `summary_date`, `shift_id`, `period_type`),
    KEY `idx_daily_sales_outlet_date` (`outlet_id`, `summary_date`),
    KEY `idx_daily_sales_shift` (`shift_id`),
    CONSTRAINT `fk_daily_sales_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_daily_sales_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Ringkasan penjualan harian per shift (pre-aggregated)';

-- Product Sales Summary
DROP TABLE IF EXISTS `product_sales_summaries`;
CREATE TABLE `product_sales_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `product_variant_id` BIGINT UNSIGNED NULL,
    `summary_date` DATE NOT NULL,
    `period_type` ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
    `quantity_sold` INT NOT NULL DEFAULT 0,
    `gross_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_discount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `net_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_cogs` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `gross_profit` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `avg_selling_price` DECIMAL(15,2) GENERATED ALWAYS AS (
        CASE WHEN `quantity_sold` > 0 THEN `net_revenue` / `quantity_sold` ELSE 0 END
    ) STORED,
    `computed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_sales_outlet_product_date` (`outlet_id`, `product_id`, `product_variant_id`, `summary_date`, `period_type`),
    KEY `idx_product_sales_outlet_date` (`outlet_id`, `summary_date`),
    KEY `idx_product_sales_product` (`product_id`),
    CONSTRAINT `fk_product_sales_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_sales_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_sales_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Ringkasan penjualan per produk (pre-aggregated)';

-- Cashier Performance Summary
DROP TABLE IF EXISTS `cashier_performance_summaries`;
CREATE TABLE `cashier_performance_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `summary_date` DATE NOT NULL,
    `orders_handled` INT NOT NULL DEFAULT 0,
    `items_sold` INT NOT NULL DEFAULT 0,
    `gross_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `net_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `void_count` INT NOT NULL DEFAULT 0,
    `void_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `refund_count` INT NOT NULL DEFAULT 0,
    `refund_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `discount_count` INT NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `avg_ticket_size` DECIMAL(15,2) GENERATED ALWAYS AS (
        CASE WHEN `orders_handled` > 0 THEN `net_revenue` / `orders_handled` ELSE 0 END
    ) STORED,
    `cash_handled` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `non_cash_handled` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `shift_duration_minutes` INT NOT NULL DEFAULT 0,
    `orders_per_hour` DECIMAL(5,2) GENERATED ALWAYS AS (
        CASE WHEN `shift_duration_minutes` > 0 THEN (`orders_handled` * 60) / `shift_duration_minutes` ELSE 0 END
    ) STORED,
    `computed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cashier_perf_outlet_user_shift_date` (`outlet_id`, `user_id`, `shift_id`, `summary_date`),
    KEY `idx_cashier_perf_outlet_date` (`outlet_id`, `summary_date`),
    KEY `idx_cashier_perf_user` (`user_id`),
    CONSTRAINT `fk_cashier_perf_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cashier_perf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cashier_perf_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Performa kasir per shift (pre-aggregated)';

-- Table Turnover Summary
DROP TABLE IF EXISTS `table_turnover_summaries`;
CREATE TABLE `table_turnover_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `outlet_id` BIGINT UNSIGNED NOT NULL,
    `table_id` BIGINT UNSIGNED NOT NULL,
    `summary_date` DATE NOT NULL,
    `total_sessions` INT NOT NULL DEFAULT 0 COMMENT 'Jumlah tab/order per meja',
    `total_guests` INT NOT NULL DEFAULT 0,
    `total_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_duration_minutes` INT NOT NULL DEFAULT 0,
    `avg_duration_minutes` DECIMAL(5,2) GENERATED ALWAYS AS (
        CASE WHEN `total_sessions` > 0 THEN `total_duration_minutes` / `total_sessions` ELSE 0 END
    ) STORED,
    `avg_revenue_per_session` DECIMAL(15,2) GENERATED ALWAYS AS (
        CASE WHEN `total_sessions` > 0 THEN `total_revenue` / `total_sessions` ELSE 0 END
    ) STORED,
    `occupancy_rate` DECIMAL(5,2) NULL COMMENT 'Persentase waktu meja terisi',
    `computed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_table_turnover_outlet_table_date` (`outlet_id`, `table_id`, `summary_date`),
    KEY `idx_table_turnover_outlet_date` (`outlet_id`, `summary_date`),
    KEY `idx_table_turnover_table` (`table_id`),
    CONSTRAINT `fk_table_turnover_outlet` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_table_turnover_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT'Turnover meja per hari';

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View: Current Stock Status (Ingredients + Products)
CREATE OR REPLACE VIEW `v_current_stock_status` AS
SELECT 
    'ingredient' AS `type`,
    i.`id` AS `item_id`,
    i.`code` AS `item_code`,
    i.`name` AS `item_name`,
    i.`current_stock` AS `stock`,
    i.`min_stock` AS `min_stock`,
    u.`code` AS `unit`,
    i.`avg_cost` AS `unit_cost`,
    (i.`current_stock` * i.`avg_cost`) AS `stock_value`,
    CASE 
        WHEN i.`current_stock` <= 0 THEN 'empty'
        WHEN i.`current_stock` <= i.`min_stock` THEN 'low'
        WHEN i.`current_stock` <= (i.`min_stock` * 1.5) THEN 'warning'
        ELSE 'ok'
    END AS `stock_status`,
    i.`outlet_id`
FROM `ingredients` i
JOIN `units` u ON i.`unit_id` = u.`id`
WHERE i.`is_active` = 1 AND i.`deleted_at` IS NULL

UNION ALL

SELECT 
    'product' AS `type`,
    p.`id` AS `item_id`,
    p.`sku` AS `item_code`,
    p.`name` AS `item_name`,
    p.`current_stock` AS `stock`,
    p.`min_stock` AS `min_stock`,
    u.`code` AS `unit`,
    p.`cost_price` AS `unit_cost`,
    (p.`current_stock` * COALESCE(p.`cost_price`, 0)) AS `stock_value`,
    CASE 
        WHEN p.`current_stock` <= 0 THEN 'empty'
        WHEN p.`current_stock` <= p.`min_stock` THEN 'low'
        WHEN p.`current_stock` <= (p.`min_stock` * 1.5) THEN 'warning'
        ELSE 'ok'
    END AS `stock_status`,
    p.`outlet_id`
FROM `products` p
LEFT JOIN `units` u ON p.`unit_id` = u.`id`
WHERE p.`is_active` = 1 AND p.`track_stock` = 1 AND p.`deleted_at` IS NULL;

-- View: Active Tabs with Details
CREATE OR REPLACE VIEW `v_active_tabs` AS
SELECT 
    t.`id`,
    t.`outlet_id`,
    t.`shift_id`,
    t.`table_id`,
    tbl.`code` AS `table_code`,
    tbl.`name` AS `table_name`,
    t.`queue_id`,
    qn.`full_code` AS `queue_code`,
    t.`customer_name`,
    t.`guest_count`,
    t.`status`,
    t.`subtotal`,
    t.`discount_amount`,
    t.`tax_amount`,
    t.`service_charge_amount`,
    t.`total_amount`,
    t.`paid_amount`,
    (t.`total_amount` - t.`paid_amount`) AS `outstanding`,
    t.`opened_at`,
    TIMESTAMPDIFF(MINUTE, t.`opened_at`, NOW()) AS `duration_minutes`,
    COUNT(ti.`id`) AS `item_count`,
    u.`name` AS `opened_by_name`
FROM `tabs` t
LEFT JOIN `tables` tbl ON t.`table_id` = tbl.`id`
LEFT JOIN `queue_numbers` qn ON t.`queue_id` = qn.`id`
LEFT JOIN `tab_items` ti ON t.`id` = ti.`tab_id` AND ti.`status` != 'void'
LEFT JOIN `users` u ON t.`opened_by` = u.`id`
WHERE t.`status` IN ('open', 'hold')
GROUP BY t.`id`;

-- View: KDS Dashboard Summary
CREATE OR REPLACE VIEW `v_kds_dashboard` AS
SELECT 
    ks.`id` AS `station_id`,
    ks.`name` AS `station_name`,
    ks.`code` AS `station_code`,
    COUNT(CASE WHEN kt.`status` = 'pending' THEN 1 END) AS `pending_count`,
    COUNT(CASE WHEN kt.`status` = 'making' THEN 1 END) AS `making_count`,
    COUNT(CASE WHEN kt.`status` = 'ready' THEN 1 END) AS `ready_count`,
    AVG(CASE WHEN kt.`status` IN ('making','ready') AND kt.`prep_time_seconds` IS NOT NULL THEN kt.`prep_time_seconds` END) AS `avg_prep_time_seconds`,
    MAX(CASE WHEN kt.`status` = 'pending' THEN kt.`received_at` END) AS `oldest_pending_at`
FROM `kds_stations` ks
LEFT JOIN `kds_tickets` kt ON ks.`id` = kt.`station_id` AND kt.`status` IN ('pending','making','ready')
WHERE ks.`is_active` = 1
GROUP BY ks.`id`;

-- View: Product with Calculated HPP
CREATE OR REPLACE VIEW `v_product_hpp` AS
SELECT 
    p.`id` AS `product_id`,
    p.`name` AS `product_name`,
    p.`base_price` AS `selling_price`,
    p.`stock_type`,
    COALESCE(r.`total_hpp`, 0) AS `calculated_hpp`,
    COALESCE(r.`total_hpp_with_overhead`, 0) AS `hpp_with_overhead`,
    (p.`base_price` - COALESCE(r.`total_hpp_with_overhead`, 0)) AS `gross_margin`,
    CASE 
        WHEN p.`base_price` > 0 THEN ((p.`base_price` - COALESCE(r.`total_hpp_with_overhead`, 0)) / p.`base_price`) * 100
        ELSE 0
    END AS `margin_percentage`,
    r.`recipe_id`,
    r.`version` AS `recipe_version`
FROM `products` p
LEFT JOIN (
    SELECT 
        r.`product_id`,
        r.`id` AS `recipe_id`,
        r.`version`,
        SUM(rd.`quantity` * COALESCE(i.`avg_cost`, 0) * (1 + COALESCE(rd.`waste_percentage`, 0) / 100)) AS `total_hpp`,
        SUM(rd.`quantity` * COALESCE(i.`avg_cost`, 0) * (1 + COALESCE(rd.`waste_percentage`, 0) / 100)) * (1 + COALESCE(r.`overhead_percentage`, 0) / 100) AS `total_hpp_with_overhead`
    FROM `recipes` r
    JOIN `recipe_details` rd ON r.`id` = rd.`recipe_id`
    JOIN `ingredients` i ON rd.`ingredient_id` = i.`id`
    WHERE r.`is_active` = 1 AND r.`is_current` = 1
    GROUP BY r.`product_id`, r.`id`, r.`version`
) r ON p.`id` = r.`product_id`
WHERE p.`is_active` = 1 AND p.`deleted_at` IS NULL;

-- ============================================================================
-- STORED PROCEDURES & FUNCTIONS
-- ============================================================================

DELIMITER //

-- Function: Convert quantity between units
DROP FUNCTION IF EXISTS `convert_unit_quantity`;
CREATE FUNCTION `convert_unit_quantity`(
    p_quantity DECIMAL(15,4),
    p_from_unit_id BIGINT UNSIGNED,
    p_to_unit_id BIGINT UNSIGNED
) RETURNS DECIMAL(15,4)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_factor_from DECIMAL(15,6) DEFAULT 1;
    DECLARE v_factor_to DECIMAL(15,6) DEFAULT 1;
    DECLARE v_base_from BIGINT UNSIGNED;
    DECLARE v_base_to BIGINT UNSIGNED;
    
    IF p_from_unit_id = p_to_unit_id THEN
        RETURN p_quantity;
    END IF;
    
    -- Get conversion factor to base unit for from_unit
    SELECT `conversion_factor`, `base_unit_id` INTO v_factor_from, v_base_from
    FROM `units` WHERE `id` = p_from_unit_id;
    
    -- Get conversion factor to base unit for to_unit
    SELECT `conversion_factor`, `base_unit_id` INTO v_factor_to, v_base_to
    FROM `units` WHERE `id` = p_to_unit_id;
    
    -- Both must have same base unit
    IF v_base_from != v_base_to THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot convert between different unit types (weight vs volume vs count)';
    END IF;
    
    -- Convert: quantity * (from_factor / to_factor)
    RETURN p_quantity * (v_factor_from / v_factor_to);
END//

-- Function: Calculate Recipe HPP
DROP FUNCTION IF EXISTS `calculate_recipe_hpp`;
CREATE FUNCTION `calculate_recipe_hpp`(
    p_recipe_id BIGINT UNSIGNED
) RETURNS DECIMAL(15,4)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_total_hpp DECIMAL(15,4) DEFAULT 0;
    DECLARE v_overhead_pct DECIMAL(5,2) DEFAULT 0;
    
    SELECT `overhead_percentage` INTO v_overhead_pct
    FROM `recipes` WHERE `id` = p_recipe_id;
    
    SELECT COALESCE(SUM(
        rd.`quantity` * 
        COALESCE(i.`avg_cost`, 0) * 
        (1 + COALESCE(rd.`waste_percentage`, 0) / 100)
    ), 0) INTO v_total_hpp
    FROM `recipe_details` rd
    JOIN `ingredients` i ON rd.`ingredient_id` = i.`id`
    WHERE rd.`recipe_id` = p_recipe_id;
    
    RETURN v_total_hpp * (1 + v_overhead_pct / 100);
END//

-- Procedure: Deduct Stock for Order (Auto-deduction bahan baku)
DROP PROCEDURE IF EXISTS `deduct_stock_for_order`;
CREATE PROCEDURE `deduct_stock_for_order`(
    IN p_order_id BIGINT UNSIGNED,
    IN p_user_id BIGINT UNSIGNED
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_ingredient_id BIGINT UNSIGNED;
    DECLARE v_qty DECIMAL(15,4);
    DECLARE v_unit_id BIGINT UNSIGNED;
    DECLARE v_unit_cost DECIMAL(15,4);
    DECLARE v_stock_before DECIMAL(15,4);
    DECLARE v_recipe_id BIGINT UNSIGNED;
    
    DECLARE cur CURSOR FOR
        SELECT 
            rd.`ingredient_id`,
            SUM(rd.`quantity` * oi.`quantity` * (1 + COALESCE(rd.`waste_percentage`, 0) / 100)) AS `total_qty`,
            rd.`unit_id`,
            i.`avg_cost` AS `unit_cost`,
            i.`current_stock` AS `stock_before`,
            r.`id` AS `recipe_id`
        FROM `order_items` oi
        JOIN `products` p ON oi.`product_id` = p.`id`
        JOIN `recipes` r ON oi.`product_id` = r.`product_id` AND r.`is_current` = 1
        JOIN `recipe_details` rd ON r.`id` = rd.`recipe_id`
        JOIN `ingredients` i ON rd.`ingredient_id` = i.`id`
        WHERE oi.`order_id` = p_order_id
        AND oi.`status` != 'void'
        AND p.`stock_type` != 'service'
        GROUP BY rd.`ingredient_id`, rd.`unit_id`, i.`avg_cost`, i.`current_stock`, r.`id`;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    START TRANSACTION;
    
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_ingredient_id, v_qty, v_unit_id, v_unit_cost, v_stock_before, v_recipe_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Update ingredient stock
        UPDATE `ingredients` 
        SET `current_stock` = `current_stock` - v_qty,
            `updated_at` = CURRENT_TIMESTAMP
        WHERE `id` = v_ingredient_id;
        
        -- Log stock movement
        INSERT INTO `stock_movements` (
            `outlet_id`, `reference_type`, `reference_id`,
            `ingredient_id`, `movement_type`, `quantity`, `unit_id`,
            `unit_cost`, `stock_before`, `stock_after`,
            `created_by`
        ) SELECT 
            o.`outlet_id`, 'recipe_deduction', p_order_id,
            v_ingredient_id, 'out', v_qty, v_unit_id,
            v_unit_cost, v_stock_before, v_stock_before - v_qty,
            p_user_id
        FROM `orders` o WHERE o.`id` = p_order_id;
        
    END LOOP;
    
    CLOSE cur;
    
    COMMIT;
END//

-- Procedure: Open Shift
DROP PROCEDURE IF EXISTS `open_shift`;
CREATE PROCEDURE `open_shift`(
    IN p_outlet_id BIGINT UNSIGNED,
    IN p_shift_definition_id BIGINT UNSIGNED,
    IN p_user_id BIGINT UNSIGNED,
    IN p_shift_date DATE,
    IN p_opening_cash_json JSON, -- [{"denomination": 100000, "quantity": 2}, ...]
    IN p_notes TEXT
)
BEGIN
    DECLARE v_shift_id BIGINT UNSIGNED;
    DECLARE v_total_opening DECIMAL(15,2) DEFAULT 0;
    
    START TRANSACTION;
    
    -- Create shift
    INSERT INTO `shifts` (`outlet_id`, `shift_definition_id`, `user_id`, `shift_date`, `status`, `opened_at`, `notes`)
    VALUES (p_outlet_id, p_shift_definition_id, p_user_id, p_shift_date, 'opened', NOW(), p_notes);
    
    SET v_shift_id = LAST_INSERT_ID();
    
    -- Insert cash details
    INSERT INTO `shift_cash_details` (`shift_id`, `type`, `denomination`, `quantity`)
    SELECT v_shift_id, 'opening', 
           JSON_UNQUOTE(JSON_EXTRACT(item, '$.denomination')),
           JSON_UNQUOTE(JSON_EXTRACT(item, '$.quantity'))
    FROM JSON_TABLE(p_opening_cash_json, '$[*]' COLUMNS (
        denomination DECIMAL(15,2) PATH '$.denomination',
        quantity INT PATH '$.quantity'
    )) AS jt;
    
    -- Calculate total opening
    SELECT SUM(`denomination` * `quantity`) INTO v_total_opening
    FROM `shift_cash_details` WHERE `shift_id` = v_shift_id AND `type` = 'opening';
    
    -- Create shift summary
    INSERT INTO `shift_summaries` (`shift_id`, `opening_cash`, `expected_cash`)
    VALUES (v_shift_id, v_total_opening, v_total_opening);
    
    COMMIT;
    
    SELECT v_shift_id AS `shift_id`;
END//

-- Procedure: Close Shift
DROP PROCEDURE IF EXISTS `close_shift`;
CREATE PROCEDURE `close_shift`(
    IN p_shift_id BIGINT UNSIGNED,
    IN p_user_id BIGINT UNSIGNED,
    IN p_closing_cash_json JSON,
    IN p_variance_notes TEXT
)
BEGIN
    DECLARE v_expected_cash DECIMAL(15,2);
    DECLARE v_actual_cash DECIMAL(15,2);
    DECLARE v_variance DECIMAL(15,2);
    DECLARE v_cash_sales DECIMAL(15,2);
    DECLARE v_non_cash_sales DECIMAL(15,2);
    DECLARE v_refund_amount DECIMAL(15,2);
    DECLARE v_transaction_count INT;
    DECLARE v_void_count INT;
    DECLARE v_refund_count INT;
    
    START TRANSACTION;
    
    -- Get shift summary data
    SELECT 
        ss.`opening_cash`,
        COALESCE(SUM(CASE WHEN p.`method` = 'cash' THEN p.`amount` END), 0),
        COALESCE(SUM(CASE WHEN p.`method` != 'cash' THEN p.`amount` END), 0),
        COALESCE(SUM(CASE WHEN o.`status` = 'refunded' THEN o.`total_amount` END), 0),
        COUNT(DISTINCT o.`id`),
        COUNT(DISTINCT CASE WHEN o.`status` = 'void' THEN o.`id` END),
        COUNT(DISTINCT CASE WHEN o.`status` = 'refunded' THEN o.`id` END)
    INTO 
        v_expected_cash, v_cash_sales, v_non_cash_sales, v_refund_amount,
        v_transaction_count, v_void_count, v_refund_count
    FROM `shifts` s
    LEFT JOIN `shift_summaries` ss ON s.`id` = ss.`shift_id`
    LEFT JOIN `orders` o ON o.`shift_id` = s.`id` AND o.`status` IN ('paid', 'void', 'refunded')
    LEFT JOIN `payments` p ON p.`order_id` = o.`id` AND p.`status` = 'paid'
    WHERE s.`id` = p_shift_id
    GROUP BY s.`id`, ss.`opening_cash`;
    
    SET v_expected_cash = v_expected_cash + v_cash_sales - v_refund_amount;
    
    -- Insert closing cash details
    INSERT INTO `shift_cash_details` (`shift_id`, `type`, `denomination`, `quantity`)
    SELECT p_shift_id, 'closing', 
           JSON_UNQUOTE(JSON_EXTRACT(item, '$.denomination')),
           JSON_UNQUOTE(JSON_EXTRACT(item, '$.quantity'))
    FROM JSON_TABLE(p_closing_cash_json, '$[*]' COLUMNS (
        denomination DECIMAL(15,2) PATH '$.denomination',
        quantity INT PATH '$.quantity'
    )) AS jt;
    
    -- Calculate actual cash
    SELECT SUM(`denomination` * `quantity`) INTO v_actual_cash
    FROM `shift_cash_details` WHERE `shift_id` = p_shift_id AND `type` = 'closing';
    
    SET v_variance = v_actual_cash - v_expected_cash;
    
    -- Update shift
    UPDATE `shifts` 
    SET `status` = 'closed', `closed_at` = NOW(), `closed_by` = p_user_id
    WHERE `id` = p_shift_id;
    
    -- Update shift summary
    UPDATE `shift_summaries`
    SET `cash_sales` = v_cash_sales,
        `non_cash_sales` = v_non_cash_sales,
        `refund_amount` = v_refund_amount,
        `expected_cash` = v_expected_cash,
        `actual_cash` = v_actual_cash,
        `variance_notes` = p_variance_notes,
        `transaction_count` = v_transaction_count,
        `void_count` = v_void_count,
        `refund_count` = v_refund_count
    WHERE `shift_id` = p_shift_id;
    
    COMMIT;
    
    SELECT v_variance AS `variance`;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Trigger: Auto-update product current_stock on order_item insert (produk jadi)
DELIMITER //
CREATE TRIGGER `trg_order_item_after_insert` 
AFTER INSERT ON `order_items`
FOR EACH ROW
BEGIN
    IF NEW.`status` != 'void' THEN
        UPDATE `products` 
        SET `current_stock` = `current_stock` - NEW.`quantity`,
            `updated_at` = CURRENT_TIMESTAMP
        WHERE `id` = NEW.`product_id` AND `track_stock` = 1 AND `stock_type` = 'finished';
        
        -- Log stock movement
        INSERT INTO `stock_movements` (
            `outlet_id`, `reference_type`, `reference_id`,
            `product_id`, `movement_type`, `quantity`, `unit_id`,
            `unit_cost`, `stock_before`, `stock_after`,
            `created_by`
        ) SELECT 
            o.`outlet_id`, 'order', NEW.`order_id`,
            NEW.`product_id`, 'out', NEW.`quantity`, p.`unit_id`,
            p.`cost_price`, p.`current_stock` + NEW.`quantity`, p.`current_stock`,
            o.`cashier_id`
        FROM `orders` o
        JOIN `products` p ON p.`id` = NEW.`product_id`
        WHERE o.`id` = NEW.`order_id`;
    END IF;
END//

-- Trigger: Auto-update tab totals
CREATE TRIGGER `trg_tab_item_after_insert`
AFTER INSERT ON `tab_items`
FOR EACH ROW
BEGIN
    UPDATE `tabs` 
    SET `subtotal` = (
        SELECT COALESCE(SUM(`quantity` * `unit_price`), 0)
        FROM `tab_items` WHERE `tab_id` = NEW.`tab_id` AND `status` != 'void'
    ),
    `total_amount` = (
        SELECT COALESCE(SUM(`quantity` * `unit_price`), 0)
        FROM `tab_items` WHERE `tab_id` = NEW.`tab_id` AND `status` != 'void'
    ) - `discount_amount` + `tax_amount` + `service_charge_amount`,
    `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = NEW.`tab_id`;
END//

CREATE TRIGGER `trg_tab_item_after_update`
AFTER UPDATE ON `tab_items`
FOR EACH ROW
BEGIN
    UPDATE `tabs` 
    SET `subtotal` = (
        SELECT COALESCE(SUM(`quantity` * `unit_price`), 0)
        FROM `tab_items` WHERE `tab_id` = NEW.`tab_id` AND `status` != 'void'
    ),
    `total_amount` = (
        SELECT COALESCE(SUM(`quantity` * `unit_price`), 0)
        FROM `tab_items` WHERE `tab_id` = NEW.`tab_id` AND `status` != 'void'
    ) - `discount_amount` + `tax_amount` + `service_charge_amount`,
    `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = NEW.`tab_id`;
END//

-- Trigger: Tab item modifier price adjustment
CREATE TRIGGER `trg_tab_item_modifier_after_insert`
AFTER INSERT ON `tab_item_modifiers`
FOR EACH ROW
BEGIN
    UPDATE `tab_items`
    SET `unit_price` = `unit_price` + NEW.`price_adjustment`,
        `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = NEW.`tab_item_id`;
END//

CREATE TRIGGER `trg_tab_item_topping_after_insert`
AFTER INSERT ON `tab_item_toppings`
FOR EACH ROW
BEGIN
    UPDATE `tab_items`
    SET `unit_price` = `unit_price` + (NEW.`quantity` * NEW.`unit_price`),
        `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = NEW.`tab_item_id`;
END//

DELIMITER ;

-- ============================================================================
-- INDEXES FOR PERFORMANCE (Additional)
-- ============================================================================

-- Composite indexes for common queries
ALTER TABLE `orders` ADD INDEX `idx_orders_outlet_status_date` (`outlet_id`, `status`, `created_at`);
ALTER TABLE `orders` ADD INDEX `idx_orders_outlet_type_date` (`outlet_id`, `order_type`, `created_at`);
ALTER TABLE `tab_items` ADD INDEX `idx_tab_items_tab_status` (`tab_id`, `status`);
ALTER TABLE `kds_tickets` ADD INDEX `idx_kds_tickets_station_status_received` (`station_id`, `status`, `received_at`);
ALTER TABLE `stock_movements` ADD INDEX `idx_stock_mov_outlet_type_date` (`outlet_id`, `movement_type`, `created_at`);
ALTER TABLE `ingredients` ADD INDEX `idx_ingredients_outlet_category_stock` (`outlet_id`, `category`, `current_stock`);
ALTER TABLE `products` ADD INDEX `idx_products_outlet_category_active` (`outlet_id`, `category_id`, `is_active`);
ALTER TABLE `payments` ADD INDEX `idx_payments_outlet_method_date` (`outlet_id`, `method`, `created_at`);

-- ============================================================================
-- SAMPLE DATA SEEDS (Minimal for testing)
-- ============================================================================

-- Default Units
INSERT INTO `units` (`outlet_id`, `name`, `code`, `type`, `base_unit_id`, `conversion_factor`, `precision`, `sort_order`) VALUES
(1, 'Kilogram', 'kg', 'weight', NULL, 1, 3, 1),
(1, 'Gram', 'g', 'weight', 1, 1000, 0, 2),
(1, 'Liter', 'L', 'volume', NULL, 1, 3, 3),
(1, 'Milliliter', 'ml', 'volume', 3, 1000, 0, 4),
(1, 'Piece', 'pcs', 'count', NULL, 1, 0, 5),
(1, 'Pack', 'pack', 'count', NULL, 1, 0, 6),
(1, 'Box', 'box', 'count', NULL, 1, 0, 7),
(1, 'Sachet', 'sachet', 'count', NULL, 1, 0, 8);

-- Default Categories
INSERT INTO `categories` (`outlet_id`, `name`, `slug`, `icon`, `color`, `sort_order`, `kds_station`) VALUES
(1, 'Kopi', 'kopi', 'coffee', '#8B6B3D', 1, 'barista'),
(1, 'Non-Kopi', 'non-kopi', 'cup', '#A88B5E', 2, 'barista'),
(1, 'Makanan Berat', 'makanan-berat', 'utensils', '#D4B87A', 3, 'kitchen'),
(1, 'Camilan', 'camilan', 'cookie', '#E8D1A8', 4, 'kitchen'),
(1, 'Indomie', 'indomie', 'bowl', '#BFA885', 5, 'kitchen'),
(1, 'Rokok', 'rokok', 'cigarette', '#735631', 6, 'both');

-- Default Modifier Groups
INSERT INTO `modifier_groups` (`outlet_id`, `name`, `code`, `selection_type`, `is_required`, `min_selection`, `max_selection`, `sort_order`) VALUES
(1, 'Level Gula', 'sugar_level', 'single', 1, 1, 1, 1),
(1, 'Es', 'ice_level', 'single', 1, 1, 1, 2),
(1, 'Level Pedas', 'spicy_level', 'single', 1, 1, 1, 3),
(1, 'Tambah Telur', 'egg_type', 'single', 0, 0, 1, 4),
(1, 'Topping', 'topping', 'multiple', 0, 0, 5, 5);

-- Default Modifiers
INSERT INTO `modifiers` (`modifier_group_id`, `name`, `code`, `price_adjustment`, `is_default`, `sort_order`) VALUES
-- Level Gula
(1, 'Less Sugar (0%)', 'less_sugar', 0, 0, 1),
(1, 'Normal (100%)', 'normal_sugar', 0, 1, 2),
(1, 'Extra Sugar (150%)', 'extra_sugar', 1000, 0, 3),
(1, 'Custom %', 'custom_sugar', 1000, 0, 4),
-- Es
(2, 'Normal Ice', 'normal_ice', 0, 1, 1),
(2, 'Extra Ice', 'extra_ice', 0, 0, 2),
(2, 'Less Ice', 'less_ice', 0, 0, 3),
(2, 'No Ice (Hanya Panas)', 'no_ice', 0, 0, 4),
-- Level Pedas
(3, 'Level 1 (Tidak Pedas)', 'spicy_1', 0, 1, 1),
(3, 'Level 2 (Sedikit)', 'spicy_2', 0, 0, 2),
(3, 'Level 3 (Sedang)', 'spicy_3', 0, 0, 3),
(3, 'Level 4 (Pedas)', 'spicy_4', 0, 0, 4),
(3, 'Level 5 (Sangat Pedas)', 'spicy_5', 0, 0, 5),
-- Tambah Telur
(4, 'Mata Sapi', 'egg_sunny', 2000, 0, 1),
(4, 'Dadar', 'egg_omelet', 2000, 0, 2),
(4, 'Setengah Matang', 'egg_soft', 2000, 0, 3);

-- Default Toppings
INSERT INTO `toppings` (`outlet_id`, `name`, `code`, `price`, `stock`, `track_stock`, `unit_id`, `max_qty_per_order`) VALUES
(1, 'Boba', 'boba', 3000, 100, 1, 5, 5),
(1, 'Jelly', 'jelly', 2000, 100, 1, 5, 5),
(1, 'Extra Shot', 'extra_shot', 4000, 50, 1, 5, 3),
(1, 'Whipped Cream', 'whipped_cream', 2000, 50, 1, 5, 2),
(1, 'Keju', 'cheese', 3000, 50, 1, 5, 3),
(1, 'Sosis', 'sausage', 4000, 50, 1, 5, 3);

-- Default KDS Stations
INSERT INTO `kds_stations` (`outlet_id`, `name`, `code`, `sound_enabled`, `auto_print`, `filter_categories`) VALUES
(1, 'Barista', 'BAR', 1, 0, '[1,2]'), -- Kopi, Non-Kopi
(1, 'Kitchen', 'KIT', 1, 1, '[3,4,5,6]'); -- Makanan Berat, Camilan, Indomie, Rokok

-- Default Printers
INSERT INTO `printers` (`outlet_id`, `name`, `code`, `type`, `paper_width`, `paper_width_dots`, `character_per_line`, `is_default`, `print_job_type`) VALUES
(1, 'Kasir Utama', 'PRN-KSR', 'thermal_bluetooth', '58mm', 384, 32, 1, 'receipt'),
(1, 'Barista', 'PRN-BAR', 'thermal_bluetooth', '58mm', 384, 32, 0, 'kitchen_ticket'),
(1, 'Kitchen', 'PRN-KIT', 'thermal_usb', '58mm', 384, 32, 0, 'kitchen_ticket'),
(1, 'Antrian', 'PRN-ANT', 'thermal_bluetooth', '58mm', 384, 32, 0, 'queue_ticket');

-- Default Settings
INSERT INTO `settings` (`outlet_id`, `group`, `key`, `value`, `type`, `label`, `description`, `is_public`) VALUES
(1, 'general', 'outlet_name', 'Warkop POS Demo', 'string', 'Nama Outlet', 'Nama yang tampil di struk', 1),
(1, 'general', 'currency', 'IDR', 'string', 'Mata Uang', 'Kode mata uang ISO 4217', 1),
(1, 'general', 'timezone', 'Asia/Jakarta', 'string', 'Timezone', 'Timezone outlet', 1),
(1, 'tax', 'ppn_rate', '11', 'float', 'PPN Rate (%)', 'Persentase PPN', 0),
(1, 'tax', 'service_charge_rate', '0', 'float', 'Service Charge (%)', 'Persentase service charge', 0),
(1, 'rounding', 'default_rounding', '100', 'integer', 'Default Rounding', 'Pembulatan default (100, 500, 1000)', 0),
(1, 'pos', 'quick_order_timeout', '300', 'integer', 'Quick Order Timeout (detik)', 'Auto-save tab setelah idle', 0),
(1, 'pos', 'enable_sound', 'true', 'boolean', 'Enable Sound', 'Suara notif order baru', 1),
(1, 'kds', 'auto_print_kitchen', 'true', 'boolean', 'Auto Print Kitchen', 'Otomatis print tiket dapur', 0),
(1, 'kds', 'alert_timeout_warning', '300', 'integer', 'Alert Warning (detik)', 'Kuning setelah 5 menit', 0),
(1, 'kds', 'alert_timeout_danger', '600', 'integer', 'Alert Danger (detik)', 'Merah setelah 10 menit', 0),
(1, 'inventory', 'auto_deduction_on_paid', 'true', 'boolean', 'Auto Deduction on Paid', 'Kurangi stok bahan baku saat bayar', 0),
(1, 'inventory', 'low_stock_threshold_pct', '20', 'integer', 'Low Stock Threshold (%)', 'Persentase dari min_stock untuk warning', 0),
(1, 'payment', 'qris_dynamic_expire_minutes', '5', 'integer', 'QRIS Dynamic Expire (menit)', 'Waktu expired QRIS dynamic', 0),
(1, 'payment', 'cash_rounding', 'true', 'boolean', 'Cash Rounding', 'Pembulatan untuk pembayaran cash', 0);

-- Default Tax Rates
INSERT INTO `tax_rates` (`outlet_id`, `name`, `code`, `rate`, `type`, `calculation_base`, `is_inclusive`, `apply_to`, `is_active`) VALUES
(1, 'PPN 11%', 'PPN', 11.00, 'percent', 'subtotal_after_discount', 0, 'all', 1),
(1, 'Service Charge 5%', 'SC', 5.00, 'percent', 'subtotal_after_discount', 0, 'dine_in', 0);

-- Default Rounding Rules
INSERT INTO `rounding_rules` (`outlet_id`, `name`, `code`, `rounding_unit`, `method`, `apply_to`, `is_active`, `is_default`) VALUES
(1, 'Pembulatan Rupiah', 'RUP', 1, 'nearest', 'total', 1, 0),
(1, 'Pembulatan 100', '100', 100, 'nearest', 'total', 1, 1),
(1, 'Pembulatan 500', '500', 500, 'nearest', 'cash_payment', 1, 0),
(1, 'Pembulatan 1000', '1000', 1000, 'nearest', 'cash_payment', 1, 0);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================