# SOP → Code Checklist

This checklist maps SOP requirements to code locations to help reviewers and AI agents validate compliance.

- SOP §1 — Role definitions
  - `dashboard/admin.php` (Admin)
  - `dashboard/gc.php` (Deputy Government Chemist)
  - `dashboard/committee.php` (Procurement Committee)
  - `dashboard/procurement.php` (Procurement Officer)
  - `dashboard/evaluation.php` (Evaluation Committee)
  - `dashboard/finance.php` (Finance)
  - `dashboard/hod.php` (HOD)
  - `dashboard/management.php` (Management)
  - `dashboard/viewer.php` (Read-only)

- SOP §2 — Permission enforcement (every protected page)
  - `config/page_guard.php` (enforcement logic)
  - Pattern to check: `$REQUIRE_PERMISSION = 'permission_name'; require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';`

- SOP §3 — Workflow stages and transitions
  - `workflow/data.php` (stage definitions, including commitment approval sub-stages 13a/13b)
  - Business logic touching stage changes: `procurement/*.php`, `rfq/*`, `po/*`, `commitments/*`
  - Commitment approval chain: `commitments/add.php` and `commitments/add_supplementary.php` (HOD → Finance insertion)
  - Commitment approval processing: `commitments/approve.php` (stage validation + status update)

- SOP §4 — Audit logging and retention
  - `audit/` endpoints (`audit/list.php`, `audit/export_pdf.php`, etc.)
  - Widget: `dashboard/widgets/recent_activity.php`

- SOP §5 — RFQ / PO / Invoice templates and generation
  - `rfq/` (generate_rtf.php, generate_loa.php)
  - `po/` (print_po.php, generate/print helpers)
  - `invoice/` (print_invoice.php)

- SOP §6 — External approvals & compliance checks
  - `dashboard/gc.php` and `workflow/data.php` (reflect external approvals like PPC/Cabinet)

- SOP §7 — Reporting & views
  - `reports/` (branch_summary.php, branch_outstanding.php)
  - DB views referenced in code (e.g., `vw_outstanding_balance`)

- SOP §8 — Security, sessions & authentication
  - `config/auth.php`, `auth/login.php`, `auth/logout.php`, `config/page_guard.php`

- SOP §9 — CI / change control
  - `.github/workflows/php.yml` — dependency checks
  - If changing dependencies or approval logic, require human review and checklist sign-off before merging.

## Reviewer Tasks
- Verify any PR that touches the above files includes a checklist entry linking back to SOP clauses.
- Require explicit mention in PR description when approval flows, audit logging, or authentication are modified.


*Use this checklist when preparing changes that affect business workflows or compliance.*
