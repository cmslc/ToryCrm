-- Plugin: Kho Logistics
INSERT INTO `plugins` (`name`, `slug`, `description`, `category`, `icon`, `version`, `author`, `is_active`, `config`) VALUES
('Kho Logistics', 'kho-logistics', 'Quản lý kho vận chuyển: quét mã barcode nhập kho, tracking kiện hàng, bao hàng, lịch sử xuất nhập, cân nặng, kích thước', 'Kho vận', 'ri-truck-line', '1.0.0', 'ToryCRM', 1, '{"fields":[{"key":"auto_notify","type":"checkbox","label":"Tự động thông báo KH khi nhận hàng","default":true},{"key":"require_weight","type":"checkbox","label":"Bắt buộc nhập cân nặng khi nhận","default":false},{"key":"default_warehouse","type":"text","label":"Kho mặc định","default":"VN"}]}');

-- Logistics Packages (Kiện hàng)
CREATE TABLE IF NOT EXISTS `logistics_packages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `package_code` VARCHAR(50) NOT NULL,
    `tracking_code` VARCHAR(100) DEFAULT NULL COMMENT 'Mã vận đơn bên ngoài',
    `tracking_intl` VARCHAR(100) DEFAULT NULL COMMENT 'Tracking quốc tế',
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `weight_actual` DECIMAL(8,2) DEFAULT NULL,
    `weight_volume` DECIMAL(8,2) DEFAULT NULL,
    `length_cm` DECIMAL(8,2) DEFAULT NULL,
    `width_cm` DECIMAL(8,2) DEFAULT NULL,
    `height_cm` DECIMAL(8,2) DEFAULT NULL,
    `quantity` INT DEFAULT 1,
    `status` ENUM('pending','warehouse_cn','packed','shipping','warehouse_vn','delivering','delivered','returned','damaged') DEFAULT 'pending',
    `warehouse_id` INT UNSIGNED DEFAULT NULL,
    `bag_id` INT UNSIGNED DEFAULT NULL,
    `receive_photo` VARCHAR(500) DEFAULT NULL,
    `received_by` INT UNSIGNED DEFAULT NULL,
    `received_at` DATETIME DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_pkg_code` (`tenant_id`, `package_code`),
    INDEX `idx_pkg_tracking` (`tracking_code`),
    INDEX `idx_pkg_status` (`status`),
    INDEX `idx_pkg_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics Bags (Bao hàng)
CREATE TABLE IF NOT EXISTS `logistics_bags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `bag_code` VARCHAR(50) NOT NULL,
    `status` ENUM('open','sealed','shipping','arrived','completed') DEFAULT 'open',
    `total_packages` INT DEFAULT 0,
    `total_weight` DECIMAL(10,2) DEFAULT 0,
    `warehouse_id` INT UNSIGNED DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `sealed_at` DATETIME DEFAULT NULL,
    `sealed_by` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_bag_code` (`tenant_id`, `bag_code`),
    INDEX `idx_bag_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics Scan Log (Lịch sử quét)
CREATE TABLE IF NOT EXISTS `logistics_scan_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `scan_code` VARCHAR(100) NOT NULL,
    `scan_type` ENUM('package','bag','tracking','unknown') DEFAULT 'unknown',
    `result` ENUM('success','error','duplicate') DEFAULT 'success',
    `package_id` INT UNSIGNED DEFAULT NULL,
    `bag_id` INT UNSIGNED DEFAULT NULL,
    `message` VARCHAR(500) DEFAULT NULL,
    `scanned_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_scan_tenant` (`tenant_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Package Status History
CREATE TABLE IF NOT EXISTS `logistics_status_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT UNSIGNED NOT NULL,
    `old_status` VARCHAR(30) DEFAULT NULL,
    `new_status` VARCHAR(30) NOT NULL,
    `note` VARCHAR(500) DEFAULT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
