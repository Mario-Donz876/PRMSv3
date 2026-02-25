# Petty Cash Procurement Process - Complete Guide

## 📋 Overview

The **Petty Cash Procurement Process** streamlines purchasing of small amounts (≤ JMD 5,000) by allowing direct cash disbursement rather than full procurement workflows. Strict **24-hour accountability** ensures funds are used immediately and properly documented.

---

## 🔄 Process Flow

```
START (Staff Member)
   │
   ▼
1️⃣ IDENTIFY NEED FOR PETTY CASH
   │
   └─→ Determine if purchase amount is ≤ JMD 5,000
   │
   ▼
2️⃣ CONFIRM AMOUNT IS $5,000 OR LESS
   │
   ├─→ If YES: Continue
   │
   └─→ If NO: Use Standard Procurement Process → END
   │
   ▼
3️⃣ COMPLETE PROCUREMENT REQUEST FORM
   │
   ├─→ Branch identification
   ├─→ Item descriptions
   ├─→ Quantities
   ├─→ Estimated total amount
   │
   ▼
4️⃣ COMPLETE PETTY CASH REQUEST FORM
   │
   ├─→ Purpose of petty cash
   ├─→ Amount requested
   ├─→ Justification
   │
   ▼
5️⃣ BRANCH HEAD REVIEWS & AUTHORIZES
   │
   ├─→ Review amount and purpose
   ├─→ Verify available branch budget
   ├─→ Approve or reject request
   │
   ▼
6️⃣ SUBMIT TO PROCUREMENT (GC2)
   │
   ├─→ Procurement Officer reviews request
   ├─→ Endorses the petty cash form
   ├─→ Confirms amount is within limits
   │
   ▼
FORWARD TO FINANCE DEPARTMENT (GC10A)
   │
   ├─→ Finance receives endorsed request
   ├─→ Prepares cash disbursement
   │
   ▼
7️⃣ FINANCE AUTHORIZES PETTY CASH FORM
   │
   ├─→ Final authorization for disbursement
   ├─→ Confirms funds availability
   │
   ▼
8️⃣ FINANCE DISBURSES PETTY CASH
   │
   ├─→ Cash handed over to staff member
   ├─→ Receipt signed and recorded in system
   ├─→ **24-HOUR DEADLINE STARTS NOW** ⏱️
   │
   ▼
9️⃣ WITHIN 24 HOURS - ACCOUNTABILITY WINDOW:
   │
   ✓ PURCHASE MUST BE MADE
   │ └─→ Purchase approved items at vendor
   │ └─→ Negotiate best prices
   │ └─→ Obtain itemized receipt/invoice
   │
   ✓ ORIGINAL INVOICE RETURNED TO FINANCE
   │ └─→ Complete documentation of purchases
   │ └─→ Invoice shows items and amounts
   │
   ✓ CHANGE RETURNED TO FINANCE
   │ └─→ Any remaining balance returned
   │ └─→ Balance must reconcile to zero
   │
   ✓ PROCUREMENT VERIFIES GOODS/SERVICE
   │ └─→ Inspect purchased items
   │ └─→ Verify quantity and quality
   │ └─→ Confirm service delivery
   │
   ▼
ACCOUNTABILITY CONFIRMED
   │
   ├─→ All documentation in order
   ├─→ All funds accounted for
   ├─→ Goods/services verified
   │
   ▼
END (Petty Cash Completed Successfully)

⚠️ IMPORTANT:
- If 24-hour deadline MISSED: Investigation required
- If goods/services NOT verified: Request escalation
- If change NOT returned: Advance recovery process
```

---

## 📊 Database Schema

### Key Tables:
1. **procurement_requests** - Main request record
   - `request_type` = 'PETTY_CASH'
   - `estimated_value` ≤ 5000

2. **petty_cash_disbursements** - Disbursement tracking
   - `amount_authorized` - Authorized petty cash amount
   - `disbursement_date` - When cash was handed over
   - `disbursement_deadline` - 24 hours from disbursement
   - `status` - AUTHORIZED → DISBURSED → RECONCILED → VERIFIED → COMPLETED

3. **petty_cash_reconciliations** - Reconciliation records
   - `purchase_amount` - Amount spent
   - `change_amount` - Balance returned
   - `submission_date` - When reconciled
   - `hours_from_disbursement` - Calculated to verify 24-hour compliance
   - `submission_deadline_met` - Boolean flag

4. **procurement_verifications** - Goods/service verification
   - `verification_type` = 'PETTY_CASH_PURCHASED'
   - `condition_status` - satisfactory/defective/incomplete

5. **workflow_notifications** - Deadline alerts
   - Sends alerts as deadline approaches
   - DEADLINE_APPROACHING (2 hours remaining)
   - DEADLINE_EXCEEDED (if missed)

