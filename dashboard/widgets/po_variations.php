<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$variations = $pdo->query("
    SELECT pv.variation_id, pv.variation_amount, pv.reason, pv.status,
           pv.requested_at, po.po_number,
           u.full_name AS requested_by_name
    FROM po_variations pv
    JOIN purchase_orders po ON pv.po_id = po.po_id
    LEFT JOIN users u ON pv.requested_by = u.user_id
    ORDER BY pv.requested_at DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

$statusMeta = [
    'PENDING'  => ['icon' => '⏳', 'bg' => 'warning', 'text' => 'text-dark'],
    'APPROVED' => ['icon' => '✅', 'bg' => 'success', 'text' => ''],
    'REJECTED' => ['icon' => '❌', 'bg' => 'danger', 'text' => ''],
];
?>
<div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
    <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;"><span style="font-size: 1.25em;">🔧</span> PO Variations</h6>
    <a href="/po/variation_queue.php" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all 0.3s ease; cursor: pointer;">View Queue</a>
  </div>
  
  <?php if (empty($variations)): ?>
    <div style="text-align: center; color: #999; padding: 2rem 0;">
      <span style="font-size: 1.5em;">✅</span><br>
      <span style="display: block; margin-top: 0.5rem;">No PO variations</span>
    </div>
  <?php else: ?>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead style="background: #f5f5f5;">
          <tr>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">PO</th>
            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Amount</th>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Reason</th>
            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($variations as $v): ?>
            <?php
            $meta = $statusMeta[$v['status']] ?? ['icon' => '📋', 'bg' => '#999', 'text' => '#fff'];
            $statusGradient = [
              'PENDING'  => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
              'APPROVED' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
              'REJECTED' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            ];
            $gradient = $statusGradient[$v['status']] ?? 'linear-gradient(135deg, #999 0%, #666 100%)';
            ?>
            <tr style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s ease;">
              <td style="padding: 0.75rem 1rem;">
                <span style="font-weight: 600; color: #333;"><?= htmlspecialchars($v['po_number']) ?></span>
              </td>
              <td style="padding: 0.75rem 1rem; text-align: right; color: <?= (float)$v['variation_amount'] >= 0 ? '#43e97b' : '#f5576c' ?>; font-weight: 600;">
                <?= ((float)$v['variation_amount'] >= 0 ? '+' : '') ?>$<?= number_format((float)$v['variation_amount'], 2) ?>
              </td>
              <td style="padding: 0.75rem 1rem;">
                <small style="color: #666; max-width: 200px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($v['reason']) ?>">
                  <?= htmlspecialchars($v['reason']) ?>
                </small>
              </td>
              <td style="padding: 0.75rem 1rem; text-align: center;">
                <span style="background: <?= $gradient ?>; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                  <?= $meta['icon'] ?> <?= $v['status'] ?>
                </span>
              </td>
              <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;">
                <?= date('d M Y', strtotime($v['requested_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
