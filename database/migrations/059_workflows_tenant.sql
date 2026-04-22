ALTER TABLE workflows ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id, ADD INDEX idx_workflow_tenant (tenant_id);
UPDATE workflows w JOIN users u ON u.id = w.created_by SET w.tenant_id = u.tenant_id WHERE w.tenant_id IS NULL;
UPDATE workflows SET tenant_id = 1 WHERE tenant_id IS NULL;
