# Role-Based Default Permissions Assignment Guide

## Overview
This document describes the default permissions assigned to each system role in the PRMS procurement system. All permissions are designed based on the Standard Operating Procedures (SOP) and security principles of least privilege.

## Migration Script
**File:** `migrations/012_assign_default_role_permissions.sql`

**Execution:**
```bash
mysql -u username -p database_name < migrations/012_assign_default_role_permissions.sql
```

---

## Role Permissions Matrix

### Role 1: Viewer (Read-Only Access)
**Purpose:** Audit and compliance viewing only
**Permissions:**
- `view_audit_dashboard` - Access audit logs and dashboard
- `view_requests` - View requests
- `view_commitments` - View commitments
- `view_purchase_orders` - View POs
- `view_invoices` - View invoices
- `view_payments` - View payment records
- `view_audit_logs` - View audit trail

**Use Case:** Internal auditors, compliance officers, read-only viewers

---

### Role 2: Procurement Officer
**Purpose:** Create and manage procurement processes
**Permissions:**
- `create_request` - Create new requests
- `edit_requests` - Edit requests and create RFQs
- `submit_request` - Submit requests for approval
- `view_requests` - View all requests
- `create_commitment` - Create commitments
- `view_commitments` - View commitments
- `create_purchase_order` - Create POs
- `edit_purchase_order` - Edit POs
- `view_purchase_orders` - View POs
- `request_po_adjustment` - Request PO variations
- `view_po_adjustments` - View PO adjustments
- `view_audit_logs` - View audit trail
- `view_procurement_dashboard` - Procurement metrics
- `view_invoices` - View invoices
- `view_payments` - View payments
- `print_request` - Print requests
- `print_purchase_order` - Print POs
- `create_reimbursement_request` - Create reimbursements
- `create_petty_cash_request` - Create petty cash requests

**Use Case:** Procurement staff, process coordinators

---

### Role 3: Finance Officer
**Purpose:** Approve financial aspects and manage invoices/payments
**Permissions:**
- `approve_request` - Approve requests at finance stage
- `approve_commitment` - Approve commitments
- `approve_po` / `approve_purchase_order` - Approve POs
- `approve_po_adjustment` - Approve PO variations
- All view permissions (requests, commitments, POs, invoices, payments)
- `create_invoice` - Record invoices
- `record_invoice` - Process received invoices
- `create_payment` - Create payment records
- `record_payment` - Process payments
- Dashboard access (finance, monthly, management)
- `view_financial_reports` - View financial reports
- `monthly_metrics` - Monthly financial metrics
- `print_*` - Print documents
- `approve_reimbursement_request` - Approve reimbursements
- `approve_petty_cash_request` - Approve petty cash

**Use Case:** Finance staff, fiscal officers, payment approvers

---

### Role 4: HOD (Head of Department)
**Purpose:** Department-level approvals and oversight
**Permissions:**
- `approve_request` - Approve at HOD stage
- `decline_request` - Reject requests
- `approve_commitment` - Approve commitments
- `approve_po` / `approve_purchase_order` - Approve POs
- `approve_po_adjustment` - Approve PO variations
- All view permissions
- All dashboard access (management, procurement, finance, monthly)
- `view_approval_analytics` - Approval metrics
- `management_dashboard` - Department overview
- `monthly_metrics` - Monthly metrics
- Print permissions
- `approve_petty_cash_request` - Approve petty cash
- `approve_reimbursement_request` - Approve reimbursements

**Use Case:** Department heads, branch managers, supervisors

---

### Role 5: Admin
**Purpose:** System administration with explicit permission grants
**Permissions:** Same as SuperAdmin (all permissions explicitly assigned)
- Full CRUD on all requests, commitments, POs, invoices, payments
- User management (`manage_users`)
- All view and approval permissions
- PO excess approvals
- Override capabilities

**Use Case:** System administrators (with audit trails)

---

### Role 6: SuperAdmin
**Purpose:** Full system access (same as Admin but with implicit access)
**Permissions:** All permissions (same list as Admin)

**Use Case:** Super administrators, system owners

**Note:** SuperAdmin role bypasses permission checks in `hasPermission()` helper, but role_permissions table is kept in sync for consistency.

---

### Role 7: Evaluation Committee Member
**Purpose:** Limited access for RFQ evaluation activities
**Permissions:**
- `view_requests` - View requests
- `view_commitments` - View commitments
- `view_purchase_orders` - View POs
- `view_audit_logs` - View audit logs

**Use Case:** Committee members scoring RFQ proposals

---

### Role 8: Procurement Committee
**Purpose:** Committee-level approval and recommendation
**Permissions:**
- `approve_request` - Approve at committee stage
- `view_requests` - View requests
- `view_commitments` - View commitments
- `view_purchase_orders` - View POs
- `view_audit_logs` - View audit logs
- `print_request` - Print requests

**Use Case:** Procurement committee members making recommendations

---

### Role 9: Deputy Government Chemist (DGC)
**Purpose:** Final approval authority and compliance oversight
**Permissions:**
- `approve_request` - Approve at DGC stage
- `decline_request` - Reject requests
- All view permissions
- All dashboard access (management, finance, monthly)
- `view_financial_reports` - Financial reports
- `view_approval_analytics` - Approval analytics
- `view_compliance` - Compliance dashboard
- Print permissions

**Use Case:** DGC executive, final approval authority

---

