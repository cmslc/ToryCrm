SET NAMES utf8mb4;

-- ============================================================
-- CÔNG NỢ (Debts)
-- ============================================================
INSERT INTO debts (tenant_id, type, contact_id, company_id, order_id, amount, paid_amount, due_date, status, description, created_by) VALUES
(1, 'receivable', 1, 3, 2, 160000000, 80000000, '2026-05-01', 'partial', 'Công nợ đơn hàng DH2603002 - ABC Software', 1),
(1, 'receivable', 5, 7, 3, 210000000, 0, '2026-06-15', 'open', 'Công nợ đơn hàng DH2603003 - GlobalLog', 1),
(1, 'receivable', 2, 4, 4, 525000000, 0, '2026-05-15', 'open', 'Công nợ báo giá BG2603001 - XYZ', 1),
(1, 'payable', NULL, 8, NULL, 83000000, 0, '2026-03-28', 'overdue', 'Công nợ mua màn hình - PO2603002', 1),
(1, 'payable', NULL, 9, NULL, 16500000, 0, '2026-04-05', 'open', 'Công nợ thiết kế brochure - PO2603003', 1),
(1, 'receivable', 8, 8, NULL, 132000000, 0, '2026-06-30', 'open', 'Công nợ BV Hòa Bình - chờ ký HĐ', 1);

-- Payments cho debt partial
INSERT INTO debt_payments (debt_id, amount, payment_method, note, paid_at, created_by) VALUES
(1, 80000000, 'Chuyển khoản', 'Thu đợt 1 qua Vietcombank', '2026-03-16', 1);

-- ============================================================
-- HỢP ĐỒNG (Contracts)
-- ============================================================
INSERT INTO contracts (tenant_id, contract_number, title, contact_id, company_id, deal_id, type, status, value, recurring_value, recurring_cycle, start_date, end_date, signed_date, auto_renew, payment_terms, notes, owner_id, created_by) VALUES
(1, 'HD-2603-0001', 'Hợp đồng CRM gói Startup - TechViet', 4, 6, 3, 'service', 'active', 50000000, 500000, 'monthly', '2026-03-01', '2027-02-28', '2026-02-28', 1, 'Thanh toán hàng tháng vào ngày 5', 'Gói starter 12 tháng + 2 buổi đào tạo', 3, 1),
(1, 'HD-2603-0002', 'Hợp đồng triển khai CRM - ABC Software', 1, 3, 1, 'service', 'signed', 160000000, 1500000, 'monthly', '2026-04-01', '2027-03-31', '2026-03-20', 0, 'Thanh toán 50% trước, 50% sau triển khai', 'Gói Professional 12 tháng + triển khai', 2, 1),
(1, 'HD-2603-0003', 'Hợp đồng đào tạo - XYZ Việt Nam', 11, 4, 10, 'consulting', 'active', 49500000, 0, 'none', '2026-03-08', '2026-04-08', '2026-03-08', 0, 'Thanh toán sau khi hoàn thành đào tạo', '5 buổi đào tạo sử dụng CRM', 2, 1),
(1, 'HD-2603-0004', 'Hợp đồng tư vấn - Minh Phát', 3, 5, 5, 'consulting', 'negotiating', 80000000, 0, 'none', '2026-05-01', '2026-07-31', NULL, 0, 'Thanh toán theo giai đoạn', 'Tư vấn và triển khai CRM 3 tháng', 3, 1),
(1, 'HD-2602-0001', 'Hợp đồng bảo trì hệ thống cũ', 10, 3, NULL, 'maintenance', 'expired', 24000000, 2000000, 'monthly', '2025-03-01', '2026-02-28', '2025-02-25', 0, 'Thanh toán hàng tháng', 'Đã hết hạn, cần gia hạn', 2, 1);

-- ============================================================
-- NGÂN SÁCH (Budgets)
-- ============================================================
INSERT INTO budgets (tenant_id, name, type, period_start, period_end, total_budget, spent_amount, status, department, notes, approved_by, created_by) VALUES
(1, 'Ngân sách Marketing Q2/2026', 'department', '2026-04-01', '2026-06-30', 30000000, 9000000, 'active', 'Marketing', 'Ngân sách marketing quý 2', 1, 4),
(1, 'Ngân sách triển khai ABC Software', 'project', '2026-04-01', '2026-05-31', 20000000, 5000000, 'active', NULL, 'Chi phí triển khai cho ABC', 1, 2),
(1, 'Ngân sách hoạt động tháng 4', 'general', '2026-04-01', '2026-04-30', 15000000, 3500000, 'active', NULL, 'Chi phí vận hành chung tháng 4', 1, 1);

INSERT INTO budget_items (budget_id, category, planned_amount, actual_amount, sort_order) VALUES
(1, 'Facebook Ads', 15000000, 6500000, 1),
(1, 'Email Marketing', 5000000, 2500000, 2),
(1, 'Sự kiện / Webinar', 5000000, 0, 3),
(1, 'Nội dung / Thiết kế', 3000000, 0, 4),
(1, 'Khác', 2000000, 0, 5),
(2, 'Nhân sự triển khai', 10000000, 3000000, 1),
(2, 'Di chuyển / Ăn ở', 5000000, 2000000, 2),
(2, 'Thiết bị / Phần mềm', 3000000, 0, 3),
(2, 'Dự phòng', 2000000, 0, 4),
(3, 'Văn phòng phẩm', 3000000, 1500000, 1),
(3, 'Điện / Nước / Internet', 5000000, 0, 2),
(3, 'Đi lại', 4000000, 2000000, 3),
(3, 'Tiếp khách', 3000000, 0, 4);

-- ============================================================
-- HOA HỒNG (Commissions)
-- ============================================================
INSERT INTO commissions (tenant_id, user_id, rule_id, entity_type, entity_id, amount, base_value, rate, status, period, notes) VALUES
(1, 3, 1, 'deal', 3, 2500000, 50000000, 5.00, 'paid', '2026-03', 'Hoa hồng deal TechViet'),
(1, 2, 2, 'deal', 10, 3960000, 49500000, 8.00, 'approved', '2026-03', 'Hoa hồng deal đào tạo XYZ'),
(1, 2, 1, 'deal', 1, 7500000, 150000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal ABC Software'),
(1, 2, 3, 'order', 1, 1650000, 55000000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn hàng TechViet'),
(1, 2, 3, 'order', 6, 1485000, 49500000, 3.00, 'paid', '2026-03', 'Hoa hồng đơn hàng đào tạo XYZ'),
(1, 3, 1, 'deal', 5, 4000000, 80000000, 5.00, 'pending', '2026-04', 'Hoa hồng deal Minh Phát');
