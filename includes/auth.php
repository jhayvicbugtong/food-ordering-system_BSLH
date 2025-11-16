<?php
// auth.php

// Function to ensure the user has the correct role(s)
function require_role($role) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_role = $_SESSION['role'] ?? null;

    // --- MODIFIED: Allow $role to be an array ---
    $is_authorized = false;
    if (is_array($role)) {
        $is_authorized = $user_role && in_array($user_role, $role);
    } else {
        $is_authorized = ($user_role === $role);
    }
    // --- END MODIFICATION ---

    // Check if the user is authenticated and if they have the right role
    if (!$is_authorized) {
        // Redirect to the login page
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