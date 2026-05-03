<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM staff_payroll 
    WHERE staff_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$payrolls = $stmt->get_result();

$summary_stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_records,
        COALESCE(SUM(net_pay), 0) AS total_net_pay,
        COALESCE(SUM(total_hours), 0) AS total_hours
    FROM staff_payroll
    WHERE staff_id = ?
");
$summary_stmt->bind_param("i", $staff_id);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<style>
body {
  background-color: #f3f4f6;
}

.main-content {
  min-height: 100vh;
  padding: 1.5rem;
  margin-left: 220px;
  transition: margin-left 0.3s ease;
}

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
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.content-card-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.content-card-header p {
  font-size: 0.85rem;
  margin-bottom: 0;
  color: #6b7280;
}

.payroll-stat {
  padding: 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ecfdf3, #ffffff);
  border: 1px solid rgba(92, 250, 99, 0.35);
  box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
  height: 100%;
}

.payroll-stat h5 {
  font-size: 0.78rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 6px;
}

.payroll-stat .value {
  font-size: 1.35rem;
  font-weight: 700;
  color: #111827;
}

.payroll-stat .hint {
  font-size: 0.8rem;
  color: #9ca3af;
}

.staff-table {
  margin-bottom: 0;
  min-width: 1100px;
}

.staff-table thead th {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 600;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.staff-table th,
.staff-table td {
  font-size: 0.9rem;
  white-space: nowrap;
  vertical-align: middle;
  padding: 12px 10px;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-paid {
  background: #dcfce7;
  color: #166534;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-other {
  background: #e5e7eb;
  color: #374151;
}

.empty-state {
  text-align: center;
  padding: 32px;
  color: #6b7280;
}

.empty-state i {
  font-size: 2rem;
  color: #9ca3af;
  margin-bottom: 8px;
}

.quick-btn {
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 8px 14px;
}

@media (max-width: 992px) {
  .main-content {
    margin-left: 0;
  }
}
</style>

<main class="main-content">

  <div class="content-card">
    <div class="content-card-header">
      <div>
        <h2>My Payroll</h2>
        <p>View your generated salary records, payment status, and payslips.</p>
      </div>

      <a href="index.php" class="btn btn-outline-dark quick-btn">
        <i class="bi bi-speedometer2 me-1"></i>
        Back to Dashboard
      </a>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-4">
        <div class="payroll-stat">
          <h5>Total Payroll Records</h5>
          <div class="value"><?= number_format((int)($summary['total_records'] ?? 0)) ?></div>
          <div class="hint">Generated payroll entries</div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="payroll-stat">
          <h5>Total Hours Paid</h5>
          <div class="value"><?= number_format((float)($summary['total_hours'] ?? 0), 2) ?> hrs</div>
          <div class="hint">All payroll periods</div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="payroll-stat">
          <h5>Total Net Pay</h5>
          <div class="value">₱<?= number_format((float)($summary['total_net_pay'] ?? 0), 2) ?></div>
          <div class="hint">Accumulated net pay</div>
        </div>
      </div>
    </div>
  </div>

  <div class="content-card">
    <div class="content-card-header">
      <div>
        <h2>Payroll History</h2>
        <p>Your payroll records sorted by latest generated date.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover staff-table">
        <thead>
          <tr>
            <th>Period</th>
            <th>Total Hours</th>
            <th>Hourly Rate</th>
            <th>Gross Pay</th>
            <th>Deductions</th>
            <th>Net Pay</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Paid Date</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($payrolls && $payrolls->num_rows > 0): ?>
            <?php while ($row = $payrolls->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status'] ?? 'pending');

                if ($status === 'paid') {
                    $badgeClass = 'status-paid';
                } elseif ($status === 'pending') {
                    $badgeClass = 'status-pending';
                } else {
                    $badgeClass = 'status-other';
                }
              ?>
              <tr>
                <td>
                  <?= date('M d, Y', strtotime($row['period_start'])) ?>
                  -
                  <?= date('M d, Y', strtotime($row['period_end'])) ?>
                </td>

                <td><?= number_format((float)$row['total_hours'], 2) ?> hrs</td>

                <td>₱<?= number_format((float)$row['hourly_rate'], 2) ?></td>

                <td>₱<?= number_format((float)$row['gross_pay'], 2) ?></td>

                <td>₱<?= number_format((float)$row['deductions'], 2) ?></td>

                <td><strong>₱<?= number_format((float)$row['net_pay'], 2) ?></strong></td>

                <td>
                  <span class="status-badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>

                <td><?= htmlspecialchars($row['payment_method'] ?: '-') ?></td>

                <td>
                  <?= !empty($row['paid_at']) ? date('M d, Y h:i A', strtotime($row['paid_at'])) : '-' ?>
                </td>

                <td>
                  <a href="print_payslip.php?id=<?= $row['payroll_id'] ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-printer"></i>
                    Payslip
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10">
                <div class="empty-state">
                  <i class="bi bi-cash-coin"></i>
                  <h5>No payroll records yet</h5>
                  <p>Your payroll records will appear here once admin generates them.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<?php include 'includes/footer.php'; ?>