<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../leave_requests.php");
    exit;
}

$staff_id = $_SESSION['user_id'];
$leave_type = $_POST['leave_type'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$reason = trim($_POST['reason'] ?? '');

$allowed_types = ['Sick Leave', 'Vacation Leave', 'Emergency Leave', 'Other'];

if (
    empty($leave_type) ||
    empty($start_date) ||
    empty($end_date) ||
    !in_array($leave_type, $allowed_types)
) {
    header("Location: ../leave_requests.php?error=invalid");
    exit;
}

if (strtotime($end_date) < strtotime($start_date)) {
    header("Location: ../leave_requests.php?error=date");
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO staff_leave_requests 
    (staff_id, leave_type, start_date, end_date, reason, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
$stmt->bind_param("issss", $staff_id, $leave_type, $start_date, $end_date, $reason);

if ($stmt->execute()) {
    header("Location: ../leave_requests.php?success=1");
} else {
    header("Location: ../leave_requests.php?error=save");
}

exit;