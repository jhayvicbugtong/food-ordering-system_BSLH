<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$staff = $conn->query("
    SELECT user_id, first_name, last_name 
    FROM users 
    WHERE role = 'staff' AND is_active = 1 
    ORDER BY first_name ASC
");

$payrolls = $conn->query("
    SELECT 
        p.*, 
        u.first_name, 
        u.last_name,
        admin.first_name AS admin_first_name,
        admin.last_name AS admin_last_name
    FROM staff_payroll p
    INNER JOIN users u ON p.staff_id = u.user_id
    LEFT JOIN users admin ON p.paid_by = admin.user_id
    ORDER BY p.created_at DESC
");
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">

<div class="main-content">

  <div class="content-card mb-4">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Payroll Management</h2>
        <p>Generate payroll based on staff attendance records.</p>
      </div>
    </div>

    <form method="POST" action="actions/generate_payroll.php" class="payroll-form">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Staff</label>
          <select name="staff_id" class="form-select" required>
            <option value="">Select Staff</option>
            <?php if ($staff && $staff->num_rows > 0): ?>
              <?php while ($row = $staff->fetch_assoc()): ?>
                <option value="<?= $row['user_id'] ?>">
                  <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                </option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-3 mb-3">
          <label class="form-label">Period Start</label>
          <input type="date" name="period_start" class="form-control" required>
        </div>

        <div class="col-md-3 mb-3">
          <label class="form-label">Period End</label>
          <input type="date" name="period_end" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Hourly Rate</label>
          <input type="number" step="0.01" name="hourly_rate" class="form-control" placeholder="0.00" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Deductions</label>
          <input type="number" step="0.01" name="deductions" class="form-control" value="0">
        </div>
      </div>

      <button type="submit" class="btn btn-success">
        <i class="bi bi-cash-stack me-1"></i>
        Generate Payroll
      </button>
    </form>
  </div>

  <div class="content-card">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Payroll Records</h2>
        <p>View generated salary records, payment method, payment date, and paid by admin.</p>
      </div>

      <div>
        <a href="print_payroll.php" class="btn btn-success">
          <i class="bi bi-printer me-1"></i>
          Print All Payroll
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-vcenter">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Period</th>
            <th>Total Hours</th>
            <th>Rate</th>
            <th>Gross</th>
            <th>Deductions</th>
            <th>Net</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Paid Date</th>
            <th>Paid By</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($payrolls && $payrolls->num_rows > 0): ?>
            <?php while ($row = $payrolls->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status']);

                if ($status === 'paid') {
                    $badgeClass = 'badge-success';
                } elseif ($status === 'pending') {
                    $badgeClass = 'badge-warning';
                } else {
                    $badgeClass = 'badge-danger';
                }

                $paidBy = '-';
                if (!empty($row['admin_first_name']) || !empty($row['admin_last_name'])) {
                    $paidBy = trim($row['admin_first_name'] . ' ' . $row['admin_last_name']);
                }
              ?>

              <tr>
                <td>
                  <strong>
                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                  </strong>
                </td>

                <td>
                  <?= date('M d, Y', strtotime($row['period_start'])) ?>
                  -
                  <?= date('M d, Y', strtotime($row['period_end'])) ?>
                </td>

                <td><?= number_format((float)$row['total_hours'], 2) ?> hrs</td>

                <td>₱<?= number_format((float)$row['hourly_rate'], 2) ?></td>

                <td>₱<?= number_format((float)$row['gross_pay'], 2) ?></td>

                <td>₱<?= number_format((float)$row['deductions'], 2) ?></td>

                <td>
                  <strong>₱<?= number_format((float)$row['net_pay'], 2) ?></strong>
                </td>

                <td>
                  <span class="badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>

                <td>
                  <?= htmlspecialchars($row['payment_method'] ?: '-') ?>
                </td>

                <td>
                  <?= !empty($row['paid_at']) ? date('M d, Y h:i A', strtotime($row['paid_at'])) : '-' ?>
                </td>

                <td>
                  <?= htmlspecialchars($paidBy) ?>
                </td>

                <td>
                  <div class="d-flex gap-1 flex-wrap">
                    <a href="print_payroll.php?staff_id=<?= $row['staff_id'] ?>" class="btn btn-sm btn-success">
                      <i class="bi bi-printer"></i>
                    </a>

                    <?php if ($status === 'pending'): ?>
                      <a href="actions/mark_paid.php?id=<?= $row['payroll_id'] ?>" class="btn btn-sm btn-dark">
                        <i class="bi bi-check-circle"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="12">
                <div class="empty-state">
                  <i class="bi bi-cash-coin"></i>
                  <h5>No payroll records found</h5>
                  <p>Generated payroll records will appear here.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>