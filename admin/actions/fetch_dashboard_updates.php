<?php
// admin/actions/fetch_dashboard_updates.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Auth Check
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2. Parameters
$selected_date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}
$day_start = $selected_date . ' 00:00:00';
$day_end   = $selected_date . ' 23:59:59';

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// ----------------- STATS -----------------
$stats = [
    'orders' => 0,
    'revenue' => 0.00,
    'pending' => 0,
    'completed' => 0
];

// Orders Today
$res = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE created_at BETWEEN '$day_start' AND '$day_end'");
$stats['orders'] = $res->fetch_assoc()['total'] ?? 0;

// Revenue Today
$res = $conn->query("
    SELECT SUM(o.total_amount) AS total 
    FROM orders o
    JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end'
");
$stats['revenue'] = (float)($res->fetch_assoc()['total'] ?? 0);

// Pending Today
$res = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE status = 'pending' AND created_at BETWEEN '$day_start' AND '$day_end'");
$stats['pending'] = $res->fetch_assoc()['total'] ?? 0;

// Completed Today
$res = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE status IN ('completed', 'delivered') AND updated_at BETWEEN '$day_start' AND '$day_end'");
$stats['completed'] = $res->fetch_assoc()['total'] ?? 0;


// ----------------- CHARTS DATA -----------------

// 1. Hourly Stats
$orders_per_hour = array_fill(0, 24, 0);
$revenue_per_hour = array_fill(0, 24, 0.0);

// Hourly Orders
$res = $conn->query("
    SELECT HOUR(created_at) AS hr, COUNT(*) AS total
    FROM orders
    WHERE created_at BETWEEN '$day_start' AND '$day_end'
    GROUP BY hr
");
while ($row = $res->fetch_assoc()) $orders_per_hour[(int)$row['hr']] = (int)$row['total'];

// Hourly Revenue
$res = $conn->query("
    SELECT HOUR(opd.paid_at) AS hr, SUM(opd.amount_paid) AS total
    FROM order_payment_details opd
    WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end'
    GROUP BY hr
");
while ($row = $res->fetch_assoc()) $revenue_per_hour[(int)$row['hr']] = (float)$row['total'];

// 2. Status Distribution
$status_labels = [];
$status_totals = [];
$res = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM orders
    WHERE created_at BETWEEN '$day_start' AND '$day_end'
    GROUP BY status ORDER BY status
");
while ($row = $res->fetch_assoc()) {
    $status_labels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $status_totals[] = (int)$row['total'];
}

// 3. Top Items
$top_labels = [];
$top_qty = [];
$top_items_html = '';
$res = $conn->query("
    SELECT oi.product_name, SUM(oi.quantity) AS total_qty, SUM(oi.total_price) AS total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end' AND o.status NOT IN ('cancelled', 'pending')
    GROUP BY oi.product_id, oi.product_name
    ORDER BY total_sales DESC LIMIT 3
");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $top_labels[] = $row['product_name'];
        $top_qty[] = (int)$row['total_qty'];
        $top_items_html .= '<tr>
            <td>' . htmlspecialchars($row['product_name']) . '</td>
            <td>' . htmlspecialchars($row['total_qty']) . '</td>
            <td>₱' . number_format($row['total_sales'], 2) . '</td>
        </tr>';
    }
} else {
    $top_items_html = '<tr><td colspan="3" class="text-center text-muted">No sales yet.</td></tr>';
}

// 4. Payment Mix
$pay_labels = [];
$pay_totals = [];
$pay_mix_html = '';
$res = $conn->query("
    SELECT opd.payment_method, COUNT(opd.payment_id) AS total_count, SUM(opd.amount_paid) AS total_sales
    FROM order_payment_details opd
    WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end'
    GROUP BY opd.payment_method ORDER BY total_sales DESC
");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $label = ucfirst($row['payment_method']);
        $pay_labels[] = $label;
        $pay_totals[] = (float)$row['total_sales'];
        $pay_mix_html .= '<tr>
            <td>' . htmlspecialchars($label) . '</td>
            <td>' . htmlspecialchars($row['total_count']) . '</td>
            <td>₱' . number_format($row['total_sales'], 2) . '</td>
        </tr>';
    }
} else {
    $pay_mix_html = '<tr><td colspan="3" class="text-center text-muted">No paid transactions.</td></tr>';
}

// ----------------- PIPELINE TABLE -----------------
$count_res = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE created_at BETWEEN '$day_start' AND '$day_end'");
$total_rows = $count_res->fetch_assoc()['total'];
$total_pages = max(1, (int)ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$pipeline_html = '';
$res = $conn->query("
    SELECT o.order_id, o.order_number, o.order_type, o.status, ocd.customer_first_name, ocd.customer_last_name, opd.payment_method
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end'
    ORDER BY o.created_at DESC
    LIMIT $per_page OFFSET $offset
");

if ($res && $res->num_rows > 0) {
    while ($order = $res->fetch_assoc()) {
        $status = $order['status'] ?? '';
        $status_map = ['pending'=>'status-pending', 'confirmed'=>'status-confirmed', 'preparing'=>'status-preparing', 'ready'=>'status-ready', 'out_for_delivery'=>'status-out-for-delivery', 'delivered'=>'status-delivered', 'completed'=>'status-completed', 'cancelled'=>'status-cancelled'];
        $status_class = $status_map[$status] ?? 'bg-secondary text-white';
        
        $pipeline_html .= '<tr>
            <td><strong>' . htmlspecialchars($order['order_number'] ?? $order['order_id']) . '</strong></td>
            <td>' . htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''))) . '</td>
            <td>' . htmlspecialchars(ucfirst($order['order_type'])) . '</td>
            <td>' . htmlspecialchars(ucfirst($order['payment_method'] ?? '')) . '</td>
            <td><span class="status-badge ' . $status_class . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) . '</span></td>
        </tr>';
    }
} else {
    $pipeline_html = '<tr><td colspan="5" class="text-center text-muted">No orders for this date.</td></tr>';
}

// ----------------- RESPONSE -----------------
echo json_encode([
    'stats' => $stats,
    'charts' => [
        'orders_per_hour' => $orders_per_hour,
        'revenue_per_hour' => $revenue_per_hour,
        'status_labels' => $status_labels,
        'status_totals' => $status_totals,
        'top_labels' => $top_labels,
        'top_qty' => $top_qty,
        'pay_labels' => $pay_labels,
        'pay_totals' => $pay_totals
    ],
    'html' => [
        'pipeline' => $pipeline_html,
        'top_items' => $top_items_html,
        'pay_mix' => $pay_mix_html
    ],
    'pagination' => [
        'current' => $page,
        'total' => $total_pages
    ]
]);
$conn->close();
?>