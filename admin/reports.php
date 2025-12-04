<?php
// admin/reports.php
include __DIR__ . '/includes/header.php'; // Includes db_connect.php
include __DIR__ . '/includes/sidebar.php';

// ============ DB CONFIG ============
$DB = [
  'TABLE_ORDERS'   => 'orders',
  'TABLE_CUST'     => 'order_customer_details',
  'TABLE_PAY'      => 'order_payment_details',
  'COL_ORDER_ID'   => 'order_id', 
  'COL_ORDER_NUM'  => 'order_number',
  'COL_USER_ID'    => 'user_id',
  'COL_CUST_NAME'  => 'customer_first_name', 
  'COL_CUST_LNAME' => 'customer_last_name',
  'COL_TYPE'       => 'order_type',
  'COL_STATUS'     => 'status', 
  'COL_PAY_STATUS' => 'payment_status',
  'COL_PAY_METHOD' => 'payment_method',
  'COL_PAY_AMOUNT' => 'amount_paid',
  'COL_PAY_DATE'   => 'paid_at',
  'COL_TOTAL'      => 'total_amount',
  'COL_CREATED'    => 'created_at',
];

if (!isset($conn) || !($conn instanceof mysqli)) {
  die('<div class="alert alert-danger m-4">DB connection not found.</div>');
}

// --- Helpers ---
function peso($n) { return '₱' . number_format((float)$n, 2); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Inputs ---
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : date('Y-m-d');
$revMode = $_GET['rev_mode'] ?? 'completed'; // 'completed' | 'paid'

// Pagination
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$from = $start . ' 00:00:00';
$to   = $end   . ' 23:59:59';

// Aliases
$T_O = $DB['TABLE_ORDERS'];
$T_C = $DB['TABLE_CUST'];
$T_P = $DB['TABLE_PAY'];
$O_ID = $DB['COL_ORDER_ID'];
$O_NUM = $DB['COL_ORDER_NUM'];
$O_CREATED = $DB['COL_CREATED'];
$O_STATUS = $DB['COL_STATUS'];
$O_TOTAL = $DB['COL_TOTAL'];
$O_TYPE = $DB['COL_TYPE'];
$C_FNAME = $DB['COL_CUST_NAME'];
$C_LNAME = $DB['COL_CUST_LNAME'];
$P_STATUS = $DB['COL_PAY_STATUS'];
$P_METHOD = $DB['COL_PAY_METHOD'];
$P_DATE = $DB['COL_PAY_DATE'];
$P_AMOUNT = $DB['COL_PAY_AMOUNT'];

// --- Summary: total sales + total orders ---
$sum = ['revenue'=>0,'orders'=>0];

if ($revMode === 'paid') {
    $sql = "SELECT COALESCE(SUM($T_P.$P_AMOUNT),0) FROM $T_P WHERE $T_P.$P_DATE BETWEEN ? AND ? AND $T_P.$P_STATUS = 'paid'";
} else {
    $sql = "SELECT COALESCE(SUM($T_O.$O_TOTAL),0) FROM $T_O WHERE $T_O.$O_CREATED BETWEEN ? AND ? AND $T_O.$O_STATUS IN ('delivered','completed')";
}

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('ss', $from, $to);
  $stmt->execute(); $stmt->bind_result($sum['revenue']); $stmt->fetch(); $stmt->close();
}

if ($stmt = $conn->prepare("SELECT COUNT(*) FROM $T_O WHERE $O_CREATED BETWEEN ? AND ?")) {
  $stmt->bind_param('ss', $from, $to);
  $stmt->execute(); $stmt->bind_result($sum['orders']); $stmt->fetch(); $stmt->close();
}

$totalOrders = (int)$sum['orders'];
$totalPages = max(1, (int)ceil($totalOrders / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// --- Payment method breakdown ---
$payRows = [];
$sql = "SELECT COALESCE($P_METHOD,'Unknown') AS method, COUNT($O_ID) AS cnt, COALESCE(SUM($P_AMOUNT),0) AS amt FROM $T_P WHERE $P_DATE BETWEEN ? AND ? AND $P_STATUS='paid' GROUP BY COALESCE($P_METHOD,'Unknown') ORDER BY amt DESC";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('ss', $from, $to);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $payRows[] = $r; }
  $stmt->close();
}

