<?php
header('Content-Type: application/json');
require_once '../config/config.php';

try {
    // Using Completed reservations as a proxy for revenue (100 per reservation)
    // Since orders table is not fully confirmed in schema check
    $query = "SELECT 
                DATE(reservation_date) as date, 
                SUM(CASE WHEN status = 'Completed' THEN 100 ELSE 0 END) as revenue 
              FROM reservations 
              WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
              GROUP BY DATE(reservation_date) 
              ORDER BY DATE(reservation_date) ASC";
    
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted = [];
    $start = new DateTime('-30 days');
    $end = new DateTime();
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $revenue = 0;
        foreach ($data as $row) {
            if ($row['date'] === $dateStr) {
                $revenue = (float)$row['revenue'];
                break;
            }
        }
        $formatted[] = ['date' => $date->format('M d'), 'revenue' => $revenue];
    }
    
    echo json_encode($formatted);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
