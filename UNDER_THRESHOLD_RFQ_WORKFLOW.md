# Under-Threshold RFQ Workflow
**Effective: February 19, 2026**

## Overview

As of February 2026, **ALL REGULAR PROCUREMENT REQUESTS now require RFQ**, regardless of threshold. This includes both under-threshold (≤500K) and over-threshold (>500K) requests.

The key difference:
- **Under-threshold RFQ**: Simplified process, skips committee evaluation step
- **Over-threshold RFQ**: Full process with committee evaluation

## Workflow Comparison

### PREVIOUS (Direct Procurement for Under-Threshold)
```
Under-Threshold (≤500K):
  SUBMITTED → HOD_APPROVED → FUNDS_VERIFIED → AWARDED → COMMITMENT → PO → INVOICE → COMPLETED
  (No vendor quotes, no committee)

Over-Threshold (>500K):
  SUBMITTED → HOD_APPROVED → PROCUREMENT_STAGE → EVALUATION_STAGE → COMMITTEE_RECOMMENDED
  → QUOTE_REVIEW_PENDING → QUOTE_APPROVED → COMMITMENT_APPROVED → PO → INVOICE → COMPLETED
```

### NEW (RFQ for All Procurement)
```
Under-Threshold (≤500K):
  SUBMITTED → HOD_APPROVED → RFQ_LETTER_AVAILABLE → QUOTE_REVIEW_PENDING → QUOTE_APPROVED
  → COMMITMENT_APPROVED → PO_PENDING → PO_APPROVED → INVOICE_RECEIVED → COMPLETED
  ✓ No committee evaluation
  ✓ Faster quote turnaround
  ✓ Streamlined vendor selection

Over-Threshold (>500K):
  SUBMITTED → HOD_APPROVED → PROCUREMENT_STAGE → EVALUATION_STAGE → COMMITTEE_RECOMMENDED
  → QUOTE_REVIEW_PENDING → QUOTE_APPROVED → COMMITMENT_APPROVED → PO_PENDING → PO_APPROVED
  → INVOICE_RECEIVED → COMPLETED
  ✓ Includes committee evaluation
  ✓ Formal bid opening and analysis
  ✓ Full transparency and compliance
```

## Stage-by-Stage Explanation

### Stage 1: Initial Approval (Same for Both)
| Element | Details |
|---------|---------|
| Status | SUBMITTED → HOD_APPROVED |
| Actor | Branch Head (HOD or designated approver) |
| Action | Reviews request completeness and budget |
| Result | Moves to RFQ stage |

### Stage 2: RFQ Letter & Vendor Outreach (DIFFERENT)

**Under-Threshold:**
- Status: `RFQ_LETTER_AVAILABLE`
- Procurement Officer can immediately generate formal RFQ letters
- Vendors contacted directly without committee involvement
- Faster turnaround expected

**Over-Threshold:**
- Status: `PROCUREMENT_STAGE` → `EVALUATION_STAGE`
- Must go through committee evaluation first
- Committee reviews procurement method before vendor outreach
- More formal evaluation period

### Stage 3: Quote Review & Selection (Same Concept, Different Actor Weight)

**Under-Threshold:**
- Status: `QUOTE_REVIEW_PENDING` → `QUOTE_APPROVED`
- Both **Requestor** and **HOD** can propose quotes
- Selection criteria focused on:
  - Vendor reliability
  - Quote completeness
  - Delivery timeline
  - Price competitiveness
- Simpler evaluation (no committee scoring)

**Over-Threshold:**
- Status: `QUOTE_REVIEW_PENDING` → `QUOTE_APPROVED`
- Both **Requestor** and **HOD** can propose quotes
- Selection criteria includes:
  - Committee recommendations
  - Detailed scoring
  - Technical compliance
  - Price analysis

### Stage 4: Finance Approval & Commitment (Same for Both)
| Element | Details |
|---------|---------|
| Status | QUOTE_APPROVED → COMMITMENT_APPROVED |
| Actor | Finance Officer |
| Action | Reviews selected quote, verifies funds available |
| Document | Uploads GFMS commitment document |
| Result | Authorizes procurement to continue |

### Stage 5-6: PO & Invoice (Same for Both)
- Purchase Order created and approved
- Vendor invoice uploaded
- Request marked COMPLETED

## Workflow Diagram

