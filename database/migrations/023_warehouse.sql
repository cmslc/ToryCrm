-- Warehouses (Kho)
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `address` VARCHAR(500) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `manager_id` INT UNSIGNED DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `description` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_wh_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock (Tồn kho theo sản phẩm + kho)
CREATE TABLE IF NOT EXISTS `stock` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(12,2) DEFAULT 0,
    `min_quantity` DECIMAL(12,2) DEFAULT 0 COMMENT 'Cảnh báo tồn kho thấp',
    `max_quantity` DECIMAL(12,2) DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_wh_product` (`warehouse_id`, `product_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements (Phiếu xuất nhập kho)
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `code` VARCHAR(50) DEFAULT NULL,
    `type` ENUM('import','export','transfer','adjustment') NOT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL COMMENT 'Kho nguồn (xuất) hoặc kho đích (nhập)',
    `to_warehouse_id` INT UNSIGNED DEFAULT NULL COMMENT 'Kho đích (chuyển kho)',
    `reference_type` VARCHAR(50) DEFAULT NULL COMMENT 'order, purchase_order, manual',
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft','confirmed','cancelled') DEFAULT 'draft',
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `confirmed_by` INT UNSIGNED DEFAULT NULL,
    `confirmed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sm_tenant` (`tenant_id`),
    INDEX `idx_sm_type` (`type`),
    INDEX `idx_sm_wh` (`warehouse_id`),
    INDEX `idx_sm_ref` (`reference_type`, `reference_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movement Items (Chi tiết phiếu)
CREATE TABLE IF NOT EXISTS `stock_movement_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `movement_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(12,2) NOT NULL,
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `note` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`movement_id`) REFERENCES `stock_movements`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Checks (Kiểm kho)
CREATE TABLE IF NOT EXISTS `stock_checks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('draft','completed','cancelled') DEFAULT 'draft',
    `note` TEXT DEFAULT NULL,
    `checked_by` INT UNSIGNED DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Check Items
CREATE TABLE IF NOT EXISTS `stock_check_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `check_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `system_qty` DECIMAL(12,2) DEFAULT 0,
    `actual_qty` DECIMAL(12,2) DEFAULT 0,
    `difference` DECIMAL(12,2) DEFAULT 0,
    `note` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`check_id`) REFERENCES `stock_checks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default warehouse
INSERT INTO `warehouses` (`tenant_id`, `name`, `code`, `is_default`, `is_active`) VALUES (1, 'Kho chính', 'WH01', 1, 1);
