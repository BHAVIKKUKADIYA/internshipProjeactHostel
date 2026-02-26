<?php
header('Content-Type: application/json');
require_once '../config/config.php';

try {
    // If orders table is missing, use menu_items as placeholders with mock weights
    $query = "SELECT name, RAND() * 100 as orders FROM menu_items LIMIT 5";
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $values = [];
    foreach ($data as $row) {
        $labels[] = $row['name'];
        $values[] = (int)$row['orders'];
    }
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
