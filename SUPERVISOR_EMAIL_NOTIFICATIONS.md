# Supervisor Email Notification Flow

## Approval Chain Summary

### When a Procurement Request is Submitted

**Flow**: Submit Request → Find Approver → Send Email to Approver's Inbox

#### Branch HRM&A (ID 5)
```
Estimated Value ≤ 500,000
  └─ Approver: Director HRM&A
     ├─ Query: Find user with role name "Director HRM&A" and is_active = 1
     ├─ Email Subject: "Action Required: Approve Request [REQ-XXXX]"
     ├─ Email To: director_hrma@email.com
     └─ CTA Button: "Review & Approve Request"

Estimated Value > 500,000
  └─ Approver: HOD
     ├─ Query: Find user with role name "HOD" and is_active = 1
     └─ Email To: hod@email.com
```

#### Branch Analytical & Advisory (ID 6)
```
Estimated Value ≤ 500,000
  └─ Approver: Deputy Government Chemist
     ├─ Query: Find user with role name "Deputy Government Chemist" and is_active = 1
     ├─ Email Subject: "Action Required: Approve Request [REQ-XXXX]"
     ├─ Email To: dgc@email.com
     └─ CTA Button: "Review & Approve Request"

Estimated Value > 500,000
  └─ Approver: HOD
     └─ Email To: hod@email.com
```

#### All Other Branches (1, 2, 3, 4, 7)
```
Estimated Value ≤ or > 500,000
  └─ Approver: HOD
     ├─ Query: Find user with role name "HOD" and is_active = 1
     ├─ Email Subject: "Action Required: Approve Request [REQ-XXXX]"
     ├─ Email To: hod@email.com
     └─ CTA Button: "Review & Approve Request"
```

## Code Flow

### 1. Request Submission ([procurement/submit.php](procurement/submit.php))

```php
$requestType = 'REGULAR';  // From request data
$estimatedValue = 250000;  // From request data
$branchId = 5;             // HRM&A - From branches table join

// Get the approval chain (returns array of roles)
$approvalRoles = getApprovalChain($requestType, $estimatedValue, $branchId);
// Result: ['Director HRM&A']

$firstApprovalRole = $approvalRoles[0];  // 'Director HRM&A'

// Find the user with that role
$stmt = $pdo->prepare('SELECT u.user_id FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE r.name = ? AND u.is_active = 1 LIMIT 1');
$stmt->execute([$firstApprovalRole]);
$approver = $stmt->fetch();  // Returns ['user_id' => 22]

// Send notification
notifyApprovalNeeded($requestId, 'HOD_APPROVED', 22);
```

### 2. Email Sending ([config/notifications.php](config/notifications.php))

```php
function notifyApprovalNeeded(int $requestId, string $stage, int $approverId) {
    // Get request details
    $request = getRequestDetails($requestId);
    
    // Get approver email
    $approverEmail = getUserEmail($approverId);  // 'director.hrma@email.com'
    
    // Send email with template
    sendMail($approverEmail, $subject, $html);
}
```

### 3. Mail Delivery ([config/mailer.php](config/mailer.php))

```php
function sendMail(string $to, string $subject, string $html): bool {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;        // smtp.gmail.com
    $mail->Port = MAIL_PORT;        // 587
    $mail->Username = MAIL_USER;    // Setup in config/app.php
    $mail->Password = MAIL_PASS;    // Setup in config/app.php
    
    $mail->setFrom(MAIL_FROM);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $html;
    
    return $mail->send();
}
```

## Database Queries Used

### Find Approver by Role
```sql
SELECT u.user_id, u.email
FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'Director HRM&A'      -- First approver role
  AND u.is_active = 1
LIMIT 1;
```

### Check Active Supervisors
```sql
-- All Director HRM&A users
SELECT * FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'Director HRM&A' AND u.is_active = 1;

-- All Deputy Government Chemist users
SELECT * FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'Deputy Government Chemist' AND u.is_active = 1;

-- All HOD users
SELECT * FROM users u
INNER JOIN roles r ON u.role_id = r.id
WHERE r.name = 'HOD' AND u.is_active = 1;
```

## Email Template Example

```
TO: director.hrma@email.com
SUBJECT: Action Required: Approve Request REQ-00080

---

Dear Director HRM&A,

A new REGULAR procurement request has been submitted and requires your immediate approval.

Request Number: REQ-00080
Requestor: Jane Smith
Branch: HRM&A
Request Type: REGULAR
Estimated Value: $250,000.00
Approval Stage: HOD Approved

[Review & Approve Request Button]
→ Links to: /procurement/approve.php?id=80

---
```

## Troubleshooting

### Email Not Sending?

1. **Check Configuration**
   ```php
   // config/app.php
   echo MAIL_HOST;      // Should be: smtp.gmail.com
   echo MAIL_PORT;      // Should be: 587
   echo MAIL_USER;      // Should be configured
   ```

2. **Check Notification Setting**
   ```sql
   SELECT * FROM system_config WHERE config_key = 'enable_notifications';
   -- Should return: 1 (enabled)
   ```

3. **Check Approver Exists**
   ```sql
   SELECT * FROM users u
   INNER JOIN roles r ON u.role_id = r.id
   WHERE r.name = 'Director HRM&A' AND u.is_active = 1;
   ```

4. **Review Error Logs**
   ```
   Check PHP error log for:
   - SMTP connection errors
   - "Mailer error:" messages
   - Notification function errors
   ```

5. **Manual Test**
   ```php
   require_once '/config/notifications.php';
   $result = notifyApprovalNeeded(80, 'HOD_APPROVED', 22);
   var_dump($result);  // true = success, false = failed
   ```

## Key Changes Made

✅ Fixed `getBranchHeadEmail()` - Now finds HOD without querying non-existent `u.branch_id`  
✅ Added `getApproverEmailForBranch()` - Uses branch-based approval rules from workflow.php  
✅ Updated `notifyRequestSubmitted()` - Uses new branch-based logic  
✅ Removed duplicate notification - Only sends one email per submission  
✅ Email now goes to the correct supervisor for each branch  
