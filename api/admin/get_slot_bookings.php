<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');
$time = $_GET['time'] ?? '';

if (empty($time)) {
    echo json_encode(['success' => false, 'message' => 'Time slot is required']);
    exit;
}

try {
    // Normalize time to H:i:s format to match database
    $db_time = date("H:i:s", strtotime($time));

    // Join reservations with tables to get detailed booking information
    $stmt = $pdo->prepare("
        SELECT 
            r.guest_name,
            r.phone,
            r.guest_count,
            r.status,
            r.reservation_time,
            r.table_number as table_name,
            r.id as reservation_id
        FROM reservations r
        WHERE r.reservation_date = ? 
        AND r.reservation_time = ?
        AND r.status IN ('Pending', 'Confirmed')
        ORDER BY r.reservation_time ASC
    ");
    
    $stmt->execute([$date, $db_time]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
