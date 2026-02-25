<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

// Get PO status summary
$query = "
    SELECT 
        status,
        COUNT(*) as count,
        SUM(po_total) as total_amount
    FROM purchase_orders
    GROUP BY status
    ORDER BY count DESC
";
$statusData = $pdo->query($query)->fetchAll();

// Get detailed PO data
$detailQuery = "
    SELECT 
        po.po_id,
        po.po_number,
        po.po_date,
        po.status,
        po.po_total,
        po.po_type,
        c.commitment_number,
        pr.description as request_desc,
        b.branch_name
    FROM purchase_orders po
    LEFT JOIN commitments c ON po.commitment_id = c.commitment_id
    LEFT JOIN procurement_requests pr ON c.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    ORDER BY po.po_date DESC
";
$details = $pdo->query($detailQuery)->fetchAll();

$totalCount = 0;
$totalAmount = 0;
foreach ($statusData as $s) {
    $totalCount += $s['count'];
    $totalAmount += $s['total_amount'];
}

// Status colors
$statusColors = [
    'Open' => '#0dcaf0',
    'Closed' => '#28a745',
    'Cancelled' => '#dc3545'
];
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">📊 Purchase Order (PO) Status Report</h2>
      <p class="text-muted mb-0">Complete overview of all purchase orders and their current status</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/reports/export_excel.php?report=po_status" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
      </a>
      <a href="/reports/export_pdf.php?report=po_status" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
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
    <div class="col-md-4">
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
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#fce4ec;">
            <span class="fs-4">⏳</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Status Distribution</small>
            <h4 class="fw-bold mb-0 text-dark"><?= count($statusData) ?> Statuses</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Summary Cards -->
  <div class="row g-3 mb-4">
    <?php foreach ($statusData as $s): ?>
    <div class="col-md-<?= count($statusData) <= 2 ? 6 : 4 ?>">
      <div class="card border-0 shadow-sm rounded-4" style="border-left: 4px solid <?= $statusColors[$s['status']] ?? '#6c757d' ?>">
        <div class="card-body">
          <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.75rem;"><?= $s['status'] ?></h6>
          <h5 class="fw-bold mb-1"><?= $s['count'] ?> POs</h5>
          <p class="text-dark fw-semibold mb-0"><?= money($s['total_amount']) ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Detailed Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light p-3 border-0 rounded-top-4">
      <h5 class="mb-0 fw-bold">📄 Detailed PO List</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3" style="width:3%;">#</th>
              <th class="text-dark">PO Number</th>
              <th class="text-dark">PO Date</th>
              <th class="text-dark">Type</th>
              <th class="text-dark">Branch</th>
              <th class="text-dark text-end">Amount</th>
              <th class="text-dark text-center" style="width:12%;">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($details)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No purchase orders found
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($details as $idx => $po): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted" style="font-size:.85rem;"><?= $idx + 1 ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($po['po_number']) ?></td>
              <td><?= date('M d, Y', strtotime($po['po_date'])) ?></td>
              <td>
                <span class="badge <?= $po['po_type'] === 'ORIGINAL' ? 'bg-primary' : 'bg-warning' ?>">
                  <?= $po['po_type'] ?>
                </span>
              </td>
              <td><?= htmlspecialchars($po['branch_name'] ?? 'N/A') ?></td>
              <td class="text-end fw-semibold"><?= money($po['po_total']) ?></td>
              <td class="text-center">
                <span class="badge" style="background-color: <?= $statusColors[$po['status']] ?? '#6c757d' ?>">
                  <?= $po['status'] ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
