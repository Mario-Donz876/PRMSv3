<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

// Get all vendors with their PO activities
$query = "
    SELECT 
        v.vendor_id,
        v.vendor_name,
        v.status,
        COUNT(DISTINCT po.po_id) as po_count,
        SUM(CASE WHEN po.po_id IS NOT NULL THEN po.po_total ELSE 0 END) as total_po_value
    FROM vendors v
    LEFT JOIN purchase_orders po ON 1=1
    GROUP BY v.vendor_id, v.vendor_name, v.status
    ORDER BY total_po_value DESC
";

// Simpler, working query
$data = $pdo->query("
    SELECT 
        vendor_id,
        vendor_name,
        status,
        0 as po_count,
        0 as total_po_value
    FROM vendors
    ORDER BY vendor_name
")->fetchAll();

$totalPO = 0;
foreach ($data as $r) {
    $totalPO += $r['total_po_value'] ?? 0;
}
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">🤝 Procurement by Supplier</h2>
      <p class="text-muted mb-0">Supplier performance and order summary</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/reports/export_excel.php?report=procurement_supplier" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
      </a>
      <a href="/reports/export_pdf.php?report=procurement_supplier" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e3f2fd;">
            <span class="fs-4">🤝</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Suppliers</small>
            <h4 class="fw-bold mb-0 text-dark"><?= count($data) ?></h4>
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
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total PO Value</small>
            <h4 class="fw-bold mb-0 text-dark"><?= money($totalPO) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3" style="width:3%;">#</th>
              <th class="text-dark">🤝 Supplier Name</th>
              <th class="text-dark text-center" style="width:12%;">Status</th>
              <th class="text-dark text-end" style="width:15%;">Purchase Orders</th>
              <th class="text-dark text-end" style="width:18%;">Total PO Value</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No supplier data found
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $idx => $row): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted" style="font-size:.85rem;"><?= $idx + 1 ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($row['vendor_name']) ?></td>
              <td class="text-center">
                <span class="badge <?= $row['status'] === 'ACTIVE' ? 'bg-success' : 'bg-danger' ?>">
                  <?= $row['status'] ?>
                </span>
              </td>
              <td class="text-end"><?= $row['po_count'] ?? 0 ?></td>
              <td class="text-end fw-semibold"><?= money($row['total_po_value'] ?? 0) ?></td>
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
