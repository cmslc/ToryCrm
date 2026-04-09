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
