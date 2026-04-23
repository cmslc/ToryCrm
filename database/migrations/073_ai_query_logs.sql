-- Audit log for AI assistant queries. Records what context was shared
-- with external LLM providers for compliance / privacy review.

CREATE TABLE IF NOT EXISTS `ai_query_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,
    `user_id` INT UNSIGNED NULL,
    `provider` VARCHAR(50) NULL,
    `message_len` INT UNSIGNED DEFAULT 0,
    `context_included` TINYINT(1) DEFAULT 0,
    `context_size` INT UNSIGNED DEFAULT 0,
    `context_preview` TEXT NULL,
    `response_len` INT UNSIGNED DEFAULT 0,
    `response_time_ms` INT UNSIGNED DEFAULT 0,
    `error` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_created (tenant_id, created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
