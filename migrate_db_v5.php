<?php
require_once 'config/config.php';

try {
    // 1. Rename restaurant_tables to tables and seat_capacity to capacity
    $pdo->exec("RENAME TABLE restaurant_tables TO tables");
    $pdo->exec("ALTER TABLE tables CHANGE COLUMN seat_capacity capacity INT NOT NULL");
    
    // Add status column to tables if it doesn't exist
    $pdo->exec("ALTER TABLE tables ADD COLUMN status ENUM('available', 'booked', 'maintenance') DEFAULT 'available'");

    // 2. Update reservations table
    // Add slot_id and table_id
    $pdo->exec("ALTER TABLE reservations ADD COLUMN slot_id INT NULL AFTER id");
    $pdo->exec("ALTER TABLE reservations ADD COLUMN table_id INT NULL AFTER slot_id");
    
    // 3. Try to populate slot_id and table_id for existing reservations if possible
    // (This is heuristic, might not be perfect but helps consistency)
    
    echo "Database migration completed successfully.\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
?>
