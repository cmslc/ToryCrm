-- Phase 2a chat: reply (quote), reactions, edit/delete
ALTER TABLE messages
    ADD COLUMN `reply_to_id` INT UNSIGNED NULL AFTER conversation_id,
    ADD COLUMN `edited_at` DATETIME NULL AFTER is_pinned,
    ADD COLUMN `deleted_at` DATETIME NULL AFTER edited_at,
    ADD INDEX idx_reply_to (`reply_to_id`);

CREATE TABLE IF NOT EXISTS `message_reactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `emoji` VARCHAR(16) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_msg_user_emoji` (`message_id`, `user_id`, `emoji`),
    INDEX `idx_msg` (`message_id`),
    CONSTRAINT `message_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mentions table (who was @-tagged in which message) for inbox-style notifications
CREATE TABLE IF NOT EXISTS `message_mentions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_msg_user` (`message_id`, `user_id`),
    INDEX `idx_user_unread` (`user_id`, `is_read`),
    CONSTRAINT `message_mentions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
