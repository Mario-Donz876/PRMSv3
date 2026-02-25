# RFQ Workflow Enhancement - Implementation Summary
**Date:** February 19, 2026  
**Version:** 2.0  
**Status:** Implemented

## Overview
This document describes the updated RFQ workflow that follows the complete procurement process from approval through invoice receipt and payment.

---

## Workflow Process Flow

### Step 1: Request Approval (HOD/Director/GC)
- **Status:** `HOD_APPROVED`, `DIRECTOR_APPROVED`, `GC_APPROVED`
- **Who:** Relevant approver based on branch and amount
- **What:** Request is reviewed and approved
- **Next Step:** RFQ letter generation becomes available

### Step 2: RFQ Letter Generation & Distribution
- **Status:** `RFQ_LETTER_AVAILABLE`
- **Who:** Procurement Officer or Requestor
- **What:** Create RFQ and generate RFQ letters to send to potential vendors
- **How:** 
  - Create RFQ in system (available immediately after approval)
  - Generate PDF letters via `/rfq/generate_rtf.php`
  - Distribute to vendors with submission deadline
- **Next Step:** Vendors submit quotes

### Step 3: Vendor Quote Submission & Review
- **Status:** `QUOTE_REVIEW_PENDING`
- **Who:** Requestor, Branch Head, or HOD
- **What:** Review received vendor quotes to ensure they meet requirements
- **How:**
  - Vendors submit quotes with supporting documents
  - Requestor/Branch Head reviews each quote
  - Mark quote as "MEETS_REQUIREMENTS" or "DOES_NOT_MEET"
  - Add review comments/notes
- **System Tracking:** `rfq_quotes.review_status` and `review_comments`
- **Next Step:** Select the best quote

### Step 4: Quote Selection & Approval
- **Status:** `QUOTE_APPROVED`
- **Who:** Procurement Officer (with approval from Requestor/Branch Head)
- **What:** Select the quote that best meets requirements and price
- **How:**
  - Mark selected quote as `is_selected = 1`
  - Award the RFQ to selected vendor
  - Update `rfq.status = 'AWARDED'`
- **Next Step:** Create commitment from GFMS

### Step 5: Commitment Creation from GFMS
- **Status:** `COMMITMENTS_PENDING` → `COMMITMENT_APPROVED`
- **Who:** Accounts Officer (creates), Finance Officer (approves)
- **What:** Generate commitment in GFMS with selected quote amount
- **How:**
  - Navigate to selected request
  - Create commitment using GFMS number
  - Commitment must link to the selected RFQ quote
  - Finance Officer reviews and approves
- **System Tracking:** `commitments.gfms_generated` flag
- **Dependencies:** Only allowed after quote is selected (QUOTE_APPROVED status)
- **Next Step:** Generate PO from GFMS

### Step 6: Purchase Order Creation from GFMS
- **Status:** `PO_PENDING` → `PO_APPROVED`
- **Who:** Procurement Officer (creates), HOD/Finance (approves)
- **What:** Generate PO in GFMS based on commitment
- **How:**
  - Navigate to approved commitment
  - Create PO using GFMS number matching the commitment
  - HOD and Finance review and approve
- **System Tracking:** `purchase_orders.gfms_generated` flag
- **Dependencies:** Only allowed after commitment is approved
- **Next Step:** Receive and upload vendor invoice

### Step 7: Invoice Receipt & Upload
- **Status:** `INVOICE_RECEIVED`
- **Who:** Accounts Officer or Finance Officer
- **What:** Receive vendor invoice and upload to system
- **How:**
  - Upon receiving vendor invoice, verify against PO
  - Upload invoice with reference to PO
  - Link invoice to corresponding PO in system
- **System Tracking:** `invoices.invoice_source = 'VENDOR_UPLOADED'`
- **Dependencies:** Only allowed after PO is created and approved
- **Next Step:** Process payment

### Step 8: Payment Processing
- **Status:** `COMPLETED`
- **Who:** Finance Officer
- **What:** Process payment against invoice
- **How:**
  - Record payment reference and amount
  - Verify invoice amount matches PO
  - Update payment status
