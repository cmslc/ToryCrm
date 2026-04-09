SET NAMES utf8mb4;

-- ============================================================
-- THÊM CÔNG NỢ (cần thêm 9)
-- ============================================================
INSERT INTO debts (tenant_id, type, contact_id, company_id, amount, paid_amount, due_date, status, description, created_by) VALUES
(1, 'receivable', 10, 3, 45000000, 45000000, '2026-03-30', 'paid', 'Thu tiền đào tạo CRM - Ngô Thị Linh', 1),
(1, 'receivable', 13, 5, 25000000, 10000000, '2026-04-20', 'partial', 'Thu tiền tư vấn - Phan Quốc Phong', 1),
(1, 'receivable', 14, 6, 35000000, 0, '2026-05-10', 'open', 'Thu tiền module HR - Hồ Thị Quỳnh', 1),
(1, 'payable', NULL, 3, 12000000, 12000000, '2026-03-15', 'paid', 'Trả tiền hosting server năm 2026', 1),
(1, 'payable', NULL, 9, 8500000, 0, '2026-04-15', 'open', 'Chi phí in ấn tài liệu marketing', 1),
(1, 'receivable', 7, 9, 18000000, 0, '2026-03-01', 'overdue', 'Công nợ quá hạn - Đặng Quốc Giang', 1),
(1, 'receivable', 6, 8, 42000000, 20000000, '2026-04-30', 'partial', 'Thu tiền bảo trì hệ thống - Võ Thị Phượng', 1),
(1, 'payable', NULL, 6, 25000000, 25000000, '2026-03-20', 'paid', 'Trả tiền mua thiết bị mạng', 1),
(1, 'receivable', 15, 7, 60000000, 0, '2026-05-30', 'open', 'Công nợ dự án logistics - Mai Văn Sơn', 1);

INSERT INTO debt_payments (debt_id, amount, payment_method, note, paid_at, created_by) VALUES
(7, 45000000, 'Chuyển khoản', 'Thu đủ qua VCB', '2026-03-28', 1),
(8, 10000000, 'Tiền mặt', 'Thu đợt 1', '2026-04-05', 1),
(10, 12000000, 'Chuyển khoản', 'Thanh toán qua ACB', '2026-03-10', 1),
(13, 20000000, 'Chuyển khoản', 'Thu đợt 1 bảo trì', '2026-04-10', 1),
(14, 25000000, 'Chuyển khoản', 'Trả đủ tiền thiết bị', '2026-03-18', 1);

-- ============================================================
-- THÊM HỢP ĐỒNG (cần thêm 10)
-- ============================================================
INSERT INTO contracts (tenant_id, contract_number, title, contact_id, company_id, type, status, value, recurring_value, recurring_cycle, start_date, end_date, signed_date, auto_renew, payment_terms, notes, owner_id, created_by) VALUES
(1, 'HD-2604-0001', 'Hợp đồng CRM cho GlobalLog', 5, 7, 'service', 'draft', 210000000, 5000000, 'monthly', '2026-05-01', '2027-04-30', NULL, 1, 'Thanh toán hàng tháng', 'Gói Enterprise + module kho', 2, 1),
(1, 'HD-2604-0002', 'Hợp đồng bảo trì BV Hòa Bình', 8, 8, 'maintenance', 'sent', 36000000, 3000000, 'monthly', '2026-05-01', '2027-04-30', NULL, 1, 'Thanh toán đầu tháng', 'Bảo trì và hỗ trợ kỹ thuật 24/7', 2, 1),
(1, 'HD-2604-0003', 'Hợp đồng thiết kế Sáng Tạo', 7, 9, 'consulting', 'cancelled', 30000000, 0, 'none', '2026-03-01', '2026-05-31', NULL, 0, 'Thanh toán 100% trước', 'Đã hủy do KH không đủ ngân sách', 4, 1),
(1, 'HD-2604-0004', 'Hợp đồng SMS Marketing', NULL, NULL, 'service', 'active', 12000000, 1000000, 'monthly', '2026-01-01', '2026-12-31', '2025-12-20', 1, 'Thanh toán tự động', 'Gói SMS 10,000 tin/tháng', 4, 1),
(1, 'HD-2604-0005', 'Hợp đồng đào tạo nội bộ', NULL, NULL, 'consulting', 'active', 15000000, 0, 'none', '2026-04-01', '2026-04-30', '2026-04-01', 0, 'Thanh toán sau đào tạo', 'Đào tạo nhân viên mới 5 buổi', 6, 1),
(1, 'HD-2604-0006', 'Hợp đồng API tích hợp ERP', 6, 8, 'service', 'negotiating', 150000000, 0, 'none', '2026-06-01', '2026-12-31', NULL, 0, 'Theo milestone', 'Tích hợp CRM với SAP ERP', 3, 1),
(1, 'HD-2604-0007', 'Gia hạn HĐ TechViet năm 2', 4, 6, 'service', 'signed', 55000000, 500000, 'monthly', '2027-03-01', '2028-02-28', '2026-04-05', 1, 'Thanh toán hàng tháng', 'Gia hạn từ HD-2603-0001', 3, 1),
(1, 'HD-2604-0008', 'Hợp đồng phát triển plugin', 14, 6, 'product', 'draft', 80000000, 0, 'none', '2026-05-01', '2026-08-31', NULL, 0, 'Thanh toán theo giai đoạn', 'Plugin quản lý kho nâng cao', 3, 1),
(1, 'HD-2604-0009', 'Hợp đồng white-label', 12, NULL, 'service', 'sent', 200000000, 10000000, 'monthly', '2026-06-01', '2027-05-31', NULL, 1, 'Thanh toán quý', 'Đối tác phân phối CRM', 2, 1),
(1, 'HD-2604-0010', 'Hợp đồng hỗ trợ kỹ thuật 24/7', 1, 3, 'maintenance', 'active', 60000000, 5000000, 'monthly', '2026-04-01', '2027-03-31', '2026-03-25', 1, 'Thanh toán đầu tháng', 'Gói premium support cho ABC Software', 2, 1);

