-- ToryCRM Extra Demo Data - Bổ sung mỗi bảng ~15 records
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USERS thêm 9 (tổng ~15)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `department`, `is_active`, `last_login`) VALUES
('Vũ Thị Thanh', 'thanh.vt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0956123456', 'staff', 'Kinh doanh', 1, NOW()),
('Đỗ Quang Hải', 'hai.dq@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0967234567', 'staff', 'Kinh doanh', 1, NOW()),
('Nguyễn Thị Lan', 'lan.nt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0978345678', 'staff', 'Marketing', 1, NULL),
('Trương Minh Đức', 'duc.tm@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0989456789', 'manager', 'Tài chính', 1, NOW()),
('Lâm Thị Ngọc', 'ngoc.lt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0990567890', 'staff', 'Hỗ trợ', 1, NULL),
('Cao Văn Phúc', 'phuc.cv@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0911678901', 'staff', 'Kỹ thuật', 1, NOW()),
('Tạ Thị Yến', 'yen.tt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0922789012', 'staff', 'Kinh doanh', 0, NULL),
('Dương Văn Khoa', 'khoa.dv@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0933890123', 'staff', 'Marketing', 1, NOW()),
('Châu Thị Mỹ', 'my.ct@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0944901234', 'admin', 'Ban Giám đốc', 1, NOW());

-- ============================================================
-- COMPANIES thêm 7 (tổng ~15)
-- ============================================================
INSERT INTO `companies` (`name`, `email`, `phone`, `website`, `address`, `city`, `tax_code`, `industry`, `company_size`, `description`, `owner_id`, `created_by`) VALUES
('Công ty Du lịch Việt Hành', 'info@viethanh.travel', '02867890123', 'https://viethanh.travel', '22 Pasteur, Quận 1', 'Hồ Chí Minh', '0316789012', 'Du lịch', '20-50', 'Công ty du lịch lữ hành quốc tế', 2, 1),
('Trường ĐH Công nghệ Sài Gòn', 'contact@stu.edu.vn', '02878901234', 'https://stu.edu.vn', '180 Cao Lỗ, Quận 8', 'Hồ Chí Minh', '0317890123', 'Giáo dục', '500+', 'Đại học tư thục top 10', 3, 1),
('Công ty Bất động sản An Phú', 'sales@anphu.land', '02889012345', 'https://anphu.land', '99 Nguyễn Văn Trỗi', 'Hồ Chí Minh', '0318901234', 'Bất động sản', '50-100', 'Phát triển dự án căn hộ cao cấp', 2, 1),
('Nhà hàng Phở 24', 'mgmt@pho24.vn', '02890123456', NULL, '5 Nguyễn Thiệp, Quận 1', 'Hồ Chí Minh', '0319012345', 'F&B', '100-500', 'Chuỗi nhà hàng Phở nổi tiếng', 3, 1),
('Công ty Nội thất Hòa Phát', 'info@hoaphat-furniture.vn', '02401234567', 'https://hoaphat-furniture.vn', 'KCN Phú Nghĩa', 'Hà Nội', '0100123456', 'Nội thất', '500+', 'Nội thất văn phòng và gia đình', 2, 1),
('Startup Fintech VNPay+', 'hello@vnpayplus.vn', '0971234567', 'https://vnpayplus.vn', '15 Thái Hà', 'Hà Nội', '0108765432', 'Fintech', '10-20', 'Ứng dụng thanh toán di động', 3, 1),
('Công ty Môi trường Xanh', 'contact@moitruongxanh.vn', '02861234567', NULL, '200 Cộng Hòa, Tân Bình', 'Hồ Chí Minh', '0315432198', 'Môi trường', '20-50', 'Xử lý rác thải và tái chế', 4, 1);

