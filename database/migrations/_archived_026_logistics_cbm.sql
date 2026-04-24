ALTER TABLE `logistics_orders` ADD COLUMN `total_weight` DECIMAL(10,2) DEFAULT 0 AFTER `total_packages`;
ALTER TABLE `logistics_orders` ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT 0 AFTER `total_weight`;
ALTER TABLE `logistics_orders` ADD COLUMN `images` JSON DEFAULT NULL AFTER `note`;
ALTER TABLE `logistics_packages` ADD COLUMN `cbm` DECIMAL(10,4) DEFAULT NULL AFTER `height_cm`;
UPDATE `logistics_packages` SET `cbm` = ROUND(`length_cm` * `width_cm` * `height_cm` / 1000000, 4) WHERE `length_cm` > 0 AND `width_cm` > 0 AND `height_cm` > 0;
