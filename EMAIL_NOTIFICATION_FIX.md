# Email Notification System - Fixed

## Changes Made

### 1. Fixed Approver Detection
**Problem**: The `getBranchHeadEmail()` function was querying for `u.branch_id`, which doesn't exist in the users table.

**Solution**: 
- Created new function `getApproverEmailForBranch()` that uses the branch-based approval rules from `workflow.php`
- Updated `notifyRequestSubmitted()` to use the new function
- Approvers are now correctly identified based on branch and request amount

### 2. Branch-Based Supervisor Assignment

| Branch | Request ≤ 500k | Request > 500k |
|--------|-----------------|----------------|
| **HRM&A** (5) | Director HRM&A | HOD |
| **Analytical & Advisory** (6) | Deputy Government Chemist | HOD |
| **Other Branches** (1,2,3,4,7) | HOD | HOD |

#### Supervisor Lookup Flow:
```
request_submitted
  ├─ Get branch_id, estimated_value, request_type
  ├─ Call getApprovalChain() → returns first approver role
  ├─ Query users table to find user with that role
  ├─ Send email to that user
```

### 3. Notification Triggers

When a request is submitted:

```
1. notifyRequestSubmitted($request_id)
   └─ Sends to: First approver (based on branch rules)
   └─ Subject: "New Procurement Request Pending Approval"
   └─ Action: Review & Approve Request

2. notifyApprovalNeeded($request_id, $stage, $approver_id)
   └─ Sends to: Same first approver (redundant notification)
   └─ Subject: "Action Required: Approve Request"
```

## Email Configuration

The system uses PHPMailer configured in [config/app.php](config/app.php):

```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-app-password');
define('MAIL_FROM', 'noreply@example.com');
define('MAIL_FROM_NAME', 'Government Chemist - PRMS');
```

## Testing Email Delivery

### 1. Check if notifications are enabled:
```sql
SELECT config_value FROM system_config WHERE config_key = 'enable_notifications';
```

### 2. Verify approvers exist:
```sql
-- For HRM&A branch
SELECT u.user_id, u.first_name, u.email, r.name 
FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'Director HRM&A' AND u.is_active = 1;

-- For Analytical & Advisory
SELECT u.user_id, u.first_name, u.email, r.name 
FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'Deputy Government Chemist' AND u.is_active = 1;

-- For other branches
SELECT u.user_id, u.first_name, u.email, r.name 
FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'HOD' AND u.is_active = 1;
```

### 3. Check error logs:
```bash
tail -100 /path/to/php-error.log | grep -i "notification\|mailer\|smtp"
```

### 4. Manual test:
```php
<?php
require_once '/config/notifications.php';
$result = notifyRequestSubmitted(80); // Replace 80 with a test request ID
echo $result ? "Email sent successfully" : "Failed to send email";
?>
```

## File Changes

### [config/notifications.php](config/notifications.php)
- ✅ Added `getApproverEmailForBranch()` - determines approver by branch rules
- ✅ Updated `getBranchHeadEmail()` - removed invalid `u.branch_id` query
- ✅ Updated `notifyRequestSubmitted()` - uses new branch-based logic

### [procurement/submit.php](procurement/submit.php)
- ✅ Already calling both notification functions correctly
- ✅ Properly retrieving approver user_id for notification

## Next Steps

1. **Verify Email Configuration**
   - Check `config/app.php` for correct SMTP credentials
   - Test email credentials with PHPMailer directly

2. **Check Notification Settings**
   - Ensure notifications are enabled in system_config
   - Verify approvers exist with correct roles

3. **Review Error Logs**
   - Check PHP error logs for SMTP connection issues
   - Verify no firewall blocking SMTP port 587

4. **Monitor First Submission**
   - Submit a test request
   - Check if email is delivered to the appropriate supervisor
   - Verify email contains correct request details and approval link

## Redundant Notifications Issue

Currently, two emails might be sent for the same request:
- `notifyRequestSubmitted()` - "New Procurement Request Pending Approval"
- `notifyApprovalNeeded()` - "Action Required: Approve Request"

**Recommendation**: Remove one of these calls in [procurement/submit.php](procurement/submit.php) if duplicate emails are problematic.
