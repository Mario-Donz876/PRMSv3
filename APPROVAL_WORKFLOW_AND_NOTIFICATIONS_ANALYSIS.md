# PRMSv3 Approval Workflow and Notifications Analysis

## Executive Summary

The PRMSv3 system uses a **branch-based single-approver model** where each branch has exactly ONE designated approver, regardless of request amount. The "Branch Head" is typically the HOD (Head of Department), with specialized branches having different approvers (Director HRM&A, Deputy Government Chemist). The system automatically sends notifications to approvers and requestors at key workflow stages.

---

## 1. CURRENT APPROVAL/REQUEST FLOW

### 1.1 Request Lifecycle

```
DRAFT → SUBMITTED → [APPROVAL STAGE(S)] → AWARDED/declined → PROCUREMENT → COMPLETED
        (Requestor)   (Branch Approver)
```

### 1.2 Branch-Based Approval Chain

The approval routing is determined by **branch ID** at submission time:

| Branch ID | Branch Name | Approver Role | Approver Title |
|-----------|-------------|---------------|----------------|
| 5 | HRM&A | Director HRM&A | Finance/HR Director |
| 6 | Analytical & Advisory | Deputy Government Chemist | Government Chemist |
| All Others | Various | HOD | Head of Department |

**Special Cases:**
- **Petty Cash Requests**: Bypass HOD → Direct to Finance Officer (fund verification only)
- **Reimbursement Requests**: Bypass HOD → Direct to Finance Officer (fund verification only)

### 1.3 Complete Request Flow with Status Transitions

```
1. DRAFT
   └─ Requestor creates request in draft status

2. SUBMITTED  
   └─ Requestor submits request
   └─ Approval chain created based on branch/type
   └─ Request awaits branch approver

3. [APPROVAL STAGE] - Branch Approver Acts
   ├─ HOD_APPROVED (for most branches)
   ├─ DIRECTOR_APPROVED (for HRM&A branch requests)
   └─ GC_APPROVED (for Analytical & Advisory branch requests)

4. Threshold Check
   ├─ Under Threshold (≤ system threshold)
   │  └─ RFQ_LETTER_AVAILABLE (can generate RFQ letter)
   │     └─ Vendor quotes → QUOTE_REVIEW_PENDING
   │     └─ Quote selected → QUOTE_APPROVED
   │     └─ Finance approves → COMMITMENT_APPROVED
   │     └─ PO created → PO_PENDING
   │     └─ Invoice → COMPLETED
   │
   └─ Over Threshold (> system threshold)
      └─ PROCUREMENT_STAGE (evaluation process)
         └─ EVALUATION_STAGE → COMMITTEE_RECOMMENDED → GC_APPROVED
         └─ Vendor selected → AWARDED
         └─ Finance approves → COMMITMENT_APPROVED
         └─ PO created → PO_PENDING
         └─ Invoice → COMPLETED

5. AWARDED
   └─ Request approved and ready for procurement

6. DECLINED (at any approval stage)
   └─ Request rejected with reason
   └─ Requestor notified
```

---

## 2. WHERE BRANCH HEAD APPROVES/DECLINES REQUESTS

### 2.1 Primary Approval Point: `/procurement/approve_hod.php`

**File Location**: [/workspaces/PRMSv3/procurement/approve_hod.php](procurement/approve_hod.php)

**Key Functionality**:
- Branch approver reviews and decides on requests
- **Approve Action**:
  - Updates request status based on approval chain
  - Marks approval as "approved" in `request_approvals` table
  - Logs audit trail
  - Sends notifications to next approver or requestor
  - Transitions request to next status (e.g., DIRECTOR_APPROVED, GC_APPROVED)

- **Reject Action**:
  - Requires rejection reason
  - Updates status to DECLINED
  - Clears approval chain
  - Logs audit entry
  - **Sends notification to requestor** with decline reason

**Authorization Check**:
```php
// Only authorized user for this approval stage can approve
if (!canApproveStage($current_role, $nextApproval['role'], $estimatedValue)) {
    // Access denied
}
```

### 2.2 Secondary Approval Points

1. **Generic Approval Handler**: `/procurement/approve.php`
   - Used for any approval stage (not just HOD)
   - Follows same pattern as approve_hod.php
   - Notifies next approver or requestor based on status

2. **Finance Approval**: `/procurement/approve_finance.php`
   - Finance Officer approves fund verification
   - Marks FUNDS_VERIFIED status
   - Also sends notifications to next approver/requestor

3. **Direct Decline**: `/procurement/decline.php`
   - Allows approval authority to decline requests at SUBMITTED status
   - Cleans up approval chain
   - Sends decline notification to requestor

---

## 3. CURRENT NOTIFICATIONS BEING SENT

### 3.1 Notification System Configuration

