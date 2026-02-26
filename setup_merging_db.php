<?php
require_once 'config/config.php';

try {
    // 1. Create restaurant_tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_tables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        table_name VARCHAR(10) NOT NULL,
        seat_capacity INT NOT NULL,
        zone_id INT DEFAULT 1,
        is_combinable TINYINT(1) DEFAULT 1,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create reservation_tables (many-to-many)
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservation_tables (
        reservation_id INT NOT NULL,
        table_id INT NOT NULL,
        PRIMARY KEY (reservation_id, table_id)
    )");

    // 3. Seed default tables if empty
    $count = $pdo->query("SELECT COUNT(*) FROM restaurant_tables")->fetchColumn();
    if ($count == 0) {
        $tables = [
            ['T1', 2, 1], ['T2', 2, 1], ['T3', 2, 1], // Zone 1 (Nearby)
            ['T4', 4, 1], ['T5', 4, 1],             // Zone 1
            ['T6', 6, 2],                            // Zone 2 (Large)
            ['T7', 2, 2], ['T8', 2, 2],             // Zone 2
            ['T9', 4, 3], ['T10', 4, 3]             // Zone 3
        ];
        $stmt = $pdo->prepare("INSERT INTO restaurant_tables (table_name, seat_capacity, zone_id) VALUES (?, ?, ?)");
        foreach ($tables as $t) {
            $stmt->execute($t);
        }
        echo "Database tables created and seeded successfully.\n";
    } else {
        echo "Database tables already exist and contain data.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
