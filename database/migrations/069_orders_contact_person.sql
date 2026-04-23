ALTER TABLE `orders`
    ADD COLUMN `contact_person_id` INT UNSIGNED DEFAULT NULL AFTER `contact_id`,
    ADD KEY `idx_orders_contact_person` (`contact_person_id`);
