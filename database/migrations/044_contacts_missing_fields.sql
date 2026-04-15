-- Add missing fields to contacts for import compatibility
ALTER TABLE `contacts`
    ADD COLUMN `tax_code` VARCHAR(50) DEFAULT NULL AFTER `account_code`,
    ADD COLUMN `website` VARCHAR(255) DEFAULT NULL AFTER `email`,
    ADD COLUMN `fax` VARCHAR(20) DEFAULT NULL AFTER `mobile`,
    ADD COLUMN `title` VARCHAR(20) DEFAULT NULL AFTER `last_name`,
    ADD COLUMN `latitude` DECIMAL(10,7) DEFAULT NULL AFTER `ward`,
    ADD COLUMN `longitude` DECIMAL(10,7) DEFAULT NULL AFTER `latitude`;
