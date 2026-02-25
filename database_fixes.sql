-- =====================================================
-- PRMS DATABASE SCHEMA FIXES
-- Database Name: u153072617_prms
-- Generated: 2026-02-17
-- =====================================================

-- =====================================================
-- PHASE 1: CRITICAL FIXES (Must execute in order)
-- =====================================================

-- Check current database
-- SELECT DATABASE();

USE `u153072617_prms`;

-- =====================================================
-- 1. CREATE MISSING TABLE: compliance_approvals
-- =====================================================
-- This table is referenced in dashboard/compliance.php but doesn't exist

CREATE TABLE IF NOT EXISTS `compliance_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL DEFAULT 'procurement_request',
  `approval_body` varchar(100),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entity_type` (`entity_type`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_status` (`status`),
  KEY `idx_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Compliance approval tracking for procurement requests';

-- =====================================================
-- 2. CREATE MISSING TABLE: system_config
-- =====================================================
-- This table is referenced in config/workflow.php and procurement/add.php

CREATE TABLE IF NOT EXISTS `system_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` varchar(255),
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uq_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System configuration parameters';

-- =====================================================
-- 3. INSERT DEFAULT SYSTEM CONFIGURATION
-- =====================================================

INSERT INTO `system_config` (config_key, config_value, description) VALUES
('petty_cash_limit', '5000', 'Maximum amount for petty cash procurement without formal approval (JMD)'),
('direct_procurement_threshold', '500000', 'Threshold value for direct procurement eligibility (JMD)'),
('enable_notifications', '1', 'Enable/disable email notifications (1=enabled, 0=disabled)')
ON DUPLICATE KEY UPDATE config_value=VALUES(config_value);

-- =====================================================
-- 4. ADD MISSING COLUMN: request_type
-- =====================================================
-- This column is referenced in multiple dashboard files

-- Check if column exists before adding
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME='procurement_requests' AND COLUMN_NAME='request_type';

ALTER TABLE `procurement_requests` 
ADD COLUMN IF NOT EXISTS `request_type` ENUM('REGULAR', 'REIMBURSEMENT', 'PETTY_CASH') DEFAULT 'REGULAR' 
AFTER `description`;

-- =====================================================
-- 5. ADD MISSING ROLES
-- =====================================================
-- These roles are defined in config/app.php but missing from the roles table

INSERT INTO `roles` (id, name, description, created_at) VALUES
(10, 'Director HRM&A', 'Director of Human Resource Management and Administration', NOW()),
(11, 'Director Procurement', 'Director of Procurement Operations', NOW()),
(12, 'Requestor', 'Employee submitting procurement requests', NOW())
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- =====================================================
-- PHASE 2: INDEX CLEANUP (Remove duplicates)
-- =====================================================

-- =====================================================
-- 6. FIX: branches TABLE - Remove duplicate unique index
-- =====================================================
ALTER TABLE `branches` 
DROP INDEX IF EXISTS `branch_name`;

-- =====================================================
-- 7. FIX: invoices TABLE - Remove duplicate unique index
-- =====================================================
ALTER TABLE `invoices` 
DROP INDEX IF EXISTS `invoice_number`;

-- =====================================================
-- 8. FIX: payments TABLE - Remove duplicate unique index
-- =====================================================
ALTER TABLE `payments` 
DROP INDEX IF EXISTS `payment_reference`;

-- =====================================================
-- 9. FIX: procurement_requests TABLE - Remove duplicate indexes
-- =====================================================
ALTER TABLE `procurement_requests` 
DROP INDEX IF EXISTS `request_number_4`,
DROP INDEX IF EXISTS `request_number_5`;

-- =====================================================
-- 10. FIX: purchase_orders TABLE - Fix duplicate unique constraint
-- =====================================================
-- The uq_po_per_commitment should be the only one
ALTER TABLE `purchase_orders` 
DROP INDEX IF EXISTS `commitment_id`;

-- =====================================================
-- 11. FIX: users TABLE - Remove duplicate unique index
-- =====================================================
ALTER TABLE `users` 
DROP INDEX IF EXISTS `uq_user_email`;

-- =====================================================
-- PHASE 3: CODE FIX VERIFICATION
-- =====================================================
-- These queries help verify the fixes are correct
-- Run these manually after executing the fixes above

-- Check compliance_approvals table exists
-- SELECT * FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA='u153072617_prms' AND TABLE_NAME='compliance_approvals';

-- Check system_config table exists and has data
-- SELECT * FROM system_config;

-- Check procurement_requests has request_type column
-- DESCRIBE procurement_requests;

-- Check all roles exist
-- SELECT id, name FROM roles ORDER BY id;

-- =====================================================
-- PHASE 4: OPTIONAL - ADD FOREIGN KEY CONSTRAINTS
-- =====================================================
-- These improve data integrity (optional but recommended)

-- Add foreign key for compliance_approvals if it references procurement_requests
-- ALTER TABLE `compliance_approvals`
-- ADD CONSTRAINT `fk_compliance_approvals_procurement` 
-- FOREIGN KEY (`entity_id`) REFERENCES `procurement_requests` (`request_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- =====================================================
-- VERIFICATION QUERIES (Run manually after fixes)
-- =====================================================

-- Verify all tables are created
-- SELECT 'Checking created tables...' as check_status;
-- SELECT COUNT(*) as total_tables FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA='u153072617_prms';

-- Verify compliance_approvals exists
-- SELECT 'Compliance Approvals Table' as verification, COUNT(*) as record_count FROM compliance_approvals;

-- Verify system_config has data
-- SELECT 'System Config' as verification, COUNT(*) as record_count FROM system_config;

-- Verify request_type column exists
-- SELECT 'Request Type Column exists' as verification, COLUMN_NAME, COLUMN_TYPE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME='procurement_requests' AND COLUMN_NAME='request_type';

-- Verify all roles are present
-- SELECT 'Total Roles' as verification, COUNT(*) as role_count FROM roles;
-- SELECT 'New roles added' as verification, id, name FROM roles WHERE id IN (10, 11, 12);

-- =====================================================
-- NOTES
-- =====================================================
/*
IMPORTANT:
1. Execute this script in order from top to bottom
2. If you get errors about table already existing, it's safe to ignore (using IF NOT EXISTS)
3. Verify no errors occur during index cleanup
4. Test the application after running these fixes
5. Consider running a full backup before executing these changes

NEXT STEPS:
1. Update database name in config/db.php if needed: u153072617_dgc_procure_sy → u153072617_prms
2. Fix column references in dashboard/compliance.php:
   - pr.id → pr.request_id
   - pr.title → pr.description
3. Test all dashboards that reference request_type
4. Run application smoke tests
*/
