<?php

$REQUIRE_PERMISSION = 'monthly_metrics';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';


$sql = "SELECT MONTH(pr.request_date) AS month, YEAR(pr.request_date) AS year, SUM(pr.estimated_value) AS total_amount, COUNT(*) AS request_count FROM procurement_requests pr GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 12";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
  <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
    <!-- Header with Navigation -->
    <div style="display: flex; flex-direction: column;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <div style="display: flex; align-items: center;">
          <span style="font-size: 1.75em; margin-right: 1rem;">📈</span>
          <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #333;">Monthly Procurement Trend <span style="font-size: 0.875rem; color: #999; font-weight: 400;">(last 12 months)</span></h2>
          </div>
        </div>
        <div style="display: flex; gap: 0.75rem;">
          <a href="/dashboard/metrics.php" style="background: white; border: 1px solid #4facfe; color: #4facfe; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="bi bi-graph-up" style="margin-right: 0.5rem;"></i>Metrics
          </a>
          <a href="/dashboard/management.php" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="bi bi-list-check" style="margin-right: 0.5rem;"></i>Approval Queue
          </a>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead style="background: #f5f5f5;">
          <tr>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">📅 Month</th>
            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">📋 Requests</th>
            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">💰 Total Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="3" style="text-align: center; color: #999; padding: 2rem 0;">
                <span style="font-size: 2em;">😕</span><br>
                <span style="display: block; margin-top: 0.5rem;">No monthly data available.</span>
              </td>
            </tr>
          <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <tr style="border-bottom: 1px solid #f0f0f0;">
              <td style="padding: 0.75rem 1rem; color: #333; font-weight: 600;"><?= date('M Y', strtotime($row['year'].'-'.$row['month'].'-01')) ?></td>
              <td style="padding: 0.75rem 1rem; text-align: center; color: #666;"><?= (int)$row['request_count'] ?></td>
              <td style="padding: 0.75rem 1rem; text-align: right; color: #333; font-weight: 600;"><?= money($row['total_amount']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