-- ============================================================
-- THÊM BÁO GIÁ (cần thêm 10)
-- ============================================================
INSERT INTO quotations (tenant_id, quote_number, title, contact_id, company_id, status, subtotal, tax_amount, discount_amount, total, valid_until, notes, terms, portal_token, owner_id, created_by) VALUES
(1, 'BG2604006', 'Báo giá module Email Marketing', 10, 3, 'sent', 9600000, 960000, 0, 10560000, '2026-04-25', 'Module email marketing 12 tháng', 'Thanh toán trước khi kích hoạt', 'emailmkt001', 2, 1),
(1, 'BG2604007', 'Báo giá hệ thống call center', 5, 7, 'draft', 45000000, 4500000, 5000000, 44500000, '2026-05-01', 'Tích hợp tổng đài Stringee', 'Bao gồm 12 tháng license', NULL, 2, 1),
(1, 'BG2604008', 'Báo giá đào tạo nâng cao', 14, 6, 'accepted', 9000000, 900000, 0, 9900000, '2026-04-15', '3 buổi đào tạo nâng cao', 'Bao gồm tài liệu', 'daotao001', 3, 1),
(1, 'BG2604009', 'Báo giá CRM cho chuỗi nhà hàng', 12, NULL, 'sent', 72000000, 7200000, 10000000, 69200000, '2026-05-10', 'Gói cho 5 chi nhánh', 'Giá đặc biệt cho chuỗi', 'nhahang001', 2, 1),
(1, 'BG2604010', 'Báo giá tư vấn chuyển đổi số', 3, 5, 'expired', 100000000, 10000000, 0, 110000000, '2026-03-15', 'Tư vấn toàn diện 6 tháng', 'Đã hết hạn', 'cds001', 3, 1),
(1, 'BG2604011', 'Báo giá gói Starter cho freelancer', 9, NULL, 'rejected', 6000000, 600000, 0, 6600000, '2026-04-01', 'Gói 12 tháng cho cá nhân', 'Không có hỗ trợ kỹ thuật', 'freelance001', 3, 1),
(1, 'BG2604012', 'Báo giá nâng cấp từ Pro lên Enterprise', 2, 4, 'sent', 42000000, 4200000, 5000000, 41200000, '2026-04-30', 'Nâng cấp giữa kỳ', 'Tính chênh lệch 8 tháng còn lại', 'upgrade001', 2, 1),
(1, 'BG2604013', 'Báo giá tích hợp Zalo OA', 1, 3, 'draft', 15000000, 1500000, 0, 16500000, '2026-05-15', 'Tích hợp Zalo vào CRM', 'Bao gồm cấu hình + đào tạo', NULL, 2, 1),
(1, 'BG2604014', 'Báo giá gói Data Migration', 6, 8, 'sent', 20000000, 2000000, 0, 22000000, '2026-04-20', 'Di chuyển dữ liệu từ Excel sang CRM', 'Bao gồm clean data', 'datamig001', 3, 1),
(1, 'BG2604015', 'Báo giá gói All-in-One', 15, 7, 'draft', 180000000, 18000000, 20000000, 178000000, '2026-05-30', 'Gói trọn bộ: CRM + ERP + Training', 'Báo giá sơ bộ', NULL, 2, 1);

