-- Phase 1.5 chat: per-side last-read timestamps for DM read receipts
ALTER TABLE conversations
    ADD COLUMN `last_read_a_at` DATETIME NULL AFTER unread_a,
    ADD COLUMN `last_read_b_at` DATETIME NULL AFTER unread_b;
