-- ============================================================================
-- Migration 008: Major Workflow Restructure
-- ============================================================================
-- Changes:
--   1. Add request_type to procurement_requests (REGULAR, REIMBURSEMENT, PETTY_CASH)
--   2. Add petty_cash_limit system config
--   3. Add new roles: Director HRM&A, Director Procurement
--   4. Set 3 approvers only: HOD, Director HRM&A, Deputy Government Chemist
--   5. Add file upload columns to purchase_orders (upload-based PO)
--   6. Add file upload columns to commitments (upload-based commitment)
--   7. Add direct_procurement threshold support
--   8. New permissions for new roles
--   9. Update approval workflow for new flow
-- ============================================================================

-- -------------------------------------------------------
-- 1. REQUEST TYPES on procurement_requests
-- -------------------------------------------------------
ALTER TABLE `procurement_requests`
  ADD COLUMN `request_type` ENUM('REGULAR','REIMBURSEMENT','PETTY_CASH') NOT NULL DEFAULT 'REGULAR'
  AFTER `status`;

-- -------------------------------------------------------
-- 2. DIRECT PROCUREMENT threshold flag
--    Requests under threshold skip RFQ and go direct
-- -------------------------------------------------------
ALTER TABLE `procurement_requests`
  ADD COLUMN `direct_procurement` TINYINT(1) NOT NULL DEFAULT 0
  AFTER `request_type`,
  ADD COLUMN `direct_procurement_reason` TEXT DEFAULT NULL
  AFTER `direct_procurement`;

-- -------------------------------------------------------
-- 3. PURCHASE ORDERS: Add upload fields (PO is now uploaded, not form-created)
-- -------------------------------------------------------
ALTER TABLE `purchase_orders`
  ADD COLUMN `po_file` VARCHAR(255) DEFAULT NULL AFTER `po_total`,
  ADD COLUMN `uploaded_by` INT(11) DEFAULT NULL AFTER `po_file`,
  ADD COLUMN `upload_date` DATETIME DEFAULT NULL AFTER `uploaded_by`;

-- -------------------------------------------------------
-- 4. COMMITMENTS: Add upload fields (Commitment is now uploaded by accounts)
-- -------------------------------------------------------
ALTER TABLE `commitments`
  ADD COLUMN `commitment_file` VARCHAR(255) DEFAULT NULL AFTER `commitment_total`,
  ADD COLUMN `uploaded_by` INT(11) DEFAULT NULL AFTER `commitment_file`,
  ADD COLUMN `responded_to_procurement` TINYINT(1) DEFAULT 0 AFTER `uploaded_by`,
  ADD COLUMN `response_date` DATETIME DEFAULT NULL AFTER `responded_to_procurement`;

-- -------------------------------------------------------
-- 5. NEW ROLES
-- -------------------------------------------------------
INSERT INTO `roles` (`name`, `description`) VALUES
  ('Director HRM&A', 'Director of Human Resource Management & Administration - Approver'),
  ('Director Procurement', 'Director for Procurement operations');

-- -------------------------------------------------------
-- 6. NEW PERMISSIONS
-- -------------------------------------------------------
INSERT INTO `permissions` (`name`, `description`) VALUES
  ('upload_purchase_order', 'Upload purchase order document'),
  ('upload_commitment', 'Upload commitment document'),
  ('verify_funds', 'Verify fund availability for procurement requests'),
  ('respond_commitment', 'Respond to procurement with commitment number'),
  ('approve_as_director_hrma', 'Approve requests as Director HRM&A'),
  ('view_director_dashboard', 'Access Director for Procurement dashboard'),
  ('direct_procurement', 'Approve direct procurement without RFQ for under-threshold requests');

-- -------------------------------------------------------
-- 7. ROLE PERMISSIONS for new roles
-- -------------------------------------------------------

-- Director HRM&A (role_id will be 10) - Approver role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Director HRM&A'
AND p.name IN (
  'approve_request', 'approve_commitment', 'approve_po',
  'approve_purchase_order', 'approve_as_director_hrma',
  'view_requests', 'view_commitments', 'view_purchase_orders',
  'view_management_dashboard', 'view_monthly_dashboard',
  'view_audit_logs'
);

-- Director Procurement (role_id will be 11) - Operational oversight
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Director Procurement'
AND p.name IN (
  'create_request', 'submit_request', 'view_requests',
  'create_commitment', 'view_commitments',
  'create_purchase_order', 'view_purchase_orders',
  'view_procurement_dashboard', 'view_director_dashboard',
  'view_audit_logs', 'view_monthly_dashboard',
  'direct_procurement', 'upload_purchase_order', 'upload_commitment',
  'request_po_adjustment', 'edit_purchase_order',
  'print_purchase_order', 'print_request'
);

