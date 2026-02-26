<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$report_type = $_GET['report_type'] ?? '';

try {
    switch ($report_type) {
        case 'daily':
            $date = $_GET['date'] ?? date('Y-m-d');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_date = ?");
            $stmt->execute([$date]);
            echo json_encode(['count' => $stmt->fetchColumn() ?: 0]);
            break;

        case 'monthly':
            $month_str = $_GET['month'] ?? date('Y-m');
            $year = date('Y', strtotime($month_str));
            $month = date('m', strtotime($month_str));
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = ? AND YEAR(reservation_date) = ?");
            $stmt->execute([$month, $year]);
            $this_month = $stmt->fetchColumn() ?: 0;
            
            $last_month_str = date('Y-m', strtotime($month_str . ' -1 month'));
            $lyear = date('Y', strtotime($last_month_str));
            $lmonth = date('m', strtotime($last_month_str));
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE MONTH(reservation_date) = ? AND YEAR(reservation_date) = ?");
            $stmt->execute([$lmonth, $lyear]);
            $last_month = $stmt->fetchColumn() ?: 0;
            
            $trend = $last_month > 0 ? round((($this_month - $last_month) / $last_month) * 100) : 0;
            echo json_encode(['trend' => $trend]);
            break;

        case 'menu':
            $limit = (int)($_GET['limit'] ?? 10);
            try {
                // Try to get actual menu items if table exists
                $stmt = $pdo->prepare("SELECT name FROM menu_items LIMIT ?");
                $stmt->execute([$limit]);
                $items = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($items)) {
                    $items = ['Wagyu Beef', 'Grilled Salmon', 'Truffle Pasta', 'Lobster Bisque', 'Duck Confit'];
                }
            } catch (PDOException $e) {
                // Fallback to mock data if menu_items doesn't exist
                $items = ['Wagyu Beef', 'Grilled Salmon', 'Truffle Pasta', 'Lobster Bisque', 'Duck Confit'];
            }
            echo json_encode(['items' => array_slice($items, 0, min($limit, count($items)))]);
            break;

        case 'analytics':
            $start = $_GET['start'] ?? '17:00';
            $end = $_GET['end'] ?? '23:00';
            $stmt = $pdo->prepare("SELECT reservation_time, COUNT(*) as count 
                                 FROM reservations 
                                 WHERE reservation_time BETWEEN ? AND ? 
                                 GROUP BY reservation_time 
                                 ORDER BY count DESC LIMIT 1");
            $stmt->execute([$start, $end]);
            $peak = $stmt->fetch();
            echo json_encode(['peak_hour' => $peak ? date("h:i A", strtotime($peak['reservation_time'])) : 'N/A']);
            break;

        case 'loyalty':
            $period = (int)($_GET['period'] ?? 3);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT email FROM reservations WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) GROUP BY email HAVING COUNT(*) > 1) as repeats");
            $stmt->execute([$period]);
            $repeat_count = $stmt->fetchColumn() ?: 0;
            
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT email) FROM reservations WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)");
            $stmt->execute([$period]);
            $total_customers = $stmt->fetchColumn() ?: 1;
            
            $percent = round(($repeat_count / $total_customers) * 100);
            echo json_encode(['percent' => $percent]);
            break;

        default:
            echo json_encode(['error' => 'Invalid report type']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
