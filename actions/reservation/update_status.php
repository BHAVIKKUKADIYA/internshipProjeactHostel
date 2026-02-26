<?php
/**
 * AJAX API: Update Reservation Status
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/reservation_actions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Call the function from reservation_actions.php
    $result = update_reservation_status($pdo, $id, $status);

    if ($result === true) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => is_string($result) ? $result : 'Status update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
