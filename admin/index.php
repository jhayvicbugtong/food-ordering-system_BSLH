<?php
include __DIR__ . '/includes/header.php'; // This already includes db_connect.php

// ----------------- DATE FILTER -----------------
$selected_date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}
$day_start = $selected_date . ' 00:00:00';
$day_end   = $selected_date . ' 23:59:59';

// ----------------- STATS (Initial) -----------------
$result_orders_today = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE created_at BETWEEN '$day_start' AND '$day_end'");
$stats_orders_today = $result_orders_today->fetch_assoc()['total'] ?? 0;

$result_revenue_today = $conn->query("SELECT SUM(o.total_amount) AS total FROM orders o JOIN order_payment_details opd ON o.order_id = opd.order_id WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end'");
$stats_revenue_today = $result_revenue_today->fetch_assoc()['total'] ?? 0;

$result_pending = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE status = 'pending' AND created_at BETWEEN '$day_start' AND '$day_end'");
$stats_pending = $result_pending->fetch_assoc()['total'] ?? 0;

$result_completed = $conn->query("SELECT COUNT(order_id) AS total FROM orders WHERE status IN ('completed', 'delivered') AND updated_at BETWEEN '$day_start' AND '$day_end'");
$stats_completed = $result_completed->fetch_assoc()['total'] ?? 0;

// ----------------- CHART DATA (Initial) -----------------
$hours_labels = [];
for ($h = 0; $h < 24; $h++) $hours_labels[] = sprintf('%02d:00', $h);

// Orders per hour
$orders_per_hour = array_fill(0, 24, 0);
if ($res = $conn->query("SELECT HOUR(created_at) AS hr, COUNT(*) AS total FROM orders WHERE created_at BETWEEN '$day_start' AND '$day_end' GROUP BY hr")) {
    while ($row = $res->fetch_assoc()) $orders_per_hour[(int)$row['hr']] = (int)$row['total'];
}

// Revenue per hour
$revenue_per_hour = array_fill(0, 24, 0.0);
if ($res = $conn->query("SELECT HOUR(opd.paid_at) AS hr, SUM(opd.amount_paid) AS total FROM order_payment_details opd WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end' GROUP BY hr")) {
    while ($row = $res->fetch_assoc()) $revenue_per_hour[(int)$row['hr']] = (float)$row['total'];
}

// ----------------- PIPELINE (Initial) -----------------
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

$count_result = $conn->query("SELECT COUNT(*) AS total FROM orders o WHERE o.created_at BETWEEN '$day_start' AND '$day_end'");
$total_rows = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, (int)ceil($total_rows / $per_page));
if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $per_page; }

