<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/feedback_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_name' => $_POST['name'],
        'customer_email' => $_POST['email'],
        'rating' => $_POST['rating'],
        'comment' => $_POST['message'], // Matching the 'name' attribute in user/feedback.php
        'status' => 'Pending'
    ];
    if (add_feedback($pdo, $data)) {
        header("Location: ../../user/feedback.php?success=1");
    } else {
        header("Location: ../../user/feedback.php?error=1");
    }
    exit;
}
?>
