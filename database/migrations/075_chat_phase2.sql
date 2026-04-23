-- Phase 2 chat: groups, pinning, attachments (reuse messages.attachments JSON)

-- Group chat needs a members table; DM can also use it but keeps user_a/b for fast lookup.
CREATE TABLE IF NOT EXISTS `conversation_members` (
    `conversation_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `unread_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_read_at` DATETIME NULL,
    `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `role` ENUM('member','admin') DEFAULT 'member',
    PRIMARY KEY (`conversation_id`, `user_id`),
    INDEX idx_user (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group name (null for DMs)
ALTER TABLE conversations
    ADD COLUMN `name` VARCHAR(100) NULL AFTER user_b_id;

-- Extend channel enum to include 'group'
ALTER TABLE conversations
    MODIFY COLUMN `channel` ENUM('email','zalo','facebook','sms','livechat','phone','internal','group') NOT NULL DEFAULT 'email';

-- Pin + full-text search index
ALTER TABLE messages
    ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER is_read,
    ADD FULLTEXT INDEX ft_content (content);
