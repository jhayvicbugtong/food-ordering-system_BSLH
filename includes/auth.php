<?php
// auth.php

// Function to ensure the user has the correct role
function require_role($role) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the user is authenticated and if they have the right role
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // Redirect to the login page if the user doesn't have the correct role
        header("Location: /food-ordering-system_BSLH/auth/login.php");
        exit();
    }
}
?>
