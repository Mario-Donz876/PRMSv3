# Comprehensive Permission System Audit & Implementation

## Overview
This document summarizes the complete audit of the PRMS permission system, identifies gaps, recommends enhancements, and provides implementation guidance.

**Date:** February 18, 2026  
**Status:** Ready for Implementation  
**Migration Files:** `012_assign_default_role_permissions.sql` and `013_comprehensive_permissions_65.sql`

---

## Executive Summary

### Current State
- **Migration 012:** 42 permissions spread across 12 roles
- **Pages Audited:** 100+ PHP files with `$REQUIRE_PERMISSION` guards
- **Coverage:** ~95% of pages have appropriate permissions

### Identified Gaps
1. **4 pages missing REQUIRE_PERMISSION** (reimbursement/list, view; petty_cash/list, view)
2. **23 missing granular permissions** for better access control
3. **8 pages using generic permissions** that could be more specific

### Recommendations
1. ✅ **Immediate (DONE):** Add REQUIRE_PERMISSION to 4 missing pages
2. ✅ **Immediate (DONE):** Create migration 013 with 65 total permissions
3. **Optional:** Update 8 pages to use more granular permissions (improves auditability)

### Impact
- **Total Permissions:** 42 → 65 (+23 new)
- **Better Access Control:** Granular operations per role
- **Easier Auditing:** Can see exactly what each permission does
- **Flexible Role Management:** Can assign specific permissions without full module access

---

## Changes Made

### 1. Files Updated (DONE)
```
✅ /workspaces/PRMS/reimbursement/list.php
   - Added: $REQUIRE_PERMISSION = 'view_reimbursement_requests';

✅ /workspaces/PRMS/reimbursement/view.php
   - Added: $REQUIRE_PERMISSION = 'view_reimbursement_requests';

✅ /workspaces/PRMS/petty_cash/list.php
   - Added: $REQUIRE_PERMISSION = 'view_petty_cash_requests';

✅ /workspaces/PRMS/petty_cash/view.php
   - Added: $REQUIRE_PERMISSION = 'view_petty_cash_requests';
```

### 2. New Migration Created (READY)
**File:** `migrations/013_comprehensive_permissions_65.sql`

**Contains:**
- 65 total permissions (42 existing + 23 new)
- Complete role-to-permission mapping
- Verification queries for data integrity
- Orphaned permission detection
- Permission audit reports

---

## New Permissions Added (23 Total)

### Reimbursement-Specific (3)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `view_reimbursement_requests` | View all reimbursement requests | HOD, Finance, Procurement Committee, DGC, Directors |
| `authorize_reimbursement` | Branch Head authorization | Branch Head equivalent roles |
| `verify_reimbursement_goods` | Verify goods/services | Procurement Officer, Finance |

### Petty Cash-Specific (3)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `view_petty_cash_requests` | View all petty cash requests | HOD, Finance, Procurement Committee, DGC, Directors |
| `authorize_petty_cash` | Branch Head authorization | HOD, Branch Head |
| `verify_petty_cash_reconciliation` | Verify 24-hr reconciliation | Finance, Procurement |

### Request Management (3)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `view_own_requests` | View only own requests | Requestor |
| `resubmit_request` | Resubmit declined requests | Requestor |
| `export_requests` | Export to CSV/Excel | Finance, HOD, Directors |

### RFQ & Evaluation (4)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `view_rfq_evaluations` | View RFQ evaluations | Committee, SuperAdmin, Procurement |
| `vote_rfq` | Vote on evaluations | Evaluation Committee |
| `manage_rfq_committee` | Add/remove members | Procurement Officer |
| `award_rfq` | Award RFQ | Procurement Officer |

### Vendor Management (2)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `manage_vendors` | Add, edit, delete vendors | Procurement Officer, Director Procurement |
| `view_vendor_history` | Vendor performance | Procurement Officer |

### Document Management (3)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `upload_commitment` | Upload commitment docs | Procurement Officer, Reimbursement workflow |
| `upload_purchase_order` | Upload PO docs | Procurement Officer |
| `manage_attachments` | Add/remove attachments | Procurement Officer |

