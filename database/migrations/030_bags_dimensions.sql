ALTER TABLE `logistics_bags` ADD COLUMN `length_cm` DECIMAL(8,2) DEFAULT NULL AFTER `total_weight`;
ALTER TABLE `logistics_bags` ADD COLUMN `width_cm` DECIMAL(8,2) DEFAULT NULL AFTER `length_cm`;
ALTER TABLE `logistics_bags` ADD COLUMN `height_cm` DECIMAL(8,2) DEFAULT NULL AFTER `width_cm`;
ALTER TABLE `logistics_bags` ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT NULL AFTER `height_cm`;
