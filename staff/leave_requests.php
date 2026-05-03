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
    FROM staff_leave_requests
    WHERE staff_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$requests = $stmt->get_result();

$summary_stmt = $conn->prepare("
    SELECT
      COUNT(*) AS total_requests,
      SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
      SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
      SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM staff_leave_requests
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

.leave-stat {
  padding: 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ecfdf3, #ffffff);
  border: 1px solid rgba(92, 250, 99, 0.35);
  box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
  height: 100%;
}

.leave-stat h5 {
  font-size: 0.78rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 6px;
}

.leave-stat .value {
  font-size: 1.35rem;
  font-weight: 700;
  color: #111827;
}

.staff-table {
  margin-bottom: 0;
  min-width: 900px;
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

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-approved {
  background: #dcfce7;
  color: #166534;
}

.status-rejected {
  background: #fee2e2;
  color: #b91c1c;
}

.quick-btn {
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 8px 14px;
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
        <h2>Leave Request</h2>
        <p>Submit leave requests and monitor approval status.</p>
      </div>

      <a href="index.php" class="btn btn-outline-dark quick-btn">
        <i class="bi bi-speedometer2 me-1"></i>
        Back to Dashboard
      </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
        Leave request submitted successfully.
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-danger">
        Unable to submit leave request. Please check your details.
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-12 col-md-3">
        <div class="leave-stat">
          <h5>Total Requests</h5>
          <div class="value"><?= number_format((int)($summary['total_requests'] ?? 0)) ?></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="leave-stat">
          <h5>Pending</h5>
          <div class="value"><?= number_format((int)($summary['pending_count'] ?? 0)) ?></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="leave-stat">
          <h5>Approved</h5>
          <div class="value"><?= number_format((int)($summary['approved_count'] ?? 0)) ?></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="leave-stat">
          <h5>Rejected</h5>
          <div class="value"><?= number_format((int)($summary['rejected_count'] ?? 0)) ?></div>
        </div>
      </div>
    </div>

    <form method="POST" action="actions/submit_leave_request.php">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Leave Type</label>
          <select name="leave_type" class="form-select" required>
            <option value="">Select Leave Type</option>
            <option value="Sick Leave">Sick Leave</option>
            <option value="Vacation Leave">Vacation Leave</option>
            <option value="Emergency Leave">Emergency Leave</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Start Date</label>
          <input type="date" name="start_date" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">End Date</label>
          <input type="date" name="end_date" class="form-control" required>
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for leave request"></textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-success quick-btn">
        <i class="bi bi-send me-1"></i>
        Submit Leave Request
      </button>
    </form>
  </div>

  <div class="content-card">
    <div class="content-card-header">
      <div>
        <h2>My Leave Requests</h2>
        <p>Your leave request history.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover staff-table">
        <thead>
          <tr>
            <th>Type</th>
            <th>Start</th>
            <th>End</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Admin Remarks</th>
            <th>Submitted</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($requests && $requests->num_rows > 0): ?>
            <?php while ($row = $requests->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status']);
                $badgeClass = 'status-pending';

                if ($status === 'approved') {
                    $badgeClass = 'status-approved';
                } elseif ($status === 'rejected') {
                    $badgeClass = 'status-rejected';
                }
              ?>

              <tr>
                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                <td><?= date('M d, Y', strtotime($row['end_date'])) ?></td>
                <td style="max-width: 240px; white-space: normal;">
                  <?= htmlspecialchars($row['reason'] ?: '-') ?>
                </td>
                <td>
                  <span class="status-badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                <td style="max-width: 240px; white-space: normal;">
                  <?= htmlspecialchars($row['admin_remarks'] ?: '-') ?>
                </td>
                <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <i class="bi bi-calendar-x"></i>
                  <h5>No leave requests yet</h5>
                  <p>Your submitted leave requests will appear here.</p>
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