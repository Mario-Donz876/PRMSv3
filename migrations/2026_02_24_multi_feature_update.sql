-- ============================================================
-- Migration: Multi-Feature Update
-- Date: 2026-02-24
-- Features:
--   1. Audit log: changed_by stores full_name (varchar) instead of user_id (int)
--   2. Currency support: USD/JMD with exchange rate
--   3. RFQ: date, deadline, and letter upload
--   4. Request document uploads (signed POs, commitments)
-- ============================================================

-- ============================================
-- 1. AUDIT LOG: Change changed_by to store full_name
-- ============================================
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'audit_log' 
  AND COLUMN_NAME = 'changed_by' AND DATA_TYPE = 'varchar');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `audit_log` MODIFY COLUMN `changed_by` VARCHAR(100) DEFAULT NULL COMMENT ''Full name of user who made the change''',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing audit_log entries to store full_name instead of user_id
UPDATE `audit_log` a
  LEFT JOIN `users` u ON a.changed_by = CAST(u.user_id AS CHAR)
  SET a.changed_by = u.full_name
  WHERE a.changed_by IS NOT NULL
    AND a.changed_by REGEXP '^[0-9]+$';

-- ============================================
-- 2. CURRENCY SUPPORT
-- ============================================

-- Add currency fields to procurement_requests
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'procurement_requests' AND COLUMN_NAME = 'currency');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `procurement_requests` ADD COLUMN `currency` ENUM(''JMD'',''USD'') NOT NULL DEFAULT ''JMD'' AFTER `estimated_value`, ADD COLUMN `usd_rate` DECIMAL(10,4) DEFAULT NULL COMMENT ''USD to JMD exchange rate at time of request'' AFTER `currency`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add currency fields to rfq_quotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rfq_quotes' AND COLUMN_NAME = 'currency');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `rfq_quotes` ADD COLUMN `currency` ENUM(''JMD'',''USD'') NOT NULL DEFAULT ''JMD'' AFTER `quote_amount`, ADD COLUMN `usd_rate` DECIMAL(10,4) DEFAULT NULL COMMENT ''USD to JMD exchange rate'' AFTER `currency`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add USD exchange rate to system_config
INSERT INTO `system_config` (`config_key`, `config_value`, `description`, `created_at`)
VALUES ('usd_to_jmd_rate', '155.00', 'Current USD to JMD exchange rate for currency conversion', NOW())
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

-- ============================================
-- 3. FINANCE WORKFLOW: Funds verification fields
-- ============================================
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'procurement_requests' AND COLUMN_NAME = 'funds_available');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `procurement_requests` ADD COLUMN `funds_available` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = Finance verified funds are available'' AFTER `status`, ADD COLUMN `finance_reviewed_by` INT(11) DEFAULT NULL COMMENT ''Finance Officer who verified funds'' AFTER `funds_available`, ADD COLUMN `finance_reviewed_at` DATETIME DEFAULT NULL COMMENT ''When funds were verified'' AFTER `finance_reviewed_by`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 4. RFQ: Letter upload field (rfq_letter_file)
-- ============================================
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rfqs' AND COLUMN_NAME = 'rfq_letter_file');
SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `rfqs` ADD COLUMN `rfq_letter_file` VARCHAR(255) DEFAULT NULL COMMENT ''Uploaded RFQ letter document path'' AFTER `letter_of_award_file`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 5. REQUEST DOCUMENT UPLOADS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `request_documents` (
  `document_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `document_type` ENUM('SIGNED_PO','SIGNED_COMMITMENT','OTHER') NOT NULL DEFAULT 'OTHER',
  `document_name` VARCHAR(255) NOT NULL COMMENT 'Original filename',
  `document_path` VARCHAR(255) NOT NULL COMMENT 'Server file path',
  `uploaded_by` INT(11) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  KEY `idx_request_documents_request` (`request_id`),
  KEY `idx_request_documents_type` (`document_type`),
  CONSTRAINT `fk_request_documents_request` FOREIGN KEY (`request_id`) REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Done
-- ============================================
