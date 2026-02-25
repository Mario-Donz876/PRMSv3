-- ═══════════════════════════════════════════════════════════════
-- Migration: Remove PO and Commitment approval requirements
-- Date: 2026-02-24
-- Purpose: POs and Commitments are now auto-approved. 
--          This cleans up existing pending approval records
--          and advances any stuck POs/requests.
-- ═══════════════════════════════════════════════════════════════

-- 1. Auto-approve all pending COMMITMENT approval records
UPDATE request_approvals
SET status = 'approved',
    approved_at = NOW()
WHERE entity_type = 'COMMITMENT'
  AND status = 'pending';

-- 2. Auto-approve all pending PO approval records
UPDATE request_approvals
SET status = 'approved',
    approved_at = NOW()
WHERE entity_type = 'PO'
  AND status = 'pending';

-- 3. Set approved_at on POs that don't have it yet
UPDATE purchase_orders
SET approved_at = COALESCE(approved_at, NOW())
WHERE approved_at IS NULL
  AND status = 'Open';

-- 4. Advance any procurement requests stuck at PO_PENDING to PO_APPROVED
UPDATE procurement_requests
SET status = 'PO_APPROVED'
WHERE UPPER(status) = 'PO_PENDING';

-- 5. Advance any procurement requests stuck at COMMITMENTS_PENDING to COMMITMENT_APPROVED
UPDATE procurement_requests
SET status = 'COMMITMENT_APPROVED'
WHERE UPPER(status) = 'COMMITMENTS_PENDING';

SELECT 'Migration complete: PO and Commitment approvals removed' AS result;
