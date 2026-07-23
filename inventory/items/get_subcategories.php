<?php
/**
 * AJAX endpoint — returns subcategories for a given parent category.
 * GET ?category_id=<int>
 * Returns JSON: [{category_id, category_name}, ...]
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

header('Content-Type: application/json');

$parentId = (int) ($_GET['category_id'] ?? 0);
if ($parentId <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT category_id, category_name
     FROM inv_categories
     WHERE parent_category_id = ? AND is_active = 1
     ORDER BY sort_order, category_name"
);
$stmt->execute([$parentId]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
