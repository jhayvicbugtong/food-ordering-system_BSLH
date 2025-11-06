<?php
// admin/reports.php
// ============ CONFIGURE YOUR DB COLUMN NAMES HERE ============
// Adjust these if your schema differs.
$DB = [
  'TABLE_ORDERS'   => 'orders',
  'COL_ID'         => 'id',
  'COL_CUSTOMER'   => 'customer_name',     // e.g. full name or contact name
  'COL_TYPE'       => 'order_type',        // e.g. 'Delivery','Pickup','POS'
  'COL_STATUS'     => 'status',            // e.g. 'pending','preparing','ready','delivered','completed','cancelled'
  'COL_PAY_STATUS' => 'payment_status',    // 'paid' / 'unpaid'
  'COL_PAY_METHOD' => 'payment_method',    // 'Cash','GCash','BankTransfer','POS','Walk-in'
  'COL_TOTAL'      => 'total_amount',      // numeric
  'COL_CREATED'    => 'created_at',        // DATETIME or TIMESTAMP
];

// =============================================================

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
//require_once __DIR__ . '/../customer/includes/db_connect.php'; // mysqli $conn

if (!isset($conn) || !($conn instanceof mysqli)) {
  die('<div class="alert alert-danger m-4">DB connection not found. Check <code>customer/includes/db_connect.php</code>.</div>');
}

// --- Helpers ---
function peso($n) { return '₱' . number_format((float)$n, 2); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Inputs (defaults: last 7 days) ---
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : date('Y-m-d');

// Optional: show revenue as PAID only or COMPLETED (delivered+completed)
$revMode = $_GET['rev_mode'] ?? 'completed'; // 'completed' | 'paid'

// CSV export?
$isExport = isset($_GET['export']) && $_GET['export'] === '1';

// Build base range
$from = $start . ' 00:00:00';
$to   = $end   . ' 23:59:59';

// --- SQL snippets from config ---
$T = $DB['TABLE_ORDERS'];
$ID= $DB['COL_ID']; $CUS=$DB['COL_CUSTOMER']; $TYPE=$DB['COL_TYPE']; $STAT=$DB['COL_STATUS'];
$PST=$DB['COL_PAY_STATUS']; $PM=$DB['COL_PAY_METHOD']; $TOT=$DB['COL_TOTAL']; $CRT=$DB['COL_CREATED'];

// Revenue filter
if ($revMode === 'paid') {
  $revWhere = "($CRT BETWEEN ? AND ?) AND $PST='paid'";
} else {
  // delivered/completed count as revenue; adjust if your statuses differ
  $revWhere = "($CRT BETWEEN ? AND ?) AND $STAT IN ('delivered','completed')";
}

// -------- Summary: total sales + total orders ----------
$sum = ['revenue'=>0,'orders'=>0];

// if ($stmt = $conn->prepare("SELECT COALESCE(SUM($TOT),0) FROM $T WHERE $revWhere")) {
//   $stmt->bind_param('ss', $from, $to);
//   $stmt->execute(); $stmt->bind_result($sum['revenue']); $stmt->fetch(); $stmt->close();
// }
// if ($stmt = $conn->prepare("SELECT COUNT(*) FROM $T WHERE $CRT BETWEEN ? AND ?")) {
//   $stmt->bind_param('ss', $from, $to);
//   $stmt->execute(); $stmt->bind_result($sum['orders']); $stmt->fetch(); $stmt->close();
// }

// -------- Payment method breakdown (paid only) ----------
$payRows = [];
// if ($stmt = $conn->prepare("SELECT COALESCE($PM,'Unknown') AS method, COUNT(*) AS cnt, COALESCE(SUM($TOT),0) AS amt
//                             FROM $T
//                             WHERE $CRT BETWEEN ? AND ? AND $PST='paid'
//                             GROUP BY COALESCE($PM,'Unknown')
//                             ORDER BY amt DESC")) {
//   $stmt->bind_param('ss', $from, $to);
//   $stmt->execute();
//   $res = $stmt->get_result();
//   while ($r = $res->fetch_assoc()) { $payRows[] = $r; }
//   $stmt->close();
// }

// -------- Orders list (all in range) ----------
$orders = [];
// if ($stmt = $conn->prepare("SELECT $ID, $CUS, $TYPE, $PST, $PM, $STAT, $TOT, $CRT
//                             FROM $T
//                             WHERE $CRT BETWEEN ? AND ?
//                             ORDER BY $CRT DESC")) {
//   $stmt->bind_param('ss', $from, $to);
//   $stmt->execute();
//   $res = $stmt->get_result();
//   while ($r = $res->fetch_assoc()) { $orders[] = $r; }
//   $stmt->close();
// }

// CSV Export
if ($isExport) {
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="orders_'.$start.'_to_'.$end.'.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Order ID','Customer','Type','Payment Status','Payment Method','Status','Total','Created At']);
  foreach ($orders as $r) {
    fputcsv($out, [
      $r[$ID], $r[$CUS], $r[$TYPE], $r[$PST], $r[$PM], $r[$STAT], $r[$TOT], $r[$CRT]
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

      <!-- Filters -->
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

    <!-- Summary Cards -->
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

    <!-- Payment Breakdown -->
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
                <td><?= h($r['method']) ?></td>
                <td><?= h(number_format($r['cnt'])) ?></td>
                <td><?= h(peso($r['amt'])) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Orders Table -->
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
                  <td>#<?= h($r[$ID]) ?></td>
                  <td><?= h($r[$CUS]) ?></td>
                  <td><?= h($r[$TYPE]) ?></td>
                  <td>
                    <span class="badge <?= $r[$PST]==='paid'?'badge-success':'badge-warning' ?>">
                      <?= h(ucfirst($r[$PST])) ?>
                    </span>
                  </td>
                  <td><?= h($r[$PM] ?? '—') ?></td>
                  <td>
                    <?php
                      $map = [
                        'pending' => 'badge-warning',
                        'preparing' => 'badge-warning',
                        'ready' => 'badge-success',
                        'out_for_delivery' => 'badge-info',
                        'delivered' => 'badge-success',
                        'completed' => 'badge-secondary',
                        'cancelled' => 'badge-danger',
                      ];
                      $cls = $map[$r[$STAT]] ?? 'badge-light';
                    ?>
                    <span class="badge <?= h($cls) ?>"><?= h(ucwords(str_replace('_',' ',$r[$STAT]))) ?></span>
                  </td>
                  <td><?= h(peso($r[$TOT])) ?></td>
                  <td><?= h(date('Y-m-d H:i', strtotime($r[$CRT]))) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- PRINT STYLES -->
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
