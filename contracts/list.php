<?php
$REQUIRE_PERMISSION = 'view_contracts';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/pagination.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';

/* ===============================
   Search & Filter
================================ */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$vendor_id = $_GET['vendor_id'] ?? '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(sc.contract_number LIKE :search OR sc.contract_title LIKE :search OR v.vendor_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status)) {
    $where[] = "sc.status = :status";
    $params[':status'] = $status;
}

if (!empty($vendor_id)) {
    $where[] = "sc.vendor_id = :vendor_id";
    $params[':vendor_id'] = (int)$vendor_id;
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ===============================
   Stats
================================ */
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'EXPIRED' THEN 1 ELSE 0 END) as expired,
        COALESCE(SUM(CASE WHEN status = 'ACTIVE' THEN total_value ELSE 0 END), 0) as total_active_value
    FROM service_contracts
");
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Pagination
extract(getPaginationParams(20));

// Filtered count
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM service_contracts sc
    JOIN vendors v ON sc.vendor_id = v.vendor_id
    $whereSQL
");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();

/* ===============================
   Contracts List
================================ */
$stmt = $pdo->prepare("
    SELECT sc.*, v.vendor_name, b.branch_name,
        (sc.total_value - sc.consumed_value) AS remaining_value
    FROM service_contracts sc
    JOIN vendors v ON sc.vendor_id = v.vendor_id
    JOIN branches b ON sc.branch_id = b.branch_id
    $whereSQL
    ORDER BY sc.created_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';
?>

<style>
    .card { transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
    .card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12); transform: translateY(-2px); }
    .form-control, .form-select { transition: all 0.2s ease; box-shadow: none !important; }
    .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    table tbody tr:hover td { background-color: #f9f9f9 !important; }
    .progress { height: 6px; border-radius: 3px; }
</style>

<div class="mb-5">

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="mb-1" style="font-weight: 700; color: #1a1a1a;">📄 Service Contracts</h2>
        <p class="text-muted mb-0">Manage contractor service agreements and track utilization</p>
    </div>
    <?php if (has_permission('manage_contracts')): ?>
    <a href="/contracts/add.php" class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; padding: 0.5rem 1rem; font-weight: 600;">
        <i class="bi bi-plus-circle me-1"></i>New Contract
    </a>
    <?php endif; ?>
</div>

<!-- KPI SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 small" style="opacity: 0.9;">Total Contracts</p>
                        <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= (int)$stats['total'] ?></h4>
                    </div>
                    <div style="font-size: 2rem; opacity: 0.3;">📄</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 small" style="opacity: 0.9;">Active</p>
                        <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= (int)$stats['active'] ?></h4>
                    </div>
                    <div style="font-size: 2rem; opacity: 0.3;">✅</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 small" style="opacity: 0.9;">Expired</p>
                        <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= (int)$stats['expired'] ?></h4>
                    </div>
                    <div style="font-size: 2rem; opacity: 0.3;">⏰</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 small" style="opacity: 0.9;">Active Value</p>
                        <h4 class="mb-0" style="font-weight: 700; font-size: 1.4rem;"><?= money((float)$stats['total_active_value']) ?></h4>
                    </div>
                    <div style="font-size: 2rem; opacity: 0.3;">💰</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILTERS -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center gap-2 py-2">
            <i class="bi bi-funnel" style="font-size: 1.2rem; color: #667eea;"></i>
            <h6 class="mb-0" style="font-weight: 600; color: #1a1a1a;">Search & Filter</h6>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted" style="font-weight: 600;">Search</label>
                <input type="text" name="search"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Contract #, title, vendor..."
                       class="form-control"
                       style="border-radius: 6px; border: 1px solid #e0e0e0;">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted" style="font-weight: 600;">Status</label>
                <select name="status" class="form-select" style="border-radius: 6px; border: 1px solid #e0e0e0;">
                    <option value="">All Status</option>
                    <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                    <option value="DRAFT" <?= $status === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
                    <option value="EXPIRED" <?= $status === 'EXPIRED' ? 'selected' : '' ?>>Expired</option>
                    <option value="TERMINATED" <?= $status === 'TERMINATED' ? 'selected' : '' ?>>Terminated</option>
                    <option value="SUSPENDED" <?= $status === 'SUSPENDED' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 6px; font-weight: 600;">
                    <i class="bi bi-search me-2"></i>Filter
                </button>
                <a href="/contracts/list.php" class="btn btn-outline-secondary" style="border-radius: 6px; font-weight: 600;">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- CONTRACTS TABLE -->
<div class="card border-0 shadow-sm mb-4">
    <div style="overflow: auto;">
        <table class="table table-hover mb-0" style="border-collapse: collapse;">
            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                <tr>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">Contract</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">Vendor</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">Period</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none; text-align: right;">Value</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">Utilization</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">Status</th>
                    <th style="padding: 1rem; font-weight: 600; color: #1a1a1a; border: none; text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
<?php if (empty($contracts)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5" style="border: none;">
                        <p style="color: #999; font-size: 1rem;">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ddd; display: block; margin-bottom: 0.5rem;"></i>
                            No contracts found
                        </p>
                    </td>
                </tr>
<?php else: ?>
<?php foreach ($contracts as $c):
    $utilPct = $c['total_value'] > 0 ? min(100, ($c['consumed_value'] / $c['total_value']) * 100) : 0;
    $statusColors = [
        'ACTIVE' => '#4caf50', 'DRAFT' => '#ff9800', 'EXPIRED' => '#9e9e9e',
        'TERMINATED' => '#f44336', 'SUSPENDED' => '#ff5722'
    ];
    $statusColor = $statusColors[$c['status']] ?? '#9e9e9e';
?>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 1rem; border: none;">
                        <strong style="color: #667eea;"><?= htmlspecialchars($c['contract_number']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($c['contract_title']) ?></small>
                    </td>
                    <td style="padding: 1rem; border: none;">
                        <small><?= htmlspecialchars($c['vendor_name']) ?></small>
                    </td>
                    <td style="padding: 1rem; border: none;">
                        <small class="text-muted">
                            <?= date('d M Y', strtotime($c['start_date'])) ?><br>
                            → <?= date('d M Y', strtotime($c['end_date'])) ?>
                        </small>
                    </td>
                    <td style="padding: 1rem; border: none; text-align: right;">
                        <strong><?= money((float)$c['total_value']) ?></strong>
                        <br><small class="text-muted">Rem: <?= money((float)$c['remaining_value']) ?></small>
                    </td>
                    <td style="padding: 1rem; border: none; min-width: 120px;">
                        <div class="progress mb-1">
                            <div class="progress-bar <?= $utilPct > 90 ? 'bg-danger' : ($utilPct > 70 ? 'bg-warning' : 'bg-success') ?>"
                                 style="width: <?= number_format($utilPct, 1) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= number_format($utilPct, 1) ?>%</small>
                    </td>
                    <td style="padding: 1rem; border: none;">
                        <span class="badge" style="background-color: <?= $statusColor ?>; color: white; padding: 0.35rem 0.75rem;">
                            <?= htmlspecialchars($c['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; border: none; text-align: center;">
                        <a href="/contracts/view.php?id=<?= $c['contract_id'] ?>"
                           class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 4px; padding: 0.35rem 0.75rem;" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
<?php endforeach; ?>
<?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalRows > 0): ?>
<div class="mt-3">
    <?php renderShowingInfo($page, $perPage, $totalRows); ?>
    <?php renderPagination($totalRows, $perPage, $page, $_GET); ?>
</div>
<?php endif; ?>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
