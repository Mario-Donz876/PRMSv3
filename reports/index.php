<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";
?>

<div class="container-fluid mt-4">

  <!-- Page Header -->
  <div class="mb-4">
    <h2 class="fw-bold mb-2">📊 Reports Center</h2>
    <p class="text-muted">Comprehensive reporting and analytics for procurement and financial data</p>
  </div>

  <!-- Procurement Reports Section -->
  <div class="mb-5">
    <h5 class="fw-bold mb-3 pb-2 border-bottom">📋 Procurement Reports</h5>
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <a href="/reports/procurement_by_status.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease; cursor: pointer;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e3f2fd;">
                  <span class="fs-5">📊</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Procurement by Status</h6>
              <p class="card-text text-muted small">View procurement requests grouped by their current status (Draft, Submitted, Approved, etc.)</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/procurement_by_type.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e8f5e9;">
                  <span class="fs-5">📁</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Procurement by Type</h6>
              <p class="card-text text-muted small">Analysis of procurement methods including single source, restricted bidding, and competitive bidding</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/procurement_by_branch.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff3e0;">
                  <span class="fs-5">🏢</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">By Department/Branch</h6>
              <p class="card-text text-muted small">Procurement requests and spending analysis by department or branch</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/procurement_by_supplier.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fce4ec;">
                  <span class="fs-5">🤝</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">By Supplier</h6>
              <p class="card-text text-muted small">Supplier performance metrics and order summary</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Purchase Order Reports Section -->
  <div class="mb-5">
    <h5 class="fw-bold mb-3 pb-2 border-bottom">📦 Purchase Order & Period Reports</h5>
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <a href="/reports/po_status_report.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e0f2f1;">
                  <span class="fs-5">📋</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">PO Status Report</h6>
              <p class="card-text text-muted small">Complete overview of all purchase orders and their current status (Open, Closed, Cancelled)</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/period_reports.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#f3e5f5;">
                  <span class="fs-5">📅</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Period Reports</h6>
              <p class="card-text text-muted small">Procurement and PO activity grouped by month, quarter, or year</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Financial Reports Section -->
  <div class="mb-5">
    <h5 class="fw-bold mb-3 pb-2 border-bottom">💰 Financial Reports</h5>
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <a href="/reports/amounts_paid_report.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#c8e6c9;">
                  <span class="fs-5">💳</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Amounts Paid Report</h6>
              <p class="card-text text-muted small">Payment tracking and analysis by period, object/description, month, quarter, or year</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/outstanding_commitments_po.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#ffccbc;">
                  <span class="fs-5">⏳</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Outstanding Commitments & PO</h6>
              <p class="card-text text-muted small">Summary of open commitments and purchase orders with outstanding amounts</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Branch Reports Section -->
  <div class="mb-5">
    <h5 class="fw-bold mb-3 pb-2 border-bottom">🏢 Branch Financial Reports</h5>
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <a href="/reports/branch_summary.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#b3e5fc;">
                  <span class="fs-5">💰</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Branch Summary</h6>
              <p class="card-text text-muted small">Financial overview of invoiced, paid, and outstanding amounts per branch</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-lg-4">
        <a href="/reports/branch_outstanding.php" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#ffe0b2;">
                  <span class="fs-5">⚠️</span>
                </div>
              </div>
              <h6 class="card-title fw-bold">Outstanding Balances</h6>
              <p class="card-text text-muted small">Branch-wise analysis of outstanding balances and aging reports</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Export Section -->
  <div class="row g-3 mb-5">
    <div class="col-md-6 col-lg-4">
      <a href="/reports/export_pdf.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#ffebee;">
                <span class="fs-5">📄</span>
              </div>
            </div>
            <h6 class="card-title fw-bold">Export to PDF</h6>
            <p class="card-text text-muted small">Export any report to PDF format for sharing and archiving</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-4">
      <a href="/reports/export_excel.php" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="transition: all 0.3s ease;">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e8f5e9;">
                <span class="fs-5">📊</span>
              </div>
            </div>
            <h6 class="card-title fw-bold">Export to Excel</h6>
            <p class="card-text text-muted small">Export report data to Excel for further analysis and manipulation</p>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light p-3 border-0">
      <h6 class="mb-0 fw-bold">📈 Quick Stats</h6>
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-3">
          <div class="mb-3">
            <h4 class="text-primary fw-bold">6</h4>
            <p class="text-muted small mb-0">Procurement Reports</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <h4 class="text-success fw-bold">3</h4>
            <p class="text-muted small mb-0">Financial Reports</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <h4 class="text-info fw-bold">2</h4>
            <p class="text-muted small mb-0">Branch Reports</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <h4 class="text-warning fw-bold">2</h4>
            <p class="text-muted small mb-0">Export Formats</p>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
  .hover-lift:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
  }
</style>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
