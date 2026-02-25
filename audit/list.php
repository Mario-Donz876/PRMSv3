<?php
$REQUIRE_PERMISSION = 'view_audit_logs';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT']."/includes/pagination.php";

/* ===============================
   Filters
================================ */
$where = [];
$params = [];

if (!empty($_GET['table'])) {
    $where[] = "a.table_name = :table";
    $params[':table'] = $_GET['table'];
}

if (!empty($_GET['action'])) {
    $where[] = "a.action = :action";
    $params[':action'] = $_GET['action'];
}

if (!empty($_GET['user'])) {
    $where[] = "a.changed_by = :user";
    $params[':user'] = (int)$_GET['user'];
}

if (!empty($_GET['from'])) {
    $where[] = "a.change_date >= :from";
    $params[':from'] = $_GET['from'];
}

if (!empty($_GET['to'])) {
    $where[] = "a.change_date <= :to";
    $params[':to'] = $_GET['to'];
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ===============================
   Pagination
================================ */
extract(getPaginationParams(20));

/* ===============================
   Data Query
================================ */
$sql = "
    SELECT 
        a.audit_id,
        a.table_name,
        a.record_id,
        a.action,
        a.notes,
        a.change_date,
        a.changed_by
    FROM audit_log a
    $whereSQL
    ORDER BY a.change_date DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   Count Query
================================ */
$countSql = "
    SELECT COUNT(*)
    FROM audit_log a
    $whereSQL
";


$countStmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) {
    $countStmt->bindValue($k, $v);
}
$countStmt->execute();
$totalRows = (int)$countStmt->fetchColumn();

require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";

