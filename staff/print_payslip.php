<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: payroll.php");
    exit;
}

$payroll_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT 
        p.*,
        u.first_name,
        u.last_name,
        u.email
    FROM staff_payroll p
    INNER JOIN users u ON p.staff_id = u.user_id
    WHERE p.payroll_id = ?
    AND p.staff_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $payroll_id, $staff_id);
$stmt->execute();
$payslip = $stmt->get_result()->fetch_assoc();

if (!$payslip) {
    header("Location: payroll.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payslip</title>

  <style>
    :root {
      --green: #5cfa63;
      --dark: #212529;
      --muted: #6c757d;
      --border: #dee2e6;
    }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 30px;
      color: #212529;
    }

    .actions {
      max-width: 800px;
      margin: 0 auto 16px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn {
      border: none;
      border-radius: 8px;
      padding: 10px 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      font-size: 13px;
    }

    .btn-print {
      background: var(--green);
      color: #000;
    }

    .btn-back {
      background: var(--dark);
      color: #fff;
    }

    .payslip {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    }

    .header {
      background: var(--dark);
      color: #fff;
      padding: 24px 30px;
      border-bottom: 5px solid var(--green);
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
    }

    .header p {
      margin: 6px 0 0;
      color: #ced4da;
      font-size: 13px;
    }

    .section {
      padding: 24px 30px;
      border-bottom: 1px solid var(--border);
    }

    .section-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 14px;
      color: #212529;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px 24px;
    }

    .info small {
      display: block;
      color: var(--muted);
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .info strong {
      font-size: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th, td {
      border: 1px solid var(--border);
      padding: 12px;
      text-align: left;
    }

    th {
      background: #f1f3f5;
      color: #495057;
      text-transform: uppercase;
      font-size: 12px;
    }

    .amount {
      font-weight: 700;
      text-align: right;
    }

    .net-pay {
      background: #f8f9fa;
      font-size: 18px;
      font-weight: 800;
    }

    .badge {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    .paid {
      background: #d3ffe0;
      color: #0f5132;
    }

    .pending {
      background: #fff3cd;
      color: #664d03;
    }

    .footer-note {
      padding: 18px 30px;
      font-size: 12px;
      color: var(--muted);
      text-align: center;
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .actions {
        display: none;
      }

      .payslip {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
      }

      .header,
      th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>

<body>

<div class="actions">
  <a href="payroll.php" class="btn btn-back">Back</a>
  <button onclick="window.print()" class="btn btn-print">Print Payslip</button>
</div>

<div class="payslip">
  <div class="header">
    <h1>Employee Payslip</h1>
    <p>Online Food Ordering System</p>
  </div>

  <div class="section">
    <div class="section-title">Employee Information</div>

    <div class="grid">
      <div class="info">
        <small>Employee Name</small>
        <strong><?= htmlspecialchars($payslip['first_name'] . ' ' . $payslip['last_name']) ?></strong>
      </div>

      <div class="info">
        <small>Email</small>
        <strong><?= htmlspecialchars($payslip['email'] ?? '-') ?></strong>
      </div>

      <div class="info">
        <small>Pay Period</small>
        <strong>
          <?= date('M d, Y', strtotime($payslip['period_start'])) ?>
          -
          <?= date('M d, Y', strtotime($payslip['period_end'])) ?>
        </strong>
      </div>

      <div class="info">
        <small>Status</small>
        <?php $statusClass = strtolower($payslip['status']) === 'paid' ? 'paid' : 'pending'; ?>
        <span class="badge <?= $statusClass ?>">
          <?= htmlspecialchars($payslip['status']) ?>
        </span>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Payroll Details</div>

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th class="amount">Amount</th>
        </tr>
      </thead>

      <tbody>
        <tr>
          <td>Total Hours</td>
          <td class="amount"><?= number_format((float)$payslip['total_hours'], 2) ?> hrs</td>
        </tr>

        <tr>
          <td>Hourly Rate</td>
          <td class="amount">₱<?= number_format((float)$payslip['hourly_rate'], 2) ?></td>
        </tr>

        <tr>
          <td>Gross Pay</td>
          <td class="amount">₱<?= number_format((float)$payslip['gross_pay'], 2) ?></td>
        </tr>

        <tr>
          <td>Deductions</td>
          <td class="amount">₱<?= number_format((float)$payslip['deductions'], 2) ?></td>
        </tr>

        <tr class="net-pay">
          <td>Net Pay</td>
          <td class="amount">₱<?= number_format((float)$payslip['net_pay'], 2) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="footer-note">
    This payslip is system-generated and valid for payroll reference.
  </div>
</div>

</body>
</html>