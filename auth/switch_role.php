<?php
/**
 * Switch Role Endpoint
 * 
 * POST /auth/switch_role.php
 *   action = 'switch' | 'revert'
 *   acting_role_id = int (required when action=switch)
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/acting_roles.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    header('Location: /auth/login.php');
    exit;
}

if ($action === 'switch') {
    $actingRoleId = (int)($_POST['acting_role_id'] ?? 0);

    if (!$actingRoleId) {
        pop('Invalid role selected.', '/dashboard/index.php', POP_DEFAULT_DELAY_MS, 'error');
        exit;
    }

    if (switchToActingRole($pdo, $userId, $actingRoleId)) {
        $roleName = $_SESSION['role_name'];
        pop("Now acting as: $roleName", '/dashboard/index.php', POP_DEFAULT_DELAY_MS, 'success');
    } else {
        pop('You do not have an active acting assignment for that role.', '/dashboard/index.php', POP_DEFAULT_DELAY_MS, 'error');
    }
    exit;
}

if ($action === 'revert') {
    $primaryName = getPrimaryRoleName();
    revertToPrimaryRole($pdo, $userId);
    pop("Reverted to primary role: $primaryName", '/dashboard/index.php', POP_DEFAULT_DELAY_MS, 'success');
    exit;
}

header('Location: /dashboard/index.php');
exit;
