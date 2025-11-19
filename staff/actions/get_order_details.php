<?php
// staff/actions/get_order_details.php

ob_start();
include __DIR__ . '/../includes/header.php';
ob_end_clean();

header('Content-Type: application/json');

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection not available.']);
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

function fmt_dt($dt) {
    if (!$dt) return null;
    $ts = strtotime($dt);
    if (!$ts) return null;
    return date('Y-m-d g:i A', $ts);
}

function peso($amount) {
    return '₱' . number_format((float)$amount, 2);
}

// -------- MAIN ORDER + CUSTOMER + PAYMENT + ADDRESS --------
$sql = "
    SELECT
        o.order_id,
        o.order_number,
        o.order_type,
        o.status,
        o.subtotal,
        o.delivery_fee,
        o.tip_amount,
        o.total_amount,
        o.created_at,
        o.confirmed_at,
        o.preparing_at,
        o.ready_at,
        o.out_for_delivery_at,
        o.delivered_at,
        o.cancelled_at,

        COALESCE(ocd.customer_first_name, u.first_name) AS first_name,
        COALESCE(ocd.customer_last_name,  u.last_name)  AS last_name,
        COALESCE(ocd.customer_email,      u.email)      AS email,
        COALESCE(ocd.customer_phone,      u.phone)      AS phone,

        opd.payment_method,
        opd.payment_status,

        oa.street,
        oa.barangay,
        oa.city,
        oa.province,
        oa.floor_number,
        oa.apt_landmark
    FROM orders o
    LEFT JOIN users u
        ON u.user_id = o.user_id
    LEFT JOIN order_customer_details ocd
        ON ocd.order_id = o.order_id
    LEFT JOIN order_payment_details opd
        ON opd.order_id = o.order_id
    LEFT JOIN order_addresses oa
        ON oa.order_id = o.order_id
    WHERE o.order_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'SQL error (order): ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$res       = $stmt->get_result();
$order_row = $res->fetch_assoc();
$stmt->close();

if (!$order_row) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

// -------- BUILD ORDER OBJECT --------
$first = trim($order_row['first_name'] ?? '');
$last  = trim($order_row['last_name'] ?? '');
$customer_name = trim($first . ' ' . $last);
if ($customer_name === '') $customer_name = 'Guest / POS';

// Type label
switch ($order_row['order_type']) {
    case 'delivery': $type_label = 'Delivery'; break;
    case 'pickup':   $type_label = 'Pickup';   break;
    default:         $type_label = 'POS / Other'; break;
}

// Status label + badge
$status = (string)$order_row['status'];
$status_label = ucfirst(str_replace('_', ' ', $status));
$status_badge_class = 'badge-secondary';
if (in_array($status, ['completed', 'delivered'], true)) {
    $status_badge_class = 'badge-success';
} elseif ($status === 'cancelled') {
    $status_badge_class = 'badge-danger';
} elseif (in_array($status, ['pending','confirmed','preparing','ready','out_for_delivery'], true)) {
    $status_badge_class = 'badge-warning';
}

// Payment label
$payment_method = $order_row['payment_method'] ?? null;
$payment_status = $order_row['payment_status'] ?? null;

switch ($payment_method) {
    case 'cash':  $payment_method_label = 'Cash';  break;
    case 'gcash': $payment_method_label = 'GCash'; break;
    case 'card':  $payment_method_label = 'Card';  break;
    default:      $payment_method_label = 'Unknown'; break;
}

switch ($payment_status) {
    case 'paid':     $payment_status_label = 'Paid';     break;
    case 'pending':  $payment_status_label = 'Pending';  break;
    case 'failed':   $payment_status_label = 'Failed';   break;
    case 'refunded': $payment_status_label = 'Refunded'; break;
    default:         $payment_status_label = '';         break;
}

$payment_label = trim($payment_method_label . ($payment_status_label ? " ({$payment_status_label})" : ''));

// -------- DELIVERY ADDRESS (ALWAYS BUILT IF DATA EXISTS) --------
$delivery_address = null;
$addrParts = [];

if (!empty($order_row['street']))      $addrParts[] = $order_row['street'];
if (!empty($order_row['barangay']))    $addrParts[] = 'Brgy. ' . $order_row['barangay'];
if (!empty($order_row['city']))        $addrParts[] = $order_row['city'];
if (!empty($order_row['province']))    $addrParts[] = $order_row['province'];

$addr = implode(', ', $addrParts);

$extras = [];
if (!empty($order_row['floor_number']))  $extras[] = 'Floor: ' . $order_row['floor_number'];
if (!empty($order_row['apt_landmark']))  $extras[] = 'Landmark: ' . $order_row['apt_landmark'];

if ($extras) {
    $addr .= $addr ? ' (' . implode('; ', $extras) . ')' : implode('; ', $extras);
}

if ($addr !== '') {
    $delivery_address = $addr;
}

$order_data = [
    'order_id'               => (int)$order_row['order_id'],
    'order_number'           => $order_row['order_number'],

    'customer_name'          => $customer_name,
    'customer_email'         => $order_row['email'] ?? null,
    'customer_phone'         => $order_row['phone'] ?? null,

    'type_label'             => $type_label,
    'status_label'           => $status_label,
    'status_badge_class'     => $status_badge_class,
    'payment_label'          => $payment_label,

    'delivery_address'       => $delivery_address,

    'subtotal_formatted'     => peso($order_row['subtotal']),
    'delivery_fee_formatted' => peso($order_row['delivery_fee']),
    'tip_formatted'          => peso($order_row['tip_amount']),
    'total_formatted'        => peso($order_row['total_amount']),

    'created_at'             => fmt_dt($order_row['created_at']),
    'confirmed_at'           => fmt_dt($order_row['confirmed_at']),
    'preparing_at'           => fmt_dt($order_row['preparing_at']),
    'ready_at'               => fmt_dt($order_row['ready_at']),
    'out_for_delivery_at'    => fmt_dt($order_row['out_for_delivery_at']),
    'delivered_at'           => fmt_dt($order_row['delivered_at']),
    'cancelled_at'           => fmt_dt($order_row['cancelled_at']),
];

// -------- ORDER ITEMS --------
$items_sql = "
    SELECT
        product_name,
        unit_price,
        quantity,
        total_price,
        special_instructions
    FROM order_items
    WHERE order_id = ?
    ORDER BY order_item_id ASC
";

$item_stmt = $conn->prepare($items_sql);
if (!$item_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'SQL error (items): ' . $conn->error]);
    exit;
}

$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$item_res = $item_stmt->get_result();

$items = [];
while ($row = $item_res->fetch_assoc()) {
    $items[] = [
        'product_name'         => $row['product_name'],
        'quantity'             => (int)$row['quantity'],
        'unit_price_fmt'       => peso($row['unit_price']),
        'total_price_fmt'      => peso($row['total_price']),
        'special_instructions' => $row['special_instructions'] ?? null,
    ];
}
$item_stmt->close();

echo json_encode([
    'success' => true,
    'order'   => $order_data,
    'items'   => $items,
]);
