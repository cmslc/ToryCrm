-- Task Comments
CREATE TABLE IF NOT EXISTS `task_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_task_comments_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Attachments
CREATE TABLE IF NOT EXISTS `task_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_task_att_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Time Tracking
CREATE TABLE IF NOT EXISTS `task_time_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `started_at` DATETIME NOT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `duration` INT UNSIGNED DEFAULT 0 COMMENT 'seconds',
    `note` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_time_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Dependencies
CREATE TABLE IF NOT EXISTS `task_dependencies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `depends_on_id` INT UNSIGNED NOT NULL,
    `type` ENUM('finish_to_start','start_to_start','finish_to_finish') DEFAULT 'finish_to_start',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_task_dep` (`task_id`, `depends_on_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`depends_on_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Templates
CREATE TABLE IF NOT EXISTS `task_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `checklist` JSON DEFAULT NULL COMMENT '[{"title":"...", "items":["sub1","sub2"]}]',
    `default_priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `default_status` ENUM('todo','in_progress','review','done') DEFAULT 'todo',
    `due_days` INT DEFAULT NULL COMMENT 'days from creation',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Recurring Rules
CREATE TABLE IF NOT EXISTS `task_recurring` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `frequency` ENUM('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'weekly',
    `interval_value` INT DEFAULT 1,
    `day_of_week` TINYINT DEFAULT NULL COMMENT '0=Sun, 1=Mon...',
    `day_of_month` TINYINT DEFAULT NULL,
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `next_run` DATETIME DEFAULT NULL,
    `last_run` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_recurring_next` (`is_active`, `next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add recurring_id to tasks
ALTER TABLE `tasks` ADD COLUMN `recurring_id` INT UNSIGNED DEFAULT NULL AFTER `parent_id`;
-- Add estimated_hours to tasks
ALTER TABLE `tasks` ADD COLUMN `estimated_hours` DECIMAL(6,2) DEFAULT NULL AFTER `progress`;
-- Add start_date to tasks
ALTER TABLE `tasks` ADD COLUMN `start_date` DATETIME DEFAULT NULL AFTER `due_date`;

-- Task SLA Rules
CREATE TABLE IF NOT EXISTS `task_sla_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `priority` ENUM('low','medium','high','urgent') NOT NULL,
    `max_hours` INT NOT NULL COMMENT 'hours to resolve',
    `escalate_to` INT UNSIGNED DEFAULT NULL,
    `notify_before_hours` INT DEFAULT 2,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`escalate_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
