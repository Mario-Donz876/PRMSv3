-- ============================================================================
-- Migration: Assign Default Permissions to System Roles
-- Purpose:   Ensure all roles have appropriate default permissions based on
--            their responsibilities in the SOP
-- Safety:    Uses INSERT IGNORE - existing mappings are preserved
-- Date:      2026-02-18
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- STEP 1: Ensure all permissions exist in permissions table
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO permissions (name, description) VALUES
('view_audit_dashboard',       'Access audit dashboard and logs'),
('view_requests',              'View procurement requests'),
('view_commitments',           'View commitments'),
('view_purchase_orders',       'View purchase orders'),
('view_invoices',              'View invoices'),
('view_payments',              'View payments'),
('view_audit_logs',            'View audit logs'),
('create_request',             'Create new procurement requests'),
('edit_requests',              'Edit procurement requests, create RFQs, manage vendors'),
('submit_request',             'Submit requests for approval'),
('approve_request',            'Approve requests at assigned stage'),
('decline_request',            'Decline/reject requests'),
('create_commitment',          'Create commitments'),
('approve_commitment',         'Approve commitments'),
('create_purchase_order',      'Create purchase orders'),
('edit_purchase_order',        'Edit purchase orders'),
('approve_purchase_order',     'Approve purchase orders'),
('approve_po',                 'Approve POs (alternate permission name)'),
('approve_po_adjustment',      'Approve PO adjustments/variations'),
('approve_po_excess',          'Approve PO excess amounts'),
('request_po_adjustment',      'Request PO adjustments'),
('view_po_adjustments',        'View PO adjustments'),
('create_invoice',             'Create invoices'),
('record_invoice',             'Record received invoices'),
('create_payment',             'Create payments'),
('record_payment',             'Record payments made'),
('manage_users',               'Manage users, roles, and permissions'),
('view_audit_dashboard',       'Access audit dashboard'),
('view_finance_dashboard',     'Access finance dashboard'),
('view_management_dashboard',  'Access management dashboard'),
('view_monthly_dashboard',     'Access monthly metrics dashboard'),
('view_procurement_dashboard', 'Access procurement dashboard'),
('view_approval_analytics',    'Access approval analytics dashboard'),
('view_compliance',            'Access compliance dashboard'),
('view_financial_reports',     'View financial reports'),
('management_dashboard',       'Access management overview dashboard'),
('monthly_metrics',            'Access monthly financial metrics dashboard'),
('print_request',              'Print procurement requests'),
('print_purchase_order',       'Print purchase orders'),
('print_invoice',              'Print invoices'),
('approve_reimbursement_request', 'Approve reimbursement requests'),
('approve_petty_cash_request',    'Approve petty cash requests'),
('create_reimbursement_request',  'Create reimbursement requests'),
('create_petty_cash_request',     'Create petty cash requests'),
('author_override',            'Override approval chain decisions');


-- ═══════════════════════════════════════════════════════════
-- STEP 2: Assign permissions to each role
-- ═══════════════════════════════════════════════════════════

-- ───────────────────────────────────────────────────────────
-- Role 1: Viewer (Read-only audit access)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name IN (
    'view_audit_dashboard',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs'
);

-- ───────────────────────────────────────────────────────────
-- Role 2: Procurement Officer
-- Can create/edit/submit requests, manage RFQs, create commitments/POs
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    'create_request',
    'edit_requests',
    'submit_request',
    'view_requests',
    'create_commitment',
    'view_commitments',
    'create_purchase_order',
    'edit_purchase_order',
    'view_purchase_orders',
    'request_po_adjustment',
    'view_po_adjustments',
    'view_audit_logs',
    'view_procurement_dashboard',
    'view_invoices',
    'view_payments',
    'print_request',
    'print_purchase_order',
    'create_reimbursement_request',
    'create_petty_cash_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 3: Finance Officer
-- Approve requests (finance stage), invoices, payments, reports
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'approve_request',
    'approve_commitment',
    'approve_po',
    'approve_purchase_order',
    'approve_po_adjustment',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'create_invoice',
    'record_invoice',
    'create_payment',
    'record_payment',
    'view_finance_dashboard',
    'view_monthly_dashboard',
    'view_financial_reports',
    'view_audit_logs',
    'print_request',
    'print_purchase_order',
    'print_invoice',
    'monthly_metrics',
    'approve_reimbursement_request',
    'approve_petty_cash_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 4: HOD (Head of Department)
-- Approve requests (HOD stage), commitments, POs, view dashboards
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    'approve_request',
    'decline_request',
    'approve_commitment',
    'approve_po',
    'approve_purchase_order',
    'approve_po_adjustment',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_management_dashboard',
    'view_procurement_dashboard',
    'view_monthly_dashboard',
    'view_finance_dashboard',
    'view_audit_logs',
    'management_dashboard',
    'print_request',
    'print_purchase_order',
    'print_invoice',
    'view_financial_reports',
    'monthly_metrics',
    'view_approval_analytics',
    'approve_petty_cash_request',
    'approve_reimbursement_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 5: Admin
