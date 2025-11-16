<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

// NEW SCHEMA: Use user_id
$user_id = (int)($_POST['user_id'] ?? 0);
if ($user_id <= 0) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

// NEW SCHEMA: Delete from users by user_id
$stmt = $conn->prepare("DELETE FROM users WHERE user_id=? AND role IN ('admin', 'staff', 'driver') LIMIT 1");
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Delete failed']);
  exit;
}
$stmt->close();
echo json_encode(['status'=>'ok']);
?>