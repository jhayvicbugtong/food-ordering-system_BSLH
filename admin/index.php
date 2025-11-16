<?php
include __DIR__ . '/includes/header.php'; // This already includes db_connect.php

// --- Fetch Stats ---
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Orders Today
$result_orders_today = $conn->query("SELECT COUNT(order_id) as total FROM orders WHERE created_at BETWEEN '$today_start' AND '$today_end'");
$stats_orders_today = $result_orders_today->fetch_assoc()['total'] ?? 0;

// Revenue Today (from PAID orders)
$result_revenue_today = $conn->query("
    SELECT SUM(o.total_amount) as total 
    FROM orders o
    JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE opd.payment_status = 'paid' 
    AND opd.paid_at BETWEEN '$today_start' AND '$today_end'
");
$stats_revenue_today = $result_revenue_today->fetch_assoc()['total'] ?? 0;

// Pending Orders
$result_pending = $conn->query("SELECT COUNT(order_id) as total FROM orders WHERE status = 'pending'");
$stats_pending = $result_pending->fetch_assoc()['total'] ?? 0;

// Completed Today
$result_completed = $conn->query("SELECT COUNT(order_id) as total FROM orders WHERE status IN ('completed', 'delivered') AND updated_at BETWEEN '$today_start' AND '$today_end'");
$stats_completed = $result_completed->fetch_assoc()['total'] ?? 0;


// --- Fetch Recent Orders for Pipeline ---
$pipeline_orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        opd.payment_method,
        h.first_name as handler_first_name
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    LEFT JOIN users h ON o.handler_id = h.user_id
    WHERE o.status NOT IN ('completed', 'delivered', 'cancelled')
    ORDER BY o.created_at DESC
    LIMIT 5
";
$pipeline_result = $conn->query($pipeline_orders_query);


// --- Fetch Top Selling Items (Today) ---
$top_items_query = "
    SELECT 
        oi.product_name, 
        SUM(oi.quantity) as total_qty, 
        SUM(oi.total_price) as total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.created_at BETWEEN '$today_start' AND '$today_end'
    AND o.status NOT IN ('cancelled', 'pending')
    GROUP BY oi.product_id, oi.product_name
    ORDER BY total_sales DESC
    LIMIT 3
";
$top_items_result = $conn->query($top_items_query);


// --- Fetch Payment Mix (Today) ---
$payment_mix_query = "
    SELECT 
        opd.payment_method, 
        COUNT(opd.payment_id) as total_count, 
        SUM(opd.amount_paid) as total_sales
    FROM order_payment_details opd
    WHERE opd.payment_status = 'paid'
    AND opd.paid_at BETWEEN '$today_start' AND '$today_end'
    GROUP BY opd.payment_method
    ORDER BY total_sales DESC
";
$payment_mix_result = $conn->query($payment_mix_query);

?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Orders Today</h5>
          <div class="value"><?= $stats_orders_today ?></div>
          <div class="hint">All order types</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Revenue Today</h5>
          <div class="value">₱<?= number_format($stats_revenue_today, 2) ?></div>
          <div class="hint">From paid orders</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Pending Orders</h5>
          <div class="value"><?= $stats_pending ?></div>
          <div class="hint">Need action now</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Completed Today</h5>
          <div class="value"><?= $stats_completed ?></div>
          <div class="hint">Served / Delivered</div>
        </div>
      </div>
    </div>

    <span id="top"></span>
    <div class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Pipeline</h2>
          <p>Live status of all active orders</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Handled By</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($pipeline_result && $pipeline_result->num_rows > 0): ?>
              <?php while($order = $pipeline_result->fetch_assoc()): ?>
                <?php
                  $status_map = [
                    'pending' => 'badge-warning',
                    'confirmed' => 'badge-primary',
                    'preparing' => 'badge-info',
                    'ready' => 'badge-success',
                    'out_for_delivery' => 'badge-success'
                  ];
                  $status_class = $status_map[$order['status']] ?? 'badge-secondary';
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong></td>
                  <td>
                    <?= htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?><br>
                  </td>
                  <td>
                    <?= htmlspecialchars(ucfirst($order['order_type'])) ?><br>
                  </td>
                  <td><?= htmlspecialchars(ucfirst($order['payment_method'])) ?></td>
                  <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))) ?></span></td>
                  <td><?= htmlspecialchars($order['handler_first_name'] ?? 'N/A') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No active orders in the pipeline.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Business Snapshot (Today)</h2>
          <p>What's selling and how people pay</p>
        </div>
        <div class="right">
          <a class="btn btn-success" href="reports.php">
            <i class="bi bi-graph-up"></i> View Full Reports
          </a>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Top Items (Today)</th>
                  <th>Qty Sold</sup></th>
                  <th>₱ Sales</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($top_items_result && $top_items_result->num_rows > 0): ?>
                  <?php while($item = $top_items_result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($item['product_name']) ?></td>
                      <td><?= htmlspecialchars($item['total_qty']) ?></td>
                      <td>₱<?= number_format($item['total_sales'], 2) ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center text-muted">No completed sales yet today.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Payment Method (Paid)</th>
                  <th>Count</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($payment_mix_result && $payment_mix_result->num_rows > 0): ?>
                  <?php while($mix = $payment_mix_result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars(ucfirst($mix['payment_method'])) ?></td>
                      <td><?= htmlspecialchars($mix['total_count']) ?></td>
                      <td>₱<?= number_format($mix['total_sales'], 2) ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center text-muted">No paid transactions yet today.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>