<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_filter = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$date_filter = $_GET['date'] ?? '';

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
    $where[] = "s.staff_id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}

if (!empty($date_filter)) {
    $where[] = "s.work_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$where_sql = "";
if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$query = "
    SELECT
      s.*,
      u.first_name,
      u.last_name
    FROM staff_schedules s
    INNER JOIN users u ON s.staff_id = u.user_id
    $where_sql
    ORDER BY s.work_date DESC, s.shift_start ASC
";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$schedules = $stmt->get_result();

$summary = $conn->query("
    SELECT
      COUNT(*) AS total_schedules,
      SUM(CASE WHEN work_date = CURDATE() THEN 1 ELSE 0 END) AS today_schedules,
      SUM(CASE WHEN work_date > CURDATE() THEN 1 ELSE 0 END) AS upcoming_schedules
    FROM staff_schedules
")->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">

<div class="main-content">

  <div class="content-card mb-4">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Staff Schedule</h2>
        <p>Create, update, and manage staff work schedules.</p>
      </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">Schedule saved successfully.</div>
    <?php elseif (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">Schedule deleted successfully.</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-danger">Unable to save schedule. Please check the details.</div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card">
          <h5>Total Schedules</h5>
          <div class="value"><?= number_format((int)($summary['total_schedules'] ?? 0)) ?></div>
          <div class="hint">All created schedules</div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="stat-card">
          <h5>Today</h5>
          <div class="value"><?= number_format((int)($summary['today_schedules'] ?? 0)) ?></div>
          <div class="hint">Schedules today</div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="stat-card">
          <h5>Upcoming</h5>
          <div class="value"><?= number_format((int)($summary['upcoming_schedules'] ?? 0)) ?></div>
          <div class="hint">Future schedules</div>
        </div>
      </div>
    </div>

    <form method="POST" action="actions/save_schedule.php" class="payroll-form">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Staff</label>
          <select name="staff_id" class="form-select" required>
            <option value="">Select Staff</option>
            <?php
              $staff_form = $conn->query("
                SELECT user_id, first_name, last_name
                FROM users
                WHERE role = 'staff' AND is_active = 1
                ORDER BY first_name ASC
              ");
            ?>
            <?php if ($staff_form && $staff_form->num_rows > 0): ?>
              <?php while ($staff = $staff_form->fetch_assoc()): ?>
                <option value="<?= $staff['user_id'] ?>">
                  <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?>
                </option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Work Date</label>
          <input type="date" name="work_date" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Shift Name</label>
          <input type="text" name="shift_name" class="form-control" placeholder="Morning Shift">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Shift Start</label>
          <input type="time" name="shift_start" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Shift End</label>
          <input type="time" name="shift_end" class="form-control" required>
        </div>
      </div>

      <button type="submit" class="btn btn-success">
        <i class="bi bi-calendar-plus me-1"></i>
        Save Schedule
      </button>
    </form>
  </div>

  <div class="content-card mb-4">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Filter Schedules</h2>
        <p>Filter schedules by staff or work date.</p>
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
        <label class="form-label">Work Date</label>
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
      </div>

      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-funnel"></i>
          Filter
        </button>

        <a href="schedules.php" class="btn btn-outline-secondary">
          Reset
        </a>
      </div>
    </form>
  </div>

  <div class="content-card">
    <div class="content-card-header mb-3">
      <div class="left">
        <h2>Schedule Records</h2>
        <p>Latest staff schedules.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-vcenter">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Date</th>
            <th>Shift Name</th>
            <th>Shift Start</th>
            <th>Shift End</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($schedules && $schedules->num_rows > 0): ?>
            <?php while ($row = $schedules->fetch_assoc()): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                </td>

                <td><?= date('M d, Y', strtotime($row['work_date'])) ?></td>

                <td><?= htmlspecialchars($row['shift_name'] ?: 'Regular Shift') ?></td>

                <td><?= date('h:i A', strtotime($row['shift_start'])) ?></td>

                <td><?= date('h:i A', strtotime($row['shift_end'])) ?></td>

                <td>
                  <a href="actions/delete_schedule.php?id=<?= $row['schedule_id'] ?>" 
                     class="btn btn-sm btn-danger">
                    <i class="bi bi-trash"></i>
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-calendar-week"></i>
                  <h5>No schedules found</h5>
                  <p>Staff schedules will appear here once created.</p>
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