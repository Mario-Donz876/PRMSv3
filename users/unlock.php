<?php
$REQUIRE_PERMISSION = 'manage_users';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pop("Invalid request.", "/users/list.php", POP_DEFAULT_DELAY_MS, "error");
    exit;
}

$target_user_id = (int)($_POST['user_id'] ?? 0);

if ($target_user_id <= 0) {
    pop("Invalid user.", "/users/list.php", POP_DEFAULT_DELAY_MS, "error");
    exit;
}

// Fetch current lock state
$stmt = $pdo->prepare("SELECT full_name, failed_attempts, lock_until FROM users WHERE user_id = ?");
$stmt->execute([$target_user_id]);
$target = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$target) {
    pop("User not found.", "/users/list.php", POP_DEFAULT_DELAY_MS, "error");
    exit;
}

// Reset lock
$pdo->prepare("
    UPDATE users
    SET failed_attempts = 0, lock_until = NULL
    WHERE user_id = ?
")->execute([$target_user_id]);

// Audit log
logAudit(
    $pdo,
    'users',
    $target_user_id,
    'ACCOUNT_UNLOCKED',
    "Account unlocked by admin (User ID: {$_SESSION['user_id']}). Previous failed attempts: {$target['failed_attempts']}"
);

pop(
    "Account for " . htmlspecialchars($target['full_name']) . " has been unlocked.",
    "/users/view.php?id=" . $target_user_id,
    2500,
    "success"
);
exit;
