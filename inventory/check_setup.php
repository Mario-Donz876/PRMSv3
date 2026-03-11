<?php
/**
 * Include this file in every inventory page (after db.php) to ensure
 * the migration has been applied. Redirects to the dashboard setup
 * message when tables are missing.
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/InventoryService.php';

if (!inventoryTablesExist($pdo)) {
    header('Location: /inventory/dashboard.php');
    exit;
}
