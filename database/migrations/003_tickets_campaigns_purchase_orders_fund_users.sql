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