-- Full system access with explicit permission grants
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    'manage_users',
    'create_request',
    'edit_requests',
    'submit_request',
    'approve_request',
    'decline_request',
    'create_commitment',
    'approve_commitment',
    'create_purchase_order',
    'edit_purchase_order',
    'approve_purchase_order',
    'approve_po',
    'approve_po_adjustment',
    'approve_po_excess',
    'request_po_adjustment',
    'create_invoice',
    'record_invoice',
    'create_payment',
    'record_payment',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs',
    'view_audit_dashboard',
    'view_finance_dashboard',
    'view_management_dashboard',
    'view_monthly_dashboard',
    'view_procurement_dashboard',
    'view_po_adjustments',
    'view_financial_reports',
    'view_approval_analytics',
    'view_compliance',
    'management_dashboard',
    'monthly_metrics',
    'print_request',
    'print_purchase_order',
    'print_invoice',
    'author_override',
    'approve_reimbursement_request',
    'approve_petty_cash_request',
    'create_reimbursement_request',
    'create_petty_cash_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 6: SuperAdmin
-- Full system access (all permissions)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions WHERE name IN (
    'manage_users',
    'create_request',
    'edit_requests',
    'submit_request',
    'approve_request',
    'decline_request',
    'create_commitment',
    'approve_commitment',
    'create_purchase_order',
    'edit_purchase_order',
    'approve_purchase_order',
    'approve_po',
    'approve_po_adjustment',
    'approve_po_excess',
    'request_po_adjustment',
    'create_invoice',
    'record_invoice',
    'create_payment',
    'record_payment',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs',
    'view_audit_dashboard',
    'view_finance_dashboard',
    'view_management_dashboard',
    'view_monthly_dashboard',
    'view_procurement_dashboard',
    'view_po_adjustments',
    'view_financial_reports',
    'view_approval_analytics',
    'view_compliance',
    'management_dashboard',
    'monthly_metrics',
    'print_request',
    'print_purchase_order',
    'print_invoice',
    'author_override',
    'approve_reimbursement_request',
    'approve_petty_cash_request',
    'create_reimbursement_request',
    'create_petty_cash_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 7: Evaluation Committee Member
-- Limited access to view assigned requests and RFQs for evaluation
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 7, id FROM permissions WHERE name IN (
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_audit_logs'
);

-- ───────────────────────────────────────────────────────────
-- Role 8: Procurement Committee
-- Approve requests at committee stage, view procurement data
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 8, id FROM permissions WHERE name IN (
    'approve_request',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_audit_logs',
    'print_request'
);

-- ───────────────────────────────────────────────────────────
-- Role 9: Deputy Government Chemist (DGC)
-- Final approval authority, external compliance checks
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 9, id FROM permissions WHERE name IN (
    'approve_request',
    'decline_request',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs',
    'view_management_dashboard',
    'view_monthly_dashboard',
    'view_finance_dashboard',
    'view_financial_reports',
    'view_approval_analytics',
    'view_compliance',
    'management_dashboard',
    'monthly_metrics',
    'print_request',
    'print_purchase_order',
    'print_invoice'
);

-- ───────────────────────────────────────────────────────────
-- Role 10: Director HRM&A
-- Approval authority for HRM&A branch requests, dashboard access
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 10, id FROM permissions WHERE name IN (
    'approve_request',
    'decline_request',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs',
    'view_management_dashboard',
    'view_monthly_dashboard',
    'view_finance_dashboard',
    'view_financial_reports',
    'view_approval_analytics',
    'management_dashboard',
    'monthly_metrics',
    'print_request',
    'print_purchase_order',
    'print_invoice'
);

-- ───────────────────────────────────────────────────────────
-- Role 11: Director Procurement
-- Director-level procurement oversight and approvals
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 11, id FROM permissions WHERE name IN (
    'approve_request',
    'approve_commitment',
    'approve_po',
    'approve_purchase_order',
    'approve_po_adjustment',
    'decline_request',
    'view_requests',
    'view_commitments',
    'view_purchase_orders',
    'view_invoices',
    'view_payments',
    'view_audit_logs',
    'view_management_dashboard',
    'view_monthly_dashboard',
    'view_finance_dashboard',
    'view_financial_reports',
    'view_approval_analytics',
    'view_procurement_dashboard',
    'management_dashboard',
    'monthly_metrics',
    'print_request',
    'print_purchase_order',
    'print_invoice'
);

-- ───────────────────────────────────────────────────────────
-- Role 12: Requestor
-- Can create and submit requests for approvals
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 12, id FROM permissions WHERE name IN (
    'create_request',
    'edit_requests',
    'submit_request',
    'view_requests',
    'print_request',
    'create_reimbursement_request',
    'create_petty_cash_request'
);


-- ═══════════════════════════════════════════════════════════
-- STEP 3: Verification Queries
-- ═══════════════════════════════════════════════════════════

-- Check that all roles have permissions assigned:
SELECT 
    r.id,
    r.name,
    COUNT(rp.permission_id) as permission_count
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
GROUP BY r.id, r.name
ORDER BY r.id;

-- Check permission counts by role type:
SELECT 
    r.name as role_name,
    COUNT(DISTINCT p.name) as unique_permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE r.id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)
GROUP BY r.id, r.name
ORDER BY r.id;

-- List permissions for each role (for review):
SELECT 
    r.name as role_name,
    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') as permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE r.id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)
GROUP BY r.id, r.name
ORDER BY r.id;
