<?php
$REQUIRE_PERMISSION = 'view_audit_logs';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

/* ===============================
   Validate input
================================ */
$table = trim($_GET['table'] ?? '');
$id    = $_GET['id'] ?? null;

$allowedTables = [
    'procurement_requests',
    'commitments',
    'purchase_orders',
    'invoices',
    'payments',
    'users',
    'POLICY'
];



$id = (int)($_GET['id'] ?? 0);

if ($table !== 'POLICY' && $id <= 0) {
    pop("Invalid audit request.", "/dashboard", 1500);
    exit;
}




/* ===============================
   Fetch audit trail (FROM VIEW)
================================ */
$sql = "
    SELECT 
        v.action,
        v.notes,
        v.change_date,
        v.changed_by
    FROM audit_log v
    WHERE v.table_name = :table
      AND v.record_id = :id
    ORDER BY v.change_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':table' => $table,
    ':id'    => (int)$id
]);

$auditLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-journal-text fs-2 text-primary me-2"></i>
                <div>
                    <h3 class="section-title mb-1">🧾 Audit Trail</h3>
                    <small class="text-muted">Tracking changes for <strong><?= htmlspecialchars($table) ?></strong> (Record ID: <?= (int)$id ?>)</small>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Notes</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$auditLogs): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <span class="d-block py-4">
                                        <i class="bi bi-emoji-frown fs-2 mb-2"></i><br>
                                        No audit history found.
                                    </span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr class="audit-row"
                                        data-table="<?= htmlspecialchars($table) ?>"
                                        data-id="<?= (int)($log['record_id'] ?? 0) ?>">
                                    <td><?= htmlspecialchars($log['change_date']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= auditIcon($log['action']) ?>
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($log['notes'])) ?></td>
                                    <td><?= htmlspecialchars($log['changed_by'] ?? 'System') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <a href="/audit/export_pdf.php?table=<?= urlencode($table) ?>&id=<?= (int)$id ?>"
                     class="btn btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>
</div>


<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
