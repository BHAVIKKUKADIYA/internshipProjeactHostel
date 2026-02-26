<?php
require_once '../../config/config.php';
require_once '../../actions/reservation/reservation_actions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reservation_id'])) {
    $id = $_POST['reservation_id'];
    
    $formData = [
        'id' => $id,
        'guest_name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'guest_count' => (int)($_POST['guests'] ?? 2),
        'reservation_date' => $_POST['date'] ?? date('Y-m-d'),
        'reservation_time' => $_POST['time'] ?? '18:00'
    ];

    // Basic validation
    if (empty($formData['guest_name']) || empty($formData['email']) || empty($formData['phone'])) {
        header("Location: ../../user/table_booking.php?edit_id=$id&status=error&msg=missing_fields");
        exit();
    }

    $success = update_reservation($pdo, $formData);

    if ($success) {
        // Redirect back to table_booking.php which will show the summary for this ID
        header("Location: ../../user/table_booking.php?id=$id&status=updated");
    } else {
        header("Location: ../../user/table_booking.php?edit_id=$id&status=error");
    }
    exit();
} else {
    header("Location: ../../user/table_booking.php");
    exit();
}
