<?php
require_once 'config/config.php';
try {
    echo "TABLE_SLOTS DATA:\n";
    $q = $pdo->query("SELECT * FROM table_slots");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) { print_r($f); }
    
    echo "\nTABLES DATA:\n";
    $q = $pdo->query("SELECT * FROM tables");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) { print_r($f); }
    
    echo "\nRESERVATIONS COUNT FOR 2026-02-27:\n";
    $q = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_date = ?");
    $q->execute(['2026-02-27']);
    echo $q->fetchColumn() . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
