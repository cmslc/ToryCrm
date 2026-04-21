ALTER TABLE users
    ADD COLUMN totp_secret VARCHAR(128) NULL,
    ADD COLUMN totp_enabled TINYINT(1) DEFAULT 0,
    ADD COLUMN totp_backup_codes TEXT NULL,
    ADD COLUMN totp_enabled_at DATETIME NULL;
