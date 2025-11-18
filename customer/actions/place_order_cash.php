<?php
// customer/actions/place_order_cash.php
header('Content-Type: application/json');

// 1. SETUP
session_start();
require_once __DIR__ . '/../../includes/db_connect.php'; // Database connection

// Get user ID from session. Exit if not logged in.
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 2. READ INCOMING DATA
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (!$data || empty($data->cartItems) || empty($data->orderDetails)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
    exit;
}

$cartItems = $data->cartItems;
$details = $data->orderDetails;

// 3. SERVER-SIDE VALIDATION & CALCULATION
$conn->begin_transaction();
try {
    $product_ids = [];
    foreach ($cartItems as $item) {
        $product_ids[] = (int)$item->id;
    }

    if (empty($product_ids)) {
         throw new Exception("Cart is empty.");
    }

    $sql_prices = "SELECT product_id, base_price FROM products WHERE product_id IN (" . implode(',', $product_ids) . ")";
    $price_res = $conn->query($sql_prices);
    $db_prices = [];
    while ($row = $price_res->fetch_assoc()) {
        $db_prices[$row['product_id']] = (float)$row['base_price'];
    }

    $server_subtotal = 0;
    foreach ($cartItems as $item) {
        if (!isset($db_prices[$item->id])) {
            throw new Exception("Item '{$item->name}' is not available.");
        }
        $server_subtotal += $db_prices[$item->id] * (int)$item->qty;
    }

    // --- START SERVER-SIDE DELIVERY FEE VALIDATION ---
    $server_delivery_fee = 0.00;
    if ($details->orderType == 'delivery') {
        $barangay = $details->deliveryDetails->barangay ?? '';
        if (empty($barangay)) {
            throw new Exception("Barangay is required for delivery.");
        }
        
        $stmt_fee = $conn->prepare("SELECT delivery_fee FROM deliverable_barangays WHERE LOWER(barangay_name) = LOWER(?) AND is_active = 1");
        $stmt_fee->bind_param('s', $barangay);
        $stmt_fee->execute();
        $res_fee = $stmt_fee->get_result();
        
        if ($fee_row = $res_fee->fetch_assoc()) {
            $server_delivery_fee = (float)$fee_row['delivery_fee'];
        } else {
            // Barangay is not in our table, reject the order.
            throw new Exception("Sorry, we do not deliver to this barangay.");
        }
        $stmt_fee->close();
    }
    // --- END SERVER-SIDE DELIVERY FEE VALIDATION ---

    $subtotal = $server_subtotal;
    $delivery_fee = $server_delivery_fee; // <-- Use the VERIFIED fee
    $tip_amount = (float)$details->tipAmount;
    $total_amount = $subtotal + $delivery_fee + $tip_amount;
    
    // This check is now more robust. It will fail if client's fee doesn't match server's.
    if (abs($total_amount - (float)$details->totalAmount) > 0.01) {
        throw new Exception("Total amount mismatch. Please refresh and try again. (Server: $total_amount, Client: {$details->totalAmount})");
    }

    // 4. INSERT INTO `orders` TABLE
    $order_number = 'BSLH-' . time();
    $status = 'pending'; 
    $preferred_time_iso = $details->preferredTime ? $details->preferredTime : null;
    
    $sql_order = "INSERT INTO orders (order_number, user_id, order_type, order_time, preferred_time, status, subtotal, delivery_fee, tip_amount, total_amount, created_at)
                  VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param(
        'sisssdddd',
        $order_number,
        $user_id,
        $details->orderType,
        $preferred_time_iso,
        $status,
        $subtotal,
        $delivery_fee, // <-- Save the VERIFIED fee
        $tip_amount,
        $total_amount
    );
    $stmt_order->execute();
    $order_id = $conn->insert_id;
    if ($order_id === 0) throw new Exception("Failed to create order.");
    $stmt_order->close();

    // ... (Rest of the inserts: order_items, order_customer_details, order_addresses are unchanged) ...
    // 5. INSERT INTO `order_items`
    $sql_items = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_items = $conn->prepare($sql_items);
    
    foreach ($cartItems as $item) {
        $product_id = (int)$item->id;
        $unit_price = $db_prices[$product_id];
        $quantity = (int)$item->qty;
        $total_price = $unit_price * $quantity;
        $stmt_items->bind_param('iisdid', $order_id, $product_id, $item->name, $unit_price, $quantity, $total_price);
        $stmt_items->execute();
    }
    $stmt_items->close();
    
    // 6. INSERT INTO `order_customer_details`
    $sql_cust = "INSERT INTO order_customer_details (order_id, customer_first_name, customer_last_name, customer_phone, customer_email, order_notes)
                 VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_cust = $conn->prepare($sql_cust);
    $notes = $details->notes ?? null;
    $stmt_cust->bind_param('isssss', $order_id, $details->firstName, $details->lastName, $details->phone, $details->email, $notes);
    $stmt_cust->execute();
    $stmt_cust->close();

    // 7. INSERT INTO `order_addresses`
    if ($details->orderType == 'delivery') {
        $sql_addr = "INSERT INTO order_addresses (order_id, street, barangay, city, province, floor_number, apt_landmark)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_addr = $conn->prepare($sql_addr);
        
        $street = $details->deliveryDetails->street ?? null;
        $barangay = $details->deliveryDetails->barangay ?? null;
        $city = $details->deliveryDetails->city ?? null;
        $province = $details->deliveryDetails->province ?? null;
        $floor_number = $details->deliveryDetails->floor_number ?? null; 
        $apt_landmark = $details->deliveryDetails->apt_landmark ?? null; 
        
        $stmt_addr->bind_param('issssss', $order_id, $street, $barangay, $city, $province, $floor_number, $apt_landmark);
        $stmt_addr->execute();
        $stmt_addr->close();
    }

    // 8. INSERT INTO `order_payment_details`
    $sql_pay = "INSERT INTO order_payment_details (order_id, payment_method, payment_status, amount_paid)
                VALUES (?, 'cash', 'pending', ?)";
    $stmt_pay = $conn->prepare($sql_pay);
    $stmt_pay->bind_param('id', $order_id, $total_amount);
    $stmt_pay->execute();
    $stmt_pay->close();

    // 9. COMMIT
    $conn->commit();
    echo json_encode([
        'success' => true,
        'orderId' => $order_id,
        'orderNumber' => $order_number,
        'paymentMethod' => 'cash'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400); // Use 400 for validation errors
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>