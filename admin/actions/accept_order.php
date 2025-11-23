<?php
// admin/actions/accept_order.php
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
  exit;
}

// 1. Correct database connection
require_once __DIR__ . '/../../includes/db_connect.php'; 

if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'DB connection ($conn) not found.']);
  exit;
}

// 2. Get POST data
$orderId       = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$paymentStatus = (isset($_POST['payment_status']) && $_POST['payment_status'] === 'unpaid') ? 'pending' : 'paid';
$method        = ($paymentStatus === 'paid') ? ($_POST['payment_method'] ?? 'cash') : 'cash';
$reference     = ($paymentStatus === 'paid') ? ($_POST['reference_no'] ?? null) : null;
$amount        = isset($_POST['amount']) ? preg_replace('/[^\d.]/', '', $_POST['amount']) : null;


if ($orderId <= 0) {
  http_response_code(422);
  echo json_encode(['status' => 'error', 'message' => 'Invalid order_id']);
  exit;
}

// 3. --- CORRECTED SCHEMA FOR online_food_ordering_db.sql ---
$table        = 'orders';
$colId        = 'order_id';       
$colStatus    = 'status';
$nextStatus   = 'preparing'; 
$curStatus    = 'pending';   

$payTable     = 'order_payment_details';
$colPayStat   = 'payment_status';
$colPayMethod = 'payment_method';
$colPayRef    = 'gcash_reference';
$colPayAmount = 'amount_paid';
$colPayDate   = 'paid_at';


// 4. Start Transaction
$conn->begin_transaction();
try {
  // Lock row & verify status is 'pending'
  $stmt = $conn->prepare("SELECT $colStatus FROM $table WHERE $colId = ? FOR UPDATE");
  $stmt->bind_param('i', $orderId);
  $stmt->execute();
  $result = $stmt->get_result();
  $current_status_row = $result->fetch_assoc();
  $stmt->close();

  if (!$current_status_row) {
    throw new Exception('Order not found');
  }
  if ($current_status_row[$colStatus] !== $curStatus) {
    throw new Exception('Order is not in pending status');
  }

  // Update the orders table
  $sql = "UPDATE $table SET $colStatus = ? WHERE $colId = ? AND $colStatus = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sis', $nextStatus, $orderId, $curStatus);
  $stmt->execute();

  if ($stmt->affected_rows < 1) {
    throw new Exception('Update failed or order was already processed');
  }
  $stmt->close();

  // Update the order_payment_details table
  $sql_pay = "UPDATE $payTable 
              SET $colPayStat = ?, $colPayMethod = ?, $colPayRef = ?, $colPayAmount = ?, $colPayDate = ?
              WHERE $colId = ?";
              
  $paid_at_time = ($paymentStatus === 'paid') ? date("Y-m-d H:i:s") : null;

  $stmt_pay = $conn->prepare($sql_pay);
  $stmt_pay->bind_param('sssdsi', $paymentStatus, $method, $reference, $amount, $paid_at_time, $orderId);
  $stmt_pay->execute();
  $stmt_pay->close();

  $conn->commit();

  // --- SEND EMAIL NOTIFICATION (ORDER ACCEPTED) ---
  try {
    // Fetch customer details
    $custQuery = "SELECT o.order_number, cd.customer_email, cd.customer_first_name 
                  FROM orders o
                  JOIN order_customer_details cd ON o.order_id = cd.order_id
                  WHERE o.order_id = ?";
    $stmtCust = $conn->prepare($custQuery);
    $stmtCust->bind_param('i', $orderId);
    $stmtCust->execute();
    $custRes = $stmtCust->get_result()->fetch_assoc();
    $stmtCust->close();

    if ($custRes && !empty($custRes['customer_email'])) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bentesaislomi.26@gmail.com'; 
        $mail->Password   = 'gqzk qvow jxee kkns'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('bentesaislomi.26@gmail.com', 'Bente Sais Lomi House');
        $mail->addAddress($custRes['customer_email'], $custRes['customer_first_name']);

        $mail->isHTML(true);
        $mail->Subject = "Order Accepted - " . $custRes['order_number'];
        $mail->Body    = "
            <h3>Order Accepted!</h3>
            <p>Hi " . htmlspecialchars($custRes['customer_first_name']) . ",</p>
            <p>Your order <strong>" . $custRes['order_number'] . "</strong> has been accepted by our staff and is now being prepared.</p>
            <p>Thank you for choosing Bente Sais Lomi House!</p>
        ";
        $mail->send();
    }
  } catch (Exception $mailEx) {
      // Log error but don't break the response
      error_log("Mail Error: " . $mailEx->getMessage());
  }
  // ------------------------------------------------

  echo json_encode(['status' => 'ok', 'next_status' => $nextStatus]);

} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>