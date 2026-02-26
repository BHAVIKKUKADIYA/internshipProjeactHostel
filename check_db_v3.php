<?php
require_once 'config/config.php';
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES: " . implode(', ', $tables) . "\n\n";
    foreach ($tables as $table) {
        echo "COLUMNS for $table:\n";
        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo " - {$c['Field']} ({$c['Type']})\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
