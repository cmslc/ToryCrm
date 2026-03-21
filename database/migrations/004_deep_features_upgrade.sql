-- ToryCRM Migration 004: Deep Features Upgrade (theo Getfly CRM API docs)
-- Nâng cấp toàn bộ modules cho sâu hơn

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CONTACT/ACCOUNT UPGRADE
-- Thêm: bonus_points, relation, multi-type, province/district/ward,
--        is_private, referrer, publisher, soft delete, last_contact
-- ============================================================

-- Bảng quan hệ khách hàng (Mối quan hệ)
CREATE TABLE IF NOT EXISTS `contact_relations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_relations` (`name`, `sort_order`) VALUES
('Khách hàng mới', 1), ('Khách hàng tiềm năng', 2), ('Khách hàng chính thức', 3),
('Đối tác', 4), ('Nhà cung cấp', 5), ('Đã rời bỏ', 6);

-- Bảng loại khách hàng (account_type - multi-select)
CREATE TABLE IF NOT EXISTS `contact_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_types` (`name`, `color`, `sort_order`) VALUES
('Cá nhân', '#299cdb', 1), ('Doanh nghiệp', '#405189', 2),
('VIP', '#f06548', 3), ('Đại lý', '#0ab39c', 4);

-- Pivot: contact <-> type (multi-select)
CREATE TABLE IF NOT EXISTS `contact_type_pivot` (
    `contact_id` INT UNSIGNED NOT NULL,
    `type_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`contact_id`, `type_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`type_id`) REFERENCES `contact_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng ngành nghề
CREATE TABLE IF NOT EXISTS `industries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `industries` (`name`, `sort_order`) VALUES
('Công nghệ thông tin', 1), ('Tài chính - Ngân hàng', 2), ('Bất động sản', 3),
('Sản xuất', 4), ('Thương mại', 5), ('Y tế', 6), ('Giáo dục', 7),
('Truyền thông', 8), ('Vận tải - Logistics', 9), ('F&B', 10),
('Du lịch', 11), ('Nông nghiệp', 12), ('Khác', 13);

-- Thêm cột mới cho bảng contacts
ALTER TABLE `contacts`
    ADD COLUMN `account_code` VARCHAR(50) DEFAULT NULL AFTER `id`,
    ADD COLUMN `relation_id` INT UNSIGNED DEFAULT NULL AFTER `source_id`,
    ADD COLUMN `industry_id` INT UNSIGNED DEFAULT NULL AFTER `relation_id`,
    ADD COLUMN `bonus_points` INT DEFAULT 0 AFTER `score`,
    ADD COLUMN `total_revenue` DECIMAL(15,2) DEFAULT 0 AFTER `bonus_points`,
    ADD COLUMN `is_private` TINYINT(1) DEFAULT 0 AFTER `total_revenue`,
    ADD COLUMN `country` VARCHAR(100) DEFAULT 'Việt Nam' AFTER `city`,
    ADD COLUMN `province` VARCHAR(100) DEFAULT NULL AFTER `country`,
    ADD COLUMN `district` VARCHAR(100) DEFAULT NULL AFTER `province`,
    ADD COLUMN `ward` VARCHAR(100) DEFAULT NULL AFTER `district`,
    ADD COLUMN `referrer_type` VARCHAR(50) DEFAULT NULL AFTER `ward`,
    ADD COLUMN `referrer_code` VARCHAR(100) DEFAULT NULL AFTER `referrer_type`,
    ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0 AFTER `is_private`,
    ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `is_deleted`,
    ADD UNIQUE KEY `uk_account_code` (`account_code`);

-- ============================================================
-- 2. PRODUCT UPGRADE
-- Thêm: barcode, weight, origin, manufacturer, multi-price,
--        variant_attributes, images, featured_image, soft delete
-- ============================================================

CREATE TABLE IF NOT EXISTS `product_origins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_origins` (`name`) VALUES
('Việt Nam'), ('Trung Quốc'), ('Nhật Bản'), ('Hàn Quốc'), ('Mỹ'), ('Châu Âu'), ('Khác');

CREATE TABLE IF NOT EXISTS `product_manufacturers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product images
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(500) NOT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product variants (biến thể sản phẩm)
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_name` VARCHAR(255) NOT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `price` DECIMAL(15,2) DEFAULT 0,
    `stock_quantity` INT DEFAULT 0,
    `attributes` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `products`
    ADD COLUMN `barcode` VARCHAR(100) DEFAULT NULL AFTER `sku`,
    ADD COLUMN `origin_id` INT UNSIGNED DEFAULT NULL AFTER `category_id`,
    ADD COLUMN `manufacturer_id` INT UNSIGNED DEFAULT NULL AFTER `origin_id`,
    ADD COLUMN `price_wholesale` DECIMAL(15,2) DEFAULT 0 AFTER `price`,
    ADD COLUMN `price_online` DECIMAL(15,2) DEFAULT 0 AFTER `price_wholesale`,
    ADD COLUMN `discount_percent` DECIMAL(5,2) DEFAULT 0 AFTER `price_online`,
    ADD COLUMN `saleoff_price` DECIMAL(15,2) DEFAULT 0 AFTER `discount_percent`,
    ADD COLUMN `weight` DECIMAL(10,3) DEFAULT NULL AFTER `saleoff_price`,
    ADD COLUMN `featured_image` VARCHAR(500) DEFAULT NULL AFTER `image`,
    ADD COLUMN `short_description` TEXT DEFAULT NULL AFTER `description`,
    ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0 AFTER `is_active`,
    ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `is_deleted`;

