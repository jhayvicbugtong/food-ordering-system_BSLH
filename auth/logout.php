<?php
// logout.php
require_once __DIR__ . '/../includes/db_connect.php'; // Provides $BASE_URL and starts session

// Start session to destroy it
// session_start(); // No longer needed, db_connect handles it
session_unset();  // Unset all session variables
session_destroy(); // Destroy session

// Redirect the user to the login page
header('Location: ' . $BASE_URL . '/auth/login.php'); // FIXED
exit();
?>