-- ToryCRM Migration 013: Phase 3 - Integrations
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. INTEGRATION SETTINGS (per tenant)
-- ============================================================
CREATE TABLE IF NOT EXISTS `integrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `provider` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `config` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `last_synced_at` DATETIME DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_provider` (`tenant_id`, `provider`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ZALO OA
-- ============================================================
CREATE TABLE IF NOT EXISTS `zalo_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `conversation_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `zalo_user_id` VARCHAR(100) DEFAULT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `message_type` ENUM('text', 'image', 'file', 'sticker', 'template') DEFAULT 'text',
    `content` TEXT DEFAULT NULL,
    `attachment_url` VARCHAR(500) DEFAULT NULL,
    `zalo_message_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_zalo_contact` (`contact_id`),
    INDEX `idx_zalo_conv` (`conversation_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. GOOGLE CALENDAR SYNC
-- ============================================================
CREATE TABLE IF NOT EXISTS `calendar_sync_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `provider` VARCHAR(50) DEFAULT 'google',
    `access_token` TEXT DEFAULT NULL,
    `refresh_token` TEXT DEFAULT NULL,
    `token_expires_at` DATETIME DEFAULT NULL,
    `sync_token` VARCHAR(255) DEFAULT NULL,
    `last_synced_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_provider` (`user_id`, `provider`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `calendar_events`
    ADD COLUMN `google_event_id` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `sync_status` ENUM('local', 'synced', 'conflict') DEFAULT 'local';

-- ============================================================
-- 4. VOIP / CLICK-TO-CALL
-- ============================================================
CREATE TABLE IF NOT EXISTS `voip_extensions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `extension` VARCHAR(20) NOT NULL,
    `provider` VARCHAR(50) DEFAULT 'stringee',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_ext` (`user_id`, `provider`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PAYMENT GATEWAY (VNPay, MoMo)
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `invoice_id` INT UNSIGNED DEFAULT NULL,
    `gateway` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `status` ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    `gateway_response` JSON DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pay_invoice` (`invoice_id`),
    INDEX `idx_pay_status` (`status`),
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
