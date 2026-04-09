-- ToryCRM Migration 016: Advanced Features
-- Custom Fields, Approval Chain, AI Chat, Booking
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CUSTOM FIELDS BUILDER
-- ============================================================
CREATE TABLE IF NOT EXISTS `custom_field_definitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `module` VARCHAR(50) NOT NULL,
    `field_key` VARCHAR(100) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `field_type` ENUM('text','number','email','phone','url','textarea','select','multi_select','checkbox','radio','date','datetime','file','color','currency') NOT NULL DEFAULT 'text',
    `options` JSON DEFAULT NULL,
    `default_value` VARCHAR(255) DEFAULT NULL,
    `placeholder` VARCHAR(255) DEFAULT NULL,
    `is_required` TINYINT(1) DEFAULT 0,
    `is_filterable` TINYINT(1) DEFAULT 0,
    `is_visible_in_list` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_module_key` (`tenant_id`, `module`, `field_key`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `value` TEXT DEFAULT NULL,
    UNIQUE KEY `uk_field_entity` (`field_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`field_id`) REFERENCES `custom_field_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. APPROVAL CHAIN
-- ============================================================
CREATE TABLE IF NOT EXISTS `approval_flows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `conditions` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_steps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL DEFAULT 1,
    `approver_id` INT UNSIGNED DEFAULT NULL,
    `approver_role` VARCHAR(50) DEFAULT NULL,
    `auto_approve_after_hours` INT DEFAULT NULL,
    FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `flow_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `current_step` INT DEFAULT 1,
    `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    `requested_by` INT UNSIGNED DEFAULT NULL,
    `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    INDEX `idx_approval_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `step_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('approve','reject','comment') NOT NULL,
    `comment` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `approval_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`step_id`) REFERENCES `approval_steps`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. AI CHAT HISTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `ai_chat_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user','assistant') NOT NULL,
    `content` TEXT NOT NULL,
    `context` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ai_user` (`user_id`, `created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. BOOKING LINKS
-- ============================================================
CREATE TABLE IF NOT EXISTS `booking_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `duration_minutes` INT DEFAULT 30,
    `available_days` JSON DEFAULT NULL,
    `available_hours` JSON DEFAULT NULL,
    `buffer_minutes` INT DEFAULT 15,
    `max_advance_days` INT DEFAULT 30,
    `is_active` TINYINT(1) DEFAULT 1,
    `booking_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `link_id` INT UNSIGNED NOT NULL,
    `contact_name` VARCHAR(255) NOT NULL,
    `contact_email` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) DEFAULT NULL,
    `start_at` DATETIME NOT NULL,
    `end_at` DATETIME NOT NULL,
    `note` TEXT DEFAULT NULL,
    `status` ENUM('confirmed','cancelled','completed','no_show') DEFAULT 'confirmed',
    `calendar_event_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`link_id`) REFERENCES `booking_links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
