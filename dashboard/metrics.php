<?php
if (!isset($pdo)) {
    $REQUIRE_PERMISSION = 'view_finance_dashboard';
    require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
}
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';

$year  = date('Y');
$month = date('m');

/* ================================
   Financial Metrics
================================ */

// This month
$invoicesThisMonth = $pdo->query("
    SELECT COALESCE(SUM(invoice_amount),0)
    FROM invoices
    WHERE YEAR(invoice_date) = $year
      AND MONTH(invoice_date) = $month
")->fetchColumn();

$paymentsThisMonth = $pdo->query("
    SELECT COALESCE(SUM(payment_amount),0)
    FROM payments
    WHERE YEAR(payment_date) = $year
      AND MONTH(payment_date) = $month
")->fetchColumn();

// Last month
$invoicesLastMonth = $pdo->query("
    SELECT COALESCE(SUM(invoice_amount),0)
    FROM invoices
    WHERE invoice_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH,'%Y-%m-01')
      AND invoice_date <  DATE_FORMAT(CURDATE(),'%Y-%m-01')
")->fetchColumn();

$paymentsLastMonth = $pdo->query("
    SELECT COALESCE(SUM(payment_amount),0)
    FROM payments
    WHERE payment_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH,'%Y-%m-01')
      AND payment_date <  DATE_FORMAT(CURDATE(),'%Y-%m-01')
")->fetchColumn();

/* ================================
   Global Indicators
================================ */

$outstanding = $pdo->query("
    SELECT balance FROM vw_outstanding_balance
")->fetchColumn();


$openPOs = $pdo->query("
    SELECT COUNT(*) FROM purchase_orders WHERE status = 'Open'
")->fetchColumn();

$overdueInvoices = $pdo->query("
    SELECT COUNT(*)
    FROM invoices
    WHERE status != 'Paid'
      AND invoice_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetchColumn();

/* ================================
   Trend calculations
================================ */
$invoiceTrend = trend($invoicesThisMonth, $invoicesLastMonth);
$paymentTrend = trend($paymentsThisMonth, $paymentsLastMonth);
//DB not initialized
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';
?>

<div class="container mt-4">
  <div class="d-flex align-items-center mb-4">
    <img src="/logo/cropped-Logo.png" alt="Logo" style="height:36px;width:auto;" class="me-3">
    <div>
      <h3 class="section-title mb-0">💰 Financial Metrics</h3>
      <small class="text-muted">Department of Government Chemist • <?= date('F Y') ?></small>
    </div>
  </div>

  <!-- Global Indicators -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Outstanding Balance</h6>
          <h3 class="fw-bold text-primary"><?= money($outstanding ?: 0) ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Open Purchase Orders</h6>
          <h3 class="fw-bold text-info"><?= (int)$openPOs ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h6 class="text-muted mb-1">Overdue Invoices (30+ days)</h6>
          <h3 class="fw-bold <?= $overdueInvoices > 0 ? 'text-danger' : 'text-success' ?>"><?= (int)$overdueInvoices ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- This Month vs Last Month -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">📄 Invoices</div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0">
            <tr>
              <td class="text-muted">This Month</td>
              <td class="fw-bold text-end"><?= money($invoicesThisMonth) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Last Month</td>
              <td class="text-end"><?= money($invoicesLastMonth) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Trend</td>
              <td class="text-end <?= $invoiceTrend['class'] ?>">
                <?= $invoiceTrend['icon'] ?> <?= $invoiceTrend['percent'] ?>%
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">💵 Payments</div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0">
            <tr>
              <td class="text-muted">This Month</td>
              <td class="fw-bold text-end"><?= money($paymentsThisMonth) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Last Month</td>
              <td class="text-end"><?= money($paymentsLastMonth) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Trend</td>
              <td class="text-end <?= $paymentTrend['class'] ?>">
                <?= $paymentTrend['icon'] ?> <?= $paymentTrend['percent'] ?>%
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mb-4">
    <a href="/dashboard/index.php" class="btn btn-outline-secondary">← Dashboard</a>
    <a href="/dashboard/monthly.php" class="btn btn-outline-primary">📊 Monthly Trends</a>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
