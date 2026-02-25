<?php
if (!isset($pdo)) {
require_once __DIR__.'/_init.php';
}
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


$openPOs = $pdo->query("
  SELECT COUNT(*) FROM purchase_orders WHERE status='Open'
")->fetchColumn();

$overdue = $pdo->query("
  SELECT COUNT(*) FROM invoices
  WHERE status!='Paid'
  AND invoice_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetchColumn();
?>

<style>
    .kpi-card-modern {
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        text-decoration: none !important;
        transition: all 0.3s ease;
        display: block;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    .kpi-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    .kpi-gold {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .kpi-green {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .kpi-blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .kpi-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>

<!-- Outstanding Balance -->
<a href="/invoice/list.php" class="kpi-card-modern kpi-gold">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; opacity: 0.9; text-transform: uppercase; margin-bottom: 0.5rem;">Outstanding Balance</div>
            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.3rem;">$<?= number_format((float)($outstanding ?? 0), 0) ?></div>
            <div style="font-size: 0.85rem; opacity: 0.9;">Across all branches</div>
        </div>
        <div style="font-size: 2.5rem; opacity: 0.8;">💰</div>
    </div>
</a>

<!-- Open Purchase Orders -->
<a href="/po/list.php?status=Open" class="kpi-card-modern kpi-green">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; opacity: 0.9; text-transform: uppercase; margin-bottom: 0.5rem;">Open Purchase Orders</div>
            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.3rem;"><?= $openPOs ?></div>
            <div style="font-size: 0.85rem; opacity: 0.9;">Currently active</div>
        </div>
        <div style="font-size: 2.5rem; opacity: 0.8;">📑</div>
    </div>
</a>

<!-- Unpaid / Overdue Invoices -->
<a href="/invoice/list.php?status=Unpaid" class="kpi-card-modern kpi-purple">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; opacity: 0.9; text-transform: uppercase; margin-bottom: 0.5rem;">Unpaid Invoices</div>
            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.3rem;"><?= $overdue ?></div>
            <div style="font-size: 0.85rem; opacity: 0.9;">Over 30 Days Old</div>
        </div>
        <div style="font-size: 2.5rem; opacity: 0.8;">⏰</div>
    </div>
</a>


