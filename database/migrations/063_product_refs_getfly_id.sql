-- Add tenant_id + getfly_id to product reference tables
-- and getfly_id to products for traceability.
-- Safe to re-run (checks for column existence via separate checks).

ALTER TABLE product_categories
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN getfly_id INT UNSIGNED NULL AFTER tenant_id,
    ADD INDEX idx_pc_tenant (tenant_id),
    ADD UNIQUE KEY uq_pc_tenant_getfly (tenant_id, getfly_id);

ALTER TABLE product_manufacturers
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN getfly_id INT UNSIGNED NULL AFTER tenant_id,
    ADD INDEX idx_pm_tenant (tenant_id),
    ADD UNIQUE KEY uq_pm_tenant_getfly (tenant_id, getfly_id);

ALTER TABLE product_origins
    ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN getfly_id INT UNSIGNED NULL AFTER tenant_id,
    ADD INDEX idx_po_tenant (tenant_id),
    ADD UNIQUE KEY uq_po_tenant_getfly (tenant_id, getfly_id);

ALTER TABLE products
    ADD COLUMN getfly_id INT UNSIGNED NULL AFTER id,
    ADD INDEX idx_products_getfly (tenant_id, getfly_id);
