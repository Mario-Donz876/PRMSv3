# Pending Actions Dashboard Feature

## Overview
A new **Pending Actions Widget** has been added to all role-based dashboards to eliminate the need for users to navigate away from their dashboard to create POs, approve requests, or take workflow actions.

## What Changed

### New Widget: `dashboard/widgets/pending_actions.php`
- **Purpose**: Displays all pending request approvals and workflow actions for the current user's role
- **Scope**: Works for any role by analyzing the user's role against workflow stage owners
- **Auto-Role Mapping**: Automatically determines which requests need action based on the user's role

### Dashboards Updated
The following dashboards now include the Pending Actions widget:

1. ✅ **Procurement Dashboard** (`dashboard/procurement.php`)
   - Procurement Officers see: RFQ creation, PO creation, quote reviews
   
2. ✅ **Finance Dashboard** (`dashboard/finance.php`)
   - Finance Officers see: Commitment approvals, funds verification items
   
3. ✅ **HOD Dashboard** (`dashboard/hod.php`)
   - HOD/Branch Heads see: Request approvals, quote reviews, PO approvals
   
4. ✅ **Director HRM&A Dashboard** (`dashboard/director_hrma.php`)
   - Director HRM&A sees: HRM&A branch request approvals
   
5. ✅ **Director Procurement Dashboard** (`dashboard/director_procurement.php`)
   - Directors see: Branch-specific approvals and workflow actions
   
6. ✅ **Government Chemist Dashboard** (`dashboard/gc.php`)
   - GC sees: Strategic approvals, RFQ recommendations
   
7. ✅ **Admin Dashboard** (`dashboard/admin.php`)
   - Admins see: All pending items for visibility
   
8. ✅ **Evaluation Dashboard** (`dashboard/evaluation.php`)
   - Evaluators see: RFQ evaluation items

## Features

### What Users See

The widget displays two sections:

#### 1. **Approvals Awaiting Your Action**
Shows all request approvals pending for the user's role:
- Request number and type
- Requestor name
- Amount and currency
- Submission date
- Direct link to review/approve

Example statuses shown:
- SUBMITTED (pending HOD approval)
- SUBMITTED (pending Director approval)

#### 2. **Workflow Actions Required**
Shows all requests at workflow stages where the user's role is responsible:
- Request number and type
- Current status (RFQ_LETTER_AVAILABLE, QUOTE_REVIEW_PENDING, etc.)
- Recommended next action
- Amount and currency
- Direct links to take action

Example workflow stages:
- **RFQ_LETTER_AVAILABLE** → "Generate RFQ Letters"
- **QUOTE_REVIEW_PENDING** → "Review Quotes"
- **QUOTE_APPROVED** → "Create Commitment"
- **COMMITMENT_APPROVED** → "Create Purchase Order"
- **PO_PENDING** → "Generate PO from GFMS"

## Use Cases

### For Procurement Officers
**Before**: Navigate to Request → Then to RFQ → Then to PO creation
**Now**: See all pending RFQ and PO creation tasks directly on dashboard

### For Finance Officers
**Before**: Navigate to Approval Queue to find commitments needing approval
**Now**: See pending commitment approvals with funds verification on dashboard

### For HOD/Branch Heads
**Before**: Check Approval Queue for pending requests and quote reviews
**Now**: See all approval and quote review items on main dashboard

### For Government Chemist
**Before**: Navigate to Approval Queue for GC-level approvals
**Now**: See all pending recommendations and approvals on dashboard

## How It Works

### Role-Based Filtering
The widget uses the `stageOwner()` function from `config/workflow.php` to determine which workflow stages apply to each role:

```php
$allWorkflowStatuses = [
    'PROCUREMENT_STAGE', 'EVALUATION_STAGE',
    'RFQ_LETTER_AVAILABLE', 'QUOTE_REVIEW_PENDING', 'QUOTE_APPROVED',
    'COMMITTEE_RECOMMENDED', 'GC_APPROVED',
    'COMMITMENT_APPROVED', 'COMMITMENT_DECLINED'
];

// Filter to only statuses this role owns
$myStatuses = [];
foreach ($allWorkflowStatuses as $st) {
    if (in_array($userRole, stageOwner($st))) {
        $myStatuses[] = $st;
    }
}
```

### Branch Filtering
Special handling for branch-specific roles:
- **Director HRM&A**: Only sees HRM&A branch (branch_id = 5) items
- **Deputy Government Chemist**: Only sees Deputy GC branch (branch_id = 6) items

### Database Queries

