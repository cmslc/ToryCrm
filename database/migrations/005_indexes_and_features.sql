-- ToryCRM Migration 005: Indexes, CSRF, Global Search, File Upload, Import, Automation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. DATABASE INDEXES (Performance)
-- ============================================================
ALTER TABLE `contacts` ADD INDEX `idx_contacts_status` (`status`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_owner` (`owner_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_source` (`source_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_company` (`company_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_created` (`created_at`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_deleted` (`is_deleted`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_email` (`email`);

ALTER TABLE `deals` ADD INDEX `idx_deals_status` (`status`);
ALTER TABLE `deals` ADD INDEX `idx_deals_stage` (`stage_id`);
ALTER TABLE `deals` ADD INDEX `idx_deals_owner` (`owner_id`);
ALTER TABLE `deals` ADD INDEX `idx_deals_created` (`created_at`);

ALTER TABLE `tasks` ADD INDEX `idx_tasks_status` (`status`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_assigned` (`assigned_to`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_due` (`due_date`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_project` (`project_id`);

ALTER TABLE `orders` ADD INDEX `idx_orders_status` (`status`);
ALTER TABLE `orders` ADD INDEX `idx_orders_type` (`type`);
ALTER TABLE `orders` ADD INDEX `idx_orders_contact` (`contact_id`);
ALTER TABLE `orders` ADD INDEX `idx_orders_payment` (`payment_status`);

ALTER TABLE `tickets` ADD INDEX `idx_tickets_status` (`status`);
ALTER TABLE `tickets` ADD INDEX `idx_tickets_priority` (`priority`);
ALTER TABLE `tickets` ADD INDEX `idx_tickets_assigned` (`assigned_to`);

ALTER TABLE `activities` ADD INDEX `idx_activities_user` (`user_id`);
ALTER TABLE `activities` ADD INDEX `idx_activities_contact` (`contact_id`);
ALTER TABLE `activities` ADD INDEX `idx_activities_created` (`created_at`);

ALTER TABLE `notifications` ADD INDEX `idx_notif_user_read` (`user_id`, `is_read`);
ALTER TABLE `fund_transactions` ADD INDEX `idx_fund_type_status` (`type`, `status`);
ALTER TABLE `call_logs` ADD INDEX `idx_calls_started` (`started_at`);
ALTER TABLE `campaigns` ADD INDEX `idx_campaigns_status` (`status`);

-- ============================================================
-- 2. FILE UPLOADS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `file_uploads` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `original_name` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT DEFAULT 0,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. IMPORT LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `import_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `total_rows` INT DEFAULT 0,
    `success_count` INT DEFAULT 0,
    `error_count` INT DEFAULT 0,
    `errors` JSON DEFAULT NULL,
    `status` ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. AUTOMATION RULES
-- ============================================================
CREATE TABLE IF NOT EXISTS `automation_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `trigger_event` VARCHAR(100) NOT NULL,
    `conditions` JSON DEFAULT NULL,
    `actions` JSON NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `run_count` INT DEFAULT 0,
    `last_run_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `automation_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rule_id` INT UNSIGNED NOT NULL,
    `trigger_data` JSON DEFAULT NULL,
    `action_result` JSON DEFAULT NULL,
    `status` ENUM('success', 'failed') DEFAULT 'success',
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`rule_id`) REFERENCES `automation_rules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. EMAIL LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `to_email` VARCHAR(255) NOT NULL,
    `to_name` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `module` VARCHAR(50) DEFAULT NULL,
    `module_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('sent', 'failed', 'queued') DEFAULT 'queued',
    `error_message` TEXT DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
