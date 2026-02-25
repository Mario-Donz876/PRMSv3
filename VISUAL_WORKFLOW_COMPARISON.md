# Visual Workflow Comparison
**Under-Threshold vs Over-Threshold RFQ Process**

---

## Request Submission & Approval

```
┌─ BOTH THRESHOLDS (Identical) ─────────────────────────────────────────────┐
│                                                                             │
│   ┌──────────┐      ┌─────────────┐      ┌──────────────────────┐        │
│   │  DRAFT   │  →   │  SUBMITTED  │  →   │  INITIAL APPROVAL   │        │
│   │(Requestor)│     │(Requestor) │     │(Branch Head Approval)│        │
│   └──────────┘      └─────────────┘      └──────────────────────┘        │
│          ▼                    ▼                        ▼                   │
│      Create            Check amount         HOD/Director/Deputy GC        │
│      request             ≤500K?             approves based on branch     │
│                              │                                            │
│                         YES │ NO                                          │
│                              ▼                                            │
└──────────────────────────────┼──────────────────────────────────────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
           ≤ 500K   │                     │   > 500K
              UNDER-THRESHOLD      OVER-THRESHOLD
              (Simplified)         (Formal)
```

---

## Path Divergence: RFQ Stage

```
═══════════════════════════════════════════════════════════════════════════

UNDER-THRESHOLD PATH (≤500K)              OVER-THRESHOLD PATH (>500K)
"Streamlined Procurement"                 "Formal Procurement"

═══════════════════════════════════════════════════════════════════════════

         ┌─────────────────────────┐
         │ HOD/DIRECTOR/DEPUTY GC  │
         │      APPROVED           │
         └────────────┬────────────┘
                      │
                      ▼
         ┌─────────────────────────┐      ┌─────────────────────────┐
         │  RFQ_LETTER_AVAILABLE   │      │ PROCUREMENT_STAGE       │
         │                         │      │ (Committee Setup Phase) │
         │ • No Committee          │      │                         │
         │ • Generate Formal RFQ   │      │ • Assign committee      │
         │   Letter (same format)  │      │ • Minimum 3 members     │
         │ • Add 3+ vendors        │      │ • Review procurement    │
         │ • Collect vendor quotes │      │   approach              │
         └────────────┬────────────┘      └────────────┬────────────┘
                      │                                 │
                      │                                 ▼
                      │                    ┌─────────────────────────┐
                      │                    │ EVALUATION_STAGE        │
                      │                    │ (Committee Review)      │
                      │                    │                         │
                      │                    │ • Formal bid opening    │
                      │                    │ • Committee analysis    │
                      │                    │ • Detailed evaluation   │
                      │                    │ • Technical compliance  │
                      │                    │   check                 │
                      │                    └────────────┬────────────┘
                      │                                 │
                      │                                 ▼
                      │                    ┌─────────────────────────┐
                      │                    │COMMITTEE_RECOMMENDED    │
                      │                    │ (Committee Decision)    │
                      │                    │                         │
                      │                    │ • Submit evaluation rep │
                      │                    │ • Committee votes       │
                      │                    │ • Recommendation issued │
                      │                    └────────────┬────────────┘
                      │                                 │
                      └──────────────────┬──────────────┘
                                         │
                                         ▼
         ┌─────────────────────────────────────────────────┐
         │     QUOTE_REVIEW_PENDING (Both Thresholds)     │
         │                                                 │
         │ • Requestor & HOD review vendor quotes         │
         │ • Both can propose alternatives                │
         │ • Evaluate against criteria:                    │
         │   - Vendor reliability                          │
         │   - Price competitiveness                       │
         │   - Delivery timeline                           │
         │   - Quote completeness                          │
         │   - [Over-T only]: Committee input              │
         │ • Mutual agreement on selection                 │
         └────────────┬────────────────────────────────────┘
                      │
                      ▼ (Selected quote finalized)
         ┌─────────────────────────┐
         │   QUOTE_APPROVED        │
         │                         │
         │ • Quote meets criteria  │
         │ • Ready for Finance     │
         │   commitment            │
         └────────────┬────────────┘
```

