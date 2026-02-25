# Procurement Workflow Diagrams - Visual Reference

## 📊 Reimbursement Request Workflow

### High-Level Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  REIMBURSEMENT REQUEST WORKFLOW                                 │
│  (For Staff Reimbursement of Personal Expenditures)             │
└─────────────────────────────────────────────────────────────────┘

   STAFF MEMBER                BRANCH HEAD                 PROCUREMENT (GC2)              FINANCE (GC10A)
        ║                          ║                              ║                             ║
        ║      1. Creates Request  ║                              ║                             ║
        ║  ─────────────────────>  ║                              ║                             ║
        ║                          ║                              ║                             ║
        ║  2. Prior Authorization  ║                              ║                             ║
        ║  <──────────────────────  ║                              ║                             ║
        ║                          ║                              ║                             ║
        ║  3. Purchases goods/services (with personal funds)      ║                             ║
        ║  ─────────────────────────────────────────────────>     ║                             ║
        ║                                                          ║                             ║
        ║  4. Submits copy of invoice ──────────────────────────> ║                             ║
        ║                                                          ║ Verifies Goods/Services   ║
        ║                                                          ║ ┌─────────────────────┐   ║
        ║                                                          ║ │ Satisfactory?       │   ║
        ║                                                          ║ │ -YES: Mark Verified │   ║
        ║                                                          ║ │ -NO: Reject         │   ║
        ║                                                          ║ └─────────────────────┘   ║
        ║                                                          ║                             ║
        ║  5. Submits original invoice (after verification) ─────────────────────────────────> ║
        ║                                                                                       ║
        ║                                                          Finance Reviews:            ║
        ║                                                          ┌─────────────────────┐    ║
        ║                                                          │ • Verify amount     │    ║
        ║                                                          │ • Check funds       │    ║
        ║                                                          │ • Cross-check auth  │    ║
        ║                                                          │ • Approve/Reject    │    ║
        ║                                                          └─────────────────────┘    ║
        ║                                                                                       ║
        ║  6. Reimbursement Issued <─────────────────────────────────────────────────────────  ║
        ║
        ▼

   REQUEST COMPLETED
   Funds returned to staff member
```

### Status Transitions

```
  DRAFT
    │
    ├─SUBMIT─────────> SUBMITTED
                         │
                         ├─APPROVE───> PRE_AUTHORIZED
                         │               │
                         │               ├─RECV INVOICE──> PENDING_PROCUREMENT_VERIFICATION
                         │               │                  │
                         │               │                  ├─VERIFY────> VERIFIED
                         │               │                  │               │
                         │               │                  │               ├─RECV ORIGINAL──> PENDING_ORIGINAL_INVOICE
                         │               │                  │               │                   │
                         │               │                  │               │                   ├─REVIEW──> PENDING_FINANCE_REVIEW
                         │               │                  │               │                   │             │
                         │               │                  │               │                   │             ├─APPROVE──> APPROVED
                         │               │                  │               │                   │             │               │
                         │               │                  │               │                   │             │               ├─REIMBURSE──> REIMBURSED
                         │               │                  │               │                   │             │               │                  │
                         │               │                  │               │                   │             │               │                  ├─COMPLETE──> COMPLETED
                         │               │                  │               │                   │             │               │
                         │               │                  │               │                   │             ├─REJECT───> DECLINED
                         │               │                  │               │                   │
                         │               │                  │               │                   ├─REJECT───> DECLINED
                         │               │                  │
                         │               │                  ├─REJECT───> DECLINED
                         │
                         ├─REJECT───> DECLINED
