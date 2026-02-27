<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/feedback_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'rating' => $_POST['rating'],
        'review_text' => $_POST['message'], // Matching the 'name' attribute in user/feedback.php
        'status' => 'pending'
    ];
    if (add_feedback($pdo, $data)) {
        header("Location: ../../user/feedback.php?success=1");
    } else {
        header("Location: ../../user/feedback.php?error=1");
    }
    exit;
}
?>
