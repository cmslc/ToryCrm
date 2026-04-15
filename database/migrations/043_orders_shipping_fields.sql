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
