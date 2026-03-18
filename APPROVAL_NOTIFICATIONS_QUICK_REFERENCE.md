# Quick Reference: Approval & Notification System

## Fast Lookup

### Where Can I...?

| Task | File | Line | Function |
|------|------|------|----------|
| **Approve a request** | `/procurement/approve_hod.php` | - | Branch head approves |
| **Decline a request** | `/procurement/decline.php` | - | Decline at SUBMITTED stage |
| **Reject at approval stage** | `/procurement/approve_hod.php` | POST handler | Reject within approval |
| **Check if I can approve** | `/config/workflow.php` | 284 | `canApproveStage()` |
| **Find who should approve** | `/config/workflow.php` | 100 | `getApprovalChain()` |
| **Send notification** | `/config/notifications.php` | - | Various `notify*()` functions |
| **Enable/disable all notifications** | Database: `system_config` | - | `enable_notifications = 1/0` |
| **View all approvals for request** | Database: `request_approvals` | - | WHERE `request_id = ?` |
| **Check request timeline** | Database: `request_timeline` | - | WHERE `request_id = ?` |

---

## Approval Rules (from `/config/workflow.php`)

### Single-Approver Model Per Branch

```
Request Type: REGULAR
├─ Branch 5 (HRM&A)           → Director HRM&A
├─ Branch 6 (Analytical)      → Deputy Government Chemist
└─ All Other Branches         → HOD

Request Type: PETTY_CASH       → Finance Officer
Request Type: REIMBURSEMENT    → Finance Officer
```

---

## Notification Functions Reference

### When Request Submitted (auto-triggered)
```php
notifyApprovalNeeded($requestId, $stage, $approverId)
// Sends to: First approver in chain
// When: Request transitions DRAFT → SUBMITTED
// Location: submit.php:90
```

### When Request Approved (auto-triggered)
```php
notifyNextApprover($requestId, $completedStage)
// Sends to: Next approver role (if any pending approvals)
// When: After stage approval
// Location: Called from approve_hod.php:114
```

### When Request Finalized (auto-triggered)
```php
notifyRequestFinalized($requestId, $finalStatus)
// Sends to: Original requestor
// When: Request reaches final status (AWARDED, RFQ_LETTER_AVAILABLE, etc.)
// Location: Called from approve_hod.php:116
```

### When Request Declined (auto-triggered)
```php
notifyRequestDeclined($requestId, $requestorId, $declineReason)
// Sends to: Requestor only
// When: Request declined/rejected
// Location: decline.php:102
```

### Manual Notifications
```php
// Send to all Finance Officers
notifyFinanceForDirectApproval($requestId, $requestType)

// Send to Procurement when commitment ready
notifyProcurementOfCommitment($requestId, $commitmentNumber)

// Send commitment lifecycle events
notifyCommitmentAction($requestId, $commitmentNumber, $action, $details)
```

---

## Status Flow Diagram

```
DRAFT
  ↓ (submit)
SUBMITTED
  ├─ APPROVED → HOD_APPROVED (most branches)
  │           → DIRECTOR_APPROVED (HRM&A)
  │           → GC_APPROVED (Analytical)
  │             ↓
  │           (check threshold)
  │             ├─ Under Threshold → RFQ_LETTER_AVAILABLE
  │             ├─ Over Threshold → PROCUREMENT_STAGE
  │             └─ Direct (Petty Cash/Reimburse) → AWARDED
  │
  └─ DECLINED (end of flow)
```

---

## Testing Notifications

### Disable All Notifications
```sql
UPDATE system_config 
SET config_value = 0 
WHERE config_key = 'enable_notifications';
```

### Re-enable Notifications
```sql
UPDATE system_config 
SET config_value = 1 
WHERE config_key = 'enable_notifications';
```

### Check Notification Status
```sql
SELECT config_value FROM system_config 
WHERE config_key = 'enable_notifications';
```

### View Approval Chain for Request
```sql
SELECT * FROM request_approvals 
WHERE request_id = [ID]
ORDER BY stage_order;
```

### Find Who's Supposed to Approve
```sql
SELECT ra.role, u.full_name, u.email
FROM request_approvals ra
LEFT JOIN users u ON u.role_id IN (
    SELECT id FROM roles WHERE name = ra.role
)
WHERE ra.request_id = [ID]
  AND ra.status = 'pending'
ORDER BY ra.stage_order;
```

---

## Common Issues & Solutions

### Issue: Procurement Never Notified
**Problem**: Branch head approves but procurement doesn't know
**Why**: System separates approval from procurement phases
**Solution**: Add `notifyProcurementOfRFQReadiness()` after approval if status is RFQ_LETTER_AVAILABLE

**Current Code Location**: 
- `/procurement/approve_hod.php` - Add after line 116

**Suggested Addition**:
```php
// After notifyRequestFinalized
if ($nextStatus === 'RFQ_LETTER_AVAILABLE') {
    notifyProcurementOfRFQReadiness($id);
}
```

### Issue: Can't Find Who Can Approve
**Solution**: Check table:
```sql
SELECT r.name as approver_role, u.email, u.full_name
FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE u.is_active = 1
ORDER BY r.name;
```

### Issue: Notifications Not Sending
**Check**:
1. System notifications enabled: `SELECT config_value FROM system_config WHERE config_key = 'enable_notifications'` (should be 1)
2. Email function working: Check SMTP in `/config/mailer.php`
3. Permissible permissions set on user: Check `permissions_assigned` table

---

## Code Locations for Reference

| Component | File | Purpose |
|-----------|------|---------|
| Approval Logic | `/config/workflow.php` | Define approval chains and rules |
| HOD Approval UI | `/procurement/approve_hod.php` | Form for branch head to approve/reject |
| Generic Approver | `/procurement/approve.php` | Generic approval handler |
| Finance Approval | `/procurement/approve_finance.php` | Finance officer action |
| Decline Handler | `/procurement/decline.php` | Submit-stage decline |
| Notifications | `/config/notifications.php` | All email sending logic |
| Email Template | `/config/mailer.php` | SMTP configuration |
| Submit Handler | `/procurement/submit.php` | Creates approval chain on submission |

---

## Database Relationships

```
users (user_id, role_id, email)
  ↑
  └─ roles (id, name)

procurement_requests (request_id, branch_id, status, created_by, approved_by)
  ↑
  ├─ request_approvals (id, request_id, role, status, approved_by, stage_order)
  │
  ├─ request_timeline (request_id, new_status, description)
  │
  └─ branches (branch_id, branch_name)

system_config (config_key, config_value)
  └─ enable_notifications, direct_procurement_threshold, usd_to_jmd_rate
```

---

## KEY INSIGHT

The PRMSv3 system uses a **role-based, not user-based** approval model:
- Users are assigned roles (HOD, Finance Officer, etc.)
- Approval rules define which ROLE can approve
- System finds first active user with that role
- If no user with required role exists, approval cannot proceed

This means:
✅ Easy for role transitions (reassign users, not reconfigure rules)
⚠️ Assumes only ONE active person per role at a time
⚠️ No department-specific approvers (only branch-based)
