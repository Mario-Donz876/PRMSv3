# Reimbursement Request Process - Complete Guide

## 📋 Overview

The **Reimbursement Process** allows staff members to be reimbursed for personal expenditures on approved goods or services. The process requires **prior authorization** before purchase and verification of goods/services delivery before payment.

---

## 🔄 Process Flow

```
START (Staff Member)
   │
   ▼
1️⃣ OBTAIN PRIOR AUTHORIZATION
   │
   ├─→ Contact Branch Head
   ├─→ Submit authorization request with:
   │    • Purpose of purchase
   │    • Estimated amount
   │    • Timeline
   │
   ▼
BRANCH HEAD REVIEWS & APPROVES (Role: Branch Head)
   │
   └─→ If approved: Continue
   └─→ If rejected: Process ENDS
   │
   ▼
2️⃣ PURCHASE GOODS/OBTAIN SERVICE
   │
   ├─→ Staff pays with personal funds
   ├─→ Ensure invoice is issued in organization's name
   ├─→ Obtain itemized receipt/invoice
   ├─→ Ensure goods are of good quality or service properly rendered
   │
   ▼
3️⃣ SUBMIT COPY OF INVOICE TO PROCUREMENT (GC2)
   │
   ├─→ Create reimbursement request in PRMS
   ├─→ Attach copy of invoice
   ├─→ Include pre-authorization details
   ├─→ Submit for Procurement verification
   │
   ▼
PROCUREMENT VERIFICATION (Role: Procurement Officer - GC2)
   │
   ├─→ Verify goods received in satisfactory condition
   │   OR
   ├─→ Verify service was properly rendered
   │
   ├─→ If satisfactory: Mark as "VERIFIED"
   └─→ If not satisfactory: Reject with notes
   │
   ▼
4️⃣ SUBMIT ORIGINAL INVOICE TO FINANCE (GC10A)
   │
   ├─→ Only after Procurement verification
   ├─→ Attach ORIGINAL invoice receipt
   ├─→ Request information shows reference number
   │
   ▼
FINANCE REVIEW & PROCESSING (Role: Finance Officer - GC10A)
   │
   ├─→ Review invoice amount
   ├─→ Verify funds are available
   ├─→ Cross-check against pre-authorization amount
   ├─→ Approve and process reimbursement
   │
   ▼
REIMBURSEMENT ISSUED
   │
   ├─→ Payment processed to staff member's account
   ├─→ Confirmation sent to requestor
   │
   ▼
END (Reimbursement Completed)
```

---

## 📊 Database Schema

### Key Tables:
1. **procurement_requests** - Main request record
   - `request_type` = 'REIMBURSEMENT'
   - Tracks overall status

2. **pre_authorizations** - Prior authorization records
   - `authorization_amount` - Approved amount
   - `authorized_by` - Branch Head user_id
   - `authorization_date`

3. **reimbursement_invoices** - Invoice submissions
   - Tracks copy (to Procurement) and original (to Finance)
   - `goods_service_verified` flag

4. **procurement_verifications** - Verification records
   - `verification_type` = 'GOODS_RECEIVED'
   - `condition_status` - satisfactory/defective/incomplete

5. **reimbursement_status_history** - Audit trail

---

## 📌 Key Business Rules

### ✅ Mandatory Requirements:
1. **Prior Authorization MUST be obtained BEFORE purchase**
   - Cannot backdate purchases
   - Staff cannot spend more than authorized amount
   - No exceptions without escalation

2. **Copy of Invoice to Procurement**
   - Required for verification of goods/services
   - Procurement Officer must inspect quality
   - Service verification is qualitative assessment

3. **Original Invoice to Finance**
   - Only after Procurement verification passes
   - Finance cross-checks against authorization
   - Payment only if invoice amount ≤ authorization amount

4. **Reimbursement Amount Cannot Exceed Authorization**
   - System enforces this validation
   - Budget constraint protection

### ⏰ Timeline Guidelines:
- **No specific deadline** for submission (flexible)
- **Invoice amount** typically processed within 1-2 weeks
- **Reimbursement timeframe depends on:**
  - Amount spent
  - Availability of funds
  - Financial processing schedule

---

## 🔐 Approval Authority Mapping

| Step | Role | Authority | Action |
|------|------|-----------|--------|
| 1 | Branch Head | Department Level | Pre-authorization approval |
| 2 | Procurement Officer (GC2) | Department Level | Goods/service verification |
| 3 | Finance Officer (GC10A) | Organization Level | Reimbursement approval & payment |

---

