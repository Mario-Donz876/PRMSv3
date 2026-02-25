# 💰 Finance Officer Commitment Control - Complete Workflow

**Date Implemented:** February 19, 2026  
**Change Type:** Workflow enhancement - Finance becomes gatekeeper for commitments

---

## Executive Summary

Finance Officers now **control commitment creation** by reviewing selected quotes and deciding whether to:
- ✅ **APPROVE** - Upload commitment document confirming funds available
- ❌ **DECLINE** - Return request with detailed reason for fund constraints

This adds critical financial control to prevent over-commitment of resources.

---

## Updated Workflow Flow

```
REQUEST SUBMITTED
    ↓ (approvals)
QUOTE APPROVED (by Requestor/HOD)
    ↓
COMMITMENT REVIEW BY FINANCE ← Finance Officer reviews quote/amount
    ↓
    ├─→ ✅ COMMITMENT_APPROVED (Finance uploads commitment doc, verifies funds)
    │      ↓
    │      PO_PENDING (Procurement creates PO)
    │      ↓
    │      COMPLETED (after invoice & payment)
    │
    └─→ ❌ COMMITMENT_DECLINED (Finance explains why funds unavailable)
           ↓
           Request returns to Requestor for revision
```

---

## Role Responsibilities

### Requestor / Branch Head / HOD
**Current duties:**
1. Create procurement request
2. Request goes through approval chain
3. Review and select vendor quote from RFQ
4. For now→ **WAIT** for Finance commitment review

### Finance Officer 🔐 [NEW GATEKEEPER ROLE]
**When Quote is APPROVED:**
1. **Navigate** to "Commitment Review & Approval" page
2. **Review** the selected quote:
   - Vendor name
   - Quote amount
   - Delivery/payment terms
   - Request amount
3. **Verify** funds in appropriate cost center
4. **DECIDE:**
   - ✅ **Approve** → Enter commitment details + upload GFMS document
   - ❌ **Decline** → Provide detailed reason why funds unavailable
5. **Upload** commitment document from GFMS (required for approval)
6. Request status changes automatically

### Procurement Officer
**Duties from COMMITMENT_APPROVED:**
1. Create Purchase Order from approved commitment
2. Generate PO in GFMS with commitment reference
3. Upload PO document to system

### Accounts Officer
**Duties from PO_APPROVED:**
1. Receive vendor invoice
2. Verify invoice matches PO
3. Upload invoice document
4. Process for payment

---

## New Workflow Statuses

| Status | Owner | Meaning | Action | Next Step |
|--------|-------|---------|--------|-----------|
| QUOTE_APPROVED | Requestor/HOD | Quote selected | — | Finance reviews |
| COMMITMENT_APPROVED | Finance Officer | Approved (funds verified) | Upload doc | Create PO |
| COMMITMENT_DECLINED | Finance Officer | Declined (insufficient funds) | Return to requestor | Revise quote/budget |

---

## Finance Officer Interface

### APPROVE TAB
**When Finance decides to APPROVE:**

1. **Commitment Date** (required)
   - Date funds are confirmed available
   - Cannot be in the past

2. **Commitment Amount** (required)
   - Pre-filled with quote amount
   - Must be > 0
   - Typically matches quote amount

3. **GFMS Commitment Number** (optional)
   - Reference number from GFMS system
   - Used for tracking against GFMS records
   - Must be unique

4. **Commitment Document** (REQUIRED)
   - Upload PDF/DOCX/XLSX document from GFMS
   - This is the **approval proof**
   - Without this, commitment cannot be created
   - Max 10 MB
   - File is stored at: `/uploads/commitments/COMMITMENT_[timestamp]_[id].pdf`

**Action:** Click "✅ Approve & Create Commitment"
- Commitment record created in system
- Request status → COMMITMENT_APPROVED
- Procurement can now create PO
- Audit trail: Documents approval + file upload timestamp

### DECLINE TAB
**When Finance decides to DECLINE:**

1. **Decline Reason** (required)
   - Minimum 10 characters
   - Maximum 1000 characters
   - Be specific for requestor guidance
   - Examples:
     - "Insufficient budget allocation for Marketing category - only $50,000 left for fiscal year"
     - "Quote exceeds departmental spending limit - please obtain quotes from alternative vendors"
     - "Missing required supporting financial documentation from cost center"

**Action:** Click "❌ Decline & Return Request"
- Request status → COMMITMENT_DECLINED
- Requestor receives notification with decline reason
- Requestor can:
  - Revise budget allocation
  - Request different quote
  - Submit new procurement request
  - Escalate for budget exception

---

## Database Schema Changes

### New Column: `procurement_requests.commitment_declined_reason`
**Purpose:** Store Finance officer's decline reason  
**Type:** TEXT  
**When used:** Set when status changes to COMMITMENT_DECLINED

### Status Values in `allowedTransitions()`
**Updated:**
```php
'QUOTE_APPROVED' => ['COMMITMENT_APPROVED', 'COMMITMENT_DECLINED', 'COMMITMENTS_PENDING', 'PROCUREMENT_STAGE'],
'COMMITMENT_DECLINED' => ['QUOTE_REVIEW_PENDING', 'PROCUREMENT_STAGE'],
```

### Workflow Configuration (`config/workflow.php`)
**Stage Ownership:**
```
'QUOTE_APPROVED' => ['Finance Officer'] 
'COMMITMENT_APPROVED' => ['Finance Officer']
'COMMITMENT_DECLINED' => ['Finance Officer']
```

