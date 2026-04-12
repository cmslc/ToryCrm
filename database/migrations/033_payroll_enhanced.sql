-- Phụ cấp cho nhân viên
ALTER TABLE `users` ADD COLUMN `allowance_lunch` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp ăn trưa';
ALTER TABLE `users` ADD COLUMN `allowance_transport` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp xăng xe';
ALTER TABLE `users` ADD COLUMN `allowance_phone` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp điện thoại';
ALTER TABLE `users` ADD COLUMN `allowance_other` DECIMAL(15,0) DEFAULT 0 COMMENT 'Phụ cấp khác';
ALTER TABLE `users` ADD COLUMN `dependents` INT DEFAULT 0 COMMENT 'Số người phụ thuộc (thuế TNCN)';

-- Bổ sung payrolls
ALTER TABLE `payrolls` ADD COLUMN `allowance_lunch` DECIMAL(15,0) DEFAULT 0 AFTER `overtime_pay`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_transport` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_lunch`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_phone` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_transport`;
ALTER TABLE `payrolls` ADD COLUMN `allowance_other` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_phone`;
ALTER TABLE `payrolls` ADD COLUMN `total_allowance` DECIMAL(15,0) DEFAULT 0 AFTER `allowance_other`;
ALTER TABLE `payrolls` ADD COLUMN `gross_salary` DECIMAL(15,0) DEFAULT 0 AFTER `total_allowance`;
ALTER TABLE `payrolls` ADD COLUMN `bhxh` DECIMAL(15,0) DEFAULT 0 COMMENT '8%' AFTER `insurance`;
ALTER TABLE `payrolls` ADD COLUMN `bhyt` DECIMAL(15,0) DEFAULT 0 COMMENT '1.5%' AFTER `bhxh`;
ALTER TABLE `payrolls` ADD COLUMN `bhtn` DECIMAL(15,0) DEFAULT 0 COMMENT '1%' AFTER `bhyt`;
ALTER TABLE `payrolls` ADD COLUMN `tax_income` DECIMAL(15,0) DEFAULT 0 COMMENT 'Thu nhập chịu thuế' AFTER `tax`;
ALTER TABLE `payrolls` ADD COLUMN `advance` DECIMAL(15,0) DEFAULT 0 COMMENT 'Tạm ứng' AFTER `tax_income`;
ALTER TABLE `payrolls` ADD COLUMN `dependents` INT DEFAULT 0 AFTER `advance`;

-- Tạm ứng
CREATE TABLE IF NOT EXISTS `salary_advances` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,0) NOT NULL,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
