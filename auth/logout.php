<?php
// logout.php
require_once __DIR__ . '/../includes/db_connect.php'; // Provides $BASE_URL and starts session

// Start session to destroy it
// session_start(); // No longer needed, db_connect handles it
session_unset();  // Unset all session variables
session_destroy(); // Destroy session

// Redirect the user to the customer login page (now the central login)
header('Location: ' . $BASE_URL . '/customer/auth/login.php');
exit();
?>