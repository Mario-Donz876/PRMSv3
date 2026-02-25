<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require a numeric GET ID
 */
function require_print_id(string $key): int
{
    $val = $_GET[$key] ?? null;
    if (!is_numeric($val) || (int)$val <= 0) {
        http_response_code(400);
        exit("Missing {$key}");
    }
    return (int)$val;
}

/**
 * Require role
 */
function require_print_role(array $allowed)
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed, true)) {
        http_response_code(403);
        exit("You are not authorized to print this document.");
    }
}
