<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$first_name = trim($data['first_name'] ?? '');
$last_name = trim($data['last_name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Get current user data
$user_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Check email uniqueness
$email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$email_check->bind_param("si", $email, $user_id);
$email_check->execute();
if ($email_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already in use']);
    exit;
}

// Handle password change
if (!empty($new_password)) {
    if (empty($current_password)) {
        echo json_encode(['success' => false, 'message' => 'Current password is required to change password']);
        exit;
    }
    
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
        exit;
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
}

// Format phone number
if (!empty($phone)) {
    $phone = '+63' . preg_replace('/\D/', '', $phone);
}

// Update user
if (!empty($new_password)) {
    $update_query = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, updated_at = NOW() WHERE user_id = ?");
    $update_query->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $user_id);
} else {
    $update_query = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE user_id = ?");
    $update_query->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
}

if ($update_query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $conn->error]);
}
?>