<?php
$REQUIRE_PERMISSION = 'view_management_dashboard';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=branch_outstanding.xls");

$data = $pdo->query("SELECT * FROM vw_branch_outstanding");

echo "Branch\tTotal Invoiced\tTotal Paid\tOutstanding\n";
while ($r = $data->fetch()) {
    echo "{$r['branch_name']}\t{$r['total_invoiced']}\t{$r['total_paid']}\t{$r['outstanding']}\n";
}
exit;
