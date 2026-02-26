<?php
/**
 * API: Get All Menu Items (Dishes)
 * Returns dishes with category names in JSON format
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../actions/menu/menu_actions.php';

try {
    $dishes = get_all_dishes($pdo);
    echo json_encode([
        'status' => 'success',
        'count' => count($dishes),
        'data' => $dishes
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve menu items: ' . $e->getMessage()
    ]);
}
?>
