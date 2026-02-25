-- ============================================================================
-- Migration 016: Granular Permissions & Correct Role Mapping
-- ============================================================================
-- Purpose:
--   1. Add new fine-grained permissions for vendor awarding, RFQ management,
--      quote upload, evaluation, vendor management, etc.
--   2. Auto-map every permission to the correct roles so the system works
--      out of the box after running this script.
--   3. Fix the admin role bypass bug (lowercase 'admin' vs 'Admin').
--
-- New permissions created:
--   - award_vendor            : Award an RFQ to a selected vendor
--   - confirm_vendor_award    : Accept/decline vendor award decision
--   - upload_rfq_quote        : Upload vendor quotation documents
--   - start_rfq_evaluation    : Initiate the evaluation stage for an RFQ
--   - upload_rfq_report       : Upload evaluation report for an RFQ
--   - create_rfq              : Create a new RFQ from a procurement request
--   - add_rfq_vendor          : Add vendors to an RFQ invitation list
--   - view_vendors            : View vendor list and details
--   - approve_as_dgc          : Approve requests as Deputy Government Chemist
--   - disburse_petty_cash     : Disburse petty cash funds
--   - process_reimbursement   : Process reimbursement payments
--
-- Date: 2026-02-22
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- STEP 1: Insert New Permissions (INSERT IGNORE — safe to re-run)
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO permissions (name, description) VALUES
-- Vendor Awarding (split from generic award_rfq)
('award_vendor',             'Award an RFQ to a selected vendor quote'),
('confirm_vendor_award',     'Accept or decline a vendor award decision'),

-- RFQ Lifecycle
('upload_rfq_quote',         'Upload vendor quotation documents to an RFQ'),
('start_rfq_evaluation',     'Start the evaluation stage for an RFQ'),
('upload_rfq_report',        'Upload evaluation report for an RFQ'),
('create_rfq',               'Create a new RFQ from a procurement request'),
('add_rfq_vendor',           'Add vendors to an RFQ invitation list'),

-- Vendor Management (view separated from manage)
('view_vendors',             'View vendor list and details'),

-- Role-specific approval
('approve_as_dgc',           'Approve requests as Deputy Government Chemist'),

-- Petty Cash / Reimbursement lifecycle
('disburse_petty_cash',      'Disburse petty cash funds after authorization'),
('process_reimbursement',    'Process reimbursement payment after approval');


-- ═══════════════════════════════════════════════════════════
-- STEP 2: Complete Role → Permission Mapping
-- ═══════════════════════════════════════════════════════════
-- Uses INSERT IGNORE so it's safe to re-run.
-- Each role gets exactly the permissions it needs.

-- ───────────────────────────────────────────────────────────
-- Role 1: Viewer (Read-only access)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name IN (
    'view_audit_dashboard', 'view_requests', 'view_reimbursement_requests',
    'view_petty_cash_requests', 'view_commitments', 'view_purchase_orders',
    'view_invoices', 'view_payments', 'view_audit_logs', 'view_vendors',
    'view_rfq_evaluations'
);

-- ───────────────────────────────────────────────────────────
-- Role 2: Procurement Officer
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    -- Core request lifecycle
    'create_request', 'edit_requests', 'submit_request', 'view_requests',
    'create_reimbursement_request', 'create_petty_cash_request',
    -- Commitments
    'create_commitment', 'view_commitments', 'upload_commitment',
    -- Purchase Orders
    'create_purchase_order', 'edit_purchase_order', 'view_purchase_orders',
    'upload_purchase_order', 'request_po_adjustment', 'view_po_adjustments',
    -- RFQ lifecycle (Procurement Officer drives the RFQ process)
    'create_rfq', 'add_rfq_vendor', 'upload_rfq_quote',
    'start_rfq_evaluation', 'upload_rfq_report',
    'manage_rfq_committee', 'award_rfq', 'award_vendor',
    'view_rfq_evaluations',
    -- Vendor management
    'manage_vendors', 'view_vendors', 'view_vendor_history',
    -- Documents & dashboards
    'manage_attachments', 'view_audit_logs',
    'view_procurement_dashboard',
    'print_request', 'print_purchase_order',
    -- Verification
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation'
);

