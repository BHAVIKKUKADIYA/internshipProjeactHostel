<?php
/**
 * API: Get All Reservations
 * Returns all table bookings in JSON format
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../actions/reservation/reservation_actions.php';

try {
    $reservations = get_all_reservations($pdo);
    echo json_encode([
        'status' => 'success',
        'count' => count($reservations),
        'data' => $reservations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve reservations: ' . $e->getMessage()
    ]);
}
?>
