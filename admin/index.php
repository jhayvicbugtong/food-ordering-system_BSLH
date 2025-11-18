<?php
include __DIR__ . '/includes/header.php'; // This already includes db_connect.php

// ----------------- DATE FILTER -----------------
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Basic validation – if format is wrong, fall back to today
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

$day_start = $selected_date . ' 00:00:00';
$day_end   = $selected_date . ' 23:59:59';

// ----------------- STATS -----------------

// Orders for the selected day (by created_at)
$result_orders_today = $conn->query("
    SELECT COUNT(order_id) AS total 
    FROM orders 
    WHERE created_at BETWEEN '$day_start' AND '$day_end'
");
$stats_orders_today = $result_orders_today->fetch_assoc()['total'] ?? 0;

// Revenue for the selected day (paid orders)
$result_revenue_today = $conn->query("
    SELECT SUM(o.total_amount) AS total 
    FROM orders o
    JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE opd.payment_status = 'paid' 
      AND opd.paid_at BETWEEN '$day_start' AND '$day_end'
");
$stats_revenue_today = $result_revenue_today->fetch_assoc()['total'] ?? 0;

// Pending orders for the selected day (still pending)
$result_pending = $conn->query("
    SELECT COUNT(order_id) AS total 
    FROM orders 
    WHERE status = 'pending'
      AND created_at BETWEEN '$day_start' AND '$day_end'
");
$stats_pending = $result_pending->fetch_assoc()['total'] ?? 0;

// Completed / delivered on the selected day (by updated_at)
$result_completed = $conn->query("
    SELECT COUNT(order_id) AS total 
    FROM orders 
    WHERE status IN ('completed', 'delivered') 
      AND updated_at BETWEEN '$day_start' AND '$day_end'
");
$stats_completed = $result_completed->fetch_assoc()['total'] ?? 0;

// ----------------- PIPELINE: PAGINATION -----------------
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

// Count total orders for that day (for pagination)
$count_query = "
    SELECT COUNT(*) AS total
    FROM orders o
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end'
";
$count_result = $conn->query($count_query);
$total_rows = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, (int)ceil($total_rows / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

// ----------------- PIPELINE: DATA -----------------
$pipeline_orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        opd.payment_method
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end'
    ORDER BY o.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$pipeline_result = $conn->query($pipeline_orders_query);

// ----------------- TOP ITEMS (Today = selected date) -----------------
$top_items_query = "
    SELECT 
        oi.product_name, 
        SUM(oi.quantity)    AS total_qty, 
        SUM(oi.total_price) AS total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end'
      AND o.status NOT IN ('cancelled', 'pending')
    GROUP BY oi.product_id, oi.product_name
    ORDER BY total_sales DESC
    LIMIT 3
";
$top_items_result = $conn->query($top_items_query);

// ----------------- PAYMENT MIX (Today = selected date) -----------------
$payment_mix_query = "
    SELECT 
        opd.payment_method, 
        COUNT(opd.payment_id) AS total_count, 
        SUM(opd.amount_paid)  AS total_sales
    FROM order_payment_details opd
    WHERE opd.payment_status = 'paid'
      AND opd.paid_at BETWEEN '$day_start' AND '$day_end'
    GROUP BY opd.payment_method
    ORDER BY total_sales DESC
";
$payment_mix_result = $conn->query($payment_mix_query);

?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Admin Dashboard</h2>

      <!-- Date filter -->
      <form method="get" class="row g-2 align-items-center">
        <div class="col-auto">
          <label for="date" class="col-form-label">Date:</label>
        </div>
        <div class="col-auto">
          <input 
            type="date" 
            id="date" 
            name="date" 
            class="form-control"
            value="<?= htmlspecialchars($selected_date) ?>"
          >
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Apply</button>
        </div>
      </form>
    </div>

    <p class="text-muted mb-4">
      Showing data for <strong><?= htmlspecialchars($selected_date) ?></strong>
      (00:00–23:59)
    </p>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Orders (Day)</h5>
          <div class="value"><?= $stats_orders_today ?></div>
          <div class="hint">All order types</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Revenue (Day)</h5>
          <div class="value">₱<?= number_format($stats_revenue_today, 2) ?></div>
          <div class="hint">From paid orders</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Pending (Day)</h5>
          <div class="value"><?= $stats_pending ?></div>
          <div class="hint">Still pending</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Completed (Day)</h5>
          <div class="value"><?= $stats_completed ?></div>
          <div class="hint">Completed / Delivered</div>
        </div>
      </div>
    </div>

    <!-- ORDER PIPELINE -->
    <span id="top"></span>
    <div class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Pipeline</h2>
          <p>Orders for the selected date</p>
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
            </tr>
          </thead>
          <tbody>
            <?php if ($pipeline_result && $pipeline_result->num_rows > 0): ?>
              <?php while($order = $pipeline_result->fetch_assoc()): ?>
                <?php
                  // High-contrast badge colors
                  $status_map = [
                    'pending'          => 'bg-warning text-dark',
                    'confirmed'        => 'bg-primary',
                    'preparing'        => 'bg-info text-dark',
                    'ready'            => 'bg-success',
                    'out_for_delivery' => 'bg-success',
                    'completed'        => 'bg-success',
                    'delivered'        => 'bg-success',
                    'cancelled'        => 'bg-danger'
                  ];
                  $status_key   = $order['status'] ?? '';
                  $status_class = $status_map[$status_key] ?? 'bg-secondary';
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong></td>
                  <td>
                    <?= htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''))) ?>
                  </td>
                  <td><?= htmlspecialchars(ucfirst($order['order_type'])) ?></td>
                  <td><?= htmlspecialchars(ucfirst($order['payment_method'] ?? '')) ?></td>
                  <td>
                    <span class="badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status_key))) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">
                  No orders for this date.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Pipeline pagination">
          <ul class="pagination justify-content-end mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" 
                 href="?date=<?= htmlspecialchars($selected_date) ?>&page=<?= max(1, $page - 1) ?>" 
                 aria-label="Previous">
                &laquo;
              </a>
            </li>
            <li class="page-item disabled">
              <span class="page-link">
                Page <?= $page ?> of <?= $total_pages ?>
              </span>
            </li>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link" 
                 href="?date=<?= htmlspecialchars($selected_date) ?>&page=<?= min($total_pages, $page + 1) ?>" 
                 aria-label="Next">
                &raquo;
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>

    <!-- BUSINESS SNAPSHOT -->
    <div class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Business Snapshot (Day)</h2>
          <p>What's selling and how people pay (for the selected date)</p>
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
                  <th>Top Items</th>
                  <th>Qty Sold</th>
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
                  <tr>
                    <td colspan="3" class="text-center text-muted">
                      No completed sales for this date.
                    </td>
                  </tr>
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
                  <tr>
                    <td colspan="3" class="text-center text-muted">
                      No paid transactions for this date.
                    </td>
                  </tr>
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
