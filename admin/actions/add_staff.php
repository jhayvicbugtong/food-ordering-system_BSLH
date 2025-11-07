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

// Inputs
$name   = trim($_POST['full_name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$pass   = $_POST['password'] ?? '';
$pass2  = $_POST['password2'] ?? '';
$phone  = trim($_POST['phone'] ?? '');
$staff_role = trim($_POST['staff_role'] ?? 'staff');  // kitchen|cashier|rider|manager
$shift  = trim($_POST['shift'] ?? '');                // morning|mid|evening
$notes  = trim($_POST['notes'] ?? '');

if ($name === '' || $email === '' || $pass === '') jerr('Please complete required fields.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jerr('Invalid email.');
if ($pass !== $pass2) jerr('Passwords do not match.');

// Enforce unique email
$stmt = $conn->prepare("SELECT 1 FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute(); $stmt->store_result();
if ($stmt->num_rows > 0) jerr('Email already exists.');
$stmt->close();

// Create base user (role = staff). Store password as hash.
$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')");
$stmt->bind_param('sss', $name, $email, $hash);
if (!$stmt->execute()) jerr('Failed to insert user.');
$userId = $stmt->insert_id;
$stmt->close();

// Try to save extra fields if those columns exist in `users`.
$columns = [];
$res = $conn->query("SHOW COLUMNS FROM users");
if ($res) {
  while ($c = $res->fetch_assoc()) { $columns[strtolower($c['Field'])] = true; }
}

$updates = [];
$params = [];
$types  = '';

if (isset($columns['phone']) && $phone !== '')      { $updates[] = "phone=?";      $params[] = $phone;      $types.='s'; }
if (isset($columns['staff_role']) && $staff_role!==''){ $updates[] = "staff_role=?"; $params[] = $staff_role; $types.='s'; }
if (isset($columns['shift']) && $shift!=='')        { $updates[] = "shift=?";      $params[] = $shift;      $types.='s'; }
if (isset($columns['notes']) && $notes!=='')        { $updates[] = "notes=?";      $params[] = $notes;      $types.='s'; }
if (isset($columns['started_at']))                  { $updates[] = "started_at=NOW()"; }

if ($updates) {
  $sql = "UPDATE users SET ".implode(',', $updates)." WHERE id=?";
  $params[] = $userId; $types .= 'i';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $stmt->close();
}

echo json_encode(['status'=>'ok','id'=>$userId]);