-- ============================================================
-- DEALS thêm 5 (tổng ~15)
-- ============================================================
INSERT INTO `deals` (`title`, `value`, `stage_id`, `contact_id`, `company_id`, `owner_id`, `description`, `expected_close_date`, `actual_close_date`, `status`, `priority`, `created_by`) VALUES
('Du lịch Việt Hành - CRM booking', 90000000, 2, NULL, 9, 2, 'Module CRM tích hợp booking tour', '2026-05-15', NULL, 'open', 'medium', 1),
('ĐH Công nghệ SG - Quản lý SV', 180000000, 1, NULL, 10, 3, 'CRM quản lý tuyển sinh & sinh viên', '2026-06-30', NULL, 'open', 'high', 1),
('An Phú Land - CRM Sales', 250000000, 3, NULL, 11, 2, 'CRM cho team bán hàng bất động sản', '2026-04-20', NULL, 'open', 'high', 1),
('VNPay+ - Module API', 60000000, 5, NULL, 14, 3, 'Tích hợp CRM qua API cho Fintech', '2026-04-10', NULL, 'open', 'medium', 1),
('Hòa Phát - Gói Enterprise', 400000000, 2, NULL, 13, 2, 'Triển khai CRM quy mô lớn', '2026-07-01', NULL, 'open', 'urgent', 1);

-- ============================================================
-- TASKS thêm 5 (tổng ~15)
-- ============================================================
INSERT INTO `tasks` (`title`, `description`, `status`, `priority`, `due_date`, `completed_at`, `contact_id`, `deal_id`, `assigned_to`, `created_by`) VALUES
('Khảo sát yêu cầu Hòa Phát', 'Đi khảo sát quy trình sales tại Hòa Phát', 'todo', 'urgent', '2026-03-26 09:00:00', NULL, NULL, 15, 2, 1),
('Cập nhật tài liệu API v2', 'Viết lại docs API cho module mới', 'in_progress', 'medium', '2026-03-30 17:00:00', NULL, NULL, NULL, 6, 1),
('Test module campaign', 'Test end-to-end module chiến dịch email', 'review', 'high', '2026-03-25 17:00:00', NULL, NULL, NULL, 6, 1),
('Gửi hóa đơn cho ABC', 'Xuất hóa đơn GTGT cho đơn DH2603002', 'todo', 'medium', '2026-03-28 12:00:00', NULL, 1, 1, 3, 1),
('Backup database hàng tuần', 'Chạy backup MySQL production', 'done', 'high', '2026-03-20 06:00:00', '2026-03-20 06:05:00', NULL, NULL, 6, 1);

-- ============================================================
-- PRODUCTS thêm 4 (tổng ~15)
-- ============================================================
INSERT INTO `products` (`name`, `sku`, `category_id`, `type`, `unit`, `price`, `cost_price`, `tax_rate`, `stock_quantity`, `min_stock`, `description`, `is_active`, `created_by`) VALUES
('Module Tổng đài IP', 'MOD-CALL', 1, 'service', 'Tháng', 1200000, 0, 10, 0, 0, 'Tích hợp tổng đài VoIP với CRM', 1, 1),
('Module Quản lý kho', 'MOD-WH', 1, 'service', 'Tháng', 900000, 0, 10, 0, 0, 'Quản lý xuất nhập kho', 1, 1),
('Bàn phím cơ Logitech', 'HW-KB-01', 3, 'product', 'Cái', 2500000, 1800000, 10, 20, 5, 'Logitech G Pro X TKL', 1, 1),
('Chuột không dây Logitech', 'HW-MS-01', 3, 'product', 'Cái', 1500000, 1000000, 10, 25, 5, 'Logitech MX Master 3S', 1, 1);

