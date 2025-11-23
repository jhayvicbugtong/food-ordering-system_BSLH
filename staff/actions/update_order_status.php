<?php
// staff/actions/update_order_status.php
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/SMTP.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Authenticate
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? null;

if ($user_id === 0 || !in_array($user_role, ['admin', 'staff', 'driver'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Get Input
$data = json_decode(file_get_contents('php://input'));
$order_id = $data->order_id ?? 0;
$new_status = $data->new_status ?? '';
$handler_id = $data->handler_id ?? null; 
$driver_id = $data->driver_id ?? null;

if ($order_id <= 0 || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// 3. Define Valid Status Transitions
$valid_statuses = [
    'pending', 'confirmed', 'preparing', 'ready', 
    'out_for_delivery', 'delivered', 'completed', 'cancelled'
];
if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

$conn->begin_transaction();
try {
    // --- FETCH CURRENT INFO (Including Subtotal, Delivery Fee, Tip) ---
    $info_stmt = $conn->prepare("
        SELECT 
            o.order_type, o.status, o.order_number, 
            o.subtotal, o.delivery_fee, o.tip_amount, o.total_amount,
            opd.payment_method, opd.payment_status 
        FROM orders o
        LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
        WHERE o.order_id = ?
    ");
    $info_stmt->bind_param('i', $order_id);
    $info_stmt->execute();
    $current_info = $info_stmt->get_result()->fetch_assoc();
    $info_stmt->close();

    // 4. Build SQL Query for Status Update
    $fields = ['status = ?'];
    $params = [$new_status];
    $types = 's';

    // Set timestamp
    $time_col = null;
    switch ($new_status) {
        case 'confirmed': $time_col = 'confirmed_at'; break;
        case 'preparing': $time_col = 'preparing_at'; break;
        case 'ready': $time_col = 'ready_at'; break;
        case 'out_for_delivery': $time_col = 'out_for_delivery_at'; break;
        case 'delivered': $time_col = 'delivered_at'; break;
        case 'cancelled': $time_col = 'cancelled_at'; break;
        case 'completed': $time_col = 'updated_at'; break; 
    }
    if ($time_col) {
        $fields[] = "$time_col = NOW()";
    }

    // Handler/Driver
    if ($handler_id && in_array($new_status, ['confirmed', 'preparing', 'ready'])) {
        $fields[] = 'handler_id = ?';
        $params[] = $handler_id;
        $types .= 'i';
    }
    if ($driver_id && $new_status === 'out_for_delivery') {
        $fields[] = 'driver_id = ?';
        $params[] = $driver_id;
        $types .= 'i';
    }

    $params[] = $order_id;
    $types .= 'i';

    $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }
    $stmt->close();

    // --- AUTOMATIC PAYMENT UPDATE FOR COD ---
    if (in_array($new_status, ['delivered', 'completed'])) {
        $method = $current_info['payment_method'] ?? '';
        $p_status = $current_info['payment_status'] ?? '';

        if ($method === 'cash' && $p_status !== 'paid') {
            $pay_upd = $conn->prepare("UPDATE order_payment_details SET payment_status = 'paid', paid_at = NOW() WHERE order_id = ?");
            $pay_upd->bind_param('i', $order_id);
            $pay_upd->execute();
            $pay_upd->close();
        }
    }

    $conn->commit();

    // --- ENHANCED EMAIL NOTIFICATIONS (AVOCADO THEME + FULL DETAILS) ---
    $sendEmail = false;
    $subject = "";
    $headline = "";
    $mainMessage = "";

    if (in_array($new_status, ['confirmed', 'preparing'])) {
        $sendEmail = true;
        $subject = "Order Accepted - " . $current_info['order_number'];
        $headline = "Order Accepted!";
        $mainMessage = "Your order has been accepted by our staff and is now being prepared.";
    }
    elseif ($new_status === 'ready' && $current_info['order_type'] === 'pickup') {
        $sendEmail = true;
        $subject = "Order Ready for Pickup - " . $current_info['order_number'];
        $headline = "Order Ready!";
        $mainMessage = "Your order is fresh and ready for pickup. Please proceed to the counter to claim it.";
    }
    elseif ($new_status === 'out_for_delivery') {
        $sendEmail = true;
        $subject = "Order Out for Delivery - " . $current_info['order_number'];
        $headline = "On the Way!";
        $mainMessage = "Your order is out for delivery. Our rider will be with you shortly!";
    }

    if ($sendEmail) {
        try {
            // 1. Get Customer Info
            $custStmt = $conn->prepare("SELECT customer_email, customer_first_name FROM order_customer_details WHERE order_id = ?");
            $custStmt->bind_param('i', $order_id);
            $custStmt->execute();
            $custData = $custStmt->get_result()->fetch_assoc();
            $custStmt->close();

            // 2. Get Order Items
            $itemsStmt = $conn->prepare("SELECT product_name, quantity, total_price FROM order_items WHERE order_id = ?");
            $itemsStmt->bind_param('i', $order_id);
            $itemsStmt->execute();
            $itemsRes = $itemsStmt->get_result();
            
            $itemsHtml = "";
            while($item = $itemsRes->fetch_assoc()) {
                $itemsHtml .= "
                <tr>
                    <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>" . $item['quantity'] . "x " . htmlspecialchars($item['product_name']) . "</td>
                    <td style='padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($item['total_price'], 2) . "</td>
                </tr>";
            }
            $itemsStmt->close();

            // 3. Build Breakdown HTML
            $breakdownHtml = "";
            
            // Subtotal
            $breakdownHtml .= "
            <tr>
                <td style='padding: 8px 0; padding-top: 15px; color: #777;'>Subtotal</td>
                <td style='padding: 8px 0; padding-top: 15px; text-align: right; color: #777;'>₱" . number_format($current_info['subtotal'], 2) . "</td>
            </tr>";

            // Delivery Fee
            if ($current_info['delivery_fee'] > 0) {
                $breakdownHtml .= "
                <tr>
                    <td style='padding: 4px 0; color: #777;'>Delivery Fee</td>
                    <td style='padding: 4px 0; text-align: right; color: #777;'>₱" . number_format($current_info['delivery_fee'], 2) . "</td>
                </tr>";
            }

            // Tip
            if ($current_info['tip_amount'] > 0) {
                $breakdownHtml .= "
                <tr>
                    <td style='padding: 4px 0; color: #777;'>Tip</td>
                    <td style='padding: 4px 0; text-align: right; color: #777;'>₱" . number_format($current_info['tip_amount'], 2) . "</td>
                </tr>";
            }

            // Grand Total
            $breakdownHtml .= "
            <tr>
                <td style='padding: 10px 0; border-top: 2px solid #568203; font-weight: bold; font-size: 16px;'>Total</td>
                <td style='padding: 10px 0; border-top: 2px solid #568203; text-align: right; font-weight: bold; font-size: 16px;'>₱" . number_format($current_info['total_amount'], 2) . "</td>
            </tr>";


            if ($custData && !empty($custData['customer_email'])) {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'bentesaislomi.26@gmail.com';
                $mail->Password   = 'gqzk qvow jxee kkns';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('bentesaislomi.26@gmail.com', 'Bente Sais Lomi House');
                $mail->addAddress($custData['customer_email'], $custData['customer_first_name']);

                $mail->isHTML(true);
                $mail->Subject = $subject;

                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
                    <div style='background-color: #5cfa63; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                        <h2 style='margin: 0;'>Bente Sais Lomi House</h2>
                    </div>
                    <div style='border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; padding: 20px;'>
                        <p>Hi <strong>" . htmlspecialchars($custData['customer_first_name']) . "</strong>,</p>
                        
                        <h3 style='color: #5cfa63;'>$headline</h3>
                        <p style='font-size: 16px; line-height: 1.5;'>$mainMessage</p>
                        
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                            <h4 style='margin-top: 0; border-bottom: 2px solid #5cfa63; padding-bottom: 10px; display: inline-block;'>Order Summary</h4>
                            <p style='margin: 5px 0; font-size: 14px; color: #555;'>Order #: <strong>" . $current_info['order_number'] . "</strong></p>
                            
                            <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                                $itemsHtml
                                $breakdownHtml
                            </table>
                        </div>

                        <p style='margin-top: 30px; font-size: 14px; color: #777;'>
                            Thank you for choosing us!<br>
                        </p>
                    </div>
                </div>
                ";
                
                $mail->send();
            }
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
        }
    }

    $status_map = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-primary',
        'preparing' => 'badge-info',
        'ready' => 'badge-success',
        'out_for_delivery' => 'badge-info',
        'delivered' => 'badge-secondary',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger',
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated.',
        'new_status_label' => ucfirst(str_replace('_', ' ', $new_status)),
        'new_status_class' => $status_map[$new_status] ?? 'badge-secondary'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>