<?php
/**
 * API: Get All Feedback
 * Returns customer reviews and ratings in JSON format
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../actions/feedback/feedback_actions.php';

try {
    $feedback = get_all_feedback($pdo);
    echo json_encode([
        'status' => 'success',
        'count' => count($feedback),
        'data' => $feedback
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve feedback: ' . $e->getMessage()
    ]);
}
?>
