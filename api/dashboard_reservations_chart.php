<?php
header('Content-Type: application/json');
require_once '../config/config.php';

try {
    // Get daily counts for last 30 days
    $query = "SELECT 
                DATE(reservation_date) as date, 
                COUNT(*) as count 
              FROM reservations 
              WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
              GROUP BY DATE(reservation_date) 
              ORDER BY DATE(reservation_date) ASC";
    
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fill in gaps with 0
    $formatted = [];
    $start = new DateTime('-30 days');
    $end = new DateTime();
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $count = 0;
        foreach ($data as $row) {
            if ($row['date'] === $dateStr) {
                $count = (int)$row['count'];
                break;
            }
        }
        $formatted[] = ['date' => $date->format('M d'), 'count' => $count];
    }
    
    echo json_encode($formatted);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
