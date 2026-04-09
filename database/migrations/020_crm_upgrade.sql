-- ToryCRM Migration 020: CRM Core Upgrade
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. DEAL PRODUCTS (Sản phẩm trong deal)
-- ============================================================
CREATE TABLE IF NOT EXISTS `deal_products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `deal_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1,
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `discount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. DEAL close reason + competitor
-- ============================================================
ALTER TABLE `deals`
    ADD COLUMN `close_reason` TEXT DEFAULT NULL,
    ADD COLUMN `competitor` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `loss_reason_category` ENUM('price','timing','competitor','feature','budget','other') DEFAULT NULL;

-- ============================================================
-- 3. TAGS MANAGEMENT
-- ============================================================
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `use_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_tag` (`tenant_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `taggables` (
    `tag_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`tag_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. DUPLICATE DETECTION LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS `duplicate_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `match_field` VARCHAR(50) NOT NULL,
    `match_value` VARCHAR(255) NOT NULL,
    `record_ids` JSON NOT NULL,
    `status` ENUM('pending', 'merged', 'ignored') DEFAULT 'pending',
    `merged_into_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_dup_status` (`status`, `entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. COMPANIES soft delete + extra fields
-- ============================================================
-- is_deleted already added in migration 004

-- Seed some tags
INSERT INTO `tags` (`tenant_id`, `name`, `color`) VALUES
(1, 'VIP', '#f06548'),
(1, 'Hot Lead', '#f7b84b'),
(1, 'Đối tác', '#0ab39c'),
(1, 'Ưu tiên cao', '#405189'),
(1, 'Cần theo dõi', '#299cdb'),
(1, 'Tiềm năng lớn', '#3577f1');

SET FOREIGN_KEY_CHECKS = 1;