---

## Commitment & Finalization (Both Thresholds)

```
         ┌─────────────────────────────────────────────────┐
         │     COMMITMENT_APPROVED (Both Thresholds)       │
         │                                                 │
         │ Finance Officer Review:                         │
         │ • Verify funds available                        │
         │ • Review quote details                          │
         │ • [APPROVE] Upload GFMS commitment document    │
         │ • [DECLINE] Provide detailed reason             │
         └────┬─────────────────────────────────┬──────────┘
              │                                 │
          APPROVE                         DECLINE
              │                                 │
              ▼                                 ▼
    ┌──────────────────────┐     ┌──────────────────────┐
    │COMMITMENT_APPROVED   │     │COMMITMENT_DECLINED   │
    │                      │     │                      │
    │ Now proceed to:      │     │ Returned to          │
    │ • PO_PENDING         │     │ QUOTE_REVIEW_PENDING │
    │ • PO_APPROVED        │     │ for revision         │
    │ • INVOICE_RECEIVED   │     │                      │
    │ • COMPLETED          │     │ Can:                 │
    │                      │     │ • Review alt quotes  │
    │                      │     │ • Request from vendor│
    │                      │     │ • Return to RFQ      │
    └─────────────────────┘     └──────────────────────┘
              │                         ▲
              │                         │
              │                    [Resubmit]
              │                         │
              ▼                         │
    ┌──────────────────────┐           │
    │   PO_PENDING         │           │
    │ (Purchasing creates  │           │
    │  from commitment)    │           │
    └─────────┬────────────┘           │
              │                        │
              ▼                        │
    ┌──────────────────────┐           │
    │   PO_APPROVED        │           │
    │ (Confirmation of     │           │
    │  purchase authority) │           │
    └─────────┬────────────┘           │
              │                        │
              ▼                        │
    ┌──────────────────────┐           │
    │  INVOICE_RECEIVED    │           │
    │ (Vendor invoice      │           │
    │  uploaded & linked)  │           │
    └─────────┬────────────┘           │
              │                        │
              ▼                        │
    ┌──────────────────────┐           │
    │   COMPLETED ✓        │           │
    │ (Procurement cycle   │           │
    │  finalized)          │           │
    └──────────────────────┘           │
                                       │
                                   [Loop back
                                    if needed]
```

---

## Timeline Comparison

```
UNDER-THRESHOLD (≤500K)                 OVER-THRESHOLD (>500K)
─────────────────────────────────────────────────────────────────

Day 1-2:  Request Submission       Day 1-2:  Request Submission
          Branch Head Approval              Branch Head Approval

Day 3:    RFQ Creation              Day 3-5:  PROCUREMENT_STAGE
          Vendor Outreach                   Committee Assignment
          RFQ Letters Generated             Bid Opening Prep

Day 4-8:  Quote Submission          Day 6-8:  EVALUATION_STAGE
          Quote Review (Requestor/HOD)      Committee Analysis
          Selection Decision                Detailed Evaluation
                                            Reports Submitted

Day 9:    Finance Approval          Day 9:    COMMITTEE_RECOMMENDED
          Commitment Created                 Recommendation Issued

Day 10:   PO Created                Day 10:   Quote Review (with
          Vendor Authorization              Committee Input)
                                            Selection Decision

Day 11:   Invoice Processing        Day 11:   Finance Approval
                                            Commitment Created

Day 12:   Payment Ready             Day 12:   PO Created
                                            Vendor Authorization

                                    Day 13:   Invoice Processing

                                    Day 14:   Payment Ready

──────────────────────────────────────────────────────────────────

AVERAGE:  8-12 days                 AVERAGE:  9-14 days
DIFFERENCE: 1-3 days faster for under-threshold (committee skipped)
```

