-- Chấm công
CREATE TABLE IF NOT EXISTS `attendances` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `check_in` TIME DEFAULT NULL,
    `check_out` TIME DEFAULT NULL,
    `status` ENUM('present','absent','late','half_day','leave','holiday') DEFAULT 'present',
    `work_hours` DECIMAL(4,2) DEFAULT NULL,
    `overtime_hours` DECIMAL(4,2) DEFAULT 0,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_attendance` (`tenant_id`, `user_id`, `date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nghỉ phép
CREATE TABLE IF NOT EXISTS `leave_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `leave_type` ENUM('annual','sick','personal','maternity','unpaid','other') DEFAULT 'annual',
    `date_from` DATE NOT NULL,
    `date_to` DATE NOT NULL,
    `days` DECIMAL(4,1) NOT NULL DEFAULT 1,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lương
CREATE TABLE IF NOT EXISTS `payrolls` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` INT UNSIGNED NOT NULL,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `work_days` DECIMAL(4,1) DEFAULT 0,
    `leave_days` DECIMAL(4,1) DEFAULT 0,
    `overtime_hours` DECIMAL(6,2) DEFAULT 0,
    `base_salary` DECIMAL(15,0) DEFAULT 0,
    `overtime_pay` DECIMAL(15,0) DEFAULT 0,
    `bonus` DECIMAL(15,0) DEFAULT 0,
    `deductions` DECIMAL(15,0) DEFAULT 0,
    `insurance` DECIMAL(15,0) DEFAULT 0,
    `tax` DECIMAL(15,0) DEFAULT 0,
    `net_salary` DECIMAL(15,0) DEFAULT 0,
    `note` TEXT DEFAULT NULL,
    `status` ENUM('draft','confirmed','paid') DEFAULT 'draft',
    `paid_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_payroll` (`tenant_id`, `user_id`, `month`, `year`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cấu hình lương nhân viên
ALTER TABLE `users` ADD COLUMN `base_salary` DECIMAL(15,0) DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `leave_balance` DECIMAL(4,1) DEFAULT 12;
