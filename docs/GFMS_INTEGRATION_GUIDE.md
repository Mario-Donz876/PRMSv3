# GFMS Integration Guide - Commitment # and PO # Support

## Overview

This document describes how to use the GFMS integration feature in PRMS to track Procurement Request Management System commitments and purchase orders against Government Financial Management System (GFMS) records.

## New Features

### 1. GFMS Commitment Number Support
Users can now optionally enter a unique commitment number from the GFMS system when creating or uploading commitments.

**Features:**
- Optional field (backward compatible)
- Unique constraint - each GFMS commitment number can only be used once
- Format validation - alphanumeric with hyphens, slashes, and periods allowed
- Maximum 50 characters
- Searchable and trackable

**Where to Use:**
- `commitments/add.php` - When initially creating a commitment
- `commitments/upload.php` - When Finance Officer uploads the commitment document

### 2. GFMS PO Number Support
Users can now optionally enter a unique PO number from the GFMS system when creating or uploading purchase orders.

**Features:**
- Optional field (backward compatible)
- Unique constraint - each GFMS PO number can only be used once
- Format validation - alphanumeric with hyphens, slashes, and periods allowed
- Maximum 50 characters
- Searchable and trackable

**Where to Use:**
- `po/add.php` - When creating a purchase order
- `po/upload.php` - When uploading the PO document

## Workflow

### Commitment Workflow

1. **Procurement Officer** creates initial commitment in `commitments/add.php`
   - Optionally enters GFMS Commitment Number
   - System auto-generates internal commitment number (CM001, etc.)
   - Creates approval chain: HOD → Finance Officer

2. **Finance Officer** uploads commitment document in `commitments/upload.php`
   - Can add/modify GFMS Commitment Number at this point
   - Uploads signed document
   - Creates next approval chain: HOD → Director HRM&A

3. System automatically validates:
   - GFMS number uniqueness (if provided)
   - Format compliance
   - No duplicate commitments per request

### Purchase Order Workflow

