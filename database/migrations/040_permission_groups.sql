-- Permission Groups System
-- Replaces hardcoded admin/manager/staff with custom permission groups

CREATE TABLE IF NOT EXISTS `permission_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `is_system` TINYINT(1) DEFAULT 0,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pg_tenant` (`tenant_id`),
    INDEX `idx_pg_parent` (`parent_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `permission_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_permissions` (
    `group_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`group_id`, `permission_id`),
    FOREIGN KEY (`group_id`) REFERENCES `permission_groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_permission_groups` (
    `user_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `group_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`group_id`) REFERENCES `permission_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add view_all permission for all major modules
INSERT IGNORE INTO `permissions` (`module`, `action`, `label`) VALUES
('contacts', 'view_all', 'Xem tất cả khách hàng'),
('companies', 'view_all', 'Xem tất cả doanh nghiệp'),
('deals', 'view_all', 'Xem tất cả cơ hội'),
('tasks', 'view_all', 'Xem tất cả công việc'),
('orders', 'view_all', 'Xem tất cả đơn hàng'),
('products', 'view_all', 'Xem tất cả sản phẩm'),
('tickets', 'view_all', 'Xem tất cả ticket'),
('campaigns', 'view_all', 'Xem tất cả chiến dịch'),
('fund', 'view_all', 'Xem tất cả quỹ'),
('reports', 'view_all', 'Xem tất cả báo cáo');

-- Add approve permission for modules that need it
INSERT IGNORE INTO `permissions` (`module`, `action`, `label`) VALUES
('contacts', 'approve', 'Duyệt khách hàng'),
('deals', 'approve', 'Duyệt cơ hội'),
('tasks', 'approve', 'Duyệt công việc');