---

## Key Decision Points

```
┌─────────────────┐
│ REQUEST AMOUNT  │
└────────┬────────┘
         │
         ├─ ≤500K ──────┐
         │              │
         └─ >500K ──┐   │
                    │   │
                    │   └──→ RFQ_LETTER_AVAILABLE
                    │        (Skip committee)
                    │        No Committee Required
                    │        Faster timeline
                    │
                    └──→ PROCUREMENT_STAGE
                         (Requires committee)
                         Min 3 Committee Members
                         Formal evaluation report
                         Longer timeline
```

---

## Approval Chain Routing

```
REQUEST APPROVAL CHAIN
(Same for both thresholds)

UNDER-THRESHOLD (≤500K):
┌─────────────────────────────────────┐
│ BRANCH 6 (Analytical & Advisory)    │
│ → Deputy Government Chemist (ONLY)  │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ BRANCH 5 (HRM&A)                    │
│ → Director HRM&A (ONLY)             │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ ALL OTHER BRANCHES                  │
│ → HOD (ONLY)                        │
│   (No fallback to higher authority) │
└─────────────────────────────────────┘

OVER-THRESHOLD (>500K):
┌─────────────────────────────────────┐
│ ALL BRANCHES                        │
│ → HOD (ONLY)                        │
│   (Single point of control)         │
└─────────────────────────────────────┘

KEY: No escalation chain, single dedicated approver per request
```

---

## Quote Selection Authority

```
BOTH THRESHOLDS USE SAME SELECTION MODEL

Who can propose quotes?        Who can select winning quote?
┌───────────────────────────┐  ┌────────────────────────────┐
│ • Requestor              │  │ • Either Requestor or      │
│ • HOD/Branch Head        │  │   HOD can make selection   │
│                          │  │                            │
│ Both roles have equal    │  │ If disagreement:           │
│ access to quote review   │  │ • Mutual agreement needed  │
│                          │  │ • Next approver decides    │
│                          │  │   (Finance Officer)        │
└───────────────────────────┘  └────────────────────────────┘

                    Finance Officer
                    ↓
        Approves commitment if funds available
        OR
        Declines with detailed reason
```

---

## Status Badge Colors

```
PIPELINE STAGES - COLOR CODING

Approval Stages:
  HOD_APPROVED          [INFO] Blue circle
  DIRECTOR_APPROVED     [INFO] Blue briefcase
  GC_APPROVED          [SUCCESS] Green building
  FUNDS_VERIFIED       [PRIMARY] Primary blue coin

Under-Threshold Only:
  RFQ_LETTER_AVAILABLE [INFO] Blue envelope
  
Over-Threshold Only:
  PROCUREMENT_STAGE    [INFO] Blue clipboard
  EVALUATION_STAGE     [WARNING] Yellow bar chart
  COMMITTEE_RECMD      [INFO] Blue people

Both Continue With:
  QUOTE_REVIEW_PENDING [WARNING] Gold chat
  QUOTE_APPROVED       [INFO] Blue check
  COMMITMENT_APPROVED  [SUCCESS] Green coin
  PO_PENDING          [WARNING] Gold document
  PO_APPROVED         [SUCCESS] Green check
  INVOICE_RECEIVED    [INFO] Blue receipt
  AWARDED             [SUCCESS] Green trophy
  COMPLETED           [SUCCESS] Green checkmark
  
Negative:
  COMMITMENT_DECLINED  [DANGER] Red X
  DECLINED            [DANGER] Red X
```

---

## This Document Is Reference Material

- Use for training staff on new workflow
- Reference when troubleshooting workflow issues  
- Share with users to explain what happens at each step
- Training: Print the timeline comparison for quick reference

**Created:** February 19, 2026  
**Last Updated:** February 19, 2026  
**Version:** 1.0
