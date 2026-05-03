<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$schedule_id = intval($_GET['id'] ?? 0);

if ($schedule_id <= 0) {
    header("Location: ../schedules.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM staff_schedules WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();

header("Location: ../schedules.php?deleted=1");
exit;