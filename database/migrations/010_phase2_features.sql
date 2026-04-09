-- ToryCRM Migration 010: Phase 2 Features
-- Team Collaboration, Email Templates, Saved Views, SLA
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TEAM COLLABORATION - Internal Chat per entity
-- ============================================================
CREATE TABLE IF NOT EXISTS `internal_chats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `mentions` JSON DEFAULT NULL,
    `attachments` JSON DEFAULT NULL,
    `is_pinned` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chat_entity` (`entity_type`, `entity_id`),
    INDEX `idx_chat_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. EMAIL TEMPLATES with merge tags
-- ============================================================
-- Upgrade email_templates if exists, create if not
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `category` VARCHAR(100) DEFAULT 'Chung',
    `variables` JSON DEFAULT NULL,
    `thumbnail` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `use_count` INT DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to existing table
SET @ct = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='email_templates' AND column_name='tenant_id');
SET @s = IF(@ct=0, 'ALTER TABLE email_templates ADD COLUMN tenant_id INT UNSIGNED DEFAULT NULL AFTER id, ADD COLUMN category VARCHAR(100) DEFAULT "Chung", ADD COLUMN variables JSON DEFAULT NULL, ADD COLUMN thumbnail VARCHAR(500) DEFAULT NULL, ADD COLUMN is_active TINYINT(1) DEFAULT 1, ADD COLUMN use_count INT DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Seed email templates
INSERT INTO `email_templates` (`tenant_id`, `name`, `subject`, `body`, `category`, `variables`) VALUES
(1, 'Chào mừng khách hàng mới', 'Chào mừng {{ten_kh}} đến với {{ten_cty}}!',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#405189">Xin chào {{ten_kh}}!</h2>
<p>Cảm ơn bạn đã tin tưởng và lựa chọn <strong>{{ten_cty}}</strong>.</p>
<p>Người phụ trách của bạn là <strong>{{nguoi_phu_trach}}</strong>. Mọi thắc mắc xin liên hệ:</p>
<ul><li>Email: {{email_npt}}</li><li>Điện thoại: {{sdt_npt}}</li></ul>
<p>Trân trọng,<br>{{ten_cty}}</p>
</div>', 'Chào hỏi', '["ten_kh","ten_cty","nguoi_phu_trach","email_npt","sdt_npt"]'),

(1, 'Báo giá sản phẩm', 'Báo giá {{ten_sp}} - {{ten_cty}}',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#405189">Báo giá sản phẩm</h2>
<p>Kính gửi {{ten_kh}},</p>
<p>Chúng tôi xin gửi báo giá cho <strong>{{ten_sp}}</strong>:</p>
<table style="width:100%;border-collapse:collapse"><tr style="background:#f3f6f9"><th style="padding:8px;border:1px solid #e9ebec;text-align:left">Sản phẩm</th><th style="padding:8px;border:1px solid #e9ebec">Đơn giá</th></tr>
<tr><td style="padding:8px;border:1px solid #e9ebec">{{ten_sp}}</td><td style="padding:8px;border:1px solid #e9ebec;text-align:center">{{don_gia}}</td></tr></table>
<p>Báo giá có hiệu lực trong 15 ngày.</p>
<p>Trân trọng,<br>{{nguoi_phu_trach}}<br>{{ten_cty}}</p>
</div>', 'Bán hàng', '["ten_kh","ten_sp","don_gia","ten_cty","nguoi_phu_trach"]'),

(1, 'Nhắc thanh toán', 'Nhắc nhở thanh toán đơn hàng {{ma_don}}',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#f06548">Nhắc nhở thanh toán</h2>
<p>Kính gửi {{ten_kh}},</p>
<p>Đơn hàng <strong>{{ma_don}}</strong> của bạn có số tiền <strong>{{so_tien}}</strong> chưa thanh toán.</p>
<p>Hạn thanh toán: <strong>{{han_thanh_toan}}</strong></p>
<p>Vui lòng thanh toán sớm để tránh gián đoạn dịch vụ.</p>
<p>Trân trọng,<br>{{ten_cty}}</p>
</div>', 'Tài chính', '["ten_kh","ma_don","so_tien","han_thanh_toan","ten_cty"]'),

(1, 'Follow-up sau demo', 'Cảm ơn {{ten_kh}} đã tham gia demo!',
'<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto">
<h2 style="color:#0ab39c">Cảm ơn bạn!</h2>
<p>Xin chào {{ten_kh}},</p>
<p>Cảm ơn bạn đã dành thời gian tham gia buổi demo sản phẩm. Hy vọng nội dung hữu ích cho bạn.</p>
<p>Nếu có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với tôi.</p>
<p>Trân trọng,<br>{{nguoi_phu_trach}}<br>{{sdt_npt}}</p>
</div>', 'Bán hàng', '["ten_kh","nguoi_phu_trach","sdt_npt"]');

-- ============================================================
-- 3. SAVED VIEWS (Bộ lọc đã lưu)
-- ============================================================
CREATE TABLE IF NOT EXISTS `saved_views` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `filters` JSON NOT NULL,
    `columns` JSON DEFAULT NULL,
    `sort_by` VARCHAR(50) DEFAULT NULL,
    `sort_dir` VARCHAR(4) DEFAULT 'DESC',
    `is_default` TINYINT(1) DEFAULT 0,
    `is_shared` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sv_user_module` (`user_id`, `module`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TICKET SLA
-- ============================================================
CREATE TABLE IF NOT EXISTS `sla_policies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    `first_response_hours` INT NOT NULL DEFAULT 24,
    `resolution_hours` INT NOT NULL DEFAULT 72,
    `escalate_to` INT UNSIGNED DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`escalate_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tickets`
    ADD COLUMN `sla_policy_id` INT UNSIGNED DEFAULT NULL,
    ADD COLUMN `first_response_at` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_first_response_due` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_resolution_due` DATETIME DEFAULT NULL,
    ADD COLUMN `sla_breached` TINYINT(1) DEFAULT 0;

-- Seed SLA policies
INSERT INTO `sla_policies` (`tenant_id`, `name`, `priority`, `first_response_hours`, `resolution_hours`) VALUES
(1, 'Khẩn cấp', 'urgent', 1, 4),
(1, 'Cao', 'high', 4, 24),
(1, 'Trung bình', 'medium', 8, 48),
(1, 'Thấp', 'low', 24, 72);

SET FOREIGN_KEY_CHECKS = 1;