**File Location**: [/workspaces/PRMSv3/config/notifications.php](config/notifications.php)

**System Features**:
- Notifications can be enabled/disabled globally via `system_config` table
- Function: `notificationsEnabled()` - checks global setting
- Default: Enabled (if no config found, returns true)

### 3.2 Notification Types and Triggers

#### 3.2.1 Request Submitted Notification
**Function**: `notifyRequestSubmitted($requestId)`
**Trigger**: When request transitions from DRAFT → SUBMITTED
**Recipient**: First approver in the chain (based on branch)
**Location Called**: [/workspaces/PRMSv3/procurement/submit.php](procurement/submit.php)
**Email Content**:
- Request number and status
- Requestor name
- Branch name
- Request type (REGULAR, PETTY_CASH, REIMBURSEMENT)
- Estimated value
- Action link to approve

#### 3.2.2 Finance Department Notification (Direct Approval)
**Function**: `notifyFinanceForDirectApproval($requestId, $requestType)`
**Trigger**: When PETTY_CASH or REIMBURSEMENT requests are submitted
**Recipient**: All Finance Officers
**Email Content**:
- Request type (Petty Cash or Reimbursement)
- Amount and description
- Requestor name
- Action link to verify funds

#### 3.2.3 Approval Needed Notification
**Function**: `notifyApprovalNeeded($requestId, $stage, $approverId)`
**Trigger**: After request submission (sent to first approver)
**Recipient**: Specific approver user
**Email Content**:
- Request details
- Current approval stage
- Action link to approve

#### 3.2.4 Next Approver Escalation Notification
**Function**: `notifyNextApprover($requestId, $completedStage)`
**Trigger**: After each approval stage completion, if more approvals pending
**Recipient**: Next approver role in the chain
**Location Called**: 
- `approve_hod.php` (after HOD approves)
- `approve.php` (after generic approval)
- `approve_finance.php` (after finance approves)
**Email Content**:
- Previous stage completed
- Next approval stage info
- Request details
- Action link to approve

#### 3.2.5 Request Finalized Notification
**Function**: `notifyRequestFinalized($requestId, $finalStatus)`
**Trigger**: When request reaches final approval status (AWARDED, RFQ_LETTER_AVAILABLE, PROCUREMENT_STAGE)
**Recipient**: Original requestor
**Location Called**: 
- `approve_hod.php` (after final approval)
- `approve_finance.php` 
- `approve.php`
- `gc_approve.php`
- `award.php`
**Email Content**:
- Final status (shown as green for approved, red for declined)
- Request details
- Action link to view full details

#### 3.2.6 Request Declined Notification
**Function**: `notifyRequestDeclined($requestId, $requestorId, $declineReason)`
**Trigger**: When request is declined at any approval stage
**Recipient**: Original requestor
**Location Called**:
- `decline.php`
- `approve_hod.php` (when rejecting)
- `approve_finance.php` (when rejecting)
- Any approver rejection
**Email Content**:
- DECLINED status
- Decline reason
- Request details
- Approver name
- Suggestion to resubmit if needed

#### 3.2.7 Commitment Lifecycle Notifications
**Function**: `notifyCommitmentAction($requestId, $commitmentNumber, $action, $details)`
**Trigger**: 
- When commitment is created (CREATED)
- When finance approves commitment (APPROVED)
- When finance declines commitment (DECLINED)
**Recipient**: 
- Requestor always
- Finance Officers (if commitment created)
**Actions**:
- `CREATED` - Commitment created, awaiting finance verification
- `APPROVED` - Finance verified, commitment approved
- `DECLINED` - Finance rejected due to insufficient funds

#### 3.2.8 Commitment Notification to Procurement
**Function**: `notifyProcurementOfCommitment($requestId, $commitmentNumber)`
**Trigger**: When Finance uploads commitment
**Recipient**: Procurement Officer
**Purpose**: Alert procurement that commitment is ready, they can now create PO

### 3.3 Email Template Features

All emails include:
- Professional header with Government Chemist branding
- Request details box with key information
- Color-coded status boxes (green for approved, red for declined)
- Action buttons linking to system URLs
- Footer with copyright and confidentiality notice
- Responsive design for mobile

---

## 4. PROCUREMENT NOTIFICATION WHEN BRANCH HEAD ACCEPTS/DECLINES

### 4.1 Branch Head Approval - Procurement Notification Flow

**When Branch Head APPROVES:**

1. **Immediate Notification Chain**:
   ```
   Branch Head Approves
   ├─ notifyNextApprover() called (if more approvals pending)
   │  └─ Notification sent to NEXT approver in chain
   ├─ notifyRequestFinalized() called (if status is AWARDED/RFQ_LETTER_AVAILABLE/PROCUREMENT_STAGE)
   │  └─ Notification sent to REQUESTOR (not procurement)
   └─ Procurement NOT directly notified at this stage
   ```

