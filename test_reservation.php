<?php
session_start();
require_once 'config/config.php';
require_once 'actions/reservation/reservation_actions.php';

echo "Testing Reservation Failure Cause...\n";

$data = [
    'guest_name' => 'Debug User',
    'email' => 'debug@example.com',
    'phone' => '1234567890',
    'guest_count' => 2,
    'reservation_date' => date('Y-m-d', strtotime('+1 day')),
    'reservation_time' => '19:00', // Normalizes to 19:00:00
    'status' => 'Pending',
    'special_requests' => 'Test'
];

if (add_reservation($pdo, $data)) {
    echo "SUCCESS: Reservation worked!\n";
} else {
    echo "FAILURE: Reservation failed. Check error log or session.\n";
    if (isset($_SESSION['reservation_error'])) {
        echo "Error Detail: " . $_SESSION['reservation_error'] . "\n";
    }
}
?>