## 📋 Request Status Workflow

```
DRAFT
  ↓ (User submits for approval)
SUBMITTED
  ↓ (Branch Head reviews and authorizes)
PRE_AUTHORIZED
  ↓ (Procurement receives copy of invoice)
PENDING_PROCUREMENT_VERIFICATION
  ↓ (Procurement inspects goods/services)
VERIFIED
  ↓ (User submits original invoice to Finance)
PENDING_ORIGINAL_INVOICE
  ↓
PENDING_FINANCE_REVIEW
  ↓ (Finance reviews and approves)
APPROVED
  ↓ (Payment is processed)
REIMBURSED
  ↓ (Record complete)
COMPLETED

Rejection Paths (available at any stage):
→ DECLINED (with reason)
```

---

## 🎯 User Interface Navigation

### For Requestor (Staff Member):
1. Dashboard → Reimbursement Requests → New Request
2. Fill form with:
   - Branch
   - Request date
   - Description of purchase
   - Pre-authorization date & amount
   - Invoice amount
3. View created request
4. Track status through workflow

### For Branch Head:
1. Dashboard → Pending Approvals → Reimbursement Queue
2. Review pre-authorization requests
3. Approve/Reject with reason

### For Procurement Officer (GC2):
1. Dashboard → Verification Queue
2. Review invoice copy
3. Inspect goods or service records
4. Mark as verified with condition notes

### For Finance Officer (GC10A):
1. Dashboard → Finance Queue
2. Review original invoice
3. Approve reimbursement
4. Process payment

---

## 💡 Important Considerations

### Invoice Documentation:
- Copy for Procurement should clearly show:
  - Item descriptions
  - Unit prices
  - Quantities
  - Total amount
- Original for Finance should match the copy exactly

### Condition Assessment (Procurement):
- **Satisfactory:** Goods received in good condition, service properly rendered
- **Defective:** Goods damaged or not as specified
- **Incomplete:** Service not fully rendered or partial goods
- **Other:** Any other condition affecting acceptance

### Amount Reconciliation:
- Finance must verify: Invoice Amount ≤ Authorization Amount
- If discrepancy found: Contact requestor for clarification
- No payment without verification

---

## 🔄 Integration Points

### With Finance Module:
- Reimbursement records link to payment processing
- Finance queue shows pending reimbursements
- Payment status updates reflected in reimbursement status

### With Audit Module:
- All status changes logged
- Pre-authorization details recorded
- Verification results documented
- Reimbursement payment recorded

### With Dashboard:
- Requestor dashboard shows personal reimbursement status
- Branch Head dashboard shows pending authorizations
- Procurement dashboard shows verification queue
- Finance dashboard shows payment queue

---

## ✅ Verification Checklist

- [ ] Prior authorization obtained before purchase
- [ ] Purchase amount does not exceed authorization
- [ ] Quality of goods/services is satisfactory
- [ ] Invoice itemization is complete and legible
- [ ] Copy submitted to Procurement for verification
- [ ] Original submitted to Finance for payment
- [ ] Payment processed to requestor bank account
- [ ] Record marked as COMPLETED

---

## 🚀 System Features

### Automated Validations:
✅ Prevents invoice amount > authorization amount
✅ Prevents backdated requests if prior authorization required
✅ Tracks authorization amounts
✅ Audit trail of all modifications

### Notifications:
📧 Branch Head gets notification of pending authorization
📧 Procurement gets notification of invoice copy submission
📧 Finance gets notification of original invoice submission
📧 Requestor gets notification of approval/rejection

### Reporting:
📊 Reimbursement status reports
📊 Pending reimbursements by branch
📊 Processing time analytics
📊 Verification completion rates

---

## 📞 Support & Escalation

### Common Issues:

**Q: Can I proceed without prior authorization?**
A: No. Prior authorization is mandatory. Contact your Branch Head immediately.

**Q: What if I spent less than authorized?**
A: Submit only the amount spent. Finance will process the actual invoice amount.

**Q: Can I submit multiple invoices for one pre-authorization?**
A: Yes, as long as the combined amount does not exceed the pre-authorization.

**Q: How long does reimbursement take?**
A: Typically 1-2 weeks, depending on fund availability and Finance processing schedule.

**Q: What happens if Procurement rejects the goods?**
A: You must either resolve the quality issue or the reimbursement is declined.

---

## 📚 Related Documentation

- See PETTY_CASH_PROCESS.md for petty cash requests
- See WORKFLOW_DIAGRAMS.md for visual workflow representations
- See PRMS README for system overview
