# 📤 Document Upload Feature Added

**Date:** February 19, 2026  
**Feature:** File upload capability for commitments and purchase orders

---

## What's New

Users can now upload commitment and PO documents directly from the system when:
- **Creating a Commitment** - Upload GFMS commitment document
- **Creating a Purchase Order** - Upload GFMS PO document

---

## File Changes

### 1. Commitments Form (`commitments/add.php`)

**Added:**
- ✅ File upload field for commitment documents
- ✅ Multi-part form encoding (enctype="multipart/form-data")
- ✅ File validation on server-side
- ✅ Automatic file storage in `/uploads/commitments/`
- ✅ Database integration - stores document path

**Supported File Types:**
- PDF (.pdf)
- Word Documents (.doc, .docx)
- Excel Spreadsheets (.xls, .xlsx)

**File Size Limit:** 10 MB

**Usage:**
1. Navigate to "Create Commitment"
2. Fill out commitment details (date, amount, GFMS number)
3. Optional: Choose a commitment document file
4. Click "Save Commitment"

---

### 2. Purchase Order Form (`po/add.php`)

**Added:**
- ✅ File upload field for PO documents
- ✅ Multi-part form encoding (enctype="multipart/form-data")
- ✅ File validation on server-side
- ✅ Automatic file storage in `/uploads/po/`
- ✅ Database integration - stores document path

**Supported File Types:**
- PDF (.pdf)
- Word Documents (.doc, .docx)
- Excel Spreadsheets (.xls, .xlsx)

**File Size Limit:** 10 MB

**Usage:**
1. Navigate to "Create Purchase Order"
2. Fill out PO details (date, amount, GFMS number)
3. Optional: Choose a PO document file
4. Click "Save Purchase Order"

---

## Database Changes

### Migration File: `migrations/011_add_document_upload_fields.sql`

**New Columns:**

1. **commitments.document_path** (VARCHAR(255))
   - Stores path to uploaded commitment document
   - Nullable (upload is optional)
   - Example: `/uploads/commitments/COMMITMENT_1708345200_xxx.pdf`

2. **purchase_orders.document_path** (VARCHAR(255))
   - Stores path to uploaded PO document
   - Nullable (upload is optional)
   - Example: `/uploads/po/PO_1708345200_xxx.pdf`

**New Indexes:**
- `idx_commitments_document_path` - For document retrieval
- `idx_po_document_path` - For document retrieval

**Deployment:**
```bash
mysql -u user -p database_name < migrations/011_add_document_upload_fields.sql
```

---

## Technical Implementation

### File Upload Validation

**Server-side checks:**
1. ✅ File type validation using MIME type detection (finfo)
2. ✅ File size validation (max 10 MB)
3. ✅ Safe filename generation using timestamp + uniqid
4. ✅ Directory creation with proper permissions (755)
5. ✅ Transaction rollback on upload failure

**Error Handling:**
- Users receive clear error messages for:
  - Invalid file types
  - Oversized files
  - Upload failures
- Errors are handled gracefully without data loss

### File Storage

**Directory Structure:**
```
/uploads/
  ├── commitments/
  │   └── COMMITMENT_1708345200_xxxxx.pdf
  └── po/
      └── PO_1708345200_xxxxx.pdf
```

**Filename Format:** `[ENTITY]_[TIMESTAMP]_[UNIQUE_ID].[EXT]`
- Prevents filename collisions
- Makes files easily traceable
- Separates documents by type

### Database Integration

Files are stored with the entity record:
```sql
-- Commitment with document
SELECT commitment_id, commitment_number, document_path 
FROM commitments 
WHERE commitment_id = 123;

-- Purchase Order with document
SELECT po_id, po_number, document_path 
FROM purchase_orders 
WHERE po_id = 456;
```

---

## Security Features

✅ **MIME Type Validation** - Verifies actual file type, not just extension  
✅ **Size Limits** - Prevents large file uploads  
✅ **Safe Filenames** - No user input in filenames  
✅ **Transaction Safety** - Files only saved if database insert succeeds  
✅ **Directory Separation** - Different directories for commitments vs POs  

---

## User Guide

### Uploading a Commitment Document

1. Go to Procurement Request details
2. Click "Add Commitment"
3. Fill in:
   - Commitment Date
   - Commitment Amount (JMD)
   - GFMS Commitment Number (optional)
   - **Commitment Document (optional)** ← NEW
4. Click "Save Commitment"
5. Document is automatically saved and linked

### Uploading a Purchase Order Document

1. Go to Commitment details
2. Click "Create Purchase Order"
3. Fill in:
   - PO Date
   - PO Total (JMD)
   - GFMS PO Number (optional)
   - **Purchase Order Document (optional)** ← NEW
4. Click "Save Purchase Order"
5. Document is automatically saved and linked

---

## Troubleshooting

### "File upload failed"
- Check file is under 10 MB
- Check file format is PDF, DOC, DOCX, XLS, or XLSX
- Try uploading again

### "Invalid file type"
- Only PDF, Word, and Excel files are supported
- Check file extension matches content

### "Failed to save document"
- Ensure `/uploads/commitments/` and `/uploads/po/` directories are writable
- Check disk space available
- Try again or contact administrator

---

## View Uploaded Documents

### In Commitment View
When viewing a commitment, document link appears:
```
Document: [View PDF] [Download]
```

### In PO View
When viewing a PO, document link appears:
```
Document: [View PDF] [Download]
```

---

## Compliance & Audit Trail

- ✅ All uploads logged in `audit_log` table
- ✅ Document paths stored with entity for traceability
- ✅ File timestamps included in filename
- ✅ Original filenames preserved (optional)
- ✅ All uploads linked to user and timestamp

---

## Migration Rollback

If needed, remove document columns:
```sql
ALTER TABLE commitments DROP COLUMN document_path;
ALTER TABLE purchase_orders DROP COLUMN document_path;
DROP INDEX idx_commitments_document_path ON commitments;
DROP INDEX idx_po_document_path ON purchase_orders;
```

---

## Summary

✅ Users can now upload commitment documents when creating commitments  
✅ Users can now upload PO documents when creating purchase orders  
✅ Documents are stored securely with automatic MIME validation  
✅ File paths stored in database for retrieval  
✅ Complete audit trail maintained  
✅ Easy rollback if needed  

**Status: READY FOR DEPLOYMENT**
