-- SLA Policies table
CREATE TABLE IF NOT EXISTS sla_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    first_response_hours DECIMAL(10,2) NOT NULL DEFAULT 4,
    resolution_hours DECIMAL(10,2) NOT NULL DEFAULT 24,
    escalate_to INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escalate_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add SLA columns to tickets table
ALTER TABLE tickets
    ADD COLUMN sla_policy_id INT NULL AFTER due_date,
    ADD COLUMN sla_first_response_due DATETIME NULL AFTER sla_policy_id,
    ADD COLUMN sla_resolution_due DATETIME NULL AFTER sla_first_response_due,
    ADD COLUMN first_response_at DATETIME NULL AFTER sla_resolution_due,
    ADD COLUMN sla_breached TINYINT(1) NOT NULL DEFAULT 0 AFTER first_response_at,
    ADD CONSTRAINT fk_tickets_sla_policy FOREIGN KEY (sla_policy_id) REFERENCES sla_policies(id) ON DELETE SET NULL;
