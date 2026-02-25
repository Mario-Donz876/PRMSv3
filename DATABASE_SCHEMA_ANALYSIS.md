# Database Schema & Code Correspondence Analysis

## Summary
The PRMS project has several **critical misalignments** between the database schema (`prmsv2.sql`) and the PHP code. This document outlines all identified issues and recommended fixes.

---

## 🚨 CRITICAL ISSUES

### 1. Database Connection Mismatch
**Severity:** ⚠️ CRITICAL

**Issue:**
- **Config file** (`config/db.php`): Points to database `u153072617_dgc_procure_sy`
- **SQL dump** (`prmsv2.sql`): Contains database name `u153072617_prms`

**Impact:** Application will fail to connect if the database name in config doesn't match the actual database.

**Fix:**
- Update `config/db.php` to use the correct database name, OR
- Rename/recreate the database to match the config

---

### 2. Missing Tables Referenced in Code
**Severity:** ⚠️ CRITICAL

#### Table: `compliance_approvals`
**Location:** `dashboard/compliance.php` (lines 11-16, 37-38, 55-62)
**Problem:** Code references non-existent table `compliance_approvals`

```php
// BROKEN CODE - Table doesn't exist
LEFT JOIN compliance_approvals ca ON pr.id = ca.entity_id
```

**Fix Option A:** Create the missing table:
```sql
CREATE TABLE `compliance_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `approval_body` varchar(100),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_entity` (`entity_type`, `entity_id`)
);
```

**Fix Option B:** Remove the compliance dashboard or refactor to use existing tables like `request_approvals`

#### Table: `system_config`
**Location:** 
- `config/workflow.php` (lines 98, 108)
- `procurement/add.php` (lines 10, 18)

**Problem:** Code attempts to fetch configuration values from non-existent table.

```php
// BROKEN CODE
$stmt = $pdo->prepare("SELECT config_value FROM system_config WHERE config_key = 'petty_cash_limit'");
```

**Fix:** Create configuration table:
```sql
CREATE TABLE `system_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `config_key` varchar(100) NOT NULL UNIQUE,
  `config_value` varchar(255),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default configuration values
