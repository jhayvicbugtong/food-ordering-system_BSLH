<?php
// admin/actions/get_order_details.php

require __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid order id',
    ]);
    exit;
}

// ----- ORDER + CUSTOMER + ADDRESS -----
$sqlOrder = "
    SELECT o.*,
           ocd.customer_first_name,
           ocd.customer_last_name,
           ocd.customer_phone,
           oa.street,
           oa.barangay,
           oa.city,
           oa.province,
           oa.floor_number,
           oa.apt_landmark
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_addresses oa ON o.order_id = oa.order_id
    WHERE o.order_id = {$order_id}
    LIMIT 1
";

$resOrder = $conn->query($sqlOrder);

if (!$resOrder || $resOrder->num_rows === 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Order not found',
    ]);
    exit;
}

$order = $resOrder->fetch_assoc();

$customer_name = trim(
    ($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')
);

// ----- FORMAT ADDRESS -----
$delivery_address = null;
$addrParts = [];

if (!empty($order['street']))      $addrParts[] = $order['street'];
if (!empty($order['barangay']))    $addrParts[] = 'Brgy. ' . $order['barangay'];
if (!empty($order['city']))        $addrParts[] = $order['city'];
if (!empty($order['province']))    $addrParts[] = $order['province'];

$addr = implode(', ', $addrParts);

$extras = [];
if (!empty($order['floor_number']))  $extras[] = 'Floor: ' . $order['floor_number'];
if (!empty($order['apt_landmark']))  $extras[] = 'Landmark: ' . $order['apt_landmark'];

if ($extras) {
    $addr .= $addr ? ' (' . implode('; ', $extras) . ')' : ' ' . implode('; ', $extras);
}

$delivery_address = trim($addr);
if ($delivery_address === '') {
    $delivery_address = null;
}

// ----- ITEMS (MATCHING YOUR TABLE: order_items) -----
$sqlItems = "
    SELECT product_name, quantity, unit_price, total_price
    FROM order_items
    WHERE order_id = {$order_id}
";

$resItems = $conn->query($sqlItems);

$items = [];
if ($resItems) {
    while ($row = $resItems->fetch_assoc()) {
        $items[] = [
            'name'  => $row['product_name'],
            'qty'   => (int)$row['quantity'],
            'price' => (float)$row['unit_price'],
            'total' => (float)$row['total_price'],
        ];
    }
}

// ----- PAYMENT (MATCHING order_payment_details) -----
// take latest record by paid_at or payment_id
$sqlPay = "
    SELECT payment_method,
           payment_status,
           gcash_reference,
           gcash_amount,
           gcash_sender_name,
           amount_paid,
           change_amount,
           paid_at
    FROM order_payment_details
    WHERE order_id = {$order_id}
    ORDER BY paid_at DESC, payment_id DESC
    LIMIT 1
";

$resPay = $conn->query($sqlPay);

$payment = null;
$payment_summary = 'No payment recorded';

if ($resPay && $resPay->num_rows > 0) {
    $p = $resPay->fetch_assoc();

    $payment = [
        'method'          => $p['payment_method'],
        'status'          => $p['payment_status'],
        'gcash_reference' => $p['gcash_reference'],
        'gcash_amount'    => $p['gcash_amount'] !== null ? (float)$p['gcash_amount'] : null,
        'gcash_sender'    => $p['gcash_sender_name'],
        'amount_paid'     => $p['amount_paid'] !== null ? (float)$p['amount_paid'] : null,
        'change_amount'   => $p['change_amount'] !== null ? (float)$p['change_amount'] : null,
        'paid_at'         => $p['paid_at'],
    ];

    // Build a human-readable summary string
    $parts = [];

    if (!empty($p['payment_status'])) {
        $parts[] = ucfirst($p['payment_status']);       // Paid / Pending
    }
    if (!empty($p['payment_method'])) {
        $parts[] = strtoupper($p['payment_method']);    // CASH / GCASH
    }
    if ($p['amount_paid'] !== null) {
        $parts[] = '₱' . number_format((float)$p['amount_paid'], 2);
    }
    if ($p['change_amount'] !== null) {
        $parts[] = 'Change ₱' . number_format((float)$p['change_amount'], 2);
    }
    if (!empty($p['paid_at'])) {
        $parts[] = 'on ' . $p['paid_at'];
    }

    if (!empty($parts)) {
        $payment_summary = implode(' • ', $parts);
    }
}

// Build response
echo json_encode([
    'status' => 'ok',
    'order' => [
        'order_number'     => $order['order_number'],
        'status'           => $order['status'],
        'type'             => $order['order_type'],
        'created_at'       => $order['created_at'],
        'customer'         => $customer_name ?: 'Walk-in Customer',
        'phone'            => $order['customer_phone'] ?? '',
        'total_amount'     => (float)$order['total_amount'],
        'delivery_address' => $delivery_address, // Included here
    ],
    'items'   => $items,
    'payment' => [
        'summary' => $payment_summary,
        'details' => $payment,
    ],
]);
exit;