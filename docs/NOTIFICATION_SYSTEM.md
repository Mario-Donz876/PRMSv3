# Email Notification System Documentation

## Overview
The PRMS now includes an automated email notification system that sends alerts at key workflow stages. Administrators can enable or disable notifications globally through the Settings page.

## Features

### 1. Request Submitted Notification
**Recipient:** Branch Head (HOD) of the requesting branch  
**Trigger:** When a procurement request is submitted from Draft to Submitted status  
**Content:** 
- Request number and details
- Requestor information
- Branch name
- Request type and estimated value
- Link to review the request in PRMS

**Purpose:** Informs the branch head that a new request requires their attention for initial approval.

---

### 2. Approval Needed Notification
**Recipient:** User with the required approval role  
**Trigger:** When an approval stage is ready for action  
**Content:**
- Request number and details
- Current approval stage
- Branch and value information
- Direct link to approve/reject the request

**Stages:**
- HOD Approval
- Finance Verification
- Director HRM&A Approval
- Deputy Government Chemist Approval

**Purpose:** Alerts approvers of pending approvals that require their action.

---

### 3. Next Stage Alert Notification
**Recipient:** Next approver in the approval chain  
**Trigger:** When current approval stage is completed and request moves to next stage  
**Content:**
- Request number and stage progression
- Next approval stage and approver role
- Request details for context

**Purpose:** Immediately notifies the next approver that request is ready for their review, reducing processing delays.

---

### 4. Request Finalized Notification
**Recipient:** Requestor (person who created the request)  
**Trigger:** When request reaches final status (AWARDED or DECLINED)  
**Content:**
- Final status with visual indicator
- Request details
- Estimated value and branch information
- Link to view full request details

**Purpose:** Informs requestor of the final decision on their procurement request.

---

## Administration

### Enabling/Disabling Notifications

1. Navigate to **Admin → Settings** (or `/admin/settings.php`)
2. Locate the **Email Notification Settings** section
3. Toggle the **Enable Email Notifications** checkbox
4. Click **Save Settings**

**Status:** The current notification status is displayed on the settings page.

---

## Configuration Requirements

### Mail Server Setup
Email notifications require proper mail server configuration in `config/app.php`:

```php
define('MAIL_HOST', 'smtp.example.com');      // SMTP server hostname
define('MAIL_PORT', 587);                      // SMTP port (typically 587 for TLS)
define('MAIL_USER', 'noreply@example.com');  // SMTP username/email
define('MAIL_PASS', 'password');              // SMTP password
define('MAIL_FROM', 'noreply@example.com');  // From email address
define('MAIL_FROM_NAME', 'PRMS');            // From display name
```

### User Email Requirements
- All users must have valid email addresses in their profiles
- Active users with the required roles will receive notifications
- Invalid or missing email addresses will silently skip notification delivery

---

## Notification Recipients

### Branch Head Notifications
- Receives notifications when requests are submitted from their branch
- Must have "HOD" role assigned

### Approval Role Recipients
- Users with specific approval roles (Finance Officer, Director HRM&A, Deputy Government Chemist) automatically receive approval notifications
- Multiple users can have the same role; they will all receive the notification

### Requestor Notifications
- Original request creator receives final status notifications
- Only active users receive notifications

---

## Technical Implementation

### Files Modified/Created

1. **config/notifications.php** - Core notification functions
   - `notificationsEnabled()` - Check if notifications are enabled
   - `notifyRequestSubmitted()` - Send submission notification
   - `notifyApprovalNeeded()` - Send approval notification
   - `notifyRequestFinalized()` - Send final status notification
   - Helper functions for user lookups

2. **admin/settings.php** - Admin settings interface
   - Toggle notifications on/off
   - Display notification features
   - Show current status

3. **procurement/add.php** - Updated to send notifications
   - Sends notification when request is created

4. **procurement/submit.php** - Updated for submission notifications
   - Sends notification to requestor and first approver

5. **procurement/approve.php** - Updated for approval workflow
   - Sends notification to next approver when stage completes
   - Sends notification when request is rejected

6. **rfq/award.php** - Updated for final status notification
   - Sends notification when request is awarded

7. **migrations/010_add_notification_settings.sql** - Database migration
   - Adds `enable_notifications` setting to system_config table

---

## How Notifications Work

### Request Flow with Notifications

```
1. User Creates Request (Draft)
   ↓
2. User Submits Request → Status: SUBMITTED
   ↓
   📧 Notification to: Branch Head (HOD) + Requestor
   ↓
3. HOD Reviews & Approves
   ↓
   📧 Notification to: Next Approver (Finance Officer)
   ↓
4. Finance Officer Approves
   ↓
   📧 Notification to: Director HRM&A
   ↓
5. Director Approves
   ↓
   📧 Notification to: Deputy Government Chemist
   ↓
6. DGC Approves (or advances to Procurement)
   ↓
7. Procurement Stage → Evaluation → Award
   ↓
   📧 Notification to: Requestor (AWARDED or DECLINED)
```

### Email Content Structure

All emails include:
- Professional HTML formatting
- Government Chemist branding
- Request-specific details
- Actionable links back to PRMS
- Footer with organization information
- Clear action items (what the recipient needs to do)

---

## Troubleshooting

### Notifications Not Being Sent

1. **Check if enabled:** Verify notifications are enabled in Admin Settings
2. **Check mail configuration:** Review MAIL_* constants in config/app.php
3. **Check recipient emails:** Ensure users have valid email addresses
4. **Check user status:** Verify users are marked as active (is_active = 1)
5. **Check error logs:** Look for mail errors in application error logs

### Emails Going to Spam

- Add PRMS to approved sender list
- Check SPF, DKIM, and DMARC records
- Use a recognized email service provider

### User Not Receiving Notifications

- Verify user role is correctly assigned
- Confirm user email address is valid
- Check that user is marked as active
- Ensure user has correct role in request approval chain

---

## Best Practices

1. **Test Configuration:** Send a test email before going live
2. **Monitor Delivery:** Check for failed email deliveries periodically
3. **Maintain User Data:** Keep email addresses up-to-date
4. **Role Assignment:** Ensure users have correct roles assigned
5. **Disable if Unavailable:** Disable notifications if mail service is unavailable

---

## Future Enhancements

Potential improvements to the notification system:
- SMS notifications
- In-app notification history
- Per-user notification preferences
- Notification schedules (digest emails)
- Custom email templates
- Slack/Teams integration

---

## Support

For issues or questions about the notification system:
1. Check this documentation
2. Review application logs for errors
3. Verify mail server configuration
4. Contact system administrator
