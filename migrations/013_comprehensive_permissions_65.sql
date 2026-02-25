-- ============================================================================
-- Migration: Comprehensive Permissions System (65 Total Permissions)
-- Purpose:   Create all permissions needed for granular access control
-- Date:      2026-02-18
-- Note:      Extends migration 012 with 23 additional granular permissions
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- STEP 1: Insert All Permissions (65 Total)
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO permissions (name, description) VALUES
-- VIEW PERMISSIONS (12)
('view_audit_dashboard',       'Access audit dashboard and logs'),
('view_requests',              'View all procurement requests'),
('view_reimbursement_requests','View all reimbursement requests'),
('view_petty_cash_requests',   'View all petty cash requests'),
('view_commitments',           'View commitments'),
('view_purchase_orders',       'View purchase orders'),
('view_invoices',              'View invoices'),
('view_payments',              'View payments'),
('view_audit_logs',            'View audit logs'),
('view_po_adjustments',        'View PO adjustments'),
('view_compliance',            'Access compliance dashboard'),
('view_financial_reports',     'View financial reports'),

-- CREATE/EDIT PERMISSIONS (9)
('create_request',             'Create new procurement requests'),
('edit_requests',              'Edit procurement requests'),
('create_reimbursement_request', 'Create reimbursement requests'),
('create_petty_cash_request',  'Create petty cash requests'),
('create_commitment',          'Create commitments'),
('create_purchase_order',      'Create purchase orders'),
('create_invoice',             'Create invoices'),
('create_payment',             'Create payments'),
('edit_purchase_order',        'Edit purchase orders'),

-- SUBMIT/MANAGEMENT PERMISSIONS (4)
('submit_request',             'Submit requests for approval'),
('submit_own_request',         'Submit own requests'),
('resubmit_request',           'Resubmit declined requests'),
('request_po_adjustment',      'Request PO adjustments'),

-- APPROVAL PERMISSIONS (8)
('approve_request',            'Approve requests at assigned stage'),
('approve_commitment',         'Approve commitments'),
('approve_purchase_order',     'Approve purchase orders'),
('approve_po',                 'Approve POs (alternate name)'),
('approve_po_excess',          'Approve PO excess amounts'),
('approve_reimbursement_request', 'Approve reimbursement requests'),
('approve_petty_cash_request', 'Approve petty cash requests'),
('decline_request',            'Decline/reject requests'),

-- AUTHORIZATION PERMISSIONS (3)
('authorize_reimbursement',    'Authorize reimbursement (Branch Head)'),
('authorize_petty_cash',       'Authorize petty cash (Branch Head)'),
('approve_po_adjustment',      'Approve PO adjustments/variations'),

-- DOCUMENT/UPLOAD PERMISSIONS (3)
('upload_commitment',          'Upload commitment documents'),
('upload_purchase_order',      'Upload PO documents'),
('manage_attachments',         'Add/remove document attachments'),

-- VERIFICATION/RECONCILIATION PERMISSIONS (3)
('verify_reimbursement_goods', 'Verify goods/services for reimbursement'),
('verify_petty_cash_reconciliation', 'Verify petty cash 24-hour reconciliation'),
('record_invoice',             'Record receipt of invoice'),

-- FINANCE/PAYMENT PERMISSIONS (2)
('record_payment',             'Record payment made'),
('reconcile_petty_cash',       'Reconcile petty cash after 24h'),

-- RFQ/EVALUATION PERMISSIONS (4)
('view_rfq_evaluations',       'View RFQ evaluations'),
('vote_rfq',                   'Vote on RFQ evaluations'),
('manage_rfq_committee',       'Add/remove RFQ committee members'),
('award_rfq',                  'Award RFQ to vendor'),

-- VENDOR MANAGEMENT PERMISSIONS (2)
('manage_vendors',             'Add, edit, delete vendors'),
('view_vendor_history',        'View vendor performance history'),

-- VIEW OWN REQUEST PERMISSIONS (1)
('view_own_requests',          'View only own submitted requests'),

-- EXPORT PERMISSIONS (1)
('export_requests',            'Export request data to CSV/Excel'),

-- DASHBOARD PERMISSIONS (6)
('view_finance_dashboard',     'Access finance dashboard'),
('view_management_dashboard',  'Access management dashboard'),
('view_monthly_dashboard',     'Access monthly metrics dashboard'),
('view_procurement_dashboard', 'Access procurement dashboard'),
('view_approval_analytics',    'Access approval analytics dashboard'),
('management_dashboard',       'Access management overview dashboard'),

-- PRINT PERMISSIONS (3)
('print_request',              'Print procurement requests'),
('print_purchase_order',       'Print purchase orders'),
('print_invoice',              'Print invoices'),

-- ADMIN PERMISSIONS (3)
('manage_users',               'Manage users, roles, and permissions'),
('manage_system_settings',     'Configure system settings'),
('author_override',            'Override approval chain decisions'),

-- SPECIAL ROLE PERMISSIONS (2)
('approve_as_director_hrma',   'Approve requests as Director HRM&A'),
('view_director_dashboard',    'Access Director for Procurement dashboard'),