2. **Database Changes**:
   - `procurement_requests.status` → Updated to HOD_APPROVED (or DIRECTOR_APPROVED, GC_APPROVED)
   - `request_approvals.status` → Updated to "approved"
   - `request_approvals.approved_by` → Set to approver's user_id
   - Audit log created

3. **Who Gets Notified**:
   - ✅ **Next Approver** (if approval chain has more stages)
   - ✅ **Requestor** (only if request reaches final status like AWARDED)
   - ❌ **Procurement Officer** - NOT notified directly

4. **When Procurement Gets Notified**:
   - Procurement is notified LATER in the workflow:
     - When RFQ letter is generated (move to QUOTE_REVIEW_PENDING)
     - When starting evaluation (move to PROCUREMENT_STAGE)
     - Via: `notifyProcurementOfCommitment()` when finance uploads commitment

### 4.2 Branch Head Rejection - Procurement Notification Flow

**When Branch Head REJECTS/DECLINES:**

1. **Immediate Actions**:
   ```
   Branch Head Declines
   ├─ Status → DECLINED
   ├─ notifyRequestDeclined() called
   │  └─ Email sent to REQUESTOR with decline reason
   ├─ Approval chain CLEARED (deleted from request_approvals table)
   └─ Audit logged
   ```

2. **Procurement Notification**:
   - ❌ **Procurement is NOT notified** of declined requests
   - Only the **requestor** receives decline notification
   - Request is effectively removed from approval pipeline

3. **Email Sent to Requestor**:
   ```
   Subject: "Request Declined: [Request Number]"
   
   Content:
   - DECLINED status (red box)
   - Decline reason provided by approver
   - Request details
   - Approver name
   - Suggestion to revise and resubmit
   ```

### 4.3 Approval Chain Progression

The system does NOT have a multi-level approval chain for regular requests. Each branch has a SINGLE approver:

```
Request Submitted
└─ Branch Approver Reviews (HOD/Director HRM&A/GC)
   ├─ Approves: → AWARDED/RFQ_LETTER_AVAILABLE/PROCUREMENT_STAGE
   │  └─ Requestor notified of approval
   │  └─ Next steps (if RFQ needed): Procurement Officer takes over
   │
   └─ Declines: → DECLINED
      └─ Requestor notified of decline
      └─ Request ends
```

### 4.4 Critical Insight: Procurement Workflow Separation

**The system separates APPROVAL from PROCUREMENT**:

1. **Approval Phase** (Branch Authority):
   - Branch Head reviews and approves financial request
   - System checks funds availability
   - Determines if RFQ needed based on threshold
   - Status → AWARDED or RFQ_LETTER_AVAILABLE

2. **Procurement Phase** (Procurement Officer):
   - Kicks in AFTER final approval
   - Manages RFQ letter generation
   - Evaluates quotes
   - Creates commitment and PO
   - Procurement notified via:
     - `notifyProcurementOfCommitment()` - when commitment ready
     - Workflow status changes (visible in procurement/list.php)

---

## 5. KEY FINDINGS & ISSUES

### 5.1 Current System Behavior

✅ **Working Correctly**:
- Branch heads can approve/decline requests via `/procurement/approve_hod.php`
- Notifications sent to appropriate parties
- Requestor always notified of approval/decline
- Next approver notified when approval stage completes
- Audit trail maintained for all approvals
- Request status properly tracked

⚠️ **Potential Issues**:

1. **Procurement NOT Notified of Approval**
   - Procurement Officer not notified when request is approved
   - Procurement must check system dashboard for new requests
   - Could delay procurement workflow startup

2. **No Multi-Level Approval for Regular Requests**
   - Each branch has only single approver
   - No escalation for high-value requests
   - Over-threshold requests still go to Procurement for evaluation (separate process)

3. **Decline Notifications Limited**
   - Only requestor notified when request declined
   - No notification to any procurement or management team
   - Declined requests just disappear from workflow

4. **Approval Chain Terminology**
   - Documentation refers to "Branch Head" but system uses role-based (HOD, Director, GC)
   - Some confusion in field naming (e.g., `branch_id` on branches table, but NOT on users)

### 5.2 Approval Role Resolution

The system uses a **role-based** approval model, not user-based:

```php
// When getting approver
$approvalRoles = getApprovalChain($requestType, $estimatedValue, $branchId);
// Returns: ['HOD'] or ['Director HRM&A'] or ['Deputy Government Chemist'] or ['Finance Officer']

// Then finds a USER with that role
$stmt = $pdo->prepare("
    SELECT u.email FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE r.name = ? AND u.is_active = 1
    LIMIT 1
");
```

