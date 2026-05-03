<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$status_filter = $_GET['status'] ?? '';
$staff_filter = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

$staff_list = $conn->query("
    SELECT user_id, first_name, last_name
    FROM users
    WHERE role = 'staff' AND is_active = 1
    ORDER BY first_name ASC
");

$where = [];
$params = [];
$types = "";

if ($staff_filter > 0) {
    $where[] = "l.staff_id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}

if (!empty($status_filter)) {
    $where[] = "l.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_sql = "";
if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$query = "
    SELECT
      l.*,
      u.first_name,
      u.last_name,
      u.email
    FROM staff_leave_requests l
    INNER JOIN users u ON l.staff_id = u.user_id
    $where_sql
    ORDER BY 
      CASE l.status
        WHEN 'Pending' THEN 1
        WHEN 'Approved' THEN 2
        WHEN 'Rejected' THEN 3
        ELSE 4
      END,
      l.created_at DESC
";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$requests = $stmt->get_result();

$summary = $conn->query("
    SELECT
      COUNT(*) AS total_requests,
      SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
      SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
      SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM staff_leave_requests
")->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">

<div class="main-content">

  <div class="content-card mb-4">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Staff Leave Requests</h2>
        <p>Review, approve, or reject staff leave requests.</p>
      </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
        Leave request updated successfully.
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-danger">
        Unable to update leave request.
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <h5>Total Requests</h5>
          <div class="value"><?= number_format((int)($summary['total_requests'] ?? 0)) ?></div>
          <div class="hint">All leave requests</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <h5>Pending</h5>
          <div class="value"><?= number_format((int)($summary['pending_count'] ?? 0)) ?></div>
          <div class="hint">Needs review</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <h5>Approved</h5>
          <div class="value"><?= number_format((int)($summary['approved_count'] ?? 0)) ?></div>
          <div class="hint">Accepted requests</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <h5>Rejected</h5>
          <div class="value"><?= number_format((int)($summary['rejected_count'] ?? 0)) ?></div>
          <div class="hint">Declined requests</div>
        </div>
      </div>
    </div>

    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label">Staff</label>
        <select name="staff_id" class="form-select">
          <option value="0">All Staff</option>
          <?php if ($staff_list && $staff_list->num_rows > 0): ?>
            <?php while ($staff = $staff_list->fetch_assoc()): ?>
              <option value="<?= $staff['user_id'] ?>" <?= $staff_filter == $staff['user_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?>
              </option>
            <?php endwhile; ?>
          <?php endif; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
          <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>

      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-funnel"></i>
          Filter
        </button>

        <a href="leave_requests.php" class="btn btn-outline-secondary">
          Reset
        </a>
      </div>
    </form>
  </div>

  <div class="content-card">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Leave Request Records</h2>
        <p>Pending requests appear first.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-vcenter">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Type</th>
            <th>Dates</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Submitted</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($requests && $requests->num_rows > 0): ?>
            <?php while ($row = $requests->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status']);

                if ($status === 'approved') {
                    $badgeClass = 'badge-success';
                } elseif ($status === 'rejected') {
                    $badgeClass = 'badge-danger';
                } else {
                    $badgeClass = 'badge-warning';
                }
              ?>

              <tr>
                <td>
                  <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($row['email'] ?? '-') ?></small>
                </td>

                <td><?= htmlspecialchars($row['leave_type']) ?></td>

                <td>
                  <?= date('M d, Y', strtotime($row['start_date'])) ?>
                  -
                  <?= date('M d, Y', strtotime($row['end_date'])) ?>
                </td>

                <td style="max-width: 220px; white-space: normal;">
                  <?= htmlspecialchars($row['reason'] ?: '-') ?>
                </td>

                <td>
                  <span class="badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>

                <td style="max-width: 220px; white-space: normal;">
                  <?= htmlspecialchars($row['admin_remarks'] ?: '-') ?>
                </td>

                <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>

                <td>
                  <?php if ($row['status'] === 'Pending'): ?>
                    <form method="POST" action="actions/update_leave_request.php" class="mb-2">
                      <input type="hidden" name="leave_id" value="<?= $row['leave_id'] ?>">
                      <input type="hidden" name="status" value="Approved">
                      <input type="text" name="admin_remarks" class="form-control form-control-sm mb-1" placeholder="Remarks optional">
                      <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-check-circle"></i>
                        Approve
                      </button>
                    </form>

                    <form method="POST" action="actions/update_leave_request.php">
                      <input type="hidden" name="leave_id" value="<?= $row['leave_id'] ?>">
                      <input type="hidden" name="status" value="Rejected">
                      <input type="text" name="admin_remarks" class="form-control form-control-sm mb-1" placeholder="Reason optional">
                      <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="bi bi-x-circle"></i>
                        Reject
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted small">No action</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <i class="bi bi-calendar-x"></i>
                  <h5>No leave requests found</h5>
                  <p>No leave requests match your filters.</p>
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