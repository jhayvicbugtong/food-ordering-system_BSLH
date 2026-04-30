<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

// Input validation
$user_id = (int)($_POST['user_id'] ?? 0);
if ($user_id <= 0) { 
    http_response_code(400); 
    echo json_encode(['status'=>'error', 'message'=>'Invalid ID']); 
    exit; 
}

// Prevent deleting yourself
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    http_response_code(400);
    echo json_encode(['status'=>'error', 'message'=>'You cannot delete your own account.']);
    exit;
}

// Prepare delete statement
// Updated to include 'customer' in the allowed roles list to match manage_staff.php
$stmt = $conn->prepare("DELETE FROM users WHERE user_id=? AND role IN ('admin', 'staff', 'driver', 'customer') LIMIT 1");
$stmt->bind_param('i', $user_id);

if (!$stmt->execute()) {
    // Check for Foreign Key Constraint Fails (Error 1451)
    if ($stmt->errno === 1451) {
        http_response_code(409); // Conflict
        echo json_encode([
            'status' => 'error', 
            'message' => 'Cannot delete user: This account is linked to existing orders or records. Please deactivate the user instead.'
        ]);
    } else {
        // Generic database error
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Delete failed: ' . $stmt->error
        ]);
    }
    exit;
}

// Check if any row was actually deleted
if ($stmt->affected_rows === 0) {
    // Could happen if ID doesn't exist or role is not in the list
    // We treat it as 'ok' (idempotent) or could return an error if strict
    // For UI consistency, we'll return ok, but ideally, we'd check existence first.
}

$stmt->close();
echo json_encode(['status'=>'ok']);
?>