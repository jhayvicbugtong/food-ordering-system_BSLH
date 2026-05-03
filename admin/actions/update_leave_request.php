<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../leave_requests.php");
    exit;
}

$leave_id = intval($_POST['leave_id'] ?? 0);
$status = $_POST['status'] ?? '';
$admin_remarks = trim($_POST['admin_remarks'] ?? '');

$allowed_statuses = ['Approved', 'Rejected'];

if ($leave_id <= 0 || !in_array($status, $allowed_statuses)) {
    header("Location: ../leave_requests.php?error=invalid");
    exit;
}

$stmt = $conn->prepare("
    UPDATE staff_leave_requests
    SET status = ?, admin_remarks = ?
    WHERE leave_id = ?
");
$stmt->bind_param("ssi", $status, $admin_remarks, $leave_id);

if ($stmt->execute()) {
    header("Location: ../leave_requests.php?success=1");
} else {
    header("Location: ../leave_requests.php?error=update");
}

exit;