```
Under-Threshold (≤500K) RFQ Flow:
┌─────────────────────────────────────────────────────┐
│ DRAFT                                               │
│ (Requestor creates request)                         │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ SUBMITTED                                           │
│ (Requestor submits for approval)                    │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ HOD_APPROVED                                        │
│ (Branch Head reviews and approves)                  │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ RFQ_LETTER_AVAILABLE ⭐ KEY DIFFERENCE             │
│ (Skips committee, goes straight to RFQ letters)     │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ QUOTE_REVIEW_PENDING                                │
│ (Requestor/HOD reviews vendor quotes)               │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ QUOTE_APPROVED                                      │
│ (Selected quote finalized)                          │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ COMMITMENT_APPROVED                                 │
│ (Finance uploads GFMS commitment doc)               │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ PO_PENDING → PO_APPROVED                            │
│ (Purchase Order created and approved)               │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ INVOICE_RECEIVED                                    │
│ (Vendor invoice uploaded)                           │
└───────────┬─────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────┐
│ COMPLETED ✓                                         │
│ (Full procurement cycle complete)                   │
└─────────────────────────────────────────────────────┘
```

## Key Roles & Permissions

### Under-Threshold RFQ Process

| Role | Stage | Actions |
|------|-------|---------|
| **Requestor** | DRAFT | Create request, add items, submit |
| **Requestor** | QUOTE_REVIEW_PENDING | Propose/select vendor quotes |
| **HOD/Branch Head** | HOD_APPROVED | Review and approve request |
| **HOD/Branch Head** | QUOTE_REVIEW_PENDING | Propose/select vendor quotes |
| **Procurement Officer** | RFQ_LETTER_AVAILABLE | Generate RFQ letters, manage vendor list |
| **Procurement Officer** | QUOTE_REVIEW_PENDING | Receive vendor quotes, upload to system |
| **Finance Officer** | QUOTE_APPROVED | Review selected quote, verify funds, upload GFMS commitment doc |
| **Accounts Officer** | PO_PENDING & INVOICE_RECEIVED | Create PO and process invoice |

### Quote Selection Rules

**For Under-Threshold:**
- ✅ Both Requestor AND HOD can propose quotes
- ✅ Either can make the final selection
- ✅ If disagreement, mutual agreement required (both approve the same quote)
- ✅ Simpler criteria (price, delivery, reliability)

**For Over-Threshold:**
- ✅ Both Requestor AND HOD can propose quotes
- ✅ Committee recommendation considered
- ✅ More detailed evaluation required
- ✅ Technical compliance must be verified

## System Configuration Changes

**workflow.php Changes:**
```php
// isDirectProcurement() function:
// - Petty Cash: Still direct (returns TRUE)
// - Reimbursement: Still direct (returns TRUE)
// - Regular Procurement: NOW REQUIRES RFQ (returns FALSE for ALL amounts)

// getNextStatusAfterApproval() function:
// - Under-threshold (≤500K): Routes to RFQ_LETTER_AVAILABLE
// - Over-threshold (>500K): Routes to PROCUREMENT_STAGE (includes committee)
```

**View.php Changes:**
- RFQ button now shows for under-threshold requests in RFQ_LETTER_AVAILABLE status
- Pipeline shows RFQ stages for all regular procurement
- Status labels updated to reflect new workflow

## Timeline Expectations

### Under-Threshold RFQ (Expected)
- Request submission to approval: 1-2 days
- RFQ letter generation: 1 day
- Vendor quote period: 3-5 days
- Quote review and selection: 1-2 days
- Finance approval: 1 day
- PO creation: 1 day
- **Total expected: 8-12 days**

### Over-Threshold RFQ (Expected)
- Request submission to approval: 1-2 days
- Committee evaluation: 2-3 days
- Bid opening and evaluation: 2-3 days
- Quote review and selection: 1-2 days
- Finance approval: 1 day
- PO creation: 1 day
- **Total expected: 9-14 days**

## Benefits of New Under-Threshold RFQ Model

1. **Vendor Transparency**: All under-threshold procurement now involves vendor quotes
2. **Best Value**: Competitive quotes ensure value for money even for smaller purchases
3. **Audit Trail**: All quote decisions documented and traceable
4. **Faster Processing**: Skipping committee evaluation speeds up under-threshold requests
5. **Consistency**: Same RFQ letter format builds vendor familiarity
6. **Flexibility**: Both Requestor and HOD can participate in selection

## Migration Notes

- Existing under-threshold requests in AWARDED status can proceed directly
- New under-threshold requests created after Feb 19, 2026 must use new RFQ workflow
- Legacy PROCUREMENT_STAGE and EVALUATION_STAGE statuses still supported for backward compatibility
- All new requests will use the improved workflow

## Support & Questions

For workflow clarification, contact:
- Procurement Officer: RFQ process and letter generation
- Finance Officer: Commitment approval and fund verification
- System Administrator: Workflow status updates and transitions

---
**Document Version:** 1.0  
**Effective Date:** February 19, 2026  
**Last Updated:** February 19, 2026
