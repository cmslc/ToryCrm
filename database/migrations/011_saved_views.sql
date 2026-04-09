-- Saved Views
CREATE TABLE IF NOT EXISTS saved_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL DEFAULT 1,
    user_id INT NOT NULL,
    module VARCHAR(50) NOT NULL COMMENT 'contacts, deals, orders, tasks, tickets',
    name VARCHAR(100) NOT NULL,
    filters JSON NULL,
    columns JSON NULL,
    sort_by VARCHAR(50) NULL,
    sort_dir VARCHAR(4) DEFAULT 'DESC',
    is_shared TINYINT(1) DEFAULT 0,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_saved_views_user_module (user_id, module),
    INDEX idx_saved_views_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
