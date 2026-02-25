-- ============================================================
-- Migration 007: Seed PO Approval Chains
-- Problem : Legacy POs have no rows in request_approvals,
--           so HOD/Finance approval always fails with
--           "Not your approval stage."
-- Fix     : Insert HOD (stage 1) + Finance Officer (stage 2)
--           for every PO that lacks an approval chain.
-- ============================================================

-- 1) Open POs that have NO approval chain → pending stages
INSERT IGNORE INTO request_approvals
    (entity_type, entity_id, role, stage_order, status)
SELECT 'PO', po.po_id, 'HOD', 1, 'pending'
FROM purchase_orders po
WHERE po.status = 'Open'
  AND NOT EXISTS (
      SELECT 1 FROM request_approvals ra
      WHERE ra.entity_type = 'PO'
        AND ra.entity_id   = po.po_id
  );

INSERT IGNORE INTO request_approvals
    (entity_type, entity_id, role, stage_order, status)
SELECT 'PO', po.po_id, 'Finance Officer', 2, 'pending'
FROM purchase_orders po
WHERE po.status = 'Open'
  AND NOT EXISTS (
      SELECT 1 FROM request_approvals ra
      WHERE ra.entity_type = 'PO'
        AND ra.entity_id   = po.po_id
        AND ra.role         = 'Finance Officer'
  );

-- 2) Closed/approved POs → mark both stages as approved
INSERT IGNORE INTO request_approvals
    (entity_type, entity_id, role, stage_order, status, approved_at)
SELECT 'PO', po.po_id, 'HOD', 1, 'approved', COALESCE(po.approved_at, po.created_at)
FROM purchase_orders po
WHERE po.status IN ('Closed', 'Cancelled')
  AND NOT EXISTS (
      SELECT 1 FROM request_approvals ra
      WHERE ra.entity_type = 'PO'
        AND ra.entity_id   = po.po_id
  );

INSERT IGNORE INTO request_approvals
    (entity_type, entity_id, role, stage_order, status, approved_at)
SELECT 'PO', po.po_id, 'Finance Officer', 2, 'approved', COALESCE(po.approved_at, po.created_at)
FROM purchase_orders po
WHERE po.status IN ('Closed', 'Cancelled')
  AND NOT EXISTS (
      SELECT 1 FROM request_approvals ra
      WHERE ra.entity_type = 'PO'
        AND ra.entity_id   = po.po_id
        AND ra.role         = 'Finance Officer'
  );
