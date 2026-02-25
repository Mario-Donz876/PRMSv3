<?php
$REQUIRE_PERMISSION = 'view_financial_reports';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

// Get outstanding commitments
$commitmentQuery = "
    SELECT 
        c.commitment_id,
        c.commitment_number,
        c.commitment_date,
        c.commitment_total,
        c.status,
        pr.description,
        b.branch_name,
        COUNT(DISTINCT po.po_id) as po_count,
        SUM(po.po_total) as total_po_issued
    FROM commitments c
    LEFT JOIN procurement_requests pr ON c.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    LEFT JOIN purchase_orders po ON c.commitment_id = po.commitment_id
    WHERE c.status = 'open'
    GROUP BY c.commitment_id, c.commitment_number, c.commitment_date, c.commitment_total, c.status, pr.description, b.branch_name
    ORDER BY c.commitment_date DESC
";
$commitments = $pdo->query($commitmentQuery)->fetchAll();

// Get outstanding POs
$poQuery = "
    SELECT 
        po.po_id,
        po.po_number,
        po.po_date,
        po.po_total,
        po.status,
        c.commitment_number,
        pr.description,
        b.branch_name,
        SUM(CASE WHEN i.status IN ('Unpaid', 'Partially Paid') THEN i.invoice_amount ELSE 0 END) as outstanding_invoice
    FROM purchase_orders po
    LEFT JOIN commitments c ON po.commitment_id = c.commitment_id
    LEFT JOIN procurement_requests pr ON c.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    LEFT JOIN invoices i ON po.po_id = i.po_id
    WHERE po.status = 'Open'
    GROUP BY po.po_id, po.po_number, po.po_date, po.po_total, po.status, c.commitment_number, pr.description, b.branch_name
    ORDER BY po.po_date DESC
";
$pos = $pdo->query($poQuery)->fetchAll();

// Calculate totals
$totalOpenCommitments = 0;
$totalOpenPOs = 0;
$totalOutstandingInvoices = 0;

foreach ($commitments as $c) {
    $totalOpenCommitments += $c['commitment_total'];
}

foreach ($pos as $p) {
    $totalOpenPOs += $p['po_total'];
    $totalOutstandingInvoices += $p['outstanding_invoice'] ?? 0;
}

// Get summary statistics
$summaryQuery = "
    SELECT 
        COUNT(CASE WHEN c.status = 'open' THEN 1 END) as open_commitments_count,
        COUNT(CASE WHEN c.status = 'closed' THEN 1 END) as closed_commitments_count,
        SUM(CASE WHEN c.status = 'open' THEN c.commitment_total ELSE 0 END) as total_open,
        SUM(c.commitment_total) as total_all
    FROM commitments c
";
$summary = $pdo->query($summaryQuery)->fetch();
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">⏳ Outstanding Commitments & PO Report</h2>
      <p class="text-muted mb-0">Outstanding financial obligations and open purchase orders</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/reports/export_excel.php?report=outstanding" class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
      </a>
      <a href="/reports/export_pdf.php?report=outstanding" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#ffe0e0;">
            <span class="fs-4">📋</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Open Commitments</small>
            <h4 class="fw-bold mb-0 text-dark"><?= count($commitments) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#fce4ec;">
            <span class="fs-4">💰</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Committed</small>
            <h4 class="fw-bold mb-0 text-danger"><?= money($totalOpenCommitments) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#fff3e0;">
            <span class="fs-4">📦</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Open POs</small>
            <h4 class="fw-bold mb-0 text-dark"><?= count($pos) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:#e0f2f1;">
            <span class="fs-4">⚠️</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.75rem;">Total Outstanding</small>
            <h4 class="fw-bold mb-0 text-danger"><?= money($totalOpenPOs) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Open Commitments Table -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-light p-3 border-0">
      <h5 class="mb-0 fw-bold">📝 Open Commitments</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3" style="width:3%;">#</th>
              <th class="text-dark">Commitment #</th>
              <th class="text-dark">Date</th>
              <th class="text-dark">Description</th>
              <th class="text-dark">Branch</th>
              <th class="text-dark text-end">Amount</th>
              <th class="text-dark text-end">POs Issued</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($commitments)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No open commitments found
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($commitments as $idx => $c): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted" style="font-size:.85rem;"><?= $idx + 1 ?></td>
              <td class="fw-semibold">
                <a href="/commitments/view.php?id=<?= $c['commitment_id'] ?>" class="text-decoration-none">
                  <?= htmlspecialchars($c['commitment_number']) ?>
                </a>
              </td>
              <td><?= date('M d, Y', strtotime($c['commitment_date'])) ?></td>
              <td><?= htmlspecialchars(substr($c['description'], 0, 40)) ?>...</td>
              <td><?= htmlspecialchars($c['branch_name'] ?? 'N/A') ?></td>
              <td class="text-end fw-semibold"><?= money($c['commitment_total']) ?></td>
              <td class="text-end"><span class="badge bg-info"><?= $c['po_count'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Open POs Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light p-3 border-0">
      <h5 class="mb-0 fw-bold">📦 Open Purchase Orders</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3" style="width:3%;">#</th>
              <th class="text-dark">PO Number</th>
              <th class="text-dark">Commitment</th>
              <th class="text-dark">Date</th>
              <th class="text-dark">Description</th>
              <th class="text-dark text-end">PO Amount</th>
              <th class="text-dark text-end">Outstanding Invoice</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($pos)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox"></i> No open purchase orders found
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pos as $idx => $p): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted" style="font-size:.85rem;"><?= $idx + 1 ?></td>
              <td class="fw-semibold">
                <a href="/po/view.php?id=<?= $p['po_id'] ?>" class="text-decoration-none">
                  <?= htmlspecialchars($p['po_number']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($p['commitment_number'] ?? 'N/A') ?></td>
              <td><?= date('M d, Y', strtotime($p['po_date'])) ?></td>
              <td><?= htmlspecialchars(substr($p['description'] ?? 'N/A', 0, 30)) ?>...</td>
              <td class="text-end fw-semibold"><?= money($p['po_total']) ?></td>
              <td class="text-end">
                <?php if ($p['outstanding_invoice'] > 0): ?>
                  <span class="badge bg-danger"><?= money($p['outstanding_invoice']) ?></span>
                <?php else: ?>
                  <span class="text-success">✓ Paid</span>
                <?php endif; ?>
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
