<?php
$REQUIRE_PERMISSION = 'management_dashboard';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

$filters = [];
$whereClauses = [];
$params = [];
$perPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Filtering logic
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
    $whereClauses[] = 'pr.status = :status';
    $params[':status'] = $_GET['status'];
}
if (!empty($_GET['stage'])) {
    $filters['stage'] = $_GET['stage'];
    $whereClauses[] = 'pr.procurement_method = :stage';
    $params[':stage'] = $_GET['stage'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
    $whereClauses[] = '(pr.description LIKE :search OR u.full_name LIKE :search)';
    $params[':search'] = '%'.$_GET['search'].'%';
}

$where = empty($whereClauses) ? '' : 'WHERE '.implode(' AND ', $whereClauses);

$sql = "SELECT pr.*, u.full_name AS requestor_name FROM procurement_requests pr LEFT JOIN users u ON pr.created_by = u.user_id $where ORDER BY pr.created_at DESC LIMIT :perPage OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "SELECT COUNT(*) FROM procurement_requests pr LEFT JOIN users u ON pr.created_by = u.user_id $where";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

?>
<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Header -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
            <div style="display: flex; align-items: center;">
                <span style="font-size: 1.75em; margin-right: 1rem;">📋</span>
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #333;">Management Approval Queue <span style="font-size: 0.875rem; color: #999; font-weight: 400;">(<?= $totalRows ?> requests)</span></h2>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="/dashboard/metrics.php" style="background: white; border: 1px solid #4facfe; color: #4facfe; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    <i class="bi bi-graph-up" style="margin-right: 0.5rem;"></i>Metrics
                </a>
                <a href="/dashboard/monthly.php" style="background: white; border: 1px solid #43e97b; color: #43e97b; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    <i class="bi bi-bar-chart-line" style="margin-right: 0.5rem;"></i>Monthly Trend
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
            <input type="text" name="search" style="padding: 0.625rem 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.875rem; width: 100%;" placeholder="🔍 Search title or requestor" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            <select name="status" style="padding: 0.625rem 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.875rem; width: 100%; background: white; cursor: pointer;">
                <option value="">All Status</option>
                <option value="DRAFT" <?= (isset($filters['status']) && $filters['status'] == 'DRAFT') ? 'selected' : '' ?>>Draft</option>
                <option value="SUBMITTED" <?= (isset($filters['status']) && $filters['status'] == 'SUBMITTED') ? 'selected' : '' ?>>Submitted</option>
                <option value="HOD_APPROVED" <?= (isset($filters['status']) && $filters['status'] == 'HOD_APPROVED') ? 'selected' : '' ?>>HOD Approved</option>
                <option value="FUNDS_VERIFIED" <?= (isset($filters['status']) && $filters['status'] == 'FUNDS_VERIFIED') ? 'selected' : '' ?>>Funds Verified</option>
                <option value="DIRECTOR_APPROVED" <?= (isset($filters['status']) && $filters['status'] == 'DIRECTOR_APPROVED') ? 'selected' : '' ?>>Director Approved</option>
                <option value="PROCUREMENT_STAGE" <?= (isset($filters['status']) && $filters['status'] == 'PROCUREMENT_STAGE') ? 'selected' : '' ?>>Procurement Stage</option>
                <option value="EVALUATION_STAGE" <?= (isset($filters['status']) && $filters['status'] == 'EVALUATION_STAGE') ? 'selected' : '' ?>>Evaluation Stage</option>
                <option value="COMMITTEE_RECOMMENDED" <?= (isset($filters['status']) && $filters['status'] == 'COMMITTEE_RECOMMENDED') ? 'selected' : '' ?>>Committee Recommended</option>
                <option value="GC_APPROVED" <?= (isset($filters['status']) && $filters['status'] == 'GC_APPROVED') ? 'selected' : '' ?>>GC Approved</option>
                <option value="AWARDED" <?= (isset($filters['status']) && $filters['status'] == 'AWARDED') ? 'selected' : '' ?>>Awarded</option>
                <option value="COMPLETED" <?= (isset($filters['status']) && $filters['status'] == 'COMPLETED') ? 'selected' : '' ?>>Completed</option>
                <option value="DECLINED" <?= (isset($filters['status']) && $filters['status'] == 'DECLINED') ? 'selected' : '' ?>>Declined</option>
            </select>
            <select name="stage" style="padding: 0.625rem 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.875rem; width: 100%; background: white; cursor: pointer;">
                <option value="">All Methods</option>
                <option value="SINGLE_SOURCE" <?= (isset($filters['stage']) && $filters['stage'] == 'SINGLE_SOURCE') ? 'selected' : '' ?>>Single Source</option>
                <option value="RESTRICTED_BIDDING" <?= (isset($filters['stage']) && $filters['stage'] == 'RESTRICTED_BIDDING') ? 'selected' : '' ?>>Restricted Bidding</option>
                <option value="NATIONAL_COMPETITIVE" <?= (isset($filters['stage']) && $filters['stage'] == 'NATIONAL_COMPETITIVE') ? 'selected' : '' ?>>National Competitive</option>
                <option value="INTERNATIONAL_COMPETITIVE" <?= (isset($filters['stage']) && $filters['stage'] == 'INTERNATIONAL_COMPETITIVE') ? 'selected' : '' ?>>International Competitive</option>
            </select>
            <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.625rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: transform 0.3s ease;">Apply Filters</button>
        </form>
    </div>

    <!-- Data Table -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead style="background: #f5f5f5;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; width: 40px;">#</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">📌 Title</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">👤 Requestor</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">🔄 Method</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">📋 Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">💰 Amount</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">📅 Created</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #999; padding: 2rem 0;">
                            <span style="font-size: 2em;">😕</span><br>
                            <span style="display: block; margin-top: 0.5rem;">No procurement requests found for the selected filters.</span>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($rows as $i => $row): ?>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333;"><?= ($offset + $i + 1) ?></td>
                        <td style="padding: 0.75rem 1rem; color: #333;"><?= htmlspecialchars($row['request_number'] . ' - ' . ($row['description'] ?: 'No description')) ?></td>
                        <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($row['requestor_name']) ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center; color: #666;">
                            <?php
                            switch ($row['procurement_method']) {
                                case 'SINGLE_SOURCE': echo 'Single Source'; break;
                                case 'RESTRICTED_BIDDING': echo 'Restricted Bidding'; break;
                                case 'NATIONAL_COMPETITIVE': echo 'National Competitive'; break;
                                case 'INTERNATIONAL_COMPETITIVE': echo 'International Competitive'; break;
                                default: echo $row['procurement_method'] ?? 'N/A';
                            }
                            ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <?php
                            $statusUpper = strtoupper($row['status']);
                            $mgmtBadgeMap = [
                                'DRAFT'                 => ['#999', '📝 Draft'],
                                'SUBMITTED'             => ['#ff9800', '⏳ Submitted'],
                                'HOD_APPROVED'          => ['#667eea', '👤 HOD Approved'],
                                'FUNDS_VERIFIED'        => ['#667eea', '💰 Funds Verified'],
                                'DIRECTOR_APPROVED'     => ['#4facfe', '👤 Director Approved'],
                                'PROCUREMENT_STAGE'     => ['#4facfe', '📑 Procurement Stage'],
                                'EVALUATION_STAGE'      => ['#4facfe', '📊 Evaluation Stage'],
                                'COMMITTEE_RECOMMENDED' => ['#4facfe', '🧾 Committee Recommended'],
                                'GC_APPROVED'           => ['#43e97b', '🏛 GC Approved'],
                                'AWARDED'               => ['#43e97b', '🏆 Awarded'],
                                'COMPLETED'             => ['#43e97b', '✔ Completed'],
                                'DECLINED'              => ['#f44336', '❌ Declined'],
                            ];
                            if (isset($mgmtBadgeMap[$statusUpper])) {
                                $badgeColor = $mgmtBadgeMap[$statusUpper][0];
                                $badgeText = $mgmtBadgeMap[$statusUpper][1];
                                echo '<span style="background: ' . $badgeColor . '; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">' . $badgeText . '</span>';
                            } else {
                                echo '<span style="background: #999; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">'.htmlspecialchars($row['status']).'</span>';
                            }
                            ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;"><?= money($row['estimated_value']) ?></td>
                        <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <a href="/procurement/view.php?id=<?= $row['request_id'] ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; transition: transform 0.3s ease;" title="View">🔎 View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; margin-bottom: 1rem; text-align: center;">
            <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" 
                       style="<?= ($p == $page) ? 'background: #667eea; color: white; font-weight: 600;' : 'background: white; color: #667eea; border: 1px solid #667eea;' ?> padding: 0.5rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; transition: all 0.3s ease;">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Widgets -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/pipeline.php"; ?>
        <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/branch_summary.php"; ?>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/commitment_utilization.php"; ?>
        <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/monthly_trend.php"; ?>
    </div>
    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
        <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/recent_activity.php"; ?>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
