<?php
// admin/actions/add_staff.php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']);
  exit;
}

require_once __DIR__ . '/../../includes/db_connect.php'; // root/includes/db_connect.php (mysqli $conn)

function jerr($msg, $code=400){ http_response_code($code); echo json_encode(['status'=>'error','message'=>$msg]); exit; }

// NEW SCHEMA: Use new fields
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$role       = trim($_POST['role'] ?? 'staff'); // 'staff', 'driver', 'admin'
$pass       = $_POST['password'] ?? '';
$pass2      = $_POST['password2'] ?? '';


if ($first_name === '' || $last_name === '' || $email === '' || $pass === '' || $role === '') jerr('Please complete all required fields.');
if (!in_array($role, ['admin', 'staff', 'customer'])) jerr('Invalid role specified.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jerr('Invalid email.');
if ($pass !== $pass2) jerr('Passwords do not match.');

// Enforce unique email
$stmt = $conn->prepare("SELECT 1 FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute(); $stmt->store_result();
if ($stmt->num_rows > 0) jerr('Email already exists.');
$stmt->close();

// Create base user. Store password as hash.
$hash = password_hash($pass, PASSWORD_DEFAULT);

// NEW SCHEMA: Insert into new users table structure
$sql = "INSERT INTO users (first_name, last_name, email, phone, password, role, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssss', $first_name, $last_name, $email, $phone, $hash, $role);

if (!$stmt->execute()) jerr('Failed to insert user: ' . $stmt->error);
$userId = $stmt->insert_id;
$stmt->close();

echo json_encode(['status'=>'ok','id'=>$userId]);
?>