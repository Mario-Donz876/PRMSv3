<?php
$REQUIRE_PERMISSION = 'view_compliance';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

// Fetch compliance metrics
$metricsQuery = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT pr.request_id) as total_requests,
        COUNT(DISTINCT CASE WHEN ca.status = 'approved' THEN pr.request_id END) as compliant,
        COUNT(DISTINCT CASE WHEN ca.status = 'rejected' THEN pr.request_id END) as non_compliant,
        COUNT(DISTINCT CASE WHEN ca.status = 'pending' THEN pr.request_id END) as pending_approval
    FROM procurement_requests pr
    LEFT JOIN compliance_approvals ca ON pr.request_id = ca.entity_id AND ca.entity_type = 'procurement_request'
");
$metricsQuery->execute();
$metrics = $metricsQuery->fetch(PDO::FETCH_ASSOC);

// Fetch compliance records with filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereConditions = [];
$params = [];

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $whereConditions[] = "ca.status = ?";
    $params[] = $_GET['status'];
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$countQuery = $pdo->prepare("
    SELECT COUNT(*) as total FROM compliance_approvals ca
    JOIN procurement_requests pr ON ca.entity_id = pr.request_id
    $whereClause
");
$countQuery->execute($params);
$totalRecords = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $perPage);

$dataQuery = $pdo->prepare("
    SELECT 
        ca.id,
        ca.entity_id,
        ca.approval_body,
        ca.status,
        ca.created_at,
        ca.updated_at,
        pr.description as title,
        pr.status as request_status
    FROM compliance_approvals ca
    JOIN procurement_requests pr ON ca.entity_id = pr.request_id
    $whereClause
    ORDER BY ca.created_at DESC
    LIMIT ? OFFSET ?
");
$dataQuery->bindValue(1, $perPage, PDO::PARAM_INT);
$dataQuery->bindValue(2, $offset, PDO::PARAM_INT);
foreach ($params as $i => $param) {
    $dataQuery->bindValue($i + 3, $param);
}
$dataQuery->execute();
$records = $dataQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <h1 style="margin: 0 0 2rem 0; font-size: 1.75rem; font-weight: 700; color: #333;">📋 Compliance Dashboard</h1>

    <!-- Metrics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; border: 2px solid #667eea; padding: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
            <h6 style="margin: 0; font-size: 0.875rem; color: #999; font-weight: 600;">Total Requests</h6>
            <h3 style="margin: 0.75rem 0 0 0; font-size: 2rem; color: #667eea; font-weight: 700;"><?php echo htmlspecialchars($metrics['total_requests']); ?></h3>
        </div>
        <div style="background: white; border-radius: 12px; border: 2px solid #43e97b; padding: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
            <h6 style="margin: 0; font-size: 0.875rem; color: #999; font-weight: 600;">✅ Compliant</h6>
            <h3 style="margin: 0.75rem 0 0 0; font-size: 2rem; color: #43e97b; font-weight: 700;"><?php echo htmlspecialchars($metrics['compliant']); ?></h3>
        </div>
        <div style="background: white; border-radius: 12px; border: 2px solid #f5576c; padding: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
            <h6 style="margin: 0; font-size: 0.875rem; color: #999; font-weight: 600;">❌ Non-Compliant</h6>
            <h3 style="margin: 0.75rem 0 0 0; font-size: 2rem; color: #f5576c; font-weight: 700;"><?php echo htmlspecialchars($metrics['non_compliant']); ?></h3>
        </div>
        <div style="background: white; border-radius: 12px; border: 2px solid #fee140; padding: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
            <h6 style="margin: 0; font-size: 0.875rem; color: #999; font-weight: 600;">⏳ Pending</h6>
            <h3 style="margin: 0.75rem 0 0 0; font-size: 2rem; color: #f5a63d; font-weight: 700;"><?php echo htmlspecialchars($metrics['pending_approval']); ?></h3>
        </div>
    </div>

    <!-- Filters -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; font-size: 0.875rem;">Status</label>
                <select name="status" id="status" style="width: 100%; padding: 0.625rem; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 0.875rem; background: white; color: #333;">
                    <option value="">All</option>
                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo isset($_GET['status']) && $_GET['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo isset($_GET['status']) && $_GET['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.625rem 1.25rem; border-radius: 8px; border: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: transform 0.3s ease;">Filter</button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Compliance Records Table -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <div style="padding: 1.5rem; border-bottom: 2px solid #e0e0e0;">
            <h5 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">Approval Records</h5>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead style="background: #f5f5f5;">
                    <tr>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request ID</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Title</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Approval Body</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Created</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Updated</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $record): 
                            $statusGradient = $record['status'] === 'approved' ? 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' : ($record['status'] === 'rejected' ? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' : 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)');
                        ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; color: #333; font-weight: 600;"><?php echo htmlspecialchars($record['entity_id']); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?php echo htmlspecialchars($record['title']); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?php echo htmlspecialchars($record['approval_body']); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <span style="background: <?= $statusGradient ?>; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                        <?php echo htmlspecialchars(ucfirst($record['status'])); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?php echo htmlspecialchars(date('d M Y', strtotime($record['created_at']))); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?php echo htmlspecialchars(date('d M Y', strtotime($record['updated_at']))); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/views/compliance-detail.php?id=<?php echo htmlspecialchars($record['id']); ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; transition: transform 0.3s ease;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 2rem 1rem; text-align: center; color: #999;">No compliance records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="padding: 1rem; border-top: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <small style="color: #999;">Page <?php echo htmlspecialchars($page); ?> of <?php echo htmlspecialchars($totalPages); ?></small>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="?page=1" style="background: white; border: 1px solid #e0e0e0; color: #333; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; <?php echo $page <= 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">First</a>
                    <a href="?page=<?php echo htmlspecialchars($page - 1); ?>" style="background: white; border: 1px solid #e0e0e0; color: #333; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; <?php echo $page <= 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">Previous</a>
                    <a href="?page=<?php echo htmlspecialchars($page + 1); ?>" style="background: white; border: 1px solid #e0e0e0; color: #333; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; <?php echo $page >= $totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">Next</a>
                    <a href="?page=<?php echo htmlspecialchars($totalPages); ?>" style="background: white; border: 1px solid #e0e0e0; color: #333; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; <?php echo $page >= $totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">Last</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>