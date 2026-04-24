-- Composite indexes for listing filters (tenant_id + status/state + created_at DESC)
-- Use IF NOT EXISTS workaround via ALTER IGNORE-like pattern: CREATE INDEX is idempotent if name differs, otherwise will fail. We name distinctly.

-- Quotations: status filter + date sort (30k+ rows)
ALTER TABLE quotations
  ADD INDEX idx_quot_tenant_status_created (tenant_id, status, created_at DESC);

-- Orders: similar
ALTER TABLE orders
  ADD INDEX idx_orders_tenant_status_created (tenant_id, status, created_at DESC);

-- Contacts: tenant + deleted + last-interaction sort
ALTER TABLE contacts
  ADD INDEX idx_contacts_tenant_deleted_updated (tenant_id, is_deleted, updated_at DESC);

-- Tasks: tenant + assigned + status for kanban queries
ALTER TABLE tasks
  ADD INDEX idx_tasks_tenant_assigned_status (tenant_id, assigned_to, status);

-- Activities: feed queries per user
ALTER TABLE activities
  ADD INDEX idx_activities_tenant_user_created (tenant_id, user_id, created_at DESC);

-- Deals: tenant + stage filter
ALTER TABLE deals
  ADD INDEX idx_deals_tenant_stage_status (tenant_id, stage_id, status);
