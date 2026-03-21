-- ToryCRM Demo Data
-- Dữ liệu mẫu cho tất cả các module

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USERS (Thêm users demo)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `department`, `is_active`, `last_login`) VALUES
('Nguyễn Văn Hùng', 'hung.nv@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 'manager', 'Kinh doanh', 1, NOW()),
('Trần Thị Mai', 'mai.tt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'staff', 'Kinh doanh', 1, NOW()),
('Lê Minh Tuấn', 'tuan.lm@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'staff', 'Marketing', 1, NOW()),
('Phạm Thị Hương', 'huong.pt@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'staff', 'Hỗ trợ', 1, NULL),
('Hoàng Đức Anh', 'anh.hd@torycrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0945678901', 'manager', 'Kỹ thuật', 1, NOW());
-- Password cho tất cả: password

-- ============================================================
-- COMPANIES (Doanh nghiệp)
-- ============================================================
INSERT INTO `companies` (`name`, `email`, `phone`, `website`, `address`, `city`, `tax_code`, `industry`, `company_size`, `description`, `owner_id`, `created_by`) VALUES
('Công ty TNHH Phần mềm ABC', 'info@abc-soft.vn', '02812345678', 'https://abc-soft.vn', '123 Nguyễn Huệ, Quận 1', 'Hồ Chí Minh', '0312345678', 'Công nghệ', '50-100', 'Công ty phần mềm hàng đầu', 2, 1),
('Tập đoàn XYZ Việt Nam', 'contact@xyz.vn', '02887654321', 'https://xyz.vn', '456 Lê Lợi, Quận 3', 'Hồ Chí Minh', '0398765432', 'Thương mại', '100-500', 'Tập đoàn thương mại đa ngành', 2, 1),
('Công ty CP Đầu tư Minh Phát', 'info@minhphat.com', '02456789012', 'https://minhphat.com', '789 Trần Hưng Đạo', 'Hà Nội', '0109876543', 'Tài chính', '20-50', 'Đầu tư và phát triển bất động sản', 3, 1),
('Startup TechViet', 'hello@techviet.io', '0967891234', NULL, '12 Hoàng Diệu, Quận 4', 'Hồ Chí Minh', '0315678901', 'Công nghệ', '10-20', 'Startup AI và Machine Learning', 3, 1),
('Công ty Logistics Toàn Cầu', 'sales@globallog.vn', '02834567890', 'https://globallog.vn', '321 Võ Văn Tần, Quận 3', 'Hồ Chí Minh', '0317654321', 'Vận tải', '200-500', 'Dịch vụ logistics quốc tế', 2, 1),
('Nhà máy Sản xuất Thành Đạt', 'factory@thanhdat.vn', '02741234567', 'https://thanhdat.vn', 'KCN Bình Dương', 'Bình Dương', '3702345678', 'Sản xuất', '500+', 'Sản xuất linh kiện điện tử', 3, 1),
('Công ty Truyền thông Sáng Tạo', 'creative@sangta.vn', '0981234567', NULL, '56 Nguyễn Thị Minh Khai', 'Hồ Chí Minh', '0319876543', 'Truyền thông', '10-20', 'Agency quảng cáo và truyền thông', 4, 1),
('Bệnh viện Đa khoa Hòa Bình', 'info@bvhoabinh.vn', '02891234567', 'https://bvhoabinh.vn', '100 Điện Biên Phủ', 'Hồ Chí Minh', '0318765432', 'Y tế', '100-500', 'Bệnh viện tư nhân chất lượng cao', 2, 1);

-- ============================================================
-- CONTACTS (Khách hàng)
-- ============================================================
INSERT INTO `contacts` (`first_name`, `last_name`, `email`, `phone`, `mobile`, `position`, `company_id`, `source_id`, `address`, `city`, `gender`, `status`, `score`, `owner_id`, `created_by`, `description`) VALUES
('Nguyễn', 'Văn An', 'an.nv@abc-soft.vn', '0901111111', '0901111111', 'Giám đốc', 1, 1, '123 Nguyễn Huệ', 'Hồ Chí Minh', 'male', 'qualified', 85, 2, 1, 'KH tiềm năng cao, quan tâm giải pháp CRM'),
('Trần', 'Thị Bình', 'binh.tt@xyz.vn', '0912222222', '0912222222', 'Trưởng phòng IT', 2, 2, '456 Lê Lợi', 'Hồ Chí Minh', 'female', 'contacted', 60, 2, 1, 'Đã demo sản phẩm, đang cân nhắc'),
('Lê', 'Hoàng Cường', 'cuong.lh@minhphat.com', '0923333333', '0923333333', 'Phó GĐ', 3, 3, '789 Trần Hưng Đạo', 'Hà Nội', 'male', 'new', 30, 3, 1, 'Liên hệ qua hotline'),
('Phạm', 'Thị Dung', 'dung.pt@techviet.io', '0934444444', '0934444444', 'CEO', 4, 1, '12 Hoàng Diệu', 'Hồ Chí Minh', 'female', 'converted', 95, 3, 1, 'Đã ký hợp đồng 12 tháng'),
('Hoàng', 'Minh Em', 'em.hm@globallog.vn', '0945555555', '0945555555', 'Giám đốc KD', 5, 4, '321 Võ Văn Tần', 'Hồ Chí Minh', 'male', 'qualified', 70, 2, 1, 'Cần giải pháp quản lý đơn hàng'),
('Võ', 'Thị Phượng', 'phuong.vt@gmail.com', '0956666666', '0956666666', 'Kế toán trưởng', 6, 5, 'KCN Bình Dương', 'Bình Dương', 'female', 'contacted', 45, 3, 1, 'Quan tâm module quỹ thu chi'),
('Đặng', 'Quốc Giang', 'giang.dq@sangta.vn', '0967777777', '0967777777', 'Creative Director', 7, 6, '56 NTMK', 'Hồ Chí Minh', 'male', 'new', 20, 4, 1, 'Đăng ký qua Facebook'),
('Bùi', 'Thị Hạnh', 'hanh.bt@bvhoabinh.vn', '0978888888', '0978888888', 'Trưởng phòng HC', 8, 7, '100 Điện Biên Phủ', 'Hồ Chí Minh', 'female', 'new', 15, 2, 1, 'Gọi điện hỏi thông tin'),
('Trịnh', 'Văn Kiên', 'kien.tv@gmail.com', '0989999999', NULL, 'Freelancer', NULL, 2, '45 Bạch Đằng', 'Đà Nẵng', 'male', 'lost', 10, 3, 1, 'Ngân sách không phù hợp'),
('Ngô', 'Thị Linh', 'linh.nt@abc-soft.vn', '0990000000', '0990000000', 'Product Manager', 1, 1, '123 Nguyễn Huệ', 'Hồ Chí Minh', 'female', 'qualified', 75, 2, 1, 'Đồng nghiệp anh An, quan tâm module task'),
('Lý', 'Minh Nhật', 'nhat.lm@xyz.vn', '0911234567', '0911234567', 'CFO', 2, 3, '456 Lê Lợi', 'Hồ Chí Minh', 'male', 'contacted', 55, 2, 1, 'Cần báo giá chi tiết'),
('Đinh', 'Thị Oanh', 'oanh.dt@outlook.com', '0922345678', '0922345678', 'Chủ cửa hàng', NULL, 4, '78 Hai Bà Trưng', 'Hà Nội', 'female', 'new', 25, 3, 1, 'Gọi điện tư vấn ban đầu'),
('Phan', 'Quốc Phong', 'phong.pq@minhphat.com', '0933456789', NULL, 'IT Manager', 3, 5, '789 THP', 'Hà Nội', 'male', 'qualified', 80, 3, 1, 'Yêu cầu demo tại chỗ'),
('Hồ', 'Thị Quỳnh', 'quynh.ht@techviet.io', '0944567890', '0944567890', 'CTO', 4, 1, '12 Hoàng Diệu', 'Hồ Chí Minh', 'female', 'converted', 90, 3, 1, 'Đồng sáng lập TechViet'),
('Mai', 'Văn Sơn', 'son.mv@globallog.vn', '0955678901', '0955678901', 'Trưởng kho', 5, 6, '321 Võ Văn Tần', 'Hồ Chí Minh', 'male', 'contacted', 40, 2, 1, 'Quan tâm quản lý kho hàng');

-- ============================================================
-- DEALS (Cơ hội kinh doanh)
-- ============================================================
INSERT INTO `deals` (`title`, `value`, `stage_id`, `contact_id`, `company_id`, `owner_id`, `description`, `expected_close_date`, `actual_close_date`, `status`, `priority`, `created_by`) VALUES
('CRM cho ABC Software', 150000000, 4, 1, 1, 2, 'Triển khai CRM cho toàn bộ team sales', '2026-04-15', NULL, 'open', 'high', 1),
('Gói Enterprise cho XYZ', 500000000, 3, 2, 2, 2, 'Gói enterprise cho tập đoàn XYZ', '2026-05-01', NULL, 'open', 'urgent', 1),
('TechViet - Gói startup', 50000000, 6, 4, 4, 3, 'Gói CRM cho startup', '2026-03-01', '2026-02-28', 'won', 'medium', 1),
('Logistics Toàn Cầu - Module kho', 200000000, 2, 5, 5, 2, 'Module quản lý kho và đơn hàng', '2026-06-01', NULL, 'open', 'high', 1),
('Minh Phát - Tư vấn triển khai', 80000000, 5, 3, 3, 3, 'Tư vấn và triển khai CRM', '2026-04-30', NULL, 'open', 'medium', 1),
('Thành Đạt - ERP tích hợp', 350000000, 1, 6, 6, 3, 'Tích hợp CRM với ERP hiện tại', '2026-07-15', NULL, 'open', 'high', 1),
('Sáng Tạo Agency - Gói SME', 30000000, 7, 7, 7, 4, 'Gói nhỏ cho agency', '2026-02-15', NULL, 'lost', 'low', 1),
('BV Hòa Bình - Quản lý bệnh nhân', 120000000, 3, 8, 8, 2, 'Module CRM cho bệnh viện', '2026-05-30', NULL, 'open', 'medium', 1),
('ABC Software - Module HR', 75000000, 4, 10, 1, 2, 'Thêm module quản lý nhân sự', '2026-04-20', NULL, 'open', 'medium', 1),
('XYZ - Gói đào tạo', 45000000, 6, 11, 2, 2, 'Đào tạo sử dụng CRM', '2026-03-10', '2026-03-08', 'won', 'low', 1);

-- ============================================================
-- TASKS (Công việc)
-- ============================================================
INSERT INTO `tasks` (`title`, `description`, `status`, `priority`, `due_date`, `completed_at`, `contact_id`, `deal_id`, `assigned_to`, `created_by`) VALUES
('Gọi điện xác nhận lịch demo ABC', 'Xác nhận lịch demo CRM với anh An - ABC Software', 'done', 'high', '2026-03-18 10:00:00', '2026-03-18 09:30:00', 1, 1, 2, 1),
('Chuẩn bị proposal cho XYZ', 'Soạn proposal chi tiết gói Enterprise cho tập đoàn XYZ', 'in_progress', 'urgent', '2026-03-22 17:00:00', NULL, 2, 2, 2, 1),
('Follow up Minh Phát', 'Gọi lại anh Cường hỏi phản hồi sau demo', 'todo', 'medium', '2026-03-25 14:00:00', NULL, 3, 5, 3, 1),
('Gửi hợp đồng TechViet', 'Gửi hợp đồng gia hạn cho TechViet', 'done', 'high', '2026-03-15 10:00:00', '2026-03-14 16:00:00', 4, 3, 3, 1),
('Tạo tài liệu hướng dẫn', 'Viết tài liệu hướng dẫn sử dụng cho module mới', 'in_progress', 'medium', '2026-03-28 17:00:00', NULL, NULL, NULL, 4, 1),
('Demo module kho cho GlobalLog', 'Demo trực tiếp tại văn phòng khách hàng', 'todo', 'high', '2026-03-24 09:00:00', NULL, 5, 4, 2, 1),
('Báo giá lại cho Sáng Tạo', 'Điều chỉnh giá và gửi lại báo giá', 'todo', 'low', '2026-03-30 17:00:00', NULL, 7, 7, 4, 1),
('Review code module ticket', 'Review và test module ticket mới phát triển', 'review', 'high', '2026-03-21 17:00:00', NULL, NULL, NULL, 6, 1),
('Họp team sales tuần', 'Họp review kết quả tuần và plan tuần tới', 'todo', 'medium', '2026-03-24 08:30:00', NULL, NULL, NULL, 2, 1),
('Onboarding TechViet', 'Hỗ trợ setup và đào tạo cho TechViet', 'in_progress', 'high', '2026-03-26 10:00:00', NULL, 14, 3, 3, 1);

-- ============================================================
-- PRODUCTS (Sản phẩm & Dịch vụ)
-- ============================================================
INSERT INTO `products` (`name`, `sku`, `category_id`, `type`, `unit`, `price`, `cost_price`, `tax_rate`, `stock_quantity`, `min_stock`, `description`, `is_active`, `created_by`) VALUES
('ToryCRM - Gói Starter', 'CRM-START', 1, 'service', 'Tháng', 500000, 0, 10, 0, 0, 'Gói cơ bản cho doanh nghiệp nhỏ, tối đa 5 users', 1, 1),
('ToryCRM - Gói Professional', 'CRM-PRO', 1, 'service', 'Tháng', 1500000, 0, 10, 0, 0, 'Gói chuyên nghiệp, tối đa 20 users, đầy đủ tính năng', 1, 1),
('ToryCRM - Gói Enterprise', 'CRM-ENT', 1, 'service', 'Tháng', 5000000, 0, 10, 0, 0, 'Gói doanh nghiệp lớn, không giới hạn users, tùy chỉnh', 1, 1),
('Dịch vụ triển khai', 'SVC-DEPLOY', 2, 'service', 'Dự án', 15000000, 5000000, 10, 0, 0, 'Dịch vụ triển khai và cấu hình ban đầu', 1, 1),
('Dịch vụ đào tạo', 'SVC-TRAIN', 2, 'service', 'Buổi', 3000000, 1000000, 10, 0, 0, 'Đào tạo sử dụng CRM (4 giờ/buổi)', 1, 1),
('Dịch vụ tư vấn', 'SVC-CONSULT', 2, 'service', 'Giờ', 1000000, 300000, 10, 0, 0, 'Tư vấn quy trình và tối ưu CRM', 1, 1),
('Module Email Marketing', 'MOD-EMAIL', 1, 'service', 'Tháng', 800000, 0, 10, 0, 0, 'Module gửi email marketing tự động', 1, 1),
('Module SMS Marketing', 'MOD-SMS', 1, 'service', 'Tháng', 600000, 0, 10, 0, 0, 'Module gửi SMS hàng loạt', 1, 1),
('Máy tính xách tay Dell', 'HW-DELL-01', 3, 'product', 'Cái', 25000000, 20000000, 10, 15, 3, 'Dell Latitude 5540 i7 16GB', 1, 1),
('Màn hình Dell 27 inch', 'HW-MON-27', 3, 'product', 'Cái', 8000000, 6000000, 10, 10, 2, 'Dell P2723QE 4K USB-C', 1, 1),
('Gói CRM + Triển khai + Đào tạo', 'PKG-FULL', 4, 'service', 'Gói', 25000000, 8000000, 10, 0, 0, 'Gói trọn bộ: License 1 năm + Triển khai + 2 buổi đào tạo', 1, 1);

-- ============================================================
-- ORDERS (Đơn hàng bán)
-- ============================================================
INSERT INTO `orders` (`order_number`, `type`, `status`, `contact_id`, `company_id`, `deal_id`, `subtotal`, `tax_amount`, `discount_amount`, `discount_type`, `total`, `notes`, `payment_status`, `payment_method`, `paid_amount`, `due_date`, `issued_date`, `owner_id`, `created_by`) VALUES
('DH2603001', 'order', 'completed', 4, 4, 3, 50000000, 5000000, 0, 'fixed', 55000000, 'Đơn hàng TechViet gói startup 12 tháng', 'paid', 'bank_transfer', 55000000, '2026-04-01', '2026-02-28', 3, 1),
('DH2603002', 'order', 'confirmed', 1, 1, 1, 150000000, 15000000, 5000000, 'fixed', 160000000, 'Đơn CRM gói Pro cho ABC Software', 'partial', 'bank_transfer', 80000000, '2026-05-01', '2026-03-15', 2, 1),
('DH2603003', 'order', 'processing', 5, 5, 4, 200000000, 20000000, 10000000, 'fixed', 210000000, 'Module kho + triển khai cho GlobalLog', 'unpaid', NULL, 0, '2026-06-15', '2026-03-18', 2, 1),
('BG2603001', 'quote', 'sent', 2, 2, 2, 500000000, 50000000, 25000000, 'fixed', 525000000, 'Báo giá gói Enterprise cho XYZ', 'unpaid', NULL, 0, '2026-05-15', '2026-03-10', 2, 1),
('BG2603002', 'quote', 'draft', 8, 8, 8, 120000000, 12000000, 0, 'fixed', 132000000, 'Báo giá CRM cho Bệnh viện Hòa Bình', 'unpaid', NULL, 0, NULL, '2026-03-19', 2, 1),
('DH2603004', 'order', 'completed', 11, 2, 10, 45000000, 4500000, 0, 'fixed', 49500000, 'Gói đào tạo cho XYZ', 'paid', 'bank_transfer', 49500000, '2026-03-30', '2026-03-08', 2, 1);

-- Order items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `sort_order`) VALUES
(1, 1, 'ToryCRM - Gói Starter', 12, 'Tháng', 500000, 10, 600000, 6600000, 0),
(1, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(1, 5, 'Dịch vụ đào tạo', 2, 'Buổi', 3000000, 10, 600000, 6600000, 2),
(2, 2, 'ToryCRM - Gói Professional', 12, 'Tháng', 1500000, 10, 1800000, 19800000, 0),
(2, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(2, 7, 'Module Email Marketing', 12, 'Tháng', 800000, 10, 960000, 10560000, 2),
(3, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(3, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 1500000, 16500000, 1),
(3, 6, 'Dịch vụ tư vấn', 10, 'Giờ', 1000000, 10, 1000000, 11000000, 2),
(4, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 6000000, 66000000, 0),
(4, 11, 'Gói CRM + Triển khai + Đào tạo', 1, 'Gói', 25000000, 10, 2500000, 27500000, 1),
(5, 2, 'ToryCRM - Gói Professional', 12, 'Tháng', 1500000, 10, 1800000, 19800000, 0),
(6, 5, 'Dịch vụ đào tạo', 5, 'Buổi', 3000000, 10, 1500000, 16500000, 0);

-- ============================================================
-- CALENDAR EVENTS (Lịch hẹn)
-- ============================================================
INSERT INTO `calendar_events` (`title`, `description`, `type`, `color`, `start_at`, `end_at`, `all_day`, `location`, `contact_id`, `company_id`, `deal_id`, `user_id`, `created_by`) VALUES
('Demo CRM cho ABC Software', 'Demo toàn bộ tính năng CRM cho team sales ABC', 'meeting', '#405189', '2026-03-22 09:00:00', '2026-03-22 11:00:00', 0, 'Văn phòng ABC, 123 Nguyễn Huệ Q1', 1, 1, 1, 2, 1),
('Gọi follow-up XYZ', 'Gọi chị Bình hỏi phản hồi báo giá Enterprise', 'call', '#0ab39c', '2026-03-21 14:00:00', '2026-03-21 14:30:00', 0, NULL, 2, 2, 2, 2, 1),
('Họp team sales', 'Review KPI tuần và phân chia lead mới', 'meeting', '#405189', '2026-03-24 08:30:00', '2026-03-24 09:30:00', 0, 'Phòng họp A', NULL, NULL, NULL, 2, 1),
('Thăm Logistics Toàn Cầu', 'Thăm kho và khảo sát hiện trạng', 'visit', '#f06548', '2026-03-25 09:00:00', '2026-03-25 12:00:00', 0, 'Kho hàng Q7', 5, 5, 4, 2, 1),
('Deadline proposal XYZ', 'Hạn chót gửi proposal cho XYZ', 'reminder', '#ffbe0b', '2026-03-23 17:00:00', NULL, 0, NULL, 2, 2, 2, 2, 1),
('Đào tạo TechViet - Buổi 1', 'Buổi đào tạo đầu tiên cho TechViet', 'meeting', '#405189', '2026-03-26 09:00:00', '2026-03-26 12:00:00', 0, 'Online - Google Meet', 4, 4, 3, 3, 1),
('Ngày Phụ nữ VN - Nghỉ', 'Ngày nghỉ lễ', 'other', '#299cdb', '2026-10-20 00:00:00', '2026-10-20 23:59:00', 1, NULL, NULL, NULL, NULL, 2, 1),
('Gọi Minh Phát - báo giá', 'Gọi anh Phong xác nhận báo giá', 'call', '#0ab39c', '2026-03-27 10:00:00', '2026-03-27 10:30:00', 0, NULL, 13, 3, 5, 3, 1);

-- ============================================================
-- TICKETS (Hỗ trợ khách hàng)
-- ============================================================
INSERT INTO `tickets` (`ticket_code`, `title`, `content`, `category_id`, `contact_id`, `company_id`, `priority`, `status`, `assigned_to`, `contact_phone`, `contact_email`, `due_date`, `created_by`) VALUES
('TK2603001', 'Không đăng nhập được CRM', 'Khi nhập email/password đúng nhưng hệ thống báo lỗi "Invalid credentials". Đã thử reset password nhưng vẫn không được.', 1, 4, 4, 'high', 'in_progress', 6, '0934444444', 'dung.pt@techviet.io', '2026-03-21 17:00:00', 4),
('TK2603002', 'Yêu cầu xuất báo cáo theo quý', 'Hiện tại báo cáo chỉ có theo tháng và năm. Cần thêm tính năng xuất báo cáo theo quý để trình lãnh đạo.', 3, 14, 4, 'medium', 'open', 6, '0944567890', 'quynh.ht@techviet.io', '2026-04-01 17:00:00', 4),
('TK2603003', 'Tốc độ tải trang chậm', 'Dashboard mất khoảng 5-8 giây để load. Đặc biệt chậm khi có nhiều deal trong pipeline.', 1, 1, 1, 'urgent', 'open', 6, '0901111111', 'an.nv@abc-soft.vn', '2026-03-22 12:00:00', 2),
('TK2603004', 'Cảm ơn team hỗ trợ', 'Cảm ơn team đã hỗ trợ rất nhanh và nhiệt tình trong quá trình triển khai. Rất hài lòng!', 4, 4, 4, 'low', 'closed', 4, '0934444444', 'dung.pt@techviet.io', NULL, 4),
('TK2603005', 'Lỗi import danh sách khách hàng', 'Upload file CSV 500 records nhưng chỉ import được 200. Không có thông báo lỗi cụ thể.', 1, 10, 1, 'high', 'waiting', 6, '0990000000', 'linh.nt@abc-soft.vn', '2026-03-23 17:00:00', 2);

-- Ticket comments
INSERT INTO `ticket_comments` (`ticket_id`, `content`, `is_internal`, `user_id`) VALUES
(1, 'Đã kiểm tra, lỗi do session bị hết hạn. Đang fix cấu hình session timeout.', 0, 6),
(1, 'Cần update config SESSION_LIFETIME trong .env. Hiện đang set 15 phút, quá ngắn.', 1, 6),
(3, 'Đã reproduce được lỗi. Nguyên nhân: query dashboard không có index. Đang tối ưu.', 1, 6),
(4, 'Cảm ơn bạn đã phản hồi! Chúng tôi rất vui khi được hỗ trợ.', 0, 4),
(5, 'Đã check log, lỗi do encoding file CSV không phải UTF-8. Đang chờ KH gửi lại file.', 0, 6);

-- ============================================================
-- CAMPAIGNS (Chiến dịch Marketing)
-- ============================================================
INSERT INTO `campaigns` (`campaign_code`, `name`, `type`, `status`, `description`, `start_date`, `end_date`, `budget`, `actual_cost`, `target_count`, `reached_count`, `converted_count`, `owner_id`, `created_by`) VALUES
('CD260001', 'Email Giới thiệu CRM Q1/2026', 'email', 'running', 'Chiến dịch email giới thiệu tính năng mới CRM Q1 2026 cho khách hàng tiềm năng', '2026-03-01', '2026-03-31', 5000000, 2500000, 100, 65, 8, 4, 1),
('CD260002', 'SMS Khuyến mãi Tết', 'sms', 'completed', 'Gửi SMS khuyến mãi dịp Tết Nguyên Đán - giảm 20% gói Pro', '2026-01-15', '2026-02-15', 3000000, 2800000, 200, 180, 15, 4, 1),
('CD260003', 'Webinar CRM cho SME', 'other', 'draft', 'Tổ chức webinar hướng dẫn sử dụng CRM cho doanh nghiệp vừa và nhỏ', '2026-04-10', '2026-04-10', 2000000, 0, 50, 0, 0, 3, 1),
('CD260004', 'Facebook Ads tháng 3', 'social', 'running', 'Quảng cáo Facebook hướng đến đối tượng CEO/Director các SME', '2026-03-01', '2026-03-31', 10000000, 6500000, 500, 320, 25, 4, 1),
('CD260005', 'Gọi điện chăm sóc KH cũ', 'call', 'paused', 'Gọi điện chăm sóc khách hàng đã hết hạn license để gia hạn', '2026-03-10', '2026-04-10', 1000000, 500000, 30, 12, 3, 2, 1);

-- Campaign contacts
INSERT INTO `campaign_contacts` (`campaign_id`, `contact_id`, `status`, `sent_at`, `opened_at`, `clicked_at`) VALUES
(1, 1, 'opened', '2026-03-02 09:00:00', '2026-03-02 10:15:00', NULL),
(1, 2, 'clicked', '2026-03-02 09:00:00', '2026-03-02 11:30:00', '2026-03-02 11:32:00'),
(1, 3, 'sent', '2026-03-02 09:00:00', NULL, NULL),
(1, 5, 'opened', '2026-03-02 09:00:00', '2026-03-03 08:45:00', NULL),
(1, 7, 'failed', NULL, NULL, NULL),
(1, 8, 'sent', '2026-03-02 09:00:00', NULL, NULL),
(1, 11, 'converted', '2026-03-02 09:00:00', '2026-03-02 14:00:00', '2026-03-02 14:05:00'),
(1, 12, 'opened', '2026-03-02 09:00:00', '2026-03-04 09:20:00', NULL),
(2, 1, 'converted', '2026-01-20 10:00:00', NULL, NULL),
(2, 4, 'converted', '2026-01-20 10:00:00', NULL, NULL),
(2, 5, 'sent', '2026-01-20 10:00:00', NULL, NULL),
(4, 3, 'clicked', '2026-03-05 08:00:00', '2026-03-05 12:00:00', '2026-03-05 12:05:00'),
(4, 7, 'converted', '2026-03-05 08:00:00', '2026-03-06 09:00:00', '2026-03-06 09:10:00'),
(4, 12, 'opened', '2026-03-05 08:00:00', '2026-03-07 11:00:00', NULL),
(5, 9, 'pending', NULL, NULL, NULL);

-- ============================================================
-- PURCHASE ORDERS (Đơn hàng mua)
-- ============================================================
INSERT INTO `purchase_orders` (`order_code`, `supplier_id`, `status`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `notes`, `payment_status`, `paid_amount`, `expected_date`, `owner_id`, `created_by`) VALUES
('PO2603001', 6, 'completed', 400000000, 40000000, 0, 440000000, 'Mua laptop Dell cho nhân viên mới', 'paid', 440000000, '2026-03-15', 6, 1),
('PO2603002', 6, 'approved', 80000000, 8000000, 5000000, 83000000, 'Mua màn hình cho phòng KD', 'unpaid', 0, '2026-03-28', 6, 1),
('PO2603003', 7, 'draft', 15000000, 1500000, 0, 16500000, 'Đặt thiết kế brochure sản phẩm', 'unpaid', 0, '2026-04-05', 4, 1);

INSERT INTO `purchase_order_items` (`purchase_order_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `tax_rate`, `tax_amount`, `total`, `received_quantity`, `sort_order`) VALUES
(1, 9, 'Máy tính xách tay Dell', 16, 'Cái', 25000000, 10, 40000000, 440000000, 16, 0),
(2, 10, 'Màn hình Dell 27 inch', 10, 'Cái', 8000000, 10, 8000000, 88000000, 0, 0),
(3, NULL, 'Thiết kế brochure A4', 1000, 'Tờ', 15000, 10, 1500000, 16500000, 0, 0);

-- ============================================================
-- FUND TRANSACTIONS (Phiếu thu / Phiếu chi)
-- ============================================================
INSERT INTO `fund_transactions` (`transaction_code`, `type`, `fund_account_id`, `amount`, `category`, `description`, `contact_id`, `company_id`, `order_id`, `transaction_date`, `status`, `confirmed_by`, `confirmed_at`, `created_by`) VALUES
('PT2603001', 'receipt', 2, 55000000, 'Thu tiền bán hàng', 'Thu tiền đơn hàng TechViet - DH2603001', 4, 4, 1, '2026-03-01', 'confirmed', 1, '2026-03-01 10:00:00', 1),
('PT2603002', 'receipt', 2, 80000000, 'Thu tiền bán hàng', 'Thu đợt 1 - ABC Software - DH2603002', 1, 1, 2, '2026-03-16', 'confirmed', 1, '2026-03-16 14:00:00', 1),
('PT2603003', 'receipt', 2, 49500000, 'Thu tiền bán hàng', 'Thu tiền đào tạo XYZ - DH2603004', 11, 2, 6, '2026-03-10', 'confirmed', 1, '2026-03-10 11:00:00', 1),
('PC2603001', 'payment', 2, 440000000, 'Chi mua hàng', 'Thanh toán đơn mua laptop - PO2603001', NULL, 6, NULL, '2026-03-15', 'confirmed', 1, '2026-03-15 15:00:00', 1),
('PC2603002', 'payment', 1, 2500000, 'Chi marketing', 'Chi phí email marketing Q1', NULL, NULL, NULL, '2026-03-05', 'confirmed', 1, '2026-03-05 09:00:00', 1),
('PC2603003', 'payment', 1, 6500000, 'Chi marketing', 'Facebook Ads tháng 3', NULL, NULL, NULL, '2026-03-18', 'confirmed', 1, '2026-03-18 16:00:00', 1),
('PT2603004', 'receipt', 1, 5000000, 'Thu khác', 'Thu tiền mặt tư vấn ngoài giờ', 3, 3, NULL, '2026-03-12', 'draft', NULL, NULL, 3),
('PC2603004', 'payment', 1, 3500000, 'Chi văn phòng phẩm', 'Mua VPP và đồ dùng văn phòng tháng 3', NULL, NULL, NULL, '2026-03-19', 'draft', NULL, NULL, 1);

-- Update fund account balances for confirmed transactions
UPDATE `fund_accounts` SET `balance` = -12500000 WHERE id = 1;
UPDATE `fund_accounts` SET `balance` = -255000000 WHERE id = 2;

-- ============================================================
-- NOTIFICATIONS (Thông báo mẫu)
-- ============================================================
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `icon`, `is_read`) VALUES
(1, 'deal', 'Deal mới thắng!', 'TechViet - Gói startup đã đóng thành công, giá trị 50,000,000đ', 'deals/3', 'ri-hand-coin-line', 0),
(1, 'task', 'Công việc quá hạn', 'Review code module ticket đã quá hạn', 'tasks/8', 'ri-task-line', 0),
(1, 'order', 'Đơn hàng mới', 'Đơn hàng DH2603003 cho GlobalLog đã được tạo', 'orders/3', 'ri-file-list-3-line', 1),
(1, 'calendar', 'Lịch hẹn sắp tới', 'Demo CRM cho ABC Software vào 9:00 ngày 22/03', 'calendar/1', 'ri-calendar-event-line', 0),
(1, 'system', 'Ticket mới cần xử lý', 'Ticket TK2603003: Tốc độ tải trang chậm - Priority: Urgent', 'tickets/3', 'ri-customer-service-line', 0),
(2, 'deal', 'Cập nhật pipeline', 'Deal "CRM cho ABC Software" đã chuyển sang giai đoạn Đàm phán', 'deals/1', 'ri-hand-coin-line', 0),
(2, 'task', 'Task mới được giao', 'Demo module kho cho GlobalLog - Hạn 24/03', 'tasks/6', 'ri-task-line', 0),
(3, 'success', 'Gia hạn hợp đồng', 'TechViet đã gia hạn thêm 12 tháng', 'deals/3', 'ri-checkbox-circle-line', 1),
(6, 'danger', 'Ticket khẩn cấp', 'Ticket TK2603003 cần xử lý gấp - tốc độ tải chậm', 'tickets/3', 'ri-alarm-warning-line', 0);

-- ============================================================
-- ACTIVITIES (Hoạt động gần đây)
-- ============================================================
INSERT INTO `activities` (`type`, `title`, `description`, `contact_id`, `deal_id`, `company_id`, `user_id`) VALUES
('deal', 'Deal thắng: TechViet', 'Deal TechViet - Gói startup đã đóng thành công với giá trị 50,000,000đ', 4, 3, 4, 3),
('call', 'Gọi điện ABC Software', 'Gọi anh An xác nhận lịch demo ngày 22/03', 1, 1, 1, 2),
('email', 'Gửi báo giá XYZ', 'Gửi báo giá gói Enterprise cho chị Bình - XYZ', 2, 2, 2, 2),
('meeting', 'Họp team sales', 'Họp review pipeline tuần 3 tháng 3', NULL, NULL, NULL, 2),
('note', 'Ghi chú GlobalLog', 'KH cần tích hợp với hệ thống WMS hiện tại, cần đánh giá API', 5, 4, 5, 2),
('task', 'Hoàn thành: Gửi HĐ TechViet', 'Đã gửi hợp đồng gia hạn 12 tháng cho TechViet', 4, 3, 4, 3),
('system', 'Ticket mới: Lỗi đăng nhập', 'Ticket TK2603001 đã được tạo - khách hàng TechViet không đăng nhập được', 4, NULL, 4, 4),
('deal', 'Deal mất: Sáng Tạo Agency', 'Deal cho Sáng Tạo Agency đã mất - lý do: ngân sách không đủ', 7, 7, 7, 4),
('email', 'Gửi campaign email Q1', 'Chiến dịch email Q1/2026 đã gửi đến 100 khách hàng tiềm năng', NULL, NULL, NULL, 4),
('system', 'Import khách hàng', 'Import 15 khách hàng mới từ file CSV', NULL, NULL, NULL, 1);

SET FOREIGN_KEY_CHECKS = 1;