- **System Tracking:** `payments` table with invoice linkage
- **Final Status:** `COMPLETED`

---

## Database Schema Changes

### New Columns Added
Files affected: `/workspaces/PRMS/migrations/010_rfq_workflow_enhancement.sql`

#### `rfqs` table
- `quote_review_status`: ENUM('PENDING','IN_REVIEW','APPROVED') - tracks quote review stage
- `reviewed_by`: INT - user who reviewed quotes
- `reviewed_at`: DATETIME - when quotes were reviewed

#### `rfq_quotes` table
- `review_status`: ENUM('PENDING','MEETS_REQUIREMENTS','DOES_NOT_MEET') - individual quote review
- `review_comments`: TEXT - reviewer comments on quote

#### `procurement_requests` table
- `requires_rfq`: TINYINT(1) - auto-set based on request type/amount
- `rfq_letter_generated_at`: DATETIME - when RFQ letters were generated

#### `commitments` table
- `quote_approved_at`: DATETIME - when quote was approved
- `gfms_generated`: TINYINT(1) - flag for GFMS-generated commitments

#### `purchase_orders` table
- `commitment_approved_at`: DATETIME - when commitment was approved
- `gfms_generated`: TINYINT(1) - flag for GFMS-generated POs

#### `invoices` table
- `po_approved_at`: DATETIME - when PO was approved
- `gfms_generated`: TINYINT(1) - flag for GFMS-generated invoices
- `invoice_source`: ENUM('VENDOR_UPLOADED','SYSTEM_GENERATED','MANUAL') - invoice source tracking

### New Triggers Created
- `trg_auto_set_requires_rfq`: Auto-set RFQ requirement flag on insert
- `trg_auto_update_requires_rfq`: Auto-update RFQ requirement flag on update
- `trg_require_quote_review_for_commitment`: Prevent commitment creation before quote review
- `trg_require_committed_amount_for_po`: Ensure PO only created with valid commitment
- `trg_track_po_approval_date`: Track PO approval for invoice dependency

### New Indexes Created
```sql
CREATE INDEX idx_rfq_status ON rfqs(status);
CREATE INDEX idx_rfq_quote_review_status ON rfqs(quote_review_status);
CREATE INDEX idx_quote_selection ON rfq_quotes(is_selected);
CREATE INDEX idx_quote_review_status ON rfq_quotes(review_status);
CREATE INDEX idx_pr_requires_rfq ON procurement_requests(requires_rfq);
CREATE INDEX idx_commitment_gfms_generated ON commitments(gfms_generated);
CREATE INDEX idx_po_gfms_generated ON purchase_orders(gfms_generated);
CREATE INDEX idx_invoice_source ON invoices(invoice_source);
```

---

## Workflow Status Transitions

### New Status Values Added
- `RFQ_LETTER_AVAILABLE`: RFQ letter can be generated and sent to vendors
- `QUOTE_REVIEW_PENDING`: Waiting for requestor/branch head to review quotes
- `QUOTE_APPROVED`: Quote selected and approved, ready for commitment
- `COMMITMENTS_PENDING`: Creating commitment from GFMS
- `COMMITMENT_APPROVED`: Commitment approved by Finance, ready for PO
- `PO_PENDING`: Creating PO from GFMS
- `PO_APPROVED`: PO approved and ready, waiting for invoice
- `INVOICE_RECEIVED`: Invoice uploaded, ready for payment

### Allowed Transitions
**From Approval Stages:**
- `HOD_APPROVED` → `RFQ_LETTER_AVAILABLE`
- `DIRECTOR_APPROVED` → `RFQ_LETTER_AVAILABLE`
- `GC_APPROVED` → `RFQ_LETTER_AVAILABLE`
- `FUNDS_VERIFIED` → `RFQ_LETTER_AVAILABLE`

**Through RFQ Workflow:**
- `RFQ_LETTER_AVAILABLE` → `QUOTE_REVIEW_PENDING`
- `QUOTE_REVIEW_PENDING` → `QUOTE_APPROVED`
- `QUOTE_APPROVED` → `COMMITMENTS_PENDING`
- `COMMITMENTS_PENDING` → `COMMITMENT_APPROVED`
- `COMMITMENT_APPROVED` → `PO_PENDING`
- `PO_PENDING` → `PO_APPROVED`
- `PO_APPROVED` → `INVOICE_RECEIVED`
- `INVOICE_RECEIVED` → `COMPLETED`

