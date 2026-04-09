-- GPS Check-in table for field sales
CREATE TABLE IF NOT EXISTS `checkins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `latitude` DECIMAL(10,8) NOT NULL,
    `longitude` DECIMAL(11,8) NOT NULL,
    `address` VARCHAR(500) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `photo` VARCHAR(500) DEFAULT NULL,
    `check_type` ENUM('visit', 'meeting', 'delivery', 'other') DEFAULT 'visit',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_checkin_user` (`user_id`),
    INDEX `idx_checkin_contact` (`contact_id`),
    INDEX `idx_checkin_date` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dark mode preference column
ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(10) DEFAULT 'light';
