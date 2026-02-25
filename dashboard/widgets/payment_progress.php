<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$poPayments = $pdo->query("
    SELECT po.po_id, po.po_number, po.po_total, po.status,
           IFNULL(SUM(i.invoice_amount), 0) AS total_invoiced,
           IFNULL(SUM(p.paid), 0) AS total_paid,
           pr.currency
    FROM purchase_orders po
    LEFT JOIN invoices i ON po.po_id = i.po_id
    LEFT JOIN (
        SELECT invoice_id, SUM(payment_amount) AS paid
        FROM payments
        GROUP BY invoice_id
    ) p ON i.invoice_id = p.invoice_id
    LEFT JOIN commitments c ON po.commitment_id = c.commitment_id
    LEFT JOIN procurement_requests pr ON c.request_id = pr.request_id
    WHERE po.status = 'Open'
    GROUP BY po.po_id
    ORDER BY po.po_date DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .payment-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .payment-item {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .payment-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .payment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .payment-number {
        font-weight: 600;
        color: #1a1a1a;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .payment-number:hover {
        color: #667eea;
    }
    .payment-bar-container {
        height: 16px;
        background: #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 0.75rem;
        display: flex;
    }
    .payment-bar-paid {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        color: white;
    }
    .payment-bar-invoiced {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .payment-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        gap: 1rem;
    }
    .payment-info-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
</style>

<div class="payment-widget">
    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">💳</span> Payment Progress</h5>
        <a href="/po/list.php?status=Open" style="color: #667eea; text-decoration: none; font-weight: 500; font-size: 0.9rem;">All Open POs →</a>
    </div>

    <?php if (empty($poPayments)): ?>
        <div style="text-align: center; padding: 2rem; color: #999;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">😕</div>
            <div>No open purchase orders</div>
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($poPayments as $po): ?>
                <?php
                $invoiced = (float)$po['total_invoiced'];
                $paid = (float)$po['total_paid'];
                $total = (float)$po['po_total'];
                $pctInvoiced = $total > 0 ? min(round(($invoiced / $total) * 100), 100) : 0;
                $pctPaid = $total > 0 ? min(round(($paid / $total) * 100), 100) : 0;
                ?>
                <div class="payment-item">
                    <div class="payment-header">
                        <a href="/po/view.php?id=<?= $po['po_id'] ?>" class="payment-number">
                            <?= htmlspecialchars($po['po_number']) ?>
                        </a>
                        <small style="color: #999;">JMD <?= number_format($total, 0) ?></small>
                    </div>

                    <div class="payment-bar-container">
                        <div class="payment-bar-paid" style="width: <?= $pctPaid ?>%;" title="<?= 'Paid: JMD ' . number_format($paid, 0) ?>">
                            <?= $pctPaid >= 15 ? $pctPaid . '%' : '' ?>
                        </div>
                        <div class="payment-bar-invoiced" style="width: <?= max($pctInvoiced - $pctPaid, 0) ?>%;" title="<?= 'Invoiced (unpaid): JMD ' . number_format($invoiced - $paid, 0) ?>"></div>
                        <div style="flex: 1; background: #e0e0e0;"></div>
                    </div>

                    <div class="payment-info">
                        <div class="payment-info-item">
                            <span style="width: 8px; height: 8px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 50%;"></span>
                            <span style="color: #666; font-weight: 500;">Paid: <strong style="color: #1a1a1a;">JMD <?= number_format($paid, 0) ?></strong></span>
                        </div>
                        <div class="payment-info-item">
                            <span style="width: 8px; height: 8px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 50%;"></span>
                            <span style="color: #666; font-weight: 500;">Invoiced: <strong style="color: #1a1a1a;">JMD <?= number_format($invoiced, 0) ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
