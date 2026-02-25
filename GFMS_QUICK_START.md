# GFMS Integration - Quick Start Deployment Guide

## What Was Done?

PRMS now supports optional GFMS (Government Financial Management System) commitment # and PO # tracking. Users can enter unique identifiers from GFMS when creating or uploading commitments and purchase orders.

## Files Changed

### New Migration Scripts
- ✅ `migrations/003_add_gfms_commitment_number.sql` - Database schema update
- ✅ `migrations/004_add_gfms_po_number.sql` - Database schema update

### Updated PHP Files
- ✅ `commitments/add.php` - Added GFMS # field
- ✅ `commitments/upload.php` - Added GFMS # field + validation
- ✅ `po/add.php` - Added GFMS # field + validation
- ✅ `po/upload.php` - Added GFMS # field + validation

### New Documentation
- ✅ `docs/GFMS_INTEGRATION_GUIDE.md` - Complete user guide
- ✅ `GFMS_IMPLEMENTATION_SUMMARY.md` - Implementation details
- ✅ `apply_gfms_migration.sh` - Migration helper script
- ✅ `GFMS_QUICK_START.md` - This file!

## How to Deploy

### Option 1: Direct SQL Execution (Recommended)

```bash
# 1. SSH into server
ssh user@server

# 2. Navigate to project directory
cd /path/to/prms

# 3. Backup database (ALWAYS DO THIS FIRST!)
mysqldump -u[username] -p[password] [database_name] > backup_$(date +%Y%m%d).sql

# 4. Run migration 003
mysql -u[username] -p[password] [database_name] < migrations/003_add_gfms_commitment_number.sql

# 5. Run migration 004
mysql -u[username] -p[password] [database_name] < migrations/004_add_gfms_po_number.sql

# 6. Verify success
mysql -u[username] -p[password] [database_name] -e "DESC commitments LIKE '%gfms%';"
mysql -u[username] -p[password] [database_name] -e "DESC purchase_orders LIKE '%gfms%';"
```

### Option 2: Using phpMyAdmin

1. Log in to phpMyAdmin
2. Select your database
3. Go to "SQL" tab
4. Copy and paste content of `migrations/003_add_gfms_commitment_number.sql`
5. Click Execute
6. Repeat with `migrations/004_add_gfms_po_number.sql`

### Option 3: Using MySQL Workbench

1. Connect to database
2. Open each migration file
3. Execute each script separately
4. Verify success

## Verification Steps

### 1. Check Database Changes

```sql
-- Verify commitments table
DESCRIBE commitments;
-- Look for: gfms_commitment_number column

-- Verify purchase_orders table
DESCRIBE purchase_orders;
-- Look for: gfms_po_number column

-- Check indexes
SHOW INDEX FROM commitments WHERE Key_name = 'idx_gfms_commitment_number';
SHOW INDEX FROM purchase_orders WHERE Key_name = 'idx_gfms_po_number';
```

### 2. Verify PHP Files Were Updated

```bash
# Check for GFMS field in commitments/add.php
grep -n "gfms_commitment_number" commitments/add.php

# Check for GFMS field in commitments/upload.php
grep -n "gfms_commitment_number" commitments/upload.php

# Check for GFMS field in po/add.php
grep -n "gfms_po_number" po/add.php

# Check for GFMS field in po/upload.php
grep -n "gfms_po_number" po/upload.php
```

### 3. Login and Test

1. Log in to PRMS as Procurement Officer
2. Create a new commitment
3. Verify the GFMS Commitment Number field appears
4. Enter a test GFMS number (e.g., "TEST-001")
5. Submit and verify it saves
6. Go to Finance Officer and upload the commitment
7. Try entering a duplicate GFMS number (should show error)
8. Enter a new GFMS number and submit
9. Repeat for POs

## Rollback (Emergency Only)

If something goes wrong:

```bash
# Restore from backup
mysql -u[username] -p[password] [database_name] < backup_20260218.sql
```

Or manually:

```sql
-- Remove GFMS columns
ALTER TABLE commitments DROP COLUMN gfms_commitment_number;
ALTER TABLE purchase_orders DROP COLUMN gfms_po_number;

-- Remove indexes
DROP INDEX idx_gfms_commitment_number ON commitments;
DROP INDEX idx_gfms_po_number ON purchase_orders;
```

## Key Features

✅ **Optional** - Not required, doesn't break existing workflows  
✅ **Unique** - Prevents duplicate GFMS numbers  
✅ **Validated** - Checks format (alphanumeric, hyphens, slashes, periods)  
✅ **Tracked** - All GFMS numbers are indexed for fast queries  
✅ **Role-Based** - Finance and Procurement can both enter/modify  

## Example GFMS Numbers

**Commitments:**
- GC/2026/00001
- GFMS-CM-123456
- DGC/FY2026/Commitment/001

**Purchase Orders:**
- GC/2026/PO/00001
- GFMS-PO-654321
- DGC/FY2026/POrder/001

## User Guide

For end-users, see: [docs/GFMS_INTEGRATION_GUIDE.md](docs/GFMS_INTEGRATION_GUIDE.md)

## FAQ

**Q: Do I have to use GFMS numbers?**  
A: No, completely optional. Existing workflows work as before.

**Q: What if I make a mistake with the GFMS number?**  
A: Contact IT to reset. Cannot be edited directly through UI.

**Q: How do I search by GFMS number?**  
A: Use these queries:
```sql
SELECT * FROM commitments WHERE gfms_commitment_number = 'YOUR-NUMBER';
SELECT * FROM purchase_orders WHERE gfms_po_number = 'YOUR-NUMBER';
```

**Q: Will this slow down the system?**  
A: No, indexes are created for fast lookups.

**Q: Can I import GFMS numbers in bulk?**  
A: Not yet, but this can be added in future versions.

## Support

**Issue:** Getting error about duplicate GFMS number  
**Solution:** Use a different GFMS number or leave blank

**Issue:** "GFMS number rejected for bad format"  
**Solution:** Only use: letters, numbers, hyphens (-), slashes (/), periods (.)

**Issue:** System says GFMS # already exists  
**Solution:** Check if already used in another commitment/PO, use unique number

## Success Indicators

✅ Users can see GFMS # fields in commitment/PO forms  
✅ Can submit with optional GFMS #  
✅ Duplicate numbers are rejected with clear error  
✅ Invalid formats are rejected  
✅ Valid GFMS # numbers are saved to database  
✅ Existing workflows work without GFMS #  
✅ No performance issues  

## What's Next?

After successful testing:
1. ✅ Push changes to version control
2. ✅ Add to release notes
3. ✅ Update user documentation
4. ✅ Train support team
5. ✅ Monitor for issues

## Support Contact

For questions or issues:
1. Check this guide and user guide
2. Review SQL migration files
3. Check PHP files for implementation details
4. Contact IT Support with specific error messages

---

**Version:** 1.0  
**Date:** 2026-02-18  
**Status:** Ready for Deployment

**How to use this guide:**
1. Read "How to Deploy" section
2. Choose deployment option
3. Follow verification steps
4. Test with users
5. Update issue tracking with status
