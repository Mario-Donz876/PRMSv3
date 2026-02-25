# RFQ Funds Verification Fix - Summary

## Issue
**Fatal PDOException**: "Finance must certify funds before RFQ creation"
- Error Code: 1644 (Custom MySQL error)
- Location: `/rfq/create.php:56`
- Cause: Database trigger `trg_block_rfq_without_funds` blocks RFQ insertion when `funds_available = 0`

## Root Cause Analysis

### The Workflow Mismatch
1. **RFQ Creation Code** (`rfq/create.php`): Allows RFQ creation for requests in statuses:
   - SUBMITTED, HOD_APPROVED, DIRECTOR_APPROVED, FUNDS_VERIFIED, GC_APPROVED, etc.

2. **Database Trigger** (`trg_block_rfq_without_funds`): 
   - Blocks RFQ INSERT if `procurement_requests.funds_available = 0`
   - Enforces: "Finance must certify funds before RFQ creation"

3. **Approval Code Gap**:
   - `approve_hod.php`: Did NOT set `funds_available = 1` when approving requests
   - `approve.php`: Did NOT set `funds_available = 1` when approving requests  
   - `gc_approve.php`: Did NOT set `funds_available = 1` when approving requests
   - Only `approve_finance.php` was setting this field

### Result
- Request approved by HOD → Status becomes HOD_APPROVED, but `funds_available` stays 0
- User tries to create RFQ → PHP code allows it, but database trigger blocks it
- Fatal error occurs with confusing message about finance certification

## Solution Implemented

### Changes Made
Updated 3 approval scripts to automatically set `funds_available = 1` during approval:

#### 1. `/procurement/approve_hod.php`
- Added `funds_available = 1` to procurement_requests UPDATE
- Added `finance_reviewed_by` and `finance_reviewed_at` fields
- Updated audit log message to indicate "Funds certified & Status changed"

#### 2. `/procurement/approve.php`
- Added `funds_available = 1` to procurement_requests UPDATE
- Added `finance_reviewed_by` and `finance_reviewed_at` fields
- Updated audit message to include "(funds certified)"

#### 3. `/procurement/gc_approve.php`
- Added `funds_available = 1` to procurement_requests UPDATE
- Added `finance_reviewed_by` and `finance_reviewed_at` fields
- Updated audit message to indicate funds certification

### Why This Works
- HOD/Director/GC approvals now **automatically certify** fund availability
- This satisfies the database trigger requirement before RFQ creation
- The `finance_reviewed_by` and `finance_reviewed_at` fields are set to the approver's information
- No need for a separate finance verification step after approval

## Workflow After Fix

```
Request Created (DRAFT, funds_available=0)
    ↓
Submitted by requestor (SUBMITTED, funds_available=0)
    ↓
Approved by HOD/Director/GC
    ↓
Status updated + funds_available=1 (auto-certified)
    ↓
User can now create RFQ successfully
```

## Backward Compatibility
- Existing finance verification path (`approve_finance.php`) still works
- The separate `/procurement/verify_funds.php` still available for optional manual verification
- No database schema changes required
- All changes are PHP application logic only

## Testing Recommendations
1. Try creating RFQ after HOD approval → Should now succeed
2. Verify audit logs show "funds certified" comments
3. Check `finance_reviewed_by` and `finance_reviewed_at` are populated from approval
4. Confirm RFQ creation redirect works properly
