-- Track which quotation a contract was generated from so /quotations/{id}
-- can show the list of contracts created from it (mirror of migration 071
-- for orders).

ALTER TABLE contracts
    ADD COLUMN quotation_id INT UNSIGNED NULL AFTER contract_number,
    ADD INDEX idx_contracts_quotation (quotation_id);
