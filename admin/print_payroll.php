<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

$where = "";
$params = [];
$types = "";

if ($staff_id > 0) {
    $where = "WHERE p.staff_id = ?";
    $params[] = $staff_id;
    $types .= "i";
}

$sql = "
    SELECT 
        p.*,
        u.first_name,
        u.last_name,
        u.email
    FROM staff_payroll p
    INNER JOIN users u ON p.staff_id = u.user_id
    $where
    ORDER BY u.first_name ASC, p.created_at DESC
";

$stmt = $conn->prepare($sql);

if ($staff_id > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$payrolls = $stmt->get_result();

$title = $staff_id > 0 ? "Staff Payroll Report" : "All Staff Payroll Report";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>

  <style>
    :root {
      --avocado-green: #5cfa63;
      --dark: #212529;
      --muted: #6c757d;
      --border: #dee2e6;
    }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: #f5f7fa;
      color: #212529;
      margin: 0;
      padding: 30px;
    }

    .print-wrapper {
      max-width: 1100px;
      margin: 0 auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    .print-header {
      background: #212529;
      color: #fff;
      padding: 24px 30px;
      border-bottom: 4px solid var(--avocado-green);
    }

    .print-header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
    }

    .print-header p {
      margin: 6px 0 0;
      font-size: 13px;
      color: #ced4da;
    }

    .print-info {
      padding: 20px 30px;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }

    .info-box small {
      display: block;
      color: var(--muted);
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .info-box strong {
      font-size: 15px;
    }

    .print-body {
      padding: 25px 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th {
      background: #f1f3f5;
      color: #495057;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: .04em;
      padding: 10px;
      border: 1px solid var(--border);
      text-align: left;
    }

    td {
      padding: 10px;
      border: 1px solid var(--border);
      vertical-align: middle;
    }

    .amount {
      font-weight: 700;
    }

    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
    }

    .badge-paid {
      background: #d3ffe0;
      color: #0f5132;
    }

    .badge-pending {
      background: #fff3cd;
      color: #664d03;
    }

    .summary {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
    }

    .summary-box {
      width: 320px;
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 14px;
      border-bottom: 1px solid var(--border);
      font-size: 14px;
    }

    .summary-row:last-child {
      border-bottom: none;
      background: #f8f9fa;
      font-weight: 800;
    }

    .print-actions {
      max-width: 1100px;
      margin: 0 auto 16px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn {
      border: none;
      border-radius: 6px;
      padding: 10px 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      font-size: 13px;
    }

    .btn-print {
      background: var(--avocado-green);
      color: #000;
    }

    .btn-back {
      background: #212529;
      color: #fff;
    }

    .empty {
      text-align: center;
      padding: 40px;
      color: var(--muted);
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .print-actions {
        display: none;
      }

      .print-wrapper {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
      }

      .print-header {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>

<body>

<div class="print-actions">
  <a href="payroll.php" class="btn btn-back">Back</a>
  <button onclick="window.print()" class="btn btn-print">Print Payroll</button>
</div>

<div class="print-wrapper">
  <div class="print-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p>Online Food Ordering System | Payroll Report</p>
  </div>

  <div class="print-info">
    <div class="info-box">
      <small>Generated Date</small>
      <strong><?= date('F d, Y h:i A') ?></strong>
    </div>

    <div class="info-box">
      <small>Report Type</small>
      <strong><?= $staff_id > 0 ? 'Specific Staff' : 'All Staff' ?></strong>
    </div>
  </div>

  <div class="print-body">
    <?php
      $total_hours_sum = 0;
      $gross_sum = 0;
      $deductions_sum = 0;
      $net_sum = 0;
    ?>

    <?php if ($payrolls && $payrolls->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Staff</th>
            <th>Email</th>
            <th>Period</th>
            <th>Hours</th>
            <th>Rate</th>
            <th>Gross</th>
            <th>Deductions</th>
            <th>Net Pay</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php while ($row = $payrolls->fetch_assoc()): ?>
            <?php
              $total_hours_sum += (float)$row['total_hours'];
              $gross_sum += (float)$row['gross_pay'];
              $deductions_sum += (float)$row['deductions'];
              $net_sum += (float)$row['net_pay'];

              $statusClass = strtolower($row['status']) === 'paid' ? 'badge-paid' : 'badge-pending';
            ?>

            <tr>
              <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
              <td>
                <?= date('M d, Y', strtotime($row['period_start'])) ?>
                -
                <?= date('M d, Y', strtotime($row['period_end'])) ?>
              </td>
              <td><?= number_format((float)$row['total_hours'], 2) ?></td>
              <td>₱<?= number_format((float)$row['hourly_rate'], 2) ?></td>
              <td>₱<?= number_format((float)$row['gross_pay'], 2) ?></td>
              <td>₱<?= number_format((float)$row['deductions'], 2) ?></td>
              <td class="amount">₱<?= number_format((float)$row['net_pay'], 2) ?></td>
              <td>
                <span class="badge <?= $statusClass ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <div class="summary">
        <div class="summary-box">
          <div class="summary-row">
            <span>Total Hours</span>
            <strong><?= number_format($total_hours_sum, 2) ?> hrs</strong>
          </div>
          <div class="summary-row">
            <span>Total Gross</span>
            <strong>₱<?= number_format($gross_sum, 2) ?></strong>
          </div>
          <div class="summary-row">
            <span>Total Deductions</span>
            <strong>₱<?= number_format($deductions_sum, 2) ?></strong>
          </div>
          <div class="summary-row">
            <span>Total Net Pay</span>
            <strong>₱<?= number_format($net_sum, 2) ?></strong>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="empty">
        <h3>No payroll records found</h3>
        <p>There are no payroll records available for this report.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>