#### Query 1: Request Approvals
```sql
SELECT requests FROM request_approvals 
WHERE role = current_role 
  AND status = 'pending' 
  AND request NOT IN (DECLINED, COMPLETED, AWARDED)
```

#### Query 2: Workflow Actions
```sql
SELECT requests FROM procurement_requests 
WHERE status IN (stages_owned_by_role)
  AND branch_id matches user's branch (if applicable)
```

## Integration

### Included In
All main role-based dashboards automatically include this widget by having:
```php
<?php include $_SERVER['DOCUMENT_ROOT'].'/dashboard/widgets/pending_actions.php'; ?>
```

### Dependencies
- `config/workflow.php` - For `stageOwner()` function and workflow definitions
- `config/db.php` - For database connection ($pdo)
- Bootstrap Icons - For visual indicators (bi-* classes)

### No New Permissions Required
The widget uses existing role-based permissions from:
- `request_approvals` table (existing records)
- `procurement_requests.status` (existing workflow states)

## Data Flow

```
User accesses dashboard
         ↓
Dashboard loads pending_actions.php
         ↓
Widget determines user's role
         ↓
Query pending_approvals matching role
         ↓
Query workflow_actions matching role
         ↓
Display both sections with action buttons
         ↓
User can click to take action immediately
```

## Workflow Status Reference

| Status | Role Responsible | Next Action |
|--------|------------------|-------------|
| PROCUREMENT_STAGE | Procurement Officer | Create RFQ |
| RFQ_LETTER_AVAILABLE | Requestor/Procurement | Send RFQ letters to vendors |
| QUOTE_REVIEW_PENDING | Requestor/HOD | Review vendor quotes |
| QUOTE_APPROVED | Finance Officer | Create commitment |
| COMMITMENT_APPROVED | Procurement Officer | Create Purchase Order |
| COMMITMENT_DECLINED | Requestor | Revise & resubmit |
| EVALUATION_STAGE | Committee Member | Evaluate RFQ bids |
| COMMITTEE_RECOMMENDED | Government Chemist | Final recommendation |
| GC_APPROVED | Procurement Officer | Award contract |

## Benefits

✅ **Reduced Navigation**: Users don't need to go to Request → Approval Queue to find what they need to do
✅ **Faster Action**: Direct links to take action from dashboard
✅ **Role-Aware**: Each role sees only relevant pending items
✅ **Comprehensive**: Covers both approvals and workflow actions
✅ **Consistent UX**: Same widget on all dashboards
✅ **No Permission Changes**: Uses existing permissions and roles

## Testing

### Test Case 1: Procurement Officer
1. Login as Procurement Officer
2. Go to Procurement Dashboard
3. Verify "Pending Actions" section shows:
   - Any pending RFQ creation items (PROCUREMENT_STAGE)
   - Any pending PO creation items (COMMITMENT_APPROVED)
   - Quote review items (if applicable)

### Test Case 2: Finance Officer
1. Login as Finance Officer
2. Go to Finance Dashboard
3. Verify "Pending Actions" section shows:
   - Any pending commitment approvals (COMMITMENT_APPROVED)
   - Any pending commitment declines

### Test Case 3: HOD
1. Login as HOD
2. Go to HOD Dashboard
3. Verify "Pending Actions" section shows:
   - Any pending request approvals (SUBMITTED)
   - Any quote reviews (QUOTE_REVIEW_PENDING)

## Troubleshooting

### Widget Not Showing
- Verify user has a valid role assigned in `users` table
- Verify `role_name` session variable is set
- Check browser console for JavaScript errors
- Verify database connection is working

### Pending Actions Not Appearing
- Check `request_approvals` table for pending records with matching role
- Verify request status is not in (DECLINED, COMPLETED, AWARDED)
- Check `procurement_requests` table for items in relevant workflow statuses
- Verify user's role matches `stageOwner()` mapping for those statuses

### Branch Filtering Issues
- For Director HRM&A: Verify user is assigned to branch 5
- For Deputy GC: Verify user is assigned to branch 6
- Check branch_id values in procurement_requests for other users

## Future Enhancements

Potential improvements for future versions:
- [ ] Sorting by priority/amount
- [ ] Filtering by request type
- [ ] Notification badges when new items arrive
- [ ] Bulk action capability
- [ ] Export pending items
- [ ] Custom timeout reminders for old pending items
- [ ] Integration with email notifications

---
**Last Updated**: February 24, 2026
**File**: `dashboard/widgets/pending_actions.php`
**Related Files**: 
- `config/workflow.php`
- `config/db.php`
- All dashboard/*.php files
