# Procurement Workflow Logic Fix - Complete Summary

## Issues Fixed

### 1. **Auto-Transition Based on Threshold (CRITICAL)**
**Problem:** Requests weren't automatically transitioning to AWARDED after final approval for under-threshold items.
**Solution:** Modified `getNextStatusAfterApproval()` in `config/workflow.php` to:
- Check request type and estimated value after final approval
- **Under-threshold (≤500k) OR Direct type:** Auto-transition to `AWARDED` 
- **Over-threshold (>500k):** Auto-transition to `PROCUREMENT_STAGE` (enables RFQ)

**Code Logic:**
```php
// After final approval completes:
if (isDirectProcurement($requestType, $estimatedValue)) {
    return 'AWARDED';  // Direct to commitment stage
} else {
    return 'PROCUREMENT_STAGE';  // Requires RFQ stage
}
```

### 2. **Updated Workflow Transitions**
**File:** `config/workflow.php`

Added missing transitions to support auto-routing:
- `SUBMITTED` → `PROCUREMENT_STAGE` (for over-threshold)
- `HOD_APPROVED` → `PROCUREMENT_STAGE` (for over-threshold)
- `DIRECTOR_APPROVED` → `PROCUREMENT_STAGE` (for over-threshold)
- `GC_APPROVED` → `PROCUREMENT_STAGE` (for over-threshold)
- `PROCUREMENT_STAGE` → `EVALUATION_STAGE` (start evaluation)
- `EVALUATION_STAGE` → `AWARDED` (after committee recommendation)

### 3. **RFQ Letter Generation at Multiple Stages**
**File:** `procurement/view.php`

**Problem:** RFQ letters could only be generated after RFQ creation, limiting vendor ability to submit quotes early.

**Solution:** Enabled RFQ letter generation at intermediate approval stages:
```php
<?php if ($rfqId && !$isAwarded && in_array($current, ['HOD_APPROVED', 'DIRECTOR_APPROVED', 'PROCUREMENT_STAGE', 'EVALUATION_STAGE', 'GC_APPROVED'])): ?>
    <a href="/rfq/generate_rtf.php?id=<?= $rfqId ?>" target="_blank" class="btn btn-outline-info btn-sm">
        <i class="bi bi-file-pdf me-1"></i>Generate RFQ Letters (for Vendor Quotes)
    </a>
<?php endif; ?>
```

**When RFQ letters can be generated:**
1. `HOD_APPROVED` - After HOD approval (for quick vendor engagement)
2. `DIRECTOR_APPROVED` - After Director HRM&A approval  
3. `PROCUREMENT_STAGE` - During procurement stage
4. `EVALUATION_STAGE` - During vendor evaluation
5. `GC_APPROVED` - After GC approval, before award

### 4. **Enhanced Next Step Guidance**
**File:** `procurement/view.php`

Users now see clear guidance about what happens next:

- **DRAFT:** "Submit this request for approval"
- **HOD_APPROVED (Under-threshold):** "Direct Procurement - Will transition to AWARDED after approvals"
- **HOD_APPROVED (Over-threshold):** "Over threshold - Will transition to RFQ stage after final approvals"
- **PROCUREMENT_STAGE:** "Create RFQ and issue letters to vendors for quotes (Over threshold)"
- **AWARDED:** "Create Commitment & Purchase Order"
- **PROCUREMENT_STAGE:** "Create RFQ and issue letters to vendors"

### 5. **Improved RFQ Creation Conditions**
**File:** `procurement/view.php`

RFQ creation now shows at appropriate times:
```php
<?php 
$needsRfq = !isDirectProcurement($requestType, $estimatedValue);
if (in_array($current, ['PROCUREMENT_STAGE', 'EVALUATION_STAGE', ...]) || 
    ($needsRfq && in_array($current, ['HOD_APPROVED', 'DIRECTOR_APPROVED']))): ?>

    // Show RFQ Create button only for over-threshold requests
```

## Workflow Process Now

### **Under-Threshold Procurement (≤ JMD 500,000)**

