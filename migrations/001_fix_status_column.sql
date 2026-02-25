-- Migration: Convert procurement_requests.status from ENUM to VARCHAR
-- and normalize all existing status values to UPPERCASE workflow stages.
-- 
-- Run this against the production database to fix blank statuses.
-- Date: 2026-02-15

-- Step 1: Drop the trigger that references old status values
DROP TRIGGER IF EXISTS `lock_procurement_after_approval`;

-- Step 2: Change ENUM to VARCHAR to support all workflow stages
ALTER TABLE `procurement_requests`
  MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'DRAFT';

-- Step 3: Normalize all existing status values to UPPERCASE
UPDATE `procurement_requests` SET `status` = 'DRAFT'      WHERE UPPER(`status`) = 'DRAFT';
UPDATE `procurement_requests` SET `status` = 'SUBMITTED'   WHERE UPPER(`status`) = 'SUBMITTED';
UPDATE `procurement_requests` SET `status` = 'GC_APPROVED' WHERE UPPER(`status`) = 'APPROVED';
UPDATE `procurement_requests` SET `status` = 'DECLINED'    WHERE UPPER(`status`) = 'DECLINED';
UPDATE `procurement_requests` SET `status` = 'CANCELLED'   WHERE UPPER(`status`) = 'CANCELLED';

-- Step 4: Fix any rows that got written as empty string (from failed ENUM writes)
-- These were likely HOD_APPROVED, FINANCE_APPROVED, etc. attempts that got blanked.
-- Check audit_log to identify what they should be, or set to SUBMITTED as safe default:
UPDATE `procurement_requests` SET `status` = 'SUBMITTED' WHERE `status` = '' OR `status` IS NULL;

-- Step 5: Recreate the trigger using the new workflow stages
DELIMITER $$
CREATE TRIGGER `lock_procurement_after_approval` BEFORE UPDATE ON `procurement_requests` FOR EACH ROW
BEGIN
  IF OLD.status IN ('GC_APPROVED', 'AWARDED', 'COMPLETED') 
     AND NEW.status NOT IN ('GC_APPROVED', 'AWARDED', 'COMPLETED') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Approved/Awarded/Completed requests cannot be reverted';
  END IF;
END
$$
DELIMITER ;
