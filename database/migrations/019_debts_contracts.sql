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
