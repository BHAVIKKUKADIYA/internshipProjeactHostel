<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Verify admin authentication (using the existing auth.php logic)
// Assuming check_admin() or similar exists in auth.php or functions.php
// If not explicitly defined, we check session here
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'toggle_status':
            $slot_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $status = filter_var($_POST['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            
            if ($slot_id === false || $status === null) {
                throw new Exception('Invalid parameters.');
            }

            $stmt = $pdo->prepare("UPDATE table_slots SET is_active = ? WHERE id = ?");
            $stmt->execute([$status ? 1 : 0, $slot_id]);
            
            echo json_encode(['success' => true, 'message' => 'Slot status updated successfully.']);
            break;

        case 'toggle_peak':
            $slot_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $is_peak = filter_var($_POST['is_peak'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($slot_id === false || $is_peak === null) {
                throw new Exception('Invalid parameters.');
            }

            $stmt = $pdo->prepare("UPDATE table_slots SET is_peak_hour = ? WHERE id = ?");
            $stmt->execute([$is_peak ? 1 : 0, $slot_id]);

            echo json_encode(['success' => true, 'message' => 'Peak hour status updated successfully.']);
            break;

        case 'update_capacity':
            $slot_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);

            if ($slot_id === false || $capacity === false || $capacity < 0) {
                throw new Exception('Invalid parameters.');
            }

            // Logic check: Capacity cannot be less than current bookings
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE TIME_FORMAT(reservation_time, '%h:%i %p') = (SELECT time_slot FROM table_slots WHERE id = ?) AND reservation_date = CURDATE() AND status IN ('Pending', 'Confirmed')");
            $stmt->execute([$slot_id]);
            $current = $stmt->fetchColumn();

            if ($capacity < $current) {
                throw new Exception("Capacity cannot be lower than current active bookings for today ($current).");
            }

            $stmt = $pdo->prepare("UPDATE table_slots SET capacity = ? WHERE id = ?");
            $stmt->execute([$capacity, $slot_id]);

            echo json_encode(['success' => true, 'message' => 'Capacity updated successfully.']);
            break;

        case 'bulk_update':
            $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);

            if ($capacity === false || $capacity < 0) {
                throw new Exception('Invalid capacity value. Cannot be negative.');
            }

            // check if any active slot has more live bookings for today than new capacity
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM table_slots t
                WHERE t.is_active = 1 
                AND (
                    SELECT COUNT(*) 
                    FROM reservations r 
                    WHERE TIME_FORMAT(r.reservation_time, '%h:%i %p') = t.time_slot 
                    AND r.reservation_date = CURDATE() 
                    AND r.status IN ('Pending', 'Confirmed')
                ) > ?
            ");
            $stmt->execute([$capacity]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Some active slots already have more bookings for today than the specified capacity.");
            }

            $stmt = $pdo->prepare("UPDATE table_slots SET capacity = ? WHERE is_active = 1");
            $stmt->execute([$capacity]);
            
            echo json_encode(['success' => true, 'message' => "Bulk update successful. Updated all active slots to $capacity tables."]);
            break;

        case 'reset_defaults':
            // Reset all to default capacity (30) and active status
            $stmt = $pdo->prepare("UPDATE table_slots SET capacity = 30, is_active = 1");
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'All slots have been reset to default values.']);
            break;

        case 'add_slot':
            // Add a new default slot - might need more robust logic but this is a 'fix'
            $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM table_slots");
            $stmt->execute();
            $max_order = $stmt->fetchColumn() ?: 0;
            
            $stmt = $pdo->prepare("INSERT INTO table_slots (time_slot, capacity, sort_order) VALUES ('New Slot', 30, ?)");
            $stmt->execute([$max_order + 1]);
            echo json_encode(['success' => true, 'message' => 'New slot added. Please update the label and capacity.']);
            break;

        default:
            throw new Exception('Unknown action.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
