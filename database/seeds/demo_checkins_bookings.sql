SET NAMES utf8mb4;

-- ============================================================
-- CHECK-INS (15 records)
-- ============================================================
INSERT INTO checkins (tenant_id, user_id, contact_id, company_id, latitude, longitude, address, note, check_type, created_at) VALUES
(1, 2, 1, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Demo CRM cho team sales ABC Software', 'visit', '2026-04-01 09:30:00'),
(1, 2, 5, 7, 10.7580, 106.7215, '321 Võ Văn Tần, Quận 3, TP.HCM', 'Khảo sát kho hàng GlobalLog', 'visit', '2026-04-01 14:00:00'),
(1, 3, 4, 6, 10.7831, 106.6965, '12 Hoàng Diệu, Quận 4, TP.HCM', 'Đào tạo sử dụng CRM cho TechViet', 'meeting', '2026-04-02 09:00:00'),
(1, 2, 2, 4, 10.7865, 106.6800, '456 Lê Lợi, Quận 3, TP.HCM', 'Họp báo giá gói Enterprise với XYZ', 'meeting', '2026-04-02 14:30:00'),
(1, 3, 3, 5, 21.0285, 105.8542, '789 Trần Hưng Đạo, Hà Nội', 'Tư vấn triển khai CRM cho Minh Phát', 'visit', '2026-04-03 10:00:00'),
(1, 4, 7, 9, 10.7756, 106.7019, '56 Nguyễn Thị Minh Khai, TP.HCM', 'Gặp Creative Director bàn gói SME', 'visit', '2026-04-03 15:00:00'),
(1, 2, 8, 8, 10.7900, 106.6500, '100 Điện Biên Phủ, TP.HCM', 'Demo module quản lý bệnh nhân', 'meeting', '2026-04-04 09:00:00'),
(1, 6, 1, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Hỗ trợ kỹ thuật triển khai ABC Software', 'visit', '2026-04-04 14:00:00'),
(1, 2, 10, 3, 10.7769, 106.7009, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Đào tạo buổi 2 cho ABC Software', 'meeting', '2026-04-05 09:30:00'),
(1, 3, 14, 6, 10.7831, 106.6965, '12 Hoàng Diệu, Quận 4, TP.HCM', 'Bàn giao module HR cho TechViet', 'delivery', '2026-04-05 14:00:00'),
(1, 2, 6, 8, 11.0544, 106.6668, 'KCN Bình Dương', 'Khảo sát nhà máy Thành Đạt', 'visit', '2026-04-07 08:30:00'),
(1, 4, 12, NULL, 10.7800, 106.6950, '78 Hai Bà Trưng, Quận 1, TP.HCM', 'Gặp chị Oanh tư vấn gói chuỗi nhà hàng', 'visit', '2026-04-07 14:00:00'),
(1, 2, 5, 7, 10.7400, 106.7300, 'Kho hàng Q7, TP.HCM', 'Kiểm tra hệ thống sau triển khai', 'visit', '2026-04-08 09:00:00'),
(1, 3, 13, 5, 21.0285, 105.8542, '789 Trần Hưng Đạo, Hà Nội', 'Demo module kho cho anh Phong', 'meeting', '2026-04-08 14:00:00'),
(1, 2, 11, 4, 10.7865, 106.6800, '456 Lê Lợi, Quận 3, TP.HCM', 'Đào tạo sử dụng CRM cho XYZ', 'meeting', '2026-04-09 09:00:00');

-- ============================================================
-- BOOKING LINKS (3 links)
-- ============================================================
INSERT INTO booking_links (tenant_id, user_id, slug, title, description, duration_minutes, available_days, available_hours, buffer_minutes, max_advance_days, is_active, booking_count) VALUES
(1, 2, 'nguyen-van-hung', 'Hẹn demo CRM với Nguyễn Văn Hùng', 'Đặt lịch demo sản phẩm ToryCRM 30 phút với anh Hùng - Trưởng phòng Kinh doanh', 30, '["1","2","3","4","5"]', '{"start":"08:00","end":"17:00"}', 15, 14, 1, 5),
(1, 3, 'le-minh-tuan', 'Tư vấn CRM với Lê Minh Tuấn', 'Đặt lịch tư vấn miễn phí 45 phút về giải pháp CRM phù hợp cho doanh nghiệp', 45, '["1","2","3","4","5"]', '{"start":"09:00","end":"16:00"}', 30, 21, 1, 3),
(1, 6, 'ho-duc-anh-support', 'Hỗ trợ kỹ thuật - Hoàng Đức Anh', 'Đặt lịch hỗ trợ kỹ thuật 1:1 với team kỹ thuật', 60, '["1","2","3","4","5","6"]', '{"start":"08:30","end":"17:30"}', 15, 7, 1, 2);

-- ============================================================
-- BOOKINGS (15 records)
-- ============================================================
INSERT INTO bookings (link_id, contact_name, contact_email, contact_phone, start_at, end_at, note, status) VALUES
(1, 'Nguyễn Thanh Tùng', 'tung.nt@startup.vn', '0901234000', '2026-04-10 09:00:00', '2026-04-10 09:30:00', 'Muốn xem demo gói Professional cho 15 người', 'confirmed'),
(1, 'Trần Văn Bảo', 'bao.tv@logistics.vn', '0912345000', '2026-04-10 10:00:00', '2026-04-10 10:30:00', 'Quan tâm module quản lý kho', 'confirmed'),
(1, 'Lê Thị Hương', 'huong.lt@fashion.vn', '0923456000', '2026-04-11 14:00:00', '2026-04-11 14:30:00', 'Chuỗi 3 cửa hàng, cần CRM quản lý KH', 'confirmed'),
(1, 'Phạm Đức Minh', 'minh.pd@realestate.vn', '0934567000', '2026-04-07 09:00:00', '2026-04-07 09:30:00', 'BĐS, 20 sales cần quản lý pipeline', 'completed'),
(1, 'Võ Thị Lan', 'lan.vt@edu.vn', '0945678000', '2026-04-05 15:00:00', '2026-04-05 15:30:00', 'Trung tâm đào tạo, quản lý học viên', 'completed'),
(2, 'Hoàng Minh Đức', 'duc.hm@fintech.vn', '0956789000', '2026-04-11 09:00:00', '2026-04-11 09:45:00', 'Fintech startup, cần tích hợp API', 'confirmed'),
(2, 'Ngô Quốc Việt', 'viet.nq@manufacturing.vn', '0967890000', '2026-04-08 14:00:00', '2026-04-08 14:45:00', 'Nhà máy 200 NV, đang dùng Excel', 'completed'),
(2, 'Đặng Thị Mai', 'mai.dt@healthcare.vn', '0978901000', '2026-04-12 10:00:00', '2026-04-12 10:45:00', 'Phòng khám tư nhân, quản lý bệnh nhân', 'confirmed'),
(3, 'Bùi Văn Khoa', 'khoa.bv@techcorp.vn', '0989012000', '2026-04-10 14:00:00', '2026-04-10 15:00:00', 'Lỗi import CSV trên 1000 dòng', 'confirmed'),
(3, 'Lý Thị Ngọc', 'ngoc.lt@design.vn', '0990123000', '2026-04-09 09:00:00', '2026-04-09 10:00:00', 'Không gửi được email campaign', 'completed'),
(1, 'Đinh Công Thành', 'thanh.dc@media.vn', '0901111000', '2026-04-14 09:00:00', '2026-04-14 09:30:00', 'Agency 10 người, quản lý dự án + KH', 'confirmed'),
(1, 'Phan Thị Yến', 'yen.pt@travel.vn', '0912222000', '2026-04-14 14:00:00', '2026-04-14 14:30:00', 'Công ty du lịch, quản lý tour + KH', 'confirmed'),
(2, 'Trương Văn Hải', 'hai.tv@construction.vn', '0923333000', '2026-04-15 09:00:00', '2026-04-15 09:45:00', 'Xây dựng, quản lý hợp đồng + tiến độ', 'confirmed'),
(3, 'Cao Thị Hạnh', 'hanh.ct@retail.vn', '0934444000', '2026-04-11 14:00:00', '2026-04-11 15:00:00', 'Lỗi đồng bộ Zalo OA', 'confirmed'),
(2, 'Mai Xuân Long', 'long.mx@fnb.vn', '0945555000', '2026-04-04 09:00:00', '2026-04-04 09:45:00', 'Chuỗi F&B 8 chi nhánh', 'no_show');
