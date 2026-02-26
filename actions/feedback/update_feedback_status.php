<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/feedback_actions.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    if (moderate_feedback($pdo, $_GET['id'], $_GET['status'])) {
        header("Location: ../../admin/feedback.php?success=1");
    } else {
        header("Location: ../../admin/feedback.php?error=1");
    }
    exit;
}
?>
