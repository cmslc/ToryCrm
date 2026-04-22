-- Add dimensions + color fields (not exposed by Getfly API v3, only via Excel export)

ALTER TABLE products
    ADD COLUMN dimensions VARCHAR(255) NULL AFTER weight,
    ADD COLUMN color VARCHAR(100) NULL AFTER dimensions;
