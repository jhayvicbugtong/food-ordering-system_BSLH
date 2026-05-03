<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../schedules.php");
    exit;
}

$staff_id = intval($_POST['staff_id'] ?? 0);
$work_date = $_POST['work_date'] ?? '';
$shift_start = $_POST['shift_start'] ?? '';
$shift_end = $_POST['shift_end'] ?? '';
$shift_name = trim($_POST['shift_name'] ?? '');

if ($staff_id <= 0 || empty($work_date) || empty($shift_start) || empty($shift_end)) {
    header("Location: ../schedules.php?error=invalid");
    exit;
}

// ✅ Allow night shifts (only block same time)
if ($shift_start === $shift_end) {
    header("Location: ../schedules.php?error=time");
    exit;
}

// ✅ CHECK APPROVED LEAVE (IMPORTANT FIX)
$leave_stmt = $conn->prepare("
    SELECT leave_id
    FROM staff_leave_requests
    WHERE staff_id = ?
      AND status = 'Approved'
      AND ? BETWEEN start_date AND end_date
    LIMIT 1
");
$leave_stmt->bind_param("is", $staff_id, $work_date);
$leave_stmt->execute();
$leave_result = $leave_stmt->get_result();

if ($leave_result->num_rows > 0) {
    header("Location: ../schedules.php?error=leave");
    exit;
}

// ✅ SAVE SCHEDULE
$stmt = $conn->prepare("
    INSERT INTO staff_schedules 
    (staff_id, work_date, shift_start, shift_end, shift_name)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      shift_start = VALUES(shift_start),
      shift_end = VALUES(shift_end),
      shift_name = VALUES(shift_name)
");

$stmt->bind_param("issss", $staff_id, $work_date, $shift_start, $shift_end, $shift_name);

if ($stmt->execute()) {
    header("Location: ../schedules.php?success=1");
} else {
    header("Location: ../schedules.php?error=save");
}

exit;