-- ============================================================
-- ORDERS thêm 8 (tổng ~15)
-- ============================================================
INSERT INTO `orders` (`order_number`, `type`, `status`, `contact_id`, `company_id`, `deal_id`, `subtotal`, `tax_amount`, `discount_amount`, `discount_type`, `total`, `notes`, `payment_status`, `payment_method`, `paid_amount`, `due_date`, `issued_date`, `owner_id`, `created_by`) VALUES
('DH2603005', 'order', 'completed', 14, 4, 3, 18000000, 1800000, 0, 'fixed', 19800000, 'Gia hạn gói Starter 12 tháng TechViet', 'paid', 'bank_transfer', 19800000, '2026-04-15', '2026-03-14', 3, 1),
('DH2603006', 'order', 'draft', NULL, 9, 11, 25000000, 2500000, 0, 'fixed', 27500000, 'Gói triển khai Du lịch Việt Hành', 'unpaid', NULL, 0, NULL, '2026-03-19', 2, 1),
('BG2603003', 'quote', 'sent', NULL, 13, 15, 400000000, 40000000, 20000000, 'fixed', 420000000, 'Báo giá Enterprise cho Hòa Phát', 'unpaid', NULL, 0, '2026-07-01', '2026-03-18', 2, 1),
('BG2603004', 'quote', 'confirmed', NULL, 10, 12, 180000000, 18000000, 0, 'fixed', 198000000, 'Báo giá CRM tuyển sinh ĐH CNSG', 'unpaid', NULL, 0, '2026-06-30', '2026-03-15', 3, 1),
('DH2603007', 'order', 'processing', 13, 3, 5, 80000000, 8000000, 0, 'fixed', 88000000, 'Tư vấn triển khai Minh Phát', 'partial', 'bank_transfer', 44000000, '2026-05-15', '2026-03-17', 3, 1),
('DH2603008', 'order', 'confirmed', NULL, 11, 13, 250000000, 25000000, 10000000, 'fixed', 265000000, 'CRM Sales cho An Phú Land', 'unpaid', NULL, 0, '2026-05-20', '2026-03-20', 2, 1),
('BG2603005', 'quote', 'draft', 6, 6, 6, 350000000, 35000000, 0, 'fixed', 385000000, 'Báo giá ERP tích hợp Thành Đạt', 'unpaid', NULL, 0, NULL, '2026-03-20', 3, 1),
('DH2603009', 'order', 'completed', NULL, 14, 14, 60000000, 6000000, 5000000, 'fixed', 61000000, 'API integration VNPay+', 'paid', 'bank_transfer', 61000000, '2026-04-10', '2026-03-12', 3, 1);

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `sort_order`) VALUES
(7, 1, 'ToryCRM - Gói Starter', 12, 'Tháng', 500000, 10, 600000, 6600000, 0),
(7, 7, 'Module Email Marketing', 12, 'Tháng', 800000, 10, 960000, 10560000, 1),
(8, 11, 'Gói CRM + Triển khai + Đào tạo', 1, 'Gói', 25000000, 10, 2500000, 27500000, 0),
(9, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(10, 2, 'ToryCRM - Gói Professional', 12, 'Tháng', 1500000, 10, 1800000, 19800000, 0),
(11, 6, 'Dịch vụ tư vấn', 20, 'Giờ', 1000000, 10, 2000000, 22000000, 0),
(11, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(12, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(12, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(12, 12, 'Module Tổng đài IP', 12, 'Tháng', 1200000, 10, 1440000, 15840000, 2),
(13, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(14, 2, 'ToryCRM - Gói Professional', 6, 'Tháng', 1500000, 10, 900000, 9900000, 0),
(14, 6, 'Dịch vụ tư vấn', 15, 'Giờ', 1000000, 10, 1500000, 16500000, 1);

-- ============================================================
-- CALENDAR EVENTS thêm 7 (tổng ~15)
-- ============================================================
INSERT INTO `calendar_events` (`title`, `description`, `type`, `color`, `start_at`, `end_at`, `all_day`, `location`, `contact_id`, `company_id`, `deal_id`, `user_id`, `created_by`) VALUES
('Demo CRM tuyển sinh cho ĐH CNSG', 'Demo module CRM quản lý tuyển sinh', 'meeting', '#405189', '2026-03-28 14:00:00', '2026-03-28 16:00:00', 0, '180 Cao Lỗ, Q8', NULL, 10, 12, 3, 1),
('Gọi An Phú Land - xác nhận HĐ', 'Xác nhận hợp đồng và lịch triển khai', 'call', '#0ab39c', '2026-03-26 10:00:00', '2026-03-26 10:30:00', 0, NULL, NULL, 11, 13, 2, 1),
('Khảo sát nhà máy Hòa Phát', 'Khảo sát quy trình và hệ thống IT hiện tại', 'visit', '#f06548', '2026-03-29 08:00:00', '2026-03-29 17:00:00', 1, 'KCN Phú Nghĩa, Hà Nội', NULL, 13, 15, 2, 1),
('Webinar CRM cho SME', 'Tổ chức online webinar giới thiệu CRM', 'meeting', '#405189', '2026-04-10 14:00:00', '2026-04-10 16:00:00', 0, 'Online - Zoom', NULL, NULL, NULL, 4, 1),
('Review sprint 2 tuần', 'Sprint review team development', 'meeting', '#405189', '2026-03-28 09:00:00', '2026-03-28 10:00:00', 0, 'Phòng họp B', NULL, NULL, NULL, 6, 1),
('Nhắc: Gia hạn SSL server', 'SSL certificate hết hạn ngày 05/04', 'reminder', '#ffbe0b', '2026-04-01 09:00:00', NULL, 0, NULL, NULL, NULL, NULL, 6, 1),
('Team building Q1', 'Team building kết thúc quý 1', 'other', '#299cdb', '2026-04-05 07:00:00', '2026-04-05 18:00:00', 1, 'Vũng Tàu', NULL, NULL, NULL, 2, 1);

-- ============================================================
-- TICKETS thêm 10 (tổng ~15)
-- ============================================================
INSERT INTO `tickets` (`ticket_code`, `title`, `content`, `category_id`, `contact_id`, `company_id`, `priority`, `status`, `assigned_to`, `contact_phone`, `contact_email`, `due_date`, `resolved_at`, `closed_at`, `created_by`) VALUES
('TK2603006', 'Không gửi được email từ CRM', 'Khi gửi email cho KH từ module CRM, hệ thống báo lỗi SMTP timeout. Đã thử nhiều lần.', 1, 14, 4, 'high', 'in_progress', 6, '0944567890', 'quynh.ht@techviet.io', '2026-03-24 17:00:00', NULL, NULL, 4),
('TK2603007', 'Thêm trường tùy chỉnh cho Contact', 'Cần thêm trường "Ngày sinh" và "CMND/CCCD" vào form khách hàng. Hiện chưa có.', 3, 1, 1, 'medium', 'open', 6, '0901111111', 'an.nv@abc-soft.vn', '2026-04-05 17:00:00', NULL, NULL, 2),
('TK2603008', 'Lỗi hiển thị biểu đồ Dashboard', 'Biểu đồ doanh thu không hiển thị dữ liệu tháng 2 và tháng 3. Các tháng khác bình thường.', 1, 5, 5, 'medium', 'resolved', 6, '0945555555', 'em.hm@globallog.vn', '2026-03-20 17:00:00', '2026-03-20 15:30:00', NULL, 2),
('TK2603009', 'Yêu cầu tích hợp Zalo OA', 'Cần tích hợp gửi tin nhắn Zalo OA từ CRM khi có deal mới hoặc ticket mới.', 3, 2, 2, 'low', 'open', 6, '0912222222', 'binh.tt@xyz.vn', '2026-04-15 17:00:00', NULL, NULL, 2),
('TK2603010', 'Khiếu nại: Dữ liệu bị mất', 'Sau khi cập nhật hệ thống, 5 contact trong danh sách bị mất thông tin số điện thoại.', 2, 10, 1, 'urgent', 'in_progress', 6, '0990000000', 'linh.nt@abc-soft.vn', '2026-03-21 10:00:00', NULL, NULL, 2),
('TK2603011', 'Hỏi cách export báo cáo PDF', 'Làm thế nào để xuất báo cáo pipeline ra file PDF để gửi cho sếp?', 4, 11, 2, 'low', 'resolved', 4, '0911234567', 'nhat.lm@xyz.vn', '2026-03-22 17:00:00', '2026-03-21 11:00:00', NULL, 2),
('TK2603012', 'Bảo hành module Email Marketing', 'Module email marketing gửi email bị vào spam. Cần kiểm tra cấu hình DKIM/SPF.', 5, 4, 4, 'high', 'waiting', 6, '0934444444', 'dung.pt@techviet.io', '2026-03-25 17:00:00', NULL, NULL, 4),
('TK2603013', 'Tốc độ search contact chậm', 'Khi tìm kiếm trong 5000+ contacts, kết quả trả về mất 3-5 giây. Cần optimize.', 1, 1, 1, 'medium', 'open', 6, '0901111111', 'an.nv@abc-soft.vn', '2026-03-30 17:00:00', NULL, NULL, 2),
('TK2603014', 'Yêu cầu: Auto-assign ticket', 'Cần tính năng tự động phân ticket cho nhân viên hỗ trợ theo round-robin.', 3, 14, 4, 'low', 'open', 6, '0944567890', 'quynh.ht@techviet.io', '2026-04-20 17:00:00', NULL, NULL, 4),
('TK2603015', 'Lỗi: Không xóa được deal đã đóng', 'Khi cố xóa deal có status=won, hệ thống báo lỗi 500. Các deal open xóa bình thường.', 1, 5, 5, 'high', 'resolved', 6, '0945555555', 'em.hm@globallog.vn', '2026-03-22 12:00:00', '2026-03-22 10:00:00', '2026-03-22 10:30:00', 2);

-- Extra ticket comments
INSERT INTO `ticket_comments` (`ticket_id`, `content`, `is_internal`, `user_id`) VALUES
(6, 'Đã kiểm tra, SMTP server đang bị block port 587. Đang liên hệ hosting.', 1, 6),
(8, 'Fix xong. Lỗi do query thiếu điều kiện YEAR cho dữ liệu mới.', 0, 6),
(10, 'Đã restore lại dữ liệu từ backup. 5 contacts đã được phục hồi.', 0, 6),
(10, 'Nguyên nhân: migration script bị lỗi truncate field phone. Đã fix.', 1, 6),
(11, 'Hiện tại chưa có tính năng export PDF trực tiếp. Bạn có thể dùng Print > Save as PDF từ trình duyệt.', 0, 4),
(12, 'Đã kiểm tra DNS. Thiếu record DKIM. Đã gửi hướng dẫn cho KH cấu hình.', 0, 6),
(15, 'Bug do foreign key constraint. Đã fix bằng cách xóa cascade order_items trước.', 1, 6);

-- ============================================================
-- CAMPAIGNS thêm 10 (tổng ~15)
-- ============================================================
INSERT INTO `campaigns` (`campaign_code`, `name`, `type`, `status`, `description`, `start_date`, `end_date`, `budget`, `actual_cost`, `target_count`, `reached_count`, `converted_count`, `owner_id`, `created_by`) VALUES
('CD260006', 'Email chúc mừng sinh nhật KH', 'email', 'running', 'Gửi email chúc mừng sinh nhật tự động kèm voucher giảm 10%', '2026-01-01', '2026-12-31', 1000000, 300000, 50, 35, 5, 4, 1),
('CD260007', 'Remarketing KH cũ Q1', 'email', 'completed', 'Email nhắc nhở KH đã hết hạn gia hạn license CRM', '2026-02-01', '2026-02-28', 2000000, 1800000, 80, 72, 12, 4, 1),
('CD260008', 'Google Ads - CRM Vietnam', 'other', 'running', 'Quảng cáo Google tìm kiếm từ khóa "phần mềm CRM Việt Nam"', '2026-03-01', '2026-04-30', 15000000, 8000000, 1000, 450, 30, 4, 1),
('CD260009', 'Khảo sát hài lòng KH 2026', 'email', 'draft', 'Gửi form khảo sát mức độ hài lòng cho KH đang sử dụng', '2026-04-01', '2026-04-15', 500000, 0, 100, 0, 0, 3, 1),
('CD260010', 'Giới thiệu module Ticket', 'email', 'draft', 'Email giới thiệu tính năng Ticket/Helpdesk mới ra mắt', '2026-04-15', '2026-04-30', 1500000, 0, 150, 0, 0, 4, 1),
('CD260011', 'SMS nhắc lịch hẹn', 'sms', 'running', 'SMS tự động nhắc lịch hẹn trước 1 ngày', '2026-03-01', '2026-06-30', 2000000, 800000, 200, 120, 0, 4, 1),
('CD260012', 'Referral Program', 'other', 'running', 'Chương trình giới thiệu KH mới - thưởng 10% hoa hồng', '2026-02-01', '2026-06-30', 5000000, 2000000, 50, 20, 8, 2, 1),
('CD260013', 'TikTok Ads tháng 4', 'social', 'draft', 'Video ngắn giới thiệu CRM trên TikTok cho đối tượng trẻ', '2026-04-01', '2026-04-30', 8000000, 0, 300, 0, 0, 4, 1),
('CD260014', 'Telesales gói Enterprise', 'call', 'running', 'Gọi điện tư vấn gói Enterprise cho doanh nghiệp >100 NV', '2026-03-15', '2026-04-15', 3000000, 1500000, 40, 18, 2, 2, 1),
('CD260015', 'Email Black Friday 2026', 'email', 'cancelled', 'Đã hủy do trùng với chương trình khác', '2026-11-25', '2026-11-30', 4000000, 0, 500, 0, 0, 4, 1);

-- Extra campaign contacts
INSERT INTO `campaign_contacts` (`campaign_id`, `contact_id`, `status`, `sent_at`, `opened_at`, `clicked_at`, `converted_at`) VALUES
(6, 1, 'opened', '2026-03-10 08:00:00', '2026-03-10 09:00:00', NULL, NULL),
(6, 4, 'converted', '2026-02-15 08:00:00', '2026-02-15 10:00:00', '2026-02-15 10:05:00', '2026-02-16 14:00:00'),
(7, 9, 'sent', '2026-02-05 09:00:00', NULL, NULL, NULL),
(7, 6, 'opened', '2026-02-05 09:00:00', '2026-02-06 11:00:00', NULL, NULL),
(8, 3, 'clicked', '2026-03-10 07:00:00', '2026-03-10 12:00:00', '2026-03-10 12:10:00', NULL),
(8, 12, 'converted', '2026-03-08 07:00:00', '2026-03-08 09:30:00', '2026-03-08 09:35:00', '2026-03-09 15:00:00'),
(11, 1, 'sent', '2026-03-21 07:00:00', NULL, NULL, NULL),
(11, 2, 'sent', '2026-03-22 07:00:00', NULL, NULL, NULL),
(14, 5, 'pending', NULL, NULL, NULL, NULL),
(14, 6, 'sent', '2026-03-18 10:00:00', NULL, NULL, NULL);

-- ============================================================
-- PURCHASE ORDERS thêm 12 (tổng ~15)
-- ============================================================
INSERT INTO `purchase_orders` (`order_code`, `supplier_id`, `status`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `notes`, `payment_status`, `paid_amount`, `expected_date`, `approved_by`, `approved_at`, `owner_id`, `created_by`) VALUES
('PO2603004', 14, 'completed', 20000000, 2000000, 0, 22000000, 'Mua API license cho tích hợp thanh toán', 'paid', 22000000, '2026-03-20', 1, '2026-03-18 10:00:00', 3, 1),
('PO2603005', 7, 'approved', 8000000, 800000, 0, 8800000, 'Thiết kế banner quảng cáo Facebook Q1', 'partial', 4400000, '2026-03-25', 1, '2026-03-20 09:00:00', 4, 1),
('PO2603006', 13, 'pending', 50000000, 5000000, 2500000, 52500000, 'Mua bàn ghế văn phòng cho phòng mới', 'unpaid', 0, '2026-04-05', NULL, NULL, 6, 1),
('PO2603007', 9, 'draft', 35000000, 3500000, 0, 38500000, 'Mua thiết bị mạng: switch, router', 'unpaid', 0, '2026-04-10', NULL, NULL, 6, 1),
('PO2603008', 6, 'completed', 160000000, 16000000, 8000000, 168000000, 'Mua 8 laptop cho team dev', 'paid', 168000000, '2026-02-28', 1, '2026-02-25 14:00:00', 6, 1),
('PO2603009', 10, 'approved', 12000000, 1200000, 0, 13200000, 'Mua tài liệu đào tạo từ ĐH CNSG', 'unpaid', 0, '2026-03-30', 1, '2026-03-19 11:00:00', 3, 1),
('PO2603010', 12, 'cancelled', 25000000, 2500000, 0, 27500000, 'Đã hủy - đổi nhà cung cấp khác', 'unpaid', 0, '2026-03-15', NULL, NULL, 4, 1),
('PO2603011', 11, 'receiving', 30000000, 3000000, 0, 33000000, 'Mua quà tặng KH dịp khai trương VP mới', 'partial', 16500000, '2026-03-22', 1, '2026-03-19 15:00:00', 2, 1),
('PO2603012', 6, 'completed', 48000000, 4800000, 0, 52800000, 'Mua 6 màn hình cho team support', 'paid', 52800000, '2026-03-10', 1, '2026-03-08 09:00:00', 6, 1),
('PO2603013', 14, 'draft', 15000000, 1500000, 0, 16500000, 'Mua SSL certificates cho 5 domains', 'unpaid', 0, '2026-04-01', NULL, NULL, 6, 1),
('PO2603014', 7, 'pending', 18000000, 1800000, 0, 19800000, 'Dịch vụ chụp ảnh sản phẩm', 'unpaid', 0, '2026-04-10', NULL, NULL, 4, 1),
('PO2603015', 15, 'approved', 5000000, 500000, 0, 5500000, 'Dịch vụ dọn vệ sinh VP hàng tháng', 'paid', 5500000, '2026-03-31', 1, '2026-03-19 08:00:00', 6, 1);

INSERT INTO `purchase_order_items` (`purchase_order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `received_quantity`, `sort_order`) VALUES
(4, NULL, 'API License - Payment Gateway', 1, 'License', 20000000, 10, 2000000, 22000000, 1, 0),
(5, NULL, 'Banner Facebook 1200x628', 5, 'Cái', 1600000, 10, 800000, 8800000, 0, 0),
(6, NULL, 'Bàn làm việc 140x70', 10, 'Cái', 3500000, 10, 3500000, 38500000, 0, 0),
(6, NULL, 'Ghế xoay văn phòng', 10, 'Cái', 1500000, 10, 1500000, 16500000, 0, 1),
(7, NULL, 'Cisco Switch 24-port', 2, 'Cái', 12000000, 10, 2400000, 26400000, 0, 0),
(7, NULL, 'Wifi Router Ubiquiti', 3, 'Cái', 3500000, 10, 1050000, 11550000, 0, 1),
(8, 9, 'Máy tính xách tay Dell', 8, 'Cái', 20000000, 10, 16000000, 176000000, 8, 0),
(9, NULL, 'Bộ tài liệu đào tạo IT', 20, 'Bộ', 600000, 10, 1200000, 13200000, 0, 0),
(11, NULL, 'Gift box cao cấp', 50, 'Hộp', 600000, 10, 3000000, 33000000, 30, 0),
(12, 10, 'Màn hình Dell 27 inch', 6, 'Cái', 8000000, 10, 4800000, 52800000, 6, 0),
(13, NULL, 'SSL Certificate Wildcard', 5, 'Domain', 3000000, 10, 1500000, 16500000, 0, 0),
(14, NULL, 'Gói chụp ảnh sản phẩm 50 SP', 1, 'Gói', 18000000, 10, 1800000, 19800000, 0, 0),
(15, NULL, 'Dịch vụ vệ sinh VP tháng 3', 1, 'Tháng', 5000000, 10, 500000, 5500000, 1, 0);

-- ============================================================
-- FUND TRANSACTIONS thêm 7 (tổng ~15)
-- ============================================================
INSERT INTO `fund_transactions` (`transaction_code`, `type`, `fund_account_id`, `amount`, `category`, `description`, `contact_id`, `company_id`, `transaction_date`, `status`, `confirmed_by`, `confirmed_at`, `created_by`) VALUES
('PT2603005', 'receipt', 2, 19800000, 'Thu tiền bán hàng', 'Thu tiền gia hạn TechViet - DH2603005', 14, 4, '2026-03-14', 'confirmed', 1, '2026-03-14 14:00:00', 1),
('PT2603006', 'receipt', 2, 44000000, 'Thu tiền bán hàng', 'Thu đợt 1 tư vấn Minh Phát - DH2603007', 13, 3, '2026-03-18', 'confirmed', 1, '2026-03-18 10:00:00', 1),
('PT2603007', 'receipt', 2, 61000000, 'Thu tiền bán hàng', 'Thu API VNPay+ - DH2603009', NULL, 14, '2026-03-15', 'confirmed', 1, '2026-03-15 11:00:00', 1),
('PC2603005', 'payment', 2, 22000000, 'Chi mua hàng', 'Thanh toán API license - PO2603004', NULL, 14, '2026-03-20', 'confirmed', 1, '2026-03-20 10:00:00', 1),
('PC2603006', 'payment', 2, 168000000, 'Chi mua hàng', 'Thanh toán laptop team dev - PO2603008', NULL, 6, '2026-02-28', 'confirmed', 1, '2026-02-28 16:00:00', 1),
('PC2603007', 'payment', 1, 1500000, 'Chi tiếp khách', 'Chi phí tiếp khách Hòa Phát', NULL, 13, '2026-03-18', 'confirmed', 1, '2026-03-18 17:00:00', 1),
('PT2603008', 'receipt', 1, 2000000, 'Thu tiền mặt', 'Thu tiền mặt bán phụ kiện lẻ', NULL, NULL, '2026-03-19', 'draft', NULL, NULL, 2);

-- ============================================================
-- NOTIFICATIONS thêm 6 (tổng ~15)
-- ============================================================
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `icon`, `is_read`) VALUES
(2, 'calendar', 'Lịch hẹn ngày mai', 'Demo CRM cho ABC Software lúc 9:00 ngày 22/03', 'calendar/1', 'ri-calendar-event-line', 0),
(3, 'order', 'Đơn hàng hoàn thành', 'DH2603009 - API VNPay+ đã hoàn thành và thanh toán đủ', 'orders/14', 'ri-file-list-3-line', 0),
(4, 'info', 'Campaign đạt mục tiêu', 'CD260004 Facebook Ads đã đạt 300/500 reached', 'campaigns/4', 'ri-megaphone-line', 1),
(6, 'danger', 'Ticket khẩn: Dữ liệu mất', 'TK2603010 - KH phản ánh mất dữ liệu contact, cần xử lý gấp', 'tickets/10', 'ri-alarm-warning-line', 0),
(1, 'success', 'Doanh thu tháng 3 tăng', 'Doanh thu tháng 3 đạt 309,300,000đ, tăng 45% so với tháng 2', NULL, 'ri-line-chart-line', 0),
(2, 'warning', 'Còn 3 deal sắp hết hạn', '3 deals có expected close date trong tuần này', 'deals', 'ri-error-warning-line', 0);

SET FOREIGN_KEY_CHECKS = 1;
