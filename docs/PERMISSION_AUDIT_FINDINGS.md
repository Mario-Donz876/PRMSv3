-- ============================================================================
-- COMPREHENSIVE PERMISSION AUDIT & NEW PERMISSIONS
-- Identifies all existing permissions, missing permissions, and new granular
-- permissions for better access control
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- CURRENT PERMISSIONS IN MIGRATION 012 (42 permissions)
-- ═══════════════════════════════════════════════════════════

-- View Permissions (10)
view_audit_dashboard
view_requests
view_commitments
view_purchase_orders
view_invoices
view_payments
view_audit_logs
view_po_adjustments
view_compliance
view_financial_reports

-- Create Permissions (8)
create_request
create_commitment
create_purchase_order
create_invoice
create_payment
create_reimbursement_request
create_petty_cash_request
edit_requests

-- Approval Permissions (9)
approve_request
approve_commitment
approve_po
approve_purchase_order
approve_po_adjustment
approve_po_excess
decline_request
approve_reimbursement_request
approve_petty_cash_request

-- Edit Permissions (4)
edit_purchase_order
submit_request
request_po_adjustment
record_payment

-- Dashboard Permissions (6)
view_finance_dashboard
view_management_dashboard
view_monthly_dashboard
view_procurement_dashboard
view_approval_analytics
management_dashboard

-- Print Permissions (3)
print_request
print_purchase_order
print_invoice

-- Admin Permissions (2)
manage_users
author_override

-- ═══════════════════════════════════════════════════════════
-- DISCOVERED GAPS & MISSING PERMISSIONS
-- ═══════════════════════════════════════════════════════════

-- Pages without REQUIRE_PERMISSION:
--   reimbursement/list.php - MISSING: view_reimbursement_requests
--   reimbursement/view.php - MISSING: view_reimbursement_requests
--   petty_cash/list.php - MISSING: view_petty_cash_requests
--   petty_cash/view.php - MISSING: view_petty_cash_requests

-- Permissions referenced in sidebar but not in migration:
--   view_own_requests - used in dashboard/requestor.php (line 2)
--   approve_as_director_hrma - used as separate permission
--   view_director_dashboard - Director Procurement dashboard

-- Permissions referenced in code but not formalized:
--   upload_commitment - commitments/upload.php (line 11)
--   upload_purchase_order - po/upload.php (line 2)
--   verify_funds - mentioned in migration 008 but not in 012
--   respond_commitment - mentioned in migration 008
--   direct_procurement - mentioned in migration 008
--   manage_system_settings - admin/settings.php (line 2)
--   record_invoice - create_invoice exists, but record_invoice also mentioned
--   admin_override - used in view.php but not in table

-- ═══════════════════════════════════════════════════════════
-- NEW GRANULAR PERMISSIONS FOR BETTER ACCESS CONTROL
-- ═══════════════════════════════════════════════════════════

-- Reimbursement-Specific Permissions (3)
view_reimbursement_requests          - View all reimbursement requests
authorize_reimbursement              - Authorize reimbursement (Branch Head)
verify_reimbursement_goods           - Verify goods/services for reimbursement

-- Petty Cash-Specific Permissions (3)
view_petty_cash_requests             - View all petty cash requests
authorize_petty_cash                 - Authorize petty cash (Branch Head)
verify_petty_cash_reconciliation     - Verify petty cash 24-hour reconciliation

-- Request Management (3 new)
view_own_requests                    - View only own submitted requests
resubmit_request                     - Resubmit declined requests
export_requests                      - Export request data to CSV/Excel

-- RFQ & Evaluation (4 new)
view_rfq_evaluations                 - View RFQ evaluations
vote_rfq                             - Vote on RFQ evaluations
manage_rfq_committee                 - Add/remove committee members
award_rfq                            - Award RFQ to vendor

-- Vendor Management (2 new)
manage_vendors                       - Add, edit, delete vendors
view_vendor_history                  - View vendor performance history

-- Document Management (3 new)
upload_commitment                    - Upload commitment documents
upload_purchase_order                - Upload PO documents
manage_attachments                   - Add/remove document attachments

-- Finance Operations (3 new)
record_invoice                       - Record receipt of invoice
record_payment                       - Record payment made
reconcile_petty_cash                 - Reconcile petty cash after 24h

-- Administrative (2 new)
manage_system_settings               - Configure system settings
override_approval_chain              - Bypass normal approval chain

-- ═══════════════════════════════════════════════════════════
-- FILES NEEDING REQUIRE_PERMISSION UPDATES
-- ═══════════════════════════════════════════════════════════

-- High Priority (Missing REQUIRE_PERMISSION)
reimbursement/list.php              → view_reimbursement_requests
reimbursement/view.php              → view_reimbursement_requests
reimbursement/submit.php            → create_reimbursement_request
petty_cash/list.php                 → view_petty_cash_requests
petty_cash/view.php                 → view_petty_cash_requests

-- Medium Priority (Have permission but could be more specific)
rfq/create.php                       → edit_requests (could be: create_rfq)
rfq/add_committee.php                → view_requests (could be: manage_rfq_committee)
rfq/remove_committee.php             → view_requests (could be: manage_rfq_committee)
rfq/vote.php                         → view_requests (could be: vote_rfq)
rfq/award.php                        → create_request (could be: award_rfq)
po/upload.php                        → upload_purchase_order (already correct)
commitments/upload.php               → upload_commitment (already correct)
vendors/add.php                      → edit_requests (could be: manage_vendors)
vendors/edit.php                     → edit_requests (could be: manage_vendors)

-- Low Priority (Already have appropriate permissions)
admin/settings.php                   → manage_system_settings (exists)
dashboard/director_hrma.php          → approve_as_director_hrma (specific)
dashboard/director_procurement.php   → view_director_dashboard (specific)

-- ═══════════════════════════════════════════════════════════
-- RECOMMENDATION SUMMARY
-- ═══════════════════════════════════════════════════════════

ACTION 1: Add REQUIRE_PERMISSION to 5 files (HIGH PRIORITY)
- reimbursement/list.php - NEW permission: view_reimbursement_requests
- reimbursement/view.php - NEW permission: view_reimbursement_requests
- petty_cash/list.php - NEW permission: view_petty_cash_requests
- petty_cash/view.php - NEW permission: view_petty_cash_requests
- Check if reimbursement/submit.php exists and add REQUIRE_PERMISSION

ACTION 2: Create 23 new granular permissions in migration 013

ACTION 3: Update 8 files to use more specific permissions (optional, improves granularity)
- RFQ operations (4 files)
- Vendor management (2 files)
- Finance operations (2 files)

ACTION 4: Create role-permission mapping for all 65 total permissions

IMPACT: 
- Total permissions: 42 (current) + 23 (new) = 65 permissions
- Better access control with specific operations
- Easier to audit and manage permissions
- Can assign permissions at granular level per role

