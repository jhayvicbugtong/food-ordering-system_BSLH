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
    FROM staff_schedules
    WHERE staff_id = ?
    ORDER BY work_date DESC, shift_start ASC
    LIMIT 30
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$schedules = $stmt->get_result();

$today = date('Y-m-d');

$today_stmt = $conn->prepare("
    SELECT *
    FROM staff_schedules
    WHERE staff_id = ?
    AND work_date = ?
    LIMIT 1
");
$today_stmt->bind_param("is", $staff_id, $today);
$today_stmt->execute();
$today_schedule = $today_stmt->get_result()->fetch_assoc();

$upcoming_stmt = $conn->prepare("
    SELECT COUNT(*) AS upcoming_count
    FROM staff_schedules
    WHERE staff_id = ?
    AND work_date > CURDATE()
");
$upcoming_stmt->bind_param("i", $staff_id);
$upcoming_stmt->execute();
$upcoming = $upcoming_stmt->get_result()->fetch_assoc();
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

.schedule-stat {
  padding: 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ecfdf3, #ffffff);
  border: 1px solid rgba(92, 250, 99, 0.35);
  box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
  height: 100%;
}

.schedule-stat h5 {
  font-size: 0.78rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 6px;
}

.schedule-stat .value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
}

.staff-table {
  margin-bottom: 0;
  min-width: 700px;
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
        <h2>My Schedule</h2>
        <p>View your assigned work schedule.</p>
      </div>

      <a href="index.php" class="btn btn-outline-dark quick-btn">
        <i class="bi bi-speedometer2 me-1"></i>
        Back to Dashboard
      </a>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="schedule-stat">
          <h5>Today Schedule</h5>
          <div class="value">
            <?php if ($today_schedule): ?>
              <?= date('h:i A', strtotime($today_schedule['shift_start'])) ?>
              -
              <?= date('h:i A', strtotime($today_schedule['shift_end'])) ?>
            <?php else: ?>
              No schedule today
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="schedule-stat">
          <h5>Upcoming Schedules</h5>
          <div class="value">
            <?= number_format((int)($upcoming['upcoming_count'] ?? 0)) ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="content-card">
    <div class="content-card-header">
      <div>
        <h2>Schedule History</h2>
        <p>Your latest 30 schedules.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover staff-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Shift Name</th>
            <th>Start</th>
            <th>End</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($schedules && $schedules->num_rows > 0): ?>
            <?php while ($row = $schedules->fetch_assoc()): ?>
              <tr>
                <td><?= date('M d, Y', strtotime($row['work_date'])) ?></td>
                <td><?= htmlspecialchars($row['shift_name'] ?: 'Regular Shift') ?></td>
                <td><?= date('h:i A', strtotime($row['shift_start'])) ?></td>
                <td><?= date('h:i A', strtotime($row['shift_end'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">
                <div class="empty-state">
                  <i class="bi bi-calendar-week"></i>
                  <h5>No schedule found</h5>
                  <p>Your assigned schedules will appear here.</p>
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