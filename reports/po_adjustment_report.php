<?php
$REQUIRE_PERMISSION = 'view_po_adjustments';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";

$stmt = $pdo->query("
    SELECT
        p.po_id,
        p.po_total AS original_amount,
        COALESCE(SUM(a.po_total), 0) AS adjustment_total,
        (p.po_total + COALESCE(SUM(a.po_total), 0)) AS approved_total
    FROM purchase_orders p
    LEFT JOIN purchase_orders a
        ON a.parent_po_id = p.po_id
       AND a.po_type = 'ADJUSTMENT'
       AND a.status = 'APPROVED'
    WHERE p.po_type = 'ORIGINAL'
    GROUP BY p.po_id
    ORDER BY p.po_id DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOriginal   = 0;
$totalAdjustment = 0;
$totalApproved   = 0;
$adjustedCount   = 0;
foreach ($rows as $r) {
    $totalOriginal   += $r['original_amount'];
    $totalAdjustment += $r['adjustment_total'];
    $totalApproved   += $r['approved_total'];
    if ($r['adjustment_total'] != 0) $adjustedCount++;
}

require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";
?>

<div class="container-fluid mt-2">

  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">📦 PO Adjustment Report</h2>
      <p class="text-muted mb-0">Overview of original purchase orders and approved adjustments</p>
    </div>
    <div>
      <a href="/po/list.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list-ul me-1"></i> All Purchase Orders
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e3f2fd;">
            <span class="fs-4">📋</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.7rem;">Total POs</small>
            <h4 class="fw-bold mb-0"><?= count($rows) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e8f5e9;">
            <span class="fs-4">💵</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.7rem;">Original Value</small>
            <h4 class="fw-bold mb-0 text-dark"><?= money($totalOriginal) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff3e0;">
            <span class="fs-4">🔄</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.7rem;">Adjustments</small>
            <h4 class="fw-bold mb-0 <?= $totalAdjustment >= 0 ? 'text-warning' : 'text-danger' ?>"><?= money($totalAdjustment) ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#ede7f6;">
            <span class="fs-4">✅</span>
          </div>
          <div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:.7rem;">Approved Total</small>
            <h4 class="fw-bold mb-0 text-success"><?= money($totalApproved) ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
      <span class="fw-semibold text-dark"><i class="bi bi-table me-1"></i> Adjustment Details</span>
      <span class="badge bg-secondary rounded-pill"><?= $adjustedCount ?> of <?= count($rows) ?> POs adjusted</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th class="text-dark ps-3" style="width:5%;">#</th>
              <th class="text-dark">📦 PO ID</th>
              <th class="text-dark text-end">💵 Original Amount</th>
              <th class="text-dark text-end">🔄 Adjustments</th>
              <th class="text-dark text-end">✅ Approved Total</th>
              <th class="text-dark" style="width:18%;">Variance</th>
              <th class="text-dark text-center" style="width:8%;">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <span style="font-size:2em;">📭</span><br>
                No purchase orders found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $i => $r):
              $hasAdj = ($r['adjustment_total'] != 0);
              $pctChange = $r['original_amount'] > 0 ? round(($r['adjustment_total'] / $r['original_amount']) * 100, 1) : 0;
              $pctAbs = min(abs($pctChange), 100);
              $barColor = $r['adjustment_total'] > 0 ? 'bg-warning' : ($r['adjustment_total'] < 0 ? 'bg-danger' : 'bg-secondary');
            ?>
            <tr>
              <td class="ps-3 text-muted fw-bold"><?= $i + 1 ?></td>
              <td>
                <span class="fw-semibold">PO-<?= (int)$r['po_id'] ?></span>
                <?php if ($hasAdj): ?>
                  <span class="badge bg-warning bg-opacity-25 text-warning ms-1" style="font-size:.7rem;">Adjusted</span>
                <?php endif; ?>
              </td>
              <td class="text-end"><?= money($r['original_amount']) ?></td>
              <td class="text-end fw-semibold <?= $r['adjustment_total'] > 0 ? 'text-warning' : ($r['adjustment_total'] < 0 ? 'text-danger' : 'text-muted') ?>">
                <?= $r['adjustment_total'] > 0 ? '+' : '' ?><?= money($r['adjustment_total']) ?>
              </td>
              <td class="text-end fw-bold text-success"><?= money($r['approved_total']) ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress rounded-pill flex-grow-1" style="height:8px;">
                    <div class="progress-bar <?= $barColor ?> rounded-pill"
                         role="progressbar"
                         style="width:<?= $pctAbs ?>%"
                         aria-valuenow="<?= $pctAbs ?>">
                    </div>
                  </div>
                  <small class="text-muted" style="min-width:42px;font-size:.75rem;">
                    <?= $pctChange > 0 ? '+' : '' ?><?= $pctChange ?>%
                  </small>
                </div>
              </td>
              <td class="text-center">
                <a href="/po/view.php?id=<?= (int)$r['po_id'] ?>" class="btn btn-sm btn-outline-info" title="View PO">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
          <?php if (!empty($rows)): ?>
          <tfoot>
            <tr class="table-light fw-bold">
              <td class="ps-3" colspan="2">Grand Total (<?= count($rows) ?> POs)</td>
              <td class="text-end"><?= money($totalOriginal) ?></td>
              <td class="text-end <?= $totalAdjustment >= 0 ? 'text-warning' : 'text-danger' ?>">
                <?= $totalAdjustment > 0 ? '+' : '' ?><?= money($totalAdjustment) ?>
              </td>
              <td class="text-end text-success"><?= money($totalApproved) ?></td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
