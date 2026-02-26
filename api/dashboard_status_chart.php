<?php
header('Content-Type: application/json');
require_once '../config/config.php';

try {
    $query = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $counts = [];
    foreach ($data as $row) {
        $labels[] = $row['status'];
        $counts[] = (int)$row['count'];
    }
    
    echo json_encode([
        'labels' => $labels,
        'counts' => $counts
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
