-- =====================================================
-- PRMS DATABASE MIGRATION: REIMBURSEMENT & PETTY CASH WORKFLOWS
-- Database Name: u153072617_prms
-- Generated: 2026-02-17
-- Purpose: Add tables for reimbursement and petty cash request workflows
-- =====================================================

USE `u153072617_prms`;

-- =====================================================
-- TABLE 1: pre_authorizations
-- Purpose: Track prior authorization for reimbursement requests
-- =====================================================
CREATE TABLE IF NOT EXISTS `pre_authorizations` (
  `auth_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `authorized_by` int(11) NOT NULL,
  `authorization_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `authorization_amount` decimal(15,2) NOT NULL,
  `authorization_notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `uq_request_id` (`request_id`),
  KEY `idx_authorized_by` (`authorized_by`),
  KEY `idx_authorization_date` (`authorization_date`),
  CONSTRAINT `fk_pre_auth_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pre_auth_user` FOREIGN KEY (`authorized_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Prior authorization for reimbursement requests (Branch Head approval)';

-- =====================================================
-- TABLE 2: reimbursement_invoices
-- Purpose: Track invoices submitted for reimbursement
-- =====================================================
CREATE TABLE IF NOT EXISTS `reimbursement_invoices` (
  `reimb_invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `invoice_stage` enum('COPY_TO_PROCUREMENT','ORIGINAL_TO_FINANCE') DEFAULT 'COPY_TO_PROCUREMENT',
  `invoice_amount` decimal(15,2) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `submitted_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `verified_by` int(11) DEFAULT NULL,
  `procurement_verified_date` datetime DEFAULT NULL,
  `verification_notes` text,
  `goods_service_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reimb_invoice_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_invoice_stage` (`invoice_stage`),
  KEY `idx_submitted_by` (`submitted_by`),
  KEY `idx_verified_by` (`verified_by`),
  CONSTRAINT `fk_reimb_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reimb_invoice` FOREIGN KEY (`invoice_id`) 
    REFERENCES `invoices` (`invoice_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reimb_submitted_by` FOREIGN KEY (`submitted_by`) 
    REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_reimb_verified_by` FOREIGN KEY (`verified_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Invoice tracking for reimbursement requests (GC2 copy and GC10A original)';

-- =====================================================
-- TABLE 3: procurement_verifications
-- Purpose: Track procurement verification of goods/services
-- =====================================================
CREATE TABLE IF NOT EXISTS `procurement_verifications` (
  `verification_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `verification_type` enum('GOODS_RECEIVED','SERVICE_RENDERED','PETTY_CASH_PURCHASED') DEFAULT 'GOODS_RECEIVED',
  `verified_by` int(11) NOT NULL,
  `verification_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `condition_status` enum('SATISFACTORY','DEFECTIVE','INCOMPLETE','OTHER') DEFAULT 'SATISFACTORY',
  `verification_notes` text,
  `verification_documents` varchar(500),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`verification_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_verified_by` (`verified_by`),
  KEY `idx_verification_date` (`verification_date`),
  CONSTRAINT `fk_verify_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_verify_user` FOREIGN KEY (`verified_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Procurement verification of goods received or services rendered';

-- =====================================================
-- TABLE 4: petty_cash_disbursements
-- Purpose: Track petty cash disbursement transactions
-- =====================================================
CREATE TABLE IF NOT EXISTS `petty_cash_disbursements` (
  `disburse_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `amount_authorized` decimal(15,2) NOT NULL,
  `disbursed_by` int(11) NOT NULL,
  `disbursement_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `disbursement_deadline` datetime NOT NULL,
  `status` enum('AUTHORIZED','DISBURSED','RECONCILED','VERIFIED','COMPLETED') DEFAULT 'AUTHORIZED',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`disburse_id`),
  UNIQUE KEY `uq_request_disburse` (`request_id`),
  KEY `idx_disbursed_by` (`disbursed_by`),
  KEY `idx_disbursement_date` (`disbursement_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_disburse_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_disburse_user` FOREIGN KEY (`disbursed_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Petty cash disbursement tracking with 24-hour accountability';

-- =====================================================
-- TABLE 5: petty_cash_reconciliations
-- Purpose: Track petty cash reconciliation (purchases, change returns)
-- =====================================================
CREATE TABLE IF NOT EXISTS `petty_cash_reconciliations` (
  `reconcile_id` int(11) NOT NULL AUTO_INCREMENT,
  `disburse_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `purchase_amount` decimal(15,2) NOT NULL,
  `change_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `submitted_by` int(11) NOT NULL,
  `submission_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `submission_deadline_met` tinyint(1) DEFAULT 0,
  `hours_from_disbursement` decimal(4,2) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verification_date` datetime DEFAULT NULL,
  `reconciliation_notes` text,
  `status` enum('PENDING_VERIFICATION','VERIFIED','DISCREPANCY','APPROVED') DEFAULT 'PENDING_VERIFICATION',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reconcile_id`),
  UNIQUE KEY `uq_disburse_reconcile` (`disburse_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_submitted_by` (`submitted_by`),
  KEY `idx_submission_date` (`submission_date`),
  KEY `idx_verified_by` (`verified_by`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_reconcile_disburse` FOREIGN KEY (`disburse_id`) 
    REFERENCES `petty_cash_disbursements` (`disburse_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reconcile_invoice` FOREIGN KEY (`invoice_id`) 
    REFERENCES `invoices` (`invoice_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reconcile_submitted_by` FOREIGN KEY (`submitted_by`) 
    REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_reconcile_verified_by` FOREIGN KEY (`verified_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Petty cash reconciliation - tracks purchases, change, and verification';

-- =====================================================
-- TABLE 6: reimbursement_statuses_history
-- Purpose: Track historical status changes for reimbursement requests
-- =====================================================
CREATE TABLE IF NOT EXISTS `reimbursement_status_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `change_notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_changed_by` (`changed_by`),
  KEY `idx_change_date` (`change_date`),
  CONSTRAINT `fk_reimb_status_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reimb_status_user` FOREIGN KEY (`changed_by`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Historical record of reimbursement request status changes';

-- =====================================================
-- TABLE 7: workflow_notifications
-- Purpose: Track and queue workflow notifications for each request type
-- =====================================================
CREATE TABLE IF NOT EXISTS `workflow_notifications` (
  `notif_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `request_type` enum('REGULAR','REIMBURSEMENT','PETTY_CASH') DEFAULT 'REGULAR',
  `notification_type` enum('PENDING_AUTHORIZATION','PENDING_VERIFICATION','DEADLINE_APPROACHING','DEADLINE_EXCEEDED','STATUS_UPDATE') DEFAULT 'STATUS_UPDATE',
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notif_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_recipient_id` (`recipient_id`),
  KEY `idx_is_sent` (`is_sent`),
  KEY `idx_request_type` (`request_type`),
  CONSTRAINT `fk_notif_request` FOREIGN KEY (`request_id`) 
    REFERENCES `procurement_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_recipient` FOREIGN KEY (`recipient_id`) 
    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Workflow notifications for reimbursement and petty cash deadlines/status';

-- =====================================================
-- UPDATES TO EXISTING TABLES
-- =====================================================

-- Add reimbursement-specific status to procurement_requests status enum if not exists
-- ALTER TABLE `procurement_requests` MODIFY COLUMN `status` varchar(50) NOT NULL DEFAULT 'DRAFT';

-- =====================================================
-- INDEXES FOR COMMON QUERIES
-- =====================================================

-- Reimbursement query optimization
CREATE INDEX IF NOT EXISTS idx_reimb_request_type 
ON procurement_requests(request_type, status, created_at DESC);

CREATE INDEX IF NOT EXISTS idx_petty_cash_deadline 
ON petty_cash_disbursements(disbursement_deadline, status);

CREATE INDEX IF NOT EXISTS idx_reconcile_deadline 
ON petty_cash_reconciliations(submission_date, status);

-- =====================================================
-- SAMPLE DATA / DEFAULTS (OPTIONAL)
-- =====================================================

-- Insert default workflow status values if using strict status checking
-- (Optional - only if you want to track allowed statuses)

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- To verify all tables created successfully, run:
-- SELECT TABLE_NAME FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA='u153072617_prms' 
-- AND TABLE_NAME IN ('pre_authorizations','reimbursement_invoices','procurement_verifications',
--                     'petty_cash_disbursements','petty_cash_reconciliations',
--                     'reimbursement_status_history','workflow_notifications');

-- Expected: 7 rows returned
