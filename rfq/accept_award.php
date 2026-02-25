<?php
$REQUIRE_PERMISSION = 'confirm_vendor_award';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';

$rfq_id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$rfq_id || !in_array($action, ['accept','decline'])) {
    pop('Invalid request', '/rfq/list.php', POP_DEFAULT_DELAY_MS, 'error');
    exit;
}

/* SOP Step 12: Verify RFQ is awarded before accepting */
$rfqStmt = $pdo->prepare("SELECT status FROM rfqs WHERE rfq_id = ?");
$rfqStmt->execute([$rfq_id]);
$rfqStatus = $rfqStmt->fetchColumn();
if ($rfqStatus !== 'AWARDED') {
    pop('Award must be issued before vendor acceptance (SOP Step 11→12).', '/rfq/view.php?id='.$rfq_id, POP_DEFAULT_DELAY_MS, 'error');
    exit;
}

$status = ($action === 'accept') ? 'ACCEPTED' : 'DECLINED';

$pdo->prepare("
    UPDATE rfqs
    SET acceptance_status = ?
    WHERE rfq_id = ?
")->execute([$status, $rfq_id]);

logAudit($pdo, 'rfqs', $rfq_id, 'STATUS_CHANGE', 'Award ' . $status . ' by vendor');

header("Location: view.php?id=".$rfq_id);
exit;
