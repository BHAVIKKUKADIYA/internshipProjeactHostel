<?php
/**
 * AJAX API: Get All Time Slots Availabilities
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/reservation_actions.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');
$guests = (int)($_GET['guests'] ?? 2);
$exclude_id = $_GET['exclude_id'] ?? null;
$isAdmin = isset($_GET['admin']) && $_GET['admin'] == '1';

try {
    // 1. Sync slot counts for the requested date to ensure accuracy
    sync_all_slot_counts($pdo, $date);

    // 2. Fetch slots from database
    $slots_data = get_active_slots($pdo, $isAdmin);

    $formatted_slots = [];
    $total_tables = 0;
    $booked_today = 0;
    $peak_slots = [];

    foreach ($slots_data as $slot) {
        $time_slot = $slot['time_slot'];
        $capacity = (int)$slot['capacity'];
        $booked = (int)$slot['current_bookings'];
        $remain = max(0, $capacity - $booked);
        $percent = $capacity > 0 ? round(($booked / $capacity) * 100) : 0;
        
        $formatted_slots[] = [
            'id' => $slot['id'],
            'time' => $time_slot,
            'capacity' => $capacity,
            'booked' => $booked,
            'remaining' => $remain,
            'percent' => $percent,
            'is_active' => (bool)$slot['is_active'],
            'is_peak' => (bool)$slot['is_peak_hour'],
            'assignment_possible' => ($remain > 0)
        ];

        // For summary stats if needed (like in admin tableslot.php)
        if ($slot['is_active']) {
            $total_tables += $capacity;
            $booked_today += $booked;
            if ($slot['is_peak_hour']) {
                $peak_slots[] = $time_slot;
            }
        }
    }

    $response = [
        'success' => true,
        'date' => $date,
        'slots' => $formatted_slots
    ];

    if ($isAdmin) {
        $response['summary_stats'] = [
            'total_tables' => $total_tables,
            'booked_today' => $booked_today,
            'available_today' => max(0, $total_tables - $booked_today),
            'peak_display' => !empty($peak_slots) ? implode(', ', array_slice($peak_slots, 0, 2)) : 'None'
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_all_availabilities.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load time slots: ' . $e->getMessage()
    ]);
}
