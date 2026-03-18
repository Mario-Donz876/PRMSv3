# Procurement Notification Implementation

## Overview
Implemented immediate notifications to Procurement Officers when branch heads accept or decline request approvals. This ensures procurement can begin work as soon as approvals are completed.

## Problem Solved
**Before:** Procurement Officers had to manually check the dashboard for new approved requests. There was a gap in communication that could delay the procurement process.

**After:** Procurement Officers receive instant email notifications when:
- A branch head approves a request → **`notifyProcurementOfApproval()`**
- A branch head declines a request → **`notifyProcurementOfDecline()`**

## Files Modified

### 1. `/config/notifications.php`
**Added two new functions:**

#### `notifyProcurementOfApproval(int $requestId, string $approvalStatus): bool`
- Triggered when a request is approved by a branch head (HOD, Director, or GC)
- Sends email to all Procurement Officers with:
  - Request number and details
  - Estimated value and branch information
  - Approval status and approver name
  - Link to view and process the request
- Includes status box highlighting "Approved - Ready for Procurement"

#### `notifyProcurementOfDecline(int $requestId, string $declineReason): bool`
- Triggered when a request is declined by a branch head
- Sends email to all Procurement Officers with:
  - Request number and decline reason
  - Request details for reference
  - Status indicating "Declined - No Further Action"
  - Acknowledgment that no further procurement processing is needed

### 2. `/procurement/approve_hod.php`
**Added notification call in the APPROVE section (line ~111):**
```php
/* Notify procurement officers that request has been approved and is ready for processing */
notifyProcurementOfApproval($id, $nextStatus);
```
- Called immediately after `notifyNextApprover()` and before checking for request finalization
- Passes the request ID and the new status after approval

### 3. `/procurement/decline.php`
**Added notification call after requestor notification (line ~100):**
```php
/* Send notification to procurement officers that request has been declined */
notifyProcurementOfDecline($id, $reason);
```
- Called after notifying the requestor
- Passes the request ID and decline reason

## Notification Email Features

### Approval Notification
- **Subject:** "Request Approved - Ready for Procurement: [Request Number]"
- **Status:** "Approved - Ready for Procurement" (green box)
- **Content:** Includes approval status, approver name, approval date, and full request details
- **CTA:** "Review & Process Request" button linking to procurement view

### Decline Notification
- **Subject:** "Request Declined - Not Proceeding: [Request Number]"
- **Status:** "Declined - No Further Action" (red box)
- **Content:** Includes decline reason in highlighted section
- **Purpose:** Informs procurement that this request will not proceed

## Key Features

✅ **Immediate Notification** - Triggered at the moment of approval/decline
✅ **Rich Content** - Includes all relevant request details in the email
✅ **Role-Based** - Only Procurement Officers receive these notifications
✅ **Respects System Config** - Uses `notificationsEnabled()` check to honor global notification settings
✅ **Error Handling** - Includes comprehensive error logging
✅ **Professional Template** - Matches existing email styling with Government Chemist branding

## Testing Recommendations

1. **Test Approval Notification:**
   - Submit a request as a requestor
   - Approve it as a branch head
   - Verify Procurement Officers receive email with "Ready for Procurement"

2. **Test Decline Notification:**
   - Submit a request
   - Decline it as a branch head
   - Verify Procurement Officers receive email with decline reason

3. **Verify Email Content:**
   - Check that all request details appear correctly
   - Verify links are working and point to correct request
   - Confirm formatting displays properly in email clients

4. **System Config:**
   - Test with notifications enabled (default)
   - Test with notifications disabled to ensure they're skipped

## Database Schema Requirements
No database schema changes required. The implementation uses existing:
- `procurement_requests` table
- `users` table (with Procurement Officer role)
- `roles` table
- `branches` table

## Configuration
Notifications respect the `system_config` table setting:
- **Key:** `enable_notifications`
- **Values:** 1 (enabled) or 0 (disabled)
- **Default:** Enabled (true)

## Status Codes Triggering Notifications

Procurement is notified when approvals transition to these statuses:
- `HOD_APPROVED` - Head of Department approval
- `DIRECTOR_APPROVED` - Director approval
- `GC_APPROVED` - Government Chemist approval
- `FUNDS_VERIFIED` - Finance has verified funds
- `PROCUREMENT_STAGE` - Request moved to active procurement
- `AWARDED` - Vendor awarded
- `RFQ_LETTER_AVAILABLE` - RFQ letter ready
- Plus any custom intermediate approval statuses

And when declines occur at `SUBMITTED` status.

## Backwards Compatibility
- ✅ No breaking changes
- ✅ Existing approval workflow unaffected
- ✅ New functions follow established notification patterns
- ✅ All existing notifications continue to work

## Error Logging
All functions include comprehensive error logging:
- "NOTIFY PROCUREMENT: ..." - For approval notifications
- "NOTIFY PROCUREMENT DECLINE: ..." - For decline notifications
- Errors logged to PHP error log with function context

## Future Enhancements

Potential improvements:
- SMS notifications for time-sensitive approvals
- Procurement dashboard widget showing newly approved requests
- Automatic assignment of procurement tasks upon approval
- Integration with procurement planning tools
- Notification templates customizable by admin
