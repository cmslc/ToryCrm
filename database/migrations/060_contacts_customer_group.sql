-- Add customer_group column to contacts (synced from Getfly account_type)
ALTER TABLE `contacts`
    ADD COLUMN `customer_group` VARCHAR(50) DEFAULT NULL AFTER `status`;