INSERT INTO quotation_items (quotation_id, product_id, product_name, quantity, unit, unit_price, tax_rate, discount, total, sort_order) VALUES
(6, 7, 'Module Email Marketing', 12, 'Tháng', 800000, 10, 0, 10560000, 0),
(7, NULL, 'Tích hợp Stringee VoIP', 1, 'Gói', 30000000, 10, 5000000, 28000000, 0),
(7, 5, 'Dịch vụ đào tạo', 2, 'Buổi', 3000000, 10, 0, 6600000, 1),
(8, 5, 'Dịch vụ đào tạo', 3, 'Buổi', 3000000, 10, 0, 9900000, 0),
(9, 2, 'ToryCRM - Gói Professional', 60, 'Tháng', 1500000, 10, 10000000, 89000000, 0),
(10, 6, 'Dịch vụ tư vấn', 50, 'Giờ', 1000000, 10, 0, 55000000, 0),
(10, 4, 'Dịch vụ triển khai', 1, 'Dự án', 15000000, 10, 0, 16500000, 1),
(11, 1, 'ToryCRM - Gói Starter', 12, 'Tháng', 500000, 10, 0, 6600000, 0),
(12, 3, 'ToryCRM - Gói Enterprise', 8, 'Tháng', 5000000, 10, 5000000, 39000000, 0),
(13, NULL, 'Tích hợp Zalo OA', 1, 'Gói', 15000000, 10, 0, 16500000, 0),
(14, NULL, 'Dịch vụ Data Migration', 1, 'Dự án', 20000000, 10, 0, 22000000, 0),
(15, 3, 'ToryCRM - Gói Enterprise', 12, 'Tháng', 5000000, 10, 0, 66000000, 0),
(15, 11, 'Gói CRM + Triển khai + Đào tạo', 1, 'Gói', 25000000, 10, 0, 27500000, 1),
(15, 6, 'Dịch vụ tư vấn', 40, 'Giờ', 1000000, 10, 0, 44000000, 2);

-- ============================================================
-- THÊM NGÂN SÁCH (cần thêm 12)
-- ============================================================
INSERT INTO budgets (tenant_id, name, type, period_start, period_end, total_budget, spent_amount, status, department, notes, approved_by, created_by) VALUES
(1, 'Ngân sách Kinh doanh Q2/2026', 'department', '2026-04-01', '2026-06-30', 50000000, 15000000, 'active', 'Kinh doanh', 'Chi phí bán hàng quý 2', 1, 2),
(1, 'Ngân sách Kỹ thuật Q2/2026', 'department', '2026-04-01', '2026-06-30', 40000000, 8000000, 'active', 'Kỹ thuật', 'Server, tools, license', 1, 6),
(1, 'Ngân sách triển khai GlobalLog', 'project', '2026-05-01', '2026-07-31', 25000000, 0, 'draft', NULL, 'Dự án triển khai cho GlobalLog', NULL, 2),
(1, 'Ngân sách sự kiện ra mắt v2.0', 'campaign', '2026-06-01', '2026-06-30', 20000000, 0, 'draft', 'Marketing', 'Sự kiện launch sản phẩm mới', NULL, 4),
(1, 'Ngân sách tuyển dụng Q2', 'department', '2026-04-01', '2026-06-30', 15000000, 5000000, 'active', 'Nhân sự', 'Chi phí tuyển 3 vị trí mới', 1, 1),
(1, 'Ngân sách văn phòng tháng 5', 'general', '2026-05-01', '2026-05-31', 15000000, 0, 'draft', NULL, 'Chi phí vận hành tháng 5', NULL, 1),
(1, 'Ngân sách hoạt động tháng 3', 'general', '2026-03-01', '2026-03-31', 15000000, 14500000, 'closed', NULL, 'Đã đóng - chi gần hết', 1, 1),
(1, 'Ngân sách Marketing Q1/2026', 'department', '2026-01-01', '2026-03-31', 25000000, 27000000, 'exceeded', 'Marketing', 'Vượt ngân sách 2tr do Facebook Ads', 1, 4),
(1, 'Ngân sách dự án BV Hòa Bình', 'project', '2026-05-01', '2026-08-31', 30000000, 0, 'approved', NULL, 'Triển khai CRM cho bệnh viện', 1, 2),
(1, 'Ngân sách Kỹ thuật Q1/2026', 'department', '2026-01-01', '2026-03-31', 35000000, 32000000, 'closed', 'Kỹ thuật', 'Đã đóng - trong ngân sách', 1, 6),
(1, 'Ngân sách đào tạo nhân viên', 'general', '2026-04-01', '2026-06-30', 10000000, 3000000, 'active', NULL, 'Đào tạo nội bộ + gửi đi', 1, 1),
(1, 'Ngân sách phát triển plugin', 'project', '2026-04-01', '2026-09-30', 60000000, 10000000, 'active', 'Kỹ thuật', 'R&D marketplace plugins', 1, 6);