-- Finance Officer gets verify_funds, upload_commitment, respond_commitment
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Finance Officer'
AND p.name IN ('verify_funds', 'upload_commitment', 'respond_commitment');

-- Procurement Officer gets upload_purchase_order
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Procurement Officer'
AND p.name IN ('upload_purchase_order');

-- -------------------------------------------------------
-- 8. SYSTEM CONFIGURATION for thresholds
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_config` (
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_config` (`config_key`, `config_value`, `description`) VALUES
  ('petty_cash_limit', '5000', 'Maximum amount for petty cash requests (JMD)'),
  ('direct_procurement_threshold', '500000', 'Requests under this amount can skip RFQ and go direct'),
  ('currency_symbol', 'JMD', 'Currency symbol for display');

-- -------------------------------------------------------
-- 9. UPDATE APPROVAL WORKFLOWS for 3-approver model
--    HOD → Director HRM&A → Deputy Government Chemist
-- -------------------------------------------------------

-- Clear old workflows and re-create
DELETE FROM `approval_steps`;
DELETE FROM `approval_workflows`;

INSERT INTO `approval_workflows` (`workflow_id`, `entity_type`, `min_amount`, `max_amount`, `description`, `is_active`) VALUES
(1, 'PROCUREMENT_REQUEST', 0.00, 5000.00, 'Petty Cash Workflow (HOD only)', 1),
(2, 'PROCUREMENT_REQUEST', 5000.01, 500000.00, 'Standard Workflow (HOD → Director HRM&A)', 1),
(3, 'PROCUREMENT_REQUEST', 500000.01, NULL, 'Full Approval Workflow (HOD → Director HRM&A → DGC)', 1);

INSERT INTO `approval_steps` (`workflow_id`, `step_order`, `role_id`, `is_mandatory`) VALUES
-- Petty Cash: HOD only
(1, 1, (SELECT id FROM roles WHERE name = 'HOD'), 1),
-- Standard: HOD → Director HRM&A
(2, 1, (SELECT id FROM roles WHERE name = 'HOD'), 1),
(2, 2, (SELECT id FROM roles WHERE name = 'Director HRM&A'), 1),
-- Full: HOD → Director HRM&A → Deputy Government Chemist
(3, 1, (SELECT id FROM roles WHERE name = 'HOD'), 1),
(3, 2, (SELECT id FROM roles WHERE name = 'Director HRM&A'), 1),
(3, 3, (SELECT id FROM roles WHERE name = 'Deputy Government Chemist'), 1);

-- -------------------------------------------------------
-- 10. UPDATE procurement_method trigger for new thresholds
-- -------------------------------------------------------
DROP TRIGGER IF EXISTS `trg_set_procurement_method`;
DELIMITER $$
CREATE TRIGGER `trg_set_procurement_method` BEFORE INSERT ON `procurement_requests` FOR EACH ROW
BEGIN
    -- Petty Cash requests are always direct procurement
    IF NEW.request_type = 'PETTY_CASH' THEN
        SET NEW.direct_procurement = 1;
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 500000 THEN
        SET NEW.direct_procurement = 1;
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 3000000 THEN
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 20000000 THEN
        SET NEW.procurement_method = 'RESTRICTED_BIDDING';
    ELSE
        SET NEW.procurement_method = 'NATIONAL_COMPETITIVE';
    END IF;
END
$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_auto_procurement_method`;
DELIMITER $$
CREATE TRIGGER `trg_auto_procurement_method` BEFORE UPDATE ON `procurement_requests` FOR EACH ROW
BEGIN
    IF NEW.request_type = 'PETTY_CASH' THEN
        SET NEW.direct_procurement = 1;
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 500000 THEN
        SET NEW.direct_procurement = 1;
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 3000000 THEN
        SET NEW.procurement_method = 'SINGLE_SOURCE';
    ELSEIF NEW.estimated_value <= 20000000 THEN
        SET NEW.procurement_method = 'RESTRICTED_BIDDING';
    ELSE
        SET NEW.procurement_method = 'NATIONAL_COMPETITIVE';
    END IF;

    IF NEW.estimated_value > 60000000 THEN
        SET NEW.external_approval_required = 'PPC';
    END IF;

    IF NEW.estimated_value > 150000000 THEN
        SET NEW.external_approval_required = 'CABINET';
    END IF;
END
$$
DELIMITER ;
