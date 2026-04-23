-- Allow multiple orders from one quotation. Reverse reference so each
-- order knows its source quote. quotations.converted_order_id is kept
-- (points at the first order) to keep legacy code paths working.

ALTER TABLE orders
    ADD COLUMN quotation_id INT UNSIGNED NULL AFTER deal_id,
    ADD INDEX idx_orders_quotation (quotation_id);

-- Backfill: copy the existing 1:1 links
UPDATE orders o
    JOIN quotations q ON q.converted_order_id = o.id
    SET o.quotation_id = q.id
WHERE o.quotation_id IS NULL;
