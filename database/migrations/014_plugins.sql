-- Plugin System & Marketplace
-- Created: 2026-04-08

CREATE TABLE IF NOT EXISTS `plugins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `author` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'ri-plug-line',
    `category` VARCHAR(100) DEFAULT 'general',
    `config` JSON DEFAULT NULL,
    `hooks` JSON DEFAULT NULL,
    `is_installed` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 0,
    `installed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tenant_plugins` (
    `tenant_id` INT UNSIGNED NOT NULL,
    `plugin_id` INT UNSIGNED NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `config` JSON DEFAULT NULL,
    `installed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`tenant_id`, `plugin_id`),
    FOREIGN KEY (`plugin_id`) REFERENCES `plugins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed sample plugins
INSERT INTO `plugins` (`slug`, `name`, `description`, `version`, `author`, `icon`, `category`, `config`) VALUES
('google-analytics', 'Google Analytics', 'Tích hợp Google Analytics để theo dõi hành vi người dùng trên portal, landing page và form. Hỗ trợ GA4.', '1.0.0', 'ToryCRM', 'ri-line-chart-line', 'tracking', '{"fields": [{"key": "tracking_id", "label": "Measurement ID", "type": "text", "placeholder": "G-XXXXXXXXXX", "required": true}, {"key": "enable_portal", "label": "Bật tracking trên Client Portal", "type": "checkbox", "default": true}]}'),
('sms-gateway', 'SMS Gateway', 'Gửi SMS tự động qua Twilio hoặc ESMS. Hỗ trợ gửi OTP, nhắc lịch hẹn, thông báo đơn hàng.', '1.2.0', 'ToryCRM', 'ri-message-2-line', 'communication', '{"fields": [{"key": "provider", "label": "Nhà cung cấp", "type": "select", "options": ["twilio", "esms"], "required": true}, {"key": "api_key", "label": "API Key", "type": "text", "required": true}, {"key": "api_secret", "label": "API Secret", "type": "text", "required": true}, {"key": "sender_name", "label": "Tên người gửi", "type": "text", "placeholder": "ToryCRM"}]}'),
('zalo-mini-app', 'Zalo Mini App', 'Kết nối Zalo Mini App để nhận tin nhắn, gửi thông báo ZNS và quản lý khách hàng từ Zalo.', '1.0.0', 'ToryCRM', 'ri-chat-smile-2-line', 'communication', '{"fields": [{"key": "app_id", "label": "Zalo App ID", "type": "text", "required": true}, {"key": "secret_key", "label": "Secret Key", "type": "text", "required": true}, {"key": "oa_id", "label": "Official Account ID", "type": "text"}, {"key": "webhook_url", "label": "Webhook URL", "type": "text", "readonly": true}]}'),
('export-pdf-pro', 'Export PDF Pro', 'Xuất báo giá, hóa đơn, hợp đồng sang PDF chuyên nghiệp với template tùy chỉnh và logo công ty.', '2.0.0', 'ToryCRM', 'ri-file-pdf-2-line', 'productivity', '{"fields": [{"key": "company_name", "label": "Tên công ty", "type": "text"}, {"key": "company_address", "label": "Địa chỉ", "type": "textarea"}, {"key": "footer_text", "label": "Chân trang", "type": "text"}, {"key": "paper_size", "label": "Khổ giấy", "type": "select", "options": ["A4", "Letter", "A5"], "default": "A4"}]}'),
('custom-fields', 'Custom Fields Builder', 'Thêm trường dữ liệu tùy chỉnh cho khách hàng, cơ hội, đơn hàng. Hỗ trợ text, số, ngày, dropdown, checkbox.', '1.1.0', 'ToryCRM', 'ri-list-settings-line', 'productivity', '{"fields": [{"key": "max_fields", "label": "Số trường tối đa mỗi module", "type": "number", "default": 20}, {"key": "modules", "label": "Áp dụng cho module", "type": "textarea", "placeholder": "contacts, deals, orders"}]}');
