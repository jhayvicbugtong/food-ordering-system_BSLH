<?php
include __DIR__ . '/includes/header.php'; // brings in $conn, auth, helpers

// ----------------------------
// DASHBOARD STATS (NO POS CARD)
// ----------------------------
$stats_sql = "
    SELECT
        SUM(CASE WHEN status IN ('pending','confirmed','preparing') THEN 1 ELSE 0 END) AS to_prepare,
        SUM(CASE WHEN status = 'ready' AND order_type = 'pickup' THEN 1 ELSE 0 END) AS ready_pickup,
        SUM(CASE WHEN status IN ('ready','out_for_delivery') AND order_type = 'delivery' THEN 1 ELSE 0 END) AS out_for_delivery
    FROM orders
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result ? $stats_result->fetch_assoc() : null;

$orders_to_prepare = (int)($stats['to_prepare'] ?? 0);
$for_pickup_ready  = (int)($stats['ready_pickup'] ?? 0);
$out_for_delivery  = (int)($stats['out_for_delivery'] ?? 0);

// ----------------------------
// ACTIVE ORDERS (TOP TABLE)
// all non-final orders + payment info
// ----------------------------
$active_sql = "
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
    LEFT JOIN order_customer_details ocd 
        ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd 
        ON opd.order_id = o.order_id
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
    LIMIT 50
";
$active_orders = $conn->query($active_sql);

// ----------------------------
// PICKUP COUNTER QUEUE (BOTTOM TABLE)
// ready pickup orders
// ----------------------------
$pickup_sql = "
    SELECT 
        o.order_id,
        o.order_number,
        o.status,
        o.ready_at,
        o.total_amount,
        ocd.customer_first_name,
        ocd.customer_last_name
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    WHERE o.order_type = 'pickup'
      AND o.status = 'ready'
    ORDER BY o.ready_at ASC
";
$pickup_orders = $conn->query($pickup_sql);
?>

