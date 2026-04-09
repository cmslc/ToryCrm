-- Commission Rules
CREATE TABLE IF NOT EXISTS commission_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(15, 2) NOT NULL DEFAULT 0,
    apply_to ENUM('deal', 'order') NOT NULL DEFAULT 'deal',
    min_value DECIMAL(15, 2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_apply_to (apply_to, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commissions
CREATE TABLE IF NOT EXISTS commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    user_id INT NOT NULL,
    entity_type ENUM('deal', 'order') NOT NULL,
    entity_id INT NOT NULL,
    rule_id INT DEFAULT NULL,
    base_amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    rate DECIMAL(10, 2) NOT NULL DEFAULT 0,
    rate_type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'approved', 'paid') NOT NULL DEFAULT 'pending',
    approved_at DATETIME DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    FOREIGN KEY (rule_id) REFERENCES commission_rules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
