<?php
/**
 * Admin API: Update Reservation Status
 * Path: /admin/actions/update_reservation_status.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$reservation_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    $new_status = ($action === 'confirm') ? 'Confirmed' : 'Cancelled';
    
    try {
        // Validate reservation exists and current status allows the update
        $stmt = $pdo->prepare("SELECT status FROM reservations WHERE id = ?");
        $stmt->execute([$reservation_id]);
        $current_status = $stmt->fetchColumn();

        if (!$current_status) {
            echo json_encode(['success' => false, 'message' => 'Reservation not found']);
            exit;
        }

        if ($current_status === $new_status) {
            echo json_encode(['success' => false, 'message' => 'Reservation already ' . strtolower($new_status)]);
            exit;
        }

        // Logic Check: Cancel only allowed for pending/confirmed
        if ($action === 'cancel' && !in_array(strtolower($current_status), ['pending', 'confirmed'])) {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel ' . strtolower($current_status) . ' reservation']);
            exit;
        }

        // UPDATE
        $sql = "UPDATE reservations SET status = ? WHERE id = ?";
        if ($action === 'cancel') {
            $sql = "UPDATE reservations SET status='Cancelled' WHERE id=? AND status IN ('Pending','Confirmed')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$reservation_id]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_status, $reservation_id]);
        }

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'new_status' => strtolower($new_status),
                'display_status' => $new_status
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or invalid transition']);
        }

    } catch (PDOException $e) {
        error_log("Admin Status Update Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
