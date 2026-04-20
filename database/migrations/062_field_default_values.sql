-- Add default_value column to field_label_overrides
ALTER TABLE `field_label_overrides`
    ADD COLUMN `default_value` TEXT DEFAULT NULL AFTER `check_duplicate`;