---

## 📌 Key Business Rules

### ✅ Mandatory Requirements:

1. **Amount Limit: ≤ JMD 5,000**
   - Configured in system_config table
   - Admin can adjust limit if needed
   - System rejects amounts exceeding limit

2. **24-Hour Accountability Window (CRITICAL)**
   - **Start:** Exact time cash is disbursed
   - **End:** Exactly 24 hours later
   - All of the following MUST happen within this window:
     - a) Purchase must be made
     - b) Invoice returned to Finance
     - c) Change (if any) returned to Finance
     - d) Procurement must verify goods/services
   - **VIOLATION:** Missing deadline triggers investigation

3. **Zero-Balance Requirement**
   - Purchase Amount + Change Returned = Disbursement Amount
   - No unaccounted funds
   - Finance reconciles daily

4. **Procurement Verification Mandatory**
   - Goods must be inspected for quality
   - Service delivery must be confirmed
   - Condition noted: satisfactory/defective/incomplete

### ⏰ Timeline - STRICT 24-HOUR RULE:
```
[Disbursement] ←---- 24 HOURS ----→ [Deadline]
   T+0h          (Purchase + Reconcile + Verify)     T+24h

Actions must fit within this window or request is flagged.
```

---

## 🔐 Approval Authority Mapping

| Step | Role | Authority | Action |
|------|------|-----------|--------|
| 1 | Branch Head | Department Level | Review & authorize request |
| 2 | Procurement Officer (GC2) | Department Level | Endorse petty cash form |
| 3 | Finance Officer (GC10A) | Organization Level | Authorize & disburse cash |
| 4 | Finance Officer (GC10A) | Organization Level | Reconciliation verification |
| 5 | Procurement Officer (GC2) | Department Level | Goods/service verification |

---

## 📋 Request Status Workflow

```
DRAFT
  ↓ (Staff submits)
SUBMITTED
  ↓ (Branch Head approves)
HOD_REVIEWED
  ↓ (Procurement endorses)
PROCUREMENT_ENDORSED
  ↓ (Finance authorizes)
FINANCE_AUTHORIZED
  ↓ (Cash disbursed)
DISBURSED ⏱️ [24-HOUR DEADLINE ACTIVE]
  ↓ (Within 24 hours: purchase, return invoice & change)
PENDING_RECONCILIATION
  ↓ (Procurement verifies goods/services)
PROCUREMENT_VERIFIED
  ↓ (All verified - reconciliation complete)
COMPLETED

Alternative Paths:
PENDING_RECONCILIATION → RECONCILIATION_DISCREPANCY (if issues found)
                       ↓
                      REVIEWED
                       ↓
                      COMPLETED (after resolution)

Rejection Paths (available early in workflow):
SUBMITTED → DECLINED
HOD_REVIEWED → DECLINED
PROCUREMENT_ENDORSED → DECLINED
FINANCE_AUTHORIZED → DECLINED
```

---

## 🎯 User Interface Navigation

### For Requestor (Staff Member):
1. Dashboard → Petty Cash → New Request
2. Fill form:
   - Branch
   - Amount requested (≤ 5000)
   - Purpose of petty cash
3. Submit for approval
4. After approval: Collect cash from Finance
5. **Within 24 hours:** Reconcile using system

### For Branch Head:
1. Dashboard → Pending Approvals → Petty Cash Queue
2. Review request details
3. Approve/Reject with reason
4. Submit to Procurement

### For Procurement Officer (GC2):
1. Dashboard → Petty Cash Queue
2. Review endorsed petty cash form
3. Endorse and forward to Finance
4. **Later:** After Finance disbursal, verify goods

### For Finance Officer (GC10A):
1. Dashboard → Petty Cash Authorization Queue
2. Review endorsed form
3. Authorize and prepare cash
4. Disburse cash to staff (mark disbursement time)
5. **Within 24 hours:** Receive reconciliation
6. Verify invoice and change amounts
7. Mark as reconciled

---

## 💡 Important Considerations

### Cash Handling:
- **Secure:** Finance handles cash securely
- **Documented:** All transactions logged in system
- **Audited:** All cash movements auditable

### Invoice Requirements:
- **Itemized:** Must show items purchased, not just total
- **Original:** Original receipt/invoice submitted (not copy)
- **Legible:** All details clearly written/printed
- **Match:** Items on invoice must match petty cash purpose

### Change Reconciliation:
- **Calculation:** (Disbursement Amount) - (Purchase Amount) = Change
- **Documentation:** Change returned to Finance or justified
- **Variance:** Any 1% variance flagged for investigation (e.g., overpaid by 50 cents)

