<?php
$REQUIRE_PERMISSION = 'view_approval_analytics';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

// Query approval analytics data
$stmt = $pdo->prepare("SELECT stage_order, status, COUNT(*) as count FROM approval_queue GROUP BY stage_order, status");
$stmt->execute();
$analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pending approvals
$stmt = $pdo->query("SELECT COUNT(*) FROM request_approvals WHERE status = 'pending'");
$total_pending = $stmt->fetchColumn();

// Map stage_order to readable stage names
$stageNames = [
        1 => 'Submitted',
        2 => 'HOD Review',
        3 => 'Finance Review',
        4 => 'Procurement Stage',
        5 => 'Evaluation Stage',
        6 => 'Committee Recommendation',
        7 => 'GC Approval',
        8 => 'Awarded',
        9 => 'Completed',
];
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-bar-chart-line fs-3 text-info me-2"></i>
                <h4 class="mb-0">Approval Analytics <span class="badge bg-info">Dashboard</span></h4>
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-diagram-3 me-1"></i> Approval Stages Overview</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Stage</th>
                                        <th>Status</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($analytics)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="bi bi-info-circle me-1"></i> No approval data available.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($analytics as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($stageNames[$row['stage_order']] ?? $row['stage_order']) ?></td>
                                                <td>
                                                    <?php
                                                        $status = $row['status'];
                                                        $badgeClass = match($status) {
                                                            'approved' => 'bg-success',
                                                            'pending' => 'bg-warning',
                                                            'rejected' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                                </td>
                                                <td><span class="fw-bold text-primary"><?= (int)$row['count'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-lightning me-1"></i> Quick Actions</strong>
                        </div>
                        <div class="card-body">
                            <a href="/dashboard/management.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-person-badge me-1"></i> Management Dashboard
                            </a>
                            <a href="/dashboard/admin.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="bi bi-tools me-1"></i> Admin Dashboard
                            </a>
                            <button type="button" class="btn btn-warning w-100" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
