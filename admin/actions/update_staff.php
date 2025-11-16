<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

function jerr($m,$c=400){ http_response_code($c); echo json_encode(['status'=>'error','message'=>$m]); exit; }

// NEW SCHEMA: Use user_id
$user_id = (int)($_POST['user_id'] ?? 0);
if ($user_id <= 0) jerr('Invalid ID');

// NEW SCHEMA: Use new fields
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$role       = trim($_POST['role'] ?? ''); // 'staff', 'driver', 'admin'
$npw        = (string)($_POST['new_password'] ?? '');
$npw2       = (string)($_POST['new_password2'] ?? '');

if ($first_name === '' || $last_name === '' || $email === '' || $role === '') jerr('First Name, Last Name, Email, and Role are required.');
if (!in_array($role, ['admin', 'staff', 'driver'])) jerr('Invalid role specified.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jerr('Invalid email.');
if ($npw !== '' && $npw !== $npw2) jerr('New passwords do not match.');

// Unique email (other than self)
// NEW SCHEMA: Use user_id
$stmt = $conn->prepare("SELECT user_id FROM users WHERE LOWER(email)=LOWER(?) AND user_id<>? LIMIT 1");
$stmt->bind_param('si', $email, $user_id);
$stmt->execute(); $stmt->store_result();
if ($stmt->num_rows) jerr('Email already in use by another account.');
$stmt->close();

// NEW SCHEMA: Build query with correct columns
$fields = ['first_name=?', 'last_name=?', 'email=?', 'phone=?', 'role=?'];
$params = [$first_name, $last_name, $email, $phone, $role];
$types  = 'sssss';

if ($npw !== '') {
  $hash = password_hash($npw, PASSWORD_DEFAULT);
  $fields[] = 'password=?';
  $params[] = $hash;
  $types   .= 's';
}

$params[] = $user_id;
$types   .= 'i';

// NEW SCHEMA: Update users table and filter by user_id
$sql = "UPDATE users SET ".implode(',', $fields)." WHERE user_id=? AND role IN ('admin', 'staff', 'driver') LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) jerr('Update failed.');
$stmt->close();

echo json_encode(['status'=>'ok']);
?>