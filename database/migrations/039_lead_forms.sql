-- Lead Forms
CREATE TABLE IF NOT EXISTS `lead_forms` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `fields` JSON NOT NULL COMMENT '[{name,label,type,required,options}]',
    `settings` JSON DEFAULT NULL COMMENT '{redirect_url,thank_you_message,notify_users,auto_assign}',
    `style` JSON DEFAULT NULL COMMENT '{theme,button_color,button_text}',
    `is_active` TINYINT(1) DEFAULT 1,
    `submission_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_form_slug` (`tenant_id`, `slug`),
    INDEX `idx_form_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form Submissions
CREATE TABLE IF NOT EXISTS `lead_form_submissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `form_id` INT UNSIGNED NOT NULL,
    `data` JSON NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL COMMENT 'Auto-created contact',
    `source_url` VARCHAR(500) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sub_form` (`form_id`),
    INDEX `idx_sub_tenant` (`tenant_id`),
    FOREIGN KEY (`form_id`) REFERENCES `lead_forms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
