<?php
require_once 'config/config.php';

echo "--- TABLE: table_slots ---\n";
try {
    $stmt = $pdo->query("DESCRIBE table_slots");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n--- DATA: table_slots ---\n";
    $stmt = $pdo->query("SELECT * FROM table_slots");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "\n--- TABLE: reservations ---\n";
try {
    $stmt = $pdo->query("DESCRIBE reservations");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
