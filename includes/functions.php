<?php
/**
 * Utility Functions
 * LUXE (KUKI Restorant)
 */

/**
 * Clean data for output
 */
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function format_price($amount) {
    return "₹ " . number_format($amount, 2);
}

/**
 * Check if user is logged in (admin)
 */
function is_logged_in() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect to page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}
?>
