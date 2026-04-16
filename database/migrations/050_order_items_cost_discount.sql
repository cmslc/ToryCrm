ALTER TABLE order_items ADD COLUMN cost_price DECIMAL(15,2) DEFAULT 0 AFTER unit_price;
ALTER TABLE order_items ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER tax_amount;
