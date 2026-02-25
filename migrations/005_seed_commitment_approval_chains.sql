-- Migration: Seed approval chains for legacy commitments
-- Fixes: HOD/Finance unable to approve commitments created before
--        the approval chain was added to commitments/add.php.
-- Any open commitment without request_approvals rows gets HOD → Finance stages.

-- HOD stage (stage_order = 1)
INSERT INTO request_approvals (entity_type, entity_id, role, stage_order, status)
SELECT 'COMMITMENT', c.commitment_id, 'HOD', 1, 'pending'
FROM commitments c
WHERE c.status = 'open'
  AND NOT EXISTS (
    SELECT 1 FROM request_approvals ra
    WHERE ra.entity_type = 'COMMITMENT'
      AND ra.entity_id = c.commitment_id
  );

-- Finance Officer stage (stage_order = 2)
INSERT INTO request_approvals (entity_type, entity_id, role, stage_order, status)
SELECT 'COMMITMENT', c.commitment_id, 'Finance Officer', 2, 'pending'
FROM commitments c
WHERE c.status = 'open'
  AND NOT EXISTS (
    SELECT 1 FROM request_approvals ra
    WHERE ra.entity_type = 'COMMITMENT'
      AND ra.entity_id = c.commitment_id
      AND ra.role = 'Finance Officer'
  );

-- For already-closed commitments, mark both stages as approved retroactively
INSERT INTO request_approvals (entity_type, entity_id, role, stage_order, status, approved_at)
SELECT 'COMMITMENT', c.commitment_id, 'HOD', 1, 'approved', c.approved_at
FROM commitments c
WHERE c.status = 'closed'
  AND NOT EXISTS (
    SELECT 1 FROM request_approvals ra
    WHERE ra.entity_type = 'COMMITMENT'
      AND ra.entity_id = c.commitment_id
  );

INSERT INTO request_approvals (entity_type, entity_id, role, stage_order, status, approved_at)
SELECT 'COMMITMENT', c.commitment_id, 'Finance Officer', 2, 'approved', c.approved_at
FROM commitments c
WHERE c.status = 'closed'
  AND NOT EXISTS (
    SELECT 1 FROM request_approvals ra
    WHERE ra.entity_type = 'COMMITMENT'
      AND ra.entity_id = c.commitment_id
      AND ra.role = 'Finance Officer'
  );
