<?php
// staff/actions/fetch_dashboard_updates.php
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

// 2. Fetch Stats (Logic matches index.php)
$stats_sql = "
    SELECT
        SUM(CASE WHEN status IN ('pending','confirmed','preparing') THEN 1 ELSE 0 END) AS to_prepare,
        SUM(CASE WHEN status = 'ready' AND order_type = 'pickup' THEN 1 ELSE 0 END) AS ready_pickup,
        SUM(CASE WHEN status IN ('ready','out_for_delivery') AND order_type = 'delivery' THEN 1 ELSE 0 END) AS out_for_delivery
    FROM orders
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result ? $stats_result->fetch_assoc() : [];

// 3. Fetch Active Orders (Top Table)
$active_sql = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status,
        o.created_at,
        o.total_amount,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        opd.payment_method,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON opd.order_id = o.order_id
    WHERE o.status NOT IN ('completed', 'delivered', 'cancelled')
    ORDER BY 
        CASE o.status
            WHEN 'pending' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'preparing' THEN 3
            WHEN 'ready' THEN 4
            WHEN 'out_for_delivery' THEN 5
            ELSE 6
        END,
        o.created_at ASC
    LIMIT 50
";
$active_orders = $conn->query($active_sql);
$active_html = '';

if ($active_orders && $active_orders->num_rows > 0) {
    while ($order = $active_orders->fetch_assoc()) {
        $active_html .= renderActiveRow($order, $conn);
    }
} else {
    $active_html = '<tr><td colspan="6" class="text-center text-muted">No active orders.</td></tr>';
}

// 4. Fetch Pickup Queue (Bottom Table)
$pickup_sql = "
    SELECT 
        o.order_id, 
        o.order_number,
        o.status,
        o.ready_at,
        o.total_amount,
        ocd.customer_first_name, 
        ocd.customer_last_name
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    WHERE o.order_type = 'pickup'
      AND o.status = 'ready'
    ORDER BY o.ready_at ASC
";
$pickup_orders = $conn->query($pickup_sql);
$pickup_html = '';

if ($pickup_orders && $pickup_orders->num_rows > 0) {
    while ($order = $pickup_orders->fetch_assoc()) {
        $pickup_html .= renderPickupRow($order, $conn);
    }
} else {
    $pickup_html = '<tr><td colspan="6" class="text-center text-muted">No pickup customers in the queue.</td></tr>';
}

// Return Data
echo json_encode([
    'stats' => [
        'to_prepare' => (int)($stats['to_prepare'] ?? 0),
        'ready_pickup' => (int)($stats['ready_pickup'] ?? 0),
        'out_for_delivery' => (int)($stats['out_for_delivery'] ?? 0)
    ],
    'active_html' => $active_html,
    'pickup_html' => $pickup_html
]);

$conn->close();

// --- Helpers ---

function renderActiveRow($order, $conn) {
    $order_id  = (int)$order['order_id'];
    $order_no  = htmlspecialchars($order['order_number'] ?? $order_id);
    $full_name = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
    if ($full_name === '') $full_name = 'Walk-in POS';

    // Source Label
    if ($order['order_type'] === 'delivery') {
        $source_label = 'Online Delivery';
        $source_class = 'delivery';
    } elseif ($order['order_type'] === 'pickup') {
        $source_label = 'Pickup';
        $source_class = 'pickup';
    } else {
        $source_label = 'Walk-in POS';
        $source_class = 'pos';
    }

    // Status Label
    $status = $order['status'];
    $status_map = [
        'pending'          => 'badge-warning',
        'confirmed'        => 'badge-info',
        'preparing'        => 'badge-info',
        'ready'            => 'badge-success',
        'out_for_delivery' => 'badge-success',
    ];
    $status_class = $status_map[$status] ?? 'badge-secondary';

    // Payment Label
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

    $created_time = $order['created_at'] ? date('g:i A', strtotime($order['created_at'])) : '';
    $total = (float)($order['total_amount'] ?? 0);

    // Items
    $items_html = '';
    $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items_res = $items_stmt->get_result();
    while ($item = $items_res->fetch_assoc()) {
        $items_html .= '<li>' . htmlspecialchars($item['product_name']) . ' x <strong>' . (int)$item['quantity'] . '</strong></li>';
    }
    $items_stmt->close();

    $html = '<tr>
        <td>
            <strong>#' . $order_no . '</strong><br>';
    if ($created_time) {
        $html .= '<span class="meta-text">Placed: ' . htmlspecialchars($created_time) . '</span>';
    }
    $html .= '</td>
        <td>
            <span class="source-pill ' . $source_class . '">' . htmlspecialchars($source_label) . '</span><br>
            <small class="text-muted">' . htmlspecialchars($full_name) . '</small>
        </td>
        <td>
            <ul class="mb-0" style="padding-left: 15px; font-size: 0.85rem;">' . $items_html . '</ul>
        </td>
        <td><span style="font-weight:600; white-space:nowrap;">₱' . number_format($total, 2) . '</span></td>
        <td><span class="payment-badge badge ' . $payment_badge_class . '">' . htmlspecialchars($payment_label) . '</span></td>
        <td><span class="status-badge badge ' . $status_class . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) . '</span></td>
    </tr>';

    return $html;
}

function renderPickupRow($order, $conn) {
    $order_id  = (int)$order['order_id'];
    $order_no  = htmlspecialchars($order['order_number'] ?? $order_id);
    $full_name = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
    if ($full_name === '') $full_name = 'Walk-in POS';

    $time_ready = $order['ready_at'] ? date('g:i A', strtotime($order['ready_at'])) : '-';
    $status       = $order['status'];
    $status_class = ($status === 'ready') ? 'badge-success' : 'badge-secondary';
    $total        = (float)($order['total_amount'] ?? 0);

    // Items
    $items_html = '';
    $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items_res = $items_stmt->get_result();
    while ($item = $items_res->fetch_assoc()) {
        $items_html .= '<li>' . htmlspecialchars($item['product_name']) . ' x <strong>' . (int)$item['quantity'] . '</strong></li>';
    }
    $items_stmt->close();

    $html = '<tr>
        <td><strong>#' . $order_no . '</strong></td>
        <td>' . htmlspecialchars($full_name) . '</td>
        <td>
            <ul class="mb-0" style="padding-left: 15px; font-size: 0.85rem;">' . $items_html . '</ul>
        </td>
        <td><span style="font-weight:600; white-space:nowrap;">₱' . number_format($total, 2) . '</span></td>
        <td>' . htmlspecialchars($time_ready) . '</td>
        <td><span class="status-badge badge ' . $status_class . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) . '</span></td>
    </tr>';

    return $html;
}
?>