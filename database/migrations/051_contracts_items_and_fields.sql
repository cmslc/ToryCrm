ALTER TABLE contracts ADD COLUMN contact_name VARCHAR(255) DEFAULT NULL AFTER company_id;
ALTER TABLE contracts ADD COLUMN subtotal DECIMAL(15,2) DEFAULT 0 AFTER value;
ALTER TABLE contracts ADD COLUMN discount_amount DECIMAL(15,2) DEFAULT 0 AFTER subtotal;
ALTER TABLE contracts ADD COLUMN shipping_fee DECIMAL(15,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE contracts ADD COLUMN installation_fee DECIMAL(15,2) DEFAULT 0 AFTER shipping_fee;
ALTER TABLE contracts ADD COLUMN tax_amount DECIMAL(15,2) DEFAULT 0 AFTER installation_fee;
ALTER TABLE contracts ADD COLUMN actual_value DECIMAL(15,2) DEFAULT 0 AFTER tax_amount;
ALTER TABLE contracts ADD COLUMN executed_amount DECIMAL(15,2) DEFAULT 0 AFTER actual_value;
ALTER TABLE contracts ADD COLUMN paid_amount DECIMAL(15,2) DEFAULT 0 AFTER executed_amount;
ALTER TABLE contracts ADD COLUMN installation_address TEXT DEFAULT NULL AFTER paid_amount;

CREATE TABLE IF NOT EXISTS contract_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit VARCHAR(50) DEFAULT NULL,
    unit_price DECIMAL(15,2) DEFAULT 0,
    cost_price DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    KEY (contract_id),
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
