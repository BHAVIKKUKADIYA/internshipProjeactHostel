<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/reservation_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'guest_name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'reservation_date' => $_POST['date'] ?? '',
        'reservation_time' => $_POST['time'] ?? '',
        'guest_count' => $_POST['guests'] ?? 2,
        'table_number' => '1',
        'status' => 'Pending',
        'special_requests' => $_POST['message'] ?? ''
    ];
    if (add_reservation($pdo, $data)) {
        header("Location: ../../user/food_menu.php?reservation=success");
    } else {
        header("Location: ../../user/food_menu.php?reservation=error");
    }
    exit;
}
?>
