<?php
session_start();
include 'includes/db.php';
include 'includes/session_manager.php';

// Save session data before logout
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
    saveUserSessionData($_SESSION['user_id']);
}

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>