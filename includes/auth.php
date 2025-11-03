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

// Additional helper functions
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['role'] ?? null;
}

function get_user_name() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['name'] ?? null;
}
?>