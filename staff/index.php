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

<!-- STYLE FIXES FOR READABILITY -->
<style>
  .dashboard-table th,
  .dashboard-table td {
    font-size: 14px;
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    vertical-align: top;
  }

  .dashboard-table td small {
    font-size: 12px;
  }

  .dashboard-table .status-badge,
  .dashboard-table .payment-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">Staff Dashboard</h2>
    <p class="mb-4 text-muted" style="font-size:14px;">
      Logged in as <strong><?= htmlspecialchars(get_user_name() ?? 'Staff') ?></strong>. Below are tasks for your shift.
    </p>

    <!-- STAT CARDS (3 cards, no POS) -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-md-4">
        <div class="stat-card">
          <h5>Orders To Prepare</h5>
          <div class="value"><?= $orders_to_prepare ?></div>
          <div class="hint">Kitchen queue</div>
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

    <!-- ACTIVE ORDERS TABLE -->
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Active Orders</h2>
          <p>Online and walk-in (POS)</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover dashboard-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Source</th>
              <th>Items</th>
              <th>Payment</th>
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

                  // Map order_type to "source" label
                  if ($order['order_type'] === 'delivery') {
                    $source_label = 'Online Delivery';
                  } elseif ($order['order_type'] === 'pickup') {
                    $source_label = 'Pickup';
                  } else {
                    $source_label = 'Walk-in POS';
                  }

                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'badge-warning',
                    'confirmed'        => 'badge-success',
                    'preparing'        => 'badge-success',
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
                ?>
                <tr>
                  <td>#<?= $order_no ?></td>
                  <td>
                    <?= htmlspecialchars($source_label) ?><br>
                    <small class="text-muted"><?= htmlspecialchars($full_name) ?></small>
                  </td>
                  <td>
                    <ul class="mb-0" style="padding-left: 15px; font-size: 0.9em;">
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
                    <span class="badge <?= $payment_badge_class ?> payment-badge">
                      <?= htmlspecialchars($payment_label) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?= $status_class ?> status-badge">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No active orders.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- PICKUP COUNTER QUEUE TABLE -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Pickup Counter Queue</h2>
          <p>Customers waiting in store</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover dashboard-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Name</th>
              <th>Items</th>
              <th>Time Ready</th>
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
                ?>
                <tr>
                  <td>#<?= $order_no ?></td>
                  <td><?= htmlspecialchars($full_name) ?></td>
                  <td>
                    <ul class="mb-0" style="padding-left: 15px; font-size: 0.9em;">
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
                  <td><?= htmlspecialchars($time_ready) ?></td>
                  <td>
                    <span class="badge <?= $status_class ?> status-badge">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No pickup customers in the queue.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<!-- crude "real-time" refresh: reload page every 10 seconds -->
<script>
  setInterval(function() {
    location.reload();
  }, 10000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