$pipeline_result = $conn->query("
    SELECT o.order_id, o.order_number, o.order_type, o.status, ocd.customer_first_name, ocd.customer_last_name, opd.payment_method
    FROM orders o LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.created_at BETWEEN '$day_start' AND '$day_end' ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset
");

// ----------------- TOP ITEMS & PAYMENT & STATUS (Initial) -----------------
$top_items_result = $conn->query("SELECT oi.product_name, SUM(oi.quantity) AS total_qty, SUM(oi.total_price) AS total_sales FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.created_at BETWEEN '$day_start' AND '$day_end' AND o.status NOT IN ('cancelled', 'pending') GROUP BY oi.product_id, oi.product_name ORDER BY total_sales DESC LIMIT 3");
$top_items = []; $top_item_labels = []; $top_item_qty = [];
while ($row = $top_items_result->fetch_assoc()) { $top_items[] = $row; $top_item_labels[] = $row['product_name']; $top_item_qty[] = (int)$row['total_qty']; }

$payment_mix_result = $conn->query("SELECT opd.payment_method, COUNT(opd.payment_id) AS total_count, SUM(opd.amount_paid) AS total_sales FROM order_payment_details opd WHERE opd.payment_status = 'paid' AND opd.paid_at BETWEEN '$day_start' AND '$day_end' GROUP BY opd.payment_method ORDER BY total_sales DESC");
$payment_mix = []; $payment_labels = []; $payment_totals = [];
while ($row = $payment_mix_result->fetch_assoc()) { $payment_mix[] = $row; $payment_labels[] = ucfirst($row['payment_method']); $payment_totals[] = (float)$row['total_sales']; }

$status_counts_result = $conn->query("SELECT status, COUNT(*) AS total FROM orders WHERE created_at BETWEEN '$day_start' AND '$day_end' GROUP BY status ORDER BY status");
$status_labels = []; $status_totals = [];
while ($row = $status_counts_result->fetch_assoc()) { $status_labels[] = ucfirst(str_replace('_', ' ', $row['status'])); $status_totals[] = (int)$row['total']; }
?>

<style>
  /* Overall page background to match modern theme */
  body {
    background-color: #f3f4f6;
  }

  .main-content {
    min-height: 100vh;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
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
    margin-bottom: 10px;
  }

  .page-title {
    font-weight: 600;
    font-size: 1.3rem;
  }

  .section-title {
    font-size: 1.05rem;
    font-weight: 600;
  }

  .text-muted small {
    font-size: 0.8rem;
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

  /* Date filter input */
  .modern-input {
    border-radius: 999px;
    border-color: #e5e7eb;
    font-size: 0.9rem;
  }

  .modern-input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.15);
  }

  /* Tables */
  .modern-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
  }

  .modern-table tbody td {
    font-size: 0.9rem;
    vertical-align: middle;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Chart container */
  .chart-container {
    position: relative;
    width: 100%;
    height: 320px;
  }
  
  /* Status badges (Same as Manage Orders) */
  .status-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .status-pending          { background:#fef3c7; color:#92400e; }
  .status-confirmed        { background:#dbeafe; color:#1d4ed8; }
  .status-preparing        { background:#e0f2fe; color:#0369a1; }
  .status-ready            { background:#dcfce7; color:#15803d; }
  .status-out-for-delivery { background:#e0f2fe; color:#0369a1; }
  .status-delivered,
  .status-completed        { background:#e5e7eb; color:#111827; }
  .status-cancelled        { background:#fee2e2; color:#b91c1c; }

  @media (max-width: 576px) {
    .chart-container {
      height: 260px;
    }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <div class="content-card">
      <div class="content-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="page-title mb-1">Admin Dashboard</h2>
          <p class="text-muted mb-0 small">Daily performance overview for your online orders.</p>
        </div>
        <form method="get" class="d-flex align-items-center gap-2" id="dateFilterForm">
          <label for="date" class="col-form-label small text-muted mb-0">Date</label>
          <input type="date" id="date" name="date" class="form-control modern-input" value="<?= htmlspecialchars($selected_date) ?>">
        </form>
      </div>
      <p class="text-muted mb-3 small">Showing data for <strong><?= htmlspecialchars($selected_date) ?></strong> (00:00–23:59) - <span id="live-indicator" class="text-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Live Updates</span></p>

      <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Orders (Day)</h5>
            <div class="value" id="stat-orders"><?= $stats_orders_today ?></div>
            <div class="hint">All order types</div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Revenue (Day)</h5>
            <div class="value" id="stat-revenue">₱<?= number_format($stats_revenue_today, 2) ?></div>
            <div class="hint">From paid orders</div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Pending (Day)</h5>
            <div class="value" id="stat-pending"><?= $stats_pending ?></div>
            <div class="hint">Still pending</div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Completed (Day)</h5>
            <div class="value" id="stat-completed"><?= $stats_completed ?></div>
            <div class="hint">Completed / Delivered</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-12 col-lg-6">
        <div class="content-card h-100">
          <div class="content-card-header"><h2 class="section-title mb-1">Orders & Revenue by Hour</h2></div>
          <div class="chart-container"><canvas id="ordersRevenueLineChart"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="content-card h-100">
          <div class="content-card-header"><h2 class="section-title mb-1">Orders by Hour</h2></div>
          <div class="chart-container"><canvas id="ordersLineChart"></canvas></div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-12 col-lg-6 col-xl-4">
        <div class="content-card h-100">
          <div class="content-card-header"><h2 class="section-title mb-1">Payment Mix (Paid)</h2></div>
          <div class="chart-container"><canvas id="paymentPieChart"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-lg-6 col-xl-8">
        <div class="content-card h-100">
          <div class="content-card-header"><h2 class="section-title mb-1">Top Items (Qty Sold)</h2></div>
          <div class="chart-container"><canvas id="topItemsBarChart"></canvas></div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-12 col-lg-6">
        <div class="content-card h-100">
          <div class="content-card-header"><h2 class="section-title mb-1">Orders by Status</h2></div>
          <div class="chart-container"><canvas id="statusBarChart"></canvas></div>
        </div>
      </div>
    </div>

    <span id="top"></span>
    <div class="content-card mb-4">
      <div class="content-card-header">
        <h2 class="section-title mb-1">Order Pipeline</h2>
        <p class="text-muted small mb-0">Orders for the selected date.</p>
      </div>
      <div class="table-responsive">
        <table class="table table-hover modern-table">
          <thead><tr><th>Order #</th><th>Customer</th><th>Type</th><th>Payment</th><th>Status</th></tr></thead>
          <tbody id="pipeline-body">
            <?php if ($pipeline_result && $pipeline_result->num_rows > 0): ?>
              <?php while($order = $pipeline_result->fetch_assoc()): ?>
                <?php
                  $status = $order['status'] ?? '';
                  $status_map = ['pending'=>'status-pending', 'confirmed'=>'status-confirmed', 'preparing'=>'status-preparing', 'ready'=>'status-ready', 'out_for_delivery'=>'status-out-for-delivery', 'delivered'=>'status-delivered', 'completed'=>'status-completed', 'cancelled'=>'status-cancelled'];
                  $status_class = $status_map[$status] ?? 'bg-secondary text-white';
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong></td>
                  <td><?= htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars(ucfirst($order['order_type'])) ?></td>
                  <td><?= htmlspecialchars(ucfirst($order['payment_method'] ?? '')) ?></td>
                  <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted">No orders for this date.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Pipeline pagination">
          <ul class="pagination justify-content-end mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?date=<?= htmlspecialchars($selected_date) ?>&page=<?= max(1, $page - 1) ?>">&laquo;</a>
            </li>
            <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $total_pages ?></span></li>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link" href="?date=<?= htmlspecialchars($selected_date) ?>&page=<?= min($total_pages, $page + 1) ?>">&raquo;</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>

    <div class="content-card">
      <div class="content-card-header">
        <h2 class="section-title mb-1">Business Snapshot (Day)</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-hover modern-table">
              <thead><tr><th>Top Items</th><th>Qty Sold</th><th>₱ Sales</th></tr></thead>
              <tbody id="top-items-body">
                <?php if (!empty($top_items)): ?>
                  <?php foreach ($top_items as $item): ?>
                    <tr><td><?= htmlspecialchars($item['product_name']) ?></td><td><?= htmlspecialchars($item['total_qty']) ?></td><td>₱<?= number_format($item['total_sales'], 2) ?></td></tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center text-muted">No completed sales for this date.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-hover modern-table">
              <thead><tr><th>Payment Method (Paid)</th><th>Count</th><th>Total</th></tr></thead>
              <tbody id="payment-mix-body">
                <?php if (!empty($payment_mix)): ?>
                  <?php foreach ($payment_mix as $mix): ?>
                    <tr><td><?= htmlspecialchars(ucfirst($mix['payment_method'])) ?></td><td><?= htmlspecialchars($mix['total_count']) ?></td><td>₱<?= number_format($mix['total_sales'], 2) ?></td></tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center text-muted">No paid transactions for this date.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  // Date Filter Auto-submit
  document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('date');
    if (dateInput) { dateInput.addEventListener('change', function () { this.form.submit(); }); }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // --- Initialize Variables with PHP Data ---
  let hoursLabels = <?= json_encode($hours_labels) ?>;
  let ordersPerHour = <?= json_encode($orders_per_hour, JSON_NUMERIC_CHECK) ?>;
  let revenuePerHour = <?= json_encode($revenue_per_hour, JSON_NUMERIC_CHECK) ?>;
  let paymentLabels = <?= json_encode($payment_labels) ?>;
  let paymentTotals = <?= json_encode($payment_totals, JSON_NUMERIC_CHECK) ?>;
  let topItemLabels = <?= json_encode($top_item_labels) ?>;
  let topItemQty = <?= json_encode($top_item_qty, JSON_NUMERIC_CHECK) ?>;
  let statusLabels = <?= json_encode($status_labels) ?>;
  let statusTotals = <?= json_encode($status_totals, JSON_NUMERIC_CHECK) ?>;

  const colorBlue = '#4e73df', colorGreen = '#1cc88a', colorCyan = '#36b9cc', colorYellow = '#f6c23e', colorRed = '#e74a3b', colorPurple = '#6f42c1', colorGray = '#858796';
  const statusColorMap = { 'Pending': colorYellow, 'Confirmed': colorBlue, 'Preparing': colorCyan, 'Ready': colorGreen, 'Out for delivery': colorGreen, 'Completed': colorGreen, 'Delivered': colorGreen, 'Cancelled': colorRed };
  let statusBarColors = statusLabels.map(label => statusColorMap[label] || colorGray);

  // --- Initialize Charts ---
  const chartOrdersRevenue = new Chart(document.getElementById('ordersRevenueLineChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: hoursLabels,
      datasets: [
        { label: 'Orders', data: ordersPerHour, tension: 0.3, borderColor: colorBlue, backgroundColor: 'rgba(78, 115, 223, 0.15)', borderWidth: 2, yAxisID: 'y' },
        { label: 'Revenue (₱)', data: revenuePerHour, tension: 0.3, borderColor: colorGreen, backgroundColor: 'rgba(28, 200, 138, 0.15)', borderWidth: 2, yAxisID: 'y1' }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, stacked: false, scales: { y: { beginAtZero: true }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } }
  });

  const chartOrders = new Chart(document.getElementById('ordersLineChart').getContext('2d'), {
    type: 'line',
    data: { labels: hoursLabels, datasets: [{ label: 'Orders', data: ordersPerHour, tension: 0.3, borderColor: colorCyan, backgroundColor: 'rgba(54, 185, 204, 0.15)', borderWidth: 2, fill: true }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  const chartPayment = new Chart(document.getElementById('paymentPieChart').getContext('2d'), {
    type: 'pie',
    data: { labels: paymentLabels, datasets: [{ data: paymentTotals, backgroundColor: [colorBlue, colorGreen, colorCyan, colorYellow, colorPurple, colorRed, colorGray], borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  const chartTopItems = new Chart(document.getElementById('topItemsBarChart').getContext('2d'), {
    type: 'bar',
    data: { labels: topItemLabels, datasets: [{ label: 'Qty Sold', data: topItemQty, backgroundColor: colorBlue, borderColor: colorBlue, borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  const chartStatus = new Chart(document.getElementById('statusBarChart').getContext('2d'), {
    type: 'bar',
    data: { labels: statusLabels, datasets: [{ label: 'Orders', data: statusTotals, backgroundColor: statusBarColors, borderColor: statusBarColors, borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  // --- Real-time Polling ---
  function fetchDashboardUpdates() {
      const params = window.location.search;
      fetch('actions/fetch_dashboard_updates.php' + params)
          .then(res => res.json())
          .then(data => {
              if (data.stats) {
                  document.getElementById('stat-orders').textContent = data.stats.orders;
                  document.getElementById('stat-revenue').textContent = '₱' + Number(data.stats.revenue).toLocaleString('en-US', {minimumFractionDigits: 2});
                  document.getElementById('stat-pending').textContent = data.stats.pending;
                  document.getElementById('stat-completed').textContent = data.stats.completed;
              }

              if (data.html) {
                  document.getElementById('pipeline-body').innerHTML = data.html.pipeline;
                  document.getElementById('top-items-body').innerHTML = data.html.top_items;
                  document.getElementById('payment-mix-body').innerHTML = data.html.pay_mix;
              }

              if (data.charts) {
                  // Update Line Charts
                  chartOrdersRevenue.data.datasets[0].data = data.charts.orders_per_hour;
                  chartOrdersRevenue.data.datasets[1].data = data.charts.revenue_per_hour;
                  chartOrdersRevenue.update();

                  chartOrders.data.datasets[0].data = data.charts.orders_per_hour;
                  chartOrders.update();

                  // Update Pie Chart
                  chartPayment.data.labels = data.charts.pay_labels;
                  chartPayment.data.datasets[0].data = data.charts.pay_totals;
                  chartPayment.update();

                  // Update Top Items Bar
                  chartTopItems.data.labels = data.charts.top_labels;
                  chartTopItems.data.datasets[0].data = data.charts.top_qty;
                  chartTopItems.update();

                  // Update Status Bar
                  chartStatus.data.labels = data.charts.status_labels;
                  chartStatus.data.datasets[0].data = data.charts.status_totals;
                  // Recalculate colors
                  const newColors = data.charts.status_labels.map(label => statusColorMap[label] || colorGray);
                  chartStatus.data.datasets[0].backgroundColor = newColors;
                  chartStatus.data.datasets[0].borderColor = newColors;
                  chartStatus.update();
              }
          })
          .catch(err => console.error("Polling error:", err));
  }

  // Poll every 1 seconds
  setInterval(fetchDashboardUpdates, 1000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>