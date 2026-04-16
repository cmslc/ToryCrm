ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_percent DECIMAL(5,2) DEFAULT 0 AFTER shipping_fee;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS shipping_after_tax TINYINT(1) DEFAULT 0 AFTER shipping_percent;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) DEFAULT 0 AFTER discount_amount;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS discount_after_tax TINYINT(1) DEFAULT 0 AFTER discount_percent;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS tax_rate DECIMAL(5,2) DEFAULT 0 AFTER tax_amount;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS installation_fee DECIMAL(15,2) DEFAULT 0 AFTER shipping_note;
ALTER TABLE quotations ADD COLUMN IF NOT EXISTS installation_percent DECIMAL(5,2) DEFAULT 0 AFTER installation_fee;
