-- ToryCRM Database Schema
-- Generated: 2026-04-21 20:20:39
-- Aggregated from database/migrations/*.sql

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


-- === database/migrations/020_task_advanced.sql ===
-- Task Comments
CREATE TABLE IF NOT EXISTS `task_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_task_comments_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Attachments
CREATE TABLE IF NOT EXISTS `task_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_task_att_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Time Tracking
CREATE TABLE IF NOT EXISTS `task_time_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `started_at` DATETIME NOT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `duration` INT UNSIGNED DEFAULT 0 COMMENT 'seconds',
    `note` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_time_task` (`task_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Dependencies
CREATE TABLE IF NOT EXISTS `task_dependencies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `depends_on_id` INT UNSIGNED NOT NULL,
    `type` ENUM('finish_to_start','start_to_start','finish_to_finish') DEFAULT 'finish_to_start',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_task_dep` (`task_id`, `depends_on_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`depends_on_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Templates
CREATE TABLE IF NOT EXISTS `task_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `checklist` JSON DEFAULT NULL COMMENT '[{"title":"...", "items":["sub1","sub2"]}]',
    `default_priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `default_status` ENUM('todo','in_progress','review','done') DEFAULT 'todo',
    `due_days` INT DEFAULT NULL COMMENT 'days from creation',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Recurring Rules
CREATE TABLE IF NOT EXISTS `task_recurring` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `frequency` ENUM('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'weekly',
    `interval_value` INT DEFAULT 1,
    `day_of_week` TINYINT DEFAULT NULL COMMENT '0=Sun, 1=Mon...',
    `day_of_month` TINYINT DEFAULT NULL,
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL,
    `deal_id` INT UNSIGNED DEFAULT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `next_run` DATETIME DEFAULT NULL,
    `last_run` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_recurring_next` (`is_active`, `next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add recurring_id to tasks
ALTER TABLE `tasks` ADD COLUMN `recurring_id` INT UNSIGNED DEFAULT NULL AFTER `parent_id`;
-- Add estimated_hours to tasks
ALTER TABLE `tasks` ADD COLUMN `estimated_hours` DECIMAL(6,2) DEFAULT NULL AFTER `progress`;
-- Add start_date to tasks
ALTER TABLE `tasks` ADD COLUMN `start_date` DATETIME DEFAULT NULL AFTER `due_date`;

-- Task SLA Rules
CREATE TABLE IF NOT EXISTS `task_sla_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `priority` ENUM('low','medium','high','urgent') NOT NULL,
    `max_hours` INT NOT NULL COMMENT 'hours to resolve',
    `escalate_to` INT UNSIGNED DEFAULT NULL,
    `notify_before_hours` INT DEFAULT 2,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`escalate_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/021_task_followers.sql ===
CREATE TABLE IF NOT EXISTS `task_followers` (
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`task_id`, `user_id`),
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/022_department_positions.sql ===
ALTER TABLE `departments` ADD COLUMN `vice_manager_id` INT UNSIGNED DEFAULT NULL AFTER `manager_id`;

CREATE TABLE IF NOT EXISTS `department_positions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `position` VARCHAR(100) NOT NULL DEFAULT 'Nhân viên',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_dept_user` (`department_id`, `user_id`),
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/023_warehouse.sql ===
-- Warehouses (Kho)
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `address` VARCHAR(500) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `manager_id` INT UNSIGNED DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `description` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_wh_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock (Tồn kho theo sản phẩm + kho)
CREATE TABLE IF NOT EXISTS `stock` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(12,2) DEFAULT 0,
    `min_quantity` DECIMAL(12,2) DEFAULT 0 COMMENT 'Cảnh báo tồn kho thấp',
    `max_quantity` DECIMAL(12,2) DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_wh_product` (`warehouse_id`, `product_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements (Phiếu xuất nhập kho)
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `code` VARCHAR(50) DEFAULT NULL,
    `type` ENUM('import','export','transfer','adjustment') NOT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL COMMENT 'Kho nguồn (xuất) hoặc kho đích (nhập)',
    `to_warehouse_id` INT UNSIGNED DEFAULT NULL COMMENT 'Kho đích (chuyển kho)',
    `reference_type` VARCHAR(50) DEFAULT NULL COMMENT 'order, purchase_order, manual',
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft','confirmed','cancelled') DEFAULT 'draft',
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `confirmed_by` INT UNSIGNED DEFAULT NULL,
    `confirmed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sm_tenant` (`tenant_id`),
    INDEX `idx_sm_type` (`type`),
    INDEX `idx_sm_wh` (`warehouse_id`),
    INDEX `idx_sm_ref` (`reference_type`, `reference_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movement Items (Chi tiết phiếu)
CREATE TABLE IF NOT EXISTS `stock_movement_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `movement_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` DECIMAL(12,2) NOT NULL,
    `unit_price` DECIMAL(15,2) DEFAULT 0,
    `note` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`movement_id`) REFERENCES `stock_movements`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Checks (Kiểm kho)
CREATE TABLE IF NOT EXISTS `stock_checks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('draft','completed','cancelled') DEFAULT 'draft',
    `note` TEXT DEFAULT NULL,
    `checked_by` INT UNSIGNED DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Check Items
CREATE TABLE IF NOT EXISTS `stock_check_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `check_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `system_qty` DECIMAL(12,2) DEFAULT 0,
    `actual_qty` DECIMAL(12,2) DEFAULT 0,
    `difference` DECIMAL(12,2) DEFAULT 0,
    `note` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`check_id`) REFERENCES `stock_checks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default warehouse
INSERT INTO `warehouses` (`tenant_id`, `name`, `code`, `is_default`, `is_active`) VALUES (1, 'Kho chính', 'WH01', 1, 1);


-- === database/migrations/024_logistics_plugin.sql ===
-- Plugin: Kho Logistics
INSERT INTO `plugins` (`name`, `slug`, `description`, `category`, `icon`, `version`, `author`, `is_active`, `config`) VALUES
('Kho Logistics', 'kho-logistics', 'Quản lý kho vận chuyển: quét mã barcode nhập kho, tracking kiện hàng, bao hàng, lịch sử xuất nhập, cân nặng, kích thước', 'Kho vận', 'ri-truck-line', '1.0.0', 'ToryCRM', 1, '{"fields":[{"key":"auto_notify","type":"checkbox","label":"Tự động thông báo KH khi nhận hàng","default":true},{"key":"require_weight","type":"checkbox","label":"Bắt buộc nhập cân nặng khi nhận","default":false},{"key":"default_warehouse","type":"text","label":"Kho mặc định","default":"VN"}]}');

-- Logistics Packages (Kiện hàng)
CREATE TABLE IF NOT EXISTS `logistics_packages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `package_code` VARCHAR(50) NOT NULL,
    `tracking_code` VARCHAR(100) DEFAULT NULL COMMENT 'Mã vận đơn bên ngoài',
    `tracking_intl` VARCHAR(100) DEFAULT NULL COMMENT 'Tracking quốc tế',
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `weight_actual` DECIMAL(8,2) DEFAULT NULL,
    `weight_volume` DECIMAL(8,2) DEFAULT NULL,
    `length_cm` DECIMAL(8,2) DEFAULT NULL,
    `width_cm` DECIMAL(8,2) DEFAULT NULL,
    `height_cm` DECIMAL(8,2) DEFAULT NULL,
    `quantity` INT DEFAULT 1,
    `status` ENUM('pending','warehouse_cn','packed','shipping','warehouse_vn','delivering','delivered','returned','damaged') DEFAULT 'pending',
    `warehouse_id` INT UNSIGNED DEFAULT NULL,
    `bag_id` INT UNSIGNED DEFAULT NULL,
    `receive_photo` VARCHAR(500) DEFAULT NULL,
    `received_by` INT UNSIGNED DEFAULT NULL,
    `received_at` DATETIME DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_pkg_code` (`tenant_id`, `package_code`),
    INDEX `idx_pkg_tracking` (`tracking_code`),
    INDEX `idx_pkg_status` (`status`),
    INDEX `idx_pkg_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics Bags (Bao hàng)
CREATE TABLE IF NOT EXISTS `logistics_bags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `bag_code` VARCHAR(50) NOT NULL,
    `status` ENUM('open','sealed','shipping','arrived','completed') DEFAULT 'open',
    `total_packages` INT DEFAULT 0,
    `total_weight` DECIMAL(10,2) DEFAULT 0,
    `warehouse_id` INT UNSIGNED DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `sealed_at` DATETIME DEFAULT NULL,
    `sealed_by` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_bag_code` (`tenant_id`, `bag_code`),
    INDEX `idx_bag_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics Scan Log (Lịch sử quét)
CREATE TABLE IF NOT EXISTS `logistics_scan_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `scan_code` VARCHAR(100) NOT NULL,
    `scan_type` ENUM('package','bag','tracking','unknown') DEFAULT 'unknown',
    `result` ENUM('success','error','duplicate') DEFAULT 'success',
    `package_id` INT UNSIGNED DEFAULT NULL,
    `bag_id` INT UNSIGNED DEFAULT NULL,
    `message` VARCHAR(500) DEFAULT NULL,
    `scanned_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_scan_tenant` (`tenant_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Package Status History
CREATE TABLE IF NOT EXISTS `logistics_status_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT UNSIGNED NOT NULL,
    `old_status` VARCHAR(30) DEFAULT NULL,
    `new_status` VARCHAR(30) NOT NULL,
    `note` VARCHAR(500) DEFAULT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/025_logistics_orders.sql ===
ALTER TABLE `logistics_packages` ADD COLUMN `type` ENUM('retail','wholesale') DEFAULT 'retail' AFTER `quantity`;
ALTER TABLE `logistics_packages` ADD COLUMN `order_id` INT UNSIGNED DEFAULT NULL AFTER `customer_id`;

CREATE TABLE IF NOT EXISTS `logistics_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `order_code` VARCHAR(50) NOT NULL,
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `type` ENUM('retail','wholesale') DEFAULT 'retail',
    `product_name` VARCHAR(500) DEFAULT NULL,
    `total_packages` INT DEFAULT 0,
    `shipped_packages` INT DEFAULT 0,
    `received_packages` INT DEFAULT 0,
    `status` ENUM('pending','processing','partial','completed','cancelled') DEFAULT 'pending',
    `total_amount` DECIMAL(15,2) DEFAULT 0,
    `cod_amount` DECIMAL(15,2) DEFAULT 0,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_order_code` (`tenant_id`, `order_code`),
    INDEX `idx_lo_status` (`status`),
    INDEX `idx_lo_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logistics_package_orders` (
    `package_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`package_id`, `order_id`),
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `logistics_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/026_logistics_cbm.sql ===
ALTER TABLE `logistics_orders` ADD COLUMN `total_weight` DECIMAL(10,2) DEFAULT 0 AFTER `total_packages`;
ALTER TABLE `logistics_orders` ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT 0 AFTER `total_weight`;
ALTER TABLE `logistics_orders` ADD COLUMN `images` JSON DEFAULT NULL AFTER `note`;
ALTER TABLE `logistics_packages` ADD COLUMN `cbm` DECIMAL(10,4) DEFAULT NULL AFTER `height_cm`;
UPDATE `logistics_packages` SET `cbm` = ROUND(`length_cm` * `width_cm` * `height_cm` / 1000000, 4) WHERE `length_cm` > 0 AND `width_cm` > 0 AND `height_cm` > 0;


-- === database/migrations/027_logistics_shipments_delivery.sql ===
-- Shipments (Lô hàng)
CREATE TABLE IF NOT EXISTS `logistics_shipments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `shipment_code` VARCHAR(50) NOT NULL,
    `origin` VARCHAR(100) DEFAULT 'CN' COMMENT 'Kho xuất (TQ/VN)',
    `destination` VARCHAR(100) DEFAULT 'VN' COMMENT 'Kho đích',
    `vehicle_info` VARCHAR(255) DEFAULT NULL COMMENT 'Thông tin xe/chuyến',
    `total_packages` INT DEFAULT 0,
    `total_bags` INT DEFAULT 0,
    `total_weight` DECIMAL(10,2) DEFAULT 0,
    `total_cbm` DECIMAL(10,4) DEFAULT 0,
    `status` ENUM('preparing','in_transit','arrived','completed','cancelled') DEFAULT 'preparing',
    `departed_at` DATETIME DEFAULT NULL,
    `arrived_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_shipment_code` (`tenant_id`, `shipment_code`),
    INDEX `idx_ship_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment-Package link
CREATE TABLE IF NOT EXISTS `logistics_shipment_packages` (
    `shipment_id` INT UNSIGNED NOT NULL,
    `package_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`shipment_id`, `package_id`),
    FOREIGN KEY (`shipment_id`) REFERENCES `logistics_shipments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `logistics_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment-Bag link
CREATE TABLE IF NOT EXISTS `logistics_shipment_bags` (
    `shipment_id` INT UNSIGNED NOT NULL,
    `bag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`shipment_id`, `bag_id`),
    FOREIGN KEY (`shipment_id`) REFERENCES `logistics_shipments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bag_id`) REFERENCES `logistics_bags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deliveries (Phiếu giao hàng)
CREATE TABLE IF NOT EXISTS `logistics_deliveries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `delivery_code` VARCHAR(50) NOT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `package_id` INT UNSIGNED DEFAULT NULL,
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `delivery_type` ENUM('full','partial') DEFAULT 'full',
    `delivered_packages` INT DEFAULT 0,
    `total_packages` INT DEFAULT 0,
    `cod_amount` DECIMAL(15,2) DEFAULT 0,
    `cod_collected` DECIMAL(15,2) DEFAULT 0,
    `cod_method` ENUM('cash','transfer','balance') DEFAULT NULL,
    `status` ENUM('pending','delivering','delivered','failed') DEFAULT 'pending',
    `delivered_by` INT UNSIGNED DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_delivery_code` (`tenant_id`, `delivery_code`),
    INDEX `idx_del_status` (`status`),
    INDEX `idx_del_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipping Rates (Bảng giá vận chuyển)
CREATE TABLE IF NOT EXISTS `logistics_shipping_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `cargo_type` ENUM('easy','difficult') DEFAULT 'easy',
    `rate_per_kg` DECIMAL(10,2) DEFAULT 0,
    `rate_per_cbm` DECIMAL(10,2) DEFAULT 0,
    `min_weight` DECIMAL(8,2) DEFAULT 0,
    `max_weight` DECIMAL(8,2) DEFAULT 0,
    `origin` VARCHAR(50) DEFAULT 'CN',
    `destination` VARCHAR(50) DEFAULT 'VN',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add shipment_id to packages
ALTER TABLE `logistics_packages` ADD COLUMN `shipment_id` INT UNSIGNED DEFAULT NULL AFTER `bag_id`;
-- Add delivery status
ALTER TABLE `logistics_packages` ADD COLUMN `delivered_by` INT UNSIGNED DEFAULT NULL AFTER `received_at`;
ALTER TABLE `logistics_packages` ADD COLUMN `delivered_at` DATETIME DEFAULT NULL AFTER `delivered_by`;
-- Add warehouse role
ALTER TABLE `logistics_packages` ADD COLUMN `warehouse_location` ENUM('cn','vn') DEFAULT NULL AFTER `warehouse_id`;

-- Bag improvements
ALTER TABLE `logistics_bags` ADD COLUMN `shipment_id` INT UNSIGNED DEFAULT NULL AFTER `warehouse_id`;
ALTER TABLE `logistics_bags` ADD COLUMN `cargo_type` ENUM('easy','difficult') DEFAULT 'easy' AFTER `total_weight`;
ALTER TABLE `logistics_bags` ADD COLUMN `rate_per_kg` DECIMAL(10,2) DEFAULT 0 AFTER `cargo_type`;
ALTER TABLE `logistics_bags` ADD COLUMN `rate_per_cbm` DECIMAL(10,2) DEFAULT 0 AFTER `rate_per_kg`;
ALTER TABLE `logistics_bags` ADD COLUMN `domestic_cost` DECIMAL(10,2) DEFAULT 0 AFTER `rate_per_cbm`;

-- Default shipping rates
INSERT INTO `logistics_shipping_rates` (`tenant_id`, `name`, `cargo_type`, `rate_per_kg`, `rate_per_cbm`, `origin`, `destination`) VALUES
(1, 'Hàng thường - Giá theo kg', 'easy', 25000, 0, 'CN', 'VN'),
(1, 'Hàng thường - Giá theo khối', 'easy', 0, 3500000, 'CN', 'VN'),
(1, 'Hàng khó - Giá theo kg', 'difficult', 45000, 0, 'CN', 'VN'),
(1, 'Hàng khó - Giá theo khối', 'difficult', 0, 5500000, 'CN', 'VN');


-- === database/migrations/028_package_modules.sql ===
-- Package 6 modules as plugins
INSERT INTO `plugins` (`name`, `slug`, `description`, `category`, `icon`, `version`, `author`, `is_active`, `config`) VALUES
('Kho hàng', 'warehouse', 'Quản lý kho: xuất nhập kho, kiểm kho, tồn kho, báo cáo, cài đặt liên kết đơn hàng', 'Kho vận', 'ri-store-2-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Gamification', 'gamification', 'Bảng xếp hạng nhân viên, huy hiệu thành tích, điểm thưởng', 'Nhân sự', 'ri-trophy-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Đặt lịch hẹn', 'booking', 'Tạo link đặt lịch hẹn công khai, khách hàng tự chọn slot', 'CRM', 'ri-calendar-check-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Check-in GPS', 'checkin', 'Nhân viên check-in vị trí GPS khi gặp khách hàng, bản đồ theo dõi', 'CRM', 'ri-map-pin-user-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Client Portal', 'client-portal', 'Trang tự phục vụ cho khách hàng: xem đơn hàng, gửi ticket, theo dõi tiến độ', 'CRM', 'ri-user-star-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Chính sách SLA', 'sla', 'Thiết lập SLA cho ticket hỗ trợ: thời gian phản hồi, escalation tự động', 'Hỗ trợ', 'ri-timer-line', '1.0.0', 'ToryCRM', 1, '{}');


-- === database/migrations/029_orders_shipment_id.sql ===
ALTER TABLE `logistics_orders` ADD COLUMN `shipment_id` INT UNSIGNED DEFAULT NULL;


-- === database/migrations/030_bags_dimensions.sql ===
ALTER TABLE `logistics_bags` ADD COLUMN `length_cm` DECIMAL(8,2) DEFAULT NULL AFTER `total_weight`;
ALTER TABLE `logistics_bags` ADD COLUMN `width_cm` DECIMAL(8,2) DEFAULT NULL AFTER `length_cm`;
ALTER TABLE `logistics_bags` ADD COLUMN `height_cm` DECIMAL(8,2) DEFAULT NULL AFTER `width_cm`;
ALTER TABLE `logistics_bags` ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT NULL AFTER `height_cm`;


-- === database/migrations/031_attendance_payroll.sql ===
-- Chấm công
CREATE TABLE IF NOT EXISTS `attendances` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `check_in` TIME DEFAULT NULL,
    `check_out` TIME DEFAULT NULL,
    `status` ENUM('present','absent','late','half_day','leave','holiday') DEFAULT 'present',
    `work_hours` DECIMAL(4,2) DEFAULT NULL,
    `overtime_hours` DECIMAL(4,2) DEFAULT 0,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_attendance` (`tenant_id`, `user_id`, `date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nghỉ phép
CREATE TABLE IF NOT EXISTS `leave_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `leave_type` ENUM('annual','sick','personal','maternity','unpaid','other') DEFAULT 'annual',
    `date_from` DATE NOT NULL,
    `date_to` DATE NOT NULL,
    `days` DECIMAL(4,1) NOT NULL DEFAULT 1,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lương
CREATE TABLE IF NOT EXISTS `payrolls` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `work_days` DECIMAL(4,1) DEFAULT 0,
    `leave_days` DECIMAL(4,1) DEFAULT 0,
    `overtime_hours` DECIMAL(6,2) DEFAULT 0,
    `base_salary` DECIMAL(15,0) DEFAULT 0,
    `overtime_pay` DECIMAL(15,0) DEFAULT 0,
    `bonus` DECIMAL(15,0) DEFAULT 0,
    `deductions` DECIMAL(15,0) DEFAULT 0,
    `insurance` DECIMAL(15,0) DEFAULT 0,
    `tax` DECIMAL(15,0) DEFAULT 0,
    `net_salary` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT DEFAULT NULL,
    `status` ENUM('draft','confirmed','paid') DEFAULT 'draft',
    `paid_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_payroll` (`tenant_id`, `user_id`, `month`, `year`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cấu hình lương nhân viên
ALTER TABLE `users` ADD COLUMN `base_salary` DECIMAL(15,0) DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `leave_balance` DECIMAL(4,1) DEFAULT 12;


-- === database/migrations/032_documents.sql ===
CREATE TABLE IF NOT EXISTS `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `file_type` VARCHAR(50) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'contact, deal, order, etc',
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `is_shared` TINYINT(1) DEFAULT 0,
    `note` TEXT DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_doc_entity` (`entity_type`, `entity_id`),
    INDEX `idx_doc_tenant` (`tenant_id`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/033_payroll_enhanced.sql ===
-- Phụ cấp cho nhân viên
ALTER TABLE `users` ADD COLUMN `allowance_lunch` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp ăn trưa';
ALTER TABLE `users` ADD COLUMN `allowance_transport` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp xăng xe';
ALTER TABLE `users` ADD COLUMN `allowance_phone` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp điện thoại';
ALTER TABLE `users` ADD COLUMN `allowance_other` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp khác';
ALTER TABLE `users` ADD COLUMN `dependents` INT DEFAULT 0 COMMENT 'Số người phụ thuộc (thuế TNCN)';

-- Bổ sung payrolls
ALTER TABLE `payrolls` ADD COLUMN `allowance_lunch` DECIMAL(15,0) DEFAULT 0 AFTER `overtime_pay`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_transport` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_lunch`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_phone` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_transport`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_other` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_phone`;
ALTER TABLE `payrolls` ADD COLUMN `total_allowance` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_other`;
ALTER TABLE `payrolls` ADD COLUMN `gross_salary` DECIMAL(15,0) DEFAULT 0 AFTER `total_allowance`;
ALTER TABLE `payrolls` ADD COLUMN `bhxh` DECIMAL(15,0) DEFAULT 0 COMMENT '8%' AFTER `insurance`;
ALTER TABLE `payrolls` ADD COLUMN `bhyt` DECIMAL(15,0) DEFAULT 0 COMMENT '1.5%' AFTER `bhxh`;
ALTER TABLE `payrolls` ADD COLUMN `bhtn` DECIMAL(15,0) DEFAULT 0 COMMENT '1%' AFTER `bhyt`;
ALTER TABLE `payrolls` ADD COLUMN `tax_income` DECIMAL(15,0) DEFAULT 0 COMMENT 'Thu nhập chịu thuế' AFTER `tax`;
ALTER TABLE `payrolls` ADD COLUMN `advance` DECIMAL(15,0) DEFAULT 0 COMMENT 'Tạm ứng' AFTER `tax_income`;
ALTER TABLE `payrolls` ADD COLUMN `dependents` INT DEFAULT 0 AFTER `advance`;

-- Tạm ứng
CREATE TABLE IF NOT EXISTS `salary_advances` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,0) NOT NULL,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/034_email_plugin.sql ===
-- Email accounts (cấu hình IMAP/SMTP)
CREATE TABLE IF NOT EXISTS `email_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) DEFAULT NULL,
    `imap_host` VARCHAR(255) DEFAULT NULL,
    `imap_port` INT DEFAULT 993,
    `imap_encryption` ENUM('ssl','tls','none') DEFAULT 'ssl',
    `smtp_host` VARCHAR(255) DEFAULT NULL,
    `smtp_port` INT DEFAULT 587,
    `smtp_encryption` ENUM('ssl','tls','none') DEFAULT 'tls',
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(500) NOT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_sync` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_tenant` (`tenant_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email messages (cached from IMAP)
CREATE TABLE IF NOT EXISTS `email_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `account_id` INT UNSIGNED NOT NULL,
    `message_uid` VARCHAR(100) DEFAULT NULL,
    `folder` VARCHAR(50) DEFAULT 'INBOX',
    `from_email` VARCHAR(255) DEFAULT NULL,
    `from_name` VARCHAR(255) DEFAULT NULL,
    `to_emails` TEXT DEFAULT NULL,
    `cc_emails` TEXT DEFAULT NULL,
    `subject` VARCHAR(500) DEFAULT NULL,
    `body_text` MEDIUMTEXT DEFAULT NULL,
    `body_html` MEDIUMTEXT DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `has_attachments` TINYINT(1) DEFAULT 0,
    `contact_id` INT UNSIGNED DEFAULT NULL COMMENT 'Auto-linked contact',
    `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'deal, ticket, order',
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_account` (`account_id`),
    INDEX `idx_email_folder` (`account_id`, `folder`),
    INDEX `idx_email_contact` (`contact_id`),
    FOREIGN KEY (`account_id`) REFERENCES `email_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email attachments
CREATE TABLE IF NOT EXISTS `email_attachments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `size` INT UNSIGNED DEFAULT 0,
    `file_path` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`message_id`) REFERENCES `email_messages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/035_email_api_token.sql ===
ALTER TABLE `email_accounts` ADD COLUMN `api_token` VARCHAR(255) DEFAULT NULL AFTER `password`;


-- === database/migrations/036_email_enhancements.sql ===
ALTER TABLE `email_accounts` ADD COLUMN `signature` TEXT DEFAULT NULL AFTER `api_token`;

CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) DEFAULT NULL,
    `body` MEDIUMTEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_et_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/037_email_labels.sql ===
CREATE TABLE IF NOT EXISTS `email_labels` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#405189',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_label_map` (
    `email_id` BIGINT UNSIGNED NOT NULL,
    `label_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`email_id`, `label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/038_performance_indexes.sql ===
-- Contacts
CREATE INDEX idx_contacts_search ON contacts(tenant_id, is_deleted, status);
CREATE INDEX idx_contacts_owner ON contacts(owner_id);
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_created ON contacts(created_at);

-- Deals
CREATE INDEX idx_deals_tenant_status ON deals(tenant_id, status);
CREATE INDEX idx_deals_owner ON deals(owner_id);

-- Tasks
CREATE INDEX idx_tasks_assigned ON tasks(assigned_to, is_deleted, status);
CREATE INDEX idx_tasks_due ON tasks(due_date);

-- Fund transactions
CREATE INDEX idx_fund_tenant_date ON fund_transactions(tenant_id, transaction_date);

-- Debts
CREATE INDEX idx_debts_tenant_type ON debts(tenant_id, type, status);
CREATE INDEX idx_debts_due ON debts(due_date);

-- Email messages
CREATE INDEX idx_email_sent ON email_messages(sent_at);

-- Activities
CREATE INDEX idx_activities_user ON activities(user_id, created_at);

-- Logistics
CREATE INDEX idx_pkg_tracking ON logistics_packages(tracking_code);
CREATE INDEX idx_lorder_tenant ON logistics_orders(tenant_id, status);

-- Payrolls
CREATE INDEX idx_payroll_period ON payrolls(tenant_id, month, year);


-- === database/migrations/039_lead_forms.sql ===
-- Lead Forms
CREATE TABLE IF NOT EXISTS `lead_forms` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `fields` JSON NOT NULL COMMENT '[{name,label,type,required,options}]',
    `settings` JSON DEFAULT NULL COMMENT '{redirect_url,thank_you_message,notify_users,auto_assign}',
    `style` JSON DEFAULT NULL COMMENT '{theme,button_color,button_text}',
    `is_active` TINYINT(1) DEFAULT 1,
    `submission_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_form_slug` (`tenant_id`, `slug`),
    INDEX `idx_form_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form Submissions
CREATE TABLE IF NOT EXISTS `lead_form_submissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `form_id` INT UNSIGNED NOT NULL,
    `data` JSON NOT NULL,
    `contact_id` INT UNSIGNED DEFAULT NULL COMMENT 'Auto-created contact',
    `source_url` VARCHAR(500) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sub_form` (`form_id`),
    INDEX `idx_sub_tenant` (`tenant_id`),
    FOREIGN KEY (`form_id`) REFERENCES `lead_forms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/040_permission_groups.sql ===
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


-- === database/migrations/041_positions.sql ===
-- Positions management
CREATE TABLE IF NOT EXISTS `positions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pos_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default positions
INSERT INTO `positions` (`tenant_id`, `name`, `sort_order`) VALUES
(1, 'Giám đốc', 1),
(1, 'Phó giám đốc', 2),
(1, 'Trưởng phòng', 3),
(1, 'Phó phòng', 4),
(1, 'Trưởng nhóm', 5),
(1, 'Nhân viên', 6),
(1, 'Thực tập sinh', 7);

-- Add position_id to users table
ALTER TABLE `users` ADD COLUMN `position_id` INT UNSIGNED DEFAULT NULL AFTER `role`;


-- === database/migrations/042_field_label_overrides.sql ===
CREATE TABLE IF NOT EXISTS `field_label_overrides` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `table_name` VARCHAR(100) NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `label` VARCHAR(200) NOT NULL,
    `is_required` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_table_field` (`tenant_id`, `table_name`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/043_orders_shipping_fields.sql ===
-- Add missing shipping & payment fields to orders
ALTER TABLE `orders`
    ADD COLUMN `shipping_address` TEXT DEFAULT NULL AFTER `lading_code`,
    ADD COLUMN `shipping_contact` VARCHAR(100) DEFAULT NULL AFTER `shipping_address`,
    ADD COLUMN `shipping_phone` VARCHAR(20) DEFAULT NULL AFTER `shipping_contact`,
    ADD COLUMN `shipping_province` VARCHAR(100) DEFAULT NULL AFTER `shipping_phone`,
    ADD COLUMN `shipping_district` VARCHAR(100) DEFAULT NULL AFTER `shipping_province`,
    ADD COLUMN `lading_status` VARCHAR(50) DEFAULT NULL AFTER `shipping_district`,
    ADD COLUMN `warehouse_id` INT UNSIGNED DEFAULT NULL AFTER `lading_status`,
    ADD COLUMN `payment_date` DATE DEFAULT NULL AFTER `paid_amount`,
    ADD COLUMN `commission_amount` DECIMAL(15,2) DEFAULT 0.00 AFTER `payment_date`;


-- === database/migrations/044_contacts_missing_fields.sql ===
-- Add missing fields to contacts for import compatibility
ALTER TABLE `contacts`
    ADD COLUMN `tax_code` VARCHAR(50) DEFAULT NULL AFTER `account_code`,
    ADD COLUMN `website` VARCHAR(255) DEFAULT NULL AFTER `email`,
    ADD COLUMN `fax` VARCHAR(20) DEFAULT NULL AFTER `mobile`,
    ADD COLUMN `title` VARCHAR(20) DEFAULT NULL AFTER `last_name`,
    ADD COLUMN `latitude` DECIMAL(10,7) DEFAULT NULL AFTER `ward`,
    ADD COLUMN `longitude` DECIMAL(10,7) DEFAULT NULL AFTER `latitude`;


-- === database/migrations/045_companies_missing_fields.sql ===
-- Add missing fields to companies
ALTER TABLE `companies`
    ADD COLUMN `province` VARCHAR(100) DEFAULT NULL AFTER `city`,
    ADD COLUMN `district` VARCHAR(100) DEFAULT NULL AFTER `province`,
    ADD COLUMN `country` VARCHAR(100) DEFAULT 'Việt Nam' AFTER `district`,
    ADD COLUMN `fax` VARCHAR(20) DEFAULT NULL AFTER `phone`;


-- === database/migrations/046_quotations_shipping.sql ===
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_fee DECIMAL(15,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_note VARCHAR(255) DEFAULT NULL AFTER shipping_fee;


-- === database/migrations/047_quotations_payment_details.sql ===
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_percent DECIMAL(5,2) DEFAULT 0 AFTER shipping_fee;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_after_tax TINYINT(1) DEFAULT 0 AFTER shipping_percent;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_after_tax TINYINT(1) DEFAULT 0 AFTER discount_percent;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS tax_rate DECIMAL(5,2) DEFAULT 0 AFTER tax_amount;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS installation_fee DECIMAL(15,2) DEFAULT 0 AFTER shipping_note;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS installation_percent DECIMAL(5,2) DEFAULT 0 AFTER installation_fee;


-- === database/migrations/048_quotation_attachments.sql ===
CREATE TABLE IF NOT EXISTS quotation_attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT NULL,
    quotation_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED DEFAULT 0,
    mime_type VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (quotation_id),
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/049_quotation_items_discount_percent.sql ===
ALTER TABLE quotation_items ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER tax_rate;


-- === database/migrations/050_order_items_cost_discount.sql ===
ALTER TABLE order_items ADD COLUMN cost_price DECIMAL(15,2) DEFAULT 0 AFTER unit_price;
ALTER TABLE order_items ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER tax_amount;


-- === database/migrations/051_contracts_items_and_fields.sql ===
ALTER TABLE contracts ADD COLUMN contact_name VARCHAR(255) DEFAULT NULL AFTER company_id;
ALTER TABLE contracts ADD COLUMN subtotal DECIMAL(15,2) DEFAULT 0 AFTER value;
ALTER TABLE contracts ADD COLUMN discount_amount DECIMAL(15,2) DEFAULT 0 AFTER subtotal;
ALTER TABLE contracts ADD COLUMN shipping_fee DECIMAL(15,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE contracts ADD COLUMN installation_fee DECIMAL(15,2) DEFAULT 0 AFTER shipping_fee;
ALTER TABLE contracts ADD COLUMN tax_amount DECIMAL(15,2) DEFAULT 0 AFTER installation_fee;
ALTER TABLE contracts ADD COLUMN actual_value DECIMAL(15,2) DEFAULT 0 AFTER tax_amount;
ALTER TABLE contracts ADD COLUMN executed_amount DECIMAL(15,2) DEFAULT 0 AFTER actual_value;
ALTER TABLE contracts ADD COLUMN paid_amount DECIMAL(15,2) DEFAULT 0 AFTER executed_amount;
ALTER TABLE contracts ADD COLUMN installation_address TEXT DEFAULT NULL AFTER paid_amount;

CREATE TABLE IF NOT EXISTS contract_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit VARCHAR(50) DEFAULT NULL,
    unit_price DECIMAL(15,2) DEFAULT 0,
    cost_price DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    KEY (contract_id),
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/052_merge_companies_into_contacts.sql ===
-- Add company fields directly to contacts table
ALTER TABLE contacts
    ADD COLUMN company_name VARCHAR(255) DEFAULT NULL AFTER last_name,
    ADD COLUMN company_phone VARCHAR(20) DEFAULT NULL AFTER company_name,
    ADD COLUMN company_email VARCHAR(150) DEFAULT NULL AFTER company_phone,
    ADD COLUMN industry VARCHAR(100) DEFAULT NULL AFTER company_email,
    ADD COLUMN company_size VARCHAR(50) DEFAULT NULL AFTER industry;

-- Create contact_persons table for sub-contacts
CREATE TABLE IF NOT EXISTS contact_persons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT NULL,
    contact_id INT UNSIGNED NOT NULL,
    title VARCHAR(20) DEFAULT NULL,
    full_name VARCHAR(200) NOT NULL,
    position VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    note TEXT DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    receive_email TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_cp_contact (contact_id),
    INDEX idx_cp_tenant (tenant_id)
);

-- Migrate: copy company data into contacts
UPDATE contacts c
INNER JOIN companies comp ON c.company_id = comp.id
SET c.company_name = comp.name,
    c.company_phone = CASE WHEN comp.phone != c.phone THEN comp.phone ELSE NULL END,
    c.company_email = CASE WHEN comp.email != c.email THEN comp.email ELSE NULL END,
    c.industry = comp.industry,
    c.company_size = comp.company_size;

-- For contacts without company: use full name as company_name
UPDATE contacts SET company_name = CONCAT(COALESCE(last_name, ''), ' ', first_name)
WHERE company_id IS NULL AND company_name IS NULL;


-- === database/migrations/053_getfly_sync.sql ===
CREATE TABLE IF NOT EXISTS getfly_sync_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    api_domain VARCHAR(255) NOT NULL,
    api_key VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS getfly_sync_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    endpoint VARCHAR(100) NOT NULL,
    status ENUM('running','success','error') DEFAULT 'running',
    records_synced INT DEFAULT 0,
    error_message TEXT DEFAULT NULL,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL
);


-- === database/migrations/054_contacts_full_name.sql ===
ALTER TABLE contacts ADD COLUMN full_name VARCHAR(255) DEFAULT NULL AFTER last_name;
UPDATE contacts SET full_name = TRIM(CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''))) WHERE full_name IS NULL OR full_name = '';


-- === database/migrations/054_order_payments.sql ===
CREATE TABLE IF NOT EXISTS order_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    order_id INT UNSIGNED NOT NULL,
    payment_date DATE DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_op_order (order_id)
);


-- === database/migrations/055_activities_location.sql ===
ALTER TABLE activities
  ADD COLUMN latitude DECIMAL(10,7) NULL,
  ADD COLUMN longitude DECIMAL(10,7) NULL,
  ADD COLUMN address VARCHAR(500) NULL;


-- === database/migrations/055_contracts_getfly_fields.sql ===
-- Contracts: Add Getfly-compatible fields
-- Status: change ENUM to VARCHAR to support Getfly statuses (pending, approved, in_progress, renewed, auto_renewed, completed, cancelled)
ALTER TABLE contracts MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending';

-- Contract code (Mã hợp đồng - khác với Số hợp đồng)
ALTER TABLE contracts ADD COLUMN `contract_code` VARCHAR(100) DEFAULT NULL AFTER `contract_number`;

-- Payment & usage
ALTER TABLE contracts ADD COLUMN `payment_method` VARCHAR(50) DEFAULT NULL AFTER `type`;
ALTER TABLE contracts ADD COLUMN `usage_type` VARCHAR(50) DEFAULT 'one_time' AFTER `payment_method`;

-- Dates
ALTER TABLE contracts ADD COLUMN `created_date` DATE DEFAULT NULL COMMENT 'Ngay tao hop dong' AFTER `end_date`;
ALTER TABLE contracts ADD COLUMN `actual_start_date` DATE DEFAULT NULL AFTER `created_date`;
ALTER TABLE contracts ADD COLUMN `actual_end_date` DATE DEFAULT NULL AFTER `actual_start_date`;

-- Location & project
ALTER TABLE contracts ADD COLUMN `location` VARCHAR(255) DEFAULT NULL AFTER `installation_address`;
ALTER TABLE contracts ADD COLUMN `project` VARCHAR(255) DEFAULT NULL AFTER `location`;

-- Related contracts
ALTER TABLE contracts ADD COLUMN `related_contract_id` INT UNSIGNED DEFAULT NULL AFTER `parent_contract_id`;
ALTER TABLE contracts ADD COLUMN `quote_id` INT UNSIGNED DEFAULT NULL COMMENT 'Bao gia lien quan' AFTER `deal_id`;

-- Party A (Ben A - Company/Seller)
ALTER TABLE contracts ADD COLUMN `party_a_company_id` INT UNSIGNED DEFAULT NULL AFTER `company_id`;
ALTER TABLE contracts ADD COLUMN `party_a_name` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_address` TEXT DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_phone` VARCHAR(50) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_fax` VARCHAR(50) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_representative` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_position` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_bank_account` VARCHAR(100) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_bank_name` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_a_tax_code` VARCHAR(50) DEFAULT NULL;

-- Party B (Ben B - Customer/Buyer)
ALTER TABLE contracts ADD COLUMN `party_b_name` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_address` TEXT DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_phone` VARCHAR(50) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_fax` VARCHAR(50) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_representative` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_position` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_bank_account` VARCHAR(100) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_bank_name` VARCHAR(255) DEFAULT NULL;
ALTER TABLE contracts ADD COLUMN `party_b_tax_code` VARCHAR(50) DEFAULT NULL;

-- Fee percents & flags
ALTER TABLE contracts ADD COLUMN `discount_percent` DECIMAL(5,2) DEFAULT 0 AFTER `discount_amount`;
ALTER TABLE contracts ADD COLUMN `discount_after_tax` TINYINT(1) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `shipping_fee_percent` DECIMAL(5,2) DEFAULT 0 AFTER `shipping_fee`;
ALTER TABLE contracts ADD COLUMN `shipping_after_tax` TINYINT(1) DEFAULT 1;
ALTER TABLE contracts ADD COLUMN `apply_vat` TINYINT(1) DEFAULT 1;
ALTER TABLE contracts ADD COLUMN `vat_percent` DECIMAL(5,2) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `vat_amount` DECIMAL(15,2) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `installation_fee_percent` DECIMAL(5,2) DEFAULT 0 AFTER `installation_fee`;

-- Auto-actions (Getfly checkboxes)
ALTER TABLE contracts ADD COLUMN `auto_create_order` TINYINT(1) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `auto_notify_expiry` TINYINT(1) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `auto_send_sms` TINYINT(1) DEFAULT 0;
ALTER TABLE contracts ADD COLUMN `auto_send_email` TINYINT(1) DEFAULT 0;

-- Contract related users (Nguoi lien quan)
CREATE TABLE IF NOT EXISTS `contract_related_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `commission` DECIMAL(15,2) DEFAULT 0,
    KEY (`contract_id`),
    KEY (`user_id`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/056_contract_comments.sql ===
-- Contract comments / activity log
CREATE TABLE IF NOT EXISTS `contract_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`contract_id`),
    KEY (`user_id`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/056_persons_table.sql ===
-- Phase 1: Schema — Global persons + employment-style contact_persons

CREATE TABLE IF NOT EXISTS persons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    gender ENUM('male','female','other') NULL,
    date_of_birth DATE NULL,
    avatar VARCHAR(255) NULL,
    note TEXT NULL,
    is_hidden TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _origin_cp_id INT UNSIGNED NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_phone (tenant_id, phone),
    INDEX idx_email (tenant_id, email),
    INDEX idx_origin (_origin_cp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE contact_persons
    ADD COLUMN person_id INT UNSIGNED NULL AFTER tenant_id,
    ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER is_primary,
    ADD COLUMN start_date DATE NULL AFTER is_active,
    ADD COLUMN end_date DATE NULL AFTER start_date,
    ADD INDEX idx_person (person_id);

-- Phase 2: Backfill — each existing contact_person gets its own person row
-- (KHÔNG auto-merge theo SĐT để tránh gộp nhầm 2 người share SĐT)

-- Clean any NULL tenant_id trước khi insert (FK invariant)
UPDATE contact_persons cp JOIN contacts c ON c.id = cp.contact_id SET cp.tenant_id = c.tenant_id WHERE cp.tenant_id IS NULL;
UPDATE contact_persons SET tenant_id = 1 WHERE tenant_id IS NULL;

INSERT INTO persons (tenant_id, full_name, phone, email, gender, date_of_birth, note, created_at, updated_at, _origin_cp_id)
SELECT tenant_id, full_name, phone, email, gender, date_of_birth, note, created_at, updated_at, id
FROM contact_persons;

UPDATE contact_persons cp
JOIN persons p ON p._origin_cp_id = cp.id
SET cp.person_id = p.id;

-- Drop temporary tracking column
ALTER TABLE persons DROP COLUMN _origin_cp_id;


-- === database/migrations/057_document_templates.sql ===
-- Document templates (Mẫu báo giá, hợp đồng)
CREATE TABLE IF NOT EXISTS `document_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `type` VARCHAR(30) NOT NULL COMMENT 'quotation, contract',
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL COMMENT 'HTML template content',
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (`tenant_id`),
    KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/058_document_enhancements.sql ===
-- Template version history
CREATE TABLE IF NOT EXISTS `document_template_versions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT UNSIGNED NOT NULL,
    `content` LONGTEXT NOT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`template_id`),
    FOREIGN KEY (`template_id`) REFERENCES `document_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract attachments (phụ lục HĐ)
CREATE TABLE IF NOT EXISTS `contract_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`contract_id`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract signatures (ký điện tử)
CREATE TABLE IF NOT EXISTS `contract_signatures` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `signer_name` VARCHAR(255) NOT NULL,
    `signer_email` VARCHAR(255) DEFAULT NULL,
    `signer_role` VARCHAR(50) DEFAULT 'customer' COMMENT 'customer, company',
    `signature_data` LONGTEXT DEFAULT NULL COMMENT 'Base64 signature image',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `signed_at` DATETIME DEFAULT NULL,
    `token` VARCHAR(100) DEFAULT NULL,
    `status` VARCHAR(30) DEFAULT 'pending' COMMENT 'pending, signed, declined',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`contract_id`),
    UNIQUE KEY (`token`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/059_company_profiles.sql ===
-- Company profiles (Thông tin các công ty/pháp nhân của mình)
CREATE TABLE IF NOT EXISTS `company_profiles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100) DEFAULT NULL,
    `tax_code` VARCHAR(50) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `fax` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `representative` VARCHAR(255) DEFAULT NULL,
    `representative_title` VARCHAR(255) DEFAULT NULL,
    `bank_account` VARCHAR(100) DEFAULT NULL,
    `bank_name` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(500) DEFAULT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- === database/migrations/060_contacts_customer_group.sql ===
-- Add customer_group column to contacts (synced from Getfly account_type)
ALTER TABLE `contacts`
    ADD COLUMN `customer_group` VARCHAR(50) DEFAULT NULL AFTER `status`;


-- === database/migrations/061_quotations_extra_fields.sql ===
-- Add extra fields for quotation form (Getfly sync)
ALTER TABLE `quotations`
    ADD COLUMN `contact_person_id` INT UNSIGNED DEFAULT NULL AFTER `contact_id`,
    ADD COLUMN `address` TEXT DEFAULT NULL AFTER `contact_person_id`,
    ADD COLUMN `contact_phone` VARCHAR(30) DEFAULT NULL AFTER `address`,
    ADD COLUMN `contact_email` VARCHAR(150) DEFAULT NULL AFTER `contact_phone`,
    ADD COLUMN `description` TEXT DEFAULT NULL AFTER `notes`,
    ADD COLUMN `project` VARCHAR(255) DEFAULT NULL AFTER `description`,
    ADD COLUMN `location` VARCHAR(255) DEFAULT NULL AFTER `project`,
    ADD COLUMN `revision` INT DEFAULT 1 AFTER `location`,
    ADD COLUMN `content` LONGTEXT DEFAULT NULL AFTER `revision`,
    ADD COLUMN `campaign_id` INT UNSIGNED DEFAULT NULL AFTER `content`,
    ADD COLUMN `shipping_percent` DECIMAL(10,2) DEFAULT 0 AFTER `shipping_fee`,
    ADD COLUMN `installation_percent` DECIMAL(10,2) DEFAULT 0 AFTER `installation_fee`,
    ADD COLUMN `discount_percent` DECIMAL(10,2) DEFAULT 0 AFTER `discount_amount`;


-- === database/migrations/062_field_default_values.sql ===
-- Add default_value column to field_label_overrides
ALTER TABLE `field_label_overrides`
    ADD COLUMN `default_value` TEXT DEFAULT NULL AFTER `check_duplicate`;


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


SET FOREIGN_KEY_CHECKS = 1;
