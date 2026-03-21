-- ToryCRM Migration 002: Products, Orders, Calendar Events, Notifications
-- Version 2.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Product categories
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `product_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products / Services
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('product', 'service') DEFAULT 'product',
    `unit` VARCHAR(50) DEFAULT 'Cái',
    `price` DECIMAL(15,2) DEFAULT 0,
    `cost_price` DECIMAL(15,2) DEFAULT 0,
    `tax_rate` DECIMAL(5,2) DEFAULT 0,
    `stock_quantity` INT DEFAULT 0,
    `min_stock` INT DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders (Đơn hàng / Báo giá)
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('quote', 'order') DEFAULT 'order',
    `status` ENUM('draft', 'sent', 'confirmed', 'processing', 'completed', 'cancelled') DEFAULT 'draft',
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `subtotal` DECIMAL(15,2) DEFAULT 0,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `discount_amount` DECIMAL(15,2) DEFAULT 0,
    `discount_type` ENUM('fixed', 'percent') DEFAULT 'fixed',
    `total` DECIMAL(15,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `notes` TEXT DEFAULT NULL,
    `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `paid_amount` DECIMAL(15,2) DEFAULT 0,
    `due_date` DATE DEFAULT NULL,
    `issued_date` DATE DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1,
    `unit` VARCHAR(50) DEFAULT 'Cái',
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `tax_rate` DECIMAL(5,2) DEFAULT 0,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `discount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendar events (Lịch hẹn)
CREATE TABLE IF NOT EXISTS `calendar_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `type` ENUM('meeting', 'call', 'visit', 'reminder', 'other') DEFAULT 'meeting',
    `color` VARCHAR(7) DEFAULT '#405189',
    `start_at` DATETIME NOT NULL,
    `end_at` DATETIME DEFAULT NULL,
    `all_day` TINYINT(1) DEFAULT 0,
    `location` VARCHAR(255) DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `reminder_at` DATETIME DEFAULT NULL,
    `is_completed` TINYINT(1) DEFAULT 0,
    `completed_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications (Thông báo)
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger', 'task', 'deal', 'order', 'calendar', 'system') DEFAULT 'info',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT DEFAULT NULL,
    `link` VARCHAR(500) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'ri-notification-3-line',
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data for product categories
INSERT INTO `product_categories` (`name`, `description`, `sort_order`) VALUES
('Phần mềm', 'Sản phẩm phần mềm, license', 1),
('Dịch vụ', 'Dịch vụ tư vấn, triển khai', 2),
('Phần cứng', 'Thiết bị phần cứng', 3),
('Gói dịch vụ', 'Gói combo sản phẩm + dịch vụ', 4);

SET FOREIGN_KEY_CHECKS = 1;
