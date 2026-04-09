-- Custom Field Definitions
CREATE TABLE IF NOT EXISTS `custom_field_definitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL COMMENT 'contacts, deals, orders, tasks, tickets, products',
    `field_label` VARCHAR(255) NOT NULL,
    `field_key` VARCHAR(100) NOT NULL,
    `field_type` VARCHAR(50) NOT NULL DEFAULT 'text' COMMENT 'text, number, email, phone, url, textarea, select, multi_select, checkbox, radio, date, datetime, file, color, currency',
    `options` TEXT NULL COMMENT 'Options for select/radio/checkbox, one per line',
    `default_value` VARCHAR(255) NULL,
    `placeholder` VARCHAR(255) NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `is_filterable` TINYINT(1) NOT NULL DEFAULT 0,
    `show_in_list` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_field_key_module_tenant` (`field_key`, `module`, `tenant_id`),
    INDEX `idx_module_tenant` (`module`, `tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Field Values
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `field_value` TEXT NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_field_entity` (`field_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    CONSTRAINT `fk_cfv_field` FOREIGN KEY (`field_id`) REFERENCES `custom_field_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Flows
CREATE TABLE IF NOT EXISTS `approval_flows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL COMMENT 'orders, deals, purchase_orders, fund_transactions',
    `conditions` JSON NULL COMMENT 'JSON conditions for when this flow applies',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_module_tenant` (`module`, `tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Flow Steps
CREATE TABLE IF NOT EXISTS `approval_flow_steps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL DEFAULT 1,
    `step_label` VARCHAR(255) NULL,
    `approver_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_flow_order` (`flow_id`, `step_order`),
    CONSTRAINT `fk_afs_flow` FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Requests
CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `requested_by` INT UNSIGNED NOT NULL,
    `current_step` INT NOT NULL DEFAULT 1,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `completed_at` DATETIME NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_ar_flow` FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Actions (history)
CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `action` ENUM('approved', 'rejected') NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_request` (`request_id`),
    CONSTRAINT `fk_aa_request` FOREIGN KEY (`request_id`) REFERENCES `approval_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
