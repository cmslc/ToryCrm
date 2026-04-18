-- Company profiles (Thông tin các công ty/pháp nhân của mình)
CREATE TABLE IF NOT EXISTS `company_profiles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100) DEFAULT NULL,
    `tax_code` VARCHAR(50) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `fax` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `representative` VARCHAR(255) DEFAULT NULL,
    `representative_title` VARCHAR(255) DEFAULT NULL,
    `bank_account` VARCHAR(100) DEFAULT NULL,
    `bank_name` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(500) DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
