ALTER TABLE `logistics_packages` ADD COLUMN `type` ENUM('retail','wholesale') DEFAULT 'retail' AFTER `quantity`;
ALTER TABLE `logistics_packages` ADD COLUMN `order_id` INT UNSIGNED DEFAULT NULL AFTER `customer_id`;

CREATE TABLE IF NOT EXISTS `logistics_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `order_code` VARCHAR(50) NOT NULL,
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('retail','wholesale') DEFAULT 'retail',
    `product_name` VARCHAR(500) DEFAULT NULL,
    `total_packages` INT DEFAULT 0,
    `shipped_packages` INT DEFAULT 0,
    `received_packages` INT DEFAULT 0,
    `status` ENUM('pending','processing','partial','completed','cancelled') DEFAULT 'pending',
    `total_amount` DECIMAL(15,2) DEFAULT 0,
    `cod_amount` DECIMAL(15,2) DEFAULT 0,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_order_code` (`tenant_id`, `order_code`),
    INDEX `idx_lo_status` (`status`),
    INDEX `idx_lo_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logistics_package_orders` (
    `package_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`package_id`, `order_id`),
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `logistics_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
