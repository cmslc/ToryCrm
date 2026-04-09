-- Conversations table
CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `contact_id` INT UNSIGNED NULL,
    `channel` ENUM('email','zalo','facebook','sms','livechat') NOT NULL DEFAULT 'email',
    `subject` VARCHAR(255) NULL,
    `status` ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
    `assigned_to` INT UNSIGNED NULL,
    `last_message_at` DATETIME NULL,
    `last_message_preview` VARCHAR(255) NULL,
    `unread_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_starred` TINYINT(1) NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_conversations_tenant` (`tenant_id`),
    INDEX `idx_conversations_contact` (`contact_id`),
    INDEX `idx_conversations_status` (`status`),
    INDEX `idx_conversations_assigned` (`assigned_to`),
    INDEX `idx_conversations_last_msg` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conversation messages table
CREATE TABLE IF NOT EXISTS `conversation_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `direction` ENUM('inbound','outbound') NOT NULL DEFAULT 'outbound',
    `content` TEXT NOT NULL,
    `sender_id` INT UNSIGNED NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_conv_messages_conv` (`conversation_id`),
    INDEX `idx_conv_messages_direction` (`direction`),
    CONSTRAINT `fk_conv_messages_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canned responses table
CREATE TABLE IF NOT EXISTS `canned_responses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_canned_responses_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
