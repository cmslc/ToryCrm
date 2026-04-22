ALTER TABLE automation_rules ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id, ADD INDEX idx_ar_tenant (tenant_id);
UPDATE automation_rules ar JOIN users u ON u.id = ar.created_by SET ar.tenant_id = u.tenant_id WHERE ar.tenant_id IS NULL;
UPDATE automation_rules SET tenant_id = 1 WHERE tenant_id IS NULL;