-- MONTHLY METRICS (1)
('monthly_metrics',            'Access monthly financial metrics dashboard');


-- ═══════════════════════════════════════════════════════════
-- STEP 2: Map Permissions to Roles
-- ═══════════════════════════════════════════════════════════

-- Role 1: Viewer (Read-only)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name IN (
    'view_audit_dashboard', 'view_requests', 'view_reimbursement_requests',
    'view_petty_cash_requests', 'view_commitments', 'view_purchase_orders',
    'view_invoices', 'view_payments', 'view_audit_logs'
);

-- Role 2: Procurement Officer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    'create_request', 'edit_requests', 'submit_request', 'view_requests',
    'create_reimbursement_request', 'create_petty_cash_request',
    'create_commitment', 'view_commitments', 'create_purchase_order',
    'edit_purchase_order', 'view_purchase_orders', 'request_po_adjustment',
    'view_po_adjustments', 'view_audit_logs', 'view_procurement_dashboard',
    'view_invoices', 'view_payments', 'print_request', 'print_purchase_order',
    'manage_vendors', 'view_vendor_history', 'upload_commitment',
    'upload_purchase_order', 'manage_attachments'
);

-- Role 3: Finance Officer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'approve_request', 'approve_commitment', 'approve_purchase_order',
    'approve_po', 'approve_po_adjustment', 'view_requests',
    'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'create_invoice', 'record_invoice', 'create_payment',
    'record_payment', 'reconcile_petty_cash', 'verify_reimbursement_goods',
    'verify_petty_cash_reconciliation', 'view_finance_dashboard',
    'view_monthly_dashboard', 'view_financial_reports', 'view_audit_logs',
    'print_request', 'print_purchase_order', 'print_invoice', 'monthly_metrics',
    'approve_reimbursement_request', 'approve_petty_cash_request',
    'export_requests'
);

-- Role 4: HOD (Head of Department)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
    'approve_request', 'decline_request', 'approve_commitment',
    'approve_purchase_order', 'approve_po', 'approve_po_adjustment',
    'authorize_petty_cash', 'view_requests', 'view_reimbursement_requests',
    'view_petty_cash_requests', 'view_commitments', 'view_purchase_orders',
    'view_invoices', 'view_payments', 'view_management_dashboard',
    'view_procurement_dashboard', 'view_monthly_dashboard',
    'view_finance_dashboard', 'view_audit_logs', 'management_dashboard',
    'print_request', 'print_purchase_order', 'print_invoice',
    'view_financial_reports', 'monthly_metrics', 'view_approval_analytics',
    'approve_reimbursement_request', 'approve_petty_cash_request',
    'export_requests'
);

-- Role 5: Admin
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    'manage_users', 'manage_system_settings', 'author_override',
    'create_request', 'edit_requests', 'submit_request', 'approve_request',
    'decline_request', 'create_reimbursement_request',
    'create_petty_cash_request', 'authorize_reimbursement',
    'authorize_petty_cash', 'create_commitment', 'approve_commitment',
    'create_purchase_order', 'edit_purchase_order', 'approve_purchase_order',
    'approve_po', 'approve_po_excess', 'request_po_adjustment',
    'approve_po_adjustment', 'create_invoice', 'record_invoice',
    'create_payment', 'record_payment', 'verify_reimbursement_goods',
    'verify_petty_cash_reconciliation', 'reconcile_petty_cash',
    'upload_commitment', 'upload_purchase_order', 'manage_attachments',
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_po_adjustments',
    'view_compliance', 'view_financial_reports', 'view_own_requests',
    'export_requests', 'view_finance_dashboard', 'view_management_dashboard',
    'view_monthly_dashboard', 'view_procurement_dashboard',
    'view_approval_analytics', 'management_dashboard', 'monthly_metrics',
    'print_request', 'print_purchase_order', 'print_invoice',
    'view_rfq_evaluations', 'vote_rfq', 'manage_rfq_committee', 'award_rfq',
    'manage_vendors', 'view_vendor_history', 'view_audit_dashboard'
);

-- Role 6: SuperAdmin (All permissions)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions;

-- Role 7: Evaluation Committee Member
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 7, id FROM permissions WHERE name IN (
    'view_requests', 'view_commitments', 'view_purchase_orders',
    'view_audit_logs', 'view_rfq_evaluations', 'vote_rfq'
);

-- Role 8: Procurement Committee
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 8, id FROM permissions WHERE name IN (
    'approve_request', 'view_requests', 'view_commitments',
    'view_purchase_orders', 'view_audit_logs', 'print_request',
    'view_rfq_evaluations'
);

-- Role 9: Deputy Government Chemist (DGC)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 9, id FROM permissions WHERE name IN (
    'approve_request', 'decline_request', 'view_requests',
    'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_management_dashboard',
    'view_monthly_dashboard', 'view_finance_dashboard',
    'view_financial_reports', 'view_approval_analytics', 'view_compliance',
    'management_dashboard', 'monthly_metrics', 'print_request',
    'print_purchase_order', 'print_invoice', 'export_requests'
);

