<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$overdue = $pdo->query("
    SELECT i.invoice_id, i.invoice_number, i.invoice_date, i.invoice_amount, i.status,
           po.po_number,
           IFNULL(SUM(p.payment_amount), 0) AS total_paid,
           DATEDIFF(CURDATE(), i.invoice_date) AS days_overdue
    FROM invoices i
    JOIN purchase_orders po ON i.po_id = po.po_id
    LEFT JOIN payments p ON i.invoice_id = p.invoice_id
    WHERE i.status != 'Paid'
    GROUP BY i.invoice_id
    HAVING days_overdue > 30
    ORDER BY days_overdue DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
    <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;"><span style="font-size: 1.25em;">⏰</span> Overdue Invoices</h6>
    <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;"><?= count($overdue) ?></span>
  </div>
  
  <?php if (empty($overdue)): ?>
    <div style="text-align: center; color: #999; padding: 2rem 0;">
      <span style="font-size: 1.5em;">✅</span><br>
      <span style="display: block; margin-top: 0.5rem;">No overdue invoices</span>
    </div>
  <?php else: ?>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead style="background: #f5f5f5;">
          <tr>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Invoice</th>
            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">PO</th>
            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Amount</th>
            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Paid</th>
            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Days Overdue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($overdue as $inv): ?>
            <?php
            $days = (int)$inv['days_overdue'];
            if ($days > 90) {
                $ageBadge = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
                $ageLabel = '90+ days';
            } elseif ($days > 60) {
                $ageBadge = 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)';
                $ageLabel = '60-90 days';
            } else {
                $ageBadge = 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';
                $ageLabel = '30-60 days';
            }
            $outstanding = (float)$inv['invoice_amount'] - (float)$inv['total_paid'];
            ?>
            <tr style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s ease;">
              <td style="padding: 0.75rem 1rem;">
                <a href="/invoice/view.php?id=<?= $inv['invoice_id'] ?>" style="text-decoration: none; font-weight: 600; color: #333; transition: color 0.3s ease;">
                  <?= htmlspecialchars($inv['invoice_number']) ?>
                </a>
              </td>
              <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.9rem;">
                <?= htmlspecialchars($inv['po_number']) ?>
              </td>
              <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;">
                $<?= number_format((float)$inv['invoice_amount'], 2) ?>
              </td>
              <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #43e97b;">
                $<?= number_format((float)$inv['total_paid'], 2) ?>
              </td>
              <td style="padding: 0.75rem 1rem; text-align: center;">
                <span style="background: <?= $ageBadge ?>; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                  <?= $days ?> days
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</div>
