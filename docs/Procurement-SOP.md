# Procurement Standard Operating Procedure (SOP)

This file is a repository-friendly summary of the official Procurement Standard Operating Procedure that governs PRMS. Add the official SOP PDF or full text to `docs/Procurement-SOP.pdf` or `docs/Procurement-SOP-full.md` for authoritative reference.

## Purpose
Ensure the PRMS application enforces the SOP for procurement request submission, evaluation, recommendation, approval, and post-approval financial steps.

## High-level stages (used across codebase)
- SUBMITTED — Request created by user and submitted for technical review (HOD).
- EVALUATION_STAGE — RFQ issuance and committee evaluation.
- COMMITTEE_RECOMMENDED — Recommendation forwarded for GC final approval (may require external compliance checks).
- APPROVED — Commitment/PO/Invoice lifecycle and payment.

### Commitment Approval Sub-stages (SOP Step 13)
After a commitment is created by Finance or Procurement, it must be approved in two stages before a PO can be issued:
1. **HOD Approval** (stage_order=1) — Head of Department verifies the commitment aligns with the approved request.
2. **Finance Approval** (stage_order=2) — Finance Officer confirms funds availability and budget compliance.

Both stages must be completed (status = 'approved' in `request_approvals` where `entity_type = 'COMMITMENT'`) before the commitment is closed and a Purchase Order can be created. This applies to both primary and supplementary commitments.

See `commitments/add.php`, `commitments/add_supplementary.php` (approval chain creation) and `commitments/approve.php` (approval processing).

## Key responsibilities
- HOD: technical review and recommendation; commitment approval (stage 1). See `dashboard/hod.php`, `commitments/approve.php`.
- Evaluation/Committee: RFQ evaluation, scoring and recommendations. See `dashboard/evaluation.php`, `rfq/` files.
- Procurement Officer: request management, RFQ creation, and commitment creation. See `dashboard/procurement.php`, `procurement/*.php`, `commitments/add.php`.
- Deputy Government Chemist (DGC): final approval and external compliance checks. See `dashboard/gc.php`.
- Finance: commitment approval (stage 2), invoice and payment workflows. See `dashboard/finance.php`, `invoice/`, `commitments/approve.php`.
- Admin/Management: oversight dashboards and user/permissions. See `dashboard/admin.php`, `dashboard/management.php`.

## Requirements the codebase must enforce
- Role-based permission checks (permission guard) must be present and unchanged at the top of protected pages.
- All approval actions must create audit records (see `audit/` and `dashboard/widgets/recent_activity.php`).
- RFQ/PO/Invoice templates must include SOP-mandated fields and disclaimers (check `rfq/`, `po/`, `invoice/`).
- External approvals (PPC/Cabinet) must be reflected in workflow state transitions and configuration (`workflow/data.php`, `config/workflow.php` if present).
- Reporting windows and retention should follow SOP — use DB views for consistent reporting (e.g., `vw_outstanding_balance`).

## How to add the official SOP to repo
1. Place the SOP PDF at `docs/Procurement-SOP.pdf` (binary) or `docs/Procurement-SOP-full.md` (text).
2. Update this summary with section references and add a short changelog entry.

## Further steps
- Create a short `SOP_CHECKLIST.md` that maps SOP clauses to code locations for reviewers.
- Ensure CI checks or PR templates require a sign-off when changes touch approval flows, audit logging, or authentication.

*This file is a summary only — always consult the official SOP document before modifying business logic.*
