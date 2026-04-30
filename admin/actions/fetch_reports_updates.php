<?php
// admin/actions/fetch_reports_updates.php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Auth Check
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 2. Helpers
function peso($n) { return '₱' . number_format((float)$n, 2); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// 3. Inputs
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-6 days'));
$end   = $_GET['end']   ?? date('Y-m-d');
$revMode = $_GET['rev_mode'] ?? 'completed';
$page  = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$from = $start . ' 00:00:00';
$to   = $end   . ' 23:59:59';

// 4. Calculate Stats
$sum = ['revenue' => 0, 'orders' => 0];

// Revenue
if ($revMode === 'paid') {
    $sql = "SELECT COALESCE(SUM(amount_paid), 0) FROM order_payment_details WHERE paid_at BETWEEN ? AND ? AND payment_status = 'paid'";
} else {
    $sql = "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE created_at BETWEEN ? AND ? AND status IN ('delivered','completed')";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$stmt->bind_result($sum['revenue']);
$stmt->fetch();
$stmt->close();

// Orders Count (Total for Range)
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN ? AND ?");
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$stmt->bind_result($sum['orders']);
$stmt->fetch();
$stmt->close();

$avg_ticket = $sum['orders'] ? $sum['revenue'] / $sum['orders'] : 0;

// 5. Payment Breakdown HTML
$pay_html = '';
$sql = "SELECT 
            COALESCE(payment_method,'Unknown') AS method, 
            COUNT(order_id) AS cnt, 
            COALESCE(SUM(amount_paid),0) AS amt
        FROM order_payment_details
        WHERE paid_at BETWEEN ? AND ? AND payment_status='paid'
        GROUP BY method
        ORDER BY amt DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $pay_html .= '<tr>
            <td class="fw-medium">
                <span class="badge rounded-pill bg-light text-dark border me-1">' . h(ucfirst($r['method'])) . '</span>
            </td>
            <td class="text-end">' . h(number_format($r['cnt'])) . '</td>
            <td class="text-end">' . h(peso($r['amt'])) . '</td>
        </tr>';
    }
} else {
    $pay_html = '<tr><td colspan="3" class="text-center text-muted py-4"><i class="bi bi-info-circle me-1"></i>No paid transactions in this range.</td></tr>';
}
$stmt->close();

// 6. Orders List HTML
$totalOrders = $sum['orders'];
$totalPages = max(1, (int)ceil($totalOrders / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$orders_html = '';
$sql = "SELECT 
            o.order_id, o.order_number, o.created_at, o.order_type, o.status, o.total_amount,
            CONCAT(ocd.customer_first_name, ' ', ocd.customer_last_name) as customer_name,
            opd.payment_status, opd.payment_method
        FROM orders o
        LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
        LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
        WHERE o.created_at BETWEEN ? AND ?
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $from, $to, $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $orders_html .= renderOrderRow($r);
    }
} else {
    $orders_html = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox me-1"></i>No orders in this period.</td></tr>';
}
$stmt->close();

echo json_encode([
    'stats' => [
        'revenue' => h(peso($sum['revenue'])),
        'orders'  => h(number_format($sum['orders'])),
        'avg'     => h(peso($avg_ticket))
    ],
    'html' => [
        'payment' => $pay_html,
        'orders'  => $orders_html
    ],
    'pagination' => [
        'current_page' => $page,
        'total_pages'  => $totalPages,
        'info'         => "Showing page $page of $totalPages • " . number_format($totalOrders) . " orders total"
    ]
]);

$conn->close();

function renderOrderRow($r) {
    $id = $r['order_id'];
    $num = $r['order_number'] ?? $id;
    $created = date('Y-m-d H:i', strtotime($r['created_at']));
    $total = peso($r['total_amount']);
    $type = ucfirst($r['order_type']);
    
    // Customer
    $custName = trim($r['customer_name'] ?? '');
    $initials = 'C';
    if ($custName !== '') {
        $parts = explode(' ', $custName);
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }
    $custDisplay = $custName ?: 'Walk-in / Unknown';

    // Payment Status
    $payStatus = $r['payment_status'] ?? 'unpaid';
    $payBadge = 'bg-secondary-subtle text-secondary';
    if ($payStatus === 'paid') $payBadge = 'bg-success-subtle text-success';
    elseif ($payStatus === 'failed') $payBadge = 'bg-danger-subtle text-danger';
    elseif ($payStatus === 'refunded') $payBadge = 'bg-info-subtle text-info';
    $payLabel = ucfirst($payStatus ?: 'Pending');

    // Payment Method
    $payMethod = $r['payment_method'] ? ucfirst($r['payment_method']) : '—';

    // Order Status
    $status = $r['status'];
    $status_map = [
        'pending' => 'status-pending', 'confirmed' => 'status-confirmed', 
        'preparing' => 'status-preparing', 'ready' => 'status-ready', 
        'out_for_delivery' => 'status-out-for-delivery', 
        'delivered' => 'status-delivered', 'completed' => 'status-completed', 
        'cancelled' => 'status-cancelled'
    ];
    $statusClass = $status_map[$status] ?? 'bg-secondary text-white';
    $statusLabel = ucfirst(str_replace('_', ' ', $status));

    return "
    <tr>
        <td class='fw-semibold'><strong>" . h($num) . "</strong></td>
        <td>
            <div class='d-flex align-items-center gap-2'>
                <div class='avatar-circle'><span>" . h($initials) . "</span></div>
                <span class='text-truncate' style='max-width: 160px;'>" . h($custDisplay) . "</span>
            </div>
        </td>
        <td>" . h($type) . "</td>
        <td><span class='badge " . h($payBadge) . " badge-rounded'>" . h($payLabel) . "</span></td>
        <td class='text-capitalize'>" . h($payMethod) . "</td>
        <td><span class='status-badge " . h($statusClass) . "'>" . h($statusLabel) . "</span></td>
        <td class='text-end fw-semibold'>" . h($total) . "</td>
        <td>" . h($created) . "</td>
    </tr>";
}
?>