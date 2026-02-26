<?php
require_once '../../config/config.php';
require_once 'reservation_actions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing reservation ID']);
        exit;
    }

    if (delete_reservation($pdo, $id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database deletion failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
