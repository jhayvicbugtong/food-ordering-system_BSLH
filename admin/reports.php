<?php
// admin/reports.php
include __DIR__ . '/includes/header.php'; // Includes db_connect.php
include __DIR__ . '/includes/sidebar.php';

// ============ NEW DB CONFIG (online_food_ordering_db.sql) ============
$DB = [
  'TABLE_ORDERS'   => 'orders',
  'TABLE_CUST'     => 'order_customer_details',
  'TABLE_PAY'      => 'order_payment_details',
  
  'COL_ORDER_ID'   => 'order_id', // This is the foreign key in all tables
  'COL_ORDER_NUM'  => 'order_number',
  'COL_USER_ID'    => 'user_id',
  
  'COL_CUST_NAME'  => 'customer_first_name', // Will combine with last_name
  'COL_CUST_LNAME' => 'customer_last_name',
  
  'COL_TYPE'       => 'order_type',
  'COL_STATUS'     => 'status', // 'pending','confirmed','preparing','ready','out_for_delivery','delivered','completed','cancelled'
  
  'COL_PAY_STATUS' => 'payment_status', // 'pending','paid','failed'
  'COL_PAY_METHOD' => 'payment_method', // 'cash','gcash','card'
  'COL_PAY_AMOUNT' => 'amount_paid',
  'COL_PAY_DATE'   => 'paid_at',

  'COL_TOTAL'      => 'total_amount',
  'COL_CREATED'    => 'created_at',
];
// =============================================================

if (!isset($conn) || !($conn instanceof mysqli)) {
  die('<div class="alert alert-danger m-4">DB connection not found. Check <code>includes/db_connect.php</code>.</div>');
}

// --- Helpers ---
function peso($n) { return '₱' . number_format((float)$n, 2); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Inputs (defaults: last 7 days) ---
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : date('Y-m-d');
$revMode = $_GET['rev_mode'] ?? 'completed'; // 'completed' | 'paid'
$isExport = isset($_GET['export']) && $_GET['export'] === '1';

// Build base range
$from = $start . ' 00:00:00';
$to   = $end   . ' 23:59:59';

// --- Table Aliases for Queries ---
$T_O = $DB['TABLE_ORDERS'];
$T_C = $DB['TABLE_CUST'];
$T_P = $DB['TABLE_PAY'];

// --- SQL Columns ---
$O_ID = $DB['COL_ORDER_ID'];
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


// Revenue filter
if ($revMode === 'paid') {
  // Revenue is SUM(amount_paid) from 'order_payment_details' where status is 'paid'
  $revWhere = "($T_P.$P_DATE BETWEEN ? AND ?) AND $T_P.$P_STATUS = 'paid'";
} else {
  // Revenue is SUM(total_amount) from 'orders' where status is 'delivered' or 'completed'
  $revWhere = "($T_O.$O_CREATED BETWEEN ? AND ?) AND $T_O.$O_STATUS IN ('delivered','completed')";
}

// -------- Summary: total sales + total orders ----------
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

// -------- Payment method breakdown (paid only) ----------
$payRows = [];
$sql = "SELECT 
            COALESCE($P_METHOD,'Unknown') AS method, 
            COUNT($O_ID) AS cnt, 
            COALESCE(SUM($P_AMOUNT),0) AS amt
        FROM $T_P
        WHERE $P_DATE BETWEEN ? AND ? AND $P_STATUS='paid'
        GROUP BY COALESCE($P_METHOD,'Unknown')
        ORDER BY amt DESC";

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('ss', $from, $to);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $payRows[] = $r; }
  $stmt->close();
}

// -------- Orders list (all in range) ----------
$orders = [];
$sql = "SELECT 
            o.$O_ID, o.$O_CREATED, o.$O_TYPE, o.$O_STATUS, o.$O_TOTAL,
            CONCAT(ocd.$C_FNAME, ' ', ocd.$C_LNAME) as customer_name,
            opd.$P_STATUS, opd.$P_METHOD
        FROM $T_O o
        LEFT JOIN $T_C ocd ON o.$O_ID = ocd.$O_ID
        LEFT JOIN $T_P opd ON o.$O_ID = opd.$O_ID
        WHERE o.$O_CREATED BETWEEN ? AND ?
        ORDER BY o.$O_CREATED DESC";

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('ss', $from, $to);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $orders[] = $r; }
  $stmt->close();
}

// CSV Export
if ($isExport) {
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="orders_'.$start.'_to_'.$end.'.csv"');
  $out = fopen('php://output', 'w');
  // Use new column names for CSV header
  fputcsv($out, ['Order ID','Customer','Type','Payment Status','Payment Method','Status','Total','Created At']);
  foreach ($orders as $r) {
    fputcsv($out, [
      $r[$O_ID], $r['customer_name'], $r[$O_TYPE], $r[$P_STATUS], $r[$P_METHOD], $r[$O_STATUS], $r[$O_TOTAL], $r[$O_CREATED]
    ]);
  }
  fclose($out);
  exit;
}
?>

