-- Add missing fields to companies
ALTER TABLE `companies`
    ADD COLUMN `province` VARCHAR(100) DEFAULT NULL AFTER `city`,
    ADD COLUMN `district` VARCHAR(100) DEFAULT NULL AFTER `province`,
    ADD COLUMN `country` VARCHAR(100) DEFAULT 'Việt Nam' AFTER `district`,
    ADD COLUMN `fax` VARCHAR(20) DEFAULT NULL AFTER `phone`;
