-- Template version history
CREATE TABLE IF NOT EXISTS `document_template_versions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT UNSIGNED NOT NULL,
    `content` LONGTEXT NOT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`template_id`),
    FOREIGN KEY (`template_id`) REFERENCES `document_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract attachments (phụ lục HĐ)
CREATE TABLE IF NOT EXISTS `contract_attachments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`contract_id`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract signatures (ký điện tử)
CREATE TABLE IF NOT EXISTS `contract_signatures` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `signer_name` VARCHAR(255) NOT NULL,
    `signer_email` VARCHAR(255) DEFAULT NULL,
    `signer_role` VARCHAR(50) DEFAULT 'customer' COMMENT 'customer, company',
    `signature_data` LONGTEXT DEFAULT NULL COMMENT 'Base64 signature image',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `signed_at` DATETIME DEFAULT NULL,
    `token` VARCHAR(100) DEFAULT NULL,
    `status` VARCHAR(30) DEFAULT 'pending' COMMENT 'pending, signed, declined',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY (`contract_id`),
    UNIQUE KEY (`token`),
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
