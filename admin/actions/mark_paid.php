<?php
session_start();
require_once '../../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$payroll_id = intval($_GET['id'] ?? 0);

if ($payroll_id <= 0) {
    header("Location: ../payroll.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$paid_at = date('Y-m-d H:i:s');
$payment_method = 'Cash';

$stmt = $conn->prepare("
    UPDATE staff_payroll 
    SET status = 'Paid',
        paid_at = ?,
        paid_by = ?,
        payment_method = ?
    WHERE payroll_id = ?
");
$stmt->bind_param("sisi", $paid_at, $admin_id, $payment_method, $payroll_id);
$stmt->execute();

header("Location: ../payroll.php");
exit;