### Role 10: Director HRM&A
**Purpose:** Director-level approval for HRM&A branch
**Permissions:**
- `approve_request` - Approve at Director stage
- `decline_request` - Reject requests
- All view permissions
- Dashboard access (management, finance, monthly)
- `view_approval_analytics` - Approval analytics
- `view_financial_reports` - Financial reports
- Print permissions

**Use Case:** Director of Human Resource Management & Administration

---

### Role 11: Director Procurement
**Purpose:** Director-level procurement operations oversight
**Permissions:**
- `approve_request` - Approve at director stage
- `approve_commitment` - Approve commitments
- `approve_po` / `approve_purchase_order` - Approve POs
- `approve_po_adjustment` - Approve PO variations
- `decline_request` - Reject requests
- All view permissions
- Dashboard access (management, procurement, finance, monthly)
- `view_approval_analytics` - Approval analytics
- `view_financial_reports` - Financial reports
- Print permissions

**Use Case:** Director of Procurement Operations

---

### Role 12: Requestor
**Purpose:** Standard user who creates and submits requests
**Permissions:**
- `create_request` - Create new requests
- `edit_requests` - Edit own requests
- `submit_request` - Submit for approval
- `view_requests` - View own requests
- `print_request` - Print requests
- `create_reimbursement_request` - Create reimbursements
- `create_petty_cash_request` - Create petty cash requests

**Use Case:** Regular employees requesting goods/services

---

## Permission Categories

### View Permissions (Read-Only)
- `view_audit_dashboard`
- `view_requests`
- `view_commitments`
- `view_purchase_orders`
- `view_invoices`
- `view_payments`
- `view_audit_logs`
- `view_po_adjustments`
- `view_compliance`
- `view_financial_reports`

### Create Permissions
- `create_request`
- `create_commitment`
- `create_purchase_order`
- `create_invoice`
- `create_payment`
- `create_reimbursement_request`
- `create_petty_cash_request`

### Approval Permissions
- `approve_request`
- `approve_commitment`
- `approve_po` / `approve_purchase_order`
- `approve_po_adjustment`
- `approve_po_excess`
- `approve_reimbursement_request`
- `approve_petty_cash_request`
- `decline_request`

### Edit Permissions
- `edit_requests`
- `edit_purchase_order`
- `request_po_adjustment` (request changes)
- `record_invoice` (record received)
- `record_payment` (record processed)

### Dashboard Permissions
- `view_audit_dashboard`
- `view_finance_dashboard`
- `view_management_dashboard`
- `view_monthly_dashboard`
- `view_procurement_dashboard`
- `view_approval_analytics`
- `management_dashboard`
- `monthly_metrics`

### Administrative Permissions
- `manage_users`
- `author_override`

### Print Permissions
- `print_request`
- `print_purchase_order`
- `print_invoice`

---

## Audit Trail & Security Notes

1. **All Changes Logged:** User permission assignments are audited in `audit_log` table
2. **Override Capability:** Only Admin/SuperAdmin have `author_override` permission
3. **Least Privilege:** Each role has only required permissions for their duties
4. **Immutable Role Names:** Role IDs are constants in `config/app.php`
5. **Dynamic Permission Checks:** Use `hasPermission()` function for runtime checks

---

## Testing the Migration

After running the migration, verify with these queries:

### Check all roles have permissions:
```sql
SELECT r.name, COUNT(rp.permission_id) as permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
GROUP BY r.id, r.name
ORDER BY r.id;
```

### Check specific role permissions:
```sql
SELECT p.name
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
WHERE rp.role_id = 3  -- Finance Officer
ORDER BY p.name;
```

### Check if all referenced permissions exist:
```sql
SELECT COUNT(*) FROM role_permissions rp
WHERE NOT EXISTS (SELECT 1 FROM permissions p WHERE p.id = rp.permission_id);
-- Should return 0
```

---

## Troubleshooting

**Issue: "Permission denied" after migration**
- Verify user has correct role_id assigned
- Check role has the required permission via query above
- Clear browser cache/session

**Issue: Some permissions missing**
- Run the migration with appropriate database credentials
- Check INSERT IGNORE results in MySQL logs
- Verify permissions table exists and is populated

**Issue: Need to add new role**
1. Insert role into `roles` table
2. Add new INSERT IGNORE block in this migration
3. Re-run migration or insert role_permissions manually

---

## Permission Maintenance

### Add a new permission:
```sql
INSERT INTO permissions (name, description) VALUES
('new_permission', 'Description of what this allows');
```

### Grant permission to role:
```sql
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT {role_id}, id FROM permissions WHERE name = 'new_permission';
```

### Remove permission from role:
```sql
DELETE FROM role_permissions
WHERE role_id = {role_id} 
  AND permission_id = (SELECT id FROM permissions WHERE name = 'permission_name');
```

### Grant permission to individual user (override):
```sql
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT {user_id}, id, 1 FROM permissions WHERE name = 'permission_name';
```

---

## Permission Inheritance

- Users inherit permissions from their assigned role
- User-level overrides can grant/deny individual permissions
- Overrides can have expiration dates (`expires_at` column)
- SuperAdmin bypasses all permission checks via `hasPermission()` helper

---

## Related Files

- `config/app.php` - Role ID constants
- `config/helper.php` - `hasPermission()` function implementation
- `config/page_guard.php` - Permission enforcement at page entry
- `users/permissions.php` - UI for managing permissions
- `migrations/006_sop_role_permission_mapping.sql` - Initial SOP-based migration
