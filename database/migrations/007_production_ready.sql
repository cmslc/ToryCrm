-- ToryCRM Migration 007: Production Ready
-- Security, Multi-tenant, Billing, Help Center
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. SECURITY: Rate Limiting + 2FA + Login Attempts
-- ============================================================
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL,
    `attempts` INT DEFAULT 1,
    `last_attempt_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rate_key` (`key`),
    INDEX `idx_rate_expire` (`last_attempt_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
    ADD COLUMN `two_factor_secret` VARCHAR(255) DEFAULT NULL AFTER `password`,
    ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `two_factor_secret`,
    ADD COLUMN `login_attempts` INT DEFAULT 0 AFTER `two_factor_enabled`,
    ADD COLUMN `locked_until` DATETIME DEFAULT NULL AFTER `login_attempts`,
    ADD COLUMN `password_changed_at` DATETIME DEFAULT NULL AFTER `locked_until`;

-- ============================================================
-- 2. MULTI-TENANT
-- ============================================================
CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `domain` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(500) DEFAULT NULL,
    `plan_id` INT UNSIGNED DEFAULT NULL,
    `settings` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `trial_ends_at` DATE DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add tenant_id to all major tables
ALTER TABLE `users` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `contacts` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `companies` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `deals` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `tasks` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `products` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `orders` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `tickets` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `campaigns` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `fund_transactions` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `calendar_events` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `activities` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `purchase_orders` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `call_logs` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `notifications` ADD COLUMN `tenant_id` INT UNSIGNED DEFAULT NULL AFTER `id`;

-- Indexes for tenant isolation
ALTER TABLE `users` ADD INDEX `idx_tenant_users` (`tenant_id`);
ALTER TABLE `contacts` ADD INDEX `idx_tenant_contacts` (`tenant_id`);
ALTER TABLE `companies` ADD INDEX `idx_tenant_companies` (`tenant_id`);
ALTER TABLE `deals` ADD INDEX `idx_tenant_deals` (`tenant_id`);
ALTER TABLE `tasks` ADD INDEX `idx_tenant_tasks` (`tenant_id`);
ALTER TABLE `orders` ADD INDEX `idx_tenant_orders` (`tenant_id`);
ALTER TABLE `tickets` ADD INDEX `idx_tenant_tickets` (`tenant_id`);

-- Default tenant
INSERT INTO `tenants` (`name`, `slug`, `domain`, `is_active`, `trial_ends_at`) VALUES
('ToryCRM Demo', 'demo', 'localhost', 1, DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Set all existing data to tenant 1
UPDATE `users` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `contacts` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `companies` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `deals` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `tasks` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `products` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `orders` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `tickets` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `campaigns` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `fund_transactions` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `calendar_events` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `activities` SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE `purchase_orders` SET tenant_id = 1 WHERE tenant_id IS NULL;

-- ============================================================
-- 3. BILLING / SUBSCRIPTION
-- ============================================================
CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `price_monthly` DECIMAL(15,2) DEFAULT 0,
    `price_yearly` DECIMAL(15,2) DEFAULT 0,
    `max_users` INT DEFAULT 5,
    `max_contacts` INT DEFAULT 500,
    `max_deals` INT DEFAULT 100,
    `max_storage_mb` INT DEFAULT 1024,
    `features` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'trial', 'past_due', 'cancelled', 'expired') DEFAULT 'trial',
    `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
    `amount` DECIMAL(15,2) DEFAULT 0,
    `starts_at` DATE NOT NULL,
    `ends_at` DATE NOT NULL,
    `trial_ends_at` DATE DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `tenant_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) NOT NULL,
    `status` ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    `due_date` DATE NOT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed plans
INSERT INTO `plans` (`name`, `slug`, `description`, `price_monthly`, `price_yearly`, `max_users`, `max_contacts`, `max_deals`, `max_storage_mb`, `features`, `sort_order`) VALUES
('Starter', 'starter', 'Cho doanh nghiệp nhỏ', 499000, 4990000, 5, 500, 100, 1024,
 '["contacts","companies","deals","tasks","calendar","basic_reports"]', 1),
('Professional', 'professional', 'Cho doanh nghiệp vừa', 1499000, 14990000, 20, 5000, 1000, 5120,
 '["contacts","companies","deals","tasks","calendar","products","orders","tickets","campaigns","reports","automation","import_export"]', 2),
('Enterprise', 'enterprise', 'Cho doanh nghiệp lớn', 4999000, 49990000, -1, -1, -1, 51200,
 '["contacts","companies","deals","tasks","calendar","products","orders","tickets","campaigns","reports","automation","import_export","webhooks","api","call_center","fund","custom_fields"]', 3);

-- ============================================================
-- 4. HELP CENTER / KNOWLEDGE BASE
-- ============================================================
CREATE TABLE IF NOT EXISTS `help_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'ri-question-line',
    `description` TEXT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `help_articles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` TEXT NOT NULL,
    `is_published` TINYINT(1) DEFAULT 1,
    `view_count` INT DEFAULT 0,
    `helpful_yes` INT DEFAULT 0,
    `helpful_no` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `help_categories`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed help categories
INSERT INTO `help_categories` (`name`, `slug`, `icon`, `description`, `sort_order`) VALUES
('Bắt đầu', 'getting-started', 'ri-rocket-line', 'Hướng dẫn cài đặt và sử dụng ban đầu', 1),
('Quản lý khách hàng', 'contacts', 'ri-contacts-line', 'Quản lý contacts, companies', 2),
('Bán hàng', 'sales', 'ri-shopping-cart-line', 'Deals, orders, products, pipeline', 3),
('Marketing', 'marketing', 'ri-megaphone-line', 'Campaigns, email marketing', 4),
('Hỗ trợ khách hàng', 'support', 'ri-customer-service-line', 'Ticket system, SLA', 5),
('Báo cáo', 'reports', 'ri-bar-chart-box-line', 'Reports, analytics, dashboard', 6),
('Quản trị hệ thống', 'admin', 'ri-settings-3-line', 'Users, permissions, API, webhooks', 7);

-- Seed sample articles
INSERT INTO `help_articles` (`category_id`, `title`, `slug`, `content`, `is_published`, `created_by`) VALUES
(1, 'Đăng nhập lần đầu', 'dang-nhap-lan-dau', '<h3>Đăng nhập hệ thống</h3><p>Truy cập URL hệ thống và nhập email/mật khẩu được cấp.</p><p>Sau khi đăng nhập, bạn nên đổi mật khẩu tại <strong>Cài đặt > Đổi mật khẩu</strong>.</p>', 1, 1),
(1, 'Giới thiệu Dashboard', 'gioi-thieu-dashboard', '<h3>Dashboard</h3><p>Dashboard hiển thị tổng quan các chỉ số quan trọng:</p><ul><li>Số lượng khách hàng, deals, tasks</li><li>Biểu đồ doanh thu theo tháng</li><li>Pipeline summary</li><li>Hoạt động gần đây</li></ul><p>Bạn có thể tùy chỉnh widget tại <strong>Cài đặt > Tùy chỉnh Dashboard</strong>.</p>', 1, 1),
(2, 'Thêm khách hàng mới', 'them-khach-hang', '<h3>Thêm khách hàng</h3><p>Vào <strong>CRM > Khách hàng > Thêm khách hàng</strong>.</p><p>Điền thông tin:</p><ul><li>Họ tên (bắt buộc)</li><li>Email, Số điện thoại</li><li>Công ty, Nguồn</li><li>Trạng thái, Người phụ trách</li></ul>', 1, 1),
(2, 'Import khách hàng từ CSV', 'import-khach-hang', '<h3>Import CSV</h3><p>Vào <strong>Hệ thống > Import/Export</strong>.</p><ol><li>Tải template CSV mẫu</li><li>Điền dữ liệu theo format</li><li>Upload file CSV (UTF-8)</li><li>Kiểm tra kết quả import</li></ol>', 1, 1),
(3, 'Quản lý Pipeline', 'quan-ly-pipeline', '<h3>Deal Pipeline</h3><p>Pipeline hiển thị dạng Kanban board. Kéo thả deal giữa các giai đoạn.</p><p>Các giai đoạn mặc định: Tiếp cận → Tìm hiểu → Đề xuất → Đàm phán → Thắng/Thua</p>', 1, 1),
(3, 'Tạo đơn hàng', 'tao-don-hang', '<h3>Tạo đơn hàng</h3><p>Vào <strong>Bán hàng > Đơn hàng bán > Tạo đơn hàng</strong>.</p><p>Chọn khách hàng, thêm sản phẩm, hệ thống tự tính thuế và tổng tiền.</p>', 1, 1),
(7, 'Quản lý API Keys', 'quan-ly-api-keys', '<h3>API Keys</h3><p>Vào <strong>Cài đặt > API Keys</strong> để tạo và quản lý API keys.</p><p>Sử dụng header <code>X-API-KEY</code> cho mọi API request.</p><p>Base URL: <code>/api/v1/</code></p>', 1, 1),
(7, 'Cấu hình Webhook', 'cau-hinh-webhook', '<h3>Webhook</h3><p>Webhook tự động gửi dữ liệu đến server của bạn khi có sự kiện xảy ra.</p><p>Vào <strong>Hệ thống > Webhook > Thêm webhook</strong>, nhập URL và chọn events cần theo dõi.</p>', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