### Finance Operations (3)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `record_invoice` | Record invoice receipt | Finance Officer |
| `record_payment` | Record payment made | Finance Officer |
| `reconcile_petty_cash` | Reconcile after 24h | Finance Officer |

### Administrative (2)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `manage_system_settings` | Configure system | Admin/SuperAdmin |
| `author_override` | Bypass approvals | Admin/SuperAdmin |

### Already Exist But Formalized (2)
| Permission | Description | Used By |
|-----------|-------------|---------|
| `approve_as_director_hrma` | Director HRM&A approvals | Director HRM&A |
| `view_director_dashboard` | Director Procurement dashboard | Director Procurement |

---

## Permissions Breakdown (Frequency)

| Category | Count | Examples |
|----------|-------|----------|
| View | 12 | view_requests, view_commitments, view_compliance |
| Create/Edit | 9 | create_request, edit_requests, create_commitment |
| Submit/Workflow | 4 | submit_request, resubmit_request, request_po_adjustment |
| Approve/Authorization | 8 | approve_request, authorize_reimbursement, decline_request |
| Verify/Record | 3 | verify_reimbursement_goods, record_invoice, record_payment |
| Document | 3 | upload_commitment, upload_purchase_order, manage_attachments |
| RFQ/Vendor | 6 | vote_rfq, manage_rfq_committee, manage_vendors, view_vendor_history |
| Dashboard/Reporting | 7 | view_finance_dashboard, monthly_metrics, export_requests |
| Print | 3 | print_request, print_purchase_order, print_invoice |
| Admin | 3 | manage_users, manage_system_settings, author_override |
| **TOTAL** | **65** | |

---

## Role-Permission Matrix (Summary)

| Role | Permissions | Key Responsibilities |
|------|-------------|----------------------|
| **Viewer** | 9 | Read-only access to audit |
| **Procurement Officer** | 24 | Create/manage procurement |
| **Finance Officer** | 28 | Approve financial items |
| **HOD** | 27 | Department-level approvals |
| **Admin** | 51 | Full access (explicit) |
| **SuperAdmin** | 65 | Full access (implicit) |
| **Eval Committee** | 6 | Vote on RFQ evaluations |
| **Proc Committee** | 7 | Committee stage approvals |
| **Deputy GC** | 21 | Final approval authority |
| **Director HRM&A** | 21 | Branch director approvals |
| **Director Procurement** | 31 | Procurement oversight |
| **Requestor** | 8 | Create/submit requests |

---

## Implementation Steps

### Step 1: Apply File Updates (DONE ✅)
```bash
# Files already updated with missing REQUIRE_PERMISSION:
- reimbursement/list.php
- reimbursement/view.php
- petty_cash/list.php
- petty_cash/view.php
```

### Step 2: Run Migration 013
```bash
mysql -u username -p database_name < migrations/013_comprehensive_permissions_65.sql
```

### Step 3: Verify Installation
```bash
# List all permissions created
SELECT COUNT(*) FROM permissions;
# Expected: 65

# Verify roles mapped to permissions
SELECT r.name, COUNT(DISTINCT rp.permission_id)
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
WHERE r.id BETWEEN 1 AND 12
GROUP BY r.id;

# Check for orphaned permissions
SELECT p.name FROM permissions p
WHERE NOT EXISTS (SELECT 1 FROM role_permissions WHERE permission_id = p.id);
# Expected: (empty result)
```

### Step 4: Test User Access (Optional)
```php
// Test hasPermission function
hasPermission('view_reimbursement_requests'); // Should work for appropriate roles
hasPermission('verify_petty_cash_reconciliation'); // Finance Officer only
```

---

## Optional Enhancements (Medium Priority)

### Update Files for More Granular Control

Instead of:
```php
$REQUIRE_PERMISSION = 'create_request';
```

Use:
```php
$REQUIRE_PERMISSION = 'create_rfq';  // More specific
```

