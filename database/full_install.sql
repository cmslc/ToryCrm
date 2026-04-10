SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- === database/migrations/001_create_tables.sql ===
-- ToryCRM Database Schema
-- Version 1.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    `department` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact sources
CREATE TABLE IF NOT EXISTS `contact_sources` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#0ab39c',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact tags
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Companies
CREATE TABLE IF NOT EXISTS `companies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `tax_code` VARCHAR(50) DEFAULT NULL,
    `industry` VARCHAR(100) DEFAULT NULL,
    `company_size` VARCHAR(50) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts (Khách hàng)
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `mobile` VARCHAR(20) DEFAULT NULL,
    `position` VARCHAR(100) DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `source_id` INT UNSIGNED DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `date_of_birth` DATE DEFAULT NULL,
    `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    `score` INT DEFAULT 0,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `last_activity_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`source_id`) REFERENCES `contact_sources`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact tags pivot
CREATE TABLE IF NOT EXISTS `contact_tags` (
    `contact_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`contact_id`, `tag_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deal stages (Pipeline stages)
CREATE TABLE IF NOT EXISTS `deal_stages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `probability` INT DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deals (Cơ hội kinh doanh)
CREATE TABLE IF NOT EXISTS `deals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `value` DECIMAL(15,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `stage_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `expected_close_date` DATE DEFAULT NULL,
    `actual_close_date` DATE DEFAULT NULL,
    `status` ENUM('open', 'won', 'lost') DEFAULT 'open',
    `lost_reason` TEXT DEFAULT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`stage_id`) REFERENCES `deal_stages`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks (Công việc)
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `due_date` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activities (Lịch sử hoạt động)
CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('note', 'call', 'email', 'meeting', 'task', 'deal', 'system') DEFAULT 'note',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `scheduled_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom fields
CREATE TABLE IF NOT EXISTS `custom_fields` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` ENUM('contact', 'company', 'deal') NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    `field_key` VARCHAR(100) NOT NULL,
    `field_type` ENUM('text', 'number', 'date', 'select', 'textarea', 'checkbox') DEFAULT 'text',
    `options` TEXT DEFAULT NULL,
    `is_required` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom field values
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `value` TEXT DEFAULT NULL,
    FOREIGN KEY (`field_id`) REFERENCES `custom_fields`(`id`) ON DELETE CASCADE,
    INDEX `idx_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email templates
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/002_products_orders_calendar_notifications.sql ===
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

-- === database/migrations/003_tickets_campaigns_purchase_orders_fund_users.sql ===
-- ToryCRM Migration 003: Tickets, Campaigns, Purchase Orders, Fund, User Management
-- Version 3.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TICKET MODULE (Hỗ trợ khách hàng)
-- ============================================================

-- Ticket categories
CREATE TABLE IF NOT EXISTS `ticket_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tickets
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'waiting', 'resolved', 'closed') DEFAULT 'open',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `contact_phone` VARCHAR(20) DEFAULT NULL,
    `contact_email` VARCHAR(150) DEFAULT NULL,
    `due_date` DATETIME DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `closed_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `ticket_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket comments / replies
