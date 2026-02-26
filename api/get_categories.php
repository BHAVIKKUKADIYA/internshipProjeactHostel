<?php
/**
 * API: Get All Categories
 * Returns categories in a standardized JSON format
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../actions/menu/menu_actions.php';

try {
    $categories = get_all_categories($pdo);
    echo json_encode([
        'status' => 'success',
        'count' => count($categories),
        'data' => $categories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve categories: ' . $e->getMessage()
    ]);
}
?>
