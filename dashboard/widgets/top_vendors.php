<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$topVendors = $pdo->query("
    SELECT v.vendor_name, v.status, v.performance_rating, v.total_awards,
           COUNT(rq.quote_id) AS quotes_submitted,
           SUM(CASE WHEN rq.is_selected = 1 THEN 1 ELSE 0 END) AS times_selected,
           IFNULL(SUM(CASE WHEN rq.is_selected = 1 THEN rq.quote_amount + rq.gct_amount ELSE 0 END), 0) AS total_awarded_value
    FROM vendors v
    LEFT JOIN rfq_vendors rv ON v.vendor_id = rv.vendor_id
    LEFT JOIN rfq_quotes rq ON rv.rfq_vendor_id = rq.rfq_vendor_id
    WHERE v.status = 'ACTIVE'
    GROUP BY v.vendor_id
    ORDER BY times_selected DESC, total_awarded_value DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .vendors-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .vendors-table {
        width: 100%;
        border-collapse: collapse;
    }
    .vendors-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }
    .vendors-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #333;
    }
    .vendors-table td {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        color: #1a1a1a;
    }
    .vendors-table tbody tr:hover {
        background: #f8f9fa;
    }
    .vendor-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 500;
        color: white;
    }
</style>

<div class="vendors-widget">
    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">🏆</span> Top Vendors</h5>
        <a href="/vendors/list.php" style="color: #667eea; text-decoration: none; font-weight: 500; font-size: 0.9rem;">View All →</a>
    </div>

    <table class="vendors-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Vendor Name</th>
                <th style="width: 15%; text-align: center;">Awards</th>
                <th style="width: 20%; text-align: right;">Awarded Value</th>
                <th style="width: 15%; text-align: center;">Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($topVendors)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: #999;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">😕</div>
                        <div>No vendor data available</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($topVendors as $i => $v): ?>
                    <tr>
                        <td><strong><?= $i + 1 ?></strong></td>
                        <td><strong><?= htmlspecialchars($v['vendor_name']) ?></strong></td>
                        <td style="text-align: center;">
                            <span class="vendor-badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"><?= (int)$v['times_selected'] ?></span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">$<?= number_format((float)$v['total_awarded_value'], 0) ?></td>
                        <td style="text-align: center;">
                            <?php
                            $rating = (float)$v['performance_rating'];
                            if ($rating >= 4) {
                                echo '<span class="vendor-badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">⭐ ' . $rating . '</span>';
                            } elseif ($rating >= 3) {
                                echo '<span class="vendor-badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">⭐ ' . $rating . '</span>';
                            } elseif ($rating > 0) {
                                echo '<span class="vendor-badge" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">⭐ ' . $rating . '</span>';
                            } else {
                                echo '<span style="color: #999;">—</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
