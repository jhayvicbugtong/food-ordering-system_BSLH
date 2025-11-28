<?php
// admin/actions/fetch_orders_updates.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Auth Check
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2. Filters (Must match manage_orders.php logic)
$order_type_filter = $_GET['order_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

$whereClauses = ["1=1"];

if ($order_type_filter === 'pickup' || $order_type_filter === 'delivery') {
    $order_type_esc = $conn->real_escape_string($order_type_filter);
    $whereClauses[] = "o.order_type = '{$order_type_esc}'";
}

if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $date_from_esc = $conn->real_escape_string($date_from);
    $whereClauses[] = "DATE(o.created_at) >= '{$date_from_esc}'";
}

if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $date_to_esc = $conn->real_escape_string($date_to);
    $whereClauses[] = "DATE(o.created_at) <= '{$date_to_esc}'";
}

$whereSql = implode(' AND ', $whereClauses);

// 3. Pagination
$perPage = 10;
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// 4. Query
$orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.total_amount, 
        o.status,
        o.created_at,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        ocd.customer_phone,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE {$whereSql}
    GROUP BY o.order_id
    ORDER BY 
        CASE o.status
            WHEN 'pending' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'preparing' THEN 3
            WHEN 'ready' THEN 4
            WHEN 'out_for_delivery' THEN 5
            ELSE 6
        END,
        o.created_at DESC
    LIMIT {$perPage} OFFSET {$offset};
";

$orders_result = $conn->query($orders_query);
$html = '';

if ($orders_result && $orders_result->num_rows > 0) {
    while ($order = $orders_result->fetch_assoc()) {
        $html .= renderRow($order);
    }
} else {
    $html = '<tr><td colspan="7" class="text-center text-muted py-4">No orders found for this filter.</td></tr>';
}

echo json_encode(['html' => $html]);
$conn->close();

// --- Helper to Render Row (Matches manage_orders.php HTML structure) ---
function renderRow($order) {
    $status = $order['status'];
    $status_map = [
        'pending'          => 'status-pending',
        'confirmed'        => 'status-confirmed',
        'preparing'        => 'status-preparing',
        'ready'            => 'status-ready',
        'out_for_delivery' => 'status-out-for-delivery',
        'delivered'        => 'status-delivered',
        'completed'        => 'status-completed',
        'cancelled'        => 'status-cancelled',
    ];
    $status_class = $status_map[$status] ?? '';
    
    $customer_name = htmlspecialchars(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
    if (trim($customer_name) === '') $customer_name = 'Walk-in Customer';

    $pay_status = $order['payment_status'] ?? 'unpaid';
    $pay_badge = 'bg-secondary-subtle text-secondary';
    if ($pay_status === 'paid') $pay_badge = 'bg-success-subtle text-success';
    elseif ($pay_status === 'failed') $pay_badge = 'bg-danger-subtle text-danger';
    elseif ($pay_status === 'refunded') $pay_badge = 'bg-info-subtle text-info';

    $order_no = htmlspecialchars($order['order_number'] ?? $order['order_id']);
    $date = date('Y-m-d g:i A', strtotime($order['created_at']));
    
    $phone_html = !empty($order['customer_phone']) 
        ? '<small class="text-muted">' . htmlspecialchars($order['customer_phone']) . '</small>'
        : '';
    
    $type_badge = ($order['order_type'] == 'delivery')
        ? '<span class="badge bg-success-subtle text-success badge-rounded">Delivery</span>'
        : '<span class="badge bg-primary-subtle text-primary badge-rounded">Pickup</span>';

    $total = number_format((float)$order['total_amount'], 2);
    $status_label = htmlspecialchars(ucfirst(str_replace('_', ' ', $status)));

    return '
    <tr data-row-id="'.(int)$order['order_id'].'">
        <td class="text-nowrap">
            <strong>'.$order_no.'</strong><br>
            <small class="text-muted">'.$date.'</small>
        </td>
        <td class="text-nowrap">
            '.$customer_name.'<br>
            '.$phone_html.'
        </td>
        <td>'.$type_badge.'</td>
        <td>
            <span class="badge '.$pay_badge.' badge-rounded">'.ucfirst($pay_status ?: 'Pending').'</span>
        </td>
        <td class="text-nowrap">₱'.$total.'</td>
        <td>
            <span class="status-badge '.$status_class.'">'.$status_label.'</span>
        </td>
        <td class="text-end text-nowrap">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary btn-view" data-order-id="'.(int)$order['order_id'].'">
                    <i class="bi bi-eye"></i> View
                </button>
            </div>
        </td>
    </tr>';
}
?>