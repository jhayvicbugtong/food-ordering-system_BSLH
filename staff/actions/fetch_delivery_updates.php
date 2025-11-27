<?php
// staff/actions/fetch_delivery_updates.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Auth Check
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? null;

if ($user_id === 0 || !in_array($user_role, ['admin', 'staff'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2. Fetch Stats (Matches deliveries.php logic)
$stats_ready = $conn->query("
    SELECT COUNT(order_id) as total 
    FROM orders 
    WHERE status = 'ready' AND order_type = 'delivery'
")->fetch_assoc()['total'] ?? 0;

$stats_out = $conn->query("
    SELECT COUNT(order_id) as total 
    FROM orders 
    WHERE status = 'out_for_delivery' AND order_type = 'delivery'
")->fetch_assoc()['total'] ?? 0;

$stats_total = $stats_ready + $stats_out;

// 3. Fetch Delivery Orders
$delivery_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.status,
        o.created_at,
        o.total_amount,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        ocd.customer_phone,
        oa.street,
        oa.barangay,
        oa.city,
        oa.province,
        oa.floor_number,
        oa.apt_landmark,
        opd.payment_method,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_addresses oa ON o.order_id = oa.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.order_type = 'delivery' 
      AND o.status IN ('ready', 'out_for_delivery', 'confirmed')
    ORDER BY o.created_at ASC;
";

$result = $conn->query($delivery_query);
$orders_data = [];

if ($result) {
    while ($order = $result->fetch_assoc()) {
        $orders_data[$order['order_id']] = [
            'status' => $order['status'],
            'html' => renderDeliveryRow($order)
        ];
    }
}

// Return stats and orders
echo json_encode([
    'stats' => [
        'ready' => $stats_ready,
        'out' => $stats_out,
        'total' => $stats_total
    ],
    'orders' => $orders_data
]);

$conn->close();

// --- Helper Function to Render HTML ---
function renderDeliveryRow($order) {
    $status = $order['status'];
    $order_id = (int)$order['order_id'];
    $total = (float)($order['total_amount'] ?? 0);
    $order_number = htmlspecialchars($order['order_number'] ?? $order_id);

    // Status Badge
    $status_map = [
        'confirmed'        => 'badge-primary',
        'ready'            => 'badge-success',
        'out_for_delivery' => 'badge-info',
    ];
    $status_class = $status_map[$status] ?? 'badge-secondary';

    // Customer
    $customer_name = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
    if ($customer_name === '') $customer_name = 'Delivery Customer';
    $customer_phone = htmlspecialchars($order['customer_phone'] ?: 'No phone');

    // Address
    $addr_parts = [];
    if (!empty($order['street']))    $addr_parts[] = $order['street'];
    if (!empty($order['barangay']))  $addr_parts[] = 'Brgy. ' . $order['barangay'];
    $full_address = implode(', ', $addr_parts);
    if ($full_address === '') $full_address = 'N/A';

    // Extra Instructions
    $extras = [];
    if (!empty($order['floor_number'])) $extras[] = 'Floor: ' . $order['floor_number'];
    if (!empty($order['apt_landmark'])) $extras[] = 'Landmark: ' . $order['apt_landmark'];
    $extra_text = implode('; ', $extras);

    // Payment
    $payment_method = $order['payment_method'] ?? null;
    $payment_status = $order['payment_status'] ?? null;

    if (!$payment_method) {
        $payment_label = 'Unpaid';
        $payment_badge_class = 'badge-secondary';
    } else {
        $payment_label = strtoupper($payment_method);
        $payment_label .= ($payment_status && $payment_status !== 'paid') ? ' (' . ucfirst($payment_status) . ')' : ' (Paid)';
        $payment_badge_class = ($payment_status === 'paid') ? 'badge-success' : 'badge-warning';
    }

    $created_at_display = '';
    if (!empty($order['created_at'])) {
        $created_at_display = '<div class="meta-text">Placed: ' . htmlspecialchars(date('g:i A', strtotime($order['created_at']))) . '</div>';
    }

    // Actions Buttons
    $actions_html = '';
    if ($status == 'confirmed' || $status == 'ready') {
        $actions_html = '<button class="btn btn-sm btn-outline-primary btn-action" data-action="out_for_delivery" data-id="'.$order_id.'">Out for Delivery</button>';
    } elseif ($status == 'out_for_delivery') {
        $actions_html = '<button class="btn btn-sm btn-outline-success btn-action" data-action="delivered" data-id="'.$order_id.'">Mark Delivered</button>';
    } else {
        $actions_html = '<span class="text-muted">No actions</span>';
    }

    $extra_html = '';
    if ($extra_text) {
        $extra_html = '<span class="address-meta"><i class="bi bi-info-circle"></i> ' . htmlspecialchars($extra_text) . '</span>';
    }

    // Build Row
    $html = '<tr data-order-id="'.$order_id.'" class="order-row">';
    
    $html .= '<td data-label="Order #">
                <div class="searchable-text">
                    <strong>'.$order_number.'</strong>
                    '.$created_at_display.'
                </div>
              </td>';
    
    $html .= '<td data-label="Customer">
                <span class="searchable-text">'.htmlspecialchars($customer_name).'</span><br>
                <small class="text-muted">'.$customer_phone.'</small>
              </td>';
              
    $html .= '<td data-label="Dropoff Address">
                <span class="address-main searchable-text">'.htmlspecialchars($full_address).'</span>
                '.$extra_html.'
              </td>';
              
    $html .= '<td data-label="Total"><span style="font-weight:600; white-space:nowrap;">₱'.number_format($total, 2).'</span></td>';
    
    $html .= '<td data-label="Payment"><span class="payment-badge badge '.$payment_badge_class.'">'.htmlspecialchars($payment_label).'</span></td>';
    
    $html .= '<td data-label="Status"><span class="status-badge badge '.$status_class.'">'.htmlspecialchars(ucfirst(str_replace('_', ' ', $status))).'</span></td>';
    
    $html .= '<td class="actions-cell"><div class="action-group">'.$actions_html.'</div></td>';
    
    $html .= '</tr>';

    return $html;
}
?>