CREATE TABLE IF NOT EXISTS `ticket_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `is_internal` TINYINT(1) DEFAULT 0,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CAMPAIGN MODULE (Chiến dịch Marketing)
-- ============================================================

CREATE TABLE IF NOT EXISTS `campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('email', 'sms', 'call', 'social', 'other') DEFAULT 'email',
    `status` ENUM('draft', 'running', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    `description` TEXT DEFAULT NULL,
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `budget` DECIMAL(15,2) DEFAULT 0,
    `actual_cost` DECIMAL(15,2) DEFAULT 0,
    `target_count` INT DEFAULT 0,
    `reached_count` INT DEFAULT 0,
    `converted_count` INT DEFAULT 0,
    `is_locked` TINYINT(1) DEFAULT 0,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign members (contacts added to campaign)
CREATE TABLE IF NOT EXISTS `campaign_contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'sent', 'opened', 'clicked', 'converted', 'failed', 'unsubscribed') DEFAULT 'pending',
    `sent_at` DATETIME DEFAULT NULL,
    `opened_at` DATETIME DEFAULT NULL,
    `clicked_at` DATETIME DEFAULT NULL,
    `converted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_campaign_contact` (`campaign_id`, `contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PURCHASE ORDER MODULE (Đơn hàng mua)
-- ============================================================

CREATE TABLE IF NOT EXISTS `purchase_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `supplier_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft', 'pending', 'approved', 'receiving', 'completed', 'cancelled') DEFAULT 'draft',
    `subtotal` DECIMAL(15,2) DEFAULT 0,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `discount_amount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `notes` TEXT DEFAULT NULL,
    `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    `paid_amount` DECIMAL(15,2) DEFAULT 0,
    `expected_date` DATE DEFAULT NULL,
    `received_date` DATE DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `purchase_order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1,
    `unit` VARCHAR(50) DEFAULT 'Cái',
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `tax_rate` DECIMAL(5,2) DEFAULT 0,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `received_quantity` DECIMAL(10,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FUND MODULE (Quỹ - Phiếu thu/chi)
-- ============================================================

CREATE TABLE IF NOT EXISTS `fund_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('cash', 'bank') DEFAULT 'cash',
    `bank_name` VARCHAR(255) DEFAULT NULL,
    `bank_account` VARCHAR(100) DEFAULT NULL,
    `balance` DECIMAL(15,2) DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phiếu thu / Phiếu chi
CREATE TABLE IF NOT EXISTS `fund_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('receipt', 'payment') NOT NULL,
    `fund_account_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `category` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `transaction_date` DATE NOT NULL,
    `status` ENUM('draft', 'confirmed', 'cancelled') DEFAULT 'draft',
    `confirmed_by` INT UNSIGNED DEFAULT NULL,
    `confirmed_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`fund_account_id`) REFERENCES `fund_accounts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`confirmed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO `ticket_categories` (`name`, `color`, `sort_order`) VALUES
('Hỗ trợ kỹ thuật', '#405189', 1),
('Khiếu nại', '#f06548', 2),
('Yêu cầu tính năng', '#0ab39c', 3),
('Câu hỏi chung', '#299cdb', 4),
('Bảo hành', '#ffbe0b', 5);

INSERT INTO `fund_accounts` (`name`, `type`, `description`) VALUES
('Tiền mặt', 'cash', 'Quỹ tiền mặt công ty'),
('Ngân hàng Vietcombank', 'bank', 'Tài khoản ngân hàng chính');

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/004_deep_features_upgrade.sql ===
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

-- === database/migrations/005_indexes_and_features.sql ===
-- ToryCRM Migration 005: Indexes, CSRF, Global Search, File Upload, Import, Automation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. DATABASE INDEXES (Performance)
-- ============================================================
ALTER TABLE `contacts` ADD INDEX `idx_contacts_status` (`status`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_owner` (`owner_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_source` (`source_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_company` (`company_id`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_created` (`created_at`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_deleted` (`is_deleted`);
ALTER TABLE `contacts` ADD INDEX `idx_contacts_email` (`email`);

ALTER TABLE `deals` ADD INDEX `idx_deals_status` (`status`);
ALTER TABLE `deals` ADD INDEX `idx_deals_stage` (`stage_id`);
ALTER TABLE `deals` ADD INDEX `idx_deals_owner` (`owner_id`);
ALTER TABLE `deals` ADD INDEX `idx_deals_created` (`created_at`);

ALTER TABLE `tasks` ADD INDEX `idx_tasks_status` (`status`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_assigned` (`assigned_to`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_due` (`due_date`);
ALTER TABLE `tasks` ADD INDEX `idx_tasks_project` (`project_id`);

ALTER TABLE `orders` ADD INDEX `idx_orders_status` (`status`);
ALTER TABLE `orders` ADD INDEX `idx_orders_type` (`type`);
ALTER TABLE `orders` ADD INDEX `idx_orders_contact` (`contact_id`);
ALTER TABLE `orders` ADD INDEX `idx_orders_payment` (`payment_status`);

ALTER TABLE `tickets` ADD INDEX `idx_tickets_status` (`status`);
ALTER TABLE `tickets` ADD INDEX `idx_tickets_priority` (`priority`);
ALTER TABLE `tickets` ADD INDEX `idx_tickets_assigned` (`assigned_to`);

ALTER TABLE `activities` ADD INDEX `idx_activities_user` (`user_id`);
ALTER TABLE `activities` ADD INDEX `idx_activities_contact` (`contact_id`);
ALTER TABLE `activities` ADD INDEX `idx_activities_created` (`created_at`);

ALTER TABLE `notifications` ADD INDEX `idx_notif_user_read` (`user_id`, `is_read`);
ALTER TABLE `fund_transactions` ADD INDEX `idx_fund_type_status` (`type`, `status`);
ALTER TABLE `call_logs` ADD INDEX `idx_calls_started` (`started_at`);
ALTER TABLE `campaigns` ADD INDEX `idx_campaigns_status` (`status`);

-- ============================================================
-- 2. FILE UPLOADS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `file_uploads` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `original_name` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT DEFAULT 0,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. IMPORT LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `import_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `total_rows` INT DEFAULT 0,
    `success_count` INT DEFAULT 0,
    `error_count` INT DEFAULT 0,
    `errors` JSON DEFAULT NULL,
    `status` ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. AUTOMATION RULES
-- ============================================================
CREATE TABLE IF NOT EXISTS `automation_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `trigger_event` VARCHAR(100) NOT NULL,
    `conditions` JSON DEFAULT NULL,
    `actions` JSON NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `run_count` INT DEFAULT 0,
    `last_run_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `automation_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rule_id` INT UNSIGNED NOT NULL,
    `trigger_data` JSON DEFAULT NULL,
    `action_result` JSON DEFAULT NULL,
    `status` ENUM('success', 'failed') DEFAULT 'success',
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`rule_id`) REFERENCES `automation_rules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. EMAIL LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `to_email` VARCHAR(255) NOT NULL,
    `to_name` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `module` VARCHAR(50) DEFAULT NULL,
    `module_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('sent', 'failed', 'queued') DEFAULT 'queued',
    `error_message` TEXT DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/006_rbac_validation_api_audit_queue.sql ===
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

-- === database/migrations/007_production_ready.sql ===
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

-- === database/migrations/008_remove_bonus_points.sql ===
-- ToryCRM Migration 008: Remove bonus points feature
SET NAMES utf8mb4;

ALTER TABLE `contacts` DROP COLUMN IF EXISTS `bonus_points`;
DROP TABLE IF EXISTS `bonus_point_logs`;

-- === database/migrations/009_smart_features.sql ===
-- ToryCRM Migration 009: Smart Features
-- Smart Dashboard, Conversation Hub, Health Score, Workflow Builder, Client Portal
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CONVERSATION HUB - Unified Inbox
-- ============================================================
CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `channel` ENUM('email', 'zalo', 'facebook', 'sms', 'livechat', 'phone', 'internal') NOT NULL,
    `channel_id` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('open', 'pending', 'resolved', 'closed') DEFAULT 'open',
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `last_message_at` DATETIME DEFAULT NULL,
    `last_message_preview` VARCHAR(255) DEFAULT NULL,
    `unread_count` INT DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `tags` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_conv_tenant_status` (`tenant_id`, `status`),
    INDEX `idx_conv_assigned` (`assigned_to`),
    INDEX `idx_conv_contact` (`contact_id`),
    INDEX `idx_conv_last_msg` (`last_message_at`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `sender_type` ENUM('user', 'contact', 'system') DEFAULT 'user',
    `sender_id` INT UNSIGNED DEFAULT NULL,
    `content` TEXT NOT NULL,
    `content_type` ENUM('text', 'html', 'image', 'file', 'audio') DEFAULT 'text',
    `attachments` JSON DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_msg_conv` (`conversation_id`, `created_at`),
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canned responses (tin nhắn mẫu)
CREATE TABLE IF NOT EXISTS `canned_responses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `shortcut` VARCHAR(50) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `use_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CUSTOMER HEALTH SCORE
-- ============================================================
CREATE TABLE IF NOT EXISTS `health_scores` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT UNSIGNED NOT NULL,
    `overall_score` INT DEFAULT 50,
    `engagement_score` INT DEFAULT 50,
    `payment_score` INT DEFAULT 50,
    `support_score` INT DEFAULT 50,
    `activity_score` INT DEFAULT 50,
    `churn_risk` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    `last_interaction_at` DATETIME DEFAULT NULL,
    `days_since_interaction` INT DEFAULT 0,
    `calculated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `factors` JSON DEFAULT NULL,
    UNIQUE KEY `uk_health_contact` (`contact_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. VISUAL WORKFLOW BUILDER
-- ============================================================
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `trigger_type` VARCHAR(100) NOT NULL,
    `trigger_config` JSON DEFAULT NULL,
    `nodes` JSON NOT NULL,
    `edges` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `run_count` INT DEFAULT 0,
    `last_run_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workflow_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `node_id` VARCHAR(50) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `status` ENUM('success', 'failed', 'waiting', 'skipped') DEFAULT 'success',
    `result` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. REVENUE ANALYTICS
-- ============================================================
CREATE TABLE IF NOT EXISTS `revenue_snapshots` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `date` DATE NOT NULL,
    `total_contacts` INT DEFAULT 0,
    `new_contacts` INT DEFAULT 0,
    `total_deals` INT DEFAULT 0,
    `won_deals` INT DEFAULT 0,
    `lost_deals` INT DEFAULT 0,
    `deal_revenue` DECIMAL(15,2) DEFAULT 0,
    `order_revenue` DECIMAL(15,2) DEFAULT 0,
    `avg_deal_size` DECIMAL(15,2) DEFAULT 0,
    `avg_close_days` INT DEFAULT 0,
    `conversion_rate` DECIMAL(5,2) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_snap_tenant_date` (`tenant_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. CLIENT PORTAL
-- ============================================================
CREATE TABLE IF NOT EXISTS `portal_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TEAM COLLABORATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `mentions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `mentioned_by` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `content` TEXT DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mention_user` (`user_id`, `is_read`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mentioned_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. SMART INSIGHTS (pre-calculated daily)
-- ============================================================
CREATE TABLE IF NOT EXISTS `smart_insights` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `action_url` VARCHAR(500) DEFAULT NULL,
    `action_label` VARCHAR(100) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(20) DEFAULT 'primary',
    `priority` INT DEFAULT 0,
    `is_dismissed` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_insight_user` (`user_id`, `is_dismissed`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED: Canned responses
-- ============================================================
INSERT INTO `canned_responses` (`tenant_id`, `title`, `content`, `shortcut`, `category`) VALUES
(1, 'Chào khách hàng', 'Xin chào! Cảm ơn bạn đã liên hệ với chúng tôi. Tôi có thể giúp gì cho bạn?', '/chao', 'Chào hỏi'),
(1, 'Cảm ơn phản hồi', 'Cảm ơn bạn đã phản hồi. Chúng tôi sẽ xem xét và phản hồi sớm nhất có thể.', '/camonthanks', 'Chào hỏi'),
(1, 'Chuyển tiếp kỹ thuật', 'Tôi sẽ chuyển vấn đề này cho bộ phận kỹ thuật để hỗ trợ bạn tốt hơn. Vui lòng chờ trong giây lát.', '/chuyen', 'Hỗ trợ'),
(1, 'Xác nhận đơn hàng', 'Đơn hàng của bạn đã được xác nhận và đang được xử lý. Chúng tôi sẽ thông báo khi có cập nhật.', '/donhang', 'Đơn hàng'),
(1, 'Hẹn demo', 'Rất vui được tư vấn cho bạn. Bạn có thể cho tôi biết thời gian phù hợp để chúng tôi demo sản phẩm không?', '/demo', 'Sales');

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/009_create_conversations.sql ===
-- Conversations table
CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `contact_id` INT UNSIGNED NULL,
    `channel` ENUM('email','zalo','facebook','sms','livechat') NOT NULL DEFAULT 'email',
    `subject` VARCHAR(255) NULL,
    `status` ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
    `assigned_to` INT UNSIGNED NULL,
    `last_message_at` DATETIME NULL,
    `last_message_preview` VARCHAR(255) NULL,
    `unread_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_starred` TINYINT(1) NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_conversations_tenant` (`tenant_id`),
    INDEX `idx_conversations_contact` (`contact_id`),
    INDEX `idx_conversations_status` (`status`),
    INDEX `idx_conversations_assigned` (`assigned_to`),
    INDEX `idx_conversations_last_msg` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conversation messages table
CREATE TABLE IF NOT EXISTS `conversation_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `direction` ENUM('inbound','outbound') NOT NULL DEFAULT 'outbound',
    `content` TEXT NOT NULL,
    `sender_id` INT UNSIGNED NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_conv_messages_conv` (`conversation_id`),
    INDEX `idx_conv_messages_direction` (`direction`),
    CONSTRAINT `fk_conv_messages_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canned responses table
CREATE TABLE IF NOT EXISTS `canned_responses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_canned_responses_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- === database/migrations/009_create_workflows.sql ===
-- Workflows table
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `trigger_type` VARCHAR(100) NULL,
    `trigger_config` JSON NULL,
    `nodes` JSON NULL,
    `edges` JSON NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `run_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_run_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_workflows_is_active` (`is_active`),
    INDEX `idx_workflows_trigger_type` (`trigger_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workflow logs table
CREATE TABLE IF NOT EXISTS `workflow_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'success',
    `message` TEXT NULL,
    `context` JSON NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_workflow_logs_workflow_id` (`workflow_id`),
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portal fields on contacts table (ignore errors if columns already exist)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'portal_token');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE contacts ADD COLUMN portal_token VARCHAR(100) NULL, ADD COLUMN portal_password VARCHAR(255) NULL, ADD COLUMN portal_active TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- === database/migrations/010_phase2_features.sql ===
-- ToryCRM Migration 010: Phase 2 Features
-- Team Collaboration, Email Templates, Saved Views, SLA
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TEAM COLLABORATION - Internal Chat per entity
-- ============================================================
CREATE TABLE IF NOT EXISTS `internal_chats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `mentions` JSON DEFAULT NULL,
    `attachments` JSON DEFAULT NULL,
    `is_pinned` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chat_entity` (`entity_type`, `entity_id`),
    INDEX `idx_chat_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. EMAIL TEMPLATES with merge tags
-- ============================================================
-- Upgrade email_templates if exists, create if not
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `category` VARCHAR(100) DEFAULT 'Chung',
    `variables` JSON DEFAULT NULL,
    `thumbnail` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `use_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to existing table
SET @ct = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='email_templates' AND column_name='tenant_id');
SET @s = IF(@ct=0, 'ALTER TABLE email_templates ADD COLUMN tenant_id INT UNSIGNED DEFAULT NULL AFTER id, ADD COLUMN category VARCHAR(100) DEFAULT "Chung", ADD COLUMN variables JSON DEFAULT NULL, ADD COLUMN thumbnail VARCHAR(500) DEFAULT NULL, ADD COLUMN is_active TINYINT(1) DEFAULT 1, ADD COLUMN use_count INT DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Seed email templates
INSERT INTO `email_templates` (`tenant_id`, `name`, `subject`, `body`, `category`, `variables`) VALUES
(1, 'Chào mừng khách hàng mới', 'Chào mừng {{ten_kh}} đến với {{ten_cty}}!',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#405189">Xin chào {{ten_kh}}!</h2>
<p>Cảm ơn bạn đã tin tưởng và lựa chọn <strong>{{ten_cty}}</strong>.</p>
<p>Người phụ trách của bạn là <strong>{{nguoi_phu_trach}}</strong>. Mọi thắc mắc xin liên hệ:</p>
<ul><li>Email: {{email_npt}}</li><li>Điện thoại: {{sdt_npt}}</li></ul>
<p>Trân trọng,<br>{{ten_cty}}</p>
</div>', 'Chào hỏi', '["ten_kh","ten_cty","nguoi_phu_trach","email_npt","sdt_npt"]'),

(1, 'Báo giá sản phẩm', 'Báo giá {{ten_sp}} - {{ten_cty}}',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#405189">Báo giá sản phẩm</h2>
<p>Kính gửi {{ten_kh}},</p>
<p>Chúng tôi xin gửi báo giá cho <strong>{{ten_sp}}</strong>:</p>
<table style="width:100%;border-collapse:collapse"><tr style="background:#f3f6f9"><th style="padding:8px;border:1px solid #e9ebec;text-align:left">Sản phẩm</th><th style="padding:8px;border:1px solid #e9ebec">Đơn giá</th></tr>
<tr><td style="padding:8px;border:1px solid #e9ebec">{{ten_sp}}</td><td style="padding:8px;border:1px solid #e9ebec;text-align:center">{{don_gia}}</td></tr></table>
<p>Báo giá có hiệu lực trong 15 ngày.</p>
<p>Trân trọng,<br>{{nguoi_phu_trach}}<br>{{ten_cty}}</p>
</div>', 'Bán hàng', '["ten_kh","ten_sp","don_gia","ten_cty","nguoi_phu_trach"]'),

(1, 'Nhắc thanh toán', 'Nhắc nhở thanh toán đơn hàng {{ma_don}}',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#f06548">Nhắc nhở thanh toán</h2>
<p>Kính gửi {{ten_kh}},</p>
<p>Đơn hàng <strong>{{ma_don}}</strong> của bạn có số tiền <strong>{{so_tien}}</strong> chưa thanh toán.</p>
<p>Hạn thanh toán: <strong>{{han_thanh_toan}}</strong></p>
<p>Vui lòng thanh toán sớm để tránh gián đoạn dịch vụ.</p>
<p>Trân trọng,<br>{{ten_cty}}</p>
</div>', 'Tài chính', '["ten_kh","ma_don","so_tien","han_thanh_toan","ten_cty"]'),

(1, 'Follow-up sau demo', 'Cảm ơn {{ten_kh}} đã tham gia demo!',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#0ab39c">Cảm ơn bạn!</h2>
<p>Xin chào {{ten_kh}},</p>
<p>Cảm ơn bạn đã dành thời gian tham gia buổi demo sản phẩm. Hy vọng nội dung hữu ích cho bạn.</p>
<p>Nếu có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với tôi.</p>
<p>Trân trọng,<br>{{nguoi_phu_trach}}<br>{{sdt_npt}}</p>
</div>', 'Bán hàng', '["ten_kh","nguoi_phu_trach","sdt_npt"]');

-- ============================================================
-- 3. SAVED VIEWS (Bộ lọc đã lưu)
-- ============================================================
CREATE TABLE IF NOT EXISTS `saved_views` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `filters` JSON NOT NULL,
    `columns` JSON DEFAULT NULL,
    `sort_by` VARCHAR(50) DEFAULT NULL,
    `sort_dir` VARCHAR(4) DEFAULT 'DESC',
    `is_default` TINYINT(1) DEFAULT 0,
    `is_shared` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sv_user_module` (`user_id`, `module`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TICKET SLA
-- ============================================================
CREATE TABLE IF NOT EXISTS `sla_policies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    `first_response_hours` INT NOT NULL DEFAULT 24,
    `resolution_hours` INT NOT NULL DEFAULT 72,
    `escalate_to` INT UNSIGNED DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`escalate_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tickets`
    ADD COLUMN `sla_policy_id` INT UNSIGNED DEFAULT NULL,
    ADD COLUMN `first_response_at` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_first_response_due` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_resolution_due` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_breached` TINYINT(1) DEFAULT 0;

-- Seed SLA policies
INSERT INTO `sla_policies` (`tenant_id`, `name`, `priority`, `first_response_hours`, `resolution_hours`) VALUES
(1, 'Khẩn cấp', 'urgent', 1, 4),
(1, 'Cao', 'high', 4, 24),
(1, 'Trung bình', 'medium', 8, 48),
(1, 'Thấp', 'low', 24, 72);

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/011_saved_views.sql ===
-- Saved Views
CREATE TABLE IF NOT EXISTS saved_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    user_id INT NOT NULL,
    module VARCHAR(50) NOT NULL COMMENT 'contacts, deals, orders, tasks, tickets',
    name VARCHAR(100) NOT NULL,
    filters JSON NULL,
    columns JSON NULL,
    sort_by VARCHAR(50) NULL,
    sort_dir VARCHAR(4) DEFAULT 'DESC',
    is_shared TINYINT(1) DEFAULT 0,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_saved_views_user_module (user_id, module),
    INDEX idx_saved_views_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- === database/migrations/011_chat_and_email_templates.sql ===
-- Internal Chat & Mentions
CREATE TABLE IF NOT EXISTS `internal_chats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `entity_type` VARCHAR(50) NOT NULL COMMENT 'deal, ticket, contact, order',
    `entity_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_tenant` (`tenant_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mentions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chat` (`chat_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email Templates
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NOT NULL DEFAULT '',
    `category` VARCHAR(100) NOT NULL DEFAULT 'general',
    `body` LONGTEXT,
    `use_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant` (`tenant_id`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- === database/migrations/012_sla_policies.sql ===
-- SLA Policies table
CREATE TABLE IF NOT EXISTS sla_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    first_response_hours DECIMAL(10,2) NOT NULL DEFAULT 4,
    resolution_hours DECIMAL(10,2) NOT NULL DEFAULT 24,
    escalate_to INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escalate_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add SLA columns to tickets table
ALTER TABLE tickets
    ADD COLUMN sla_policy_id INT NULL AFTER due_date,
    ADD COLUMN sla_first_response_due DATETIME NULL AFTER sla_policy_id,
    ADD COLUMN sla_resolution_due DATETIME NULL AFTER sla_first_response_due,
    ADD COLUMN first_response_at DATETIME NULL AFTER sla_resolution_due,
    ADD COLUMN sla_breached TINYINT(1) NOT NULL DEFAULT 0 AFTER first_response_at,
    ADD CONSTRAINT fk_tickets_sla_policy FOREIGN KEY (sla_policy_id) REFERENCES sla_policies(id) ON DELETE SET NULL;

-- === database/migrations/013_phase3_integrations.sql ===
-- ToryCRM Migration 013: Phase 3 - Integrations
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. INTEGRATION SETTINGS (per tenant)
-- ============================================================
CREATE TABLE IF NOT EXISTS `integrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `provider` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `config` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `last_synced_at` DATETIME DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_provider` (`tenant_id`, `provider`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ZALO OA
-- ============================================================
CREATE TABLE IF NOT EXISTS `zalo_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `conversation_id` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `zalo_user_id` VARCHAR(100) DEFAULT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `message_type` ENUM('text', 'image', 'file', 'sticker', 'template') DEFAULT 'text',
    `content` TEXT DEFAULT NULL,
    `attachment_url` VARCHAR(500) DEFAULT NULL,
    `zalo_message_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_zalo_contact` (`contact_id`),
    INDEX `idx_zalo_conv` (`conversation_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. GOOGLE CALENDAR SYNC
-- ============================================================
CREATE TABLE IF NOT EXISTS `calendar_sync_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `provider` VARCHAR(50) DEFAULT 'google',
    `access_token` TEXT DEFAULT NULL,
    `refresh_token` TEXT DEFAULT NULL,
    `token_expires_at` DATETIME DEFAULT NULL,
    `sync_token` VARCHAR(255) DEFAULT NULL,
    `last_synced_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_provider` (`user_id`, `provider`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `calendar_events`
    ADD COLUMN `google_event_id` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `sync_status` ENUM('local', 'synced', 'conflict') DEFAULT 'local';

-- ============================================================
-- 4. VOIP / CLICK-TO-CALL
-- ============================================================
CREATE TABLE IF NOT EXISTS `voip_extensions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `extension` VARCHAR(20) NOT NULL,
    `provider` VARCHAR(50) DEFAULT 'stringee',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_ext` (`user_id`, `provider`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PAYMENT GATEWAY (VNPay, MoMo)
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `invoice_id` INT UNSIGNED DEFAULT NULL,
    `gateway` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `status` ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    `gateway_response` JSON DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pay_invoice` (`invoice_id`),
    INDEX `idx_pay_status` (`status`),
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/014_plugins.sql ===
-- Plugin System & Marketplace
-- Created: 2026-04-08

CREATE TABLE IF NOT EXISTS `plugins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `author` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'ri-plug-line',
    `category` VARCHAR(100) DEFAULT 'general',
    `config` JSON DEFAULT NULL,
    `hooks` JSON DEFAULT NULL,
    `is_installed` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 0,
    `installed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tenant_plugins` (
    `tenant_id` INT UNSIGNED NOT NULL,
    `plugin_id` INT UNSIGNED NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `config` JSON DEFAULT NULL,
    `installed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`tenant_id`, `plugin_id`),
    FOREIGN KEY (`plugin_id`) REFERENCES `plugins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed sample plugins
INSERT INTO `plugins` (`slug`, `name`, `description`, `version`, `author`, `icon`, `category`, `config`) VALUES
('google-analytics', 'Google Analytics', 'Tích hợp Google Analytics để theo dõi hành vi người dùng trên portal, landing page và form. Hỗ trợ GA4.', '1.0.0', 'ToryCRM', 'ri-line-chart-line', 'tracking', '{"fields": [{"key": "tracking_id", "label": "Measurement ID", "type": "text", "placeholder": "G-XXXXXXXXXX", "required": true}, {"key": "enable_portal", "label": "Bật tracking trên Client Portal", "type": "checkbox", "default": true}]}'),
('sms-gateway', 'SMS Gateway', 'Gửi SMS tự động qua Twilio hoặc ESMS. Hỗ trợ gửi OTP, nhắc lịch hẹn, thông báo đơn hàng.', '1.2.0', 'ToryCRM', 'ri-message-2-line', 'communication', '{"fields": [{"key": "provider", "label": "Nhà cung cấp", "type": "select", "options": ["twilio", "esms"], "required": true}, {"key": "api_key", "label": "API Key", "type": "text", "required": true}, {"key": "api_secret", "label": "API Secret", "type": "text", "required": true}, {"key": "sender_name", "label": "Tên người gửi", "type": "text", "placeholder": "ToryCRM"}]}'),
('zalo-mini-app', 'Zalo Mini App', 'Kết nối Zalo Mini App để nhận tin nhắn, gửi thông báo ZNS và quản lý khách hàng từ Zalo.', '1.0.0', 'ToryCRM', 'ri-chat-smile-2-line', 'communication', '{"fields": [{"key": "app_id", "label": "Zalo App ID", "type": "text", "required": true}, {"key": "secret_key", "label": "Secret Key", "type": "text", "required": true}, {"key": "oa_id", "label": "Official Account ID", "type": "text"}, {"key": "webhook_url", "label": "Webhook URL", "type": "text", "readonly": true}]}'),
('export-pdf-pro', 'Export PDF Pro', 'Xuất báo giá, hóa đơn, hợp đồng sang PDF chuyên nghiệp với template tùy chỉnh và logo công ty.', '2.0.0', 'ToryCRM', 'ri-file-pdf-2-line', 'productivity', '{"fields": [{"key": "company_name", "label": "Tên công ty", "type": "text"}, {"key": "company_address", "label": "Địa chỉ", "type": "textarea"}, {"key": "footer_text", "label": "Chân trang", "type": "text"}, {"key": "paper_size", "label": "Khổ giấy", "type": "select", "options": ["A4", "Letter", "A5"], "default": "A4"}]}'),
('custom-fields', 'Custom Fields Builder', 'Thêm trường dữ liệu tùy chỉnh cho khách hàng, cơ hội, đơn hàng. Hỗ trợ text, số, ngày, dropdown, checkbox.', '1.1.0', 'ToryCRM', 'ri-list-settings-line', 'productivity', '{"fields": [{"key": "max_fields", "label": "Số trường tối đa mỗi module", "type": "number", "default": 20}, {"key": "modules", "label": "Áp dụng cho module", "type": "textarea", "placeholder": "contacts, deals, orders"}]}');

-- === database/migrations/015_checkin_darkmode.sql ===
-- GPS Check-in table for field sales
CREATE TABLE IF NOT EXISTS `checkins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `latitude` DECIMAL(10,8) NOT NULL,
    `longitude` DECIMAL(11,8) NOT NULL,
    `address` VARCHAR(500) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `photo` VARCHAR(500) DEFAULT NULL,
    `check_type` ENUM('visit', 'meeting', 'delivery', 'other') DEFAULT 'visit',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_checkin_user` (`user_id`),
    INDEX `idx_checkin_contact` (`contact_id`),
    INDEX `idx_checkin_date` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dark mode preference column
ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(10) DEFAULT 'light';

-- === database/migrations/016_advanced_features.sql ===
-- ToryCRM Migration 016: Advanced Features
-- Custom Fields, Approval Chain, AI Chat, Booking
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CUSTOM FIELDS BUILDER
-- ============================================================
CREATE TABLE IF NOT EXISTS `custom_field_definitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `module` VARCHAR(50) NOT NULL,
    `field_key` VARCHAR(100) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `field_type` ENUM('text','number','email','phone','url','textarea','select','multi_select','checkbox','radio','date','datetime','file','color','currency') NOT NULL DEFAULT 'text',
    `options` JSON DEFAULT NULL,
    `default_value` VARCHAR(255) DEFAULT NULL,
    `placeholder` VARCHAR(255) DEFAULT NULL,
    `is_required` TINYINT(1) DEFAULT 0,
    `is_filterable` TINYINT(1) DEFAULT 0,
    `is_visible_in_list` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_module_key` (`tenant_id`, `module`, `field_key`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `value` TEXT DEFAULT NULL,
    UNIQUE KEY `uk_field_entity` (`field_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`field_id`) REFERENCES `custom_field_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. APPROVAL CHAIN
-- ============================================================
CREATE TABLE IF NOT EXISTS `approval_flows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `conditions` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_steps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL DEFAULT 1,
    `approver_id` INT UNSIGNED DEFAULT NULL,
    `approver_role` VARCHAR(50) DEFAULT NULL,
    `auto_approve_after_hours` INT DEFAULT NULL,
    FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `flow_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `current_step` INT DEFAULT 1,
    `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    `requested_by` INT UNSIGNED DEFAULT NULL,
    `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    INDEX `idx_approval_entity` (`entity_type`, `entity_id`),
    FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `step_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('approve','reject','comment') NOT NULL,
    `comment` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `approval_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`step_id`) REFERENCES `approval_steps`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. AI CHAT HISTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `ai_chat_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user','assistant') NOT NULL,
    `content` TEXT NOT NULL,
    `context` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ai_user` (`user_id`, `created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. BOOKING LINKS
-- ============================================================
CREATE TABLE IF NOT EXISTS `booking_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `duration_minutes` INT DEFAULT 30,
    `available_days` JSON DEFAULT NULL,
    `available_hours` JSON DEFAULT NULL,
    `buffer_minutes` INT DEFAULT 15,
    `max_advance_days` INT DEFAULT 30,
    `is_active` TINYINT(1) DEFAULT 1,
    `booking_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `link_id` INT UNSIGNED NOT NULL,
    `contact_name` VARCHAR(255) NOT NULL,
    `contact_email` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) DEFAULT NULL,
    `start_at` DATETIME NOT NULL,
    `end_at` DATETIME NOT NULL,
    `note` TEXT DEFAULT NULL,
    `status` ENUM('confirmed','cancelled','completed','no_show') DEFAULT 'confirmed',
    `calendar_event_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`link_id`) REFERENCES `booking_links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/017_gamification.sql ===
-- Gamification tables
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(50) DEFAULT 'ri-trophy-line',
    `color` VARCHAR(20) DEFAULT 'warning',
    `criteria_type` VARCHAR(50) NOT NULL,
    `criteria_value` INT NOT NULL DEFAULT 1,
    `points` INT DEFAULT 10,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_achievements` (
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` INT UNSIGNED NOT NULL,
    `earned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `achievement_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `leaderboard_snapshots` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `period` VARCHAR(7) NOT NULL,
    `deals_won` INT DEFAULT 0,
    `revenue` DECIMAL(15,2) DEFAULT 0,
    `activities_count` INT DEFAULT 0,
    `points` INT DEFAULT 0,
    `rank_position` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_period` (`user_id`, `period`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- AI Chat History
CREATE TABLE IF NOT EXISTS `ai_chat_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user','assistant') NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Booking Links
CREATE TABLE IF NOT EXISTS `booking_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `duration` INT NOT NULL DEFAULT 30,
    `available_days` VARCHAR(50) DEFAULT '1,2,3,4,5',
    `start_time` TIME DEFAULT '08:00:00',
    `end_time` TIME DEFAULT '17:00:00',
    `buffer_minutes` INT DEFAULT 15,
    `max_advance_days` INT DEFAULT 30,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `booking_link_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(255) NOT NULL,
    `guest_email` VARCHAR(255) NOT NULL,
    `guest_phone` VARCHAR(50) DEFAULT NULL,
    `note` TEXT,
    `booking_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('confirmed','cancelled') DEFAULT 'confirmed',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_link_id`) REFERENCES `booking_links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed achievements
INSERT IGNORE INTO `achievements` (`slug`, `name`, `description`, `icon`, `color`, `criteria_type`, `criteria_value`, `points`) VALUES
('first-deal', 'Deal đầu tiên', 'Chiến thắng deal đầu tiên của bạn', 'ri-hand-coin-line', 'success', 'deals_won', 1, 10),
('rising-star', 'Ngôi sao mới', 'Chiến thắng 5 deal', 'ri-star-line', 'warning', 'deals_won', 5, 25),
('warrior', 'Chiến binh', 'Chiến thắng 10 deal', 'ri-sword-line', 'danger', 'deals_won', 10, 50),
('top-performer', 'Top performer', 'Doanh thu cao nhất tháng', 'ri-vip-crown-line', 'primary', 'top_revenue', 1, 100),
('hard-worker', 'Chăm chỉ', 'Hoàn thành 50 hoạt động trong tháng', 'ri-run-line', 'info', 'monthly_activities', 50, 30),
('connector', 'Kết nối', 'Tạo 20 liên hệ khách hàng', 'ri-links-line', 'secondary', 'contacts_created', 20, 20),
('support-hero', 'Hỗ trợ tốt', 'Giải quyết 10 ticket hỗ trợ', 'ri-customer-service-line', 'success', 'tickets_resolved', 10, 30);

-- === database/migrations/018_finance_complete.sql ===
-- ToryCRM Migration 018: Complete Finance Module
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CÔNG NỢ (Receivables / Payables)
-- ============================================================
CREATE TABLE IF NOT EXISTS `debts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('receivable', 'payable') NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `purchase_order_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `paid_amount` DECIMAL(15,2) DEFAULT 0,
    `remaining` DECIMAL(15,2) GENERATED ALWAYS AS (amount - paid_amount) STORED,
    `due_date` DATE DEFAULT NULL,
    `status` ENUM('open', 'partial', 'paid', 'overdue', 'written_off') DEFAULT 'open',
    `description` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_debt_type` (`type`, `status`),
    INDEX `idx_debt_contact` (`contact_id`),
    INDEX `idx_debt_company` (`company_id`),
    INDEX `idx_debt_due` (`due_date`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `debt_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `debt_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `fund_transaction_id` INT UNSIGNED DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `paid_at` DATE NOT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`debt_id`) REFERENCES `debts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. HỢP ĐỒNG
-- ============================================================
CREATE TABLE IF NOT EXISTS `contracts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `contract_number` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('service', 'product', 'maintenance', 'consulting', 'other') DEFAULT 'service',
    `status` ENUM('draft', 'sent', 'negotiating', 'signed', 'active', 'expired', 'cancelled', 'terminated') DEFAULT 'draft',
    `value` DECIMAL(15,2) DEFAULT 0,
    `recurring_value` DECIMAL(15,2) DEFAULT 0,
    `recurring_cycle` ENUM('monthly', 'quarterly', 'yearly', 'none') DEFAULT 'none',
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `signed_date` DATE DEFAULT NULL,
    `auto_renew` TINYINT(1) DEFAULT 0,
    `renew_before_days` INT DEFAULT 30,
    `payment_terms` TEXT DEFAULT NULL,
    `terms_conditions` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `file_path` VARCHAR(500) DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `signed_by` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_contract_number` (`tenant_id`, `contract_number`),
    INDEX `idx_contract_status` (`status`),
    INDEX `idx_contract_dates` (`start_date`, `end_date`),
    INDEX `idx_contract_contact` (`contact_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. BÁO GIÁ (Quotations - standalone)
-- ============================================================
CREATE TABLE IF NOT EXISTS `quotations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `quote_number` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `company_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    `subtotal` DECIMAL(15,2) DEFAULT 0,
    `tax_amount` DECIMAL(15,2) DEFAULT 0,
    `discount_amount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'VND',
    `valid_until` DATE DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `terms` TEXT DEFAULT NULL,
    `client_note` TEXT DEFAULT NULL,
    `accepted_at` DATETIME DEFAULT NULL,
    `rejected_at` DATETIME DEFAULT NULL,
    `rejection_reason` TEXT DEFAULT NULL,
    `portal_token` VARCHAR(64) DEFAULT NULL,
    `view_count` INT DEFAULT 0,
    `last_viewed_at` DATETIME DEFAULT NULL,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_quote_number` (`tenant_id`, `quote_number`),
    INDEX `idx_quote_status` (`status`),
    INDEX `idx_quote_contact` (`contact_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `quotation_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1,
    `unit` VARCHAR(50) DEFAULT NULL,
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `tax_rate` DECIMAL(5,2) DEFAULT 0,
    `discount` DECIMAL(15,2) DEFAULT 0,
    `total` DECIMAL(15,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. NGÂN SÁCH (Budget)
-- ============================================================
CREATE TABLE IF NOT EXISTS `budgets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('department', 'project', 'campaign', 'general') DEFAULT 'general',
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `total_budget` DECIMAL(15,2) DEFAULT 0,
    `spent_amount` DECIMAL(15,2) DEFAULT 0,
    `remaining` DECIMAL(15,2) GENERATED ALWAYS AS (total_budget - spent_amount) STORED,
    `status` ENUM('draft', 'approved', 'active', 'closed', 'exceeded') DEFAULT 'draft',
    `department` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `budget_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `budget_id` INT UNSIGNED NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `planned_amount` DECIMAL(15,2) DEFAULT 0,
    `actual_amount` DECIMAL(15,2) DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`budget_id`) REFERENCES `budgets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. HOA HỒNG (Commission)
-- ============================================================
CREATE TABLE IF NOT EXISTS `commission_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('fixed', 'percentage') DEFAULT 'percentage',
    `value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `apply_to` ENUM('deal', 'order', 'product') DEFAULT 'deal',
    `product_id` INT UNSIGNED DEFAULT NULL,
    `min_value` DECIMAL(15,2) DEFAULT 0,
    `max_value` DECIMAL(15,2) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `commissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `rule_id` INT UNSIGNED DEFAULT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `base_value` DECIMAL(15,2) DEFAULT 0,
    `rate` DECIMAL(10,2) DEFAULT 0,
    `status` ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    `paid_at` DATETIME DEFAULT NULL,
    `period` VARCHAR(7) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_commission_user` (`user_id`, `status`),
    INDEX `idx_commission_period` (`period`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rule_id`) REFERENCES `commission_rules`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed commission rules
-- ============================================================
INSERT INTO `commission_rules` (`tenant_id`, `name`, `type`, `value`, `apply_to`, `min_value`) VALUES
(1, 'Hoa hồng deal cơ bản', 'percentage', 5.00, 'deal', 10000000),
(1, 'Hoa hồng deal lớn (>100tr)', 'percentage', 8.00, 'deal', 100000000),
(1, 'Hoa hồng đơn hàng', 'percentage', 3.00, 'order', 0);

SET FOREIGN_KEY_CHECKS = 1;

-- === database/migrations/019_debts_contracts.sql ===
-- Debts (Công nợ)
CREATE TABLE IF NOT EXISTS `debts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('receivable', 'payable') NOT NULL DEFAULT 'receivable' COMMENT 'Phải thu / Phải trả',
    `contact_id` INT UNSIGNED NULL,
    `company_id` INT UNSIGNED NULL,
    `order_id` INT UNSIGNED NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `due_date` DATE NULL,
    `status` ENUM('open', 'partial', 'paid', 'overdue', 'written_off') NOT NULL DEFAULT 'open',
    `note` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_debts_type` (`type`),
    INDEX `idx_debts_status` (`status`),
    INDEX `idx_debts_contact` (`contact_id`),
    INDEX `idx_debts_company` (`company_id`),
    INDEX `idx_debts_due_date` (`due_date`),
    INDEX `idx_debts_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Debt Payments (Lịch sử thanh toán công nợ)
CREATE TABLE IF NOT EXISTS `debt_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `debt_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash',
    `payment_date` DATE NOT NULL,
    `note` TEXT NULL,
    `recorded_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_debt_payments_debt` (`debt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contracts (Hợp đồng)
CREATE TABLE IF NOT EXISTS `contracts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_number` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'service',
    `status` ENUM('draft', 'sent', 'signed', 'active', 'expired', 'cancelled') NOT NULL DEFAULT 'draft',
    `value` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `recurring_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `recurring_cycle` VARCHAR(20) NULL COMMENT 'monthly, quarterly, yearly',
    `contact_id` INT UNSIGNED NULL,
    `company_id` INT UNSIGNED NULL,
    `deal_id` INT UNSIGNED NULL,
    `owner_id` INT UNSIGNED NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `signed_date` DATE NULL,
    `auto_renew` TINYINT(1) NOT NULL DEFAULT 0,
    `parent_contract_id` INT UNSIGNED NULL COMMENT 'Hợp đồng gốc nếu gia hạn',
    `notes` TEXT NULL,
    `terms` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_contract_number` (`contract_number`),
    INDEX `idx_contracts_status` (`status`),
    INDEX `idx_contracts_contact` (`contact_id`),
    INDEX `idx_contracts_company` (`company_id`),
    INDEX `idx_contracts_deal` (`deal_id`),
    INDEX `idx_contracts_end_date` (`end_date`),
    INDEX `idx_contracts_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add contract_id to orders table for linking
ALTER TABLE `orders` ADD COLUMN `contract_id` INT UNSIGNED NULL AFTER `deal_id`;
ALTER TABLE `orders` ADD INDEX `idx_orders_contract` (`contract_id`);

-- === database/migrations/020_crm_upgrade.sql ===
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

-- === database/migrations/custom_fields_approvals.sql ===
-- Custom Field Definitions
CREATE TABLE IF NOT EXISTS `custom_field_definitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL COMMENT 'contacts, deals, orders, tasks, tickets, products',
    `field_label` VARCHAR(255) NOT NULL,
    `field_key` VARCHAR(100) NOT NULL,
    `field_type` VARCHAR(50) NOT NULL DEFAULT 'text' COMMENT 'text, number, email, phone, url, textarea, select, multi_select, checkbox, radio, date, datetime, file, color, currency',
    `options` TEXT NULL COMMENT 'Options for select/radio/checkbox, one per line',
    `default_value` VARCHAR(255) NULL,
    `placeholder` VARCHAR(255) NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `is_filterable` TINYINT(1) NOT NULL DEFAULT 0,
    `show_in_list` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_field_key_module_tenant` (`field_key`, `module`, `tenant_id`),
    INDEX `idx_module_tenant` (`module`, `tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom Field Values
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `field_value` TEXT NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_field_entity` (`field_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    CONSTRAINT `fk_cfv_field` FOREIGN KEY (`field_id`) REFERENCES `custom_field_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Flows
CREATE TABLE IF NOT EXISTS `approval_flows` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL COMMENT 'orders, deals, purchase_orders, fund_transactions',
    `conditions` JSON NULL COMMENT 'JSON conditions for when this flow applies',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_module_tenant` (`module`, `tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Flow Steps
CREATE TABLE IF NOT EXISTS `approval_flow_steps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL DEFAULT 1,
    `step_label` VARCHAR(255) NULL,
    `approver_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_flow_order` (`flow_id`, `step_order`),
    CONSTRAINT `fk_afs_flow` FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Requests
CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT UNSIGNED NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `requested_by` INT UNSIGNED NOT NULL,
    `current_step` INT NOT NULL DEFAULT 1,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `completed_at` DATETIME NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_ar_flow` FOREIGN KEY (`flow_id`) REFERENCES `approval_flows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Actions (history)
CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `step_order` INT NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `action` ENUM('approved', 'rejected') NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_request` (`request_id`),
    CONSTRAINT `fk_aa_request` FOREIGN KEY (`request_id`) REFERENCES `approval_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- === database/migrations/create_commissions_tables.sql ===
-- Commission Rules
CREATE TABLE IF NOT EXISTS commission_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(15, 2) NOT NULL DEFAULT 0,
    apply_to ENUM('deal', 'order') NOT NULL DEFAULT 'deal',
    min_value DECIMAL(15, 2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_apply_to (apply_to, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commissions
CREATE TABLE IF NOT EXISTS commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    user_id INT NOT NULL,
    entity_type ENUM('deal', 'order') NOT NULL,
    entity_id INT NOT NULL,
    rule_id INT DEFAULT NULL,
    base_amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    rate DECIMAL(10, 2) NOT NULL DEFAULT 0,
    rate_type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'approved', 'paid') NOT NULL DEFAULT 'pending',
    approved_at DATETIME DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    FOREIGN KEY (rule_id) REFERENCES commission_rules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- === database/seeds/seed.sql ===
-- ToryCRM Seed Data

-- Default admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
('Admin', 'admin@torycrm.com', '$2y$10$Ww5sQbe.Nt29/7U2GHWsV.mOufKGLtVcNSORJ7Knp35X31rSLP2o6', 'admin', 1);

-- Contact sources
INSERT INTO `contact_sources` (`name`, `color`, `sort_order`) VALUES
('Website', '#405189', 1),
('Facebook', '#3b5998', 2),
('Zalo', '#0068ff', 3),
('Điện thoại', '#0ab39c', 4),
('Email', '#f06548', 5),
('Giới thiệu', '#f7b84b', 6),
('Sự kiện', '#299cdb', 7),
('Khác', '#878a99', 8);

-- Default tags
INSERT INTO `tags` (`name`, `color`) VALUES
('VIP', '#f06548'),
('Tiềm năng', '#0ab39c'),
('Đối tác', '#405189'),
('Nhà cung cấp', '#f7b84b'),
('Khách hàng mới', '#299cdb');

-- Deal stages (Pipeline)
INSERT INTO `deal_stages` (`name`, `color`, `probability`, `sort_order`) VALUES
('Tiếp cận', '#405189', 10, 1),
('Tìm hiểu nhu cầu', '#299cdb', 25, 2),
('Đề xuất giải pháp', '#f7b84b', 50, 3),
('Báo giá', '#0ab39c', 75, 4),
('Đàm phán', '#f06548', 90, 5),
('Thắng', '#0ab39c', 100, 6),
('Thua', '#f06548', 0, 7);

-- === database/seeds/demo_data.sql ===
-- ToryCRM Demo Data
-- Dữ liệu mẫu cho tất cả các module

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USERS (Thêm users demo)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `department`, `is_active`, `last_login`) VALUES
('Nguyễn Văn Hùng', 'hung.nv@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 'manager', 'Kinh doanh', 1, NOW()),
('Trần Thị Mai', 'mai.tt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'staff', 'Kinh doanh', 1, NOW()),
('Lê Minh Tuấn', 'tuan.lm@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'staff', 'Marketing', 1, NOW()),
('Phạm Thị Hương', 'huong.pt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'staff', 'Hỗ trợ', 1, NULL),
('Hoàng Đức Anh', 'anh.hd@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0945678901', 'manager', 'Kỹ thuật', 1, NOW());
-- Password cho tất cả: password

-- ============================================================
-- COMPANIES (Doanh nghiệp)
-- ============================================================
INSERT INTO `companies` (`name`, `email`, `phone`, `website`, `address`, `city`, `tax_code`, `industry`, `company_size`, `description`, `owner_id`, `created_by`) VALUES
('Công ty TNHH Phần mềm ABC', 'info@abc-soft.vn', '02812345678', 'https://abc-soft.vn', '123 Nguyễn Huệ, Quận 1', 'Hồ Chí Minh', '0312345678', 'Công nghệ', '50-100', 'Công ty phần mềm hàng đầu', 2, 1),
('Tập đoàn XYZ Việt Nam', 'contact@xyz.vn', '02887654321', 'https://xyz.vn', '456 Lê Lợi, Quận 3', 'Hồ Chí Minh', '0398765432', 'Thương mại', '100-500', 'Tập đoàn thương mại đa ngành', 2, 1),
('Công ty CP Đầu tư Minh Phát', 'info@minhphat.com', '02456789012', 'https://minhphat.com', '789 Trần Hưng Đạo', 'Hà Nội', '0109876543', 'Tài chính', '20-50', 'Đầu tư và phát triển bất động sản', 3, 1),
('Startup TechViet', 'hello@techviet.io', '0967891234', NULL, '12 Hoàng Diệu, Quận 4', 'Hồ Chí Minh', '0315678901', 'Công nghệ', '10-20', 'Startup AI và Machine Learning', 3, 1),
('Công ty Logistics Toàn Cầu', 'sales@globallog.vn', '02834567890', 'https://globallog.vn', '321 Võ Văn Tần, Quận 3', 'Hồ Chí Minh', '0317654321', 'Vận tải', '200-500', 'Dịch vụ logistics quốc tế', 2, 1),
('Nhà máy Sản xuất Thành Đạt', 'factory@thanhdat.vn', '02741234567', 'https://thanhdat.vn', 'KCN Bình Dương', 'Bình Dương', '3702345678', 'Sản xuất', '500+', 'Sản xuất linh kiện điện tử', 3, 1),
('Công ty Truyền thông Sáng Tạo', 'creative@sangta.vn', '0981234567', NULL, '56 Nguyễn Thị Minh Khai', 'Hồ Chí Minh', '0319876543', 'Truyền thông', '10-20', 'Agency quảng cáo và truyền thông', 4, 1),
('Bệnh viện Đa khoa Hòa Bình', 'info@bvhoabinh.vn', '02891234567', 'https://bvhoabinh.vn', '100 Điện Biên Phủ', 'Hồ Chí Minh', '0318765432', 'Y tế', '100-500', 'Bệnh viện tư nhân chất lượng cao', 2, 1);

-- ============================================================
-- CONTACTS (Khách hàng)
-- ============================================================
INSERT INTO `contacts` (`first_name`, `last_name`, `email`, `phone`, `mobile`, `position`, `company_id`, `source_id`, `address`, `city`, `gender`, `status`, `score`, `owner_id`, `created_by`, `description`) VALUES
('Nguyễn', 'Văn An', 'an.nv@abc-soft.vn', '0901111111', '0901111111', 'Giám đốc', 1, 1, '123 Nguyễn Huệ', 'Hồ Chí Minh', 'male', 'qualified', 85, 2, 1, 'KH tiềm năng cao, quan tâm giải pháp CRM'),
('Trần', 'Thị Bình', 'binh.tt@xyz.vn', '0912222222', '0912222222', 'Trưởng phòng IT', 2, 2, '456 Lê Lợi', 'Hồ Chí Minh', 'female', 'contacted', 60, 2, 1, 'Đã demo sản phẩm, đang cân nhắc'),
('Lê', 'Hoàng Cường', 'cuong.lh@minhphat.com', '0923333333', '0923333333', 'Phó GĐ', 3, 3, '789 Trần Hưng Đạo', 'Hà Nội', 'male', 'new', 30, 3, 1, 'Liên hệ qua hotline'),
('Phạm', 'Thị Dung', 'dung.pt@techviet.io', '0934444444', '0934444444', 'CEO', 4, 1, '12 Hoàng Diệu', 'Hồ Chí Minh', 'female', 'converted', 95, 3, 1, 'Đã ký hợp đồng 12 tháng'),
('Hoàng', 'Minh Em', 'em.hm@globallog.vn', '0945555555', '0945555555', 'Giám đốc KD', 5, 4, '321 Võ Văn Tần', 'Hồ Chí Minh', 'male', 'qualified', 70, 2, 1, 'Cần giải pháp quản lý đơn hàng'),
('Võ', 'Thị Phượng', 'phuong.vt@gmail.com', '0956666666', '0956666666', 'Kế toán trưởng', 6, 5, 'KCN Bình Dương', 'Bình Dương', 'female', 'contacted', 45, 3, 1, 'Quan tâm module quỹ thu chi'),
('Đặng', 'Quốc Giang', 'giang.dq@sangta.vn', '0967777777', '0967777777', 'Creative Director', 7, 6, '56 NTMK', 'Hồ Chí Minh', 'male', 'new', 20, 4, 1, 'Đăng ký qua Facebook'),
('Bùi', 'Thị Hạnh', 'hanh.bt@bvhoabinh.vn', '0978888888', '0978888888', 'Trưởng phòng HC', 8, 7, '100 Điện Biên Phủ', 'Hồ Chí Minh', 'female', 'new', 15, 2, 1, 'Gọi điện hỏi thông tin'),
('Trịnh', 'Văn Kiên', 'kien.tv@gmail.com', '0989999999', NULL, 'Freelancer', NULL, 2, '45 Bạch Đằng', 'Đà Nẵng', 'male', 'lost', 10, 3, 1, 'Ngân sách không phù hợp'),
('Ngô', 'Thị Linh', 'linh.nt@abc-soft.vn', '0990000000', '0990000000', 'Product Manager', 1, 1, '123 Nguyễn Huệ', 'Hồ Chí Minh', 'female', 'qualified', 75, 2, 1, 'Đồng nghiệp anh An, quan tâm module task'),
('Lý', 'Minh Nhật', 'nhat.lm@xyz.vn', '0911234567', '0911234567', 'CFO', 2, 3, '456 Lê Lợi', 'Hồ Chí Minh', 'male', 'contacted', 55, 2, 1, 'Cần báo giá chi tiết'),
('Đinh', 'Thị Oanh', 'oanh.dt@outlook.com', '0922345678', '0922345678', 'Chủ cửa hàng', NULL, 4, '78 Hai Bà Trưng', 'Hà Nội', 'female', 'new', 25, 3, 1, 'Gọi điện tư vấn ban đầu'),
('Phan', 'Quốc Phong', 'phong.pq@minhphat.com', '0933456789', NULL, 'IT Manager', 3, 5, '789 THP', 'Hà Nội', 'male', 'qualified', 80, 3, 1, 'Yêu cầu demo tại chỗ'),
('Hồ', 'Thị Quỳnh', 'quynh.ht@techviet.io', '0944567890', '0944567890', 'CTO', 4, 1, '12 Hoàng Diệu', 'Hồ Chí Minh', 'female', 'converted', 90, 3, 1, 'Đồng sáng lập TechViet'),
('Mai', 'Văn Sơn', 'son.mv@globallog.vn', '0955678901', '0955678901', 'Trưởng kho', 5, 6, '321 Võ Văn Tần', 'Hồ Chí Minh', 'male', 'contacted', 40, 2, 1, 'Quan tâm quản lý kho hàng');

-- ============================================================
-- DEALS (Cơ hội kinh doanh)
-- ============================================================
INSERT INTO `deals` (`title`, `value`, `stage_id`, `contact_id`, `company_id`, `owner_id`, `description`, `expected_close_date`, `actual_close_date`, `status`, `priority`, `created_by`) VALUES
('CRM cho ABC Software', 150000000, 4, 1, 1, 2, 'Triển khai CRM cho toàn bộ team sales', '2026-04-15', NULL, 'open', 'high', 1),
('Gói Enterprise cho XYZ', 500000000, 3, 2, 2, 2, 'Gói enterprise cho tập đoàn XYZ', '2026-05-01', NULL, 'open', 'urgent', 1),
('TechViet - Gói startup', 50000000, 6, 4, 4, 3, 'Gói CRM cho startup', '2026-03-01', '2026-02-28', 'won', 'medium', 1),
('Logistics Toàn Cầu - Module kho', 200000000, 2, 5, 5, 2, 'Module quản lý kho và đơn hàng', '2026-06-01', NULL, 'open', 'high', 1),
('Minh Phát - Tư vấn triển khai', 80000000, 5, 3, 3, 3, 'Tư vấn và triển khai CRM', '2026-04-30', NULL, 'open', 'medium', 1),
('Thành Đạt - ERP tích hợp', 350000000, 1, 6, 6, 3, 'Tích hợp CRM với ERP hiện tại', '2026-07-15', NULL, 'open', 'high', 1),
('Sáng Tạo Agency - Gói SME', 30000000, 7, 7, 7, 4, 'Gói nhỏ cho agency', '2026-02-15', NULL, 'lost', 'low', 1),
('BV Hòa Bình - Quản lý bệnh nhân', 120000000, 3, 8, 8, 2, 'Module CRM cho bệnh viện', '2026-05-30', NULL, 'open', 'medium', 1),
('ABC Software - Module HR', 75000000, 4, 10, 1, 2, 'Thêm module quản lý nhân sự', '2026-04-20', NULL, 'open', 'medium', 1),
('XYZ - Gói đào tạo', 45000000, 6, 11, 2, 2, 'Đào tạo sử dụng CRM', '2026-03-10', '2026-03-08', 'won', 'low', 1);

-- ============================================================
-- TASKS (Công việc)
-- ============================================================
INSERT INTO `tasks` (`title`, `description`, `status`, `priority`, `due_date`, `completed_at`, `contact_id`, `deal_id`, `assigned_to`, `created_by`) VALUES
('Gọi điện xác nhận lịch demo ABC', 'Xác nhận lịch demo CRM với anh An - ABC Software', 'done', 'high', '2026-03-18 10:00:00', '2026-03-18 09:30:00', 1, 1, 2, 1),
('Chuẩn bị proposal cho XYZ', 'Soạn proposal chi tiết gói Enterprise cho tập đoàn XYZ', 'in_progress', 'urgent', '2026-03-22 17:00:00', NULL, 2, 2, 2, 1),
('Follow up Minh Phát', 'Gọi lại anh Cường hỏi phản hồi sau demo', 'todo', 'medium', '2026-03-25 14:00:00', NULL, 3, 5, 3, 1),
('Gửi hợp đồng TechViet', 'Gửi hợp đồng gia hạn cho TechViet', 'done', 'high', '2026-03-15 10:00:00', '2026-03-14 16:00:00', 4, 3, 3, 1),
('Tạo tài liệu hướng dẫn', 'Viết tài liệu hướng dẫn sử dụng cho module mới', 'in_progress', 'medium', '2026-03-28 17:00:00', NULL, NULL, NULL, 4, 1),
('Demo module kho cho GlobalLog', 'Demo trực tiếp tại văn phòng khách hàng', 'todo', 'high', '2026-03-24 09:00:00', NULL, 5, 4, 2, 1),
('Báo giá lại cho Sáng Tạo', 'Điều chỉnh giá và gửi lại báo giá', 'todo', 'low', '2026-03-30 17:00:00', NULL, 7, 7, 4, 1),
('Review code module ticket', 'Review và test module ticket mới phát triển', 'review', 'high', '2026-03-21 17:00:00', NULL, NULL, NULL, 6, 1),
('Họp team sales tuần', 'Họp review kết quả tuần và plan tuần tới', 'todo', 'medium', '2026-03-24 08:30:00', NULL, NULL, NULL, 2, 1),
('Onboarding TechViet', 'Hỗ trợ setup và đào tạo cho TechViet', 'in_progress', 'high', '2026-03-26 10:00:00', NULL, 14, 3, 3, 1);

-- ============================================================
-- PRODUCTS (Sản phẩm & Dịch vụ)
-- ============================================================
INSERT INTO `products` (`name`, `sku`, `category_id`, `type`, `unit`, `price`, `cost_price`, `tax_rate`, `stock_quantity`, `min_stock`, `description`, `is_active`, `created_by`) VALUES
('ToryCRM - Gói Starter', 'CRM-START', 1, 'service', 'Tháng', 500000, 0, 10, 0, 0, 'Gói cơ bản cho doanh nghiệp nhỏ, tối đa 5 users', 1, 1),
('ToryCRM - Gói Professional', 'CRM-PRO', 1, 'service', 'Tháng', 1500000, 0, 10, 0, 0, 'Gói chuyên nghiệp, tối đa 20 users, đầy đủ tính năng', 1, 1),
('ToryCRM - Gói Enterprise', 'CRM-ENT', 1, 'service', 'Tháng', 5000000, 0, 10, 0, 0, 'Gói doanh nghiệp lớn, không giới hạn users, tùy chỉnh', 1, 1),
('Dịch vụ triển khai', 'SVC-DEPLOY', 2, 'service', 'Dự án', 15000000, 5000000, 10, 0, 0, 'Dịch vụ triển khai và cấu hình ban đầu', 1, 1),
('Dịch vụ đào tạo', 'SVC-TRAIN', 2, 'service', 'Buổi', 3000000, 1000000, 10, 0, 0, 'Đào tạo sử dụng CRM (4 giờ/buổi)', 1, 1),
('Dịch vụ tư vấn', 'SVC-CONSULT', 2, 'service', 'Giờ', 1000000, 300000, 10, 0, 0, 'Tư vấn quy trình và tối ưu CRM', 1, 1),
('Module Email Marketing', 'MOD-EMAIL', 1, 'service', 'Tháng', 800000, 0, 10, 0, 0, 'Module gửi email marketing tự động', 1, 1),
('Module SMS Marketing', 'MOD-SMS', 1, 'service', 'Tháng', 600000, 0, 10, 0, 0, 'Module gửi SMS hàng loạt', 1, 1),
('Máy tính xách tay Dell', 'HW-DELL-01', 3, 'product', 'Cái', 25000000, 20000000, 10, 15, 3, 'Dell Latitude 5540 i7 16GB', 1, 1),
('Màn hình Dell 27 inch', 'HW-MON-27', 3, 'product', 'Cái', 8000000, 6000000, 10, 10, 2, 'Dell P2723QE 4K USB-C', 1, 1),
('Gói CRM + Triển khai + Đào tạo', 'PKG-FULL', 4, 'service', 'Gói', 25000000, 8000000, 10, 0, 0, 'Gói trọn bộ: License 1 năm + Triển khai + 2 buổi đào tạo', 1, 1);

-- ============================================================
-- ORDERS (Đơn hàng bán)
-- ============================================================
INSERT INTO `orders` (`order_number`, `type`, `status`, `contact_id`, `company_id`, `deal_id`, `subtotal`, `tax_amount`, `discount_amount`, `discount_type`, `total`, `notes`, `payment_status`, `payment_method`, `paid_amount`, `due_date`, `issued_date`, `owner_id`, `created_by`) VALUES
('DH2603001', 'order', 'completed', 4, 4, 3, 50000000, 5000000, 0, 'fixed', 55000000, 'Đơn hàng TechViet gói startup 12 tháng', 'paid', 'bank_transfer', 55000000, '2026-04-01', '2026-02-28', 3, 1),
('DH2603002', 'order', 'confirmed', 1, 1, 1, 150000000, 15000000, 5000000, 'fixed', 160000000, 'Đơn CRM gói Pro cho ABC Software', 'partial', 'bank_transfer', 80000000, '2026-05-01', '2026-03-15', 2, 1),
('DH2603003', 'order', 'processing', 5, 5, 4, 200000000, 20000000, 10000000, 'fixed', 210000000, 'Module kho + triển khai cho GlobalLog', 'unpaid', NULL, 0, '2026-06-15', '2026-03-18', 2, 1),
('BG2603001', 'quote', 'sent', 2, 2, 2, 500000000, 50000000, 25000000, 'fixed', 525000000, 'Báo giá gói Enterprise cho XYZ', 'unpaid', NULL, 0, '2026-05-15', '2026-03-10', 2, 1),
('BG2603002', 'quote', 'draft', 8, 8, 8, 120000000, 12000000, 0, 'fixed', 132000000, 'Báo giá CRM cho Bệnh viện Hòa Bình', 'unpaid', NULL, 0, NULL, '2026-03-19', 2, 1),
('DH2603004', 'order', 'completed', 11, 2, 10, 45000000, 4500000, 0, 'fixed', 49500000, 'Gói đào tạo cho XYZ', 'paid', 'bank_transfer', 49500000, '2026-03-30', '2026-03-08', 2, 1);

-- Order items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `sort_order`) VALUES
(1, 1, 'ToryCRM - Gói Starter', 12, 'Tháng', 500000, 10, 600000, 6600000, 0),
(1, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(1, 5, 'Dịch vụ đào tạo', 2, 'Buổi', 3000000, 10, 600000, 6600000, 2),
(2, 2, 'ToryCRM - Gói Professional', 12, 'Tháng', 1500000, 10, 1800000, 19800000, 0),
(2, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(2, 7, 'Module Email Marketing', 12, 'Tháng', 800000, 10, 960000, 10560000, 2),
(3, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(3, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(3, 6, 'Dịch vụ tư vấn', 10, 'Giờ', 1000000, 10, 1000000, 11000000, 2),
(4, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(4, 11, 'Gói CRM + Triển khai + Đào tạo', 1, 'Gói', 25000000, 10, 2500000, 27500000, 1),
(5, 2, 'ToryCRM - Gói Professional', 12, 'Tháng', 1500000, 10, 1800000, 19800000, 0),
(6, 5, 'Dịch vụ đào tạo', 5, 'Buổi', 3000000, 10, 1500000, 16500000, 0);

-- ============================================================
-- CALENDAR EVENTS (Lịch hẹn)
-- ============================================================
INSERT INTO `calendar_events` (`title`, `description`, `type`, `color`, `start_at`, `end_at`, `all_day`, `location`, `contact_id`, `company_id`, `deal_id`, `user_id`, `created_by`) VALUES
('Demo CRM cho ABC Software', 'Demo toàn bộ tính năng CRM cho team sales ABC', 'meeting', '#405189', '2026-03-22 09:00:00', '2026-03-22 11:00:00', 0, 'Văn phòng ABC, 123 Nguyễn Huệ Q1', 1, 1, 1, 2, 1),
('Gọi follow-up XYZ', 'Gọi chị Bình hỏi phản hồi báo giá Enterprise', 'call', '#0ab39c', '2026-03-21 14:00:00', '2026-03-21 14:30:00', 0, NULL, 2, 2, 2, 2, 1),
('Họp team sales', 'Review KPI tuần và phân chia lead mới', 'meeting', '#405189', '2026-03-24 08:30:00', '2026-03-24 09:30:00', 0, 'Phòng họp A', NULL, NULL, NULL, 2, 1),
('Thăm Logistics Toàn Cầu', 'Thăm kho và khảo sát hiện trạng', 'visit', '#f06548', '2026-03-25 09:00:00', '2026-03-25 12:00:00', 0, 'Kho hàng Q7', 5, 5, 4, 2, 1),
('Deadline proposal XYZ', 'Hạn chót gửi proposal cho XYZ', 'reminder', '#ffbe0b', '2026-03-23 17:00:00', NULL, 0, NULL, 2, 2, 2, 2, 1),
('Đào tạo TechViet - Buổi 1', 'Buổi đào tạo đầu tiên cho TechViet', 'meeting', '#405189', '2026-03-26 09:00:00', '2026-03-26 12:00:00', 0, 'Online - Google Meet', 4, 4, 3, 3, 1),
('Ngày Phụ nữ VN - Nghỉ', 'Ngày nghỉ lễ', 'other', '#299cdb', '2026-10-20 00:00:00', '2026-10-20 23:59:00', 1, NULL, NULL, NULL, NULL, 2, 1),
('Gọi Minh Phát - báo giá', 'Gọi anh Phong xác nhận báo giá', 'call', '#0ab39c', '2026-03-27 10:00:00', '2026-03-27 10:30:00', 0, NULL, 13, 3, 5, 3, 1);

-- ============================================================
-- TICKETS (Hỗ trợ khách hàng)
-- ============================================================
INSERT INTO `tickets` (`ticket_code`, `title`, `content`, `category_id`, `contact_id`, `company_id`, `priority`, `status`, `assigned_to`, `contact_phone`, `contact_email`, `due_date`, `created_by`) VALUES
('TK2603001', 'Không đăng nhập được CRM', 'Khi nhập email/password đúng nhưng hệ thống báo lỗi "Invalid credentials". Đã thử reset password nhưng vẫn không được.', 1, 4, 4, 'high', 'in_progress', 6, '0934444444', 'dung.pt@techviet.io', '2026-03-21 17:00:00', 4),
('TK2603002', 'Yêu cầu xuất báo cáo theo quý', 'Hiện tại báo cáo chỉ có theo tháng và năm. Cần thêm tính năng xuất báo cáo theo quý để trình lãnh đạo.', 3, 14, 4, 'medium', 'open', 6, '0944567890', 'quynh.ht@techviet.io', '2026-04-01 17:00:00', 4),
('TK2603003', 'Tốc độ tải trang chậm', 'Dashboard mất khoảng 5-8 giây để load. Đặc biệt chậm khi có nhiều deal trong pipeline.', 1, 1, 1, 'urgent', 'open', 6, '0901111111', 'an.nv@abc-soft.vn', '2026-03-22 12:00:00', 2),
('TK2603004', 'Cảm ơn team hỗ trợ', 'Cảm ơn team đã hỗ trợ rất nhanh và nhiệt tình trong quá trình triển khai. Rất hài lòng!', 4, 4, 4, 'low', 'closed', 4, '0934444444', 'dung.pt@techviet.io', NULL, 4),
('TK2603005', 'Lỗi import danh sách khách hàng', 'Upload file CSV 500 records nhưng chỉ import được 200. Không có thông báo lỗi cụ thể.', 1, 10, 1, 'high', 'waiting', 6, '0990000000', 'linh.nt@abc-soft.vn', '2026-03-23 17:00:00', 2);

-- Ticket comments
INSERT INTO `ticket_comments` (`ticket_id`, `content`, `is_internal`, `user_id`) VALUES
(1, 'Đã kiểm tra, lỗi do session bị hết hạn. Đang fix cấu hình session timeout.', 0, 6),
(1, 'Cần update config SESSION_LIFETIME trong .env. Hiện đang set 15 phút, quá ngắn.', 1, 6),
(3, 'Đã reproduce được lỗi. Nguyên nhân: query dashboard không có index. Đang tối ưu.', 1, 6),
(4, 'Cảm ơn bạn đã phản hồi! Chúng tôi rất vui khi được hỗ trợ.', 0, 4),
(5, 'Đã check log, lỗi do encoding file CSV không phải UTF-8. Đang chờ KH gửi lại file.', 0, 6);

-- ============================================================
-- CAMPAIGNS (Chiến dịch Marketing)
-- ============================================================
INSERT INTO `campaigns` (`campaign_code`, `name`, `type`, `status`, `description`, `start_date`, `end_date`, `budget`, `actual_cost`, `target_count`, `reached_count`, `converted_count`, `owner_id`, `created_by`) VALUES
('CD260001', 'Email Giới thiệu CRM Q1/2026', 'email', 'running', 'Chiến dịch email giới thiệu tính năng mới CRM Q1 2026 cho khách hàng tiềm năng', '2026-03-01', '2026-03-31', 5000000, 2500000, 100, 65, 8, 4, 1),
('CD260002', 'SMS Khuyến mãi Tết', 'sms', 'completed', 'Gửi SMS khuyến mãi dịp Tết Nguyên Đán - giảm 20% gói Pro', '2026-01-15', '2026-02-15', 3000000, 2800000, 200, 180, 15, 4, 1),
('CD260003', 'Webinar CRM cho SME', 'other', 'draft', 'Tổ chức webinar hướng dẫn sử dụng CRM cho doanh nghiệp vừa và nhỏ', '2026-04-10', '2026-04-10', 2000000, 0, 50, 0, 0, 3, 1),
('CD260004', 'Facebook Ads tháng 3', 'social', 'running', 'Quảng cáo Facebook hướng đến đối tượng CEO/Director các SME', '2026-03-01', '2026-03-31', 10000000, 6500000, 500, 320, 25, 4, 1),
('CD260005', 'Gọi điện chăm sóc KH cũ', 'call', 'paused', 'Gọi điện chăm sóc khách hàng đã hết hạn license để gia hạn', '2026-03-10', '2026-04-10', 1000000, 500000, 30, 12, 3, 2, 1);

-- Campaign contacts
INSERT INTO `campaign_contacts` (`campaign_id`, `contact_id`, `status`, `sent_at`, `opened_at`, `clicked_at`) VALUES
(1, 1, 'opened', '2026-03-02 09:00:00', '2026-03-02 10:15:00', NULL),
(1, 2, 'clicked', '2026-03-02 09:00:00', '2026-03-02 11:30:00', '2026-03-02 11:32:00'),
(1, 3, 'sent', '2026-03-02 09:00:00', NULL, NULL),
(1, 5, 'opened', '2026-03-02 09:00:00', '2026-03-03 08:45:00', NULL),
(1, 7, 'failed', NULL, NULL, NULL),
(1, 8, 'sent', '2026-03-02 09:00:00', NULL, NULL),
(1, 11, 'converted', '2026-03-02 09:00:00', '2026-03-02 14:00:00', '2026-03-02 14:05:00'),
(1, 12, 'opened', '2026-03-02 09:00:00', '2026-03-04 09:20:00', NULL),
(2, 1, 'converted', '2026-01-20 10:00:00', NULL, NULL),
(2, 4, 'converted', '2026-01-20 10:00:00', NULL, NULL),
(2, 5, 'sent', '2026-01-20 10:00:00', NULL, NULL),
(4, 3, 'clicked', '2026-03-05 08:00:00', '2026-03-05 12:00:00', '2026-03-05 12:05:00'),
(4, 7, 'converted', '2026-03-05 08:00:00', '2026-03-06 09:00:00', '2026-03-06 09:10:00'),
(4, 12, 'opened', '2026-03-05 08:00:00', '2026-03-07 11:00:00', NULL),
(5, 9, 'pending', NULL, NULL, NULL);

-- ============================================================
-- PURCHASE ORDERS (Đơn hàng mua)
-- ============================================================
INSERT INTO `purchase_orders` (`order_code`, `supplier_id`, `status`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `notes`, `payment_status`, `paid_amount`, `expected_date`, `owner_id`, `created_by`) VALUES
('PO2603001', 6, 'completed', 400000000, 40000000, 0, 440000000, 'Mua laptop Dell cho nhân viên mới', 'paid', 440000000, '2026-03-15', 6, 1),
('PO2603002', 6, 'approved', 80000000, 8000000, 5000000, 83000000, 'Mua màn hình cho phòng KD', 'unpaid', 0, '2026-03-28', 6, 1),
('PO2603003', 7, 'draft', 15000000, 1500000, 0, 16500000, 'Đặt thiết kế brochure sản phẩm', 'unpaid', 0, '2026-04-05', 4, 1);

INSERT INTO `purchase_order_items` (`purchase_order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `received_quantity`, `sort_order`) VALUES
(1, 9, 'Máy tính xách tay Dell', 16, 'Cái', 25000000, 10, 40000000, 440000000, 16, 0),
(2, 10, 'Màn hình Dell 27 inch', 10, 'Cái', 8000000, 10, 8000000, 88000000, 0, 0),
(3, NULL, 'Thiết kế brochure A4', 1000, 'Tờ', 15000, 10, 1500000, 16500000, 0, 0);

-- ============================================================
-- FUND TRANSACTIONS (Phiếu thu / Phiếu chi)
-- ============================================================
INSERT INTO `fund_transactions` (`transaction_code`, `type`, `fund_account_id`, `amount`, `category`, `description`, `contact_id`, `company_id`, `order_id`, `transaction_date`, `status`, `confirmed_by`, `confirmed_at`, `created_by`) VALUES
('PT2603001', 'receipt', 2, 55000000, 'Thu tiền bán hàng', 'Thu tiền đơn hàng TechViet - DH2603001', 4, 4, 1, '2026-03-01', 'confirmed', 1, '2026-03-01 10:00:00', 1),
('PT2603002', 'receipt', 2, 80000000, 'Thu tiền bán hàng', 'Thu đợt 1 - ABC Software - DH2603002', 1, 1, 2, '2026-03-16', 'confirmed', 1, '2026-03-16 14:00:00', 1),
('PT2603003', 'receipt', 2, 49500000, 'Thu tiền bán hàng', 'Thu tiền đào tạo XYZ - DH2603004', 11, 2, 6, '2026-03-10', 'confirmed', 1, '2026-03-10 11:00:00', 1),
('PC2603001', 'payment', 2, 440000000, 'Chi mua hàng', 'Thanh toán đơn mua laptop - PO2603001', NULL, 6, NULL, '2026-03-15', 'confirmed', 1, '2026-03-15 15:00:00', 1),
('PC2603002', 'payment', 1, 2500000, 'Chi marketing', 'Chi phí email marketing Q1', NULL, NULL, NULL, '2026-03-05', 'confirmed', 1, '2026-03-05 09:00:00', 1),
('PC2603003', 'payment', 1, 6500000, 'Chi marketing', 'Facebook Ads tháng 3', NULL, NULL, NULL, '2026-03-18', 'confirmed', 1, '2026-03-18 16:00:00', 1),
('PT2603004', 'receipt', 1, 5000000, 'Thu khác', 'Thu tiền mặt tư vấn ngoài giờ', 3, 3, NULL, '2026-03-12', 'draft', NULL, NULL, 3),
('PC2603004', 'payment', 1, 3500000, 'Chi văn phòng phẩm', 'Mua VPP và đồ dùng văn phòng tháng 3', NULL, NULL, NULL, '2026-03-19', 'draft', NULL, NULL, 1);

-- Update fund account balances for confirmed transactions
UPDATE `fund_accounts` SET `balance` = -12500000 WHERE id = 1;
UPDATE `fund_accounts` SET `balance` = -255000000 WHERE id = 2;

-- ============================================================
-- NOTIFICATIONS (Thông báo mẫu)
-- ============================================================
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `icon`, `is_read`) VALUES
(1, 'deal', 'Deal mới thắng!', 'TechViet - Gói startup đã đóng thành công, giá trị 50,000,000đ', 'deals/3', 'ri-hand-coin-line', 0),
(1, 'task', 'Công việc quá hạn', 'Review code module ticket đã quá hạn', 'tasks/8', 'ri-task-line', 0),
(1, 'order', 'Đơn hàng mới', 'Đơn hàng DH2603003 cho GlobalLog đã được tạo', 'orders/3', 'ri-file-list-3-line', 1),
(1, 'calendar', 'Lịch hẹn sắp tới', 'Demo CRM cho ABC Software vào 9:00 ngày 22/03', 'calendar/1', 'ri-calendar-event-line', 0),
(1, 'system', 'Ticket mới cần xử lý', 'Ticket TK2603003: Tốc độ tải trang chậm - Priority: Urgent', 'tickets/3', 'ri-customer-service-line', 0),
(2, 'deal', 'Cập nhật pipeline', 'Deal "CRM cho ABC Software" đã chuyển sang giai đoạn Đàm phán', 'deals/1', 'ri-hand-coin-line', 0),
(2, 'task', 'Task mới được giao', 'Demo module kho cho GlobalLog - Hạn 24/03', 'tasks/6', 'ri-task-line', 0),
(3, 'success', 'Gia hạn hợp đồng', 'TechViet đã gia hạn thêm 12 tháng', 'deals/3', 'ri-checkbox-circle-line', 1),
(6, 'danger', 'Ticket khẩn cấp', 'Ticket TK2603003 cần xử lý gấp - tốc độ tải chậm', 'tickets/3', 'ri-alarm-warning-line', 0);

-- ============================================================
-- ACTIVITIES (Hoạt động gần đây)
-- ============================================================
INSERT INTO `activities` (`type`, `title`, `description`, `contact_id`, `deal_id`, `company_id`, `user_id`) VALUES
('deal', 'Deal thắng: TechViet', 'Deal TechViet - Gói startup đã đóng thành công với giá trị 50,000,000đ', 4, 3, 4, 3),
('call', 'Gọi điện ABC Software', 'Gọi anh An xác nhận lịch demo ngày 22/03', 1, 1, 1, 2),
('email', 'Gửi báo giá XYZ', 'Gửi báo giá gói Enterprise cho chị Bình - XYZ', 2, 2, 2, 2),
('meeting', 'Họp team sales', 'Họp review pipeline tuần 3 tháng 3', NULL, NULL, NULL, 2),
('note', 'Ghi chú GlobalLog', 'KH cần tích hợp với hệ thống WMS hiện tại, cần đánh giá API', 5, 4, 5, 2),
('task', 'Hoàn thành: Gửi HĐ TechViet', 'Đã gửi hợp đồng gia hạn 12 tháng cho TechViet', 4, 3, 4, 3),
('system', 'Ticket mới: Lỗi đăng nhập', 'Ticket TK2603001 đã được tạo - khách hàng TechViet không đăng nhập được', 4, NULL, 4, 4),
('deal', 'Deal mất: Sáng Tạo Agency', 'Deal cho Sáng Tạo Agency đã mất - lý do: ngân sách không đủ', 7, 7, 7, 4),
('email', 'Gửi campaign email Q1', 'Chiến dịch email Q1/2026 đã gửi đến 100 khách hàng tiềm năng', NULL, NULL, NULL, 4),
('system', 'Import khách hàng', 'Import 15 khách hàng mới từ file CSV', NULL, NULL, NULL, 1);

SET FOREIGN_KEY_CHECKS = 1;

-- === database/seeds/demo_conversations.sql ===
SET NAMES utf8mb4;

INSERT INTO conversations (tenant_id, contact_id, channel, subject, status, assigned_to, last_message_at, last_message_preview, unread_count, is_starred) VALUES
(1, 3, 'email', 'Hỏi về gói Professional', 'open', 2, NOW() - INTERVAL 30 MINUTE, 'Cho tôi hỏi giá gói Pro cho 20 users?', 2, 0),
(1, 5, 'zalo', NULL, 'open', 2, NOW() - INTERVAL 120 MINUTE, 'Anh ơi đơn hàng của em tới đâu rồi?', 1, 1),
(1, 8, 'facebook', 'Tư vấn giải pháp CRM', 'pending', 3, NOW() - INTERVAL 300 MINUTE, 'Dạ em cảm ơn, em sẽ cân nhắc thêm', 0, 0),
(1, 1, 'email', 'Báo lỗi không export được', 'resolved', 6, NOW() - INTERVAL 1440 MINUTE, 'Đã fix rồi anh, anh thử lại giúp em', 0, 0),
(1, 4, 'livechat', NULL, 'open', 3, NOW() - INTERVAL 10 MINUTE, 'Tôi muốn gia hạn thêm 6 tháng', 3, 1),
(1, 10, 'sms', NULL, 'closed', 2, NOW() - INTERVAL 4320 MINUTE, 'Cảm ơn bạn đã xác nhận', 0, 0),
(1, 13, 'phone', 'Yêu cầu demo', 'open', 3, NOW() - INTERVAL 240 MINUTE, 'OK hẹn 2h chiều thứ 4 nhé', 0, 0);

SET @c1 = (SELECT id FROM conversations WHERE contact_id = 3 AND channel = 'email' LIMIT 1);
SET @c2 = (SELECT id FROM conversations WHERE contact_id = 5 AND channel = 'zalo' LIMIT 1);
SET @c3 = (SELECT id FROM conversations WHERE contact_id = 8 AND channel = 'facebook' LIMIT 1);
SET @c4 = (SELECT id FROM conversations WHERE contact_id = 1 AND channel = 'email' LIMIT 1);
SET @c5 = (SELECT id FROM conversations WHERE contact_id = 4 AND channel = 'livechat' LIMIT 1);
SET @c6 = (SELECT id FROM conversations WHERE contact_id = 10 AND channel = 'sms' LIMIT 1);
SET @c7 = (SELECT id FROM conversations WHERE contact_id = 13 AND channel = 'phone' LIMIT 1);

INSERT INTO messages (conversation_id, direction, sender_type, sender_id, content, content_type, created_at) VALUES
(@c1, 'inbound', 'contact', 3, 'Xin chào, cho tôi hỏi giá gói Professional cho team 20 người được không ạ?', 'text', NOW() - INTERVAL 120 MINUTE),
(@c1, 'outbound', 'user', 2, 'Chào anh Cường! Gói Professional giá 1.499.000đ/tháng cho tối đa 20 users. Anh có muốn em gửi báo giá chi tiết không ạ?', 'text', NOW() - INTERVAL 90 MINUTE),
(@c1, 'inbound', 'contact', 3, 'Cho tôi hỏi giá gói Pro cho 20 users?', 'text', NOW() - INTERVAL 30 MINUTE),

(@c2, 'inbound', 'contact', 5, 'Chào shop, đơn DH2603003 của mình giờ tới đâu rồi?', 'text', NOW() - INTERVAL 180 MINUTE),
(@c2, 'outbound', 'user', 2, 'Chào anh Em! Đơn DH2603003 đang được xử lý, dự kiến giao tuần sau ạ.', 'text', NOW() - INTERVAL 150 MINUTE),
(@c2, 'inbound', 'contact', 5, 'Anh ơi đơn hàng của em tới đâu rồi?', 'text', NOW() - INTERVAL 120 MINUTE),

(@c3, 'inbound', 'contact', 8, 'Mình muốn tìm hiểu giải pháp CRM cho bệnh viện, có phù hợp không?', 'text', NOW() - INTERVAL 480 MINUTE),
(@c3, 'outbound', 'user', 3, 'Chào chị Hạnh! ToryCRM hoàn toàn phù hợp cho ngành y tế. Em có thể demo trực tiếp cho chị xem ạ.', 'text', NOW() - INTERVAL 420 MINUTE),
(@c3, 'inbound', 'contact', 8, 'Để mình sắp xếp thời gian rồi liên hệ lại nhé', 'text', NOW() - INTERVAL 360 MINUTE),
(@c3, 'outbound', 'user', 3, 'Dạ vâng ạ! Chị có thể liên hệ bất cứ lúc nào.', 'text', NOW() - INTERVAL 330 MINUTE),
(@c3, 'inbound', 'contact', 8, 'Dạ em cảm ơn, em sẽ cân nhắc thêm', 'text', NOW() - INTERVAL 300 MINUTE),

(@c4, 'inbound', 'contact', 1, 'Khi bấm Export CSV ở trang khách hàng thì bị lỗi trắng trang, không tải được file.', 'text', NOW() - INTERVAL 2880 MINUTE),
(@c4, 'outbound', 'user', 6, 'Chào anh An, em đã ghi nhận lỗi. Đang kiểm tra và sẽ phản hồi sớm nhất ạ.', 'text', NOW() - INTERVAL 2760 MINUTE),
(@c4, 'outbound', 'user', 6, 'Đã fix rồi anh, anh thử lại giúp em. Lỗi do file CSV quá lớn, em đã tối ưu lại.', 'text', NOW() - INTERVAL 1440 MINUTE),

(@c5, 'inbound', 'contact', 4, 'Xin chào, tôi muốn gia hạn license thêm 6 tháng', 'text', NOW() - INTERVAL 20 MINUTE),
(@c5, 'outbound', 'user', 3, 'Chào chị Dung! Em kiểm tra hợp đồng và gửi báo giá gia hạn cho chị nhé.', 'text', NOW() - INTERVAL 15 MINUTE),
(@c5, 'inbound', 'contact', 4, 'Tôi muốn gia hạn thêm 6 tháng', 'text', NOW() - INTERVAL 10 MINUTE),

(@c6, 'outbound', 'user', 2, 'Chào chị Linh, xác nhận lịch hẹn demo ngày mai lúc 10h ạ.', 'text', NOW() - INTERVAL 4400 MINUTE),
(@c6, 'inbound', 'contact', 10, 'Cảm ơn bạn đã xác nhận', 'text', NOW() - INTERVAL 4320 MINUTE),

(@c7, 'outbound', 'user', 3, 'Gọi anh Phong hẹn demo module kho tại văn phòng Minh Phát.', 'text', NOW() - INTERVAL 300 MINUTE),
(@c7, 'inbound', 'contact', 13, 'Đồng ý, hẹn 2h chiều thứ 4 tại văn phòng.', 'text', NOW() - INTERVAL 270 MINUTE),
(@c7, 'outbound', 'user', 3, 'OK hẹn 2h chiều thứ 4 nhé. Em sẽ chuẩn bị demo module kho và đơn hàng mua.', 'text', NOW() - INTERVAL 240 MINUTE);

-- === database/seeds/demo_finance.sql ===
SET NAMES utf8mb4;

-- ============================================================
-- CÔNG NỢ (Debts)
-- ============================================================
INSERT INTO debts (tenant_id, type, contact_id, company_id, order_id, amount, paid_amount, due_date, status, description, created_by) VALUES
(1, 'receivable', 1, 3, 2, 160000000, 80000000, '2026-05-01', 'partial', 'Công nợ đơn hàng DH2603002 - ABC Software', 1),
(1, 'receivable', 5, 7, 3, 210000000, 0, '2026-06-15', 'open', 'Công nợ đơn hàng DH2603003 - GlobalLog', 1),
(1, 'receivable', 2, 4, 4, 525000000, 0, '2026-05-15', 'open', 'Công nợ báo giá BG2603001 - XYZ', 1),
(1, 'payable', NULL, 8, NULL, 83000000, 0, '2026-03-28', 'overdue', 'Công nợ mua màn hình - PO2603002', 1),
(1, 'payable', NULL, 9, NULL, 16500000, 0, '2026-04-05', 'open', 'Công nợ thiết kế brochure - PO2603003', 1),
(1, 'receivable', 8, 8, NULL, 132000000, 0, '2026-06-30', 'open', 'Công nợ BV Hòa Bình - chờ ký HĐ', 1);

-- Payments cho debt partial
INSERT INTO debt_payments (debt_id, amount, payment_method, note, paid_at, created_by) VALUES
(1, 80000000, 'Chuyển khoản', 'Thu đợt 1 qua Vietcombank', '2026-03-16', 1);

-- ============================================================
-- HỢP ĐỒNG (Contracts)
-- ============================================================
INSERT INTO contracts (tenant_id, contract_number, title, contact_id, company_id, deal_id, type, status, value, recurring_value, recurring_cycle, start_date, end_date, signed_date, auto_renew, payment_terms, notes, owner_id, created_by) VALUES
(1, 'HD-2603-0001', 'Hợp đồng CRM gói Startup - TechViet', 4, 6, 3, 'service', 'active', 50000000, 500000, 'monthly', '2026-03-01', '2027-02-28', '2026-02-28', 1, 'Thanh toán hàng tháng vào ngày 5', 'Gói starter 12 tháng + 2 buổi đào tạo', 3, 1),
(1, 'HD-2603-0002', 'Hợp đồng triển khai CRM - ABC Software', 1, 3, 1, 'service', 'signed', 160000000, 1500000, 'monthly', '2026-04-01', '2027-03-31', '2026-03-20', 0, 'Thanh toán 50% trước, 50% sau triển khai', 'Gói Professional 12 tháng + triển khai', 2, 1),
(1, 'HD-2603-0003', 'Hợp đồng đào tạo - XYZ Việt Nam', 11, 4, 10, 'consulting', 'active', 49500000, 0, 'none', '2026-03-08', '2026-04-08', '2026-03-08', 0, 'Thanh toán sau khi hoàn thành đào tạo', '5 buổi đào tạo sử dụng CRM', 2, 1),
(1, 'HD-2603-0004', 'Hợp đồng tư vấn - Minh Phát', 3, 5, 5, 'consulting', 'negotiating', 80000000, 0, 'none', '2026-05-01', '2026-07-31', NULL, 0, 'Thanh toán theo giai đoạn', 'Tư vấn và triển khai CRM 3 tháng', 3, 1),
(1, 'HD-2602-0001', 'Hợp đồng bảo trì hệ thống cũ', 10, 3, NULL, 'maintenance', 'expired', 24000000, 2000000, 'monthly', '2025-03-01', '2026-02-28', '2025-02-25', 0, 'Thanh toán hàng tháng', 'Đã hết hạn, cần gia hạn', 2, 1);

-- ============================================================
-- NGÂN SÁCH (Budgets)
-- ============================================================
INSERT INTO budgets (tenant_id, name, type, period_start, period_end, total_budget, spent_amount, status, department, notes, approved_by, created_by) VALUES
(1, 'Ngân sách Marketing Q2/2026', 'department', '2026-04-01', '2026-06-30', 30000000, 9000000, 'active', 'Marketing', 'Ngân sách marketing quý 2', 1, 4),
(1, 'Ngân sách triển khai ABC Software', 'project', '2026-04-01', '2026-05-31', 20000000, 5000000, 'active', NULL, 'Chi phí triển khai cho ABC', 1, 2),
(1, 'Ngân sách hoạt động tháng 4', 'general', '2026-04-01', '2026-04-30', 15000000, 3500000, 'active', NULL, 'Chi phí vận hành chung tháng 4', 1, 1);

INSERT INTO budget_items (budget_id, category, planned_amount, actual_amount, sort_order) VALUES
(1, 'Facebook Ads', 15000000, 6500000, 1),
(1, 'Email Marketing', 5000000, 2500000, 2),
(1, 'Sự kiện / Webinar', 5000000, 0, 3),
(1, 'Nội dung / Thiết kế', 3000000, 0, 4),
(1, 'Khác', 2000000, 0, 5),
(2, 'Nhân sự triển khai', 10000000, 3000000, 1),
(2, 'Di chuyển / Ăn ở', 5000000, 2000000, 2),
(2, 'Thiết bị / Phần mềm', 3000000, 0, 3),
(2, 'Dự phòng', 2000000, 0, 4),
(3, 'Văn phòng phẩm', 3000000, 1500000, 1),
(3, 'Điện / Nước / Internet', 5000000, 0, 2),
(3, 'Đi lại', 4000000, 2000000, 3),
(3, 'Tiếp khách', 3000000, 0, 4);

-- ============================================================
-- HOA HỒNG (Commissions)
-- ============================================================
INSERT INTO commissions (tenant_id, user_id, rule_id, entity_type, entity_id, amount, base_value, rate, status, period, notes) VALUES
(1, 3, 1, 'deal', 3, 2500000, 50000000, 5.00, 'paid', '2026-03', 'Hoa hồng deal TechViet'),
(1, 2, 2, 'deal', 10, 3960000, 49500000, 8.00, 'approved', '2026-03', 'Hoa hồng deal đào tạo XYZ'),
(1, 2, 1, 'deal', 1, 7500000, 150000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ABC Software'),
(1, 2, 3, 'order', 1, 1650000, 55000000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn hàng TechViet'),
(1, 2, 3, 'order', 6, 1485000, 49500000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn hàng đào tạo XYZ'),
(1, 3, 1, 'deal', 5, 4000000, 80000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal Minh Phát');

-- === database/seeds/demo_extra.sql ===
SET NAMES utf8mb4;

-- ============================================================
-- THÊM CÔNG NỢ (cần thêm 9)
-- ============================================================
INSERT INTO debts (tenant_id, type, contact_id, company_id, amount, paid_amount, due_date, status, description, created_by) VALUES
(1, 'receivable', 10, 3, 45000000, 45000000, '2026-03-30', 'paid', 'Thu tiền đào tạo CRM - Ngô Thị Linh', 1),
(1, 'receivable', 13, 5, 25000000, 10000000, '2026-04-20', 'partial', 'Thu tiền tư vấn - Phan Quốc Phong', 1),
(1, 'receivable', 14, 6, 35000000, 0, '2026-05-10', 'open', 'Thu tiền module HR - Hồ Thị Quỳnh', 1),
(1, 'payable', NULL, 3, 12000000, 12000000, '2026-03-15', 'paid', 'Trả tiền hosting server năm 2026', 1),
(1, 'payable', NULL, 9, 8500000, 0, '2026-04-15', 'open', 'Chi phí in ấn tài liệu marketing', 1),
(1, 'receivable', 7, 9, 18000000, 0, '2026-03-01', 'overdue', 'Công nợ quá hạn - Đặng Quốc Giang', 1),
(1, 'receivable', 6, 8, 42000000, 20000000, '2026-04-30', 'partial', 'Thu tiền bảo trì hệ thống - Võ Thị Phượng', 1),
(1, 'payable', NULL, 6, 25000000, 25000000, '2026-03-20', 'paid', 'Trả tiền mua thiết bị mạng', 1),
(1, 'receivable', 15, 7, 60000000, 0, '2026-05-30', 'open', 'Công nợ dự án logistics - Mai Văn Sơn', 1);

INSERT INTO debt_payments (debt_id, amount, payment_method, note, paid_at, created_by) VALUES
(7, 45000000, 'Chuyển khoản', 'Thu đủ qua VCB', '2026-03-28', 1),
(8, 10000000, 'Tiền mặt', 'Thu đợt 1', '2026-04-05', 1),
(10, 12000000, 'Chuyển khoản', 'Thanh toán qua ACB', '2026-03-10', 1),
(13, 20000000, 'Chuyển khoản', 'Thu đợt 1 bảo trì', '2026-04-10', 1),
(14, 25000000, 'Chuyển khoản', 'Trả đủ tiền thiết bị', '2026-03-18', 1);

-- ============================================================
-- THÊM HỢP ĐỒNG (cần thêm 10)
-- ============================================================
INSERT INTO contracts (tenant_id, contract_number, title, contact_id, company_id, type, status, value, recurring_value, recurring_cycle, start_date, end_date, signed_date, auto_renew, payment_terms, notes, owner_id, created_by) VALUES
(1, 'HD-2604-0001', 'Hợp đồng CRM cho GlobalLog', 5, 7, 'service', 'draft', 210000000, 5000000, 'monthly', '2026-05-01', '2027-04-30', NULL, 1, 'Thanh toán hàng tháng', 'Gói Enterprise + module kho', 2, 1),
(1, 'HD-2604-0002', 'Hợp đồng bảo trì BV Hòa Bình', 8, 8, 'maintenance', 'sent', 36000000, 3000000, 'monthly', '2026-05-01', '2027-04-30', NULL, 1, 'Thanh toán đầu tháng', 'Bảo trì và hỗ trợ kỹ thuật 24/7', 2, 1),
(1, 'HD-2604-0003', 'Hợp đồng thiết kế Sáng Tạo', 7, 9, 'consulting', 'cancelled', 30000000, 0, 'none', '2026-03-01', '2026-05-31', NULL, 0, 'Thanh toán 100% trước', 'Đã hủy do KH không đủ ngân sách', 4, 1),
(1, 'HD-2604-0004', 'Hợp đồng SMS Marketing', NULL, NULL, 'service', 'active', 12000000, 1000000, 'monthly', '2026-01-01', '2026-12-31', '2025-12-20', 1, 'Thanh toán tự động', 'Gói SMS 10,000 tin/tháng', 4, 1),
(1, 'HD-2604-0005', 'Hợp đồng đào tạo nội bộ', NULL, NULL, 'consulting', 'active', 15000000, 0, 'none', '2026-04-01', '2026-04-30', '2026-04-01', 0, 'Thanh toán sau đào tạo', 'Đào tạo nhân viên mới 5 buổi', 6, 1),
(1, 'HD-2604-0006', 'Hợp đồng API tích hợp ERP', 6, 8, 'service', 'negotiating', 150000000, 0, 'none', '2026-06-01', '2026-12-31', NULL, 0, 'Theo milestone', 'Tích hợp CRM với SAP ERP', 3, 1),
(1, 'HD-2604-0007', 'Gia hạn HĐ TechViet năm 2', 4, 6, 'service', 'signed', 55000000, 500000, 'monthly', '2027-03-01', '2028-02-28', '2026-04-05', 1, 'Thanh toán hàng tháng', 'Gia hạn từ HD-2603-0001', 3, 1),
(1, 'HD-2604-0008', 'Hợp đồng phát triển plugin', 14, 6, 'product', 'draft', 80000000, 0, 'none', '2026-05-01', '2026-08-31', NULL, 0, 'Thanh toán theo giai đoạn', 'Plugin quản lý kho nâng cao', 3, 1),
(1, 'HD-2604-0009', 'Hợp đồng white-label', 12, NULL, 'service', 'sent', 200000000, 10000000, 'monthly', '2026-06-01', '2027-05-31', NULL, 1, 'Thanh toán quý', 'Đối tác phân phối CRM', 2, 1),
(1, 'HD-2604-0010', 'Hợp đồng hỗ trợ kỹ thuật 24/7', 1, 3, 'maintenance', 'active', 60000000, 5000000, 'monthly', '2026-04-01', '2027-03-31', '2026-03-25', 1, 'Thanh toán đầu tháng', 'Gói premium support cho ABC Software', 2, 1);

-- ============================================================
-- THÊM BÁO GIÁ (cần thêm 10)
-- ============================================================
INSERT INTO quotations (tenant_id, quote_number, title, contact_id, company_id, status, subtotal, tax_amount, discount_amount, total, valid_until, notes, terms, portal_token, owner_id, created_by) VALUES
(1, 'BG2604006', 'Báo giá module Email Marketing', 10, 3, 'sent', 9600000, 960000, 0, 10560000, '2026-04-25', 'Module email marketing 12 tháng', 'Thanh toán trước khi kích hoạt', 'emailmkt001', 2, 1),
(1, 'BG2604007', 'Báo giá hệ thống call center', 5, 7, 'draft', 45000000, 4500000, 5000000, 44500000, '2026-05-01', 'Tích hợp tổng đài Stringee', 'Bao gồm 12 tháng license', NULL, 2, 1),
(1, 'BG2604008', 'Báo giá đào tạo nâng cao', 14, 6, 'accepted', 9000000, 900000, 0, 9900000, '2026-04-15', '3 buổi đào tạo nâng cao', 'Bao gồm tài liệu', 'daotao001', 3, 1),
(1, 'BG2604009', 'Báo giá CRM cho chuỗi nhà hàng', 12, NULL, 'sent', 72000000, 7200000, 10000000, 69200000, '2026-05-10', 'Gói cho 5 chi nhánh', 'Giá đặc biệt cho chuỗi', 'nhahang001', 2, 1),
(1, 'BG2604010', 'Báo giá tư vấn chuyển đổi số', 3, 5, 'expired', 100000000, 10000000, 0, 110000000, '2026-03-15', 'Tư vấn toàn diện 6 tháng', 'Đã hết hạn', 'cds001', 3, 1),
(1, 'BG2604011', 'Báo giá gói Starter cho freelancer', 9, NULL, 'rejected', 6000000, 600000, 0, 6600000, '2026-04-01', 'Gói 12 tháng cho cá nhân', 'Không có hỗ trợ kỹ thuật', 'freelance001', 3, 1),
(1, 'BG2604012', 'Báo giá nâng cấp từ Pro lên Enterprise', 2, 4, 'sent', 42000000, 4200000, 5000000, 41200000, '2026-04-30', 'Nâng cấp giữa kỳ', 'Tính chênh lệch 8 tháng còn lại', 'upgrade001', 2, 1),
(1, 'BG2604013', 'Báo giá tích hợp Zalo OA', 1, 3, 'draft', 15000000, 1500000, 0, 16500000, '2026-05-15', 'Tích hợp Zalo vào CRM', 'Bao gồm cấu hình + đào tạo', NULL, 2, 1),
(1, 'BG2604014', 'Báo giá gói Data Migration', 6, 8, 'sent', 20000000, 2000000, 0, 22000000, '2026-04-20', 'Di chuyển dữ liệu từ Excel sang CRM', 'Bao gồm clean data', 'datamig001', 3, 1),
(1, 'BG2604015', 'Báo giá gói All-in-One', 15, 7, 'draft', 180000000, 18000000, 20000000, 178000000, '2026-05-30', 'Gói trọn bộ: CRM + ERP + Training', 'Báo giá sơ bộ', NULL, 2, 1);

INSERT INTO quotation_items (quotation_id, product_id, product_name, quantity, unit, unit_price, tax_rate, discount, total, sort_order) VALUES
(6, 7, 'Module Email Marketing', 12, 'Tháng', 800000, 10, 0, 10560000, 0),
(7, NULL, 'Tích hợp Stringee VoIP', 1, 'Gói', 30000000, 10, 5000000, 28000000, 0),
(7, 5, 'Dịch vụ đào tạo', 2, 'Buổi', 3000000, 10, 0, 6600000, 1),
(8, 5, 'Dịch vụ đào tạo', 3, 'Buổi', 3000000, 10, 0, 9900000, 0),
(9, 2, 'ToryCRM - Gói Professional', 60, 'Tháng', 1500000, 10, 10000000, 89000000, 0),
(10, 6, 'Dịch vụ tư vấn', 50, 'Giờ', 1000000, 10, 0, 55000000, 0),
(10, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 0, 16500000, 1),
(11, 1, 'ToryCRM - Gói Starter', 12, 'Tháng', 500000, 10, 0, 6600000, 0),
(12, 3, 'ToryCRM - Gói Enterprise', 8, 'Tháng', 5000000, 10, 5000000, 39000000, 0),
(13, NULL, 'Tích hợp Zalo OA', 1, 'Gói', 15000000, 10, 0, 16500000, 0),
(14, NULL, 'Dịch vụ Data Migration', 1, 'Dự án', 20000000, 10, 0, 22000000, 0),
(15, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 0, 66000000, 0),
(15, 11, 'Gói CRM + Triển khai + Đào tạo', 1, 'Gói', 25000000, 10, 0, 27500000, 1),
(15, 6, 'Dịch vụ tư vấn', 40, 'Giờ', 1000000, 10, 0, 44000000, 2);

-- ============================================================
-- THÊM NGÂN SÁCH (cần thêm 12)
-- ============================================================
INSERT INTO budgets (tenant_id, name, type, period_start, period_end, total_budget, spent_amount, status, department, notes, approved_by, created_by) VALUES
(1, 'Ngân sách Kinh doanh Q2/2026', 'department', '2026-04-01', '2026-06-30', 50000000, 15000000, 'active', 'Kinh doanh', 'Chi phí bán hàng quý 2', 1, 2),
(1, 'Ngân sách Kỹ thuật Q2/2026', 'department', '2026-04-01', '2026-06-30', 40000000, 8000000, 'active', 'Kỹ thuật', 'Server, tools, license', 1, 6),
(1, 'Ngân sách triển khai GlobalLog', 'project', '2026-05-01', '2026-07-31', 25000000, 0, 'draft', NULL, 'Dự án triển khai cho GlobalLog', NULL, 2),
(1, 'Ngân sách sự kiện ra mắt v2.0', 'campaign', '2026-06-01', '2026-06-30', 20000000, 0, 'draft', 'Marketing', 'Sự kiện launch sản phẩm mới', NULL, 4),
(1, 'Ngân sách tuyển dụng Q2', 'department', '2026-04-01', '2026-06-30', 15000000, 5000000, 'active', 'Nhân sự', 'Chi phí tuyển 3 vị trí mới', 1, 1),
(1, 'Ngân sách văn phòng tháng 5', 'general', '2026-05-01', '2026-05-31', 15000000, 0, 'draft', NULL, 'Chi phí vận hành tháng 5', NULL, 1),
(1, 'Ngân sách hoạt động tháng 3', 'general', '2026-03-01', '2026-03-31', 15000000, 14500000, 'closed', NULL, 'Đã đóng - chi gần hết', 1, 1),
(1, 'Ngân sách Marketing Q1/2026', 'department', '2026-01-01', '2026-03-31', 25000000, 27000000, 'exceeded', 'Marketing', 'Vượt ngân sách 2tr do Facebook Ads', 1, 4),
(1, 'Ngân sách dự án BV Hòa Bình', 'project', '2026-05-01', '2026-08-31', 30000000, 0, 'approved', NULL, 'Triển khai CRM cho bệnh viện', 1, 2),
(1, 'Ngân sách Kỹ thuật Q1/2026', 'department', '2026-01-01', '2026-03-31', 35000000, 32000000, 'closed', 'Kỹ thuật', 'Đã đóng - trong ngân sách', 1, 6),
(1, 'Ngân sách đào tạo nhân viên', 'general', '2026-04-01', '2026-06-30', 10000000, 3000000, 'active', NULL, 'Đào tạo nội bộ + gửi đi', 1, 1),
(1, 'Ngân sách phát triển plugin', 'project', '2026-04-01', '2026-09-30', 60000000, 10000000, 'active', 'Kỹ thuật', 'R&D marketplace plugins', 1, 6);

INSERT INTO budget_items (budget_id, category, planned_amount, actual_amount, sort_order) VALUES
(4, 'Di chuyển / Tiếp khách', 20000000, 8000000, 1),
(4, 'Demo / POC', 15000000, 5000000, 2),
(4, 'Hoa hồng', 10000000, 2000000, 3),
(4, 'Khác', 5000000, 0, 4),
(5, 'Server / Cloud', 20000000, 5000000, 1),
(5, 'License phần mềm', 10000000, 3000000, 2),
(5, 'Thiết bị', 5000000, 0, 3),
(5, 'Dự phòng', 5000000, 0, 4),
(8, 'Tuyển trên TopCV', 5000000, 3000000, 1),
(8, 'Headhunter', 7000000, 2000000, 2),
(8, 'Phỏng vấn / Test', 3000000, 0, 3),
(15, 'Plugin kho nâng cao', 25000000, 5000000, 1),
(15, 'Plugin báo cáo BI', 20000000, 5000000, 2),
(15, 'Plugin chatbot', 15000000, 0, 3);

-- ============================================================
-- THÊM HOA HỒNG (cần thêm 9)
-- ============================================================
INSERT INTO commissions (tenant_id, user_id, rule_id, entity_type, entity_id, amount, base_value, rate, status, period, notes) VALUES
(1, 3, 3, 'order', 1, 1650000, 55000000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn TechViet - Lê Minh Tuấn'),
(1, 2, 1, 'deal', 9, 3750000, 75000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ABC Module HR'),
(1, 2, 2, 'deal', 2, 40000000, 500000000, 8.00, 'pending', '2026-04', 'Hoa hồng deal Enterprise XYZ'),
(1, 3, 1, 'deal', 6, 17500000, 350000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ERP Thành Đạt'),
(1, 2, 1, 'deal', 4, 10000000, 200000000, 5.00, 'approved', '2026-04', 'Hoa hồng deal GlobalLog'),
(1, 4, 1, 'deal', 8, 6000000, 120000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal BV Hòa Bình'),
(1, 2, 3, 'order', 2, 4800000, 160000000, 3.00, 'approved', '2026-03', 'Hoa hồng đơn ABC Software'),
(1, 2, 3, 'order', 3, 6300000, 210000000, 3.00, 'pending', '2026-04', 'Hoa hồng đơn GlobalLog'),
(1, 3, 1, 'deal', 3, 2500000, 50000000, 5.00, 'paid', '2026-02', 'Hoa hồng deal TechViet tháng 2');

-- ============================================================
-- THÊM CONVERSATIONS (cần thêm 8)
-- ============================================================
INSERT INTO conversations (tenant_id, contact_id, channel, subject, status, assigned_to, last_message_at, last_message_preview, unread_count, is_starred) VALUES
(1, 6, 'email', 'Hỏi về module quỹ thu chi', 'open', 3, NOW() - INTERVAL 45 MINUTE, 'Module quỹ có hỗ trợ xuất báo cáo không?', 1, 0),
(1, 7, 'zalo', NULL, 'open', 4, NOW() - INTERVAL 90 MINUTE, 'Em muốn đăng ký gói dùng thử', 2, 0),
(1, 9, 'facebook', 'Hỏi giá cho freelancer', 'closed', 3, NOW() - INTERVAL 5000 MINUTE, 'Dạ cảm ơn, để em suy nghĩ thêm', 0, 0),
(1, 11, 'email', 'Yêu cầu hỗ trợ API', 'pending', 6, NOW() - INTERVAL 200 MINUTE, 'API rate limit là bao nhiêu request/phút?', 1, 1),
(1, 14, 'livechat', NULL, 'open', 3, NOW() - INTERVAL 5 MINUTE, 'Cho mình hỏi về tích hợp Zalo OA', 3, 1),
(1, 12, 'phone', 'Tư vấn gói cho chuỗi nhà hàng', 'open', 2, NOW() - INTERVAL 180 MINUTE, 'Hẹn thứ 5 demo tại nhà hàng Q7', 0, 0),
(1, 15, 'sms', NULL, 'resolved', 2, NOW() - INTERVAL 2000 MINUTE, 'Đã nhận được tài liệu, cảm ơn', 0, 0),
(1, 3, 'email', 'Follow up sau demo', 'open', 3, NOW() - INTERVAL 360 MINUTE, 'Anh Cường ơi, phản hồi demo hôm qua thế nào ạ?', 1, 0);

INSERT INTO messages (conversation_id, direction, sender_type, sender_id, content, content_type, created_at) VALUES
(16, 'inbound', 'contact', 6, 'Cho tôi hỏi module quỹ thu chi có xuất báo cáo Excel không?', 'text', NOW() - INTERVAL 60 MINUTE),
(16, 'outbound', 'user', 3, 'Chào chị Phượng, module Quỹ hỗ trợ xuất PDF và sắp có Excel ạ.', 'text', NOW() - INTERVAL 50 MINUTE),
(16, 'inbound', 'contact', 6, 'Module quỹ có hỗ trợ xuất báo cáo không?', 'text', NOW() - INTERVAL 45 MINUTE),
(17, 'inbound', 'contact', 7, 'Chào, mình là Giang, Creative Director. Muốn dùng thử CRM.', 'text', NOW() - INTERVAL 120 MINUTE),
(17, 'outbound', 'user', 4, 'Chào anh Giang! Anh có thể đăng ký dùng thử 14 ngày miễn phí tại website ạ.', 'text', NOW() - INTERVAL 100 MINUTE),
(17, 'inbound', 'contact', 7, 'Em muốn đăng ký gói dùng thử', 'text', NOW() - INTERVAL 90 MINUTE),
(20, 'inbound', 'contact', 11, 'API của ToryCRM có tài liệu ở đâu?', 'text', NOW() - INTERVAL 300 MINUTE),
(20, 'outbound', 'user', 6, 'Chào anh Nhật, tài liệu API tại /help/category/admin. Rate limit là 100 request/phút.', 'text', NOW() - INTERVAL 250 MINUTE),
(20, 'inbound', 'contact', 11, 'API rate limit là bao nhiêu request/phút?', 'text', NOW() - INTERVAL 200 MINUTE),
(21, 'inbound', 'contact', 14, 'Tôi đang dùng ToryCRM, muốn tích hợp Zalo OA', 'text', NOW() - INTERVAL 15 MINUTE),
(21, 'outbound', 'user', 3, 'Chào chị Quỳnh! Tính năng Zalo OA đã có, em hướng dẫn chị cấu hình nhé.', 'text', NOW() - INTERVAL 10 MINUTE),
(21, 'inbound', 'contact', 14, 'Cho mình hỏi về tích hợp Zalo OA', 'text', NOW() - INTERVAL 5 MINUTE),
(22, 'outbound', 'user', 2, 'Gọi chị Oanh tư vấn gói CRM cho chuỗi 5 nhà hàng.', 'text', NOW() - INTERVAL 240 MINUTE),
(22, 'inbound', 'contact', 12, 'Hẹn thứ 5 demo tại nhà hàng Q7', 'text', NOW() - INTERVAL 180 MINUTE),
(23, 'outbound', 'user', 3, 'Anh Cường ơi, phản hồi demo hôm qua thế nào ạ?', 'text', NOW() - INTERVAL 360 MINUTE);

-- === database/seeds/demo_checkins_bookings.sql ===
SET NAMES utf8mb4;

-- ============================================================
-- CHECK-INS (15 records)
-- ============================================================
INSERT INTO checkins (tenant_id, user_id, contact_id, company_id, latitude, longitude, address, note, check_type, created_at) VALUES
(1, 2, 1, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Demo CRM cho team sales ABC Software', 'visit', '2026-04-01 09:30:00'),
(1, 2, 5, 7, 10.7580, 106.7215, '321 Võ Văn Tần, Quận 3, TP.HCM', 'Khảo sát kho hàng GlobalLog', 'visit', '2026-04-01 14:00:00'),
(1, 3, 4, 6, 10.7831, 106.6965, '12 Hoàng Diệu, Quận 4, TP.HCM', 'Đào tạo sử dụng CRM cho TechViet', 'meeting', '2026-04-02 09:00:00'),
(1, 2, 2, 4, 10.7865, 106.6800, '456 Lê Lợi, Quận 3, TP.HCM', 'Họp báo giá gói Enterprise với XYZ', 'meeting', '2026-04-02 14:30:00'),
(1, 3, 3, 5, 21.0285, 105.8542, '789 Trần Hưng Đạo, Hà Nội', 'Tư vấn triển khai CRM cho Minh Phát', 'visit', '2026-04-03 10:00:00'),
(1, 4, 7, 9, 10.7756, 106.7019, '56 Nguyễn Thị Minh Khai, TP.HCM', 'Gặp Creative Director bàn gói SME', 'visit', '2026-04-03 15:00:00'),
(1, 2, 8, 8, 10.7900, 106.6500, '100 Điện Biên Phủ, TP.HCM', 'Demo module quản lý bệnh nhân', 'meeting', '2026-04-04 09:00:00'),
(1, 6, 1, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Hỗ trợ kỹ thuật triển khai ABC Software', 'visit', '2026-04-04 14:00:00'),
(1, 2, 10, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Đào tạo buổi 2 cho ABC Software', 'meeting', '2026-04-05 09:30:00'),
(1, 3, 14, 6, 10.7831, 106.6965, '12 Hoàng Diệu, Quận 4, TP.HCM', 'Bàn giao module HR cho TechViet', 'delivery', '2026-04-05 14:00:00'),
(1, 2, 6, 8, 11.0544, 106.6668, 'KCN Bình Dương', 'Khảo sát nhà máy Thành Đạt', 'visit', '2026-04-07 08:30:00'),
(1, 4, 12, NULL, 10.7800, 106.6950, '78 Hai Bà Trưng, Quận 1, TP.HCM', 'Gặp chị Oanh tư vấn gói chuỗi nhà hàng', 'visit', '2026-04-07 14:00:00'),
(1, 2, 5, 7, 10.7400, 106.7300, 'Kho hàng Q7, TP.HCM', 'Kiểm tra hệ thống sau triển khai', 'visit', '2026-04-08 09:00:00'),
(1, 3, 13, 5, 21.0285, 105.8542, '789 Trần Hưng Đạo, Hà Nội', 'Demo module kho cho anh Phong', 'meeting', '2026-04-08 14:00:00'),
(1, 2, 11, 4, 10.7865, 106.6800, '456 Lê Lợi, Quận 3, TP.HCM', 'Đào tạo sử dụng CRM cho XYZ', 'meeting', '2026-04-09 09:00:00');

-- ============================================================
-- BOOKING LINKS (3 links)
-- ============================================================
INSERT INTO booking_links (tenant_id, user_id, slug, title, description, duration_minutes, available_days, available_hours, buffer_minutes, max_advance_days, is_active, booking_count) VALUES
(1, 2, 'nguyen-van-hung', 'Hẹn demo CRM với Nguyễn Văn Hùng', 'Đặt lịch demo sản phẩm ToryCRM 30 phút với anh Hùng - Trưởng phòng Kinh doanh', 30, '["1","2","3","4","5"]', '{"start":"08:00","end":"17:00"}', 15, 14, 1, 5),
(1, 3, 'le-minh-tuan', 'Tư vấn CRM với Lê Minh Tuấn', 'Đặt lịch tư vấn miễn phí 45 phút về giải pháp CRM phù hợp cho doanh nghiệp', 45, '["1","2","3","4","5"]', '{"start":"09:00","end":"16:00"}', 30, 21, 1, 3),
(1, 6, 'ho-duc-anh-support', 'Hỗ trợ kỹ thuật - Hoàng Đức Anh', 'Đặt lịch hỗ trợ kỹ thuật 1:1 với team kỹ thuật', 60, '["1","2","3","4","5","6"]', '{"start":"08:30","end":"17:30"}', 15, 7, 1, 2);

-- ============================================================
-- BOOKINGS (15 records)
-- ============================================================
INSERT INTO bookings (link_id, contact_name, contact_email, contact_phone, start_at, end_at, note, status) VALUES
(1, 'Nguyễn Thanh Tùng', 'tung.nt@startup.vn', '0901234000', '2026-04-10 09:00:00', '2026-04-10 09:30:00', 'Muốn xem demo gói Professional cho 15 người', 'confirmed'),
(1, 'Trần Văn Bảo', 'bao.tv@logistics.vn', '0912345000', '2026-04-10 10:00:00', '2026-04-10 10:30:00', 'Quan tâm module quản lý kho', 'confirmed'),
(1, 'Lê Thị Hương', 'huong.lt@fashion.vn', '0923456000', '2026-04-11 14:00:00', '2026-04-11 14:30:00', 'Chuỗi 3 cửa hàng, cần CRM quản lý KH', 'confirmed'),
(1, 'Phạm Đức Minh', 'minh.pd@realestate.vn', '0934567000', '2026-04-07 09:00:00', '2026-04-07 09:30:00', 'BĐS, 20 sales cần quản lý pipeline', 'completed'),
(1, 'Võ Thị Lan', 'lan.vt@edu.vn', '0945678000', '2026-04-05 15:00:00', '2026-04-05 15:30:00', 'Trung tâm đào tạo, quản lý học viên', 'completed'),
(2, 'Hoàng Minh Đức', 'duc.hm@fintech.vn', '0956789000', '2026-04-11 09:00:00', '2026-04-11 09:45:00', 'Fintech startup, cần tích hợp API', 'confirmed'),
(2, 'Ngô Quốc Việt', 'viet.nq@manufacturing.vn', '0967890000', '2026-04-08 14:00:00', '2026-04-08 14:45:00', 'Nhà máy 200 NV, đang dùng Excel', 'completed'),
(2, 'Đặng Thị Mai', 'mai.dt@healthcare.vn', '0978901000', '2026-04-12 10:00:00', '2026-04-12 10:45:00', 'Phòng khám tư nhân, quản lý bệnh nhân', 'confirmed'),
(3, 'Bùi Văn Khoa', 'khoa.bv@techcorp.vn', '0989012000', '2026-04-10 14:00:00', '2026-04-10 15:00:00', 'Lỗi import CSV trên 1000 dòng', 'confirmed'),
(3, 'Lý Thị Ngọc', 'ngoc.lt@design.vn', '0990123000', '2026-04-09 09:00:00', '2026-04-09 10:00:00', 'Không gửi được email campaign', 'completed'),
(1, 'Đinh Công Thành', 'thanh.dc@media.vn', '0901111000', '2026-04-14 09:00:00', '2026-04-14 09:30:00', 'Agency 10 người, quản lý dự án + KH', 'confirmed'),
(1, 'Phan Thị Yến', 'yen.pt@travel.vn', '0912222000', '2026-04-14 14:00:00', '2026-04-14 14:30:00', 'Công ty du lịch, quản lý tour + KH', 'confirmed'),
(2, 'Trương Văn Hải', 'hai.tv@construction.vn', '0923333000', '2026-04-15 09:00:00', '2026-04-15 09:45:00', 'Xây dựng, quản lý hợp đồng + tiến độ', 'confirmed'),
(3, 'Cao Thị Hạnh', 'hanh.ct@retail.vn', '0934444000', '2026-04-11 14:00:00', '2026-04-11 15:00:00', 'Lỗi đồng bộ Zalo OA', 'confirmed'),
(2, 'Mai Xuân Long', 'long.mx@fnb.vn', '0945555000', '2026-04-04 09:00:00', '2026-04-04 09:45:00', 'Chuỗi F&B 8 chi nhánh', 'no_show');

SET FOREIGN_KEY_CHECKS = 1;

-- === Manual tables ===
CREATE TABLE IF NOT EXISTS `contact_statuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) DEFAULT 'secondary',
    `icon` VARCHAR(50) DEFAULT 'ri-circle-line',
    `sort_order` INT DEFAULT 0,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `contact_statuses` (`tenant_id`, `slug`, `name`, `color`, `icon`, `sort_order`, `is_default`) VALUES
(1, 'new', 'Mới', 'info', 'ri-user-add-line', 1, 1),
(1, 'contacted', 'Đã liên hệ', 'primary', 'ri-phone-line', 2, 0),
(1, 'qualified', 'Tiềm năng', 'warning', 'ri-star-line', 3, 0),
(1, 'converted', 'Chuyển đổi', 'success', 'ri-checkbox-circle-line', 4, 0),
(1, 'lost', 'Mất', 'danger', 'ri-close-circle-line', 5, 0);

CREATE TABLE IF NOT EXISTS `contact_followers` (
    `contact_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`contact_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `manager_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `departments` (`tenant_id`, `name`, `color`, `sort_order`) VALUES
(1, 'Ban Giám đốc', '#f06548', 1),
(1, 'Kinh doanh', '#405189', 2),
(1, 'Marketing', '#0ab39c', 3),
(1, 'Kỹ thuật', '#299cdb', 4),
(1, 'Hỗ trợ khách hàng', '#f7b84b', 5);

-- Reset admin password to admin123
UPDATE `users` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', `login_attempts` = 0, `locked_until` = NULL WHERE `id` = 1;

SET FOREIGN_KEY_CHECKS = 1;
