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
