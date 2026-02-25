<?php
function policyViolation($pdo, $action, $notes = '') {

    // Log to audit trail
    $stmt = $pdo->prepare("
        INSERT INTO audit_log
        (table_name, action, changed_by, notes)
        VALUES ('POLICY', ?, ?, ?)
    ");

    $stmt->execute([
        $action,
        $_SESSION['user_id'] ?? null,
        $notes
    ]);

    // Set friendly error message
    $_SESSION['error'] = $notes ?: 'Action not permitted by policy.';

    // Redirect back safely
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/dashboard/index.php'));
    exit;
}

function assertEditableRequest(array $request)
{
    if (strtoupper($request['status']) !== 'DRAFT') {
        pop(
            "This request is locked and can no longer be modified.",
            "/procurement/view.php?id=" . $request['request_id']
        );
        exit;
    }
}



