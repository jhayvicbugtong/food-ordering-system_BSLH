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
function formatStatus($status) {
    // ... [rest of your helper functions] ...
    $status_map = [
        'pending' => ['class' => 'warning', 'label' => 'Pending Confirmation', 'icon' => 'clock'],
        'confirmed' => ['class' => 'primary', 'label' => 'Confirmed', 'icon' => 'check-circle'],
        'preparing' => ['class' => 'info', 'label' => 'Preparing', 'icon' => 'egg-fried'],
        'ready' => ['class' => 'success', 'label' => 'Ready for Pickup', 'icon' => 'box-seam'],
        'out_for_delivery' => ['class' => 'success', 'label' => 'Out for Delivery', 'icon' => 'truck'],
        'delivered' => ['class' => 'secondary', 'label' => 'Delivered', 'icon' => 'check-lg'],
        'completed' => ['class' => 'secondary', 'label' => 'Completed', 'icon' => 'award'],
        'cancelled' => ['class' => 'danger', 'label' => 'Cancelled', 'icon' => 'x-circle'],
    ];
    // Use 'text-dark' for light-colored badges for better readability
    $text_class = in_array($status, ['pending', 'preparing']) ? ' text-dark' : '';
    $config = $status_map[$status] ?? ['class' => 'light', 'label' => ucfirst($status), 'icon' => 'question-circle'];
    
    return "<span class=\"badge bg-{$config['class']}{$text_class} d-flex align-items-center gap-1\">
                <i class=\"bi bi-{$config['icon']}\"></i>
                " . htmlspecialchars($config['label']) . "
            </span>";
}

// Helper function for payment status
function formatPayment($p_method, $p_status) {
    // ... [rest of your helper functions] ...
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
    // ... [rest of your helper functions] ...
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
// ... [rest of your data fetching logic] ...
$stmt_orders = $conn->prepare(
    "SELECT o.order_id, o.order_number, o.order_type, o.status, o.total_amount, o.created_at, 
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
            "SELECT product_name, quantity, total_price 
             FROM order_items 
             WHERE order_id = ?"
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
        
        $order['items'] = $items; // Attach items to the order array
        $order['progress'] = getOrderProgress($order['status'], $order['order_type']);
        $orders[] = $order; // Add the complete order to the list
    }
}
$stmt_orders->close();
$conn->close();

