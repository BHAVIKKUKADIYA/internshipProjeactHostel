<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/menu_actions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $data = [
        'category_id' => $_POST['category_id'],
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'image_url' => $_POST['image_url'] ?? '',
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0
    ];
    if (update_dish($pdo, $id, $data)) {
        header("Location: ../../admin/menumanage.php?success=1");
    } else {
        header("Location: ../../admin/menumanage.php?error=1");
    }
    exit;
}
?>
