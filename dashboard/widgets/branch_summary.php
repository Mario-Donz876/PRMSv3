<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$branches = $pdo->query("
    SELECT b.branch_name,
           COUNT(pr.request_id) AS total_requests,
           SUM(pr.estimated_value) AS total_estimated,
           SUM(CASE WHEN UPPER(pr.status) IN ('GC_APPROVED','AWARDED','COMPLETED') THEN 1 ELSE 0 END) AS approved,
           SUM(CASE WHEN UPPER(pr.status) = 'SUBMITTED' THEN 1 ELSE 0 END) AS pending
    FROM branches b
    LEFT JOIN procurement_requests pr ON b.branch_id = pr.branch_id
    WHERE b.is_active = 1
    GROUP BY b.branch_id
    ORDER BY total_estimated DESC
")->fetchAll(PDO::FETCH_ASSOC);

$grandTotal = array_sum(array_column($branches, 'total_estimated'));
?>

<style>
    .branch-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .branch-table {
        width: 100%;
        border-collapse: collapse;
    }
    .branch-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }
    .branch-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #333;
    }
    .branch-table td {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        color: #1a1a1a;
    }
    .branch-table tbody tr:hover {
        background: #f8f9fa;
    }
    .branch-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 500;
        color: white;
    }
</style>

<div class="branch-widget">
    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">🏢</span> Branch Summary</h5>
        <a href="/reports/branch_summary.php" style="color: #667eea; text-decoration: none; font-weight: 500; font-size: 0.9rem;">Full Report →</a>
    </div>

    <table class="branch-table">
        <thead>
            <tr>
                <th style="width: 25%;">🏢 Branch</th>
                <th style="width: 12%; text-align: center;">📋 Requests</th>
                <th style="width: 12%; text-align: center;">✅ Approved</th>
                <th style="width: 12%; text-align: center;">⏳ Pending</th>
                <th style="width: 18%; text-align: right;">💰 Est. Value</th>
                <th style="width: 21%;">Share</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($branches)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">😕</div>
                        <div>No branch data available</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($branches as $b): ?>
                    <?php $pct = $grandTotal > 0 ? round(($b['total_estimated'] / $grandTotal) * 100) : 0; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                        <td style="text-align: center;"><?= (int)$b['total_requests'] ?></td>
                        <td style="text-align: center;">
                            <span class="branch-badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"><?= (int)$b['approved'] ?></span>
                        </td>
                        <td style="text-align: center;">
                            <span class="branch-badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);"><?= (int)$b['pending'] ?></span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">$<?= number_format((float)$b['total_estimated'], 0) ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: <?= $pct ?>%; transition: width 0.5s ease;"></div>
                                </div>
                                <span style="font-size: 0.8rem; color: #999; min-width: 2.5rem; text-align: right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