/* ===============================
   Inline Styles for Modern UI
================================ */
?>
<style>
    .section-title {
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1.5rem;
    }
    
    .card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .form-control, .form-select {
        transition: all 0.2s ease;
        box-shadow: none !important;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    }
    
    .btn {
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    table tbody tr:hover td {
        background-color: #f9f9f9 !important;
    }
    
    code {
        font-family: 'Monaco', 'Courier New', monospace;
        font-size: 0.85rem;
    }
</style>
<?php

/* Calculate additional stats */
if (!empty($where)) {
    $filteredCount = $totalRows;
} else {
    $allCountStmt = $pdo->query("SELECT COUNT(*) FROM audit_log");
    $allCount = (int)$allCountStmt->fetchColumn();
    $filteredCount = $totalRows;
}
?>

<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; color: #1a1a1a;">📜 System Audit Register</h2>
            <p class="text-muted mb-0">Track all system changes and user activities</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/audit/export_csv.php<?php echo !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>" class="btn btn-sm btn-outline-success" title="Export as CSV">
                <i class="bi bi-download"></i> CSV
            </a>
            <a href="/audit/export_pdf.php<?php echo !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Export as PDF">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 small" style="opacity: 0.9;">Total Records</p>
                            <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= number_format($totalRows) ?></h4>
                        </div>
                        <div style="font-size: 2rem; opacity: 0.3;">📊</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 small" style="opacity: 0.9;">On Page</p>
                            <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= count($logs) ?></h4>
                        </div>
                        <div style="font-size: 2rem; opacity: 0.3;">📋</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 small" style="opacity: 0.9;">Total Pages</p>
                            <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= ceil($totalRows / $perPage) ?? 1 ?></h4>
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
                            <p class="mb-1 small" style="opacity: 0.9;">Per Page</p>
                            <h4 class="mb-0" style="font-weight: 700; font-size: 2rem;"><?= $perPage ?></h4>
                        </div>
                        <div style="font-size: 2rem; opacity: 0.3;">⚙️</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center gap-2 py-2">
            <i class="bi bi-funnel" style="font-size: 1.2rem; color: #667eea;"></i>
            <h6 class="mb-0" style="font-weight: 600; color: #1a1a1a;">Filter Audit Logs</h6>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted" style="font-weight: 600;">Table Name</label>
                <input type="text" name="table"
                       value="<?= htmlspecialchars($_GET['table'] ?? '') ?>"
                       placeholder="e.g., users"
                       class="form-control"
                       style="border-radius: 6px; border: 1px solid #e0e0e0;">
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted" style="font-weight: 600;">Action</label>
                <input type="text" name="action"
                       value="<?= htmlspecialchars($_GET['action'] ?? '') ?>"
                       placeholder="e.g., CREATE"
                       class="form-control"
                       style="border-radius: 6px; border: 1px solid #e0e0e0;">
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted" style="font-weight: 600;">From Date</label>
                <input type="date" name="from"
                       value="<?= $_GET['from'] ?? '' ?>"
                       class="form-control"
                       style="border-radius: 6px; border: 1px solid #e0e0e0;">
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted" style="font-weight: 600;">To Date</label>
                <input type="date" name="to"
                       value="<?= $_GET['to'] ?? '' ?>"
                       class="form-control"
                       style="border-radius: 6px; border: 1px solid #e0e0e0;">
            </div>

            <div class="col-md-4 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 6px; font-weight: 600;">
                    <i class="bi bi-search me-2"></i>Apply Filters
                </button>
                <a href="/audit/list.php" class="btn btn-outline-secondary" style="border-radius: 6px; font-weight: 600;">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info border-0 mb-4" style="border-radius: 6px; background-color: #e3f2fd; color: #1565c0;">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-info-circle" style="font-size: 1.2rem;"></i>
        <small><strong>Showing</strong> <?= ($offset + 1) ?> - <?= min($offset + $perPage, $totalRows) ?> of <?= number_format($totalRows) ?> records (Page <?= $page ?>/<?= max(1, ceil($totalRows / $perPage)) ?>)</small>
    </div>
</div>


<div class="card border-0 shadow-sm">
    <div style="overflow: auto;">
        <table class="table table-hover mb-0" style="border-collapse: collapse;">
            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                <tr>
                    <th style="width: 15%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">📅 Timestamp</th>
                    <th style="width: 12%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">📋 Table</th>
                    <th style="width: 10%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">🔑 ID</th>
                    <th style="width: 15%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">⚡ Action</th>
                    <th style="width: 35%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">📝 Details</th>
                    <th style="width: 13%; padding: 1rem; font-weight: 600; color: #1a1a1a; border: none;">👤 User</th>
                </tr>
            </thead>
            <tbody>

<?php if (empty($logs)): ?>
<tr>
    <td colspan="6" class="text-center py-5">
        <div style="color: #999;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
            <h5 style="color: #1a1a1a; font-weight: 600; margin-bottom: 0.5rem;">No Records Found</h5>
            <small>Try adjusting your filters or date range</small>
        </div>
    </td>
</tr>
<?php else: ?>

<?php $currentDate = null; ?>

<?php foreach ($logs as $log): ?>

<?php
$logDate = date('Y-m-d', strtotime($log['change_date']));
if ($currentDate !== $logDate):
    $currentDate = $logDate;
?>
<tr style="background-color: #f8f9fa; border-top: 2px solid #e0e0e0; border-bottom: 1px solid #e0e0e0;">
    <td colspan="6" style="padding: 1rem; font-weight: 600; color: #1a1a1a;">
        📆 <?= date('l, d M Y', strtotime($log['change_date'])) ?>
    </td>
</tr>
<?php endif; ?>

<?php
$action = strtoupper($log['action']);
$rowClass = '';
$rowBgColor = 'white';

if (str_contains($action, 'DELETE')) {
    $rowBgColor = '#ffebee';
} elseif (str_contains($action, 'UPDATE')) {
    $rowBgColor = '#e3f2fd';
} elseif (str_contains($action, 'CREATE')) {
    $rowBgColor = '#e8f5e9';
}
?>

<tr style="background-color: <?= $rowBgColor ?>; border-bottom: 1px solid #e0e0e0; transition: background-color 0.2s; cursor: pointer;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='<?= $rowBgColor ?>'">
    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <div style="font-size: 0.85rem; color: #666;">
            <div><?= date('g:i:s A', strtotime($log['change_date'])) ?></div>
            <code style="font-size: 0.75rem; color: #999; display: block; margin-top: 0.25rem;"><?= date('Y-m-d', strtotime($log['change_date'])) ?></code>
        </div>
    </td>

    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <code style="background-color: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 4px; color: #1a1a1a; font-size: 0.85rem;"><?= htmlspecialchars($log['table_name']) ?></code>
    </td>

    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <a href="/audit/view.php?table=<?= urlencode($log['table_name']) ?>&id=<?= (int)$log['record_id'] ?>"
           class="btn btn-sm" style="border-radius: 4px; background-color: #e8eaf6; color: #3f51b5; border: none; font-weight: 600; text-decoration: none;">
            #<?= (int)$log['record_id'] ?>
        </a>
    </td>

    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <?php
        $action = strtoupper($log['action']);
        $actionIcon = '⚡';
        $badgeBgColor = '#f5f5f5';
        $badgeTextColor = '#666';

        if (str_contains($action, 'CREATE')) {
            $badgeBgColor = '#e8f5e9';
            $badgeTextColor = '#2e7d32';
            $actionIcon = '✨';
        } elseif (str_contains($action, 'UPDATE')) {
            $badgeBgColor = '#e3f2fd';
            $badgeTextColor = '#1565c0';
            $actionIcon = '✏️';
        } elseif (str_contains($action, 'DELETE')) {
            $badgeBgColor = '#ffebee';
            $badgeTextColor = '#c62828';
            $actionIcon = '🗑';
        } elseif (str_contains($action, 'APPROVE')) {
            $badgeBgColor = '#f3e5f5';
            $badgeTextColor = '#6a1b9a';
            $actionIcon = '✅';
        } elseif (str_contains($action, 'REJECT')) {
            $badgeBgColor = '#ffebee';
            $badgeTextColor = '#c62828';
            $actionIcon = '❌';
        } elseif (str_contains($action, 'LOGIN')) {
            $badgeBgColor = '#e0f2f1';
            $badgeTextColor = '#00695c';
            $actionIcon = '🔓';
        }
        ?>
        <span style="display: inline-block; background-color: <?= $badgeBgColor ?>; color: <?= $badgeTextColor ?>; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
            <?= $actionIcon ?> <?= htmlspecialchars($log['action']) ?>
        </span>
    </td>

    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <?php $noteId = md5($log['change_date'] . $log['audit_id']); ?>
        <?php if (!empty($log['notes'])): ?>
            <button class="btn btn-sm" 
                    style="background-color: #fff3cd; color: #b09500; border: none; border-radius: 4px; font-weight: 600;"
                    data-bs-toggle="collapse"
                    data-bs-target="#note<?= $noteId ?>">
                📋 View Details
            </button>
            <div id="note<?= $noteId ?>" class="collapse mt-2">
                <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 4px; border-left: 3px solid #667eea;">
                    <small style="color: #1a1a1a;"><?= nl2br(htmlspecialchars($log['notes'])) ?></small>
                </div>
            </div>
        <?php else: ?>
            <small style="color: #ccc;">—</small>
        <?php endif; ?>
    </td>

    <td style="padding: 1rem; border: none; vertical-align: middle;">
        <div style="display: flex; align-items: center; gap: 0.5rem; color: #666; font-size: 0.9rem;">
            <span style="display: inline-block; width: 28px; height: 28px; background-color: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: #666;">?</span>
            <small><?= htmlspecialchars($log['changed_by'] ?? 'System') ?></small>
        </div>
    </td>
</tr>
<?php endforeach; ?>

<?php endif; ?>

</tbody>
            </table>
    </div>

    <div style="background-color: #f8f9fa; padding: 1.5rem; border-top: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <small style="color: #666; font-weight: 500;">
                Showing <strong><?= count($logs) ?></strong> records 
                <span style="color: #999;">•</span> 
                Page <strong><?= $page ?></strong> of <strong><?= max(1, ceil($totalRows / $perPage)) ?></strong>
            </small>
        </div>
        <small style="color: #999;">
            Total: <strong style="color: #1a1a1a;"><?= number_format($totalRows) ?> audit entries</strong>
        </small>
    </div>
</div>

<!-- Modern Pagination -->
<div class="mt-4 mb-4">
    <?php
    $queryParams = $_GET;
    unset($queryParams['page']);

    renderPagination(
        $totalRows,
        $perPage,
        $page,
        $queryParams
    );
    ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
