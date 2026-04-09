-- Gamification tables
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(50) DEFAULT 'ri-trophy-line',
    `color` VARCHAR(20) DEFAULT 'warning',
    `criteria_type` VARCHAR(50) NOT NULL,
    `criteria_value` INT NOT NULL DEFAULT 1,
    `points` INT DEFAULT 10,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_achievements` (
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` INT UNSIGNED NOT NULL,
    `earned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `achievement_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `leaderboard_snapshots` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `period` VARCHAR(7) NOT NULL,
    `deals_won` INT DEFAULT 0,
    `revenue` DECIMAL(15,2) DEFAULT 0,
    `activities_count` INT DEFAULT 0,
    `points` INT DEFAULT 0,
    `rank_position` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_period` (`user_id`, `period`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- AI Chat History
CREATE TABLE IF NOT EXISTS `ai_chat_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user','assistant') NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Booking Links
CREATE TABLE IF NOT EXISTS `booking_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `duration` INT NOT NULL DEFAULT 30,
    `available_days` VARCHAR(50) DEFAULT '1,2,3,4,5',
    `start_time` TIME DEFAULT '08:00:00',
    `end_time` TIME DEFAULT '17:00:00',
    `buffer_minutes` INT DEFAULT 15,
    `max_advance_days` INT DEFAULT 30,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `booking_link_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(255) NOT NULL,
    `guest_email` VARCHAR(255) NOT NULL,
    `guest_phone` VARCHAR(50) DEFAULT NULL,
    `note` TEXT,
    `booking_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('confirmed','cancelled') DEFAULT 'confirmed',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_link_id`) REFERENCES `booking_links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed achievements
INSERT IGNORE INTO `achievements` (`slug`, `name`, `description`, `icon`, `color`, `criteria_type`, `criteria_value`, `points`) VALUES
('first-deal', 'Deal đầu tiên', 'Chiến thắng deal đầu tiên của bạn', 'ri-hand-coin-line', 'success', 'deals_won', 1, 10),
('rising-star', 'Ngôi sao mới', 'Chiến thắng 5 deal', 'ri-star-line', 'warning', 'deals_won', 5, 25),
('warrior', 'Chiến binh', 'Chiến thắng 10 deal', 'ri-sword-line', 'danger', 'deals_won', 10, 50),
('top-performer', 'Top performer', 'Doanh thu cao nhất tháng', 'ri-vip-crown-line', 'primary', 'top_revenue', 1, 100),
('hard-worker', 'Chăm chỉ', 'Hoàn thành 50 hoạt động trong tháng', 'ri-run-line', 'info', 'monthly_activities', 50, 30),
('connector', 'Kết nối', 'Tạo 20 liên hệ khách hàng', 'ri-links-line', 'secondary', 'contacts_created', 20, 20),
('support-hero', 'Hỗ trợ tốt', 'Giải quyết 10 ticket hỗ trợ', 'ri-customer-service-line', 'success', 'tickets_resolved', 10, 30);
