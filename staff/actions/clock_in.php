<?php
session_start();
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$staff_id = $_SESSION['user_id'];
$date = date('Y-m-d');
$time = date('H:i:s');

$grace_minutes = 10;

// Check schedule today
$schedule_stmt = $conn->prepare("
    SELECT shift_start, shift_end, shift_name
    FROM staff_schedules
    WHERE staff_id = ? AND work_date = ?
    LIMIT 1
");
$schedule_stmt->bind_param("is", $staff_id, $date);
$schedule_stmt->execute();
$schedule = $schedule_stmt->get_result()->fetch_assoc();

if (!$schedule) {
    echo json_encode([
        'success' => false,
        'message' => 'You have no assigned schedule today. Please contact admin.'
    ]);
    exit;
}

// Prevent clock-in before shift day record duplicate
$check = $conn->prepare("
    SELECT attendance_id 
    FROM staff_attendance 
    WHERE staff_id = ? AND attendance_date = ?
");
$check->bind_param("is", $staff_id, $date);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'You already clocked in today.'
    ]);
    exit;
}

// Late calculation using schedule + grace period
$shift_start_timestamp = strtotime($date . ' ' . $schedule['shift_start']);
$grace_timestamp = strtotime("+{$grace_minutes} minutes", $shift_start_timestamp);
$current_timestamp = strtotime($date . ' ' . $time);

$status = ($current_timestamp > $grace_timestamp) ? 'Late' : 'Present';

$stmt = $conn->prepare("
    INSERT INTO staff_attendance 
    (staff_id, attendance_date, time_in, status) 
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("isss", $staff_id, $date, $time, $status);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Clock in successful. Status: ' . $status
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Clock in failed.'
    ]);
}
?>