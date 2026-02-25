-- ============================================================================
-- Migration: SOP-based role ↔ permission auto-mapping
-- Purpose:   Ensure every role has the permissions it needs per SOP, and that
--            all permissions referenced by $REQUIRE_PERMISSION guards exist.
-- Safety:    Uses INSERT IGNORE — existing mappings are untouched.
-- Date:      2026-02-16
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- STEP 1: Create any missing permissions used by page guards
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO permissions (name, description) VALUES
('edit_requests',            'Edit procurement requests, create RFQs, manage vendors'),
('view_approval_analytics',  'Access approval analytics dashboard'),
('view_compliance',          'Access compliance dashboard'),
('management_dashboard',     'Access management overview dashboard'),
('monthly_metrics',          'Access monthly financial metrics dashboard'),
('view_financial_reports',   'View financial reports (branch summary/outstanding)'),
('print_invoice',            'Print invoice PDF');


-- ═══════════════════════════════════════════════════════════
-- STEP 2: SOP role → permission mapping
-- Uses subqueries for permission IDs so it works regardless
-- of auto-increment values. INSERT IGNORE skips duplicates.
-- ═══════════════════════════════════════════════════════════

-- ───────────────────────────────────────────────────────────
-- Role 1: Viewer  (Read-only audit access)
-- SOP: Can view requests, commitments, POs, invoices, audit
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
-- SOP: Create/edit/submit requests, create RFQs, manage
--      vendors, create commitments, create POs, view all
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
    'view_audit_logs',
    'view_procurement_dashboard',
    'view_invoices',
    'view_payments',
    'print_request',
    'print_purchase_order'
);

-- ───────────────────────────────────────────────────────────
-- Role 3: Finance Officer
-- SOP: Approve requests (finance stage), approve commitments
--      (stage 2), approve POs, invoices, payments, reports
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
    'monthly_metrics'
);

-- ───────────────────────────────────────────────────────────
-- Role 4: HOD  (Head of Department)
-- SOP: Approve requests (HOD stage), approve commitments
--      (stage 1), approve POs, view dashboards
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
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
    'view_approval_analytics'
);

-- ───────────────────────────────────────────────────────────
-- Role 5: Admin
-- SOP: System administration, user management, all views
-- (SuperAdmin bypasses checks, but Admin needs explicit grants)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE name IN (
    'manage_users',
    'create_request',
    'edit_requests',
    'submit_request',
    'approve_request',
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
    'print_invoice'
);

-- ───────────────────────────────────────────────────────────
-- Role 6: SuperAdmin  (has_permission() always returns true,
--          but explicit grants keep role_permissions consistent)
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions WHERE name IN (
    'manage_users',
    'create_request',
    'edit_requests',
    'submit_request',
    'approve_request',
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
    'print_invoice'
);

-- ───────────────────────────────────────────────────────────
-- Role 7: Evaluation Committee Member
-- SOP: View requests/RFQs assigned to them, vote on evaluations
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
-- SOP: Recommend requests at COMMITTEE_RECOMMENDED stage,
--      view requests and procurement data
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
-- SOP: Final approval authority, external compliance checks,
--      award RFQs, view all dashboards
-- ───────────────────────────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 9, id FROM permissions WHERE name IN (
    'approve_request',
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
