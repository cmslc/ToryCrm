-- Email accounts (cấu hình IMAP/SMTP)
CREATE TABLE IF NOT EXISTS `email_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) DEFAULT NULL,
    `imap_host` VARCHAR(255) DEFAULT NULL,
    `imap_port` INT DEFAULT 993,
    `imap_encryption` ENUM('ssl','tls','none') DEFAULT 'ssl',
    `smtp_host` VARCHAR(255) DEFAULT NULL,
    `smtp_port` INT DEFAULT 587,
    `smtp_encryption` ENUM('ssl','tls','none') DEFAULT 'tls',
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(500) NOT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_sync` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_tenant` (`tenant_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email messages (cached from IMAP)
CREATE TABLE IF NOT EXISTS `email_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `account_id` INT UNSIGNED NOT NULL,
    `message_uid` VARCHAR(100) DEFAULT NULL,
    `folder` VARCHAR(50) DEFAULT 'INBOX',
    `from_email` VARCHAR(255) DEFAULT NULL,
    `from_name` VARCHAR(255) DEFAULT NULL,
    `to_emails` TEXT DEFAULT NULL,
    `cc_emails` TEXT DEFAULT NULL,
    `subject` VARCHAR(500) DEFAULT NULL,
    `body_text` MEDIUMTEXT DEFAULT NULL,
    `body_html` MEDIUMTEXT DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `has_attachments` TINYINT(1) DEFAULT 0,
    `contact_id` INT UNSIGNED DEFAULT NULL COMMENT 'Auto-linked contact',
    `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'deal, ticket, order',
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_account` (`account_id`),
    INDEX `idx_email_folder` (`account_id`, `folder`),
    INDEX `idx_email_contact` (`contact_id`),
    FOREIGN KEY (`account_id`) REFERENCES `email_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email attachments
CREATE TABLE IF NOT EXISTS `email_attachments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `size` INT UNSIGNED DEFAULT 0,
    `file_path` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`message_id`) REFERENCES `email_messages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