<style>
  /* Page background + layout to match modern theme */
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
  }

  /* Modern cards */
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

  /* Stat cards */
  .stat-card {
    padding: 14px 16px;
    border-radius: 16px;
    background: linear-gradient(135deg, #eef2ff, #f9fafb);
    border: 1px solid rgba(129, 140, 248, 0.25);
    box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
    transition: transform 0.12s ease-out, box-shadow 0.12s ease-out;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  }

  .stat-card h5 {
    margin: 0;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
  }

  .stat-card .value {
    font-size: 1.3rem;
    font-weight: 600;
    margin-top: 4px;
  }

  .stat-card .hint {
    font-size: 0.8rem;
    color: #9ca3af;
    margin-top: 2px;
  }

  /* Tables */
  .dashboard-table {
    margin-bottom: 0;
  }

  .dashboard-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
  }

  .dashboard-table th,
  .dashboard-table td {
    font-size: 0.9rem;
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    vertical-align: top;
  }

  .dashboard-table td small {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Pills / badges */
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
  .source-pill.pickup {
    background: #ecfdf3;
    color: #166534;
  }

  .source-pill.delivery {
    background: #eff6ff;
    color: #1d4ed8;
  }

  .source-pill.pos {
    background: #fef3c7;
    color: #92400e;
  }

  /* Payment pills (override Bootstrap badge look into softer chips) */
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

  /* Tiny helper text */
  .meta-text {
    font-size: 0.8rem;
    color: #9ca3af;
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

    <div class="content-card mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <h2 class="page-title mb-1">Staff Dashboard</h2>
          <p class="page-subtitle text-muted mb-1">
            Logged in as <strong><?= htmlspecialchars(get_user_name() ?? 'Staff') ?></strong>.
          </p>
          <p class="meta-text mb-0">
            Focus on what's currently in the queue. Page auto-refreshes every 10 seconds.
          </p>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-md-4">
        <div class="stat-card">
          <h5>Orders To Prepare</h5>
          <div class="value"><?= $orders_to_prepare ?></div>
          <div class="hint">Pending / Confirmed / Preparing</div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="stat-card">
          <h5>For Pickup / Ready</h5>
          <div class="value"><?= $for_pickup_ready ?></div>
          <div class="hint">Waiting at counter</div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="stat-card">
          <h5>Out for Delivery</h5>
          <div class="value"><?= $out_for_delivery ?></div>
          <div class="hint">On the road</div>
        </div>
      </div>
    </div>

    <section class="content-card mb-4">
      <div class="content-card-header">
        <div>
          <h2>Active Orders</h2>
          <p>Online orders, pickups, and walk-in POS that are not yet completed.</p>
        </div>
        <div class="text-end meta-text">
          <span class="d-block">Auto refresh: 10s</span>
          <span class="d-block">Sorted by status &amp; time placed</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover dashboard-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Source / Customer</th>
              <th>Items</th>
              <th>Total</th> <th>Payment</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="active-orders-body">
            <?php if ($active_orders && $active_orders->num_rows > 0): ?>
              <?php while ($order = $active_orders->fetch_assoc()): ?>
                <?php
                  $order_id  = (int)$order['order_id'];
                  $order_no  = htmlspecialchars($order['order_number'] ?? $order_id);
                  $full_name = trim(
                    ($order['customer_first_name'] ?? '') . ' ' .
                    ($order['customer_last_name'] ?? '')
                  );
                  if ($full_name === '') {
                    $full_name = 'Walk-in POS';
                  }

                  // Map order_type to "source" label + pill type
                  if ($order['order_type'] === 'delivery') {
                    $source_label = 'Online Delivery';
                    $source_class = 'delivery';
                  } elseif ($order['order_type'] === 'pickup') {
                    $source_label = 'Pickup';
                    $source_class = 'pickup';
                  } else {
                    $source_label = 'Walk-in POS';
                    $source_class = 'pos';
                  }

                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'badge-warning',
                    'confirmed'        => 'badge-info',
                    'preparing'        => 'badge-info',
                    'ready'            => 'badge-success',
                    'out_for_delivery' => 'badge-success',
                  ];
                  $status_class = $status_map[$status] ?? 'badge-secondary';

                  // Payment label & badge
                  $payment_method = $order['payment_method'] ?? null;
                  $payment_status = $order['payment_status'] ?? null;

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

                  $created_time = $order['created_at']
                    ? date('g:i A', strtotime($order['created_at']))
                    : '';
                    
                  // Total
                  $total = (float)($order['total_amount'] ?? 0);
                ?>
                <tr>
                  <td>
                    <strong>#<?= $order_no ?></strong><br>
                    <?php if ($created_time): ?>
                      <span class="meta-text">Placed: <?= htmlspecialchars($created_time) ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="source-pill <?= $source_class ?>">
                      <?= htmlspecialchars($source_label) ?>
                    </span><br>
                    <small class="text-muted"><?= htmlspecialchars($full_name) ?></small>
                  </td>
                  <td>
                    <ul class="mb-0" style="padding-left: 15px; font-size: 0.85rem;">
                      <?php
                        $items_stmt = $conn->prepare("
                          SELECT product_name, quantity 
                          FROM order_items 
                          WHERE order_id = ?
                        ");
                        $items_stmt->bind_param('i', $order_id);
                        $items_stmt->execute();
                        $items_res = $items_stmt->get_result();
                        while ($item = $items_res->fetch_assoc()):
                      ?>
                        <li>
                          <?= htmlspecialchars($item['product_name']) ?> 
                          x <strong><?= (int)$item['quantity'] ?></strong>
                        </li>
                      <?php endwhile; $items_stmt->close(); ?>
                    </ul>
                  </td>
                  <td>
                    <span style="font-weight:600; white-space:nowrap;">
                        ₱<?= number_format($total, 2) ?>
                    </span>
                  </td>
                  <td>
                    <span class="payment-badge badge <?= $payment_badge_class ?>">
                      <?= htmlspecialchars($payment_label) ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No active orders.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div>
          <h2>Pickup Counter Queue</h2>
          <p>Orders marked as <strong>Ready</strong> for pickup in-store.</p>
        </div>
        <div class="text-end meta-text">
          <span class="d-block">Sorted by time marked ready</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover dashboard-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Total</th> <th>Time Ready</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="pickup-queue-body">
            <?php if ($pickup_orders && $pickup_orders->num_rows > 0): ?>
              <?php while ($order = $pickup_orders->fetch_assoc()): ?>
                <?php
                  $order_id  = (int)$order['order_id'];
                  $order_no  = htmlspecialchars($order['order_number'] ?? $order_id);
                  $full_name = trim(
                    ($order['customer_first_name'] ?? '') . ' ' .
                    ($order['customer_last_name'] ?? '')
                  );
                  if ($full_name === '') {
                    $full_name = 'Walk-in POS';
                  }

                  $time_ready = $order['ready_at']
                    ? date('g:i A', strtotime($order['ready_at']))
                    : '-';

                  $status       = $order['status'];
                  $status_class = ($status === 'ready') ? 'badge-success' : 'badge-secondary';
                  $total        = (float)($order['total_amount'] ?? 0);
                ?>
                <tr>
                  <td><strong>#<?= $order_no ?></strong></td>
                  <td><?= htmlspecialchars($full_name) ?></td>
                  <td>
                    <ul class="mb-0" style="padding-left: 15px; font-size: 0.85rem;">
                      <?php
                        $items_stmt = $conn->prepare("
                          SELECT product_name, quantity 
                          FROM order_items 
                          WHERE order_id = ?
                        ");
                        $items_stmt->bind_param('i', $order_id);
                        $items_stmt->execute();
                        $items_res = $items_stmt->get_result();
                        while ($item = $items_res->fetch_assoc()):
                      ?>
                        <li>
                          <?= htmlspecialchars($item['product_name']) ?> 
                          x <strong><?= (int)$item['quantity'] ?></strong>
                        </li>
                      <?php endwhile; $items_stmt->close(); ?>
                    </ul>
                  </td>
                  <td>
                    <span style="font-weight:600; white-space:nowrap;">
                        ₱<?= number_format($total, 2) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($time_ready) ?></td>
                  <td>
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No pickup customers in the queue.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<script>
  setInterval(function() {
    location.reload();
  }, 10000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>