---

## File Changes

### 1. `/workspaces/PRMS/commitments/add.php`
**Complete rewrite** - Now Finance Officer approval/decline form

**Key Features:**
- ✅ Restricted to Finance Officers only
- ✅ Displays selected quote details for review
- ✅ Two tabs: APPROVE vs DECLINE
- ✅ Approve requires document upload
- ✅ Decline requires detailed reason
- ✅ Automatic status updates
- ✅ Audit logging for both actions

**Entry Point:** `/commitments/add.php?request_id=[id]`

### 2. `/workspaces/PRMS/config/workflow.php`
**Updates:**
- ✅ Added COMMITMENT_DECLINED to transitions
- ✅ Updated stageOwner with Finance assignment
- ✅ Added getStatusLabel() function with COMMITMENT_DECLINED label
- ✅ Color-coded status: Red for decline (danger)

### 3. `/workspaces/PRMS/po/add.php`
**No changes needed** - Already checks for COMMITMENT_APPROVED status

---

## Approval Workflow Diagram

```
                         QUOTE_APPROVED
                              ↓
          ╔════════════════════════════════════════════╗
          ║   FINANCE OFFICER REVIEWS QUOTE            ║
          ║                                            ║
          ║   • Verify funds available                 ║
          ║   • Check budget allocation                ║
          ║   • Review quote terms                     ║
          ║   • Validate amount                        ║
          ╚════════════════════════════════════════════╝
            ↓                              ↓
      [APPROVE]                      [DECLINE]
         ↓                               ↓
    Upload Doc              Explain Why Funds Unavailable
         ↓                               ↓
  COMMITMENT_APPROVED          COMMITMENT_DECLINED
    (✅ Success)                  (❌ Returned)
         ↓                               ↓
    PO_PENDING                  QUOTE_REVIEW_PENDING
         ↓                               ↓
    PO_APPROVED                     Requestor Revises
         ↓                               ↓
   INVOICE_RECEIVED          Resubmit/New Quote
         ↓
    COMPLETED
```

---

## User Permissions

**Required Permissions:**
- Finance Officer users need: `verify_funds` permission
- Access to `/commitments/add.php` restricted to Finance Officers

**Role Check:**
```php
if (($_SESSION['role'] ?? '') !== 'Finance Officer') {
    pop("Only Finance Officers can review commitments.", ...);
    exit;
}
```

---

## Audit & Compliance

Every decision is tracked:

**✅ COMMITMENT_APPROVED:**
- Logged: "Approved by Finance Officer - Funds verified and commitment uploaded from GFMS"
- Document: PDF/DOCX uploaded and stored
- Timeline: Timestamp of approval recorded
- Who: Finance Officer name recorded
- When: Exact approval date/time

**❌ COMMITMENT_DECLINED:**
- Logged: "Finance declined - Reason: [summary]"
- Reason: Full decline reasoning stored
- Timeline: Timestamp of decline recorded
- Who: Finance Officer name recorded
- Notification: Requestor gets detailed decline message

---

## Testing Checklist

- [ ] Finance Officer can access commitment approval form
- [ ] Non-Finance users cannot access commitment approval form
- [ ] Approve form shows correct quote details
- [ ] Document upload is required for approval
- [ ] Document file size limits enforced (10 MB)
- [ ] File type validation working (PDF, DOC, XLSX only)
- [ ] Approve creates commitment record
- [ ] Status changes to COMMITMENT_APPROVED after approval
- [ ] Decline updates status to COMMITMENT_DECLINED
- [ ] Decline reason is required (min 10 chars)
- [ ] Audit log captures both approve and decline
- [ ] Request can move back to quote review after decline
- [ ] PO creation available only after COMMITMENT_APPROVED
- [ ] Requestor receives notification of decline

---

## FAQ

**Q: What if Finance declines?**  
A: Request status changes to COMMITMENT_DECLINED. Requestor can revise the quote, request different vendor, or request budget exception.

**Q: Can Finance upload the commitment document later?**  
A: No, document upload is part of the approval action. It must be uploaded when committing funds.

**Q: What if the commitment amount differs from quote amount?**  
A: Finance Officer can enter different amount if budget policy requires. It's editable in the form.

**Q: Can the decline be appealed?**  
A: Requestor can escalate to HOD/Director for budget exception. Decline reason provides context.

**Q: What if document upload fails?**  
A: User sees error message and can retry. Transaction rolls back if upload fails.

**Q: Is the document visible to Requestor?**  
A: Yes, can be viewed in commitment details page. Provides transparency on what Finance approved.

---

## Migration Notes

**For Deployment:**
1. Ensure Finance Officer users have `verify_funds` permission
2. Create upload directories if not exist: `/uploads/commitments/`
3. Test with staging database first
4. Brief Finance Officers on new workflow
5. Update any RPA/automation that creates commitments (must now go through approval)

**Backward Compatibility:**
- Direct procurement (SINGLE_SOURCE, no RFQ) still works
- Legacy PROCUREMENTS_PENDING status still supported
- Existing approved commitments unaffected

---

## Summary

✅ Finance Officer now has critical control point  
✅ Commitments cannot be created without funds verification  
✅ Complete audit trail of approvals/declines  
✅ Clear communication about constraints  
✅ Prevents budget overruns  
✅ Compliance with financial governance  

**Status: READY FOR DEPLOYMENT**
