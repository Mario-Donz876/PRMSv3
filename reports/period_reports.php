<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

// Get period filter from query or default to monthly
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get years for dropdown
$yearsQuery = "
    SELECT DISTINCT YEAR(po_date) as year
    FROM purchase_orders
    WHERE po_date IS NOT NULL
    ORDER BY year DESC
";
$years = $pdo->query($yearsQuery)->fetchAll();

// Build query based on period
switch ($period) {
    case 'quarterly':
        $dateFormat = "%Y-Q%q";
        $periodLabel = "Quarter";
        $poGroupBy = "CONCAT(YEAR(po.po_date), '-Q', QUARTER(po.po_date))";
        $commitmentGroupBy = "CONCAT(YEAR(c.commitment_date), '-Q', QUARTER(c.commitment_date))";
        break;
    case 'yearly':
        $dateFormat = "%Y";
        $periodLabel = "Year";
        $poGroupBy = "YEAR(po.po_date)";
        $commitmentGroupBy = "YEAR(c.commitment_date)";
        break;
    case 'monthly':
    default:
        $dateFormat = "%Y-%m";
        $periodLabel = "Month";
        $poGroupBy = "DATE_FORMAT(po.po_date, '%Y-%m')";
        $commitmentGroupBy = "DATE_FORMAT(c.commitment_date, '%Y-%m')";
        break;
}

$query = "
    SELECT 
        $poGroupBy as period,
        COUNT(*) as po_count,
        SUM(po.po_total) as total_amount,
        COUNT(CASE WHEN po.status = 'Open' THEN 1 END) as open_count,
        COUNT(CASE WHEN po.status = 'Closed' THEN 1 END) as closed_count,
        COUNT(CASE WHEN po.status = 'Cancelled' THEN 1 END) as cancelled_count
    FROM purchase_orders po
    WHERE po.po_date IS NOT NULL
    " . ($period === 'monthly' ? "AND YEAR(po.po_date) = $year" : "") . "
    GROUP BY $poGroupBy
    ORDER BY period ASC
";

$data = $pdo->query($query)->fetchAll();

// Calculate totals
$totalCount = 0;
$totalAmount = 0;
$totalOpen = 0;
$totalClosed = 0;
$totalCancelled = 0;

foreach ($data as $r) {
    $totalCount += $r['po_count'];
    $totalAmount += $r['total_amount'];
    $totalOpen += $r['open_count'];
    $totalClosed += $r['closed_count'];
    $totalCancelled += $r['cancelled_count'];
}

// Get commitment data by period
$commitmentQuery = "
    SELECT 
        $commitmentGroupBy as period,
        COUNT(*) as commitment_count,
        SUM(c.commitment_total) as total_committed,
        COUNT(CASE WHEN c.status = 'open' THEN 1 END) as open_commitments
    FROM commitments c
    WHERE c.commitment_date IS NOT NULL
    " . ($period === 'monthly' ? "AND YEAR(c.commitment_date) = $year" : "") . "
    GROUP BY $commitmentGroupBy
    ORDER BY period ASC
";

$commitmentData = $pdo->query($commitmentQuery)->fetchAll();
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">📅 Period Reports</h2>
      <p class="text-muted mb-0">Procurement and purchase order activity grouped by time period</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/reports/export_excel.php?report=period&period=<?= $period ?>&year=<?= $year ?>" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
      </a>
      <a href="/reports/export_pdf.php?report=period&period=<?= $period ?>&year=<?= $year ?>" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Period Type</label>
          <select class="form-select" id="periodSelect" onchange="changePeriod(this.value)">
            <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="quarterly" <?= $period === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
            <option value="yearly" <?= $period === 'yearly' ? 'selected' : '' ?>>Yearly</option>
          </select>
        </div>
        <?php if ($period === 'monthly'): ?>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Year</label>
          <select class="form-select" id="yearSelect" onchange="changeYear(this.value)">
            <?php foreach ($years as $y): ?>
            <option value="<?= $y['year'] ?>" <?= $y['year'] === $year ? 'selected' : '' ?>><?= $y['year'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-md-4">
          <label class="form-label fw-semibold">&nbsp;</label>
          <button class="btn btn-primary w-100" onclick="applyFilters()">Apply Filters</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e3f2fd;">
            <span class="fs-4">📋</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total POs</small>
            <h4 class="fw-bold mb-0 text-dark"><?= $totalCount ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e8f5e9;">
            <span class="fs-4">💰</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Amount</small>
            <h4 class="fw-bold mb-0 text-dark"><?= money($totalAmount) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#cceef5;">
            <span class="fs-4">🟦</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Open POs</small>
            <h4 class="fw-bold mb-0 text-dark"><?= $totalOpen ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#d1e7dd;">
            <span class="fs-4">✓</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Closed POs</small>
            <h4 class="fw-bold mb-0 text-dark"><?= $totalClosed ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Purchase Orders by Period -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-light p-3 border-0">
      <h5 class="mb-0 fw-bold">🛒 Purchase Orders by <?= ucfirst($periodLabel) ?></h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3"><?= $periodLabel ?></th>
              <th class="text-dark text-end">Count</th>
              <th class="text-dark text-end">Total Amount</th>
              <th class="text-dark text-center">Open</th>
              <th class="text-dark text-center">Closed</th>
              <th class="text-dark text-center">Cancelled</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No data found for selected period
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $row): ?>
            <tr>
              <td class="ps-3 fw-semibold"><?= htmlspecialchars($row['period']) ?></td>
              <td class="text-end"><?= $row['po_count'] ?></td>
              <td class="text-end fw-semibold"><?= money($row['total_amount']) ?></td>
              <td class="text-center"><span class="badge bg-info"><?= $row['open_count'] ?></span></td>
              <td class="text-center"><span class="badge bg-success"><?= $row['closed_count'] ?></span></td>
              <td class="text-center"><span class="badge bg-danger"><?= $row['cancelled_count'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Commitments by Period -->
  <?php if (!empty($commitmentData)): ?>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light p-3 border-0">
      <h5 class="mb-0 fw-bold">📝 Commitments by <?= ucfirst($periodLabel) ?></h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3"><?= $periodLabel ?></th>
              <th class="text-dark text-end">Count</th>
              <th class="text-dark text-end">Total Committed</th>
              <th class="text-dark text-end">Open</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($commitmentData as $row): ?>
            <tr>
              <td class="ps-3 fw-semibold"><?= htmlspecialchars($row['period']) ?></td>
              <td class="text-end"><?= $row['commitment_count'] ?></td>
              <td class="text-end fw-semibold"><?= money($row['total_committed']) ?></td>
              <td class="text-end"><span class="badge bg-warning text-dark"><?= $row['open_commitments'] ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
function changePeriod(value) {
    const url = new URL(window.location);
    url.searchParams.set('period', value);
    if (value !== 'monthly') {
        url.searchParams.delete('year');
    }
    window.location = url.toString();
}

function changeYear(value) {
    const url = new URL(window.location);
    url.searchParams.set('year', value);
    window.location = url.toString();
}

function applyFilters() {
    const period = document.getElementById('periodSelect').value;
    const yearSelect = document.getElementById('yearSelect');
    const year = yearSelect ? yearSelect.value : '<?= $year ?>';
    
    let url = '/reports/period_reports.php?period=' + period;
    if (period === 'monthly') {
        url += '&year=' + year;
    }
    window.location = url;
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
