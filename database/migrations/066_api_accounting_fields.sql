-- Phase: accounting API support
-- Add tenant_id + updated_at where missing, to enable:
--  (a) tenant-scoped API queries
--  (b) incremental sync via WHERE updated_at >= ? from the accounting team

-- 1. tenant_id on leaf tables — backfilled from parent rows

ALTER TABLE fund_accounts
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_fa_tenant (tenant_id);
UPDATE fund_accounts SET tenant_id = 1 WHERE tenant_id IS NULL;

ALTER TABLE order_items
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_oi_tenant (tenant_id);
UPDATE order_items oi
    JOIN orders o ON oi.order_id = o.id
    SET oi.tenant_id = o.tenant_id
WHERE oi.tenant_id IS NULL;

ALTER TABLE debt_payments
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_dp_tenant (tenant_id);
UPDATE debt_payments dp
    JOIN debts d ON dp.debt_id = d.id
    SET dp.tenant_id = d.tenant_id
WHERE dp.tenant_id IS NULL;

ALTER TABLE purchase_order_items
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_poi_tenant (tenant_id);
UPDATE purchase_order_items poi
    JOIN purchase_orders po ON poi.purchase_order_id = po.id
    SET poi.tenant_id = po.tenant_id
WHERE poi.tenant_id IS NULL;

-- 2. updated_at where missing — for incremental sync filters

ALTER TABLE order_items
    ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE payrolls
    ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE debt_payments
    ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE purchase_order_items
    ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE product_categories
    ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
