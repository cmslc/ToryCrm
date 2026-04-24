-- ToryCRM Migration 009: Smart Features
-- Smart Dashboard, Conversation Hub, Health Score, Workflow Builder, Client Portal
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CONVERSATION HUB - Unified Inbox
-- ============================================================
CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `channel` ENUM('email', 'zalo', 'facebook', 'sms', 'livechat', 'phone', 'internal') NOT NULL,
    `channel_id` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('open', 'pending', 'resolved', 'closed') DEFAULT 'open',
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `last_message_at` DATETIME DEFAULT NULL,
    `last_message_preview` VARCHAR(255) DEFAULT NULL,
    `unread_count` INT DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `tags` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_conv_tenant_status` (`tenant_id`, `status`),
    INDEX `idx_conv_assigned` (`assigned_to`),
    INDEX `idx_conv_contact` (`contact_id`),
    INDEX `idx_conv_last_msg` (`last_message_at`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `sender_type` ENUM('user', 'contact', 'system') DEFAULT 'user',
    `sender_id` INT UNSIGNED DEFAULT NULL,
    `content` TEXT NOT NULL,
    `content_type` ENUM('text', 'html', 'image', 'file', 'audio') DEFAULT 'text',
    `attachments` JSON DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_conv` (`conversation_id`, `created_at`),
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canned responses (tin nhắn mẫu)
CREATE TABLE IF NOT EXISTS `canned_responses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `shortcut` VARCHAR(50) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `use_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CUSTOMER HEALTH SCORE
-- ============================================================
CREATE TABLE IF NOT EXISTS `health_scores` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT UNSIGNED NOT NULL,
    `overall_score` INT DEFAULT 50,
    `engagement_score` INT DEFAULT 50,
    `payment_score` INT DEFAULT 50,
    `support_score` INT DEFAULT 50,
    `activity_score` INT DEFAULT 50,
    `churn_risk` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    `last_interaction_at` DATETIME DEFAULT NULL,
    `days_since_interaction` INT DEFAULT 0,
    `calculated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `factors` JSON DEFAULT NULL,
    UNIQUE KEY `uk_health_contact` (`contact_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. VISUAL WORKFLOW BUILDER
-- ============================================================
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `trigger_type` VARCHAR(100) NOT NULL,
    `trigger_config` JSON DEFAULT NULL,
    `nodes` JSON NOT NULL,
    `edges` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `run_count` INT DEFAULT 0,
    `last_run_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workflow_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `node_id` VARCHAR(50) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `status` ENUM('success', 'failed', 'waiting', 'skipped') DEFAULT 'success',
    `result` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. REVENUE ANALYTICS
-- ============================================================
CREATE TABLE IF NOT EXISTS `revenue_snapshots` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `date` DATE NOT NULL,
    `total_contacts` INT DEFAULT 0,
    `new_contacts` INT DEFAULT 0,
    `total_deals` INT DEFAULT 0,
    `won_deals` INT DEFAULT 0,
    `lost_deals` INT DEFAULT 0,
    `deal_revenue` DECIMAL(15,2) DEFAULT 0,
    `order_revenue` DECIMAL(15,2) DEFAULT 0,
    `avg_deal_size` DECIMAL(15,2) DEFAULT 0,
    `avg_close_days` INT DEFAULT 0,
    `conversion_rate` DECIMAL(5,2) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_snap_tenant_date` (`tenant_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. CLIENT PORTAL
-- ============================================================
CREATE TABLE IF NOT EXISTS `portal_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TEAM COLLABORATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `mentions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `mentioned_by` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `content` TEXT DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mention_user` (`user_id`, `is_read`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mentioned_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. SMART INSIGHTS (pre-calculated daily)
-- ============================================================
CREATE TABLE IF NOT EXISTS `smart_insights` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `action_url` VARCHAR(500) DEFAULT NULL,
    `action_label` VARCHAR(100) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(20) DEFAULT 'primary',
    `priority` INT DEFAULT 0,
    `is_dismissed` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_insight_user` (`user_id`, `is_dismissed`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED: Canned responses
-- ============================================================
INSERT INTO `canned_responses` (`tenant_id`, `title`, `content`, `shortcut`, `category`) VALUES
(1, 'Chào khách hàng', 'Xin chào! Cảm ơn bạn đã liên hệ với chúng tôi. Tôi có thể giúp gì cho bạn?', '/chao', 'Chào hỏi'),
(1, 'Cảm ơn phản hồi', 'Cảm ơn bạn đã phản hồi. Chúng tôi sẽ xem xét và phản hồi sớm nhất có thể.', '/camonthanks', 'Chào hỏi'),
(1, 'Chuyển tiếp kỹ thuật', 'Tôi sẽ chuyển vấn đề này cho bộ phận kỹ thuật để hỗ trợ bạn tốt hơn. Vui lòng chờ trong giây lát.', '/chuyen', 'Hỗ trợ'),
(1, 'Xác nhận đơn hàng', 'Đơn hàng của bạn đã được xác nhận và đang được xử lý. Chúng tôi sẽ thông báo khi có cập nhật.', '/donhang', 'Đơn hàng'),
(1, 'Hẹn demo', 'Rất vui được tư vấn cho bạn. Bạn có thể cho tôi biết thời gian phù hợp để chúng tôi demo sản phẩm không?', '/demo', 'Sales');

SET FOREIGN_KEY_CHECKS = 1;