-- ============================================================
-- 3. SALE ORDER UPGRADE
-- Thêm: shipping, lading_code, tracking_url, order_source,
--        order_terms, approve/cancel/restore, auto_approve, attachments
-- ============================================================

CREATE TABLE IF NOT EXISTS `order_sources` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_sources` (`name`, `sort_order`) VALUES
('Website', 1), ('Facebook', 2), ('Zalo', 3), ('Điện thoại', 4),
('Email', 5), ('Đại lý', 6), ('Khác', 7);

ALTER TABLE `orders`
    ADD COLUMN `transport_amount` DECIMAL(15,2) DEFAULT 0 AFTER `discount_type`,
    ADD COLUMN `installation_amount` DECIMAL(15,2) DEFAULT 0 AFTER `transport_amount`,
    ADD COLUMN `lading_code` VARCHAR(100) DEFAULT NULL AFTER `payment_method`,
    ADD COLUMN `tracking_url` VARCHAR(500) DEFAULT NULL AFTER `lading_code`,
    ADD COLUMN `order_source_id` INT UNSIGNED DEFAULT NULL AFTER `deal_id`,
    ADD COLUMN `campaign_id` INT UNSIGNED DEFAULT NULL AFTER `order_source_id`,
    ADD COLUMN `order_terms` TEXT DEFAULT NULL AFTER `notes`,
    ADD COLUMN `approved_by` INT UNSIGNED DEFAULT NULL AFTER `created_by`,
    ADD COLUMN `approved_at` DATETIME DEFAULT NULL AFTER `approved_by`,
    ADD COLUMN `cancelled_at` DATETIME DEFAULT NULL AFTER `approved_at`,
    ADD COLUMN `cancelled_reason` TEXT DEFAULT NULL AFTER `cancelled_at`,
    ADD COLUMN `is_auto_approve` TINYINT(1) DEFAULT 0 AFTER `cancelled_reason`,
    ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0 AFTER `is_auto_approve`,
    ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `is_deleted`;

-- Order payments (lịch sử thanh toán chi tiết)
CREATE TABLE IF NOT EXISTS `order_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `payment_via` ENUM('direct', 'point', 'fund') DEFAULT 'direct',
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `point_used` INT DEFAULT 0,
    `fund_account_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `pay_date` DATE NOT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items thêm note
ALTER TABLE `order_items`
    ADD COLUMN `note` TEXT DEFAULT NULL AFTER `total`;

-- ============================================================
-- 4. TASK UPGRADE
-- Thêm: project, parent_task, task_type, progress, color,
--        related_accounts, complete/cancel/restore workflow
-- ============================================================

-- Projects (Dự án)
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active', 'completed', 'archived') DEFAULT 'active',
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task types
CREATE TABLE IF NOT EXISTS `task_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_types` (`name`, `color`, `sort_order`) VALUES
('Công việc chung', '#405189', 1), ('Cuộc gọi', '#0ab39c', 2),
('Cuộc họp', '#f7b84b', 3), ('Demo', '#299cdb', 4),
('Follow-up', '#f06548', 5), ('Khác', '#878a99', 6);

-- Task related accounts pivot
CREATE TABLE IF NOT EXISTS `task_accounts` (
    `task_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`task_id`, `contact_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task related users (related_users)
CREATE TABLE IF NOT EXISTS `task_users` (
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`task_id`, `user_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks`
    ADD COLUMN `task_code` VARCHAR(50) DEFAULT NULL AFTER `id`,
    ADD COLUMN `project_id` INT UNSIGNED DEFAULT NULL AFTER `company_id`,
    ADD COLUMN `parent_id` INT UNSIGNED DEFAULT NULL AFTER `project_id`,
    ADD COLUMN `task_type_id` INT UNSIGNED DEFAULT NULL AFTER `parent_id`,
    ADD COLUMN `progress` INT DEFAULT 0 AFTER `priority`,
    ADD COLUMN `color` VARCHAR(7) DEFAULT NULL AFTER `progress`,
    ADD COLUMN `is_important` TINYINT(1) DEFAULT 0 AFTER `color`,
    ADD COLUMN `cancelled_at` DATETIME DEFAULT NULL AFTER `completed_at`,
    ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0 AFTER `cancelled_at`,
    ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `is_deleted`,
    ADD UNIQUE KEY `uk_task_code` (`task_code`);

-- ============================================================
-- 5. DEAL/OPPORTUNITY UPGRADE
-- Thêm: probability, opportunity_code, opportunity_status custom,
--        campaign link, amount, receipt_date
-- ============================================================

-- Opportunity custom statuses (per campaign)
CREATE TABLE IF NOT EXISTS `opportunity_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `deals`
    ADD COLUMN `deal_code` VARCHAR(50) DEFAULT NULL AFTER `id`,
    ADD COLUMN `probability` INT DEFAULT 0 AFTER `priority`,
    ADD COLUMN `campaign_id` INT UNSIGNED DEFAULT NULL AFTER `company_id`,
    ADD COLUMN `opportunity_status_id` INT UNSIGNED DEFAULT NULL AFTER `campaign_id`,
    ADD COLUMN `receipt_date` DATETIME DEFAULT NULL AFTER `actual_close_date`,
    ADD UNIQUE KEY `uk_deal_code` (`deal_code`);

