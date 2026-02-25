# PHP Code Fixes Required

## Overview
The PHP code has several references to database columns and tables that don't exist or are incorrectly named. This document outlines all required code fixes.

---

## CRITICAL: Fix Dashboard/Compliance.php

**File:** `/workspaces/PRMS/dashboard/compliance.php`

### Issue 1: Column name reference error (lines 11-16)
**Current Code:**
```php
COUNT(DISTINCT pr.id) as total_requests,
...
FROM procurement_requests pr
LEFT JOIN compliance_approvals ca ON pr.id = ca.entity_id AND ca.entity_type = 'procurement_request'
```

**Problem:** 
- `pr.id` doesn't exist; should be `pr.request_id`
- `pr.title` doesn't exist; should be `pr.description`

**Fixed Code:**
```php
COUNT(DISTINCT pr.request_id) as total_requests,
...
FROM procurement_requests pr
LEFT JOIN compliance_approvals ca ON pr.request_id = ca.entity_id AND ca.entity_type = 'procurement_request'
```

### Issue 2: Column name from second query (line 53)
**Current Code:**
```php
pr.title,
```

**Fixed Code:**
```php
pr.description as title,  -- or rename the select alias
```

### Issue 3: JOIN in data query (lines 55-62)
**Current Code:**
```php
JOIN procurement_requests pr ON ca.entity_id = pr.id
```

**Fixed Code:**
```php
JOIN procurement_requests pr ON ca.entity_id = pr.request_id
```

---

## MEDIUM: Fix Config Files

### File: `/workspaces/PRMS/config/db.php`

**Current Code:**
```php
$dbname = "u153072617_dgc_procure_sy";
```

**Issue:** Database name doesn't match the SQL dump which uses `u153072617_prms`

**Options:**

**Option 1 - Update config to match SQL dump:**
```php
$dbname = "u153072617_prms";
```

**Option 2 - Create correct database name in config and rename database:**
First, rename the database to match config:
```sql
-- In MySQL:
CREATE DATABASE IF NOT EXISTS `u153072617_dgc_procure_sy`;
USE `u153072617_dgc_procure_sy`;
-- Then import the prmsv2.sql file into this database
```

**Recommendation:** Option 1 - use the database name from the SQL dump unless there's a specific requirement to use the other name.

---

## MEDIUM: Fix Dashboard Files - request_type References

These files reference a `request_type` column that now exists after database fix, but they may need adjustments based on actual usage.

### File: `/workspaces/PRMS/dashboard/requestor.php`
**Location:** Line 17-18

**Code:**
```php
$stmt = $pdo->prepare("SELECT request_id, request_number, request_type, estimated_value, status, request_date
```

**Status:** ✓ Will work after adding the `request_type` column to database

---

### File: `/workspaces/PRMS/dashboard/director_hrma.php`
**Location:** Line 24

**Code:**
```php
FROM ra.*, pr.request_number, pr.request_type, pr.estimated_value, pr.status
```

**Status:** ✓ Will work after database fix

---

### File: `/workspaces/PRMS/dashboard/director_procurement.php`
**Location:** Line 24

**Code:**
```php
SELECT request_id, request_number, request_type, estimated_value, status, request_date
```

**Status:** ✓ Will work after database fix

---

### File: `/workspaces/PRMS/commitments/upload.php`
**Location:** Line 214

**Code:**
```php
<?= match($request['request_type'] ?? 'REGULAR') {
```

**Status:** ✓ Will work after database fix. The default value 'REGULAR' is already set in the database.

---

## LOW: Config File - Role Definitions

These are already handled by database fixes (adding roles to database), but verify the constants are used correctly.

### File: `/workspaces/PRMS/config/app.php`
**Lines:** 16-27

**Current:**
```php
const ROLE_DIRECTOR_HRMA = 10;
const ROLE_DIRECTOR_PROCUREMENT = 11;
```

**Status:** ✓ After database fix (roles 10-11 will be added to database), these constants will work correctly.

---

## Implementation Checklist

### Phase 1: Database Fixes (First)
- [ ] Execute all SQL commands from `database_fixes.sql`
- [ ] Verify all tables created successfully
- [ ] Verify all columns added successfully
- [ ] Verify all roles inserted successfully