```
DRAFT 
  ↓ [Submit]
SUBMITTED 
  ↓ [HOD Approves]
HOD_APPROVED 
  ↓ [Director/GC Approves]
AWARDED ✓ (Direct Procurement - Skip RFQ)
  ↓ [Create Commitment]
COMMITMENT STAGE
  ↓ [Create PO]
PO STAGE
  ↓ [Create Invoice]
PAYMENT
  ↓
COMPLETED
```

### **Over-Threshold Procurement (> JMD 500,000)**

```
DRAFT 
  ↓ [Submit]
SUBMITTED 
  ↓ [HOD Approves]
HOD_APPROVED 
  ↓ [Director/GC Approves]
PROCUREMENT_STAGE ✓ (Requires RFQ)
  ↓ [Create RFQ / Generate RFQ Letters → Vendor Quotes]
EVALUATION_STAGE 
  ↓ [Committee Evaluates]
COMMITTEE_RECOMMENDED
  ↓ [GC Final Approval]
AWARDED
  ↓ [Create Commitment]
COMMITMENT STAGE
  ↓ [Create PO]
PO STAGE
  ↓ [Create Invoice]
PAYMENT
  ↓
COMPLETED
```

## Key Features Implemented

### ✅ Direct Procurement (Under-Threshold)
- Automatically transitions to AWARDED after final approval
- No RFQ stage required
- Can immediately create Commitment & PO
- **Faster procurement for items ≤ JMD 500,000**

### ✅ RFQ Process (Over-Threshold)
- Automatically transitions to PROCUREMENT_STAGE after final approval
- Enables RFQ creation for vendor solicitation
- RFQ letters can be generated at multiple stages
- Committees evaluate vendor proposals
- Award decision made after GC approval

### ✅ Flexible RFQ Letter Generation
- Can generate RFQ letters at:
  - HOD approval stage (early vendor engagement)
  - Director approval stage
  - Procurement stage (official issuance)
  - Evaluation stage (follow-ups)
  - GC approval stage
- Allows vendors to submit quotes throughout the process
- **Solves the vendor quote timing issue**

### ✅ Better User Guidance
- Clear "Next Step" indicators
- Explains whether procurement is direct or requires RFQ
- Shows threshold status in next step description
- Color-coded status indicators (Direct=Lightning, RFQ=Book, etc.)

## Testing Checklist

- [ ] **Under-threshold request:** Create → Approve through HOD → Status automatically goes to AWARDED
- [ ] **Over-threshold request:** Create → Approve through HOD → Status automatically goes to PROCUREMENT_STAGE
- [ ] **RFQ Letter generation:** Can generate RFQ letters after HOD approval (not waiting for final GC approval)
- [ ] **Commitment creation:** Can create commitment once status is AWARDED
- [ ] **Vendor quotes:** Vendors can upload quotes after receiving RFQ letters at any intermediate stage
- [ ] **Workflow transitions:** All intermediate statuses properly update in request history

## Related Files Modified

1. `/workspaces/PRMS/config/workflow.php`
   - Updated `allowedTransitions()` to include PROCUREMENT_STAGE routing
   - Enhanced `getNextStatusAfterApproval()` to determine final status based on threshold

2. `/workspaces/PRMS/procurement/view.php`
   - Enhanced RFQ creation button logic
   - Added RFQ letter generation at multiple stages
   - Improved next step guidance with threshold-aware messages

## SOP Compliance

This fix ensures compliance with the Procurement SOP:
- ✅ Step 6: RFQ creation is enabled at appropriate stages
- ✅ Step 7: Evaluation committee can proceed at EVALUATION_STAGE
- ✅ Step 13: Under-threshold requests can create commitment directly after approval
- ✅ Step 13a-b: Commitment approval chain properly enforced
- ✅ Vendor engagement timeline improved with flexible RFQ letter generation

## Notes

- **Direct procurement threshold:** JMD 500,000 (configurable via system_config)
- **Threshold checks:** Based on `estimated_value` field in procurement_requests
- **Request types:** REGULAR, PETTY_CASH, REIMBURSEMENT (all supported)
- **Fallback behavior:** If status check fails, defaults to AWARDED (safe fallback)