// --- Orders list ---
$orders = [];
$sql = "SELECT o.$O_ID, o.$O_NUM, o.$O_CREATED, o.$O_TYPE, o.$O_STATUS, o.$O_TOTAL, CONCAT(ocd.$C_FNAME, ' ', ocd.$C_LNAME) as customer_name, opd.$P_STATUS, opd.$P_METHOD FROM $T_O o LEFT JOIN $T_C ocd ON o.$O_ID = ocd.$O_ID LEFT JOIN $T_P opd ON o.$O_ID = opd.$O_ID WHERE o.$O_CREATED BETWEEN ? AND ? ORDER BY o.$O_CREATED DESC LIMIT ? OFFSET ?";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('ssii', $from, $to, $perPage, $offset);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $orders[] = $r; }
  $stmt->close();
}
?>

<div class="container-fluid">
  <main class="main-content py-4">
    
    <div class="content-card mb-4 no-print">
      <div class="content-card-header d-flex justify-content-between align-items-center">
        <div>
          <h2 class="page-title mb-1">Reports &amp; Analytics</h2>
          <p class="text-muted mb-0 small">
            Track revenue, orders, and payment performance for your online food orders.
          </p>
        </div>
        <div class="d-flex gap-2 header-quick-range">
          <a href="?start=<?=h(date('Y-m-01'))?>&end=<?=h(date('Y-m-d'))?>&rev_mode=completed" class="btn btn-light btn-sm"><i class="bi bi-calendar3"></i> This Month</a>
          <a href="?start=<?=h(date('Y-m-d', strtotime('-6 days')))?>&end=<?=h(date('Y-m-d'))?>&rev_mode=completed" class="btn btn-light btn-sm"><i class="bi bi-clock-history"></i> Last 7 Days</a>
        </div>
      </div>

      <form class="row g-3 align-items-end mt-2" method="get" id="filterForm">
        <input type="hidden" name="page" value="1">
        <div class="col-md-3">
          <label class="form-label small text-muted">Start date</label>
          <input type="date" class="form-control modern-input" name="start" value="<?=h($start)?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted">End date</label>
          <input type="date" class="form-control modern-input" name="end" value="<?=h($end)?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted">Revenue mode</label>
          <select class="form-select modern-input" name="rev_mode">
            <option value="completed" <?= $revMode==='completed' ? 'selected':''; ?>>Delivered / Completed Orders</option>
            <option value="paid"      <?= $revMode==='paid' ? 'selected':''; ?>>Paid Transactions Only</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2 justify-content-md-end">
          <button class="btn btn-primary flex-grow-1 flex-md-grow-0 px-3"><i class="bi bi-funnel"></i> Apply</button>
          <a class="btn btn-outline-secondary px-3" href="reports.php"><i class="bi bi-x-circle"></i> Reset</a>
        </div>
      </form>
    </div>

    <div id="print-area">
      
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card stat-card-main">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div>
              <h5 class="stat-label">Total Revenue</h5>
              <div class="stat-value" id="stat-revenue"><?= h(peso($sum['revenue'])) ?></div>
              <div class="stat-hint"><?= $revMode==='paid' ? 'Paid transactions only' : 'Delivered / Completed orders' ?></div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon subtle"><i class="bi bi-basket2"></i></div>
            <div>
              <h5 class="stat-label">Total Orders</h5>
              <div class="stat-value" id="stat-orders"><?= h(number_format($sum['orders'])) ?></div>
              <div class="stat-hint"><?= h($start) ?> – <?= h($end) ?></div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon subtle"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
              <h5 class="stat-label">Average Ticket</h5>
              <div class="stat-value" id="stat-avg">
                <?= $sum['orders'] ? h(peso($sum['revenue'] / $sum['orders'])) : '₱0.00' ?>
              </div>
              <div class="stat-hint">Revenue ÷ Orders</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon subtle"><i class="bi bi-calendar-range"></i></div>
            <div>
              <h5 class="stat-label">Date Range</h5>
              <div class="stat-value"><?= h(date('M d', strtotime($start))) ?> – <?= h(date('M d, Y', strtotime($end))) ?></div>
              <div class="stat-hint">Customizable range</div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-card mb-4">
        <div class="content-card-header d-flex justify-content-between align-items-center">
          <div>
            <h2 class="section-title mb-1">Payment Breakdown</h2>
            <p class="text-muted small mb-0">Distribution of <strong>paid</strong> transactions by payment method.</p>
          </div>
          <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="printReport()"><i class="bi bi-printer"></i> Print report</button>
          </div>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-hover align-middle modern-table">
            <thead><tr><th>Payment method</th><th class="text-end">Count</th><th class="text-end">Total (₱)</th></tr></thead>
            <tbody id="payment-body">
              <?php if (!$payRows): ?>
                <tr><td colspan="3" class="text-center text-muted py-4"><i class="bi bi-info-circle me-1"></i>No paid transactions in this range.</td></tr>
              <?php else: ?>
                <?php foreach ($payRows as $r): ?>
                  <tr>
                    <td class="fw-medium"><span class="badge rounded-pill bg-light text-dark border me-1"><?= h(ucfirst($r['method'])) ?></span></td>
                    <td class="text-end"><?= h(number_format($r['cnt'])) ?></td>
                    <td class="text-end"><?= h(peso($r['amt'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="content-card">
        <div class="content-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h2 class="section-title mb-1">All Orders <span class="text-muted fw-normal">(<?= h($start) ?> – <?= h($end) ?>)</span></h2>
            <p class="text-muted small mb-0">All orders in the selected range, 10 per page.</p>
          </div>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-hover align-middle modern-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Type</th><th>Payment status</th><th>Payment method</th><th>Order status</th><th class="text-end">Total (₱)</th><th>Created at</th></tr></thead>
            <tbody id="orders-body">
              <?php if (!$orders): ?>
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox me-1"></i>No orders in this period.</td></tr>
              <?php else: ?>
                <?php foreach ($orders as $r): ?>
                  <?php
                    $payStatus = $r[$P_STATUS] ?? 'unpaid';
                    $payBadge = ($payStatus === 'paid') ? 'bg-success-subtle text-success' : (($payStatus === 'failed') ? 'bg-danger-subtle text-danger' : (($payStatus === 'refunded') ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary'));
                    $status_map = ['pending'=>'status-pending', 'confirmed'=>'status-confirmed', 'preparing'=>'status-preparing', 'ready'=>'status-ready', 'out_for_delivery'=>'status-out-for-delivery', 'delivered'=>'status-delivered', 'completed'=>'status-completed', 'cancelled'=>'status-cancelled'];
                    $status_class = $status_map[$r[$O_STATUS]] ?? 'bg-secondary text-white';
                    $customer = trim($r['customer_name'] ?? '');
                    $initials = ($customer !== '') ? strtoupper(substr($customer, 0, 1)) : 'C';
                  ?>
                  <tr>
                    <td class="fw-semibold"><strong><?= h($r[$O_NUM] ?? $r[$O_ID]) ?></strong></td>
                    <td><div class="d-flex align-items-center gap-2"><div class="avatar-circle"><span><?= h($initials) ?></span></div><span class="text-truncate" style="max-width: 160px;"><?= h($customer ?: 'Walk-in / Unknown') ?></span></div></td>
                    <td><?= h(ucfirst($r[$O_TYPE])) ?></td>
                    <td><span class="badge <?= h($payBadge) ?> badge-rounded"><?= h(ucfirst($payStatus ?: 'Pending')) ?></span></td>
                    <td class="text-capitalize"><?= h($r[$P_METHOD] ? $r[$P_METHOD] : '—') ?></td>
                    <td><span class="status-badge <?= h($status_class) ?>"><?= h(ucfirst(str_replace('_', ' ', $r[$O_STATUS]))) ?></span></td>
                    <td class="text-end fw-semibold"><?= h(peso($r[$O_TOTAL])) ?></td>
                    <td><?= h(date('Y-m-d H:i', strtotime($r[$O_CREATED]))) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center no-print mt-3 flex-wrap gap-2">
          <small class="text-muted" id="pagination-info">Showing page <?= $page ?> of <?= $totalPages ?> • <?= h(number_format($totalOrders)) ?> orders total</small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?start=<?=h($start)?>&end=<?=h($end)?>&rev_mode=<?=h($revMode)?>&page=<?= max(1, $page - 1) ?>">&laquo;</a>
              </li>
              <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
              <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?start=<?=h($start)?>&end=<?=h($end)?>&rev_mode=<?=h($revMode)?>&page=<?= min($totalPages, $page + 1) ?>">&raquo;</a>
              </li>
            </ul>
          </nav>
        </div>
        <?php endif; ?>
      </div>
    </div> 
  </main>
</div>

<style>
/* --- Modern look for dashboard --- */
body {
  background-color: #f3f4f6;
}

/* Main layout */
.main-content {
  min-height: 100vh;
}

/* Card styling */
.content-card {
  border-radius: 18px;
  border: 1px solid rgba(148, 163, 184, 0.3);
  background: #ffffff;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
  padding: 18px 20px;
}

.content-card-header {
  border-bottom: 1px solid rgba(148, 163, 184, 0.25);
  padding-bottom: 12px;
  margin-bottom: 8px;
}

.page-title {
  font-weight: 600;
}

.section-title {
  font-size: 1.05rem;
  font-weight: 600;
}

/* Stat cards */
.stat-card {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #eef2ff, #f9fafb);
  border: 1px solid rgba(129, 140, 248, 0.2);
  box-shadow: 0 12px 30px rgba(31, 41, 55, 0.08);
  transition: transform 0.12s ease-out, box-shadow 0.12s ease-out, background 0.12s ease-out;
}

.stat-card-main {
  background: radial-gradient(circle at top left, #4f46e5, #312e81);
  color: #f9fafb;
  border: none;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
}

.stat-card-main .stat-label,
.stat-card-main .stat-value,
.stat-card-main .stat-hint {
  color: #e5e7eb;
}

.stat-card-main .stat-value {
  color: #f9fafb;
}

.stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.08);
  color: #111827;
  flex-shrink: 0;
}

.stat-card-main .stat-icon {
  background: rgba(15, 23, 42, 0.18);
  color: #f9fafb;
}

.stat-icon.subtle {
  background: rgba(148, 163, 184, 0.15);
  color: #4b5563;
}

.stat-label {
  margin: 0;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6b7280;
}

.stat-card-main .stat-label {
  color: #e5e7eb;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 600;
}

.stat-hint {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Inputs */
.modern-input {
  border-radius: 999px;
  border-color: #e5e7eb;
  font-size: 0.9rem;
}

.modern-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.15);
}

/* Table */
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

/* Avatar circle for customer initials */
.avatar-circle {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: #eef2ff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 600;
  color: #4f46e5;
}

/* Status Badges (Same as Manage Orders) */
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

/* Payment Badge (Same as Manage Orders) */
.badge-rounded {
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
}

/* Buttons */
.header-quick-range .btn-light {
  border-radius: 999px;
  border-color: #e5e7eb;
  font-size: 0.8rem;
}

.header-quick-range .btn-light:hover {
  background-color: #eef2ff;
}

/* Pagination */
.pagination .page-link {
  border-radius: 999px !important;
}

/* Print styles */
@media print {
  body { background: #fff; }
  .sidebar, .no-print, nav, .content-card-header .right { display:none !important; }
  .content-card { box-shadow:none !important; border:none !important; }
  .main-content { padding:0 !important; }
  .table { font-size: 12px; }
}
</style>

<script>
  function printReport() {
    var printArea = document.getElementById('print-area');
    if (!printArea) { window.print(); return; }
    var printWindow = window.open('', '_blank', 'width=1000,height=700');
    printWindow.document.write('<html><head><title>Reports - Print</title>');
    var styles = document.querySelectorAll('link[rel="stylesheet"], style');
    styles.forEach(function(node) { printWindow.document.write(node.outerHTML); });
    printWindow.document.write('</head><body>');
    printWindow.document.write(printArea.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  }

  // --- Real-time Polling ---
  document.addEventListener('DOMContentLoaded', function() {
      function fetchUpdates() {
          const params = window.location.search;
          fetch('actions/fetch_reports_updates.php' + params)
              .then(res => res.json())
              .then(data => {
                  // Update Stats
                  document.getElementById('stat-revenue').textContent = data.stats.revenue;
                  document.getElementById('stat-orders').textContent = data.stats.orders;
                  document.getElementById('stat-avg').textContent = data.stats.avg;

                  // Update Tables (only if changed to avoid flicker, simplified here)
                  document.getElementById('payment-body').innerHTML = data.html.payment;
                  document.getElementById('orders-body').innerHTML = data.html.orders;
                  
                  // Update pagination info if exists
                  const pagInfo = document.getElementById('pagination-info');
                  if (pagInfo) pagInfo.textContent = data.pagination.info;
              })
              .catch(err => console.error("Polling error:", err));
      }

      // Poll every 1 seconds
      setInterval(fetchUpdates, 1000);
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>