-- ============================================================
-- 6. CAMPAIGN UPGRADE
-- Thêm: related_users with profit sharing, opportunity_status list,
--        allow_duplicate_opp
-- ============================================================

CREATE TABLE IF NOT EXISTS `campaign_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `accept_opp` TINYINT(1) DEFAULT 1,
    `divided_percent` DECIMAL(5,2) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `campaigns`
    ADD COLUMN `allow_duplicate_opp` TINYINT(1) DEFAULT 0 AFTER `is_locked`;

-- ============================================================
-- 7. FUND UPGRADE
-- Thêm: budget tracking, approval workflow, linked orders
-- ============================================================

ALTER TABLE `fund_transactions`
    ADD COLUMN `sheet_title` VARCHAR(255) DEFAULT NULL AFTER `description`,
    ADD COLUMN `budget_id` INT UNSIGNED DEFAULT NULL AFTER `sheet_title`,
    ADD COLUMN `employee_id` INT UNSIGNED DEFAULT NULL AFTER `budget_id`,
    ADD COLUMN `payment_method` VARCHAR(100) DEFAULT NULL AFTER `employee_id`;

-- Link fund transactions to sale/purchase orders
CREATE TABLE IF NOT EXISTS `fund_transaction_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT UNSIGNED NOT NULL,
    `order_type` ENUM('sale', 'purchase') NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (`transaction_id`) REFERENCES `fund_transactions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. WEBHOOK MODULE
-- ============================================================

CREATE TABLE IF NOT EXISTS `webhooks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `secret_key` VARCHAR(100) DEFAULT NULL,
    `events` JSON NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_triggered_at` DATETIME DEFAULT NULL,
    `last_response_code` INT DEFAULT NULL,
    `fail_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `webhook_id` INT UNSIGNED NOT NULL,
    `event` VARCHAR(100) NOT NULL,
    `payload` JSON DEFAULT NULL,
    `response_code` INT DEFAULT NULL,
    `response_body` TEXT DEFAULT NULL,
    `duration_ms` INT DEFAULT NULL,
    `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`webhook_id`) REFERENCES `webhooks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. CALL CENTER / CALL LOG MODULE
-- ============================================================

CREATE TABLE IF NOT EXISTS `call_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `call_type` ENUM('inbound', 'outbound') NOT NULL,
    `caller_number` VARCHAR(20) NOT NULL,
    `callee_number` VARCHAR(20) DEFAULT NULL,
    `extension` VARCHAR(20) DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `duration` INT DEFAULT 0,
    `status` ENUM('answered', 'missed', 'busy', 'failed', 'voicemail') DEFAULT 'answered',
    `recording_url` VARCHAR(500) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `provider_code` VARCHAR(50) DEFAULT NULL,
    `started_at` DATETIME NOT NULL,
    `answered_at` DATETIME DEFAULT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. TICKET UPGRADE - custom status configurable
-- ============================================================

CREATE TABLE IF NOT EXISTS `ticket_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `is_closed` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ticket_statuses` (`name`, `color`, `is_closed`, `sort_order`) VALUES
('Mở', '#299cdb', 0, 1),
('Đang xử lý', '#405189', 0, 2),
('Chờ phản hồi', '#f7b84b', 0, 3),
('Đã xử lý', '#0ab39c', 0, 4),
('Đóng', '#878a99', 1, 5);

-- Ticket attachments
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT DEFAULT 0,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tickets`
    ADD COLUMN `expected_at` DATE DEFAULT NULL AFTER `due_date`,
    ADD COLUMN `status_id` INT UNSIGNED DEFAULT NULL AFTER `status`;

-- ============================================================
-- 11. COMPANIES UPGRADE - thêm soft delete
-- ============================================================
ALTER TABLE `companies`
    ADD COLUMN `is_deleted` TINYINT(1) DEFAULT 0 AFTER `created_by`,
    ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `is_deleted`;

SET FOREIGN_KEY_CHECKS = 1;
