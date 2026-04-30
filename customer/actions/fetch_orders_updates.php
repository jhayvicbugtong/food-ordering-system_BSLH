<?php
// customer/actions/fetch_orders_updates.php
// This file returns JSON data containing updated HTML for orders to support real-time updates.

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

// Check auth
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db_connect.php';

$customer_id = (int)$_SESSION['user_id'];

// --- Helper Functions (Duplicated from orders.php to ensure consistent rendering) ---

function formatStatus($status, $order_type) {
    $readyLabel = ($order_type === 'delivery') ? 'Ready for Delivery' : 'Ready for Pickup';

    $status_map = [
        'pending' => ['class' => 'warning', 'label' => 'Pending Confirmation', 'icon' => 'clock'],
        'confirmed' => ['class' => 'primary', 'label' => 'Confirmed', 'icon' => 'check-circle'],
        'preparing' => ['class' => 'info', 'label' => 'Preparing', 'icon' => 'egg-fried'],
        'ready' => ['class' => 'success', 'label' => $readyLabel, 'icon' => 'box-seam'],
        'out_for_delivery' => ['class' => 'success', 'label' => 'Out for Delivery', 'icon' => 'truck'],
        'delivered' => ['class' => 'secondary', 'label' => 'Delivered', 'icon' => 'check-lg'],
        'completed' => ['class' => 'secondary', 'label' => 'Completed', 'icon' => 'award'],
        'cancelled' => ['class' => 'danger', 'label' => 'Cancelled', 'icon' => 'x-circle'],
    ];
    
    $text_class = in_array($status, ['pending', 'preparing']) ? ' text-dark' : '';
    $config = $status_map[$status] ?? ['class' => 'light', 'label' => ucfirst($status), 'icon' => 'question-circle'];
    
    return "<span class=\"badge bg-{$config['class']}{$text_class} d-flex align-items-center gap-1\">
                <i class=\"bi bi-{$config['icon']}\"></i>
                " . htmlspecialchars($config['label']) . "
            </span>";
}

function formatPayment($p_method, $p_status) {
    $p_method = htmlspecialchars(ucfirst($p_method));
    $icon_class = '';
    
    if ($p_status === 'paid') {
        $icon_class = 'text-success bi-check-circle-fill';
        return "<span class=\"d-flex align-items-center gap-1\"><i class=\"bi {$icon_class}\"></i> Paid via {$p_method}</span>";
    }
    if ($p_status === 'pending') {
        $icon_class = 'text-warning bi-clock-fill';
        return "<span class=\"d-flex align-items-center gap-1\"><i class=\"bi {$icon_class}\"></i> Pay via {$p_method} (Pending)</span>";
    }
    if ($p_status === 'failed') {
        $icon_class = 'text-danger bi-x-circle-fill';
        return "<span class=\"d-flex align-items-center gap-1\"><i class=\"bi {$icon_class}\"></i> Payment Failed</span>";
    }
    return "N/A";
}

function getOrderProgress($status, $order_type) {
    $steps = [
        'pending' => ['Pending', 'clock'],
        'confirmed' => ['Confirmed', 'check-circle'],
        'preparing' => ['Preparing', 'egg-fried'],
    ];
    
    if ($order_type === 'pickup') {
        $steps['ready'] = ['Ready for Pickup', 'box-seam'];
        $steps['completed'] = ['Completed', 'award'];
    } else {
        $steps['ready'] = ['Ready', 'box-seam'];
        $steps['out_for_delivery'] = ['Out for Delivery', 'truck'];
        $steps['delivered'] = ['Delivered', 'check-lg'];
    }
    
    $cancelled_step = ['cancelled' => ['Cancelled', 'x-circle']];
    
    if ($status === 'cancelled') {
        $steps = $cancelled_step;
    }
    
    $current_index = array_search($status, array_keys($steps));
    $total_steps = count($steps);
    
    return [
        'steps' => $steps,
        'current_index' => $current_index !== false ? $current_index : 0,
        'total_steps' => $total_steps,
        'progress_percent' => $current_index !== false ? (($current_index + 1) / $total_steps) * 100 : 0
    ];
}

function renderTrackingHTML($order) {
    // Logic matching orders.php: only show for active statuses
    $is_active = in_array($order['status'], ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery']);
    $is_cancelled = $order['status'] === 'cancelled';
    
    if (!$is_active) return '';

    $progress = getOrderProgress($order['status'], $order['order_type']);
    
    $html = '<div class="order-tracking">';
    $html .= '<h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-graph-up me-2"></i>Order Progress</h6>';
    $html .= '<div class="progress-container"><div class="progress-bar" style="width: ' . $progress['progress_percent'] . '%"></div></div>';
    $html .= '<div class="progress-steps">';
    
    $step_index = 0;
    foreach ($progress['steps'] as $step_key => $step_data) {
        $is_active_step = $step_index === $progress['current_index'];
        $is_completed_step = $step_index < $progress['current_index'];
        $is_cancelled_step = $is_cancelled && $step_key === 'cancelled';
        
        $step_class = '';
        if ($is_cancelled_step) $step_class = 'step-cancelled';
        elseif ($is_active_step) $step_class = 'step-active';
        elseif ($is_completed_step) $step_class = 'step-completed';
        
        $html .= '<div class="progress-step ' . $step_class . '">';
        $html .= '<div class="step-icon"><i class="bi bi-' . $step_data[1] . '"></i></div>';
        $html .= '<div class="step-label">' . $step_data[0] . '</div>';
        $html .= '</div>';
        $step_index++;
    }
    $html .= '</div></div>';
    return $html;
}

function renderActionsHTML($order) {
    $is_pending = $order['status'] === 'pending';
    $html = '';
    
    if ($is_pending) {
        $html .= '<a href="#" class="order-card-action-btn danger cancel-order-btn" data-id="' . $order['order_id'] . '">';
        $html .= '<i class="bi bi-x-circle"></i> Cancel</a>';
    }
    
    $html .= '<a href="menu.php?reorder=' . $order['order_id'] . '" class="order-card-action-btn primary">';
    $html .= '<i class="bi bi-arrow-repeat"></i> Reorder</a>';
    
    return $html;
}

// --- Fetch Orders ---

$orders_data = [];
$stmt = $conn->prepare(
    "SELECT o.order_id, o.order_type, o.status, p.payment_method, p.payment_status 
     FROM orders o
     LEFT JOIN order_payment_details p ON o.order_id = p.order_id
     WHERE o.user_id = ? 
     ORDER BY o.created_at DESC"
);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($order = $result->fetch_assoc()) {
    $orders_data[$order['order_id']] = [
        'status' => $order['status'],
        'status_html' => formatStatus($order['status'], $order['order_type']),
        'payment_html' => formatPayment($order['payment_method'], $order['payment_status']),
        'tracking_html' => renderTrackingHTML($order),
        'actions_html' => renderActionsHTML($order)
    ];
}
$stmt->close();
$conn->close();

echo json_encode(['orders' => $orders_data]);
?>