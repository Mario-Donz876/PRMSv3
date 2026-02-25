-- Migration: Fix lock triggers that block legitimate forward transitions
-- from GC_APPROVED/AWARDED/COMPLETED statuses.
--
-- Problem: The `lock_procurement_after_approval` trigger only allows
-- transitions to GC_APPROVED, AWARDED, or COMPLETED. This blocks
-- Finance Officers from creating commitments (COMMITMENT_APPROVED,
-- COMMITMENT_DECLINED) for AWARDED requests.
--
-- Fix: Instead of whitelisting allowed forward statuses, blacklist
-- only the early-stage statuses that would constitute a true revert.
--
-- Also fixes `trg_lock_after_gc_approval` which incorrectly blocks
-- field-level updates (e.g., setting awardee) when status is unchanged.
--
-- Date: 2026-02-22

-- =====================================================
-- Fix 1: lock_procurement_after_approval
-- =====================================================
DROP TRIGGER IF EXISTS `lock_procurement_after_approval`;

DELIMITER $$
CREATE TRIGGER `lock_procurement_after_approval` BEFORE UPDATE ON `procurement_requests` FOR EACH ROW
BEGIN
  -- Only block true reversions to early-stage statuses.
  -- Allow all legitimate forward transitions (commitment, PO, invoice, etc.)
  IF OLD.status IN ('GC_APPROVED', 'AWARDED', 'COMPLETED')
     AND NEW.status IN ('DRAFT', 'SUBMITTED', 'HOD_APPROVED', 'FUNDS_VERIFIED', 'DIRECTOR_APPROVED', 'DECLINED') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Approved/Awarded/Completed requests cannot be reverted to earlier stages';
  END IF;
END
$$
DELIMITER ;

-- =====================================================
-- Fix 2: trg_lock_after_gc_approval
-- =====================================================
-- This trigger blocks ANY update on approved/awarded/completed requests
-- when the status stays the same. This prevents legitimate field-level
-- updates (e.g., setting awardee, award_date, updated_at).
-- Remove it — the first trigger already prevents status reversions.
DROP TRIGGER IF EXISTS `trg_lock_after_gc_approval`;
