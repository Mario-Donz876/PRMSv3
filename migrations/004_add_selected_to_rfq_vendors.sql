-- Migration: Add 'SELECTED' to rfq_vendors.response_status enum
-- 
-- Problem: award.php sets response_status = 'SELECTED' but the enum
-- only allows PENDING, WILL_SUBMIT, DECLINED, SUBMITTED.
-- The UPDATE silently fails in strict mode or stores empty string.
-- Date: 2026-02-16

ALTER TABLE `rfq_vendors`
  MODIFY COLUMN `response_status` 
  ENUM('PENDING','WILL_SUBMIT','DECLINED','SUBMITTED','SELECTED') 
  DEFAULT 'PENDING';

-- Fix any vendors that were awarded but didn't get SELECTED status
UPDATE rfq_vendors rv
JOIN rfq_quotes q ON rv.rfq_vendor_id = q.rfq_vendor_id
JOIN rfqs r ON rv.rfq_id = r.rfq_id
SET rv.response_status = 'SELECTED'
WHERE q.is_selected = 1
  AND r.status = 'AWARDED';
