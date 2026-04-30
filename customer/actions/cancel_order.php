<?php
// customer/actions/cancel_order.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Check Authentication
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$order_id = (int)($input['order_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// 3. Verify Order Ownership and Status
$stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $res->fetch_assoc();
if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled (already confirmed or processed).']);
    exit;
}
$stmt->close();

// 4. Perform Cancellation
$cancel_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE order_id = ?");
$cancel_stmt->bind_param("i", $order_id);

if ($cancel_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
$cancel_stmt->close();
$conn->close();
?>