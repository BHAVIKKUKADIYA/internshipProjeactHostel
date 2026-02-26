<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/menu_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (add_category($pdo, $name, $slug)) {
        header("Location: ../../admin/menumanage.php?success=1");
    } else {
        header("Location: ../../admin/menumanage.php?error=1");
    }
    exit;
}
?>
