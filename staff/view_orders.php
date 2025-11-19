<?php
include __DIR__ . '/includes/header.php'; // Includes auth and db_connect

// Fetch orders for the table
$orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status,
        o.created_at,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        opd.payment_method,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
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
    LIMIT 50;
";
$orders_result = $conn->query($orders_query);
?>

<!-- Modernized layout + orders queue styles -->
<style>
  body {
    background-color: #f3f4f6;
  }

  .main-content {
    min-height: 100vh;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }

  .page-title {
    font-weight: 600;
    font-size: 1.3rem;
  }

  .page-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
  }

  /* Modern card */
  .content-card {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
    padding: 18px 20px;
    margin-bottom: 1.5rem;
  }

  .content-card-header {
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    padding-bottom: 10px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .content-card-header h2 {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 4px;
  }

  .content-card-header p {
    font-size: 0.8rem;
    margin-bottom: 0;
    color: #6b7280;
  }

  .meta-text {
    font-size: 0.8rem;
    color: #9ca3af;
  }

  /* Table styling */
  .orders-queue-table {
    margin-bottom: 0;
  }

  .orders-queue-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
  }

  .orders-queue-table th,
  .orders-queue-table td {
    font-size: 0.9rem;
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    vertical-align: top;
  }

  .orders-queue-table td small {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Pills / chips */
  .status-badge,
  .payment-badge,
  .source-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }

  /* Source pills */
  .source-pill.delivery {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid rgba(37, 99, 235, 0.18);
  }

  .source-pill.pickup {
    background: #ecfdf3;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.18);
  }

  /* Payment pills */
  .payment-badge.badge-success,
  .payment-badge.bg-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.15);
  }

  .payment-badge.badge-warning,
  .payment-badge.bg-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid rgba(146, 64, 14, 0.12);
  }

  .payment-badge.badge-secondary,
  .payment-badge.bg-secondary {
    background: #e5e7eb;
    color: #374151;
    border: 1px solid rgba(55, 65, 81, 0.12);
  }

  /* Status pills */
  .status-badge.badge-warning,
  .status-badge.bg-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid rgba(146, 64, 14, 0.12);
  }

  .status-badge.badge-success,
  .status-badge.bg-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.15);
  }

  .status-badge.badge-secondary,
  .status-badge.bg-secondary {
    background: #e5e7eb;
    color: #374151;
    border: 1px solid rgba(55, 65, 81, 0.12);
  }

  .status-badge.badge-info,
  .status-badge.bg-info {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid rgba(3, 105, 161, 0.15);
  }

  .actions-cell .btn {
    white-space: nowrap;
  }

  @media (max-width: 576px) {
    .content-card {
      padding: 14px 14px;
    }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <!-- Top header card -->
    <div class="content-card mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <h2 class="page-title mb-1">Orders Queue</h2>
          <p class="page-subtitle mb-1">All active orders that still need action.</p>
          <p class="meta-text mb-0">
            Sorted by status and time placed. Use the actions on the right to move orders through the pipeline.
          </p>
        </div>
        <div class="text-end">
          <button class="btn btn-success btn-sm" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Orders table card -->
    <section class="content-card">
      <div class="content-card-header">
        <div>
          <h2>Active Orders</h2>
          <p>Pending, confirmed, preparing, ready, and out-for-delivery.</p>
        </div>
        <div class="text-end meta-text">
          <span class="d-block">Max 50 latest active orders</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover orders-queue-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Placed</th>
              <th>Items</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
              <?php while($order = $orders_result->fetch_assoc()): ?>
                <?php
                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'badge-warning',
                    'confirmed'        => 'badge-info',
                    'preparing'        => 'badge-info',
                    'ready'            => 'badge-success',
                    'out_for_delivery' => 'badge-success',
                  ];
                  $status_class   = $status_map[$status] ?? 'badge-secondary';

                  $customer_name  = htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')));
                  if ($customer_name === '') {
                    $customer_name = 'Walk-in Customer';
                  }

                  $order_id       = (int)$order['order_id'];
                  $payment_status = $order['payment_status'] ?? null;
                  $created_time   = $order['created_at']
                    ? date('g:i A', strtotime($order['created_at']))
                    : '';

                  // Payment display
                  $payment_method = $order['payment_method'] ?? null;
                  if (!$payment_method) {
                    $payment_label = 'Unpaid';
                    $payment_badge_class = 'badge-secondary';
                  } else {
                    $payment_label = strtoupper($payment_method);
                    if ($payment_status && $payment_status !== 'paid') {
                      $payment_label .= ' (' . ucfirst($payment_status) . ')';
                    } else {
                      $payment_label .= ' (Paid)';
                    }
                    $payment_badge_class = ($payment_status === 'paid')
                      ? 'badge-success'
                      : 'badge-warning';
                  }

                  // Source pill class
                  if ($order['order_type'] == 'delivery') {
                    $source_class = 'delivery';
                    $source_label = 'Delivery';
                  } else {
                    $source_class = 'pickup';
                    $source_label = 'Pickup';
                  }
                ?>
                <tr data-order-id="<?= $order_id ?>">
                  <td>
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order_id) ?></strong><br>
                    <?php if ($created_time): ?>
                      <span class="meta-text">Placed: <?= htmlspecialchars($created_time) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= $created_time ? htmlspecialchars($created_time) : '—' ?></td>
                  <td>
                    <ul class="list-unstyled mb-0" style="padding-left: 15px; font-size: 0.85em;">
                      <?php
                        // Fetch items for this order
                        $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ?");
                        $items_stmt->bind_param('i', $order_id);
                        $items_stmt->execute();
                        $items_result = $items_stmt->get_result();
                        while($item = $items_result->fetch_assoc()):
                      ?>
                        <li><?= htmlspecialchars($item['product_name']) ?> x <strong><?= (int)$item['quantity'] ?></strong></li>
                      <?php endwhile; $items_stmt->close(); ?>
                    </ul>
                  </td>
                  <td><?= $customer_name ?></td>
                  <td>
                    <span class="source-pill <?= $source_class ?>">
                      <?= htmlspecialchars($source_label) ?>
                    </span><br>
                    <span class="payment-badge badge <?= $payment_badge_class ?> mt-1 d-inline-block">
                      <?= htmlspecialchars($payment_label) ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <div class="btn-group btn-group-sm">
                      <?php if ($status == 'pending'): ?>
                        <button class="btn btn-outline-success btn-action" data-action="confirm" data-id="<?= $order_id ?>">Accept</button>
                        <button class="btn btn-outline-danger btn-action" data-action="cancel" data-id="<?= $order_id ?>">Reject</button>

                      <?php elseif ($status == 'confirmed'): ?>
                        <button class="btn btn-outline-primary btn-action" data-action="prepare" data-id="<?= $order_id ?>">Start Prep</button>

                      <?php elseif ($status == 'preparing'): ?>
                        <button class="btn btn-outline-success btn-action" data-action="ready" data-id="<?= $order_id ?>">Mark Ready</button>

                      <?php elseif ($status == 'ready' && $order['order_type'] == 'delivery'): ?>
                        <!-- staff handles delivery; move to out_for_delivery -->
                        <button class="btn btn-outline-info btn-action" data-action="start_delivery" data-id="<?= $order_id ?>">Out for Delivery</button>

                      <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup' && $payment_status === 'paid'): ?>
                        <!-- already paid (e.g. GCash) -> just mark picked up -->
                        <button class="btn btn-outline-success btn-action" data-action="complete" data-id="<?= $order_id ?>">Mark Picked Up</button>

                      <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup' && $payment_status !== 'paid'): ?>
                        <!-- pay-on-pickup -> open POS payment screen -->
                        <a href="pos_payment.php?order_id=<?= $order_id ?>" class="btn btn-outline-success">Take Payment</a>

                      <?php elseif ($status == 'out_for_delivery'): ?>
                        <!-- delivery is on the road; mark delivered when done -->
                        <button class="btn btn-outline-success btn-action" data-action="mark_delivered" data-id="<?= $order_id ?>">Mark Delivered</button>

                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary" disabled>View</button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">No active orders in the queue.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const staffUserId = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
  
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const button = e.currentTarget;
      const orderId = button.dataset.id;
      const action = button.dataset.action;
      
      let newStatus = '';
      if (action === 'confirm')        newStatus = 'confirmed';
      if (action === 'prepare')        newStatus = 'preparing';
      if (action === 'ready')          newStatus = 'ready';
      if (action === 'start_delivery') newStatus = 'out_for_delivery';
      if (action === 'mark_delivered') newStatus = 'delivered';
      if (action === 'complete')       newStatus = 'completed';
      if (action === 'cancel')         newStatus = 'cancelled';
      
      if (!newStatus) return;

      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

      try {
        const res = await fetch('actions/update_order_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            order_id: orderId,
            new_status: newStatus,
            handler_id: staffUserId 
          })
        });
        
        const data = await res.json();
        
        if (data.success) {
          const row = button.closest('tr');
          const statusBadge = row.querySelector('.status-badge');

          if (statusBadge) {
            statusBadge.textContent = data.new_status_label;
            statusBadge.className = `status-badge badge ${data.new_status_class}`;
          }

          row.querySelector('.actions-cell').innerHTML = `<span class="text-success fw-bold">Done</span>`;
          setTimeout(() => location.reload(), 1500);
        } else {
          throw new Error(data.message || 'Failed to update status');
        }

      } catch (err) {
        alert('Error: ' + err.message);
        button.disabled = false;
        button.innerHTML = 'Retry';
      }
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
