<?php
if (!isset($pdo)) {
require_once __DIR__.'/_init.php';
}
/* Current month */
$thisMonth = $pdo->query("
    SELECT 
      COUNT(DISTINCT pr.request_id) AS requests,
      COUNT(DISTINCT po.po_id) AS pos,
      IFNULL(SUM(i.invoice_amount),0) AS invoiced
    FROM procurement_requests pr
    LEFT JOIN commitments c ON pr.request_id = c.request_id
    LEFT JOIN purchase_orders po ON c.commitment_id = po.commitment_id
    LEFT JOIN invoices i ON po.po_id = i.po_id
    WHERE MONTH(pr.request_date) = MONTH(CURDATE())
      AND YEAR(pr.request_date) = YEAR(CURDATE())
")->fetch(PDO::FETCH_ASSOC);

/* Last month */
$lastMonth = $pdo->query("
    SELECT 
      COUNT(DISTINCT pr.request_id) AS requests,
      COUNT(DISTINCT po.po_id) AS pos,
      IFNULL(SUM(i.invoice_amount),0) AS invoiced
    FROM procurement_requests pr
    LEFT JOIN commitments c ON pr.request_id = c.request_id
    LEFT JOIN purchase_orders po ON c.commitment_id = po.commitment_id
    LEFT JOIN invoices i ON po.po_id = i.po_id
    WHERE MONTH(pr.request_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)
      AND YEAR(pr.request_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)
")->fetch(PDO::FETCH_ASSOC);
?>

<style>
    .monthly-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .metric-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 1rem;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .metric-row:last-child {
        border-bottom: none;
    }
    .metric-label {
        font-weight: 600;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .metric-value {
        font-size: 1.2rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        background: #f8f9fa;
        text-align: center;
    }
    .metric-value-this {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    .metric-value-last {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
</style>

<div class="monthly-widget">
    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">📊</span> Monthly Overview</h5>
    </div>

    <div>
        <div class="metric-row">
            <div class="metric-label"><span style="font-size: 1.2rem;">📋</span> Requests</div>
            <div class="metric-value metric-value-this"><?= (int)$thisMonth['requests'] ?></div>
            <div class="metric-value metric-value-last"><?= (int)$lastMonth['requests'] ?></div>
        </div>

        <div class="metric-row">
            <div class="metric-label"><span style="font-size: 1.2rem;">📑</span> Purchase Orders</div>
            <div class="metric-value metric-value-this"><?= (int)$thisMonth['pos'] ?></div>
            <div class="metric-value metric-value-last"><?= (int)$lastMonth['pos'] ?></div>
        </div>

        <div class="metric-row">
            <div class="metric-label"><span style="font-size: 1.2rem;">💰</span> Invoiced</div>
            <div class="metric-value metric-value-this">$<?= number_format((float)$thisMonth['invoiced'], 0) ?></div>
            <div class="metric-value metric-value-last">$<?= number_format((float)$lastMonth['invoiced'], 0) ?></div>
        </div>
    </div>

    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e0e0e0; display: flex; justify-content: space-between; font-size: 0.85rem; color: #999;">
        <span><span style="font-size: 1rem;">🟢</span> This Month</span>
        <span><span style="font-size: 1rem;">🔵</span> Last Month</span>
    </div>
</div>
