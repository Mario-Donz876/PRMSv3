<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

// Get filter from query
$groupBy = isset($_GET['group']) ? $_GET['group'] : 'monthly';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get years for dropdown
$yearsQuery = "
    SELECT DISTINCT YEAR(p.payment_date) as year
    FROM payments p
    WHERE p.payment_date IS NOT NULL
    ORDER BY year DESC
";
$years = $pdo->query($yearsQuery)->fetchAll();

// Build query based on group
switch ($groupBy) {
    case 'quarterly':
        $selectClause = "CONCAT(YEAR(p.payment_date), '-Q', QUARTER(p.payment_date)) as period";
        $groupClause = "CONCAT(YEAR(p.payment_date), '-Q', QUARTER(p.payment_date))";
        $periodLabel = "Quarter";
        break;
    case 'yearly':
        $selectClause = "YEAR(p.payment_date) as period";
        $groupClause = "YEAR(p.payment_date)";
        $periodLabel = "Year";
        break;
    case 'by_object':
        $selectClause = "pr.description as period";
        $groupClause = "pr.description";
        $periodLabel = "Object/Description";
        break;
    case 'monthly':
    default:
        $selectClause = "DATE_FORMAT(p.payment_date, '%Y-%m') as period";
        $groupClause = "DATE_FORMAT(p.payment_date, '%Y-%m')";
        $periodLabel = "Month";
        break;
}

$whereClause = "WHERE p.payment_date IS NOT NULL";
if ($groupBy !== 'yearly' && $groupBy !== 'by_object') {
    $whereClause .= " AND YEAR(p.payment_date) = $year";
}

$query = "
    SELECT 
        $selectClause,
        COUNT(*) as payment_count,
        SUM(p.payment_amount) as total_paid
    FROM payments p
    LEFT JOIN invoices i ON p.invoice_id = i.invoice_id
    LEFT JOIN purchase_orders po ON i.po_id = po.po_id
    LEFT JOIN commitments c ON po.commitment_id = c.commitment_id
    LEFT JOIN procurement_requests pr ON c.request_id = pr.request_id
    $whereClause
    GROUP BY $groupClause
    ORDER BY period ASC
";

$data = $pdo->query($query)->fetchAll();

// Calculate totals
$totalCount = 0;
$totalPaid = 0;
foreach ($data as $r) {
    $totalCount += $r['payment_count'];
    $totalPaid += $r['total_paid'];
}

// Get payment trends for chart
$trendQuery = "
    SELECT 
        DATE_FORMAT(p.payment_date, '%Y-%m') as month,
        SUM(p.payment_amount) as total
    FROM payments p
    WHERE YEAR(p.payment_date) = $year
    GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
    ORDER BY month ASC
";
$trends = $pdo->query($trendQuery)->fetchAll();
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">💳 Amounts Paid Report</h2>
      <p class="text-muted mb-0">Payment tracking and analysis by period, object, or time frame</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/reports/export_excel.php?report=amounts_paid&group=<?= $groupBy ?>&year=<?= $year ?>" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
      </a>
      <a href="/reports/export_pdf.php?report=amounts_paid&group=<?= $groupBy ?>&year=<?= $year ?>" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Group By</label>
          <select class="form-select" id="groupSelect" onchange="changeGrouping(this.value)">
            <option value="monthly" <?= $groupBy === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="quarterly" <?= $groupBy === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
            <option value="yearly" <?= $groupBy === 'yearly' ? 'selected' : '' ?>>Yearly</option>
            <option value="by_object" <?= $groupBy === 'by_object' ? 'selected' : '' ?>>By Object/Description</option>
          </select>
        </div>
        <?php if (in_array($groupBy, ['monthly', 'quarterly'])): ?>
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
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e3f2fd;">
            <span class="fs-4">💳</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Payments</small>
            <h4 class="fw-bold mb-0 text-dark"><?= $totalCount ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e8f5e9;">
            <span class="fs-4">💰</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Paid</small>
            <h4 class="fw-bold mb-0 text-dark"><?= money($totalPaid) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Trend Chart (for monthly view) -->
  <?php if ($groupBy === 'monthly' && !empty($trends)): ?>
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">💹 Payment Trends - <?= $year ?></h5>
      <canvas id="trendChart"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <!-- Data Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light p-3 border-0">
      <h5 class="mb-0 fw-bold">📊 Amounts Paid by <?= $periodLabel ?></h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-dark">
              <th class="text-light ps-3">Period</th>
              <th class="text-light text-end" style="width:15%;">Payment Count</th>
              <th class="text-light text-end" style="width:20%;">Total Paid</th>
              <th class="text-light text-end" style="width:15%;">% of Total</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No payment data found
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $row): ?>
            <tr>
              <td class="ps-3 fw-semibold"><?= htmlspecialchars($row['period']) ?></td>
              <td class="text-end"><?= $row['payment_count'] ?></td>
              <td class="text-end fw-semibold"><?= money($row['total_paid']) ?></td>
              <td class="text-end"><?= number_format(($row['total_paid'] / $totalPaid) * 100, 2) ?>%</td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function changeGrouping(value) {
    const url = new URL(window.location);
    url.searchParams.set('group', value);
    if (value === 'by_object' || value === 'yearly') {
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
    const group = document.getElementById('groupSelect').value;
    const yearSelect = document.getElementById('yearSelect');
    const year = yearSelect ? yearSelect.value : '<?= $year ?>';
    
    let url = '/reports/amounts_paid_report.php?group=' + group;
    if (group !== 'by_object' && group !== 'yearly') {
        url += '&year=' + year;
    }
    window.location = url;
}

<?php if ($groupBy === 'monthly' && !empty($trends)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const chartData = {
        labels: [<?php echo implode(',', array_map(fn($d) => '"'.$d['month'].'"', $trends)); ?>],
        datasets: [{
            label: 'Amount Paid',
            data: [<?php echo implode(',', array_column($trends, 'total')); ?>],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#007bff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: ' + new Intl.NumberFormat('en-US', {style: 'currency', currency: 'USD'}).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('en-US', {style: 'currency', currency: 'USD', minimumFractionDigits: 0}).format(value);
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
