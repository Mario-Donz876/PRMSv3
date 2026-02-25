# RFQ Workflow Quick Start Guide
**For System Users** | February 19, 2026

## What Changed?

The procurement system now follows a complete end-to-end workflow from request approval through payment. Here's what you need to know:

---

## The Workflow at a Glance

```
Request Approved
       ↓
   RFQ Letter Available (send to vendors)
       ↓
   Vendors Submit Quotes
       ↓
   Review & Select Best Quote
       ↓
   Create Commitment (Accounts)
       ↓
   Finance Approves Commitment
       ↓
   Create PO (Procurement)
       ↓
   Approve PO (HOD/Finance)
       ↓
   Upload Vendor Invoice
       ↓
   Record Payment (Finance)
```

---

## Step-by-Step: How to Use Each Stage

### 1. Request Approval ✅
**Status:** HOD_APPROVED / DIRECTOR_APPROVED / GC_APPROVED  
**Who:** Approver  
**What to do:** Approve the procurement request

**When you approve:**
- The "Create RFQ & Generate Letters" button appears
- You're ready to move to step 2

---

### 2. RFQ Letter Generation 📬
**Status:** RFQ_LETTER_AVAILABLE  
**Who:** Procurement Officer  
**What to do:** Create RFQ and generate letters to send to vendors

**How:**
1. Go to request view
2. Click "Create RFQ & Generate Letters" button
3. System creates RFQ
4. Click "Generate RFQ Letters" 
5. Download PDF and send to vendors
6. Vendors submit quotes by deadline

**Pro Tips:**
- RFQ letters can be generated immediately after approval (no waiting!)
- Send to 3+ vendors for competitive pricing
- Set a clear submission deadline in the letter

---

### 3. Quote Review & Selection 🔍
**Status:** QUOTE_REVIEW_PENDING → QUOTE_APPROVED  
**Who:** Requestor, Branch Head, or HOD  
**What to do:** Review vendor quotes and select the best one

**How to Review:**
1. Go to RFQ view
2. Review each vendor's quote
3. Check supporting documents
4. Verify quote meets all requirements
5. Mark as "Meets Requirements" or "Does Not Meet"
6. Add comments if needed

**How to Select:**
1. Choose best quote based on:
   - Price (lowest)
   - Quality (meets specs)
   - Vendor reliability
2. Click "Select Quote"
3. System updates RFQ status to QUOTE_APPROVED

**Tip:** You can reject quotes that don't meet specs! This prevents poor vendor contracts.

---

### 4. Create Commitment from GFMS 💰
**Status:** COMMITMENTS_PENDING → COMMITMENT_APPROVED  
**Who:** Accounts Officer (creates), Finance Officer (approves)  
**What to do:** Create commitment in system with selected quote amount

**How:**
1. Go to request view
2. Selected quote is now visible
3. Click "Create Commitment"
4. Enter:
   - Commitment Date
   - Commitment Amount (from selected quote)
   - GFMS Commitment Number
5. Click Submit
6. Finance Officer receives approval notification
7. Finance Officer approves commitment

**Important:**
- Commitment amount should match selected quote
- GFMS number must match the number in GFMS system
- Only possible AFTER quote is selected

---

### 5. Create Purchase Order from GFMS 📋
**Status:** PO_PENDING → PO_APPROVED  
**Who:** Procurement Officer (creates), HOD/Finance (approves)  
**What to do:** Create PO in system based on approved commitment

**How:**
1. Go to approved commitment
2. Click "Create PO"
3. Enter:
   - PO Date
   - PO Amount (from commitment)
   - GFMS PO Number
   - Line Items (optional)
4. Click Submit
5. HOD and Finance Officer approve
6. PO status becomes PO_APPROVED

**Approval Flow:**
- HOD approves first
- Then Finance Officer approves

---

### 6. Upload Vendor Invoice 📄
**Status:** INVOICE_RECEIVED  
**Who:** Accounts Officer, Finance Officer  
**What to do:** Receive vendor invoice and upload to system

**How:**
1. When vendor sends invoice, verify against PO:
   - Check amounts match (±tolerance)
   - Verify items received
   - Confirm dates
2. Go to PO view
3. Click "Add Invoice"
4. Enter:
   - Invoice Number (from vendor)
   - Invoice Date
   - Invoice Amount
   - Upload invoice file
