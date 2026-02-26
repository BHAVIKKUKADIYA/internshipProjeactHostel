<?php
require_once '../../config/config.php';
require_once '../reservation/reservation_actions.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');
$time = $_GET['time'] ?? '';

if (empty($time)) {
    echo json_encode(['success' => false, 'message' => 'Time slot is required']);
    exit;
}

try {
    $data = get_table_occupancy($pdo, $date, $time);
    echo json_encode(array_merge(['success' => true], $data));
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
