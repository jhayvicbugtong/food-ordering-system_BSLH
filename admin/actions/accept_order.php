<?php
// admin/actions/accept_order.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
  exit;
}

// Mysqli connection from customer/includes/db_connect.php (change path if needed)
require_once __DIR__ . '/../../customer/includes/db_connect.php'; // should define $conn (mysqli)

if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'DB connection ($conn) not found. Check db_connect.php include path.']);
  exit;
}

// ---------- INPUT ----------
$orderId       = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$amount        = isset($_POST['amount']) ? preg_replace('/[^\d.]/', '', $_POST['amount']) : null;
$paymentStatus = (isset($_POST['payment_status']) && $_POST['payment_status'] === 'unpaid') ? 'unpaid' : 'paid';
$method        = ($paymentStatus === 'paid') ? ($_POST['payment_method'] ?? null) : null;
$reference     = ($paymentStatus === 'paid') ? (($_POST['reference_no'] ?? null) ?: null) : null;

if ($orderId <= 0) {
  http_response_code(422);
  echo json_encode(['status' => 'error', 'message' => 'Invalid order_id']);
  exit;
}

// ---------- CONFIG: adjust to your schema ----------
$table        = 'orders';            // your orders table
$colId        = 'id';                // PK column
$colStatus    = 'status';            // ENUM('pending','preparing','ready','out_for_delivery','delivered','cancelled')
$colTotal     = 'total_amount';      // numeric
$colPayStat   = 'payment_status';    // 'paid' / 'unpaid'
$colPayMethod = 'payment_method';    // nullable
$colPayRef    = 'payment_reference'; // nullable
$colUpdatedAt = 'updated_at';        // DATETIME
$nextStatus   = 'preparing';         // after accept

// ---------- TX + UPDATE ----------
$conn->begin_transaction();
try {
  // Lock row & verify status
  $stmt = $conn->prepare("SELECT $colStatus FROM $table WHERE $colId = ? FOR UPDATE");
  $stmt->bind_param('i', $orderId);
  $stmt->execute();
  $stmt->bind_result($curStatus);
  if (!$stmt->fetch()) {
    throw new Exception('Order not found');
  }
  $stmt->close();

  if ($curStatus !== 'pending') {
    throw new Exception('Order is not in pending status');
  }

  // (Optional) you may want to verify $amount against $colTotal here.

  $sql = "UPDATE $table
            SET $colStatus = ?,
                $colPayStat = ?,
                $colPayMethod = ?,
                $colPayRef = ?,
                $colUpdatedAt = NOW()
          WHERE $colId = ? AND $colStatus = 'pending'";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssssi', $nextStatus, $paymentStatus, $method, $reference, $orderId);
  $stmt->execute();

  if ($stmt->affected_rows < 1) {
    throw new Exception('Update failed or already processed');
  }
  $stmt->close();

  $conn->commit();
  echo json_encode(['status' => 'ok', 'next_status' => $nextStatus]);
} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
