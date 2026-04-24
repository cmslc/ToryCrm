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
