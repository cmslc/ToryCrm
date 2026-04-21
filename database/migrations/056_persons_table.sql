-- Phase 1: Schema — Global persons + employment-style contact_persons

CREATE TABLE IF NOT EXISTS persons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    gender ENUM('male','female','other') NULL,
    date_of_birth DATE NULL,
    avatar VARCHAR(255) NULL,
    note TEXT NULL,
    is_hidden TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _origin_cp_id INT UNSIGNED NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_phone (tenant_id, phone),
    INDEX idx_email (tenant_id, email),
    INDEX idx_origin (_origin_cp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE contact_persons
    ADD COLUMN person_id INT UNSIGNED NULL AFTER tenant_id,
    ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER is_primary,
    ADD COLUMN start_date DATE NULL AFTER is_active,
    ADD COLUMN end_date DATE NULL AFTER start_date,
    ADD INDEX idx_person (person_id);

-- Phase 2: Backfill — each existing contact_person gets its own person row
-- (KHÔNG auto-merge theo SĐT để tránh gộp nhầm 2 người share SĐT)

-- Clean any NULL tenant_id trước khi insert (FK invariant)
UPDATE contact_persons cp JOIN contacts c ON c.id = cp.contact_id SET cp.tenant_id = c.tenant_id WHERE cp.tenant_id IS NULL;
UPDATE contact_persons SET tenant_id = 1 WHERE tenant_id IS NULL;

INSERT INTO persons (tenant_id, full_name, phone, email, gender, date_of_birth, note, created_at, updated_at, _origin_cp_id)
SELECT tenant_id, full_name, phone, email, gender, date_of_birth, note, created_at, updated_at, id
FROM contact_persons;

UPDATE contact_persons cp
JOIN persons p ON p._origin_cp_id = cp.id
SET cp.person_id = p.id;

-- Drop temporary tracking column
ALTER TABLE persons DROP COLUMN _origin_cp_id;
