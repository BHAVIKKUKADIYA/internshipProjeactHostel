<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/menu_actions.php';

if (isset($_GET['id'])) {
    if (delete_dish($pdo, $_GET['id'])) {
        header("Location: ../../admin/menumanage.php?success=1");
    } else {
        header("Location: ../../admin/menumanage.php?error=1");
    }
    exit;
}
?>
