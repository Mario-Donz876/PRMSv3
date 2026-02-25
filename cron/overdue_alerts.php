<?php
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

$invoices = $pdo->query("
  SELECT i.invoice_number, i.invoice_date, po.po_number
  FROM invoices i
  JOIN purchase_orders po ON i.po_id = po.po_id
  WHERE i.status != 'Paid'
  AND i.invoice_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetchAll();

if (!$invoices) exit;

$message = "Overdue Invoices:\n\n";
foreach ($invoices as $i) {
    $message .= "Invoice {$i['invoice_number']} (PO {$i['po_number']})\n";
}

mail(
  "accounts@governmentchemist.com",
  "Overdue Invoice Alert",
  $message
);