This means:
- Any user with HOD role can approve (taken FIFO if multiple)
- No branch-specific user assignment
- System assumes only one HOD per system

---

## 6. REQUEST TABLES AND WORKFLOW TRACKING

### 6.1 Key Tables

1. **`procurement_requests`** table columns related to approval:
   ```sql
   - request_id (PK)
   - status (DRAFT, SUBMITTED, HOD_APPROVED, AWARDED, DECLINED, etc.)
   - estimated_value (used for threshold determination)
   - branch_id (determines which approver)
   - request_type (REGULAR, PETTY_CASH, REIMBURSEMENT)
   - created_by (requestor user_id)
   - approved_by (approver user_id after first approval)
   - approved_at (timestamp of approval)
   - decline_reason (if declined)
   - funds_available (flag set during approval)
   - finance_reviewed_by (finance officer who verified)
   - finance_reviewed_at (timestamp)
   ```

2. **`request_approvals`** table (tracks multi-stage approvals):
   ```sql
   - id (PK)
   - entity_type ('REQUEST')
   - entity_id (same as request_id)
   - request_id
   - role (HOD, Finance Officer, Director HRM&A, Deputy Government Chemist)
   - stage_order (1, 2, 3, ... for sequencing if multiple)
   - status (pending, approved, rejected)
   - approved_by (user_id)
   - approved_at (timestamp)
   - rejection_reason
   ```

3. **`request_timeline`** table (audit):
   ```sql
   - request_id
   - new_status (what status changed to)
   - description (human readable change)
   - created_at
   ```

---

## 7. SUMMARY TABLE: APPROVAL & NOTIFICATION FLOW

| Step | Actor | Action | Files/Functions | Notifications Sent | Recipients |
|------|-------|--------|------------------|--------------------|------------|
| 1 | Requestor | Submit request | `submit.php` | `notifyApprovalNeeded()` | First approver |
| 2 | Branch Approver | Review & Approve | `approve_hod.php` | `notifyNextApprover()` or `notifyRequestFinalized()` | Next approver OR Requestor |
| 2b | Branch Approver | Decline | `decline.php` | `notifyRequestDeclined()` | Requestor |
| 3 | Finance | Verify funds | `approve_finance.php` | `notifyNextApprover()` or `notifyRequestFinalized()` | Next approver OR Requestor |
| 4 | Procurement | Review quotes | `/rfq/` workflow | `notifyCommitmentAction()` | Requestor + Finance |
| 5 | Finance | Upload commitment | `commitments/upload.php` | `notifyProcurementOfCommitment()` | Procurement |
| 6 | Procurement | Create PO | `/po/` workflow | N/A | Internal only |
| 7 | Accounts | Upload invoice | `payment/add.php` | `notifyRequestFinalized()` | Requestor |

---

## 8. CONFIGURATION & SYSTEM SETTINGS

### 8.1 Changeable System Settings

Located in **`system_config`** table:

```sql
- enable_notifications: 1 (enabled) or 0 (disabled)
  └─ Controls all email notifications system-wide

- direct_procurement_threshold: [amount]
  └─ Determines if request needs RFQ
  └─ Retrieved via: getDirectProcurementThreshold($pdo)
  └─ Default: 500000 JMD (approx)

- usd_to_jmd_rate: [rate]
  └─ Converts USD amounts to JMD for threshold comparison
  └─ Default: 155.00
```

### 8.2 How to Modify Approval Rules

To change approver for a branch:
1. Modify `getApprovalChain()` in [config/workflow.php](config/workflow.php) - line 100+
2. Change branch ID conditions (5, 6, etc.)
3. Update role assignments

Example: To change HRM&A approver from "Director HRM&A" to "HOD":
```php
// Current
if ($branchId === 5) {
    return ['Director HRM&A'];
}

// New
if ($branchId === 5) {
    return ['HOD'];  // Now requires HOD instead
}
```

---

## CONCLUSIONS

1. **Branch heads (HODs, Directors, GCs) approve requests** via `approve_hod.php` and generic `approve.php`

2. **Approvals are BRANCH-BASED** (not user-based):
   - HRM&A branch (5) → Director HRM&A
   - Analytical & Advisory (6) → Deputy Government Chemist
   - All others → HOD

3. **Procurement is NOT notified of approval** - this is a gap:
   - Procurement only notified when commitment is ready
   - Procurement must check dashboard for new RFQ-eligible requests
   - Could cause workflow delays

4. **Notifications sent at each stage**:
   - Request submitted → Approver notified
   - Approval complete → Next approver or requestor notified
   - Request declined → Requestor notified (procurement never notified)
   - Commitment ready → Procurement notified

5. **Approval chain is single-stage per request** (no sequential multi-level approval):
   - Request either approved or declined at first stage
   - Next procurement steps are separate workflow, not sequential approval
