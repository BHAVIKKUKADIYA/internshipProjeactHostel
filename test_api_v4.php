<?php
require_once 'config/config.php';
require_once 'actions/reservation/reservation_actions.php';

$date = date('Y-m-d');
$time = '18:00:00'; // Adjust if needed

echo "Testing get_all_availabilities.php logic...\n";
$_GET['date'] = $date;
$_GET['guests'] = 1;
ob_start();
include 'actions/reservation/get_all_availabilities.php';
$avail = ob_get_clean();
echo "Availabilities: $avail\n\n";

echo "Testing get_table_status.php logic...\n";
$_GET['date'] = $date;
$_GET['time'] = '06:00 PM'; // Example slot from UI
ob_start();
include 'api/admin/get_table_status.php';
$status = ob_get_clean();
echo "Table Status: $status\n\n";
?>
