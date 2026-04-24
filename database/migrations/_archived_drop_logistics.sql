-- ARCHIVED: chạy thủ công để xóa toàn bộ data Kho Logistics
-- Hiện tại 23 packages / 22 orders / 1 shipment đang nằm trong các bảng này.
-- Chạy bằng: mysql torycrm < _archived_drop_logistics.sql
-- File prefix `_archived_` để không auto-load vào migration runner.

DROP TABLE IF EXISTS logistics_scan_logs;
DROP TABLE IF EXISTS logistics_status_history;
DROP TABLE IF EXISTS logistics_shipment_packages;
DROP TABLE IF EXISTS logistics_shipment_bags;
DROP TABLE IF EXISTS logistics_package_orders;
DROP TABLE IF EXISTS logistics_deliveries;
DROP TABLE IF EXISTS logistics_shipments;
DROP TABLE IF EXISTS logistics_bags;
DROP TABLE IF EXISTS logistics_orders;
DROP TABLE IF EXISTS logistics_packages;
DROP TABLE IF EXISTS logistics_shipping_rates;
