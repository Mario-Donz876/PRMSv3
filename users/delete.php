<?php
$REQUIRE_PERMISSION = 'manage_users';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';

$user_id = (int)($_POST['user_id'] ?? 0);

if (!$user_id) {
    pop('Invalid user ID', '/users/list.php', POP_DEFAULT_DELAY_MS, 'error');
    exit;
}

/* Prevent self-deletion */
if ($user_id == $_SESSION['user_id']) {
    pop('You cannot delete your own account', '/users/list.php', POP_DEFAULT_DELAY_MS, 'error');
    exit;
}

try {
    /* Start transaction */
    $pdo->beginTransaction();

    /* Fetch user details for audit */
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        pop('User not found', '/users/list.php', POP_DEFAULT_DELAY_MS, 'error');
        exit;
    }

    /* Remove foreign key references: set audit_log.changed_by to NULL */
    $stmt = $pdo->prepare("UPDATE audit_log SET changed_by = NULL WHERE changed_by = ?");
    $stmt->execute([$user_id]);

    /* Delete user */
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    /* Log the deletion */
    logAudit($pdo, 'users', $user_id, 'DELETE', "User '{$user['full_name']}' ({$user['email']}) deleted.");

    /* Commit transaction */
    $pdo->commit();

    /* Redirect with success message */
    header("Location: /users/list.php?success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('User delete failed: ' . $e->getMessage());
    pop('Error deleting user. Please try again.', '/users/list.php', POP_DEFAULT_DELAY_MS, 'error');
    exit;
}
