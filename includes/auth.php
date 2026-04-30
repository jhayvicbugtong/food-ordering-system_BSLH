<?php
// includes/auth.php

// Ensure database connection and configuration (like $BASE_URL) are loaded
require_once __DIR__ . '/db_connect.php';

// Function to ensure the user has the correct role(s)
function require_role($role) {
    // Access the global $BASE_URL defined in db_connect.php
    global $BASE_URL;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_role = $_SESSION['role'] ?? null;

    // Allow $role to be an array or a single string
    $is_authorized = false;
    if (is_array($role)) {
        $is_authorized = $user_role && in_array($user_role, $role);
    } else {
        $is_authorized = ($user_role === $role);
    }

    // Check if the user is authenticated and if they have the right role
    if (!$is_authorized) {
        // Redirect to the customer login page using dynamic Base URL
        header("Location: " . $BASE_URL . "/customer/auth/login.php");
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