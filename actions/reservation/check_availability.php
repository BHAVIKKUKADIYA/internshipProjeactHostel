    <?php
require_once '../../config/config.php';
require_once 'reservation_actions.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;
$exclude_id = $_GET['exclude_id'] ?? null;

if (!$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Missing date or time']);
    exit;
}

$availability = get_slot_availability($pdo, $date, $time, $exclude_id);

echo json_encode([
    'success' => true,
    'capacity' => $availability['capacity'],
    'booked' => $availability['booked'],
    'remaining' => $availability['remaining']
]);
