# Approval Chain Configuration Analysis

## Issue Identified

**Current Problem**: The `getFallbackApprovers()` function allows HOD to approve under-threshold requests even when branch supervisors are the primary approver. This violates the requirement that under-threshold requests should ONLY require branch supervisor approval, with HOD as fallback only for over-threshold.

**Requirements**:
- ✅ Under Threshold (≤500k): Only branch supervisors approve
  - HRM&A Branch (id=5) → Director HRM&A (ONLY)
  - Analytical & Advisory Branch (id=6) → Deputy Government Chemist (ONLY)
  - Other Branches → HOD (ONLY)
  - ❌ HOD should NOT be allowed to approve for branches 5 & 6
  
- ✅ Over Threshold (>500k): HOD required for all branches
  - Primary: HOD
  - Fallback: None (HOD is already top level)
  
- ✅ Petty Cash: HOD only
  - No branches, HOD required

## Current Implementation

### ✅ CORRECT: `getApprovalChain()` function
```php
function getApprovalChain(string $requestType, float $estimatedValue, ?int $branchId = null): array {
    if ($requestType === 'PETTY_CASH') {
        return ['HOD'];  // ✅ Correct: Petty cash needs HOD only
    }

    if ($estimatedValue <= 500000) {
        if ($branchId === 6) {
            return ['Deputy Government Chemist'];  // ✅ Correct: Only Deputy GC
        } elseif ($branchId === 5) {
            return ['Director HRM&A'];  // ✅ Correct: Only Director HRM&A
        } else {
            return ['HOD'];  // ✅ Correct: HOD for other branches
        }
    }

    return ['HOD'];  // ✅ Correct: All over-threshold need HOD
}
```

### ❌ INCORRECT: `getFallbackApprovers()` function
```php
function getFallbackApprovers(string $primaryRole, float $estimatedValue): array {
    $approvers = [$primaryRole];
    
    if ($estimatedValue > 500000) {
        // ✅ CORRECT: Over threshold can have HOD fallback
        if ($primaryRole !== 'HOD') {
            $approvers[] = 'HOD';
        }
    }
    
    // ❌ INCORRECT: This allows HOD for under-threshold with branch supervisors
    if ($estimatedValue <= 500000 && $primaryRole !== 'HOD') {
        $approvers[] = 'HOD';
    }
    
    return $approvers;
}
```

## Problematic Scenarios (Current vs Required)

| Scenario | Branch | Amount | Primary Approver | Current Allowable | Should Allow | Issue |
|----------|--------|--------|------------------|------------------|--------------|-------|
| Stationery | HRM&A (5) | 100k | Director HRM&A | Director HRM&A, **HOD** | Director HRM&A | ❌ HOD allowed |
| Lab Equipment | Analytical (6) | 250k | Deputy GC | Deputy GC, **HOD** | Deputy GC | ❌ HOD allowed |
| Vehicle | Other | 150k | HOD | HOD | HOD | ✅ Correct |
| Large Tender | Any | 700k | HOD | HOD | HOD | ✅ Correct |
| Petty Cash | Various | 3k | HOD | HOD | HOD | ✅ Correct |

## Impact

1. **Director HRM&A can approve for other branches**: When reviewing under-threshold requests from non-HRM&A branches, Director HRM&A can approve them (wrong role)
2. **Deputy GC can approve for other branches**: When reviewing under-threshold requests, Deputy GC can approve anything (wrong role)
3. **HOD bypasses department authority**: HOD approved requests from supervised branches without going through proper branch supervisor

## Fix Required

The `getFallbackApprovers()` function needs to be updated to:
- Allow HOD fallback ONLY for over-threshold requests
- NO fallback for under-threshold branch supervisor requests
- When primary is HOD (under-threshold other branches), no fallback needed

## Approval Chain Workflow (Post-Fix)

### Under Threshold - HRM&A Branch
- Approval Chain: `['Director HRM&A']`
- Who can approve: Director HRM&A (only)
- HOD cannot approve unless they ARE the Director HRM&A role

### Under Threshold - Analytical Branch  
- Approval Chain: `['Deputy Government Chemist']`
- Who can approve: Deputy Government Chemist (only)
- HOD cannot approve unless they ARE the Deputy GC role

### Under Threshold - Other Branches
- Approval Chain: `['HOD']`
- Who can approve: HOD (only)
- No fallback needed

### Over Threshold - All Branches
- Approval Chain: `['HOD']`
- Who can approve: HOD (only)
- No fallback (HOD is the ultimate approver)

### Petty Cash
- Approval Chain: `['HOD']`
- Who can approve: HOD (only)
- No fallback (HOD only option)
