CREATE TABLE IF NOT EXISTS `field_label_overrides` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `table_name` VARCHAR(100) NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `label` VARCHAR(200) NOT NULL,
    `is_required` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_table_field` (`tenant_id`, `table_name`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