<div class="container-fluid">
  <main class="main-content">
    <div class="content-card mb-4 no-print">
      <div class="content-card-header">
        <div class="left">
          <h2>Reports</h2>
          <p>Sales, payments, and orders within a selected date range</p>
        </div>
        <div class="right">
          <a href="?start=<?=h(date('Y-m-01'))?>&end=<?=h(date('Y-m-d'))?>&rev_mode=completed" class="btn btn-outline-secondary">
            This Month
          </a>
          <a href="?start=<?=h(date('Y-m-d', strtotime('-6 days')))?>&end=<?=h(date('Y-m-d'))?>&rev_mode=completed" class="btn btn-outline-secondary">
            Last 7 Days
          </a>
        </div>
      </div>

      <form class="row g-3 align-items-end" method="get">
        <div class="col-md-3">
          <label class="form-label">Start date</label>
          <input type="date" class="form-control" name="start" value="<?=h($start)?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">End date</label>
          <input type="date" class="form-control" name="end" value="<?=h($end)?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Revenue Mode</label>
          <select class="form-select" name="rev_mode">
            <option value="completed" <?= $revMode==='completed' ? 'selected':''; ?>>Delivered/Completed Orders</option>
            <option value="paid"      <?= $revMode==='paid' ? 'selected':''; ?>>Paid Transactions Only</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
          <a class="btn btn-outline-secondary" href="reports.php"><i class="bi bi-x-circle"></i> Reset</a>
        </div>
      </form>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Total Revenue</h5>
          <div class="value"><?= h(peso($sum['revenue'])) ?></div>
          <div class="hint"><?= $revMode==='paid' ? 'Paid only' : 'Delivered/Completed' ?></div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Total Orders</h5>
          <div class="value"><?= h(number_format($sum['orders'])) ?></div>
          <div class="hint"><?= h($start) ?> – <?= h($end) ?></div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Avg Ticket</h5>
          <div class="value">
            <?= $sum['orders'] ? h(peso($sum['revenue'] / $sum['orders'])) : '₱0.00' ?>
          </div>
          <div class="hint">Revenue / Orders</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Date Range</h5>
          <div class="value"><?= h(date('M d', strtotime($start))) ?>–<?= h(date('M d, Y', strtotime($end))) ?></div>
          <div class="hint">Customizable</div>
        </div>
      </div>
    </div>

    <div class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Payment Breakdown</h2>
          <p>Paid transactions by method</p>
        </div>
        <div class="right">
          <button class="btn btn-outline-secondary no-print" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
          </button>
          <a class="btn btn-success no-print"
             href="?start=<?=h($start)?>&end=<?=h($end)?>&rev_mode=<?=h($revMode)?>&export=1">
            <i class="bi bi-filetype-csv"></i> Export CSV
          </a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Payment Method</th>
              <th>Count</th>
              <th>Total (₱)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$payRows): ?>
              <tr><td colspan="3" class="text-center text-muted">No paid transactions in this range.</td></tr>
            <?php else: ?>
              <?php foreach ($payRows as $r): ?>
              <tr>
                <td><?= h(ucfirst($r['method'])) ?></td>
                <td><?= h(number_format($r['cnt'])) ?></td>
                <td><?= h(peso($r['amt'])) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>All Orders (<?= h($start) ?> – <?= h($end) ?>)</h2>
          <p>Every order in the selected range</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Payment Status</th>
              <th>Payment Method</th>
              <th>Status</th>
              <th>Total (₱)</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$orders): ?>
              <tr><td colspan="8" class="text-center text-muted">No orders in this period.</td></tr>
            <?php else: ?>
              <?php foreach ($orders as $r): ?>
                <tr>
                  <td>#<?= h($r[$O_ID]) ?></td>
                  <td><?= h($r['customer_name']) ?></td>
                  <td><?= h(ucfirst($r[$O_TYPE])) ?></td>
                  <td>
                    <span class="badge <?= $r[$P_STATUS]==='paid'?'badge-success':'badge-warning' ?>">
                      <?= h(ucfirst($r[$P_STATUS] ?? 'N/A')) ?>
                    </span>
                  </td>
                  <td><?= h(ucfirst($r[$P_METHOD] ?? '—')) ?></td>
                  <td>
                    <?php
                      $map = [
                        'pending' => 'badge-warning',
                        'confirmed' => 'badge-primary',
                        'preparing' => 'badge-info',
                        'ready' => 'badge-success',
                        'out_for_delivery' => 'badge-info',
                        'delivered' => 'badge-secondary',
                        'completed' => 'badge-secondary',
                        'cancelled' => 'badge-danger',
                      ];
                      $cls = $map[$r[$O_STATUS]] ?? 'badge-light';
                    ?>
                    <span class="badge <?= h($cls) ?>"><?= h(ucwords(str_replace('_',' ',$r[$O_STATUS]))) ?></span>
                  </td>
                  <td><?= h(peso($r[$O_TOTAL])) ?></td>
                  <td><?= h(date('Y-m-d H:i', strtotime($r[$O_CREATED]))) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<style>
@media print {
  body { background: #fff; }
  .sidebar, .no-print, nav, .content-card-header .right { display:none !important; }
  .content-card { box-shadow:none !important; border:none !important; }
  .main-content { padding:0 !important; }
  .table { font-size: 12px; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>