```

---

## 💰 Petty Cash Request Workflow

### High-Level Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  PETTY CASH PROCUREMENT WORKFLOW                                │
│  (For Small Direct Purchases ≤ JMD 5,000)                       │
└─────────────────────────────────────────────────────────────────┘

   STAFF          BRANCH HEAD        PROCUREMENT (GC2)      FINANCE (GC10A)         VERIFICATION
    ║                  ║                    ║                    ║                        ║
    ║  1. Creates      ║                    ║                    ║                        ║
    ║  Request         ║                    ║                    ║                        ║
    ║  ─────────────>  ║                    ║                    ║                        ║
    ║                  ║                    ║                    ║                        ║
    ║  2. Reviews &    ║                    ║                    ║                        ║
    ║  Authorizes      ║                    ║                    ║                        ║
    ║  <──────────────  ║                    ║                    ║                        ║
    ║                  ║                    ║                    ║                        ║
    ║  3. Submits to   ║                    ║                    ║                        ║
    ║  Procurement     ├───────────────────> ║                    ║                        ║
    ║                  ║                     ║                    ║                        ║
    ║                  ║  4. Endorses        ║                    ║                        ║
    ║                  ║  <────────────────── ║                    ║                        ║
    ║                  ║                     ├─────────────────>  ║                        ║
    ║                  ║                                           ║  5. Authorizes &      ║
    ║                  ║                                           ║  Disburses Cash       ║
    ║  6. Receives     ║                                           ║  ┌─────────────────┐  ║
    ║  Cash            ║  <────────────────────────────────────────  │ DEADLINE STARTS │  ║
    ║  <──────────────────────────────────────────────────────────────  T + 24 HOURS  │  ║
    ║                  ║                                           ║  └─────────────────┘  ║
    ║                  ║                                           ║                        ║
    ║  7. Makes        ║                                           ║                        ║
    ║  Purchase        ║                                           ║                        ║
    ║  (within 24h)    ║                                           ║                        ║
    ║  ─────────>      ║                                           ║                        ║
    ║                  ║                                           ║                        ║
    ║  8. Returns      ║                                           ║                        ║
    ║  Invoice         ║                                           ║                        ║
    ║  & Change        ║                                           ║  Reconciles           ║
    ║  (within 24h)    ├───────────────────────────────────────────> (within 24h)         ║
    ║                  ║                                           ║                        ║
    ║                  ║  9. Verifies        ║                        ║  Inspects      ║
    ║                  ║  Goods/Services     ║                        ║  Items         ║
    ║                  ║  (within 24h)       ║  <──────────────────────                  ║
    ║                  ║                     ║                        ║  ┌────────────┐ ║
    ║                  ║                     ║                        ║  │ Satisfactory│ ║
    ║                  ║                     ║                        ║  │ Defective  │ ║
    ║                  ║                     ║                        ║  │ Incomplete │ ║
    ║                  ║                     ║                        ║  └────────────┘ ║
    │                  │                     │                        │                  │
    ▼                  ▼                     ▼                        ▼                  ▼

    PETTY CASH REQUEST COMPLETED
    All Funds Accounted For
    Goods/Services Verified
    24-Hour Rule Satisfied
```

### Status Transitions with 24-Hour Accountability

```
  DRAFT
    │
    ├─SUBMIT─────────> SUBMITTED
                         │
                         ├─REVIEW────> HOD_REVIEWED
                         │               │
                         │               ├─ENDORSE──> PROCUREMENT_ENDORSED
                         │               │             │
                         │               │             ├─AUTHORIZE──> FINANCE_AUTHORIZED
                         │               │             │                │
                         │               │             │                ├─DISBURSE──> DISBURSED ⏱️
                         │               │             │                │            (24h DEADLINE ACTIVE)
                         │               │             │                │             │
                         │               │             │                │             ├─RECONCILE────> PENDING_RECONCILIATION
                         │               │             │                │             │                 │
                         │               │             │                │             │                 ├─VERIFY────> PROCUREMENT_VERIFIED
                         │               │             │                │             │                │              │
                         │               │             │                │             │                │              ├─COMPLETE──> COMPLETED
                         │               │             │                │             │                │              │
                         │               │             │                │             │                ├─DISCREPANCY──> RECONCILIATION_DISCREPANCY
                         │               │             │                │             │                               │
                         │               │             │                │             │                               ├─REVIEW──> REVIEWED
                         │               │             │                │             │                               │              │
                         │               │             │                │             │                               │              ├─COMPLETE──> COMPLETED
                         │               │             │                │             │                               │
                         │               │             │                ├─REJECT───> DECLINED
                         │               │             │
                         │               │             ├─REJECT───> DECLINED
                         │               │
                         │               ├─REJECT───> DECLINED
                         │
                         ├─REJECT───> DECLINED

⚠️ CRITICAL: If DISBURSED status not transitioned to PENDING_RECONCILIATION within 24 hours:
             → DEADLINE_EXCEEDED alert triggered
             → Investigation workflow initiated
             → Escalation to management required
```

---

## 🔔 Comparison Table

| Aspect | Reimbursement | Petty Cash |
|--------|---|---|
| **Amount Limit** | No fixed limit | ≤ JMD 5,000 |
| **Prior Action** | Staff purchases first | Finance disburses cash first |
| **Risk** | Financial loss if authorization missed | Accountability loss if 24h rule broken |
| **Timeline** | Flexible (days/weeks) | STRICT 24 hours |
| **Key Control** | Pre-authorization | 24-hour reconciliation |
| **Invoice Type** | Original (after verification) | Itemized receipt (MUST reconcile) |
| **Change** | N/A | MUST equal amount difference |
| **Verification** | Goods/service quality | Goods/service quality + reconciliation |
| **Approval Steps** | 3 (Branch Head, Procurement, Finance) | 4 (Branch Head, Procurement, Finance, Verification) |
| **Primary Risk** | Budget overrun | Unaccounted cash |