---

## Code Changes Summary

### 1. `/workspaces/PRMS/config/workflow.php`
**Changes:**
- Updated `allowedTransitions()` to include new status values
- Updated `stageOwner()` to define roles for each new stage
- Added 8 new workflow helper functions:
  - `canGenerateRFQLetterAtStage()`
  - `getRFQWorkflowStep()`
  - `getNextRFQStep()`
  - `canProceedToQuoteReview()`
  - `getQuoteReviewComments()`
  - `updateQuoteReviewStatus()`

**References Updated:**
- All code checking workflow stages now includes new status values
- RFQ letter generation now available immediately after approval

### 2. `/workspaces/PRMS/rfq/create.php`
**Changes:**
- Allow RFQ creation immediately after approval (not just at PROCUREMENT_STAGE)
- Updated allowed statuses: Added `HOD_APPROVED`, `DIRECTOR_APPROVED`, `FUNDS_VERIFIED`, `GC_APPROVED`, `RFQ_LETTER_AVAILABLE`

**Impact:**
- Users can now create RFQ letters right after approval

### 3. `/workspaces/PRMS/commitments/add.php`
**Changes:**
- Updated allowed statuses for commitment creation to include `QUOTE_APPROVED`, `COMMITMENTS_PENDING`
- Added import of workflow.php functions
- Added validation to check if quote selection is complete before commitment
- Added check for `requires_rfq` flag on request

**Impact:**
- Commitment creation enforces quote selection requirement
- Better workflow step tracking

### 4. `/workspaces/PRMS/po/add.php`
**Changes:**
- Relaxed status requirements for PO creation
- Updated allowed request statuses
- Added backward compatibility check for approval records
- Added import of workflow.php functions

**Impact:**
- PO creation more flexible for different workflow paths

### 5. `/workspaces/PRMS/procurement/view.php`
**Changes:**
- Updated RFQ/PO workflow buttons to show options at correct workflow stages
- Added RFQ letter generation button right after approval
- Added support for new status values in button display logic

**Impact:**
- Users see appropriate action buttons at each workflow stage
- Better UX with clear next steps

---

## Role Responsibilities

### Requestor
- Create and submit procurement request
- Review vendor quotes (in QUOTE_REVIEW_PENDING stage)
- Approve selected quote

### Head of Branch / HOD
- Approve request
- Review vendor quotes
- Approve selected quote
- Approve commitment
- Approve PO

### Procurement Officer
- Create RFQ and generate letters
- Manage vendor submissions
- Manage RFQ evaluation
- Create PO from GFMS

### Accounts Officer / Finance Officer
- Certify funds availability
- Review and approve commitment from GFMS
- Receive and upload vendor invoices
- Process payments

### Director HRM&A / Deputy Government Chemist
- Approve requests (for specific branches/thresholds)
- May review quotes
- May approve PO

---

## Permission Mapping

### Existing Permissions Still Applied
- `view_requests`: View procurement requests and RFQs
- `edit_requests`: Edit requests
- `create_commitment`: Create commitments
- `approve_commitment`: Approve commitments
- `create_purchase_order`: Create POs
- `approve_po`: Approve POs
- `create_invoice`: Create/upload invoices
- `create_payment`: Record payments

### Usage Across Stages

| Stage | Permission | Actor |
|-------|-----------|-------|
| Request Approval | `approve_request` | HOD/Director/GC |
| RFQ Letter | `view_requests` + RFQ ownership | Procurement |
| Quote Review | `view_requests` | Requestor/HOD |
| Quote Approval | `view_requests` | Requestor/HOD |
| Commitment | `create_commitment` | Accounts |
| Commitment Approval | `approve_commitment` | Finance Officer |
| PO Creation | `create_purchase_order` | Procurement |
| PO Approval | `approve_po` | HOD/Finance |
| Invoice Upload | `create_invoice` | Accounts |
| Payment | `create_payment` | Finance |

