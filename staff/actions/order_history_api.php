<?php
// staff/actions/order_history_api.php

// include header but suppress any HTML output
ob_start();
include __DIR__ . '/../includes/header.php'; // gives you $conn, session, etc.
ob_end_clean();

header('Content-Type: application/json');

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection not available.']);
    exit;
}

// ---------- INPUT ----------
// q is now used for order number / order id search
$search_term   = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? '';
$type_filter   = $_GET['type'] ?? '';
$page          = max(1, (int)($_GET['page'] ?? 1));

$limit  = 10;
$offset = ($page - 1) * $limit;

$allowed_statuses = ['completed', 'delivered', 'cancelled'];
$allowed_types    = ['delivery', 'pickup', 'other'];

// ---------- BUILD BASE SQL ----------
$base_sql = "
    FROM orders o
    LEFT JOIN users u
        ON u.user_id = o.user_id
    LEFT JOIN order_customer_details ocd
        ON o.order_id = ocd.order_id
    WHERE o.status IN ('completed', 'delivered', 'cancelled')
";

$params = [];
$types  = "";

// status filter
if ($status_filter !== '' && in_array($status_filter, $allowed_statuses, true)) {
    $base_sql .= " AND o.status = ? ";
    $params[] = $status_filter;
    $types   .= "s";
}

// order type filter
if ($type_filter !== '' && in_array($type_filter, $allowed_types, true)) {
    if ($type_filter === 'other') {
        $base_sql .= " AND (o.order_type IS NULL OR o.order_type NOT IN ('delivery','pickup')) ";
    } else {
        $base_sql .= " AND o.order_type = ? ";
        $params[] = $type_filter;
        $types   .= "s";
    }
}

// order id / order number search
if ($search_term !== '') {
    // allow user to paste "#BSLH-xxxx" → strip leading '#'
    if ($search_term[0] === '#') {
        $search_term = substr($search_term, 1);
        $search_term = trim($search_term);
    }

    if ($search_term !== '') {
        $base_sql .= "
            AND (
                o.order_number LIKE ?
                OR CAST(o.order_id AS CHAR) LIKE ?
            )
        ";
        $like = '%' . $search_term . '%';
        $params[] = $like;
        $params[] = $like;
        $types   .= "ss";
    }
}

// ---------- TOTAL COUNT ----------
$count_sql = "SELECT COUNT(*) AS total " . $base_sql;
$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'SQL error (count): ' . $conn->error,
    ]);
    exit;
}

if ($types !== '') {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_res  = $count_stmt->get_result()->fetch_assoc();
$total_rows = (int)($count_res['total'] ?? 0);
$count_stmt->close();

$total_pages = max(1, (int)ceil($total_rows / $limit));
if ($page > $total_pages) {
    $page   = $total_pages;
    $offset = ($page - 1) * $limit;
}

// ---------- DATA QUERY (10 PER PAGE) ----------
$data_sql = "
    SELECT
        o.order_id,
        o.order_number,
        o.order_type,
        o.status,
        o.total_amount,
        o.created_at,
        o.updated_at,
        COALESCE(ocd.customer_first_name, u.first_name) AS first_name,
        COALESCE(ocd.customer_last_name,  u.last_name)  AS last_name
    " . $base_sql . "
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
";

$data_stmt = $conn->prepare($data_sql);
if (!$data_stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'SQL error (data): ' . $conn->error,
    ]);
    exit;
}

// build params for data query (filters + limit + offset)
$data_params = $params;
$data_types  = $types . "ii";
$data_params[] = $limit;
$data_params[] = $offset;

$data_stmt->bind_param($data_types, ...$data_params);
$data_stmt->execute();
$res = $data_stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $order_id = (int)$row['order_id'];
    $order_no = $row['order_number'] ?: $order_id;

    $first = trim($row['first_name'] ?? '');
    $last  = trim($row['last_name'] ?? '');
    $customer = trim($first . ' ' . $last);
    if ($customer === '') {
        $customer = 'Guest / POS';
    }

    switch ($row['order_type']) {
        case 'delivery': $type_label = 'Delivery'; break;
        case 'pickup':   $type_label = 'Pickup';   break;
        default:         $type_label = 'POS / Other';
    }

    $status       = (string)$row['status'];
    $status_label = ucfirst(str_replace('_', ' ', $status));
    $status_class = 'badge-secondary';
    if ($status === 'completed' || $status === 'delivered') {
        $status_class = 'badge-success';
    } elseif ($status === 'cancelled') {
        $status_class = 'badge-danger';
    }

    $rows[] = [
        'order_id'           => $order_id,
        'order_number'       => '#' . $order_no,
        'customer'           => $customer,
        'type_label'         => $type_label,
        'total_formatted'    => '₱' . number_format((float)$row['total_amount'], 2),
        'status_label'       => $status_label,
        'status_badge_class' => $status_class,
        'created_at'         => $row['created_at'] ? date('Y-m-d g:i A', strtotime($row['created_at'])) : '-',
        'updated_at'         => $row['updated_at'] ? date('Y-m-d g:i A', strtotime($row['updated_at'])) : '-',
    ];
}

$data_stmt->close();

echo json_encode([
    'success'     => true,
    'page'        => $page,
    'total_rows'  => $total_rows,
    'total_pages' => $total_pages,
    'rows'        => $rows,
]);
