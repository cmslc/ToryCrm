-- Workflows table
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `trigger_type` VARCHAR(100) NULL,
    `trigger_config` JSON NULL,
    `nodes` JSON NULL,
    `edges` JSON NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `run_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_run_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_workflows_is_active` (`is_active`),
    INDEX `idx_workflows_trigger_type` (`trigger_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workflow logs table
CREATE TABLE IF NOT EXISTS `workflow_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'success',
    `message` TEXT NULL,
    `context` JSON NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_workflow_logs_workflow_id` (`workflow_id`),
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portal fields on contacts table (ignore errors if columns already exist)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'portal_token');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE contacts ADD COLUMN portal_token VARCHAR(100) NULL, ADD COLUMN portal_password VARCHAR(255) NULL, ADD COLUMN portal_active TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
