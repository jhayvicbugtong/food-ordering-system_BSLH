<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../payroll.php");
    exit;
}

$staff_id = intval($_POST['staff_id']);
$period_start = $_POST['period_start'];
$period_end = $_POST['period_end'];
$hourly_rate = floatval($_POST['hourly_rate']);
$deductions = floatval($_POST['deductions']);

$stmt = $conn->prepare("
    SELECT SUM(total_hours) AS total_hours 
    FROM staff_attendance 
    WHERE staff_id = ? 
    AND attendance_date BETWEEN ? AND ?
");
$stmt->bind_param("iss", $staff_id, $period_start, $period_end);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$total_hours = floatval($result['total_hours'] ?? 0);
$gross_pay = $total_hours * $hourly_rate;
$net_pay = $gross_pay - $deductions;

$insert = $conn->prepare("
    INSERT INTO staff_payroll 
    (staff_id, period_start, period_end, hourly_rate, total_hours, gross_pay, deductions, net_pay, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
");

$insert->bind_param(
    "issddddd",
    $staff_id,
    $period_start,
    $period_end,
    $hourly_rate,
    $total_hours,
    $gross_pay,
    $deductions,
    $net_pay
);

$insert->execute();

header("Location: ../payroll.php");
exit;
?>