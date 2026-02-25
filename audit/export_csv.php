<?php
$REQUIRE_PERMISSION = 'view_audit_logs';
require_once $_SERVER['DOCUMENT_ROOT']."/config/page_guard.php";
require_once $_SERVER['DOCUMENT_ROOT']."/config/db.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="audit_export_' . date('Y-m-d_His') . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Date', 'Time', 'Table', 'Record ID', 'Action', 'Notes', 'Changed By']);

$stmt = $pdo->query("
    SELECT a.change_date, a.table_name, a.record_id,
           a.action, a.notes, a.changed_by
    FROM audit_log a
    ORDER BY a.change_date DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        date('Y-m-d', strtotime($row['change_date'])),
        date('g:i:s A', strtotime($row['change_date'])),
        $row['table_name'],
        $row['record_id'],
        $row['action'],
        $row['notes'],
        $row['changed_by'] ?? 'System'
    ]);
}

fclose($output);
exit;