INSERT INTO budget_items (budget_id, category, planned_amount, actual_amount, sort_order) VALUES
(4, 'Di chuyển / Tiếp khách', 20000000, 8000000, 1),
(4, 'Demo / POC', 15000000, 5000000, 2),
(4, 'Hoa hồng', 10000000, 2000000, 3),
(4, 'Khác', 5000000, 0, 4),
(5, 'Server / Cloud', 20000000, 5000000, 1),
(5, 'License phần mềm', 10000000, 3000000, 2),
(5, 'Thiết bị', 5000000, 0, 3),
(5, 'Dự phòng', 5000000, 0, 4),
(8, 'Tuyển trên TopCV', 5000000, 3000000, 1),
(8, 'Headhunter', 7000000, 2000000, 2),
(8, 'Phỏng vấn / Test', 3000000, 0, 3),
(15, 'Plugin kho nâng cao', 25000000, 5000000, 1),
(15, 'Plugin báo cáo BI', 20000000, 5000000, 2),
(15, 'Plugin chatbot', 15000000, 0, 3);

-- ============================================================
-- THÊM HOA HỒNG (cần thêm 9)
-- ============================================================
INSERT INTO commissions (tenant_id, user_id, rule_id, entity_type, entity_id, amount, base_value, rate, status, period, notes) VALUES
(1, 3, 3, 'order', 1, 1650000, 55000000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn TechViet - Lê Minh Tuấn'),
(1, 2, 1, 'deal', 9, 3750000, 75000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ABC Module HR'),
(1, 2, 2, 'deal', 2, 40000000, 500000000, 8.00, 'pending', '2026-04', 'Hoa hồng deal Enterprise XYZ'),
(1, 3, 1, 'deal', 6, 17500000, 350000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ERP Thành Đạt'),
(1, 2, 1, 'deal', 4, 10000000, 200000000, 5.00, 'approved', '2026-04', 'Hoa hồng deal GlobalLog'),
(1, 4, 1, 'deal', 8, 6000000, 120000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal BV Hòa Bình'),
(1, 2, 3, 'order', 2, 4800000, 160000000, 3.00, 'approved', '2026-03', 'Hoa hồng đơn ABC Software'),
(1, 2, 3, 'order', 3, 6300000, 210000000, 3.00, 'pending', '2026-04', 'Hoa hồng đơn GlobalLog'),
(1, 3, 1, 'deal', 3, 2500000, 50000000, 5.00, 'paid', '2026-02', 'Hoa hồng deal TechViet tháng 2');

-- ============================================================
-- THÊM CONVERSATIONS (cần thêm 8)
-- ============================================================
INSERT INTO conversations (tenant_id, contact_id, channel, subject, status, assigned_to, last_message_at, last_message_preview, unread_count, is_starred) VALUES
(1, 6, 'email', 'Hỏi về module quỹ thu chi', 'open', 3, NOW() - INTERVAL 45 MINUTE, 'Module quỹ có hỗ trợ xuất báo cáo không?', 1, 0),
(1, 7, 'zalo', NULL, 'open', 4, NOW() - INTERVAL 90 MINUTE, 'Em muốn đăng ký gói dùng thử', 2, 0),
(1, 9, 'facebook', 'Hỏi giá cho freelancer', 'closed', 3, NOW() - INTERVAL 5000 MINUTE, 'Dạ cảm ơn, để em suy nghĩ thêm', 0, 0),
(1, 11, 'email', 'Yêu cầu hỗ trợ API', 'pending', 6, NOW() - INTERVAL 200 MINUTE, 'API rate limit là bao nhiêu request/phút?', 1, 1),
(1, 14, 'livechat', NULL, 'open', 3, NOW() - INTERVAL 5 MINUTE, 'Cho mình hỏi về tích hợp Zalo OA', 3, 1),
(1, 12, 'phone', 'Tư vấn gói cho chuỗi nhà hàng', 'open', 2, NOW() - INTERVAL 180 MINUTE, 'Hẹn thứ 5 demo tại nhà hàng Q7', 0, 0),
(1, 15, 'sms', NULL, 'resolved', 2, NOW() - INTERVAL 2000 MINUTE, 'Đã nhận được tài liệu, cảm ơn', 0, 0),
(1, 3, 'email', 'Follow up sau demo', 'open', 3, NOW() - INTERVAL 360 MINUTE, 'Anh Cường ơi, phản hồi demo hôm qua thế nào ạ?', 1, 0);

INSERT INTO messages (conversation_id, direction, sender_type, sender_id, content, content_type, created_at) VALUES
(16, 'inbound', 'contact', 6, 'Cho tôi hỏi module quỹ thu chi có xuất báo cáo Excel không?', 'text', NOW() - INTERVAL 60 MINUTE),
(16, 'outbound', 'user', 3, 'Chào chị Phượng, module Quỹ hỗ trợ xuất PDF và sắp có Excel ạ.', 'text', NOW() - INTERVAL 50 MINUTE),
(16, 'inbound', 'contact', 6, 'Module quỹ có hỗ trợ xuất báo cáo không?', 'text', NOW() - INTERVAL 45 MINUTE),
(17, 'inbound', 'contact', 7, 'Chào, mình là Giang, Creative Director. Muốn dùng thử CRM.', 'text', NOW() - INTERVAL 120 MINUTE),
(17, 'outbound', 'user', 4, 'Chào anh Giang! Anh có thể đăng ký dùng thử 14 ngày miễn phí tại website ạ.', 'text', NOW() - INTERVAL 100 MINUTE),
(17, 'inbound', 'contact', 7, 'Em muốn đăng ký gói dùng thử', 'text', NOW() - INTERVAL 90 MINUTE),
(20, 'inbound', 'contact', 11, 'API của ToryCRM có tài liệu ở đâu?', 'text', NOW() - INTERVAL 300 MINUTE),
(20, 'outbound', 'user', 6, 'Chào anh Nhật, tài liệu API tại /help/category/admin. Rate limit là 100 request/phút.', 'text', NOW() - INTERVAL 250 MINUTE),
(20, 'inbound', 'contact', 11, 'API rate limit là bao nhiêu request/phút?', 'text', NOW() - INTERVAL 200 MINUTE),
(21, 'inbound', 'contact', 14, 'Tôi đang dùng ToryCRM, muốn tích hợp Zalo OA', 'text', NOW() - INTERVAL 15 MINUTE),
(21, 'outbound', 'user', 3, 'Chào chị Quỳnh! Tính năng Zalo OA đã có, em hướng dẫn chị cấu hình nhé.', 'text', NOW() - INTERVAL 10 MINUTE),
(21, 'inbound', 'contact', 14, 'Cho mình hỏi về tích hợp Zalo OA', 'text', NOW() - INTERVAL 5 MINUTE),
(22, 'outbound', 'user', 2, 'Gọi chị Oanh tư vấn gói CRM cho chuỗi 5 nhà hàng.', 'text', NOW() - INTERVAL 240 MINUTE),
(22, 'inbound', 'contact', 12, 'Hẹn thứ 5 demo tại nhà hàng Q7', 'text', NOW() - INTERVAL 180 MINUTE),
(23, 'outbound', 'user', 3, 'Anh Cường ơi, phản hồi demo hôm qua thế nào ạ?', 'text', NOW() - INTERVAL 360 MINUTE);
