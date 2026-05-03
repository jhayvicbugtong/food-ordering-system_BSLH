<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$today_stmt = $conn->prepare("
    SELECT * 
    FROM staff_attendance 
    WHERE staff_id = ? AND attendance_date = ?
    LIMIT 1
");
$today_stmt->bind_param("is", $staff_id, $today);
$today_stmt->execute();
$today_attendance = $today_stmt->get_result()->fetch_assoc();

$schedule_stmt = $conn->prepare("
    SELECT *
    FROM staff_schedules
    WHERE staff_id = ? AND work_date = ?
    LIMIT 1
");
$schedule_stmt->bind_param("is", $staff_id, $today);
$schedule_stmt->execute();
$today_schedule = $schedule_stmt->get_result()->fetch_assoc();

$history_stmt = $conn->prepare("
    SELECT 
      a.*,
      s.shift_start,
      s.shift_end,
      s.shift_name
    FROM staff_attendance a
    LEFT JOIN staff_schedules s 
      ON a.staff_id = s.staff_id 
      AND a.attendance_date = s.work_date
    WHERE a.staff_id = ? 
    ORDER BY a.attendance_date DESC 
    LIMIT 30
");
$history_stmt->bind_param("i", $staff_id);
$history_stmt->execute();
$history = $history_stmt->get_result();
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

.attendance-stat {
  padding: 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ecfdf3, #ffffff);
  border: 1px solid rgba(92, 250, 99, 0.35);
  box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
  height: 100%;
}

.attendance-stat h5 {
  font-size: 0.78rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 6px;
}

.attendance-stat .value {
  font-size: 1.35rem;
  font-weight: 700;
  color: #111827;
}

.attendance-stat .hint {
  font-size: 0.8rem;
  color: #9ca3af;
}

.quick-btn {
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 8px 14px;
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

.status-present {
  background: #dcfce7;
  color: #166534;
}

.status-late {
  background: #fef3c7;
  color: #92400e;
}

.status-absent {
  background: #fee2e2;
  color: #b91c1c;
}

.status-none {
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
        <h2>My Attendance</h2>
        <p>Track your daily schedule, time-in, time-out, and attendance history.</p>
      </div>
      <div class="text-muted small">
        <?= date('F d, Y') ?>
      </div>
    </div>

    <?php if (!$today_schedule): ?>
      <div class="alert alert-warning">
        <strong>No schedule today.</strong> You cannot clock in until admin assigns a schedule for today.
      </div>
    <?php else: ?>
      <div class="alert alert-success">
        <strong>Today's Schedule:</strong>
        <?= htmlspecialchars($today_schedule['shift_name'] ?: 'Regular Shift') ?>
        —
        <?= date('h:i A', strtotime($today_schedule['shift_start'])) ?>
        to
        <?= date('h:i A', strtotime($today_schedule['shift_end'])) ?>
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="attendance-stat">
          <h5>Time In</h5>
          <div class="value">
            <?= !empty($today_attendance['time_in']) ? date('h:i A', strtotime($today_attendance['time_in'])) : '--:--' ?>
          </div>
          <div class="hint">Today clock-in</div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="attendance-stat">
          <h5>Time Out</h5>
          <div class="value">
            <?= !empty($today_attendance['time_out']) ? date('h:i A', strtotime($today_attendance['time_out'])) : '--:--' ?>
          </div>
          <div class="hint">Today clock-out</div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="attendance-stat">
          <h5>Total Hours</h5>
          <div class="value">
            <?= number_format((float)($today_attendance['total_hours'] ?? 0), 2) ?> hrs
          </div>
          <div class="hint">Worked today</div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="attendance-stat">
          <h5>Status</h5>
          <div class="value">
            <?= htmlspecialchars($today_attendance['status'] ?? 'No Record') ?>
          </div>
          <div class="hint">Attendance status</div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <button 
        type="button" 
        class="btn btn-success quick-btn" 
        onclick="clockIn()"
        <?= !$today_schedule || !empty($today_attendance['time_in']) ? 'disabled' : '' ?>>
        <i class="bi bi-box-arrow-in-right me-1"></i>
        Clock In
      </button>

      <button 
        type="button" 
        class="btn btn-dark quick-btn" 
        onclick="clockOut()"
        <?= empty($today_attendance['time_in']) || !empty($today_attendance['time_out']) ? 'disabled' : '' ?>>
        <i class="bi bi-box-arrow-right me-1"></i>
        Clock Out
      </button>

      <a href="my_schedule.php" class="btn btn-outline-success quick-btn">
        <i class="bi bi-calendar-week me-1"></i>
        View Schedule
      </a>

      <a href="index.php" class="btn btn-outline-dark quick-btn">
        <i class="bi bi-speedometer2 me-1"></i>
        Back to Dashboard
      </a>
    </div>
  </div>

  <div class="content-card">
    <div class="content-card-header">
      <div>
        <h2>Attendance History</h2>
        <p>Your latest 30 attendance records with assigned schedules.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover staff-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Schedule</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Total Hours</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($history && $history->num_rows > 0): ?>
            <?php while ($row = $history->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status'] ?? 'none');

                if ($status === 'present') {
                    $badgeClass = 'status-present';
                } elseif ($status === 'late') {
                    $badgeClass = 'status-late';
                } elseif ($status === 'absent') {
                    $badgeClass = 'status-absent';
                } else {
                    $badgeClass = 'status-none';
                }
              ?>
              <tr>
                <td><?= date('M d, Y', strtotime($row['attendance_date'])) ?></td>

                <td>
                  <?php if (!empty($row['shift_start'])): ?>
                    <?= htmlspecialchars($row['shift_name'] ?: 'Regular Shift') ?><br>
                    <small class="text-muted">
                      <?= date('h:i A', strtotime($row['shift_start'])) ?>
                      -
                      <?= date('h:i A', strtotime($row['shift_end'])) ?>
                    </small>
                  <?php else: ?>
                    <span class="text-muted">No schedule</span>
                  <?php endif; ?>
                </td>

                <td><?= !empty($row['time_in']) ? date('h:i A', strtotime($row['time_in'])) : '-' ?></td>
                <td><?= !empty($row['time_out']) ? date('h:i A', strtotime($row['time_out'])) : '-' ?></td>
                <td><?= number_format((float)$row['total_hours'], 2) ?> hrs</td>

                <td>
                  <span class="status-badge <?= $badgeClass ?>">
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
                  <h5>No attendance records yet</h5>
                  <p>Your attendance history will appear here.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script>
function clockIn() {
  fetch('actions/clock_in.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: data.success ? 'success' : 'warning',
          title: data.success ? 'Success' : 'Notice',
          text: data.message
        }).then(() => {
          if (data.success) location.reload();
        });
      } else {
        alert(data.message);
        if (data.success) location.reload();
      }
    })
    .catch(() => {
      alert('Unable to clock in.');
    });
}

function clockOut() {
  fetch('actions/clock_out.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: data.success ? 'success' : 'warning',
          title: data.success ? 'Success' : 'Notice',
          text: data.message
        }).then(() => {
          if (data.success) location.reload();
        });
      } else {
        alert(data.message);
        if (data.success) location.reload();
      }
    })
    .catch(() => {
      alert('Unable to clock out.');
    });
}
</script>

<?php include 'includes/footer.php'; ?>