1. **Procurement Officer** creates PO in `po/add.php`
   - Requires prior commitment creation and Finance Officer approval
   - Optionally enters GFMS PO Number
   - System auto-generates internal PO number (PO-YYYY-####)
   - Creates approval chain: HOD → Finance Officer

2. **Finance Officer** uploads PO document in `po/upload.php`
   - Can add/modify GFMS PO Number at this point
   - Uploads signed document
   - Creates next approval chain: HOD → Director HRM&A

3. System automatically:
   - Validates GFMS number uniqueness (if provided)
   - Checks format compliance
   - Prevents duplicate POs per commitment

## Database Schema Changes

### Commitments Table
```sql
ALTER TABLE `commitments` 
ADD COLUMN `gfms_commitment_number` VARCHAR(50) NULL UNIQUE 
COMMENT 'Unique commitment number from GFMS system';

CREATE INDEX `idx_gfms_commitment_number` ON `commitments` (`gfms_commitment_number`);
```

### Purchase Orders Table
```sql
ALTER TABLE `purchase_orders` 
ADD COLUMN `gfms_po_number` VARCHAR(50) NULL UNIQUE 
COMMENT 'Unique PO number from GFMS system';

CREATE INDEX `idx_gfms_po_number` ON `purchase_orders` (`gfms_po_number`);
```

## Installation

### Step 1: Apply Database Migrations

Run the migration scripts in order:

```bash
mysql -u[username] -p[password] [database] < migrations/003_add_gfms_commitment_number.sql
mysql -u[username] -p[password] [database] < migrations/004_add_gfms_po_number.sql
```

Or execute manually in phpMyAdmin:

1. Navigate to SQL tab
2. Copy and paste migration 003 SQL
3. Execute
4. Copy and paste migration 004 SQL
5. Execute

### Step 2: Verify File Updates

The following files have been automatically updated:

- ✅ `commitments/add.php` - Added GFMS commitment number input
- ✅ `commitments/upload.php` - Added GFMS commitment number input and validation
- ✅ `po/add.php` - Added GFMS PO number input and validation
- ✅ `po/upload.php` - Added GFMS PO number input and validation

### Step 3: Test the Implementation

1. **Test Commitment Creation:**
   - Go to Procurement → Create Request → Reach "Add Commitment" stage
   - Click "Add Commitment"
   - Fill in commitment details
   - Enter a GFMS Commitment Number (e.g., "GC/2026/00001")
   - Submit and verify it's saved

2. **Test Commitment Upload:**
   - Go to Finance → Find approved commitment
   - Click "Upload Commitment"
   - Try entering a GFMS number that was already used (should fail)
   - Enter new GFMS number and upload

3. **Test PO Creation:**
   - Go to Procurement → After commitment approved
   - Click "Create PO"
   - Enter GFMS PO Number (e.g., "GC/2026/PO/00001")
   - Submit and verify

4. **Test PO Upload:**
   - Go to Finance → Find created PO
   - Click "Upload PO"
   - Try entering a GFMS number that was already used (should fail)
   - Enter new GFMS number and upload

5. **Test Uniqueness Validation:**
   - Try entering the same GFMS number in another commitment/PO
   - Should see error: "GFMS [Commitment/PO] Number 'XXX' already exists in the system."

6. **Test Format Validation:**
   - Try entering invalid characters: `!@#$%` (should fail)
   - Allowed: letters, numbers, hyphens, slashes, periods
   - Try entering more than 50 characters (should fail)

## Example GFMS Numbers

The system accepts GFMS numbers in various formats:

### Commitment Numbers
- `GC/2026/00001`
- `GFMS-CM-123456`
- `DGC/2026/Commit/001`
- `CM-2026-001`

### PO Numbers
- `GC/2026/PO/00001`
- `GFMS-PO-123456`
- `DGC/2026/POrder/001`
- `PO-2026-001`

## Permissions

The GFMS number fields can be entered by users with these permissions:

### Commitment Numbers
- `create_commitment` - Can add GFMS # when creating commitment
- `upload_commitment` - Can add/modify GFMS # when uploading (Finance Officers)

### PO Numbers
- `create_purchase_order` - Can add GFMS # when creating PO
- `upload_purchase_order` - Can add/modify GFMS # when uploading (Finance Officers)

## Reporting and Tracking

After implementation, you can query GFMS numbers:

```sql
-- Find commitment by GFMS number
SELECT * FROM commitments WHERE gfms_commitment_number = 'GC/2026/00001';

-- Find PO by GFMS number
SELECT * FROM purchase_orders WHERE gfms_po_number = 'GC/2026/PO/00001';

-- Show all commitments with GFMS numbers
SELECT commitment_id, commitment_number, gfms_commitment_number, commitment_date
FROM commitments 
WHERE gfms_commitment_number IS NOT NULL
ORDER BY created_at DESC;

-- Show all POs with GFMS numbers
SELECT po_id, po_number, gfms_po_number, po_date
FROM purchase_orders 
WHERE gfms_po_number IS NOT NULL
ORDER BY created_at DESC;
```

## Backward Compatibility

The GFMS fields are completely optional. No existing functionality is affected:

- ✅ Creating commitments WITHOUT GFMS numbers still works
- ✅ Creating POs WITHOUT GFMS numbers still works
- ✅ All existing workflows remain unchanged
- ✅ No required changes to existing processes

## Error Handling

### Common Errors

1. **"GFMS Commitment Number 'XXX' already exists in the system."**
   - Solution: Use a different GFMS number, or leave blank to skip

2. **"GFMS Commitment Number can only contain letters, numbers, hyphens, slashes, and periods."**
   - Problem: Invalid character entered
   - Solution: Remove special characters (!@#$%, etc.)

3. **"GFMS Commitment Number cannot exceed 50 characters."**
   - Problem: Number too long
   - Solution: Use shorter reference or abbreviate

## Support

For issues or questions about GFMS integration:

1. Check this documentation first
2. Review error messages carefully
3. Contact IT Support with:
   - Screenshots of the error
   - GFMS number being used
   - Which form was being filled (add/upload)
   - User role and permissions

## Migration Rollback (if needed)

If you need to remove the GFMS fields:

```sql
-- Remove GFMS commitment number
DROP INDEX `idx_gfms_commitment_number` ON `commitments`;
ALTER TABLE `commitments` DROP COLUMN `gfms_commitment_number`;

-- Remove GFMS PO number
DROP INDEX `idx_gfms_po_number` ON `purchase_orders`;
ALTER TABLE `purchase_orders` DROP COLUMN `gfms_po_number`;
```

## Future Enhancements

Potential improvements for future versions:

1. **Integration with GFMS API** - Automatic validation against live GFMS system
2. **GFMS Sync Reports** - Reconciliation reports between PRMS and GFMS
3. **Search by GFMS Number** - Quick lookup in dashboards
4. **GFMS Validation Rules** - Custom format rules per organization
5. **Two-way Sync** - Automatic updates when GFMS records change

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-18  
**Status:** Active