// --- END: Fetch Order Data ---
// --- START OF CORRECTION ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>My Orders | Bente Sais Lomi House</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/customer.css"/>

  <style>
    /* ... [your existing <style> block for this page] ... */
    .page-my-orders {
        background-color: #f8f9fa;
    }
    
    .orders-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
    }
    
    .order-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
    }
    
    .order-card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    
    .order-card-header {
        background-color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
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
        border-bottom: 1px solid #dee2e6;
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
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }
    
    .order-type-badge {
        background-color: #e7f1ff;
        color: #0d6efd;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .reorder-btn {
        transition: all 0.3s ease;
    }
    
    .reorder-btn:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Order Tracking Progress Styles */
    .order-tracking {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
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
        background: linear-gradient(90deg, #198754, #20c997);
        border-radius: 10px;
        transition: width 0.5s ease;
        width: 0%;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .step-active .step-icon {
        background-color: #198754;
        color: white;
        transform: scale(1.1);
    }
    
    .step-completed .step-icon {
        background-color: #198754;
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
        color: #198754;
        font-weight: 600;
    }
    
    .step-completed .step-label {
        color: #198754;
    }
    
    .step-cancelled .step-label {
        color: #dc3545;
        font-weight: 600;
    }
    
    .estimated-time {
        background-color: #e7f1ff;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-top: 1rem;
        border-left: 4px solid #0d6efd;
    }
    
    .time-badge {
        background-color: #0d6efd;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
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
    }
  </style>
</head>
<body class="page-my-orders">

<?php 
// Include the header *inside* the body
include __DIR__ . '/includes/header.php'; 
?>

<div class="orders-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 fw-bold mb-2">My Orders</h1>
                <p class="text-muted mb-0">Track and manage your food orders</p>
            </div>
            <div class="col-md-4 text-md-end">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h5 fw-semibold text-muted">Recent Orders (<?= count($orders) ?>)</h2>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">All Orders</a></li>
                    <li><a class="dropdown-item" href="#">Pending</a></li>
                    <li><a class="dropdown-item" href="#">Completed</a></li>
                    <li><a class="dropdown-item" href="#">Cancelled</a></li>
                </ul>
            </div>
        </div>
        
        <div class="accordion" id="ordersAccordion">
          <?php foreach ($orders as $index => $order): 
                $progress = $order['progress'];
                $is_cancelled = $order['status'] === 'cancelled';
          ?>
            <div class="order-card">
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
                    <div class="flex-shrink-0">
                        <?= formatStatus($order['status']) ?>
                    </div>
                    <div class="fw-bold text-dark fs-5">
                        ₱<?= number_format($order['total_amount'], 2) ?>
                    </div>
                    <div class="flex-shrink-0">
                      <i class="bi bi-chevron-down transition-rotate <?= $index === 0 ? 'rotate-180' : '' ?>" style="transition: transform 0.3s ease;"></i>
                    </div>
                  </div>
                </div>
              </div>
              
              <div id="collapse<?= $order['order_id'] ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $order['order_id'] ?>" data-bs-parent="#ordersAccordion">
                <div class="order-card-body">
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
                          $is_active = $step_index === $progress['current_index'];
                          $is_completed = $step_index < $progress['current_index'];
                          $is_cancelled_step = $is_cancelled && $step_key === 'cancelled';
                          
                          $step_class = '';
                          if ($is_cancelled_step) {
                              $step_class = 'step-cancelled';
                          } elseif ($is_active) {
                              $step_class = 'step-active';
                          } elseif ($is_completed) {
                              $step_class = 'step-completed';
                          }
                      ?>
                        <div class="progress-step <?= $step_class ?>">
                          <div class="step-icon">
                            <i class="bi bi-<?= $step_data[1] ?>"></i>
                          </div>
                          <div class="step-label"><?= $step_data[0] ?></div>
                        </div>
                      <?php 
                      $step_index++;
                      endforeach; 
                      ?>
                    </div>
                    
                    <?php if (!$is_cancelled): ?>
                    
                    <?php endif; ?>
                  </div>
                  
                  <div class="row gy-4">
                    <div class="col-md-8">
                      <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-basket me-2"></i>Order Items
                      </h6>
                      <?php if (empty($order['items'])): ?>
                        <div class="alert alert-light text-center py-4">
                          <i class="bi bi-exclamation-circle text-muted d-block mb-2" style="font-size: 2rem;"></i>
                          <p class="text-muted mb-0">No items found for this order.</p>
                        </div>
                      <?php else: ?>
                        <div class="table-responsive">
                          <table class="table table-sm order-items-table">
                            <tbody>
                              <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                  <td class="ps-0">
                                    <div class="fw-medium"><?= htmlspecialchars($item['product_name']) ?></div>
                                  </td>
                                  <td class="text-center" style="width: 80px;">
                                    <span class="badge bg-light text-dark">x <?= htmlspecialchars($item['quantity']) ?></span>
                                  </td>
                                  <td class="text-end pe-0 fw-medium" style="width: 100px;">
                                    ₱<?= number_format($item['total_price'], 2) ?>
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
                          <span class="fw-medium text-dark text-end"><?= formatPayment($order['payment_method'], $order['payment_status']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Type:</span>
                          <span class="fw-medium text-dark"><?= htmlspecialchars(ucfirst($order['order_type'])) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Placed:</span>
                          <span class="fw-medium text-dark text-end"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Time:</span>
                          <span class="fw-medium text-dark text-end"><?= date('g:i A', strtotime($order['created_at'])) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                          <span class="text-muted">Total:</span>
                          <span class="fw-bold text-dark">₱<?= number_format($order['total_amount'], 2) ?></span>
                        </li>
                      </ul>
                      
                      <div class="mt-4 pt-3 border-top">
                        <a href="menu.php?reorder=<?= $order['order_id'] ?>" class="btn btn-outline-success reorder-btn w-100 disabled">
                            <i class="bi bi-arrow-repeat me-2"></i> Re-order Items
                        </a>
                        <small class="text-muted d-block text-center mt-2" style="font-size: 0.75rem;">(Re-order feature coming soon)</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ... [rest of your <script> block for this page] ...
    // Rotate chevron when accordion is toggled
    const accordionHeaders = document.querySelectorAll('.order-card-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const chevron = this.querySelector('.bi-chevron-down');
            if (chevron) {
                chevron.classList.toggle('rotate-180');
            }
        });
    });
    
    // Auto-close other accordion items when one is opened (optional)
    const accordionItems = document.querySelectorAll('.order-card');
    accordionItems.forEach(item => {
        const header = item.querySelector('.order-card-header');
        const collapse = item.querySelector('.collapse');
        
        header.addEventListener('click', function() {
            // If this item is being opened, close others
            if (!collapse.classList.contains('show')) {
                accordionItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        const otherCollapse = otherItem.querySelector('.collapse');
                        const otherChevron = otherItem.querySelector('.bi-chevron-down');
                        if (otherCollapse.classList.contains('show')) {
                            otherCollapse.classList.remove('show');
                            if (otherChevron) {
                                otherChevron.classList.remove('rotate-180');
                            }
                        }
                    }
                });
            }
        });
    });
    
    // Animate progress bars when they come into view
    const progressBars = document.querySelectorAll('.progress-bar');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                const targetWidth = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = targetWidth;
                }, 300);
            }
        });
    });
    
    progressBars.forEach(bar => {
        observer.observe(bar);
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
<?php // --- END OF CORRECTION --- ?>