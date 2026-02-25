-- Migration: Advance legacy procurement_requests that have completed RFQ work
-- but are stuck at old/intermediate statuses.
--
-- Problem: Requests created before the multi-step workflow have statuses like
-- 'Approved', 'PROCUREMENT_STAGE', etc., but their RFQs already have vendors,
-- quotes, committee members, and evaluation reports. The award process requires
-- status = 'GC_APPROVED', so these requests are stuck.
--
-- This migration advances such requests to 'GC_APPROVED' so the GC can award them.
-- Date: 2026-02-16

-- Advance requests that have RFQs with evaluation work completed
-- (i.e., have committee members, quotes, and are not yet awarded)
UPDATE procurement_requests pr
SET pr.status = 'GC_APPROVED'
WHERE pr.status IN ('Approved', 'PROCUREMENT_STAGE', 'EVALUATION_STAGE', 'COMMITTEE_RECOMMENDED')
  AND EXISTS (
    SELECT 1 FROM rfqs r
    WHERE r.request_id = pr.request_id
      AND r.status <> 'AWARDED'
  )
  AND EXISTS (
    SELECT 1 FROM rfqs r
    JOIN rfq_evaluation_committee ec ON r.rfq_id = ec.rfq_id
    WHERE r.request_id = pr.request_id
    GROUP BY r.rfq_id
    HAVING COUNT(*) >= 3
  );

-- Also normalize any remaining old 'Approved' statuses to 'GC_APPROVED'
-- (requests that don't have RFQs but were approved under the old system)
UPDATE procurement_requests
SET status = 'GC_APPROVED'
WHERE UPPER(status) = 'APPROVED';
