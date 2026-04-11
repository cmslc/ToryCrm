-- Package 6 modules as plugins
INSERT INTO `plugins` (`name`, `slug`, `description`, `category`, `icon`, `version`, `author`, `is_active`, `config`) VALUES
('Kho hàng', 'warehouse', 'Quản lý kho: xuất nhập kho, kiểm kho, tồn kho, báo cáo, cài đặt liên kết đơn hàng', 'Kho vận', 'ri-store-2-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Gamification', 'gamification', 'Bảng xếp hạng nhân viên, huy hiệu thành tích, điểm thưởng', 'Nhân sự', 'ri-trophy-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Đặt lịch hẹn', 'booking', 'Tạo link đặt lịch hẹn công khai, khách hàng tự chọn slot', 'CRM', 'ri-calendar-check-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Check-in GPS', 'checkin', 'Nhân viên check-in vị trí GPS khi gặp khách hàng, bản đồ theo dõi', 'CRM', 'ri-map-pin-user-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Client Portal', 'client-portal', 'Trang tự phục vụ cho khách hàng: xem đơn hàng, gửi ticket, theo dõi tiến độ', 'CRM', 'ri-user-star-line', '1.0.0', 'ToryCRM', 1, '{}'),
('Chính sách SLA', 'sla', 'Thiết lập SLA cho ticket hỗ trợ: thời gian phản hồi, escalation tự động', 'Hỗ trợ', 'ri-timer-line', '1.0.0', 'ToryCRM', 1, '{}');