-- Role 10: Director HRM&A
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 10, id FROM permissions WHERE name IN (
    'approve_request', 'approve_as_director_hrma', 'decline_request',
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_management_dashboard',
    'view_monthly_dashboard', 'view_finance_dashboard',
    'view_financial_reports', 'view_approval_analytics', 'management_dashboard',
    'monthly_metrics', 'print_request', 'print_purchase_order',
    'print_invoice', 'export_requests'
);

-- Role 11: Director Procurement
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 11, id FROM permissions WHERE name IN (
    'approve_request', 'approve_commitment', 'approve_purchase_order',
    'approve_po', 'approve_po_adjustment', 'decline_request',
    'view_requests', 'view_reimbursement_requests', 'view_petty_cash_requests',
    'view_commitments', 'view_purchase_orders', 'view_invoices',
    'view_payments', 'view_audit_logs', 'view_management_dashboard',
    'view_monthly_dashboard', 'view_finance_dashboard',
    'view_financial_reports', 'view_approval_analytics',
    'view_procurement_dashboard', 'view_director_dashboard',
    'management_dashboard', 'monthly_metrics', 'print_request',
    'print_purchase_order', 'print_invoice', 'export_requests',
    'view_rfq_evaluations', 'manage_rfq_committee', 'award_rfq',
    'manage_vendors'
);

-- Role 12: Requestor
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 12, id FROM permissions WHERE name IN (
    'create_request', 'edit_requests', 'submit_request',
    'resubmit_request', 'view_own_requests', 'create_reimbursement_request',
    'create_petty_cash_request', 'print_request'
);

-- ═══════════════════════════════════════════════════════════
-- STEP 3: Secondary Roles (Additional permissions for specific scenarios)
-- ═══════════════════════════════════════════════════════════

-- Branch Head permissions (for supervisory staff)
-- Can authorize reimbursements and petty cash
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT role_id, p.id
FROM (SELECT id as role_id FROM roles WHERE name IN ('HOD', 'Director HRM&A')) r
JOIN permissions p ON p.name IN (
    'authorize_reimbursement', 'authorize_petty_cash',
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation'
);

-- Procurement verification staff
-- Can verify goods but not approve full requests
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT role_id, p.id
FROM (SELECT id as role_id FROM roles WHERE name = 'Procurement Officer') r
JOIN permissions p ON p.name IN (
    'verify_reimbursement_goods', 'verify_petty_cash_reconciliation',
    'award_rfq', 'manage_rfq_committee'
);

-- ═══════════════════════════════════════════════════════════
-- STEP 4: Verification Queries
-- ═══════════════════════════════════════════════════════════

-- Count total permissions created
SELECT COUNT(*) as total_permissions FROM permissions;

-- Verify all 12 roles have permissions
SELECT 
    r.id, r.name,
    COUNT(DISTINCT rp.permission_id) as permission_count
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
WHERE r.id BETWEEN 1 AND 12
GROUP BY r.id, r.name
ORDER BY r.id;

-- Show permission breakdown by category
SELECT 
    CASE 
        WHEN name LIKE 'view_%' THEN 'View'
        WHEN name LIKE 'create_%' THEN 'Create'
        WHEN name LIKE 'approve_%' THEN 'Approve'
        WHEN name LIKE 'authorize_%' THEN 'Authorize'
        WHEN name LIKE 'verify_%' THEN 'Verify'
        WHEN name LIKE 'record_%' THEN 'Record'
        WHEN name LIKE 'edit_%' THEN 'Edit'
        WHEN name LIKE 'upload_%' THEN 'Upload'
        WHEN name LIKE 'manage_%' THEN 'Manage'
        WHEN name LIKE 'vote_%' THEN 'Vote'
        WHEN name LIKE 'award_%' THEN 'Award'
        WHEN name LIKE 'print_%' THEN 'Print'
        WHEN name IN ('submit_request', 'resubmit_request', 'request_po_adjustment', 'decline_request', 'export_requests', 'reconcile_petty_cash') THEN 'Submit/Export'
        WHEN name IN ('management_dashboard', 'monthly_metrics', 'author_override') THEN 'Admin'
        ELSE 'Other'
    END as category,
    COUNT(*) as count
FROM permissions
GROUP BY category
ORDER BY count DESC;

-- List all permissions by role (for audit)
SELECT 
    r.name as role_name,
    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') as permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE r.id BETWEEN 1 AND 12
GROUP BY r.id, r.name
ORDER BY r.id;

-- Find permissions assigned to only one role
SELECT 
    p.name,
    COUNT(DISTINCT rp.role_id) as assigned_to_roles,
    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') as roles
FROM permissions p
LEFT JOIN role_permissions rp ON p.id = rp.permission_id
LEFT JOIN roles r ON rp.role_id = r.id
GROUP BY p.id, p.name
HAVING assigned_to_roles = 1
ORDER BY p.name;

-- Find orphaned permissions (not assigned to any role)
SELECT 
    p.id,
    p.name,
    p.description
FROM permissions p
WHERE NOT EXISTS (
    SELECT 1 FROM role_permissions rp WHERE rp.permission_id = p.id
)
ORDER BY p.name;
