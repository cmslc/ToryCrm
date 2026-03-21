-- ToryCRM Migration 006: RBAC, API Keys, Audit Log, Job Queue
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. RBAC - Permissions
-- ============================================================
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    UNIQUE KEY `uk_module_action` (`module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role` VARCHAR(20) NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role`, `permission_id`),
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed permissions
INSERT INTO `permissions` (`module`, `action`, `label`) VALUES
('contacts', 'view', 'Xem khách hàng'), ('contacts', 'create', 'Thêm khách hàng'),
('contacts', 'edit', 'Sửa khách hàng'), ('contacts', 'delete', 'Xóa khách hàng'),
('companies', 'view', 'Xem doanh nghiệp'), ('companies', 'create', 'Thêm doanh nghiệp'),
('companies', 'edit', 'Sửa doanh nghiệp'), ('companies', 'delete', 'Xóa doanh nghiệp'),
('deals', 'view', 'Xem cơ hội'), ('deals', 'create', 'Thêm cơ hội'),
('deals', 'edit', 'Sửa cơ hội'), ('deals', 'delete', 'Xóa cơ hội'),
('tasks', 'view', 'Xem công việc'), ('tasks', 'create', 'Thêm công việc'),
('tasks', 'edit', 'Sửa công việc'), ('tasks', 'delete', 'Xóa công việc'),
('products', 'view', 'Xem sản phẩm'), ('products', 'create', 'Thêm sản phẩm'),
('products', 'edit', 'Sửa sản phẩm'), ('products', 'delete', 'Xóa sản phẩm'),
('orders', 'view', 'Xem đơn hàng'), ('orders', 'create', 'Thêm đơn hàng'),
('orders', 'edit', 'Sửa đơn hàng'), ('orders', 'delete', 'Xóa đơn hàng'),
('orders', 'approve', 'Duyệt đơn hàng'),
('tickets', 'view', 'Xem ticket'), ('tickets', 'create', 'Thêm ticket'),
('tickets', 'edit', 'Sửa ticket'), ('tickets', 'delete', 'Xóa ticket'),
('campaigns', 'view', 'Xem chiến dịch'), ('campaigns', 'create', 'Thêm chiến dịch'),
('campaigns', 'edit', 'Sửa chiến dịch'), ('campaigns', 'delete', 'Xóa chiến dịch'),
('fund', 'view', 'Xem quỹ'), ('fund', 'create', 'Tạo phiếu thu/chi'),
('fund', 'confirm', 'Xác nhận phiếu'), ('fund', 'delete', 'Xóa phiếu'),
('users', 'view', 'Xem người dùng'), ('users', 'create', 'Thêm người dùng'),
('users', 'edit', 'Sửa người dùng'), ('users', 'delete', 'Khóa người dùng'),
('reports', 'view', 'Xem báo cáo'),
('automation', 'view', 'Xem automation'), ('automation', 'manage', 'Quản lý automation'),
('webhooks', 'view', 'Xem webhook'), ('webhooks', 'manage', 'Quản lý webhook'),
('settings', 'manage', 'Quản lý cài đặt'),
('import_export', 'use', 'Import / Export dữ liệu');

-- Admin gets ALL permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'admin', id FROM `permissions`;

-- Manager gets most (except users.delete, settings, webhooks.manage, automation.manage)
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'manager', id FROM `permissions`
WHERE CONCAT(module, '.', action) NOT IN ('users.delete', 'settings.manage', 'webhooks.manage', 'automation.manage');

-- Staff gets view + create + edit (no delete, approve, manage)
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'staff', id FROM `permissions`
WHERE action IN ('view', 'create', 'edit') AND module NOT IN ('users', 'automation', 'webhooks', 'settings');

-- ============================================================
-- 2. API KEYS
-- ============================================================
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `permissions` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_used_at` DATETIME DEFAULT NULL,
    `request_count` INT DEFAULT 0,
    `rate_limit` INT DEFAULT 100,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. AUDIT LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_module` (`module`, `entity_id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. JOB QUEUE
-- ============================================================
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(50) DEFAULT 'default',
    `handler` VARCHAR(255) NOT NULL,
    `payload` JSON NOT NULL,
    `attempts` INT DEFAULT 0,
    `max_attempts` INT DEFAULT 3,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `error_message` TEXT DEFAULT NULL,
    `available_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_jobs_status_queue` (`status`, `queue`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. DASHBOARD WIDGET PREFERENCES
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_widget_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `widget_key` VARCHAR(50) NOT NULL,
    `is_visible` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `settings` JSON DEFAULT NULL,
    UNIQUE KEY `uk_user_widget` (`user_id`, `widget_key`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
