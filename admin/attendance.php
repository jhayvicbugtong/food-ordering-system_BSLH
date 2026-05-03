<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_filter = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

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
    $where[] = "a.staff_id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}

if (!empty($date_filter)) {
    $where[] = "a.attendance_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $where[] = "a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_sql = "";
if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$query = "
    SELECT 
        a.*, 
        u.first_name, 
        u.last_name
    FROM staff_attendance a
    INNER JOIN users u ON a.staff_id = u.user_id
    $where_sql
    ORDER BY a.attendance_date DESC, a.time_in DESC
";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$attendance = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">

<div class="main-content">
  <div class="content-card mb-4">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Staff Attendance</h2>
        <p>Monitor and filter staff time-in and time-out records.</p>
      </div>
    </div>

    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
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

      <div class="col-md-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="Present" <?= $status_filter === 'Present' ? 'selected' : '' ?>>Present</option>
          <option value="Late" <?= $status_filter === 'Late' ? 'selected' : '' ?>>Late</option>
          <option value="Absent" <?= $status_filter === 'Absent' ? 'selected' : '' ?>>Absent</option>
        </select>
      </div>

      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-funnel"></i>
          Filter
        </button>
      </div>

      <div class="col-md-12">
        <a href="attendance.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-x-circle"></i>
          Reset Filters
        </a>
      </div>
    </form>
  </div>

  <div class="content-card">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Attendance Records</h2>
        <p>
          Showing records
          <?php if (!empty($date_filter)): ?>
            for <?= date('M d, Y', strtotime($date_filter)) ?>
          <?php endif; ?>
        </p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-vcenter">
        <thead>
          <tr>
            <th>Staff Name</th>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Total Hours</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($attendance && $attendance->num_rows > 0): ?>
            <?php while ($row = $attendance->fetch_assoc()): ?>
              <tr>
                <td>
                  <strong>
                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                  </strong>
                </td>

                <td>
                  <?= date('M d, Y', strtotime($row['attendance_date'])) ?>
                </td>

                <td>
                  <?= !empty($row['time_in']) ? date('h:i A', strtotime($row['time_in'])) : '-' ?>
                </td>

                <td>
                  <?= !empty($row['time_out']) ? date('h:i A', strtotime($row['time_out'])) : '-' ?>
                </td>

                <td>
                  <?= number_format((float)$row['total_hours'], 2) ?> hrs
                </td>

                <td>
                  <?php
                    $status = strtolower($row['status']);

                    if ($status === 'present') {
                        $badgeClass = 'badge-success';
                    } elseif ($status === 'late') {
                        $badgeClass = 'badge-warning';
                    } else {
                        $badgeClass = 'badge-danger';
                    }
                  ?>

                  <span class="badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-calendar-x"></i>
                  <h5>No attendance records found</h5>
                  <p>No records match your selected filters.</p>
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