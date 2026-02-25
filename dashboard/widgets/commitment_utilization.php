<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$commitments = $pdo->query("
    SELECT c.commitment_id, c.commitment_number, c.commitment_total, c.status,
           IFNULL(SUM(po.po_total), 0) AS total_pos,
           IFNULL(SUM(inv_total.invoiced), 0) AS total_invoiced
    FROM commitments c
    LEFT JOIN purchase_orders po ON c.commitment_id = po.commitment_id
    LEFT JOIN (
        SELECT po.commitment_id, SUM(i.invoice_amount) AS invoiced
        FROM purchase_orders po
        JOIN invoices i ON po.po_id = i.po_id
        GROUP BY po.commitment_id
    ) inv_total ON c.commitment_id = inv_total.commitment_id
    WHERE c.status = 'open'
    GROUP BY c.commitment_id
    ORDER BY c.commitment_date DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
    <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;"><span style="font-size: 1.25em;">📌</span> Commitment Utilization</h6>
    <a href="/commitments/list.php" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all 0.3s ease; cursor: pointer;">All Commitments</a>
  </div>
  
  <?php if (empty($commitments)): ?>
    <div style="text-align: center; color: #999; padding: 2rem 0;">
      <span style="font-size: 2em;">😕</span><br>
      <span style="display: block; margin-top: 0.5rem;">No open commitments</span>
    </div>
  <?php else: ?>
    <?php foreach ($commitments as $c): ?>
      <?php
      $total = (float)$c['commitment_total'];
      $pos = (float)$c['total_pos'];
      $invoiced = (float)$c['total_invoiced'];
      $pctPO = $total > 0 ? min(round(($pos / $total) * 100), 100) : 0;
      $pctInv = $total > 0 ? min(round(($invoiced / $total) * 100), 100) : 0;
      $remaining = max($total - $pos, 0);

      if ($pctPO >= 90) {
          $barGradient = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
      } elseif ($pctPO >= 70) {
          $barGradient = 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)';
      } else {
          $barGradient = 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';
      }
      ?>
      <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f0f0f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
          <a href="/commitments/view.php?id=<?= $c['commitment_id'] ?>" style="font-weight: 600; text-decoration: none; color: #333; transition: color 0.3s ease;">
            <?= htmlspecialchars($c['commitment_number']) ?>
          </a>
          <small style="color: #999; font-size: 0.875rem;">Budget: <span style="font-weight: 600; color: #333;">$<?= number_format($total, 2) ?></span></small>
        </div>
        <div style="background: #f0f0f0; border-radius: 8px; overflow: hidden; height: 12px;">
          <div style="background: <?= $barGradient ?>; width: <?= $pctPO ?>%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: white; font-weight: 600; transition: width 0.3s ease;" title="POs: $<?= number_format($pos, 2) ?>">
            <?php if ($pctPO > 15): ?><?= $pctPO ?>%<?php endif; ?>
          </div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; font-size: 0.875rem;">
          <small style="color: #666;">POs: <span style="font-weight: 600; color: #333;">$<?= number_format($pos, 2) ?></span></small>
          <small style="color: #999;">Remaining: <span style="font-weight: 600; color: #43e97b;">$<?= number_format($remaining, 2) ?></span></small>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