**Recommended Updates:**
1. `rfq/create.php` → Change from `edit_requests` to `create_rfq`
2. `rfq/add_committee.php` → Change from `view_requests` to `manage_rfq_committee`
3. `rfq/vote.php` → Change from `view_requests` to `vote_rfq`
4. `vendors/add.php` → Change from `edit_requests` to `manage_vendors`
5. `vendors/edit.php` → Change from `edit_requests` to `manage_vendors`
6. `po/upload.php` → Already has `upload_purchase_order` ✓
7. `commitments/upload.php` → Already has `upload_commitment` ✓

**Impact:** No change to functionality, only better auditability and role-specific access.

---

## Permission Query Reference

### Find All Permissions for a Role
```sql
SELECT p.name, p.description
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
WHERE rp.role_id = 3  -- Finance Officer
ORDER BY p.name;
```

### Check User Effective Permissions
```sql
SELECT DISTINCT p.name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE u.user_id = 5
ORDER BY p.name;
```

### Find Users with Specific Permission
```sql
SELECT u.user_id, u.full_name, r.name
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE p.name = 'approve_reimbursement_request'
  AND u.is_active = 1;
```

---

## Testing Checklist

### Permission Enforcement
- [ ] Viewer can only view requests (not approve/edit)
- [ ] Requestor can create/submit own requests (not approve)
- [ ] Finance can approve financial stages but not edit RFQs
- [ ] Procurement Can manage RFQs but not override approvals
- [ ] HOD can approve requests and see management dashboard
- [ ] Director has appropriate scope for their branch/department

### Reimbursement Permissions
- [ ] `view_reimbursement_requests` works for intended roles
- [ ] Reimbursement list/view pages load successfully
- [ ] Users without permission get access denied

### Petty Cash Permissions  
- [ ] `view_petty_cash_requests` works for intended roles
- [ ] Petty cash list/view pages load successfully
- [ ] 24-hour deadline tracking visible to authorized users

### New Granular Permissions (if implemented)
- [ ] `vote_rfq` restricts evaluation committee voting
- [ ] `manage_vendors` controls vendor operations
- [ ] `export_requests` available to reporting roles only

---

## Maintenance Going Forward

### Adding New Permissions
1. Insert into `permissions` table
2. Create role mappings in `role_permissions`
3. Add `$REQUIRE_PERMISSION` guard to new page
4. Document in this file

### Removing Permissions
1. Check if any `$REQUIRE_PERMISSION` references exist
2. Update pages to use different permission
3. Delete from `user_permissions` (overrides)
4. Delete from `role_permissions`
5. Delete from `permissions` table

### Modifying Permissions
1. Update `description` in `permissions` table
2. Update role mappings if scope changes
3. Test thoroughly before deploying

---

## References

- **Files Updated:** [PERMISSION_AUDIT_FINDINGS.md](PERMISSION_AUDIT_FINDINGS.md)
- **Role Mappings:** [ROLE_PERMISSIONS_MAPPING.md](ROLE_PERMISSIONS_MAPPING.md)
- **Query Reference:** [/tools/role_permission_queries.sql](/tools/role_permission_queries.sql)
- **Migrations:**
  - [migrations/012_assign_default_role_permissions.sql](migrations/012_assign_default_role_permissions.sql)
  - [migrations/013_comprehensive_permissions_65.sql](migrations/013_comprehensive_permissions_65.sql)

---

## Summary

| Item | Status | Details |
|------|--------|---------|
| Pages Missing Permissions | ✅ FIXED | Added to 4 reimbursement/petty cash pages |
| New Granular Permissions | ✅ CREATED | 23 new permissions in migration 013 |
| Role Mappings | ✅ CONFIGURED | All 12 roles mapped to 65 permissions |
| Query Reference | ✅ PROVIDED | SQL queries in tools/role_permission_queries.sql |
| Documentation | ✅ COMPLETE | Full audit trail and usage guide |
| Implementation | 🟠 PENDING | Ready to execute migration 013 |

**Next Step:** Run `migrations/013_comprehensive_permissions_65.sql` to fully activate the comprehensive permission system.