-- ───────────────────────────────────────────────────────────
-- Role 3: Finance Officer
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    -- Approvals
    'approve_request', 'approve_commitment', 'approve_purchase_order',
    'approve_po', 'approve_po_adjustment', 'approve_po_excess',
    'approve_reimbursement_request', 'approve_petty_cash_request',
    -- Financial operations
    'create_invoice', 'record_invoice', 'create_payment', 'record_payment',
    'create_commitment', 'upload_commitment', 'verify_funds',
    'reconcile_petty_cash', 'disburse_petty_cash', 'process_reimbursement',
    -- Verification
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation',
    -- View everything financial
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_vendors', 'view_audit_logs',
    'view_rfq_evaluations',
    -- Dashboards & reports
    'view_finance_dashboard', 'view_monthly_dashboard',
    'view_financial_reports', 'monthly_metrics',
    -- Print & export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 4: HOD (Head of Department)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    -- Approvals
    'approve_request', 'decline_request', 'approve_commitment',
    'approve_purchase_order', 'approve_po', 'approve_po_adjustment',
    'approve_reimbursement_request', 'approve_petty_cash_request',
    'authorize_petty_cash', 'authorize_reimbursement',
    -- Vendor awarding (HOD can confirm awards)
    'award_rfq', 'award_vendor', 'confirm_vendor_award',
    -- Verification
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation',
    -- View everything
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_vendors', 'view_audit_logs',
    'view_rfq_evaluations',
    -- Dashboards & reports
    'view_management_dashboard', 'view_procurement_dashboard',
    'view_monthly_dashboard', 'view_finance_dashboard',
    'view_financial_reports', 'view_approval_analytics',
    'management_dashboard', 'monthly_metrics',
    -- Print & export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 5: Admin
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    -- Full admin + system
    'manage_users', 'manage_system_settings', 'author_override',
    -- All request lifecycle
    'create_request', 'edit_requests', 'submit_request',
    'approve_request', 'decline_request',
    'create_reimbursement_request', 'create_petty_cash_request',
    'authorize_reimbursement', 'authorize_petty_cash',
    'approve_reimbursement_request', 'approve_petty_cash_request',
    -- All commitment
    'create_commitment', 'approve_commitment', 'upload_commitment',
    -- All PO
    'create_purchase_order', 'edit_purchase_order', 'approve_purchase_order',
    'approve_po', 'approve_po_excess', 'request_po_adjustment',
    'approve_po_adjustment', 'upload_purchase_order',
    -- All financial
    'create_invoice', 'record_invoice', 'create_payment', 'record_payment',
    'verify_funds', 'reconcile_petty_cash', 'disburse_petty_cash',
    'process_reimbursement',
    -- All verification
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation',
    -- All RFQ
    'create_rfq', 'add_rfq_vendor', 'upload_rfq_quote',
    'start_rfq_evaluation', 'upload_rfq_report',
    'manage_rfq_committee', 'award_rfq', 'award_vendor',
    'confirm_vendor_award', 'vote_rfq', 'view_rfq_evaluations',
    -- All vendor
    'manage_vendors', 'view_vendors', 'view_vendor_history',
    -- All documents
    'manage_attachments',
    -- All views
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_po_adjustments',
    'view_compliance', 'view_financial_reports', 'view_own_requests',
    -- All dashboards
    'view_finance_dashboard', 'view_management_dashboard',
    'view_monthly_dashboard', 'view_procurement_dashboard',
    'view_approval_analytics', 'management_dashboard',
    'view_audit_dashboard', 'monthly_metrics',
    'view_director_dashboard', 'approve_as_director_hrma',
    -- All print/export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 6: SuperAdmin (All permissions — automatic)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions;

-- ───────────────────────────────────────────────────────────
-- Role 7: Evaluation Committee Member
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 7, id FROM permissions WHERE name IN (
    'view_requests', 'view_commitments', 'view_purchase_orders',
    'view_audit_logs', 'view_rfq_evaluations', 'vote_rfq',
    'view_vendors', 'upload_rfq_report'
);

