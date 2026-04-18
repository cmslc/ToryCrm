ALTER TABLE contacts ADD COLUMN full_name VARCHAR(255) DEFAULT NULL AFTER last_name;
UPDATE contacts SET full_name = TRIM(CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''))) WHERE full_name IS NULL OR full_name = '';
