# Reimbursement & Petty Cash Workflow Implementation

## Current System Status

### ✅ EXISTING SUPPORT
- Database: `request_type` column in procurement_requests (to be added via database_fixes.sql)
- PHP: procurement/add.php already supports REIMBURSEMENT and PETTY_CASH request types
- Workflow: config/workflow.php has logic for routing these request types
- Configuration: system_config table for petty_cash_limit and direct_procurement_threshold

### ❌ GAPS IDENTIFIED

#### 1. Database Schema Gaps
- [ ] No reimbursement-specific invoice tracking
- [ ] No petty cash account records
- [ ] No 24-hour accountability tracking for petty cash
- [ ] No procurement verification status for reimbursements
- [ ] No change tracking for petty cash disbursements

#### 2. Workflow Feature Gaps
- [ ] No reimbursement approval workflow implementation
- [ ] No petty cash approval workflow implementation
- [ ] No prior authorization step for reimbursements
- [ ] No goods verification workflow
- [ ] No 24-hour deadline enforcement for petty cash
- [ ] No change return tracking

#### 3. UI/Navigation Gaps
- [ ] No separate reimbursement request list/view
- [ ] No separate petty cash request list/view
- [ ] No reimbursement creation form
- [ ] No petty cash creation form
- [ ] No verification interfaces for procurement
- [ ] No change/balance tracking interface

#### 4. Process Flow Gaps
- [ ] No prior authorization verification step
- [ ] No goods/service verification step (after Procurement GC2)
- [ ] No original invoice submission to Finance (after verification)
- [ ] No 24-hour deadline tracking for petty cash
- [ ] No change return documentation

---

## Implementation Roadmap

### PHASE 1: Database Schema
**Files to Create:**
- migrations/009_reimbursement_workflow.sql

**Tables to Create:**
1. reimbursement_requests (tracks reimbursement-specific data)
2. reimbursement_invoices (links reimbursement requests to invoices)
3. petty_cash_accounts (tracks P disbursements)
4. petty_cash_transactions (tracks purchases and change returns)
5. pre_authorizations (tracks prior authorizations for reimbursements)
6. procurement_verifications (tracks good/service verification)

### PHASE 2: Workflow Configuration
**Files to Modify:**
- config/workflow.php (update approval chains)
- config/helper.php (add workflow helpers)

**Changes:**
- Reimbursement approval chain: Branch Head → Procurement → Finance
- Petty cash approval chain: Staff → Branch Head → Finance
- 24-hour deadline tracking logic
- Change return handling

### PHASE 3: Feature Implementation
**Reimbursement Module:**
- reimbursement/add.php (create reimbursement request)
- reimbursement/list.php (list all reimbursement requests)
- reimbursement/view.php (view reimbursement details)
- reimbursement/authorize.php (Branch Head authorization)
- reimbursement/submit_invoice.php (Submit copy to Procurement)
- reimbursement/verify.php (Procurement verification interface)
- reimbursement/submit_original.php (Submit original invoice to Finance)

**Petty Cash Module:**
- petty_cash/add.php (create petty cash request)
- petty_cash/list.php (list all petty cash requests)
- petty_cash/view.php (view petty cash details)
- petty_cash/authorize.php (Branch Head authorization)
- petty_cash/disburse.php (Finance disbursal interface)
- petty_cash/reconcile.php (Document purchases and change)
- petty_cash/verify.php (Procurement verification)

### PHASE 4: Dashboard Updates
**Files to Modify:**
- dashboard/requestor.php (add reimbursement and petty cash sections)
- dashboard/hod.php (add authorization options)
- dashboard/admin.php (add oversight)

### PHASE 5: Documentation & Testing
**Documentation:**
- REIMBURSEMENT_PROCESS.md (complete process guide)
- PETTY_CASH_PROCESS.md (complete process guide)
- WORKFLOW_DIAGRAMS.md (visual flowcharts)

---

## Key Business Rules to Implement

### Reimbursement Process
1. ✅ Staff must obtain prior authorization (Branch Head)
2. ✅ Staff purchases goods/services and pays personally
3. ✅ Staff submits copy of invoice to Procurement (GC2)
4. ✅ Procurement verifies goods/services received satisfactorily
5. ✅ Staff submits ORIGINAL invoice to Finance (GC10A)
6. ✅ Finance reviews and processes reimbursement

### Petty Cash Process
1. ✅ Amount must be ≤ JMD 5,000 (configurable)
2. ✅ Staff completes Procurement Request Form + Petty Cash Form
3. ✅ Branch Head reviews and authorizes
4. ✅ Submitted to Procurement (GC2) for endorsement
5. ✅ Forwarded to Finance (GC10A) for authorization
6. ✅ Finance disburses cash
7. ✅ **Within 24 hours:**
   - Purchase must be made
   - Original invoice returned to Finance
   - Change returned to Finance
   - Procurement verifies goods/services
8. ✅ Accountability confirmed

---

## Approval Authority Mapping

### Reimbursement Authorization
- **Prior Authorization Approver:** Branch Head
- **GC2 (Procurement Endorsement):** Procurement Officer
- **GC10A (Finance):** Finance Officer/Manager

### Petty Cash Authorization
- **Form Authorization:** Branch Head
- **GC2 (Procurement Endorsement):** Procurement Officer
- **GC10A (Finance Authorization & Disbursal):** Finance Officer

---

## Status Workflow Definitions

### Reimbursement Request Statuses
```
DRAFT → SUBMITTED → AUTHORIZED → PENDING_PROCUREMENT_VERIFICATION 
→ PENDING_ORIGINAL_INVOICE → PENDING_FINANCE_REVIEW → APPROVED → REIMBURSED
→ COMPLETED

REJECTED paths available at: AUTHORIZED, PENDING_PROCUREMENT_VERIFICATION, 
PENDING_FINANCE_REVIEW
```

### Petty Cash Request Statuses
```
DRAFT → SUBMITTED → HOD_APPROVED → PROCUREMENT_ENDORSED 
→ FINANCE_AUTHORIZED → DISBURSED → PENDING_RECONCILIATION 
→ VERIFIED → COMPLETED

REJECTED paths available at: SUBMITTED, PROCUREMENT_ENDORSED, FINANCE_AUTHORIZED
```

---

## Integration Points

### With Existing Systems
1. **Procurement Module:**
   - Verification interface for goods/services
   - Link to RFQ/Tender process (for reference)

2. **Finance Module:**
   - Invoice linking
   - Payment processing
   - Change return tracking

3. **Audit Module:**
   - All transactions logged
   - 24-hour deadline tracking
   - Change verification

4. **Dashboard Module:**
   - Request queue displays
   - Status tracking
   - Metrics/analytics

---

## Priority Implementation Sequence

### Priority 1: Database & Core Workflow
- Create database schema
- Update workflow.php
- Database_fixes.sql application

### Priority 2: Reimbursement Module
- Create add/list/view pages
- Authorization workflow
- Procurement verification interface
- Finance submission interface

### Priority 3: Petty Cash Module
- Create add/list/view pages
- Authorization workflow
- 24-hour reconciliation interface
- Verification interface

### Priority 4: Dashboard & Integration
- Update dashboards
- Add queue displays
- Add metrics

### Priority 5: Documentation
- Complete process guides
- Flowcharts
- Training materials