-- ───────────────────────────────────────────────────────────
-- Role 8: Procurement Committee
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 8, id FROM permissions WHERE name IN (
    'approve_request', 'view_requests', 'view_commitments',
    'view_purchase_orders', 'view_audit_logs', 'print_request',
    'view_rfq_evaluations', 'view_vendors',
    'award_rfq', 'award_vendor', 'confirm_vendor_award'
);

-- ───────────────────────────────────────────────────────────
-- Role 9: Deputy Government Chemist (DGC)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 9, id FROM permissions WHERE name IN (
    -- Approvals
    'approve_request', 'decline_request', 'approve_as_dgc',
    'approve_commitment',
    -- Vendor awarding (final authority)
    'award_rfq', 'award_vendor', 'confirm_vendor_award',
    -- Views
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_vendors',
    'view_rfq_evaluations',
    -- Dashboards & reports
    'view_management_dashboard', 'view_monthly_dashboard',
    'view_finance_dashboard', 'view_financial_reports',
    'view_approval_analytics', 'view_compliance',
    'management_dashboard', 'monthly_metrics',
    -- Print & export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 10: Director HRM&A
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 10, id FROM permissions WHERE name IN (
    -- Approvals
    'approve_request', 'approve_as_director_hrma', 'decline_request',
    'approve_commitment',
    -- Vendor awarding
    'award_rfq', 'award_vendor', 'confirm_vendor_award',
    -- Views
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_vendors',
    'view_rfq_evaluations',
    -- Authorization
    'authorize_reimbursement', 'authorize_petty_cash',
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation',
    -- Dashboards & reports
    'view_management_dashboard', 'view_monthly_dashboard',
    'view_finance_dashboard', 'view_financial_reports',
    'view_approval_analytics', 'management_dashboard', 'monthly_metrics',
    -- Print & export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 11: Director Procurement
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 11, id FROM permissions WHERE name IN (
    -- Approvals
    'approve_request', 'approve_commitment', 'approve_purchase_order',
    'approve_po', 'approve_po_adjustment', 'decline_request',
    -- Full RFQ control
    'create_rfq', 'add_rfq_vendor', 'start_rfq_evaluation',
    'upload_rfq_report', 'manage_rfq_committee',
    'award_rfq', 'award_vendor', 'confirm_vendor_award',
    'view_rfq_evaluations',
    -- Vendor management
    'manage_vendors', 'view_vendors', 'view_vendor_history',
    -- Views
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs',
    -- Dashboards & reports
    'view_management_dashboard', 'view_monthly_dashboard',
    'view_finance_dashboard', 'view_financial_reports',
    'view_approval_analytics', 'view_procurement_dashboard',
    'view_director_dashboard',
    'management_dashboard', 'monthly_metrics',
    -- Print & export
    'print_request', 'print_purchase_order', 'print_invoice',
    'export_requests'
);

-- ───────────────────────────────────────────────────────────
-- Role 12: Requestor
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 12, id FROM permissions WHERE name IN (
    'create_request', 'edit_requests', 'submit_request',
    'submit_own_request', 'resubmit_request', 'view_own_requests',
    'create_reimbursement_request', 'create_petty_cash_request',
    'print_request', 'view_vendors'
);


-- ═══════════════════════════════════════════════════════════
-- STEP 3: Verification Queries
-- ═══════════════════════════════════════════════════════════

-- Total permissions should now be ~76
SELECT COUNT(*) AS total_permissions FROM permissions;

-- Permissions per role
SELECT
    r.id, r.name AS role_name,
    COUNT(DISTINCT rp.permission_id) AS perm_count
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
WHERE r.id BETWEEN 1 AND 12
GROUP BY r.id, r.name
ORDER BY r.id;

-- New permissions verification
SELECT id, name, description
FROM permissions
WHERE name IN (
    'award_vendor', 'confirm_vendor_award', 'upload_rfq_quote',
    'start_rfq_evaluation', 'upload_rfq_report', 'create_rfq',
    'add_rfq_vendor', 'view_vendors', 'approve_as_dgc',
    'disburse_petty_cash', 'process_reimbursement'
)
ORDER BY name;
