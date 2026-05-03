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

// Check attendance today
$stmt = $conn->prepare("
    SELECT attendance_id, time_in, time_out 
    FROM staff_attendance 
    WHERE staff_id = ? AND attendance_date = ?
    LIMIT 1
");
$stmt->bind_param("is", $staff_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'You need to clock in first.'
    ]);
    exit;
}

$row = $result->fetch_assoc();

if (!empty($row['time_out'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You already clocked out today.'
    ]);
    exit;
}

$time_in = strtotime($date . ' ' . $row['time_in']);
$time_out = strtotime($date . ' ' . $time);

if ($time_out <= $time_in) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid clock-out time.'
    ]);
    exit;
}

$total_hours = round(($time_out - $time_in) / 3600, 2);

$update = $conn->prepare("
    UPDATE staff_attendance 
    SET time_out = ?, total_hours = ? 
    WHERE attendance_id = ?
");
$update->bind_param("sdi", $time, $total_hours, $row['attendance_id']);

if ($update->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Clock out successful. Total hours: ' . number_format($total_hours, 2)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Clock out failed.'
    ]);
}
?>