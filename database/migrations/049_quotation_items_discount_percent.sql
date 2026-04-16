ALTER TABLE quotation_items ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER tax_rate;
