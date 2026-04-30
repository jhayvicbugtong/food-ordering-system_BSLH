<?php
// staff/actions/fetch_queue_updates.php
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

// 2. Fetch Orders (Logic matches view_orders.php)
$orders_query = "
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
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.status NOT IN ('completed', 'delivered', 'cancelled')
      AND NOT (o.order_type = 'delivery' AND o.status IN ('ready', 'out_for_delivery'))
    ORDER BY o.created_at ASC
    LIMIT 100;
";

$result = $conn->query($orders_query);
$orders_data = [];

if ($result) {
    while ($order = $result->fetch_assoc()) {
        $orders_data[$order['order_id']] = [
            'status' => $order['status'],
            'html' => renderOrderRow($order, $conn)
        ];
    }
}

echo json_encode(['orders' => $orders_data]);
$conn->close();

// --- Helper function to generate HTML for a single row ---
function renderOrderRow($order, $conn) {
    $order_id = (int)$order['order_id'];
    $status = $order['status'];
    $order_number = htmlspecialchars($order['order_number'] ?? $order_id);
    $total = $order['total_amount'] ?? 0;
    
    // 1. Calculate Status Class
    $status_map = [
        'pending'          => 'badge-warning',
        'confirmed'        => 'badge-info',
        'preparing'        => 'badge-info',
        'ready'            => 'badge-success',
        'out_for_delivery' => 'badge-success',
    ];
    $status_class = $status_map[$status] ?? 'badge-secondary';

    // 2. Customer Name
    $customer_name = htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')));
    if ($customer_name === '') $customer_name = 'Walk-in Customer';

    // 3. Payment Info
    $payment_status = $order['payment_status'] ?? null;
    $payment_method = $order['payment_method'] ?? null;
    if (!$payment_method) {
        $payment_label = 'Unpaid';
        $payment_badge_class = 'badge-secondary';
    } else {
        $payment_label = strtoupper($payment_method);
        $payment_label .= ($payment_status && $payment_status !== 'paid') ? ' (' . ucfirst($payment_status) . ')' : ' (Paid)';
        $payment_badge_class = ($payment_status === 'paid') ? 'badge-success' : 'badge-warning';
    }

    // 4. Source Pill
    if ($order['order_type'] == 'delivery') {
        $source_class = 'delivery';
        $source_label = 'Delivery';
    } else {
        $source_class = 'pickup';
        $source_label = 'Pickup';
    }

    // 5. Created Time
    $created_time = $order['created_at'] ? date('g:i A', strtotime($order['created_at'])) : '';

    // 6. Fetch Items (Limit 3)
    $items_html = '<ul class="list-unstyled mb-0 searchable-text" style="padding-left: 0; font-size: 0.85em;">';
    $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ? LIMIT 3");
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items_res = $items_stmt->get_result();
    $item_count = 0;
    while($item = $items_res->fetch_assoc()) {
        $item_count++;
        $items_html .= '<li>' . htmlspecialchars($item['product_name']) . ' x <strong>' . (int)$item['quantity'] . '</strong></li>';
    }
    $items_stmt->close();
    if ($item_count >= 3) {
        $items_html .= '<li class="text-muted" style="font-size:0.8em;">...and more</li>';
    }
    $items_html .= '</ul>';

    // 7. Render Buttons based on status
    $buttons_html = '';
    if ($status == 'pending') {
        $buttons_html = '<button class="btn btn-outline-success btn-action" data-action="confirm" data-id="'.$order_id.'">Accept</button>
                         <button class="btn btn-outline-danger btn-action" data-action="cancel" data-id="'.$order_id.'">Reject</button>';
    } elseif ($status == 'confirmed') {
        $buttons_html = '<button class="btn btn-outline-primary btn-action" data-action="prepare" data-id="'.$order_id.'">Prep</button>';
    } elseif ($status == 'preparing') {
        $buttons_html = '<button class="btn btn-outline-success btn-action" data-action="ready" data-id="'.$order_id.'">Ready</button>';
    } elseif ($status == 'ready' && $order['order_type'] == 'pickup') {
        if ($payment_status === 'paid') {
            $buttons_html = '<button class="btn btn-outline-success btn-action" data-action="complete" data-id="'.$order_id.'">Done</button>';
        } else {
            $buttons_html = '<a href="pos_payment.php?order_id='.$order_id.'" class="btn btn-outline-success">Pay</a>';
        }
    }

    // --- Start Building Row HTML ---
    // Note: We maintain the data attributes used by the JS search function
    $html = '<tr data-order-id="'.$order_id.'" 
                 data-order-type="'.htmlspecialchars($order['order_type']).'" 
                 data-payment-status="'.htmlspecialchars($payment_status ?? '').'"
                 data-status="'.htmlspecialchars($status).'"
                 class="order-row">';
    
    // Cell 1: Order #
    $html .= '<td data-label="Order #">
                <div class="searchable-text">
                    <strong>'.$order_number.'</strong>';
    if ($created_time) {
        $html .= '<div class="meta-text">Placed: '.$created_time.'</div>';
    }
    $html .= '  </div>
              </td>';

    // Cell 2: Items
    $html .= '<td data-label="Items">'.$items_html.'</td>';

    // Cell 3: Customer
    $html .= '<td data-label="Customer" class="searchable-text">'.$customer_name.'</td>';

    // Cell 4: Total
    $html .= '<td data-label="Total"><span style="font-weight:600; white-space:nowrap;">₱'.number_format($total, 2).'</span></td>';

    // Cell 5: Type/Payment
    $html .= '<td data-label="Type / Payment">
                <div class="d-flex flex-column align-items-end align-items-md-start gap-1">
                    <span class="source-pill '.$source_class.'">'.$source_label.'</span>
                    <span class="payment-badge badge '.$payment_badge_class.'">'.$payment_label.'</span>
                </div>
              </td>';

    // Cell 6: Status
    $html .= '<td data-label="Status">
                <span class="status-badge badge '.$status_class.'">'.ucfirst(str_replace('_', ' ', $status)).'</span>
              </td>';

    // Cell 7: Actions
    $html .= '<td class="actions-cell">
                <div class="d-flex align-items-center justify-content-end justify-content-md-start gap-2 action-buttons-container">
                    <button class="btn btn-sm btn-outline-secondary btn-view-details" data-id="'.$order_id.'" title="View Details">
                         <i class="bi bi-eye"></i>
                    </button>
                    <div class="btn-group btn-group-sm action-group">
                        '.$buttons_html.'
                    </div>
                </div>
              </td>';
    
    $html .= '</tr>';

    return $html;
}
?>