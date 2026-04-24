-- Shipments (Lô hàng)
CREATE TABLE IF NOT EXISTS `logistics_shipments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `shipment_code` VARCHAR(50) NOT NULL,
    `origin` VARCHAR(100) DEFAULT 'CN' COMMENT 'Kho xuất (TQ/VN)',
    `destination` VARCHAR(100) DEFAULT 'VN' COMMENT 'Kho đích',
    `vehicle_info` VARCHAR(255) DEFAULT NULL COMMENT 'Thông tin xe/chuyến',
    `total_packages` INT DEFAULT 0,
    `total_bags` INT DEFAULT 0,
    `total_weight` DECIMAL(10,2) DEFAULT 0,
    `total_cbm` DECIMAL(10,4) DEFAULT 0,
    `status` ENUM('preparing','in_transit','arrived','completed','cancelled') DEFAULT 'preparing',
    `departed_at` DATETIME DEFAULT NULL,
    `arrived_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_shipment_code` (`tenant_id`, `shipment_code`),
    INDEX `idx_ship_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment-Package link
CREATE TABLE IF NOT EXISTS `logistics_shipment_packages` (
    `shipment_id` INT UNSIGNED NOT NULL,
    `package_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`shipment_id`, `package_id`),
    FOREIGN KEY (`shipment_id`) REFERENCES `logistics_shipments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment-Bag link
CREATE TABLE IF NOT EXISTS `logistics_shipment_bags` (
    `shipment_id` INT UNSIGNED NOT NULL,
    `bag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`shipment_id`, `bag_id`),
    FOREIGN KEY (`shipment_id`) REFERENCES `logistics_shipments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bag_id`) REFERENCES `logistics_bags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries (Phiếu giao hàng)
CREATE TABLE IF NOT EXISTS `logistics_deliveries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `delivery_code` VARCHAR(50) NOT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `package_id` INT UNSIGNED DEFAULT NULL,
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `delivery_type` ENUM('full','partial') DEFAULT 'full',
    `delivered_packages` INT DEFAULT 0,
    `total_packages` INT DEFAULT 0,
    `cod_amount` DECIMAL(15,2) DEFAULT 0,
    `cod_collected` DECIMAL(15,2) DEFAULT 0,
    `cod_method` ENUM('cash','transfer','balance') DEFAULT NULL,
    `status` ENUM('pending','delivering','delivered','failed') DEFAULT 'pending',
    `delivered_by` INT UNSIGNED DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_delivery_code` (`tenant_id`, `delivery_code`),
    INDEX `idx_del_status` (`status`),
    INDEX `idx_del_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipping Rates (Bảng giá vận chuyển)
CREATE TABLE IF NOT EXISTS `logistics_shipping_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `cargo_type` ENUM('easy','difficult') DEFAULT 'easy',
    `rate_per_kg` DECIMAL(10,2) DEFAULT 0,
    `rate_per_cbm` DECIMAL(10,2) DEFAULT 0,
    `min_weight` DECIMAL(8,2) DEFAULT 0,
    `max_weight` DECIMAL(8,2) DEFAULT 0,
    `origin` VARCHAR(50) DEFAULT 'CN',
    `destination` VARCHAR(50) DEFAULT 'VN',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add shipment_id to packages
ALTER TABLE `logistics_packages` ADD COLUMN `shipment_id` INT UNSIGNED DEFAULT NULL AFTER `bag_id`;
-- Add delivery status
ALTER TABLE `logistics_packages` ADD COLUMN `delivered_by` INT UNSIGNED DEFAULT NULL AFTER `received_at`;
ALTER TABLE `logistics_packages` ADD COLUMN `delivered_at` DATETIME DEFAULT NULL AFTER `delivered_by`;
-- Add warehouse role
ALTER TABLE `logistics_packages` ADD COLUMN `warehouse_location` ENUM('cn','vn') DEFAULT NULL AFTER `warehouse_id`;

-- Bag improvements
ALTER TABLE `logistics_bags` ADD COLUMN `shipment_id` INT UNSIGNED DEFAULT NULL AFTER `warehouse_id`;
ALTER TABLE `logistics_bags` ADD COLUMN `cargo_type` ENUM('easy','difficult') DEFAULT 'easy' AFTER `total_weight`;
ALTER TABLE `logistics_bags` ADD COLUMN `rate_per_kg` DECIMAL(10,2) DEFAULT 0 AFTER `cargo_type`;
ALTER TABLE `logistics_bags` ADD COLUMN `rate_per_cbm` DECIMAL(10,2) DEFAULT 0 AFTER `rate_per_kg`;
ALTER TABLE `logistics_bags` ADD COLUMN `domestic_cost` DECIMAL(10,2) DEFAULT 0 AFTER `rate_per_cbm`;

-- Default shipping rates
INSERT INTO `logistics_shipping_rates` (`tenant_id`, `name`, `cargo_type`, `rate_per_kg`, `rate_per_cbm`, `origin`, `destination`) VALUES
(1, 'Hàng thường - Giá theo kg', 'easy', 25000, 0, 'CN', 'VN'),
(1, 'Hàng thường - Giá theo khối', 'easy', 0, 3500000, 'CN', 'VN'),
(1, 'Hàng khó - Giá theo kg', 'difficult', 45000, 0, 'CN', 'VN'),
(1, 'Hàng khó - Giá theo khối', 'difficult', 0, 5500000, 'CN', 'VN');
