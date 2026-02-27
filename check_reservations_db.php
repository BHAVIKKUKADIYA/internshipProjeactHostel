<?php
require_once 'config/config.php';
try {
    $q = $pdo->query("DESCRIBE reservations");
    while($f = $q->fetch(PDO::FETCH_ASSOC)) {
        print_r($f);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
