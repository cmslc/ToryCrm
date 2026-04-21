CREATE TABLE IF NOT EXISTS order_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    order_id INT UNSIGNED NOT NULL,
    payment_date DATE DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_op_order (order_id)
);
