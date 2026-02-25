<?php
$REQUIRE_PERMISSION = 'view_audit_logs';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT']."/config/helper.php";


/* Validate input */
$table = $_GET['table'] ?? '';
$id    = $_GET['id'] ?? null;
if (!empty($id) && !ctype_digit((string)$id)) {
    pop('Invalid record ID', '/audit/list.php', POP_DEFAULT_DELAY_MS, 'error');
    exit;
}

/* Fetch audit data */
$where = [];
$params = [];

if (!empty($table)) {
    $where[] = "table_name = :table";
    $params[':table'] = $table;
}

if (!empty($id)) {
    $where[] = "record_id = :id";
    $params[':id'] = (int)$id;
}

if (!empty($_GET['action'])) {
    $where[] = "action = :action";
    $params[':action'] = $_GET['action'];
}

if (!empty($_GET['from'])) {
    $where[] = "change_date >= :from";
    $params[':from'] = $_GET['from'];
}

if (!empty($_GET['to'])) {
    $where[] = "change_date <= :to";
    $params[':to'] = $_GET['to'];
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("
    SELECT action, notes, change_date, changed_by, table_name, record_id
    FROM audit_log
    $whereSQL
    ORDER BY change_date DESC
");

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->execute();
$audit = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Force print-friendly layout */
header("Content-Type: text/html; charset=UTF-8");
?>

<!DOCTYPE html>
<html>
<head>
<title>Audit Trail</title>
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}
h2 {
    margin-bottom: 5px;
}
small {
    color: #555;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #333;
    padding: 6px;
    text-align: left;
}
th {
    background: #0b5e2b;
    color: #fff;
}
</style>
</head>

<body onload="window.print()">

<h2>Audit Trail</h2>
<small>
<?php if (!empty($table)): ?>
    Table: <strong><?= htmlspecialchars($table) ?></strong>
    <?php if ($id): ?> | Record ID: <strong><?= (int)$id ?></strong><?php endif; ?>
<?php else: ?>
    <strong>Complete Audit Register</strong>
    <?php if (!empty($_GET['from'])): ?> | From: <?= htmlspecialchars($_GET['from']) ?><?php endif; ?>
    <?php if (!empty($_GET['to'])): ?> | To: <?= htmlspecialchars($_GET['to']) ?><?php endif; ?>
<?php endif; ?>
</small>

<table>
<thead>
<tr>
    <th>Date</th>
    <th>Table</th>
    <th>Record ID</th>
    <th>Action</th>
    <th>Notes</th>
    <th>User</th>
</tr>
</thead>
<tbody>

<?php if (!$audit): ?>
<tr>
    <td colspan="6">No audit records found.</td>
</tr>
<?php else: ?>
<?php foreach ($audit as $a): ?>
<tr>
    <td><?= $a['change_date'] ?></td>
    <td><?= htmlspecialchars($a['table_name']) ?></td>
    <td><?= (int)$a['record_id'] ?></td>
    <td><?= htmlspecialchars($a['action']) ?></td>
    <td><?= nl2br(htmlspecialchars($a['notes'])) ?></td>
    <td><?= htmlspecialchars($a['changed_by'] ?? 'System') ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

</body>
</html>