INSERT INTO `system_config` (config_key, config_value) VALUES
('petty_cash_limit', '50000'),
('direct_procurement_threshold', '3000000');
```

---

### 3. Column Name Mismatches
**Severity:** ⚠️ CRITICAL

#### `procurement_requests` Table
| Code References | Database Column | Status | Issue |
|---|---|---|---|
| `pr.id` | `request_id` | ❌ MISMATCH | Used in `dashboard/compliance.php` |
| `pr.title` | `description` | ❌ MISMATCH | Used in `dashboard/compliance.php` line 53 |
| `request_type` | NOT EXISTS | ❌ MISSING | Used in multiple dashboards |
| `pr.request_type` | N/A | ❌ FIELD MISSING | N/A |

**Files with issues:**
- `dashboard/compliance.php` - Uses `pr.id` and `pr.title` (should be `request_id`, `description`)
- `dashboard/requestor.php` - References `request_type` column
- `dashboard/director_hrma.php` - References `request_type` column
- `dashboard/director_procurement.php` - References `request_type` column
- `commitments/upload.php` - References `request['request_type']`

**Fix:** 
1. Add `request_type` column to `procurement_requests` table:
```sql
ALTER TABLE `procurement_requests` ADD COLUMN `request_type` ENUM('REGULAR', 'EXPEDITED', 'EMERGENCY') DEFAULT 'REGULAR' AFTER `description`;
```

2. Update all code references from `pr.id` → `pr.request_id` and `pr.title` → `pr.description` in compliance.php

---

### 4. Missing Role Definitions
**Severity:** ⚠️ MEDIUM

**Issue:** `config/app.php` defines role constants that don't exist in the `roles` table:

```php
const ROLE_DIRECTOR_HRMA = 10;           // NOT IN DATABASE
const ROLE_DIRECTOR_PROCUREMENT = 11;    // NOT IN DATABASE
```

**Database roles:**
- Only IDs 1-9 exist (Viewer through Deputy Government Chemist)

**Database schema has:** Roles with IDs 1-9
**Missing in database:**
- ROLE_DIRECTOR_HRMA (10)
- ROLE_DIRECTOR_PROCUREMENT (11)
- ROLE_REQUESTOR (12) - defined in code but not created

**Fix:** Insert missing roles:
```sql
INSERT INTO `roles` (id, name, description) VALUES
(10, 'Director HRM&A', 'Director of Human Resource Management and Administration'),
(11, 'Director Procurement', 'Director of Procurement Operations'),
(12, 'Requestor', 'Employee submitting procurement requests');
```

---

## ⚠️ MEDIUM-LEVEL ISSUES

### 5. Redundant/Duplicate Indexes
**Issue:** `branches` table has duplicate unique constraints:
```sql
UNIQUE KEY `uq_branch_name` (`branch_name`),
UNIQUE KEY `branch_name` (`branch_name`)  -- DUPLICATE
```

**Fix:** Remove the redundant index:
```sql
ALTER TABLE `branches` DROP INDEX `branch_name`;
```

### 6. Similar duplicate issues found in other tables:
- `invoices` - `uq_invoice_number` and `invoice_number` (both unique on same column)
- `payments` - `uq_payment_reference` and `payment_reference` (both unique on same column)
- `purchase_orders` - `po_number` duplicated uniqueness
- `procurement_requests` - Multiple duplicate unique indexes on `request_number`
- `users` - Duplicate `email` unique constraint

**Fix:** Remove all duplicate constraints:
```sql
ALTER TABLE `invoices` DROP INDEX `invoice_number`;
ALTER TABLE `payments` DROP INDEX `payment_reference`;
ALTER TABLE `purchase_orders` DROP INDEX `commitment_id`;
ALTER TABLE `procurement_requests` DROP INDEX `request_number_4`, DROP INDEX `request_number_5`;
ALTER TABLE `users` DROP INDEX `uq_user_email`;
```

---

## 📋 DATA INTEGRITY ISSUES

### 7. Orphaned/Invalid Foreign Key References

**Issue:** `dashboard/compliance.php` references `pr.id` which doesn't exist:
```php
LEFT JOIN compliance_approvals ca ON pr.id = ca.entity_id  // NOT A COLUMN!
```

Should be: `pr.request_id = ca.entity_id`

**Files affected:**
- `dashboard/compliance.php` (multiple occurrences)

---

## 🔧 RECOMMENDED FIXES PRIORITY

### Phase 1: Critical (Must Fix Before Deployment)
1. ✅ Fix database name in `config/db.php` or update SQL dump destination
2. ✅ Create `compliance_approvals` table OR refactor compliance dashboard
3. ✅ Create `system_config` table with default values
4. ✅ Add `request_type` column to `procurement_requests`
5. ✅ Fix column name references in `compliance.php` (`pr.id` → `pr.request_id`, `pr.title` → `pr.description`)
6. ✅ Add missing role definitions to `roles` table (IDs 10-12)

### Phase 2: Important (Code Quality)
7. Remove duplicate unique indexes from all tables
8. Add foreign key constraints to ensure referential integrity
9. Validate all dashboard queries use correct column names

### Phase 3: Future Enhancements
10. Consider adding explicit table prefixes to SQL queries to avoid ambiguity
11. Consider adding a database migration system
12. Add data validation for enum values

---

## 📊 Table-by-Table Verification

| Table | Status | Issues |
|---|---|---|
| `approval_rules` | ✓ OK | None |
| `approval_steps` | ✓ OK | None |
| `approval_transactions` | ✓ OK | None |
| `approval_workflows` | ✓ OK | None |
| `audit_log` | ✓ OK | None |
| `branches` | ⚠️ DUPLICATE INDEX | Remove redundant `branch_name` unique index |
| `commitments` | ✓ OK | None |
| `compliance_approvals` | ❌ MISSING | Must create |
| `external_approvals` | ✓ OK | None |
| `invoices` | ⚠️ DUPLICATE INDEX | Remove `invoice_number` unique index |
| `password_resets` | ✓ OK | None |
| `payments` | ⚠️ DUPLICATE INDEX | Remove `payment_reference` unique index |
| `permissions` | ✓ OK | None |
| `po_adjustment_log` | ✓ OK | None |
| `po_items` | ✓ OK | None |
| `po_variations` | ✓ OK | None |
| `po_warnings` | ✓ OK | None |
| `procurement_requests` | ❌ COLUMN MISSING | Add `request_type` column; has duplicate indexes |
| `procurement_request_items` | ✓ OK | None |
| `purchase_orders` | ⚠️ DUPLICATE INDEX | Remove redundant unique constraint on `commitment_id` |
| `request_approvals` | ✓ OK | None |
| `rfqs` | ✓ OK | None |
| `rfq_evaluation_committee` | ✓ OK | None |
| `rfq_evaluation_reports` | ✓ OK | None |
| `rfq_quotes` | ✓ OK | None |
| `rfq_scores` | ✓ OK | None |
| `rfq_vendors` | ✓ OK | None |
| `rfq_votes` | ✓ OK | None |
| `roles` | ❌ INCOMPLETE | Missing roles with IDs 10-12 |
| `role_permissions` | ✓ OK | None |
| `system_alerts` | ✓ OK | None |
| `system_config` | ❌ MISSING | Must create |
| `users` | ⚠️ DUPLICATE INDEX | Remove `uq_user_email` unique index |
| `user_permissions` | ✓ OK | None |
| `vendors` | ✓ OK | None |

---

## ✅ Recommended Creation/Alter Scripts

Execute in order:

```sql
-- 1. Add missing table: compliance_approvals
CREATE TABLE `compliance_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `approval_body` varchar(100),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add missing table: system_config
CREATE TABLE `system_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` varchar(255),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uq_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insert default configurations
INSERT INTO `system_config` (config_key, config_value) VALUES
('petty_cash_limit', '50000'),
('direct_procurement_threshold', '3000000');

-- 4. Add missing column to procurement_requests
ALTER TABLE `procurement_requests` 
ADD COLUMN `request_type` ENUM('REGULAR', 'EXPEDITED', 'EMERGENCY') DEFAULT 'REGULAR' AFTER `description`;

-- 5. Add missing roles
INSERT INTO `roles` (id, name, description) VALUES
(10, 'Director HRM&A', 'Director of Human Resource Management and Administration'),
(11, 'Director Procurement', 'Director of Procurement Operations'),
(12, 'Requestor', 'Employee submitting procurement requests');

-- 6. Clean up duplicate indexes
ALTER TABLE `branches` DROP INDEX `branch_name`;
ALTER TABLE `invoices` DROP INDEX `invoice_number`;
ALTER TABLE `payments` DROP INDEX `payment_reference`;
ALTER TABLE `procurement_requests` DROP INDEX `request_number_4`, DROP INDEX `request_number_5`;
ALTER TABLE `users` DROP INDEX `uq_user_email`;
```

---

## File-by-File Issues Summary

### Critical Files With Issues:
- **`dashboard/compliance.php`** - Uses non-existent table and wrong column names
- **`config/workflow.php`** - References non-existent `system_config` table
- **`procurement/add.php`** - References non-existent `system_config` table
- **`dashboard/requestor.php`** - References non-existent `request_type` column
- **`dashboard/director_hrma.php`** - References non-existent `request_type` column
- **`dashboard/director_procurement.php`** - References non-existent `request_type` column
- **`commitments/upload.php`** - References non-existent `request_type` column

---

## Conclusion

The database schema has been recently restructured (as evidenced by migrations 001-007), but the PHP codebase has not been fully updated to match. The most critical items requiring immediate attention are:

1. **Database connectivity** - Verify database name
2. **Missing tables** - Create `compliance_approvals` and `system_config`
3. **Missing columns** - Add `request_type` to `procurement_requests`
4. **Missing roles** - Add Director roles to support application features
5. **Query fixes** - Update column references in `compliance.php`

Once these critical issues are resolved, the application should function correctly with the current database schema.
