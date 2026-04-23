-- Add delivery info fields to orders
ALTER TABLE `orders`
    ADD COLUMN `delivery_type` ENUM('self','partner') DEFAULT 'self' AFTER `shipping_district`,
    ADD COLUMN `delivery_date` DATE DEFAULT NULL AFTER `delivery_type`,
    ADD COLUMN `delivery_partner` VARCHAR(255) DEFAULT NULL AFTER `delivery_date`,
    ADD COLUMN `delivery_notes` TEXT DEFAULT NULL AFTER `delivery_partner`;