---

## Migration File
**Location:** `/workspaces/PRMS/migrations/010_rfq_workflow_enhancement.sql`

**Contents:**
1. Adds new columns to 5 tables
2. Creates 5 database triggers for workflow enforcement
3. Creates 8 indexes for performance
4. Includes comprehensive comments

**To Apply:**
```bash
mysql -u user -p database_name < /workspaces/PRMS/migrations/010_rfq_workflow_enhancement.sql
```

---

## Testing Checklist

### RFQ Letter Generation
- [ ] Create request with amount > 500k
- [ ] Approve request to HOD_APPROVED status
- [ ] Verify "Create RFQ & Generate Letters" button appears
- [ ] Click to create RFQ
- [ ] Verify RFQ PDF can be generated

### Quote Review Process
- [ ] Add vendors to RFQ
- [ ] Vendors submit quotes
- [ ] Requestor navigates to quote review
- [ ] Mark quotes as meeting/not meeting requirements
- [ ] Add review comments

### Commitment Creation
- [ ] After quote selection, navigate to commitment
- [ ] Verify quote requirement is enforced
- [ ] Create commitment with GFMS number
- [ ] Verify commitment status updates

### PO Creation
- [ ] After commitment approval
- [ ] Create PO with GFMS number
- [ ] Verify PO is linked to commitment
- [ ] Test PO approval workflow

### Invoice Upload
- [ ] After PO approval
- [ ] Upload vendor invoice
- [ ] Verify invoice is linked correctly
- [ ] Record payment

---

## Backward Compatibility

### Legacy Requests
- Requests that skip RFQ (under threshold, PETTY_CASH, REIMBURSEMENT) continue to work
- Direct procurement path still available
- PROCUREMENT_STAGE workflow still supported

### Status Mapping
- Old transitions still work: `PROCUREMENT_STAGE` → `EVALUATION_STAGE` → `AWARDED`
- New transitions provide more granular control
- System auto-determines path based on request type/amount

---

## Audit Trail

All workflow transitions are logged in `audit_log` table:
- **Table:** `audit_log`
- **Fields:** `action`, `changed_by`, `change_date`, `notes`
- **New Actions:**
  - `QUOTE_REVIEW` - Quote review by requestor
  - `STATUS_CHANGE` - Status transition records

Approvals are tracked in `request_approvals` table:
- **Entity Types:** `PROCUREMENT_REQUEST`, `RFQ`, `COMMITMENT`, `PO`, `INVOICE`
- **Statuses:** `pending`, `approved`, `rejected`
- **Tracks:** Who approved, when, with comments

---

## Summary of Changes

### Files Modified: 5
1. `/workspaces/PRMS/config/workflow.php` - ✅ Updated
2. `/workspaces/PRMS/rfq/create.php` - ✅ Updated  
3. `/workspaces/PRMS/commitments/add.php` - ✅ Updated
4. `/workspaces/PRMS/po/add.php` - ✅ Updated
5. `/workspaces/PRMS/procurement/view.php` - ✅ Updated

### Files Created: 1
1. `/workspaces/PRMS/migrations/010_rfq_workflow_enhancement.sql` - ✅ Created

### Database Changes
- 5 tables modified with new columns
- 5 new triggers created
- 8 new indexes created

### Workflow Statuses: 8 new values added
- Fully backward compatible with existing statuses
- Allows granular control of RFQ workflow

---

## Next Steps

1. **Apply Migration:** Run the SQL migration file to update database schema
2. **Test Workflow:** Walk through complete RFQ process with test data
3. **User Training:** Train users on new statuses and buttons
4. **Monitor:** Check audit logs for any issues
5. **Optimize:** Monitor performance indexes

---

## References

- Original RFQ system: `/workspaces/PRMS/rfq/` module
- Workflow logic: `/workspaces/PRMS/config/workflow.php`
- Approval tracking: `/workspaces/PRMS/prmsv2.sql` - `request_approvals` table
- Audit trail: `/workspaces/PRMS/prmsv2.sql` - `audit_log` table