### Goods/Service Verification:
- **Physical Inspection:** Procurement Officer inspects items (if goods)
- **Quality Check:** Items match specifications in invoice
- **Condition:** Note any damage, defects, or incomplete delivery
- **Certification:** Officer sign-off on verification

### 24-Hour Rule Violations:
- **Approaching Deadline:** System sends alert at T+22h
- **Missed Deadline:** Flag raised at T+24h if reconciliation not received
- **Investigation Trigger:** 
  - Require staff explanation
  - Review cash flow
  - Determine if cash was misused
  - Recovery action if needed

---

## 🔄 Integration Points

### With Finance Module:
- Cash disbursement from Finance treasury
- Reconciliation verification against disbursement
- Payment audit trail

### With Procurement Module:
- Goods/service inspection and verification
- Quality assurance records
- Vendor compliance tracking

### With Audit Module:
- Complete audit trail of all transactions
- Deadline compliance monitoring
- Variance tracking
- Investigation documentation

### With Dashboard:
- Staff dashboard shows petty cash status & deadline
- Branch Head sees pending approvals
- Finance sees pending reconciliations
- Procurement sees verification queue

---

## ✅ Verification Checklist

### Pre-Disbursement:
- [ ] Branch Head approved request
- [ ] Procurement Officer endorsed form
- [ ] Finance authorized disbursement
- [ ] Amount ≤ JMD 5,000

### Within 24-Hour Window:
- [ ] Purchase made per request purpose
- [ ] Original itemized invoice obtained
- [ ] All items received in good condition
- [ ] Original invoice returned to Finance
- [ ] Change (if any) returned to Finance
- [ ] Procurement verified goods/services

### Post-24-Hour Window:
- [ ] Reconciliation documents complete
- [ ] Purchase amount recorded
- [ ] Change amount recorded
- [ ] Invoice and change amounts reconciled
- [ ] Procurement verification completed
- [ ] All status updates recorded

---

## 🚀 System Features

### Automated Validations:
✅ Enforces ≤ JMD 5,000 limit
✅ Calculates 24-hour deadline automatically
✅ Tracks time from disbursement to reconciliation
✅ Prevents purchase amount > authorized amount
✅ Requires zero-balance reconciliation

### Automated Notifications:
📧 T+0h: Disbursement confirmation
📧 T+22h: Deadline approaching alert
📧 T+24h: If not reconciled, escalation alert
📧 Throughout: Status updates to all stakeholders

### Deadline Monitoring:
⏱️ Real-time countdown display
⏱️ Visual indicators (green/yellow/red)
⏱️ Overdue flag after deadline
⏱️ Escalation workflow for misses

### Reporting:
📊 Petty cash utilization by branch
📊 24-hour compliance rate
📊 Average reconciliation time
📊 Deadline miss rate
📊 Variance analysis

---

## 📞 Support & Escalation

### Common Issues:

**Q: What if I can't spend all the petty cash within 24 hours?**
A: You must return the unused balance to Finance within 24 hours. The system requires zero-balance reconciliation.

**Q: What's the deadline if I receive cash at 3 PM?**
A: Exactly 24 hours later at 3 PM the next day.

**Q: Can I ask for an extension beyond 24 hours?**
A: No. The rule is firm. Contact your Branch Head before requesting petty cash if you cannot meet the deadline.

**Q: What happens if I miss the 24-hour deadline?**
A: Investigation will be triggered. You must explain the delay. Repeated violations may result in policy escalation.

**Q: Can I request petty cash multiple times per day?**
A: Yes, but each request is separate and each has its own 24-hour deadline.

**Q: Do I need permission for each item or just total amount?**
A: Just the total amount. However, you must stay within the stated purpose.

**Q: What if vendor raises price after I agreed?**
A: Budget only what vendor quoted. If price increases exceed authorization, reject the sale and return cash.

---

## 🔗 Related Documentation

- See REIMBURSEMENT_PROCESS.md for reimbursement requests
- See WORKFLOW_DIAGRAMS.md for visual workflow representations
- See PRMS README for system overview

---

## ⚠️ Audit & Compliance Notes

### Critical Controls:
1. **24-Hour Rule:** Non-negotiable time limit for accountability
2. **Zero-Balance:** No unaccounted petty cash at any time
3. **Documentation:** Original invoices required (not estimates)
4. **Verification:** Procurement must physically inspect items
5. **Audit Trail:** All actions timestamped and user-attributable

### Investigation Triggers:
- Invoice amount > authorized amount
- Change not returned to Finance
- 24-hour deadline missed
- Goods not verified within 24 hours
- Variance in reconciliation > 1%
- Duplicate petty cash requests same day

### Escalation Path:
1. **First Violation:** Warning + documentation
2. **Second Violation:** Investigation + manager notification
3. **Third Violation:** Possible suspension of petty cash privileges
4. **Serious Violation:** Policy escalation + audit review
