<?php
// staff/actions/update_order_status.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Authenticate
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? null;

if ($user_id === 0 || !in_array($user_role, ['admin', 'staff', 'driver'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Get Input
$data = json_decode(file_get_contents('php://input'));
$order_id = $data->order_id ?? 0;
$new_status = $data->new_status ?? '';
$handler_id = $data->handler_id ?? null; // Staff handling prep
$driver_id = $data->driver_id ?? null;   // Driver doing delivery

if ($order_id <= 0 || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// 3. Define Valid Status Transitions
$valid_statuses = [
    'pending', 'confirmed', 'preparing', 'ready', 
    'out_for_delivery', 'delivered', 'completed', 'cancelled'
];
if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

$conn->begin_transaction();
try {
    // --- FETCH CURRENT INFO (For Logic Checks) ---
    $info_stmt = $conn->prepare("
        SELECT o.order_type, o.status, opd.payment_method, opd.payment_status 
        FROM orders o
        LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
        WHERE o.order_id = ?
    ");
    $info_stmt->bind_param('i', $order_id);
    $info_stmt->execute();
    $current_info = $info_stmt->get_result()->fetch_assoc();
    $info_stmt->close();

    // 4. Build SQL Query for Status Update
    $fields = ['status = ?'];
    $params = [$new_status];
    $types = 's';

    // Set the correct timestamp column based on the new status
    $time_col = null;
    switch ($new_status) {
        case 'confirmed': $time_col = 'confirmed_at'; break;
        case 'preparing': $time_col = 'preparing_at'; break;
        case 'ready': $time_col = 'ready_at'; break;
        case 'out_for_delivery': $time_col = 'out_for_delivery_at'; break;
        case 'delivered': $time_col = 'delivered_at'; break;
        case 'cancelled': $time_col = 'cancelled_at'; break;
        case 'completed': $time_col = 'updated_at'; break; 
    }
    
    if ($time_col) {
        $fields[] = "$time_col = NOW()";
    }

    // Assign a handler (kitchen/staff)
    if ($handler_id && in_array($new_status, ['confirmed', 'preparing', 'ready'])) {
        $fields[] = 'handler_id = ?';
        $params[] = $handler_id;
        $types .= 'i';
    }
    
    // Assign a driver
    if ($driver_id && $new_status === 'out_for_delivery') {
        $fields[] = 'driver_id = ?';
        $params[] = $driver_id;
        $types .= 'i';
    }

    $params[] = $order_id;
    $types .= 'i';

    $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }
    $stmt->close();

    // --- AUTOMATIC PAYMENT UPDATE FOR COD ---
    // If marking as 'delivered' or 'completed', and it's Cash, mark as Paid.
    if (in_array($new_status, ['delivered', 'completed'])) {
        $method = $current_info['payment_method'] ?? '';
        $p_status = $current_info['payment_status'] ?? '';

        // If Cash and not yet paid, assume rider collected cash
        if ($method === 'cash' && $p_status !== 'paid') {
            $pay_upd = $conn->prepare("
                UPDATE order_payment_details 
                SET payment_status = 'paid', paid_at = NOW() 
                WHERE order_id = ?
            ");
            $pay_upd->bind_param('i', $order_id);
            $pay_upd->execute();
            $pay_upd->close();
        }
    }

    // Commit transaction
    $conn->commit();

    // Send back success response
    $status_map = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-primary',
        'preparing' => 'badge-info',
        'ready' => 'badge-success',
        'out_for_delivery' => 'badge-info',
        'delivered' => 'badge-secondary',
        'completed' => 'badge-success', // 'Completed' is usually success green
        'cancelled' => 'badge-danger',
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated.',
        'new_status_label' => ucfirst(str_replace('_', ' ', $new_status)),
        'new_status_class' => $status_map[$new_status] ?? 'badge-secondary'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>