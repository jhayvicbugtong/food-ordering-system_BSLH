<?php
// customer/orders.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to login if not a customer
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $BASE_URL = rtrim(preg_replace('#/customer(/.*)?$#', '', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')), '/');
    if ($BASE_URL === '/') $BASE_URL = '';
    $next = $BASE_URL . '/customer/orders.php';
    header('Location: ' . $BASE_URL . '/customer/auth/login.php?next=' . urlencode($next));
    exit;
}

// --- START: Fetch Order Data ---
include __DIR__ . '/../includes/db_connect.php'; // Get $conn
$customer_id = (int)$_SESSION['user_id'];

// Helper function for status badges
function formatStatus($status, $order_type) {
    // Dynamic label based on order type
    $readyLabel = ($order_type === 'delivery') ? 'Ready for Delivery' : 'Ready for Pickup';

    $status_map = [
        'pending' => ['class' => 'warning', 'label' => 'Pending Confirmation', 'icon' => 'clock'],
        'confirmed' => ['class' => 'primary', 'label' => 'Confirmed', 'icon' => 'check-circle'],
        'preparing' => ['class' => 'info', 'label' => 'Preparing', 'icon' => 'egg-fried'],
        'ready' => ['class' => 'success', 'label' => $readyLabel, 'icon' => 'box-seam'], // Use variable here
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

// Helper function for payment status
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

// Helper function for order tracking progress
function getOrderProgress($status, $order_type) {
    $steps = [
        'pending' => ['Pending', 'clock'],
        'confirmed' => ['Confirmed', 'check-circle'],
        'preparing' => ['Preparing', 'egg-fried'],
    ];
    
    // Different steps for pickup vs delivery
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

// Fetch all orders for this user
$orders = [];
$stmt_orders = $conn->prepare(
    "SELECT o.order_id, o.order_number, o.order_type, o.status, o.total_amount, o.created_at, 
            o.delivery_fee, o.tip_amount, o.subtotal,
            p.payment_method, p.payment_status 
     FROM orders o
     LEFT JOIN order_payment_details p ON o.order_id = p.order_id
     WHERE o.user_id = ? 
     ORDER BY o.created_at DESC"
);
$stmt_orders->bind_param('i', $customer_id);
$stmt_orders->execute();
$orders_result = $stmt_orders->get_result();

if ($orders_result) {
    while ($order = $orders_result->fetch_assoc()) {
        // Fetch items for this order
        $items = [];
        $stmt_items = $conn->prepare(
            "SELECT oi.product_id, oi.product_name, oi.quantity, oi.total_price, p.image_url 
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.product_id
             WHERE oi.order_id = ?"
        );
        $stmt_items->bind_param('i', $order['order_id']);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }
        $stmt_items->close();
        
        $order['items'] = $items; 
        $order['progress'] = getOrderProgress($order['status'], $order['order_type']);
        $orders[] = $order; 
    }
}
$stmt_orders->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>My Orders | Bente Sais Lomi House</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>

  <style>
    :root {
        --primary-color: #5cfa63;
        --primary-dark: #4cd853;
        --primary-light: #e8f7e9;
        --accent-color: #ff6b35;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8f9fa;
        --border-light: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.05);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8f9fa;
        color: var(--text-dark);
        line-height: 1.6;
    }
    
    .page-my-orders {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    
    .orders-header {
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border-light);
        padding: 2rem 0;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .orders-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 100%;
        background: linear-gradient(90deg, transparent 0%, rgba(92, 250, 99, 0.05) 100%);
        z-index: 0;
    }
    
    .order-card {
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        border: 1px solid var(--border-light);
        margin-bottom: 1.5rem;
        background: #fff;
        position: relative;
    }
    
    .order-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    
    .order-card-header {
        background-color: white;
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-light);
        cursor: pointer;
        position: relative;
    }
    
    .order-card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(90deg, var(--primary-color) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .order-card-header:hover::after {
        opacity: 1;
    }
    
    .order-card-body {
        background-color: #fdfdfd;
        padding: 1.5rem;
    }
    
    .order-status-badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
        border-radius: 50px;
    }
    
    .order-items-table {
        width: 100%;
    }
    
    .order-items-table tr:last-child {
        border-bottom: 1px solid var(--border-light);
    }
    
    .order-details-list li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .order-details-list li:last-child {
        border-bottom: none;
    }
    
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1.5rem;
        opacity: 0.7;
    }
    
    .order-type-badge {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .reorder-btn, .cancel-order-btn {
        transition: all 0.3s ease;
        border-radius: var(--radius-sm);
        font-weight: 500;
    }
    
    .reorder-btn:hover:not(.disabled), .cancel-order-btn:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .reorder-btn {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #000;
    }
    
    .reorder-btn:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    
    /* Simplified Order Tracking Progress Styles */
    .order-tracking {
        background: white;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-light);
        position: relative;
    }
    
    .order-tracking::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-color), var(--accent-color));
        border-radius: var(--radius-md) 0 0 var(--radius-md);
    }
    
    .progress-container {
        position: relative;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 10px;
        margin: 1.5rem 0;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        background-color: var(--primary-color);
        border-radius: 10px;
        transition: width 0.5s ease;
        width: 0%;
        position: relative;
    }
    
    .progress-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--primary-color);
        border-radius: 10px;
    }
    
    .progress-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-top: 1rem;
    }
    
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
        background-color: #e9ecef;
        color: #6c757d;
        border: 3px solid white;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }
    
    .step-active .step-icon {
        background-color: var(--primary-color);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.2);
    }
    
    .step-completed .step-icon {
        background-color: var(--primary-color);
        color: white;
    }
    
    .step-cancelled .step-icon {
        background-color: #dc3545;
        color: white;
    }
    
    .step-label {
        font-size: 0.75rem;
        text-align: center;
        font-weight: 500;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .step-active .step-label {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .step-completed .step-label {
        color: var(--primary-color);
    }
    
    .step-cancelled .step-label {
        color: #dc3545;
        font-weight: 600;
    }
    
    /* Enhanced Filter Toolbar */
    .filter-toolbar {
        background: white;
        border-radius: var(--radius-md);
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text-light);
        border: 1px solid transparent;
        white-space: nowrap;
    }
    
    .filter-tab:hover {
        background-color: var(--primary-light);
        color: var(--primary-dark);
    }
    
    .filter-tab.active {
        background-color: var(--primary-color);
        color: #000;
    }
    
    .order-search-box {
        position: relative;
        min-width: 280px;
    }
    
    .order-search-box .form-control {
        padding-left: 2.5rem;
        border-radius: 50px;
        border-color: var(--border-light);
        background-color: #fcfcfc;
    }
    
    .order-search-box .form-control:focus {
        background-color: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(92, 250, 99, 0.15);
    }
    
    .order-search-box .bi-search {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .order-count-badge {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 50px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    .order-summary-card {
        background: white;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        margin-bottom: 1.5rem;
    }
    
    .order-summary-title {
        font-size: 0.9rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }
    
    .order-summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }
    
    .order-summary-change {
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .order-summary-change.positive {
        color: var(--primary-color);
    }
    
    .order-summary-change.negative {
        color: #e53e3e;
    }
    
    .order-card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .order-card-action-btn {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }
    
    .order-card-action-btn.primary {
        background-color: var(--primary-light);
        color: var(--primary-dark);
    }
    
    .order-card-action-btn.primary:hover {
        background-color: var(--primary-color);
        color: #000;
    }
    
    .order-card-action-btn.secondary {
        background-color: #f1f5f9;
        color: var(--text-light);
    }
    
    .order-card-action-btn.secondary:hover {
        background-color: #e2e8f0;
        color: var(--text-dark);
    }
    
    .order-card-action-btn.danger {
        background-color: #fed7d7;
        color: #c53030;
    }
    
    .order-card-action-btn.danger:hover {
        background-color: #feb2b2;
        color: #9b2c2c;
    }
    
    .order-item-image {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-sm);
        object-fit: cover;
        margin-right: 1rem;
        background-color: #f8f9fa;
    }
    
    .order-item-details {
        display: flex;
        align-items: center;
    }
    
    .order-item-name {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .order-item-price {
        color: var(--text-light);
        font-size: 0.9rem;
    }
    
    .order-item-quantity {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        border-radius: 50px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .order-card-header {
            padding: 1rem;
        }
        
        .order-card-body {
            padding: 1rem;
        }
        
        .order-details-list {
            margin-top: 1.5rem;
        }
        
        .progress-step {
            flex: 0 0 auto;
            width: 80px;
        }
        
        .step-label {
            font-size: 0.7rem;
        }
        
        .progress-steps {
            justify-content: flex-start;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .order-card-actions {
            flex-direction: column;
        }
        
        .order-summary-card {
            padding: 1rem;
        }
        
        .order-summary-value {
            font-size: 1.25rem;
        }

        /* Responsive Filter Toolbar */
        .filter-toolbar {
            flex-direction: column;
            align-items: stretch;
            padding: 1rem;
            gap: 1rem;
        }
        
        .filter-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 4px;
            margin: 0 -4px; /* Slight offset to align scroll with edge */
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }

        .filter-tabs::-webkit-scrollbar {
            display: none; /* Safari/Chrome */
        }
        
        .filter-tab {
            flex-shrink: 0; /* Don't shrink tabs */
        }

        .order-search-box {
            width: 100%;
        }
    }
  </style>
</head>
<body class="page-my-orders">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="orders-header">
    <div class="container position-relative" style="z-index: 5;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 fw-bold mb-2">My Orders</h1>
                <p class="text-muted mb-0">Track and manage your food orders</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="menu.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Order
                </a>
            </div>
        </div>
    </div>
</div>

<main class="container py-4" style="min-height: 60vh;">
  <div class="row">
    <div class="col-12">
      <?php if (empty($orders)): ?>
        <div class="empty-state">
          <i class="bi bi-receipt empty-state-icon"></i>
          <h3 class="h5 text-muted mb-3">You haven't placed any orders yet</h3>
          <p class="text-muted mb-4">When you do, they will show up here with all the details.</p>
          <a href="menu.php" class="btn btn-primary btn-lg">
            <i class="bi bi-egg-fried me-2"></i>Browse Menu
          </a>
        </div>
      <?php else: ?>
        <div class="row mb-4 g-3">
          <div class="col-6 col-md-3">
            <div class="order-summary-card h-100">
              <div class="order-summary-title">Total Orders</div>
              <div class="order-summary-value"><?= count($orders) ?></div>
              <div class="order-summary-change positive"><i class="bi bi-arrow-up"></i> All time</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="order-summary-card h-100">
              <div class="order-summary-title">Pending</div>
              <div class="order-summary-value"><?= count(array_filter($orders, function($order) { return in_array($order['status'], ['pending', 'confirmed', 'preparing']); })) ?></div>
              <div class="order-summary-change positive"><i class="bi bi-clock"></i> Active</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="order-summary-card h-100">
              <div class="order-summary-title">Completed</div>
              <div class="order-summary-value"><?= count(array_filter($orders, function($order) { return in_array($order['status'], ['completed', 'delivered']); })) ?></div>
              <div class="order-summary-change positive"><i class="bi bi-check-circle"></i> Delivered</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="order-summary-card h-100">
              <div class="order-summary-title">Total Spent</div>
              <div class="order-summary-value">₱<?= number_format(array_sum(array_column($orders, 'total_amount')), 2) ?></div>
             <div class="order-summary-change positive" style="font-weight: 600;">₱ All orders</div>
            </div>
          </div>
        </div>

        <div class="filter-toolbar">
          <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">All</div>
            <div class="filter-tab" data-filter="pending">Pending</div>
            <div class="filter-tab" data-filter="completed">Completed</div>
            <div class="filter-tab" data-filter="cancelled">Cancelled</div>
          </div>
          <div class="order-search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="orderSearch" class="form-control" placeholder="Search order # or item...">
          </div>
        </div>
        
        <div id="noSearchResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-search text-muted fs-1 d-block mb-3"></i>
            <h5 class="text-muted">No matching orders found</h5>
        </div>

        <div class="accordion" id="ordersAccordion">
          <?php foreach ($orders as $index => $order): 
                $progress = $order['progress'];
                $is_active = in_array($order['status'], ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery']);
                $is_delivery = $order['order_type'] === 'delivery';
                $has_tip = $order['tip_amount'] > 0;
                
                // Search Data
                $item_text = '';
                foreach($order['items'] as $it) { $item_text .= $it['product_name'] . ' '; }
                $search_data = strtolower($order['order_number'] . ' ' . $item_text . ' ' . $order['status']);
          ?>
            <div class="order-card" data-order-id="<?= $order['order_id'] ?>" data-status="<?= $order['status'] ?>" data-search="<?= htmlspecialchars($search_data) ?>">
              <div class="order-card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapse<?= $order['order_id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $order['order_id'] ?>">
                <div class="d-flex flex-column flex-md-row w-100 align-items-md-center gap-3">
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                      <span class="fw-bold me-2">Order #<?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></span>
                      <span class="order-type-badge"><?= htmlspecialchars(ucfirst($order['order_type'])) ?></span>
                    </div>
                    <small class="text-muted d-flex align-items-center">
                      <i class="bi bi-calendar-event me-1"></i>
                      <?= date('M d, Y - g:i A', strtotime($order['created_at'])) ?>
                    </small>
                  </div>
                  
                  <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="flex-shrink-0 js-status-container">
                        <?= formatStatus($order['status'], $order['order_type']) ?>
                    </div>
                    <div class="fw-bold text-dark fs-5">
                        ₱<?= number_format($order['total_amount'], 2) ?>
                    </div>
                    <div class="flex-shrink-0">
                      <i class="bi bi-chevron-down transition-rotate <?= $index === 0 ? 'rotate-180' : '' ?>"></i>
                    </div>
                  </div>
                </div>
              </div>
              
              <div id="collapse<?= $order['order_id'] ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $order['order_id'] ?>" data-bs-parent="#ordersAccordion">
                <div class="order-card-body">
                  
                  <div class="js-tracking-container">
                  <?php if ($is_active): ?>
                  <div class="order-tracking">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                      <i class="bi bi-graph-up me-2"></i>Order Progress
                    </h6>
                    <div class="progress-container">
                      <div class="progress-bar" style="width: <?= $progress['progress_percent'] ?>%"></div>
                    </div>
                    <div class="progress-steps">
                      <?php 
                      $step_index = 0;
                      foreach ($progress['steps'] as $step_key => $step_data): 
                          $is_active_step = $step_index === $progress['current_index'];
                          $is_completed_step = $step_index < $progress['current_index'];
                          $is_cancelled_step = $order['status'] === 'cancelled' && $step_key === 'cancelled';
                          
                          $step_class = '';
                          if ($is_cancelled_step) $step_class = 'step-cancelled';
                          elseif ($is_active_step) $step_class = 'step-active';
                          elseif ($is_completed_step) $step_class = 'step-completed';
                      ?>
                        <div class="progress-step <?= $step_class ?>">
                          <div class="step-icon"><i class="bi bi-<?= $step_data[1] ?>"></i></div>
                          <div class="step-label"><?= $step_data[0] ?></div>
                        </div>
                      <?php $step_index++; endforeach; ?>
                    </div>
                  </div>
                  <?php endif; ?>
                  </div>
                  
                  <div class="row gy-4">
                    <div class="col-md-8">
                      <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-basket me-2"></i>Order Items
                      </h6>
                      <?php if (empty($order['items'])): ?>
                        <div class="alert alert-light text-center py-4">No items found.</div>
                      <?php else: ?>
                        <div class="table-responsive">
                          <table class="table table-sm order-items-table">
                            <tbody>
                              <?php foreach ($order['items'] as $item): 
                                $image_url = $item['image_url'] ? htmlspecialchars($BASE_URL . '/' . $item['image_url']) : $BASE_URL . '/assets/images/placeholder-food.jpg';
                              ?>
                                <tr>
                                  <td class="ps-0">
                                    <div class="order-item-details">
                                      <img src="<?= $image_url ?>" class="order-item-image" onerror="this.src='<?= $BASE_URL ?>/assets/images/placeholder-food.jpg'">
                                      <div>
                                        <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <div class="order-item-price">₱<?= number_format($item['total_price'], 2) ?></div>
                                      </div>
                                    </div>
                                  </td>
                                  <td class="text-center" style="width: 80px;">
                                    <span class="order-item-quantity">x <?= htmlspecialchars($item['quantity']) ?></span>
                                  </td>
                                  <td class="text-end pe-0 fw-medium" style="width: 100px;">
                                    ₱<?= number_format($item['total_price'] * $item['quantity'], 2) ?>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                      <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>Order Details
                      </h6>
                      <ul class="list-unstyled order-details-list">
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Payment:</span>
                          <span class="fw-medium text-dark text-end js-payment-container">
                              <?= formatPayment($order['payment_method'], $order['payment_status']) ?>
                          </span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Type:</span>
                          <span class="fw-medium text-dark"><?= htmlspecialchars(ucfirst($order['order_type'])) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Total:</span>
                          <span class="fw-bold text-dark">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </li>
                      </ul>
                      
                      <div class="order-card-actions js-actions-container">
                        <?php if ($order['status'] === 'pending'): ?>
                            <a href="#" class="order-card-action-btn danger cancel-order-btn" data-id="<?= $order['order_id'] ?>">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        <?php endif; ?>
                        
                        <a href="menu.php?reorder=<?= $order['order_id'] ?>" class="order-card-action-btn primary">
                            <i class="bi bi-arrow-repeat"></i> Reorder
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- REALTIME POLLING LOGIC ---
    function fetchUpdates() {
        fetch('actions/fetch_orders_updates.php')
            .then(response => response.json())
            .then(data => {
                if (data.orders) {
                    updateOrdersUI(data.orders);
                }
            })
            .catch(err => console.error('Polling error:', err));
    }

    function updateOrdersUI(ordersData) {
        // Iterate through valid order IDs
        for (const [orderId, orderData] of Object.entries(ordersData)) {
            const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
            if (!card) continue;

            const currentStatus = card.getAttribute('data-status');
            
            // Only update DOM if necessary (you can expand this check)
            
            // 1. Update Status Badge
            const statusContainer = card.querySelector('.js-status-container');
            if (statusContainer && statusContainer.innerHTML !== orderData.status_html) {
                statusContainer.innerHTML = orderData.status_html;
            }

            // 2. Update Tracking/Progress
            const trackingContainer = card.querySelector('.js-tracking-container');
            if (trackingContainer && trackingContainer.innerHTML !== orderData.tracking_html) {
                trackingContainer.innerHTML = orderData.tracking_html;
            }

            // 3. Update Payment Status
            const paymentContainer = card.querySelector('.js-payment-container');
            if (paymentContainer && paymentContainer.innerHTML !== orderData.payment_html) {
                paymentContainer.innerHTML = orderData.payment_html;
            }

            // 4. Update Actions (Buttons)
            const actionsContainer = card.querySelector('.js-actions-container');
            if (actionsContainer && actionsContainer.innerHTML !== orderData.actions_html) {
                actionsContainer.innerHTML = orderData.actions_html;
                // Re-bind Cancel event listener since HTML was replaced
                bindCancelButtons(actionsContainer);
            }

            // 5. Update Card Data Attribute (for filtering)
            if (currentStatus !== orderData.status) {
                card.setAttribute('data-status', orderData.status);
            }
        }
        // Refresh filtering in case statuses changed visibility
        filterOrders();
    }

    // Poll every 1 seconds
    setInterval(fetchUpdates, 1000);

    // --- RE-BINDING EVENT LISTENERS ---
    function bindCancelButtons(container = document) {
        const btns = container.querySelectorAll('.cancel-order-btn');
        btns.forEach(btn => {
            // Remove old listener if exists to prevent duplicates (simple cloning trick)
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleCancelOrder(this);
            });
        });
    }

    function handleCancelOrder(btnEl) {
        const orderId = btnEl.dataset.id;
        const originalText = btnEl.innerHTML;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btnEl.disabled = true;
                btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...';

                fetch('actions/cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Cancelled!', 'Your order has been cancelled.', 'success');
                        // Force immediate update
                        fetchUpdates();
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to cancel order.', 'error');
                        btnEl.disabled = false;
                        btnEl.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalText;
                });
            }
        });
    }

    // Initial binding
    bindCancelButtons();

    // --- UNIFIED FILTER & SEARCH LOGIC ---
    const filterTabs = document.querySelectorAll('.filter-tab');
    const searchInput = document.getElementById('orderSearch');
    const noResultsMsg = document.getElementById('noSearchResults');

    window.filterOrders = function() { // Expose to global so updateOrdersUI can call it
        const query = searchInput.value.toLowerCase().trim();
        const activeTab = document.querySelector('.filter-tab.active').dataset.filter;
        let visibleCount = 0;

        document.querySelectorAll('.order-card').forEach(card => {
            const status = card.getAttribute('data-status'); // Use getAttribute for dynamic updates
            const searchableText = card.dataset.search;

            // 1. Check Tab Filter
            let matchesTab = (activeTab === 'all');
            if (activeTab === 'pending') matchesTab = ['pending', 'confirmed', 'preparing'].includes(status);
            if (activeTab === 'completed') matchesTab = ['completed', 'delivered'].includes(status);
            if (activeTab === 'cancelled') matchesTab = (status === 'cancelled');

            // 2. Check Search Query
            const matchesSearch = !query || searchableText.includes(query);

            // 3. Show/Hide
            if (matchesTab && matchesSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle "No Results"
        if (visibleCount === 0 && document.querySelectorAll('.order-card').length > 0) {
             noResultsMsg.style.display = 'block';
        } else {
            noResultsMsg.style.display = 'none';
        }
    }
    
    // UI Event Listeners
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterOrders();
        });
    });
    searchInput.addEventListener('keyup', filterOrders);
    
    // Initial Filter
    filterOrders();
    
    // Accordion Logic (Unchanged)
    const accordionHeaders = document.querySelectorAll('.order-card-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const chevron = this.querySelector('.bi-chevron-down');
            if (chevron) chevron.classList.toggle('rotate-180');
        });
    });
    // ... (rest of existing accordion logic if needed) ...
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>