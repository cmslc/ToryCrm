-- Positions management
CREATE TABLE IF NOT EXISTS `positions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pos_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default positions
INSERT INTO `positions` (`tenant_id`, `name`, `sort_order`) VALUES
(1, 'Giám đốc', 1),
(1, 'Phó giám đốc', 2),
(1, 'Trưởng phòng', 3),
(1, 'Phó phòng', 4),
(1, 'Trưởng nhóm', 5),
(1, 'Nhân viên', 6),
(1, 'Thực tập sinh', 7);

-- Add position_id to users table
ALTER TABLE `users` ADD COLUMN `position_id` INT UNSIGNED DEFAULT NULL AFTER `role`;
