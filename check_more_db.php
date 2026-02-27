<?php
require_once 'config/config.php';
try {
    echo "TABLES:\n";
    $q = $pdo->query("DESCRIBE tables");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) { print_r($f); }
    
    echo "\nTABLE_SLOTS:\n";
    $q = $pdo->query("DESCRIBE table_slots");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) { print_r($f); }
    
    echo "\nRESERVATION_TABLES:\n";
    $q = $pdo->query("DESCRIBE reservation_tables");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) { print_r($f); }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
