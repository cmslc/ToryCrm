-- Write-back fields populated by KT Accounting after posting the invoice.
-- KT calls POST /api/v1/order/accounting-update with these three values
-- so the CRM order page can show the VAT invoice number + reconcile status.

ALTER TABLE orders
    ADD COLUMN vat_invoice_number   VARCHAR(50)  NULL AFTER payment_status,
    ADD COLUMN accounting_synced_at DATETIME     NULL AFTER vat_invoice_number,
    ADD COLUMN accounting_entity    VARCHAR(100) NULL AFTER accounting_synced_at,
    ADD INDEX idx_vat_invoice (vat_invoice_number);