---

## 📱 Dashboard Workflow Views

### Requestor Dashboard
```
┌─ Reimbursement Queue ────┐    ┌─ Petty Cash Queue ────────┐
│  • Pending Authorization │    │  • Pending Approval       │
│  • Awaiting Verification │    │  • Pending Disbursal      │
│  • Pending Finance       │    │  • URGENT: Due in 2h      │  ⚠️ Deadline Alert
│  • Completed            │    │  • OVERDUE: Reconcile Now │
└──────────────────────────┘    └───────────────────────────┘

┌─ My Reimbursements ──────┐    ┌─ My Petty Cash ───────────┐
│  [Status Timeline]       │    │  [Status Timeline]        │
│  [Amount Tracker]        │    │  [24h Countdown]          │
│  [Action Items]          │    │  [Deadline Alert]         │
└──────────────────────────┘    └───────────────────────────┘
```

### Branch Head Dashboard
```
┌─ Reimbursement Authorizations ─┐    ┌─ Petty Cash Authorizations ────┐
│  • Pending My Approval          │    │  • Pending My Approval         │
│  • Recent Approved              │    │  • Recent Approved             │
│  • Rejected                     │    │  • Rejected                    │
└─────────────────────────────────┘    └────────────────────────────────┘
```

### Procurement (GC2) Dashboard
```
┌─ Verification Queue ────────┐    ┌─ Petty Cash Endorsements ─────┐
│  • Pending Verification     │    │  • Pending My Endorsement      │
│  • Verified                 │    │  • Recent Endorsed             │
│  • Rejected                 │    │  • Pending Final Verification  │
└─────────────────────────────┘    └────────────────────────────────┘
```

### Finance (GC10A) Dashboard
```
┌─ Reimbursement Approvals ──┐    ┌─ Petty Cash Authorizations ────┐
│  • Pending My Approval      │    │  • Pending My Approval         │
│  • Ready to Process         │    │  • Ready to Disburse           │
│  • Pending Reconciliation   │    │  ⚠️ URGENT: Due in 1h          │
│  • Completed                │    │  • Pending Reconciliation      │
└─────────────────────────────┘    └────────────────────────────────┘
```

---

## 🔗 Integration Points

### Data Flow Diagram

```
┌─────────────────┐
│ Procurement     │
│ Requests Core   │
│ (Branch, Date,  │
│  Description)   │
└────────┬────────┘
         │
         ├──────────────────────────────────────┐
         │                                      │
         ▼                                      ▼
    ┌──────────────┐                  ┌──────────────┐
    │ REIMBURSEMENT│                  │ PETTY CASH   │
    │ Request Path │                  │ Request Path │
    └──────┬───────┘                  └──────┬───────┘
           │                                 │
           ├─> Pre-Authorization            ├─> Disbursement
           │   Table                        │   Table
           │                                │
           ├─> Reimbursement Invoice       ├─> Petty Cash
           │   Table                        │   Reconciliation
           │                                │   Table
           ├─> Procurement Verification    ├─> Workflow
           │   Table                        │   Notifications
           │
           └─> Status History
               Table

All tied to:
• Audit Log (all actions)
• Users (approvers, requestors)
• Branches (departmental tracking)
```

---

## ✅ Process Verification Checklist

### Reimbursement Process Checklist
- [x] Prior authorization obtained
- [x] Purchase made per authorization
- [x] Invoice copy submitted to Procurement
- [x] Goods/services verified satisfactory
- [x] Original invoice submitted to Finance
- [x] Finance approved reimbursement
- [x] Payment issued to staff
- [x] All audit trails completed

### Petty Cash Process Checklist
- [x] Amount ≤ JMD 5,000
- [x] Branch Head authorized
- [x] Procurement endorsed
- [x] Finance authorized & disbursed
- [x] **24-hour clock started**
- [x] Purchase made (within 24h)
- [x] Invoice returned to Finance (within 24h)
- [x] Change returned to Finance (within 24h)
- [x] Procurement verified goods (within 24h)
- [x] Reconciliation completed
- [x] All audit trails completed

---

## 📚 Related Files
- docs/REIMBURSEMENT_PROCESS.md - Detailed reimbursement guide
- docs/PETTY_CASH_PROCESS.md - Detailed petty cash guide
- migrations/009_reimbursement_petty_cash_workflows.sql - Database schema
- config/workflow.php - Workflow functions
- reimbursement/*.php - UI controllers
- petty_cash/*.php - UI controllers
