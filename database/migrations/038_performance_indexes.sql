-- Contacts
CREATE INDEX idx_contacts_search ON contacts(tenant_id, is_deleted, status);
CREATE INDEX idx_contacts_owner ON contacts(owner_id);
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_created ON contacts(created_at);

-- Deals
CREATE INDEX idx_deals_tenant_status ON deals(tenant_id, status);
CREATE INDEX idx_deals_owner ON deals(owner_id);

-- Tasks
CREATE INDEX idx_tasks_assigned ON tasks(assigned_to, is_deleted, status);
CREATE INDEX idx_tasks_due ON tasks(due_date);

-- Fund transactions
CREATE INDEX idx_fund_tenant_date ON fund_transactions(tenant_id, transaction_date);

-- Debts
CREATE INDEX idx_debts_tenant_type ON debts(tenant_id, type, status);
CREATE INDEX idx_debts_due ON debts(due_date);

-- Email messages
CREATE INDEX idx_email_sent ON email_messages(sent_at);

-- Activities
CREATE INDEX idx_activities_user ON activities(user_id, created_at);

-- Logistics
CREATE INDEX idx_pkg_tracking ON logistics_packages(tracking_code);
CREATE INDEX idx_lorder_tenant ON logistics_orders(tenant_id, status);

-- Payrolls
CREATE INDEX idx_payroll_period ON payrolls(tenant_id, month, year);
