<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get all columns so we can include optional ones safely
$columns = [];
if ($res = $conn->query("SHOW COLUMNS FROM users")) {
  while ($c = $res->fetch_assoc()) $columns[strtolower($c['Field'])] = true;
}

$select = "id,name,email,role";
$opt = ['phone','staff_role','shift','notes','started_at'];
foreach ($opt as $c) if (isset($columns[$c])) $select .= ",$c";

if ($id > 0) {
  $stmt = $conn->prepare("SELECT $select FROM users WHERE role='staff' AND id=? LIMIT 1");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if ($row) echo json_encode(['status'=>'ok','row'=>$row]);
  else { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Not found']); }
  exit;
}

$q = $conn->query("SELECT $select FROM users WHERE role='staff' ORDER BY name");
$rows = [];
while ($r = $q->fetch_assoc()) $rows[] = $r;
echo json_encode(['status'=>'ok','rows'=>$rows]);
