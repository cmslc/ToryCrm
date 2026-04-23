-- Internal employee-to-employee DM on top of the existing conversations table.
-- Uses the already-defined channel='internal' enum; adds DM pair columns.

ALTER TABLE conversations
    ADD COLUMN user_a_id INT UNSIGNED NULL AFTER contact_id,
    ADD COLUMN user_b_id INT UNSIGNED NULL AFTER user_a_id,
    ADD COLUMN unread_a   INT UNSIGNED NOT NULL DEFAULT 0 AFTER unread_count,
    ADD COLUMN unread_b   INT UNSIGNED NOT NULL DEFAULT 0 AFTER unread_a,
    ADD INDEX idx_dm_pair (tenant_id, channel, user_a_id, user_b_id);
