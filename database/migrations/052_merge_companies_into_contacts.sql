-- Add company fields directly to contacts table
ALTER TABLE contacts
    ADD COLUMN company_name VARCHAR(255) DEFAULT NULL AFTER last_name,
    ADD COLUMN company_phone VARCHAR(20) DEFAULT NULL AFTER company_name,
    ADD COLUMN company_email VARCHAR(150) DEFAULT NULL AFTER company_phone,
    ADD COLUMN industry VARCHAR(100) DEFAULT NULL AFTER company_email,
    ADD COLUMN company_size VARCHAR(50) DEFAULT NULL AFTER industry;

-- Create contact_persons table for sub-contacts
CREATE TABLE IF NOT EXISTS contact_persons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT NULL,
    contact_id INT UNSIGNED NOT NULL,
    title VARCHAR(20) DEFAULT NULL,
    full_name VARCHAR(200) NOT NULL,
    position VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    note TEXT DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    receive_email TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_cp_contact (contact_id),
    INDEX idx_cp_tenant (tenant_id)
);

-- Migrate: copy company data into contacts
UPDATE contacts c
INNER JOIN companies comp ON c.company_id = comp.id
SET c.company_name = comp.name,
    c.company_phone = CASE WHEN comp.phone != c.phone THEN comp.phone ELSE NULL END,
    c.company_email = CASE WHEN comp.email != c.email THEN comp.email ELSE NULL END,
    c.industry = comp.industry,
    c.company_size = comp.company_size;

-- For contacts without company: use full name as company_name
UPDATE contacts SET company_name = CONCAT(COALESCE(last_name, ''), ' ', first_name)
WHERE company_id IS NULL AND company_name IS NULL;
