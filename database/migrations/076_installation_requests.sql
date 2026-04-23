-- Installation Requests (Yêu cầu thi công)
CREATE TABLE IF NOT EXISTS `installation_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `code` VARCHAR(30) NOT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `contact_person_id` INT UNSIGNED DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `requester_name` VARCHAR(255) DEFAULT NULL,
    `requester_phone` VARCHAR(50) DEFAULT NULL,
    `contractor` VARCHAR(255) DEFAULT NULL COMMENT 'Đơn vị thi công',
    `installation_address` TEXT DEFAULT NULL,
    `customer_contact_name` VARCHAR(255) DEFAULT NULL,
    `customer_contact_phone` VARCHAR(50) DEFAULT NULL,
    `requested_date` DATE DEFAULT NULL,
    `execution_date` DATETIME DEFAULT NULL,
    `installer_name` VARCHAR(255) DEFAULT NULL,
    `condition_report` TEXT DEFAULT NULL,
    `status` ENUM('pending','scheduled','completed','cancelled') DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `is_deleted` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_code` (`tenant_id`, `code`),
    KEY (`tenant_id`),
    KEY (`order_id`),
    KEY (`contact_id`),
    KEY (`owner_id`),
    KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `installation_request_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_sku` VARCHAR(100) DEFAULT NULL,
    `size_color` VARCHAR(255) DEFAULT NULL COMMENT 'Kích thước, màu sắc',
    `unit` VARCHAR(50) DEFAULT 'Chiếc',
    `quantity` DECIMAL(15,2) DEFAULT 0,
    `check_status` VARCHAR(50) DEFAULT NULL COMMENT 'Đã kiểm/Chưa kiểm',
    `notes` TEXT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`request_id`),
    CONSTRAINT `fk_ir_items_request` FOREIGN KEY (`request_id`) REFERENCES `installation_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions for installation_requests module
INSERT IGNORE INTO permissions (module, action, label) VALUES
('installation_requests', 'view', 'Xem yêu cầu thi công'),
('installation_requests', 'create', 'Thêm yêu cầu thi công'),
('installation_requests', 'edit', 'Sửa yêu cầu thi công'),
('installation_requests', 'delete', 'Xóa yêu cầu thi công');

-- Grant to system (admin) groups
INSERT IGNORE INTO group_permissions (group_id, permission_id)
SELECT pg.id, p.id
FROM permission_groups pg
CROSS JOIN permissions p
WHERE pg.is_system = 1 AND p.module = 'installation_requests';
