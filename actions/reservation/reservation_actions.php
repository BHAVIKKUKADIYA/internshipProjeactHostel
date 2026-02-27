    <?php
/**
 * Reservation Actions
 * Handles table bookings and management
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Get all reservations with optional filtering
 */
function get_all_reservations($pdo, $filters = []) {
    try {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        $params = [];
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND reservation_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['status']) && $filters['status'] !== 'All Statuses') {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['guest_count'])) {
            if ($filters['guest_count'] === '5+') {
                $sql .= " AND guest_count >= 5";
            } else {
                $sql .= " AND guest_count = ?";
                $params[] = (int)$filters['guest_count'];
            }
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (guest_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
        }
        
        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add a new reservation
 */
function add_reservation($pdo, $data) {
    try {
        $pdo->beginTransaction();

        // Normalize time
        if (preg_match('/(0[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)/i', $data['reservation_time'])) {
            $data['reservation_time'] = date("H:i:s", strtotime($data['reservation_time']));
        }

        // 0. Check capacity before anything else
        $availability = get_slot_availability($pdo, $data['reservation_date'], $data['reservation_time']);
        if ($availability['remaining'] <= 0) {
            error_log("Reservation Failed: Slot Full for " . $data['reservation_date'] . " " . $data['reservation_time']);
            $pdo->rollBack();
            return false;
        }

        // 1. Find best table assignment
        $assignment = find_best_table_assignment($pdo, $data['reservation_date'], $data['reservation_time'], $data['guest_count']);
        
        // If no automated assignment, default to table 1 or first available (Avoid failing)
        if (!$assignment) {
            $stmt_fallback = $pdo->query("SELECT id, table_name FROM tables WHERE is_active = 1 LIMIT 1");
            $fallback = $stmt_fallback->fetch();
            if ($fallback) {
                $assignment = [['id' => $fallback['id'], 'table_name' => $fallback['table_name']]];
            } else {
                error_log("Reservation Failed: No active tables found.");
                $pdo->rollBack();
                return false;
            }
        }

        $data['table_number'] = implode(', ', array_map(function($t) { return $t['table_name']; }, $assignment));
        $data['table_id'] = $assignment[0]['id']; // Primary table reference

        // Get slot_id
        $stmt_slot = $pdo->prepare("SELECT id FROM table_slots WHERE time_slot = ? OR time_slot = ?");
        $display_time = date("h:i A", strtotime($data['reservation_time']));
        $stmt_slot->execute([$data['reservation_time'], $display_time]);
        $data['slot_id'] = $stmt_slot->fetchColumn() ?: null;

        // 2. Insert into reservations
        $sql = "INSERT INTO reservations (slot_id, table_id, guest_name, email, phone, reservation_date, reservation_time, guest_count, table_number, status, special_requests) 
                VALUES (:slot_id, :table_id, :guest_name, :email, :phone, :reservation_date, :reservation_time, :guest_count, :table_number, :status, :special_requests)";
        $stmt = $pdo->prepare($sql);
        
        // Execute with only relevant keys to avoid PDO parameter mismatch
        $exec_params = [
            ':slot_id' => $data['slot_id'],
            ':table_id' => $data['table_id'],
            ':guest_name' => $data['guest_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'],
            ':guest_count' => $data['guest_count'],
            ':table_number' => $data['table_number'],
            ':status' => $data['status'] ?? 'Confirmed',
            ':special_requests' => $data['special_requests'] ?? ''
        ];
        
        if (!$stmt->execute($exec_params)) {
             error_log("Reservation SQL Error: " . implode(" ", $stmt->errorInfo()));
             throw new Exception("SQL execution failed");
        }
        $reservation_id = $pdo->lastInsertId();

        // 3. Insert into reservation_tables (for merging support)
        // Check if table exists first to avoid crash if migration failed
        $table_check = $pdo->query("SHOW TABLES LIKE 'reservation_tables'")->fetch();
        if ($table_check) {
            $stmt_table = $pdo->prepare("INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)");
            foreach ($assignment as $table) {
                $stmt_table->execute([$reservation_id, $table['id']]);
            }
        }

        $pdo->commit();
        sync_all_slot_counts($pdo, $data['reservation_date']);
        return true;
    } catch (Exception $e) {
        error_log("Reservation Error: " . $e->getMessage());
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}



/**
 * Get reservation stats
 */
function get_reservation_stats($pdo) {
    try {
        $stats = [];
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Pending'")->fetchColumn();
        $stats['confirmed'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Confirmed'")->fetchColumn();
        return $stats;
    } catch (PDOException $e) {
        return ['total' => 0, 'pending' => 0, 'confirmed' => 0];
    }
}
/**
 * Get reservations by contact details (Email OR Phone)
 */
function get_reservations_by_contact($pdo, $email, $phone) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE email = ? OR phone = ? ORDER BY reservation_date DESC, reservation_time DESC");
        $stmt->execute([$email, $phone]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
/**
 * Delete a reservation
 */
function delete_reservation($pdo, $id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT reservation_date FROM reservations WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $date = $stmt->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $res = $stmt->execute([$id]);
        
        $pdo->commit();

        if ($res && $date) {
            sync_all_slot_counts($pdo, $date);
        }
        return $res;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

/**
 * Update reservation status with pro-level logic
 */
function update_reservation_status($pdo, $id, $status) {
    try {
        $pdo->beginTransaction();

        // 1. Get reservation details
        $stmt = $pdo->prepare("SELECT reservation_date, reservation_time, status as current_status FROM reservations WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $res_data = $stmt->fetch();

        if (!$res_data) {
            throw new Exception("Reservation not found");
        }

        $date = $res_data['reservation_date'];
        $time = $res_data['reservation_time'];
        $current_status = $res_data['current_status'];

        // 2. If confirming, check for overbooking
        if ($status === 'Confirmed' && $current_status !== 'Confirmed') {
            $availability = get_slot_availability($pdo, $date, $time);
            if ($availability['remaining'] <= 0) {
                throw new Exception("Slot Full — Cannot Confirm");
            }
        }

        // 3. Update status
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $res = $stmt->execute([$status, $id]);

        $pdo->commit();

        // 4. Always sync counts after status change to keep table_slots accurate
        sync_all_slot_counts($pdo, $date);

        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // Rethrow or return false? The API needs the message.
        // We'll return the message for the API to handle.
        return $e->getMessage();
    }
}

/**
 * Get active time slots
 */
function get_active_slots($pdo, $admin = false) {
    try {
        $sql = $admin ? "SELECT * FROM table_slots ORDER BY sort_order ASC" : "SELECT * FROM table_slots WHERE is_active = 1 ORDER BY sort_order ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Sync slot counts for a specific date
 */
function sync_all_slot_counts($pdo, $date) {
    try {
        $slots = $pdo->query("SELECT id, time_slot FROM table_slots")->fetchAll();
        foreach ($slots as $slot) {
            $db_time = date("H:i:s", strtotime($slot['time_slot']));
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT rt.table_id) 
                FROM reservation_tables rt
                JOIN reservations r ON r.id = rt.reservation_id
                WHERE r.reservation_date = ? AND r.reservation_time = ? 
                AND r.status IN ('Pending', 'Confirmed')
            ");
            $stmt->execute([$date, $db_time]);
            $count = (int)$stmt->fetchColumn();
            
            $update = $pdo->prepare("UPDATE table_slots SET current_bookings = ? WHERE id = ?");
            $update->execute([$count, $slot['id']]);
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get availability for a specific slot on a date
 */
function get_slot_availability($pdo, $date, $time, $exclude_id = null) {
    try {
        // Get max capacity for this slot (Handle both 24h and 12h formats in table_slots)
        $stmt = $pdo->prepare("SELECT capacity FROM table_slots WHERE time_slot = ? OR time_slot = ?");
        $display_time = date("h:i A", strtotime($time));
        $stmt->execute([$time, $display_time]);
        $capacity = $stmt->fetchColumn() ?: 0;

        // Get active bookings for this date/time (TABLE-BASED: count number of reservations)
        $sql_booked_tables = "
            SELECT COUNT(DISTINCT rt.table_id) 
            FROM reservation_tables rt
            JOIN reservations r ON r.id = rt.reservation_id
            WHERE r.reservation_date = ? AND r.reservation_time = ? 
            AND r.status IN ('Pending', 'Confirmed')
        ";
        $params = [$date, $time];

        if ($exclude_id) {
            $sql_booked_tables .= " AND r.id != ?";
            $params[] = $exclude_id;
        }

        $stmt_booked = $pdo->prepare($sql_booked_tables);
        $stmt_booked->execute($params);
        $booked = $stmt_booked->fetchColumn() ?: 0;

        return [
            'capacity' => (int)$capacity,
            'booked' => (int)$booked,
            'remaining' => (int)($capacity - $booked)
        ];
    } catch (PDOException $e) {
        return ['capacity' => 0, 'booked' => 0, 'remaining' => 0];
    }
}
/**
 * Get a single reservation by ID
 */
function get_reservation_by_id($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update an existing reservation
 */
function update_reservation($pdo, $data) {
    try {
        // Normalize time if it's in 12-hour format
        if (isset($data['reservation_time']) && preg_match('/(0[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)/i', $data['reservation_time'])) {
            $data['reservation_time'] = date("H:i:s", strtotime($data['reservation_time']));
        }

        $sql = "UPDATE reservations SET 
                guest_name = :guest_name, 
                email = :email, 
                phone = :phone, 
                reservation_date = :reservation_date, 
                reservation_time = :reservation_time, 
                guest_count = :guest_count,
                special_requests = :special_requests
                WHERE id = :id";
        
        $pdo->beginTransaction();

        $stmt = $pdo->prepare($sql);
        
        // Ensure only allowed fields are passed
        $params = [
            'guest_name' => $data['guest_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'reservation_date' => $data['reservation_date'],
            'reservation_time' => $data['reservation_time'],
            'guest_count' => $data['guest_count'],
            'special_requests' => $data['special_requests'],
            'id' => $data['id']
        ];
        
        $res = $stmt->execute($params);
        $pdo->commit();

        if ($res) {
            sync_all_slot_counts($pdo, $data['reservation_date']);
        }
        return $res;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

/**
 * Find the best table assignment (single or merged)
 */
function find_best_table_assignment($pdo, $date, $time, $guests) {
    try {
        // Normalize time for DB lookup
        $db_time = date("H:i:s", strtotime($time));

        // 1. Get all available tables for this slot
        $stmt = $pdo->prepare("
            SELECT t.* FROM tables t
            WHERE t.is_active = 1
            AND t.id NOT IN (
                SELECT rt.table_id FROM reservation_tables rt
                JOIN reservations r ON r.id = rt.reservation_id
                WHERE r.reservation_date = ? AND r.reservation_time = ?
                AND r.status IN ('Pending', 'Confirmed')
            )
        ");
        $stmt->execute([$date, $db_time]);
        $available_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($available_tables)) return null;

        // 2. Try single table match first (Smallest table >= guests)
        $single_matches = array_filter($available_tables, function($t) use ($guests) {
            return $t['capacity'] >= $guests;
        });

        if (!empty($single_matches)) {
            usort($single_matches, function($a, $b) {
                return $a['capacity'] - $b['capacity'];
            });
            return [$single_matches[0]];
        }

        // 3. Try table combinations (within same zone)
        $zones = [];
        foreach ($available_tables as $t) {
            if ($t['is_combinable']) {
                $zones[$t['zone_id']][] = $t;
            }
        }

        $best_combination = null;
        $min_extra_seats = PHP_INT_MAX;
        $min_tables_count = PHP_INT_MAX;

        foreach ($zones as $zone_tables) {
            $combinations = [];
            find_combinations($zone_tables, $guests, 0, [], $combinations);

            foreach ($combinations as $combo) {
                $capacity = array_sum(array_column($combo, 'seat_capacity'));
                $extra_seats = $capacity - $guests;
                $tables_count = count($combo);

                if ($extra_seats < $min_extra_seats || 
                   ($extra_seats == $min_extra_seats && $tables_count < $min_tables_count)) {
                    $min_extra_seats = $extra_seats;
                    $min_tables_count = $tables_count;
                    $best_combination = $combo;
                }
            }
        }

        return $best_combination;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Recursive helper to find combinations
 */
function find_combinations($tables, $target, $index, $current, &$results) {
    $current_cap = array_sum(array_column($current, 'seat_capacity'));
    if ($current_cap >= $target) {
        $results[] = $current;
        return;
    }

    for ($i = $index; $i < count($tables); $i++) {
        $current[] = $tables[$i];
        find_combinations($tables, $target, $i + 1, $current, $results);
        array_pop($current);
    }
}

/**
 * Get visual table occupancy for a specific slot (Upgraded for merging)
 */
function get_table_occupancy($pdo, $date, $time) {
    try {
        // Normalize time
        $db_time = date("H:i:s", strtotime($time));

        // Get all active tables
        $stmt = $pdo->query("SELECT id, table_name as name FROM tables WHERE is_active = 1");
        $all_tables = $stmt->fetchAll();

        // Get booked table IDs
        $stmt_booked = $pdo->prepare("
            SELECT table_id FROM reservation_tables rt
            JOIN reservations r ON r.id = rt.reservation_id
            WHERE r.reservation_date = ? AND r.reservation_time = ?
            AND r.status IN ('Pending', 'Confirmed')
        ");
        $stmt_booked->execute([$date, $db_time]);
        $booked_ids = $stmt_booked->fetchAll(PDO::FETCH_COLUMN);

        $tables_list = [];
        $booked_count = 0;
        foreach ($all_tables as $t) {
            $is_booked = in_array($t['id'], $booked_ids);
            if ($is_booked) $booked_count++;
            
            $tables_list[] = [
                'id' => (int)$t['id'],
                'name' => $t['name'],
                'status' => $is_booked ? 'booked' : 'available'
            ];
        }

        $total_tables = count($all_tables);
        return [
            'total_tables' => $total_tables,
            'booked_tables' => $booked_count,
            'available_tables' => $total_tables - $booked_count,
            'tables' => $tables_list
        ];
    } catch (PDOException $e) {
        return [
            'total_tables' => 0,
            'booked_tables' => 0,
            'available_tables' => 0,
            'tables' => []
        ];
    }
}

/**
 * Compatibility helper (deprecated but kept for UI calls)
 */
function find_available_table($pdo, $date, $time) {
    $assignment = find_best_table_assignment($pdo, $date, $time, 2); // Default 2 guests
    return $assignment ? $assignment[0]['id'] : null;
}
?>
