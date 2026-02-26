<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Basic authentication check for admin pages
 * Redirects to login if not authenticated
 */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../admin/login.php");
    exit;
}
?>
