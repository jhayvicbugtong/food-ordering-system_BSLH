<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

$stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='staff' LIMIT 1");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Delete failed']);
  exit;
}
$stmt->close();
echo json_encode(['status'=>'ok']);
