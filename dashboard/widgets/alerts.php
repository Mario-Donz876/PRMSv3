<?php
if (!isset($pdo)) {
require_once __DIR__.'/_init.php';

}


/* ===============================
   ALERT METRICS
================================ */

// Outstanding balance
$outstanding = $pdo->query("
    SELECT 
        SUM(i.invoice_amount - IFNULL(p.paid, 0)) AS outstanding
    FROM invoices i
    LEFT JOIN (
        SELECT invoice_id, SUM(payment_amount) AS paid
        FROM payments
        GROUP BY invoice_id
    ) p ON i.invoice_id = p.invoice_id
    WHERE i.status != 'Paid'
")->fetchColumn();


// Open POs
$openPOs = (int) $pdo->query("
  SELECT COUNT(*) FROM purchase_orders WHERE status = 'Open'
")->fetchColumn();

// Unpaid invoices
$unpaidInvoices = (int) $pdo->query("
  SELECT COUNT(*) FROM invoices WHERE status != 'Paid'
")->fetchColumn();

/* ===============================
   THRESHOLDS
================================ */
$alerts = [];

if ($outstanding > 100000) {
  $alerts[] = [
    'type' => 'danger',
    'icon' => '💰',
    'text' => 'Outstanding balance exceeds $100,000',
    'link' => '/invoice/list.php'
  ];
}

if ($openPOs >= 10) {
  $alerts[] = [
    'type' => 'warning',
    'icon' => '📑',
    'text' => "There are $openPOs open purchase orders",
    'link' => '/po/list.php?status=Open'
  ];
}

if ($unpaidInvoices >= 5) {
  $alerts[] = [
    'type' => 'danger',
    'icon' => '⏰',
    'text' => "$unpaidInvoices unpaid invoices require attention",
    'link' => '/invoice/list.php?status=Unpaid'
  ];
}
?>

<style>
    .alert-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .alert-item {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        text-decoration: none !important;
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }
    .alert-item:last-child {
        margin-bottom: 0;
    }
    .alert-item:hover {
        transform: translateX(4px);
    }
    .alert-danger-gradient {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
    }
    .alert-warning-gradient {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        box-shadow: 0 4px 12px rgba(250, 112, 154, 0.3);
    }
    .alert-success-gradient {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        box-shadow: 0 4px 12px rgba(67, 233, 123, 0.3);
    }
    .alert-icon {
        font-size: 1.5rem;
        min-width: 2rem;
    }
    .alert-content {
        flex: 1;
        font-weight: 500;
    }
</style>

<div class="alert-widget">
    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">🚨</span> System Alerts</h5>
    </div>

    <?php if (empty($alerts)): ?>
        <div class="alert-item alert-success-gradient">
            <div class="alert-icon">✅</div>
            <div class="alert-content">All systems operating normally</div>
        </div>
    <?php else: ?>
        <?php foreach ($alerts as $a): 
            $alertClass = match($a['type']) {
                'warning' => 'alert-warning-gradient',
                default => 'alert-danger-gradient'
            };
        ?>
            <a href="<?= htmlspecialchars($a['link']) ?>" class="alert-item <?= $alertClass ?>">
                <div class="alert-icon"><?= $a['icon'] ?></div>
                <div class="alert-content"><?= htmlspecialchars($a['text']) ?></div>
                <div style="font-size: 1.2rem; opacity: 0.8;">→</div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
