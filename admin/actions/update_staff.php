<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

function jerr($m,$c=400){ http_response_code($c); echo json_encode(['status'=>'error','message'=>$m]); exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) jerr('Invalid ID');

$name  = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role2 = trim($_POST['staff_role'] ?? '');
$shift = trim($_POST['shift'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$npw   = (string)($_POST['new_password'] ?? '');
$npw2  = (string)($_POST['new_password2'] ?? '');

if ($name === '' || $email === '') jerr('Name and Email are required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jerr('Invalid email.');
if ($npw !== '' && $npw !== $npw2) jerr('New passwords do not match.');

// Unique email (other than self)
$stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=LOWER(?) AND id<>? LIMIT 1");
$stmt->bind_param('si', $email, $id);
$stmt->execute(); $stmt->store_result();
if ($stmt->num_rows) jerr('Email already in use by another account.');
$stmt->close();

// Detect optional columns
$cols = [];
if ($res = $conn->query("SHOW COLUMNS FROM users")) {
  while ($c = $res->fetch_assoc()) $cols[strtolower($c['Field'])] = true;
}

$fields = ['name=?','email=?'];
$params = [$name, $email];
$types  = 'ss';

if (isset($cols['phone']))      { $fields[]='phone=?';      $params[]=$phone; $types.='s'; }
if (isset($cols['staff_role'])) { $fields[]='staff_role=?'; $params[]=$role2; $types.='s'; }
if (isset($cols['shift']))      { $fields[]='shift=?';      $params[]=$shift; $types.='s'; }
if (isset($cols['notes']))      { $fields[]='notes=?';      $params[]=$notes; $types.='s'; }

if ($npw !== '') {
  $hash = password_hash($npw, PASSWORD_DEFAULT);
  $fields[] = 'password=?';
  $params[] = $hash;
  $types   .= 's';
}

$params[] = $id;
$types   .= 'i';

$sql = "UPDATE users SET ".implode(',', $fields)." WHERE id=? AND role='staff' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) jerr('Update failed.');
$stmt->close();

echo json_encode(['status'=>'ok']);
