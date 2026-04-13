-- Contacts
ALTER TABLE `contacts` ADD INDEX IF NOT EXISTS `idx_contacts_search` (`tenant_id`, `is_deleted`, `status`);
ALTER TABLE `contacts` ADD INDEX IF NOT EXISTS `idx_contacts_owner` (`owner_id`);
ALTER TABLE `contacts` ADD INDEX IF NOT EXISTS `idx_contacts_email` (`email`);
ALTER TABLE `contacts` ADD INDEX IF NOT EXISTS `idx_contacts_created` (`created_at`);

-- Deals
ALTER TABLE `deals` ADD INDEX IF NOT EXISTS `idx_deals_tenant_status` (`tenant_id`, `status`);
ALTER TABLE `deals` ADD INDEX IF NOT EXISTS `idx_deals_owner` (`owner_id`);
ALTER TABLE `deals` ADD INDEX IF NOT EXISTS `idx_deals_stage` (`stage_id`);

-- Tasks
ALTER TABLE `tasks` ADD INDEX IF NOT EXISTS `idx_tasks_assigned` (`assigned_to`, `is_deleted`, `status`);
ALTER TABLE `tasks` ADD INDEX IF NOT EXISTS `idx_tasks_due` (`due_date`);

-- Orders
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_tenant` (`tenant_id`, `status`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_contact` (`contact_id`);

-- Fund transactions
ALTER TABLE `fund_transactions` ADD INDEX IF NOT EXISTS `idx_fund_tenant_date` (`tenant_id`, `transaction_date`);
ALTER TABLE `fund_transactions` ADD INDEX IF NOT EXISTS `idx_fund_type_status` (`type`, `status`);

-- Debts
ALTER TABLE `debts` ADD INDEX IF NOT EXISTS `idx_debts_tenant_type` (`tenant_id`, `type`, `status`);
ALTER TABLE `debts` ADD INDEX IF NOT EXISTS `idx_debts_due` (`due_date`);

-- Email messages
ALTER TABLE `email_messages` ADD INDEX IF NOT EXISTS `idx_email_sent` (`sent_at`);
ALTER TABLE `email_messages` ADD INDEX IF NOT EXISTS `idx_email_read` (`account_id`, `is_read`);

-- Activities
ALTER TABLE `activities` ADD INDEX IF NOT EXISTS `idx_activities_user` (`user_id`, `created_at`);
ALTER TABLE `activities` ADD INDEX IF NOT EXISTS `idx_activities_contact` (`contact_id`);

-- Logistics
ALTER TABLE `logistics_packages` ADD INDEX IF NOT EXISTS `idx_pkg_tenant_status` (`tenant_id`, `status`);
ALTER TABLE `logistics_packages` ADD INDEX IF NOT EXISTS `idx_pkg_tracking` (`tracking_code`);
ALTER TABLE `logistics_orders` ADD INDEX IF NOT EXISTS `idx_lorder_tenant` (`tenant_id`, `status`);
ALTER TABLE `logistics_bags` ADD INDEX IF NOT EXISTS `idx_bag_tenant` (`tenant_id`, `status`);

-- Payrolls
ALTER TABLE `payrolls` ADD INDEX IF NOT EXISTS `idx_payroll_period` (`tenant_id`, `month`, `year`);

-- Commissions
ALTER TABLE `commissions` ADD INDEX IF NOT EXISTS `idx_comm_period` (`tenant_id`, `created_at`);
