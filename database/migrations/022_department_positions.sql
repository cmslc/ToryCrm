ALTER TABLE `departments` ADD COLUMN `vice_manager_id` INT UNSIGNED DEFAULT NULL AFTER `manager_id`;

CREATE TABLE IF NOT EXISTS `department_positions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `position` VARCHAR(100) NOT NULL DEFAULT 'Nhân viên',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_dept_user` (`department_id`, `user_id`),
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