5. Click Submit
6. System links invoice to PO

**Tip:** Keep invoice file for records

---

### 7. Record Payment 💳
**Status:** COMPLETED  
**Who:** Finance Officer  
**What to do:** Process payment to vendor

**How:**
1. Go to invoice view
2. Click "Record Payment"
3. Enter:
   - Payment Date
   - Payment Reference (check number, etc.)
   - Payment Amount
4. Click Submit
5. System updates payment status

**Final Status:** Request is now COMPLETED

---

## Key Features of New Workflow

✅ **RFQ Letter Immediately Available** - No waiting for multiple approvals  
✅ **Quote Review & Approval** - Requestor controls quality before commitment  
✅ **GFMS Integration** - Track GFMS numbers throughout process  
✅ **Clear Approval Steps** - Each stage has clear owners and approvals  
✅ **Full Audit Trail** - Every step is logged and tracked  
✅ **Direct Procurement Still Supported** - For under-threshold purchases

---

## Status Reference

| Status | Meaning | Next Action |
|--------|---------|-------------|
| HOD_APPROVED | Approved by HOD | Generate RFQ Letters |
| RFQ_LETTER_AVAILABLE | Ready to send RFQs | Create RFQ, send to vendors |
| QUOTE_REVIEW_PENDING | Vendors submitted quotes | Review & select quote |
| QUOTE_APPROVED | Quote selected | Create commitment |
| COMMITMENTS_PENDING | Creating commitment | Finance approves |
| COMMITMENT_APPROVED | Commitment ready | Create PO |
| PO_PENDING | Creating PO | Get approvals |
| PO_APPROVED | PO complete | Upload invoice |
| INVOICE_RECEIVED | Invoice uploaded | Record payment |
| COMPLETED | Fully paid | ✓ Complete |

---

## Who Does What?

| Role | Action | Stage |
|------|--------|-------|
| Requestor | Submit request, review quotes, approve quote | Multiple |
| HOD | Approve request, review quotes, approve PO | Multiple |
| Procurement Officer | Generate RFQ, manage vendors, create PO | RFQ + PO |
| Accounts Officer | Create commitment, upload invoice | Commitment + Invoice |
| Finance Officer | Approve commitment, approve PO, record payment | Final stages |

---

## Common Questions

**Q: Can I skip the RFQ process?**  
A: Yes, if purchase is under 500k or is petty cash/reimbursement, you go directly to AWARDED.

**Q: What if a quote doesn't meet requirements?**  
A: Mark it as "Does Not Meet" with comments. You can select a different vendor's quote.

**Q: Can I change the quote after approval?**  
A: You cannot undo quote selection, but you can document the reason in notes and work with your supervisor.

**Q: What if vendor reduces price before PO?**  
A: Document in commitment/PO notes and get approval for the change.

**Q: How do I track where my request is?**  
A: Check the request status and view tab. Each stage shows current approvers.

---

## Key Rules to Remember

1. ✅ Request must be approved before RFQ letter generation
2. ✅ Quote must be selected before commitment creation
3. ✅ Commitment must be approved before PO creation
4. ✅ PO must be approved before invoice upload
5. ✅ GFMS numbers must match between commitment and system
6. ✅ All approvers must complete their stage

---

## Getting Help

- **Questions about status?** Check "View" → "Status" section
- **Need to go back a step?** Contact your supervisor
- **System error?** Check audit log or contact admin
- **How do I approve?** Look for "Pending Approval" dashboard

---

## Troubleshooting

**Problem:** "Can't create commitment"  
**Solution:** Check if quote is selected (QUOTE_APPROVED status)

**Problem:** "PO button not showing"  
**Solution:** Wait for commitment to be approved by Finance Officer

**Problem:** "Can't upload invoice"  
**Solution:** Make sure PO is approved (both HOD and Finance)

**Problem:** "GFMS number error"  
**Solution:** Verify number matches exactly in GFMS system (no spaces, correct case)

---

## Contact Info

For workflow questions: [Procurement Department]  
For system issues: [IT Support]  
For approval delays: [Your Manager]

---

**Remember:** Each stage in the workflow protects the organization and ensures quality in procurement. Thank you for following these steps carefully!