### Phase 2: Config Updates
- [ ] Update database name in `config/db.php` to `u153072617_prms`
- [ ] Test database connection

### Phase 3: Code Updates
- [ ] Update `/workspaces/PRMS/dashboard/compliance.php`:
  - [ ] Change all `pr.id` to `pr.request_id` (3 occurrences)
  - [ ] Change `pr.title` to `pr.description` (1 occurrence)
  - [ ] Change `ON pr.id = ca.entity_id` to `ON pr.request_id = ca.entity_id` (1 occurrence)
  - [ ] Change `pr.id` in JOIN to `pr.request_id` (1 occurrence:)

### Phase 4: Testing
- [ ] Log in and verify no database connection errors
- [ ] Test Compliance Dashboard page loads without errors
- [ ] Test Requestor dashboard shows requests with types
-test all role-based dashboards display correctly
- [ ] Check browser console for any JavaScript errors
- [ ] Test data operations (create, update, read)

---

## Before & After Comparison

### Example 1: compliance.php Join Fix

**Before:**
```php
LEFT JOIN compliance_approvals ca ON pr.id = ca.entity_id
--                                      ^^^^
--                                      ERROR: column doesn't exist
```

**After:**
```php
LEFT JOIN compliance_approvals ca ON pr.request_id = ca.entity_id
--                                      ^^^^^^^^^^
--                                      CORRECT: column exists
```

### Example 2: Column Selection Fix

**Before:**
```php
SELECT pr.title, pr.status
--          ^^^^^
--          ERROR: doesn't exist in schema
```

**After:**
```php
SELECT pr.description as title, pr.status
--          ^^^^^^^^^^^
--          CORRECT: uses existing column
```

### Example 3: Database Connection Fix

**Before:**
```php
$dbname = "u153072617_dgc_procure_sy";  // Database doesn't have the schema
$pdo = new PDO("mysql:host=$host;dbname=$dbname...");
// RESULT: Connection fails or connects to wrong database
```

**After:**
```php
$dbname = "u153072617_prms";  // Correct database with schema loaded
$pdo = new PDO("mysql:host=$host;dbname=$dbname...");
// RESULT: Successful connection to correct database
```

---

## Testing Queries After Fixes

```php
// Test 1: Verify compliance_approvals table works
$stmt = $pdo->prepare("SELECT COUNT(*) FROM compliance_approvals");
$stmt->execute();
echo "Compliance records: " . $stmt->fetchColumn();

// Test 2: Verify system_config table works
$stmt = $pdo->prepare("SELECT config_value FROM system_config WHERE config_key = 'petty_cash_limit'");
$stmt->execute();
echo "Petty cash limit: " . $stmt->fetchColumn();

// Test 3: Verify request_type column works
$stmt = $pdo->prepare("SELECT request_type FROM procurement_requests LIMIT 1");
$stmt->execute();
$result = $stmt->fetch();
echo "Request type: " . ($result['request_type'] ?? 'NULL/DEFAULT');

// Test 4: Verify roles exist
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE id IN (10, 11, 12)");
$stmt->execute();
echo "New roles count: " . $stmt->fetchColumn(); // Should return 3
```

---

## Summary of All Changes

| File | Change | Priority | Status |
|------|--------|----------|--------|
| `config/db.php` | Update database name to `u153072617_prms` | CRITICAL | Required |
| `dashboard/compliance.php` | Replace `pr.id` with `pr.request_id` | CRITICAL | Required |
| `dashboard/compliance.php` | Replace `pr.title` with `pr.description` | CRITICAL | Required |
| Database | Create `compliance_approvals` table | CRITICAL | SQL fix |
| Database | Create `system_config` table | CRITICAL | SQL fix |
| Database | Add `request_type` column | MEDIUM | SQL fix |
| Database | Add missing roles (10, 11, 12) | MEDIUM | SQL fix |
| Database | Remove duplicate indexes | MEDIUM | SQL fix |

---

## Notes
- All database changes are provided in `database_fixes.sql`
- All code changes should be tested in a development environment first
- After making changes, clear any application caches if applicable
- Run full application smoke tests after deployment
