<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

// NEW SCHEMA: Use user_id
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// NEW SCHEMA: Select columns that exist in online_food_ordering_db.sql
$select = "user_id, first_name, last_name, email, phone, role, created_at";

if ($user_id > 0) {
  // NEW SCHEMA: Filter by user_id and role
  $stmt = $conn->prepare("SELECT $select FROM users WHERE role IN ('admin', 'staff', 'customer') AND user_id=? LIMIT 1");
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if ($row) echo json_encode(['status'=>'ok','row'=>$row]);
  else { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Not found']); }
  exit;
}

// NEW SCHEMA: Filter by role
$q = $conn->query("SELECT $select FROM users WHERE role IN ('admin', 'staff', 'customer') ORDER BY first_name, last_name");
$rows = [];
while ($r = $q->fetch_assoc()) $rows[] = $r;
echo json_encode(['status'=>'ok','rows'=>$rows]);
?>