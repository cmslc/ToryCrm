-- Index for fast SKU lookups during Excel import and duplicate checks.
-- Composite with tenant_id for multi-tenant safety; not UNIQUE because some
-- legacy products share SKU across tenants.

ALTER TABLE products ADD INDEX idx_products_tenant_sku (tenant_id, sku);
