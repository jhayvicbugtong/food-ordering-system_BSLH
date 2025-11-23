<?php
// customer/actions/paymongo_webhook.php
header('Content-Type: application/json');

// 1. ENABLE LOGGING
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/webhook_errors.log');
error_log("=== NEW WEBHOOK REQUEST ===");

require_once __DIR__ . '/../../includes/db_connect.php';


// !! REPLACE WITH YOUR ACTUAL SECRETS !!
// for testing purposes only 
define('PAYMONGO_WEBHOOK_SECRET', 'whsk_EH9ab63WRBCxmhccfaxTChwp'); // Aldrie's test secret
// define('PAYMONGO_WEBHOOK_SECRET', 'whsk_oYvkB1xmdV28sCpwWaP6FDLP'); // Jhabik's test secret
// define('PAYMONGO_WEBHOOK_SECRET', 'whsk_SpkukLULkqPBJxfP3nAqWT3C'); // Aeron's test secret

// for production purposes
// define('PAYMONGO_WEBHOOK_SECRET', 'whsk_xpJovku7BGFndm3bEQjzA1Ly'); 
define('PAYMONGO_SECRET_KEY', 'sk_test_MVV2EXZhRxpfiQmM16c18aM7');

// 2. GET RAW BODY
$raw_body = file_get_contents('php://input');
error_log("Raw Body: " . $raw_body);

$signature_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
error_log("Signature Header: " . $signature_header);

// 3. VERIFY SIGNATURE
function verify_signature($payload, $signature_header, $secret) {
    if (empty($signature_header)) {
        error_log("ERROR: No signature header found");
        return false;
    }
    
    $timestamp = null; 
    $signature_found = null;
    
    $parts = explode(',', $signature_header);
    foreach ($parts as $part) {
        $split = explode('=', $part, 2);
        if (count($split) != 2) continue;
        list($key, $value) = $split;
        
        if ($key === 't') {
            $timestamp = $value;
        } else if ($key === 'v1' || $key === 'te') {
            $signature_found = $value;
        }
    }
    
    if (!$timestamp || !$signature_found) {
        error_log("ERROR: Timestamp or Signature missing. t=$timestamp, sig=$signature_found");
        return false;
    }
    
    $signed_payload = $timestamp . '.' . $payload;
    $expected_signature = hash_hmac('sha256', $signed_payload, $secret);
    
    if (!hash_equals($expected_signature, $signature_found)) {
        error_log("ERROR: Signature Mismatch. Expected: $expected_signature, Got: $signature_found");
        return false;
    }
    
    error_log("SUCCESS: Signature verified!");
    return true;
}

if (!verify_signature($raw_body, $signature_header, PAYMONGO_WEBHOOK_SECRET)) {
    http_response_code(401);
    error_log("FATAL: Invalid signature - rejecting webhook");
    exit('Invalid signature');
}

// 4. PARSE THE EVENT
$event = json_decode($raw_body, true);
error_log("Event Type: " . ($event['data']['attributes']['type'] ?? 'UNKNOWN'));

if (!isset($event['data']['attributes']['type'])) {
    error_log("ERROR: No event type found");
    http_response_code(400);
    exit('Invalid event');
}

$event_type = $event['data']['attributes']['type'];

// 5. HANDLE PAYMENT SUCCESS EVENT
if ($event_type === 'checkout_session.payment.paid') {
    error_log("Processing payment.paid event...");
    
    $checkout_session_id = $event['data']['attributes']['data']['id'] ?? null;
    
    if (!$checkout_session_id) {
        error_log("ERROR: No checkout session ID in webhook payload");
        http_response_code(400);
        exit('Missing checkout session ID');
    }
    
    error_log("Checkout Session ID: " . $checkout_session_id);

    // RETRIEVE SESSION FROM PAYMONGO API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions/' . $checkout_session_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("PayMongo API Response Code: " . $http_code);

    if ($http_code !== 200) {
        error_log("ERROR: Failed to retrieve checkout session from PayMongo");
        http_response_code(400);
        exit('Failed to retrieve session');
    }

    $paymongo_data = json_decode($response_body, true);

    if (empty($paymongo_data['data']['attributes']['metadata']['order_data'])) {
        error_log("ERROR: No order_data in metadata");
        http_response_code(400);
        exit('Missing metadata');
    }

    $order_json = $paymongo_data['data']['attributes']['metadata']['order_data'];
    $user_id_from_meta = (int)($paymongo_data['data']['attributes']['metadata']['user_id'] ?? 0);
    
    error_log("User ID from metadata: " . $user_id_from_meta);

    $data = json_decode($order_json);

    if (!$data || empty($data->cartItems) || empty($data->orderDetails)) {
        error_log("ERROR: Invalid order data in metadata");
        http_response_code(400);
        exit('Invalid data');
    }

    $cartItems = $data->cartItems;
    $details = $data->orderDetails;

    $conn->begin_transaction();
    try {
        error_log("Starting database transaction...");
        
        // --- !! FIX FOR MISSING USER_ID !! ---
        $final_user_id = null;
        if ($user_id_from_meta > 0) {
            $stmt_check = $conn->prepare("SELECT 1 FROM users WHERE user_id = ?");
            $stmt_check->bind_param('i', $user_id_from_meta);
            $stmt_check->execute();
            $user_result = $stmt_check->get_result();
            if ($user_result->num_rows > 0) {
                $final_user_id = $user_id_from_meta;
            } else {
                error_log("WARNING: User ID {$user_id_from_meta} from metadata not found. Setting order user_id to NULL.");
            }
            $stmt_check->close();
        } else {
            error_log("WARNING: No User ID in metadata. Setting order user_id to NULL.");
        }
        // --- END FIX ---

        // --- START SERVER-SIDE FEE VALIDATION (WEBHOOK) ---
        $server_delivery_fee = 0.00;
        if ($details->orderType == 'delivery') {
            $barangay = $details->deliveryDetails->barangay ?? '';
            if (empty($barangay)) {
                throw new Exception("Webhook Error: Barangay is missing for delivery order.");
            }
            
            $stmt_fee = $conn->prepare("SELECT delivery_fee FROM deliverable_barangays WHERE LOWER(barangay_name) = LOWER(?) AND is_active = 1");
            $stmt_fee->bind_param('s', $barangay);
            $stmt_fee->execute();
            $res_fee = $stmt_fee->get_result();
            
            if ($fee_row = $res_fee->fetch_assoc()) {
                $server_delivery_fee = (float)$fee_row['delivery_fee'];
            } else {
                throw new Exception("Webhook Error: Delivery to barangay '{$barangay}' is not supported.");
            }
            $stmt_fee->close();
        }
        // --- END SERVER-SIDE FEE VALIDATION ---

        // --- RE-CALCULATE TOTALS USING SERVER-SIDE DATA ---
        // We MUST trust the subtotal and tip from the client JSON, 
        // but we OVERWRITE the delivery fee.
        $subtotal = (float)$details->subtotal;
        $delivery_fee = $server_delivery_fee; // <-- Use the VERIFIED fee
        $tip_amount = (float)$details->tipAmount;
        $total_amount = $subtotal + $delivery_fee + $tip_amount;
        
        // Compare our calculated total with the total in the metadata
        if (abs($total_amount - (float)$details->totalAmount) > 0.01) {
             error_log("WARNING: Webhook total mismatch. Client sent {$details->totalAmount}, server calculated $total_amount. Proceeding with server total.");
             // We don't throw an error, as payment is already taken. We log it and save the correct total.
        }
        // --- END RE-CALCULATION ---


        $order_number = 'BSLH-' . time() . '-' . substr($checkout_session_id, -4);
        $status = 'pending'; 
        $preferred_time_iso = $details->preferredTime ? $details->preferredTime : null;
        
        error_log("Creating order: " . $order_number);
        
        // INSERT ORDER
        $sql_order = "INSERT INTO orders 
        (order_number, user_id, order_type, order_time, preferred_time, status, subtotal, delivery_fee, tip_amount, total_amount, created_at)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt_order = $conn->prepare($sql_order);
        if (!$stmt_order) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt_order->bind_param('sisssdddd', 
            $order_number, $final_user_id, $details->orderType, $preferred_time_iso, $status, 
            $subtotal, $delivery_fee, $tip_amount, $total_amount // <-- Use VERIFIED fees
        );
        
        if (!$stmt_order->execute()) {
            throw new Exception("Order Insert Failed: " . $stmt_order->error);
        }
        
        $order_id = $conn->insert_id;
        error_log("Order created with ID: " . $order_id);
        $stmt_order->close();

        // ... (Rest of inserts: order_items, order_customer_details, order_addresses are unchanged) ...
        // INSERT ITEMS
        $sql_items = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_items = $conn->prepare($sql_items);
        
        foreach ($cartItems as $item) {
            $total_item_price = $item->unitPrice * $item->qty;
            $stmt_items->bind_param('iisdid', $order_id, $item->id, $item->name, $item->unitPrice, $item->qty, $total_item_price);
            $stmt_items->execute();
            error_log("Added item: " . $item->name);
        }
        $stmt_items->close();
        
        // INSERT CUSTOMER DETAILS
        $sql_cust = "INSERT INTO order_customer_details (order_id, customer_first_name, customer_last_name, customer_phone, customer_email, order_notes)
                     VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_cust = $conn->prepare($sql_cust);
        $notes = $details->notes ?? '';
        $stmt_cust->bind_param('isssss', $order_id, $details->firstName, $details->lastName, $details->phone, $details->email, $notes);
        $stmt_cust->execute();
        $stmt_cust->close();
        
        error_log("Customer details saved");

        // INSERT INTO `order_addresses`
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
            error_log("Delivery address saved");
        }


        // INSERT PAYMENT DETAILS
        $payment_id_ref = $paymongo_data['data']['attributes']['payments'][0]['id'] ?? $checkout_session_id; 
        $pay_method_source = $paymongo_data['data']['attributes']['payments'][0]['attributes']['source']['type'] ?? 'gcash';
        $payment_method = ($pay_method_source == 'card') ? 'card' : 'gcash';

        $sql_pay = "INSERT INTO order_payment_details (order_id, payment_method, payment_status, gcash_reference, amount_paid, paid_at)
                    VALUES (?, ?, 'paid', ?, ?, NOW())";
        $stmt_pay = $conn->prepare($sql_pay);
        // --- MODIFICATION: Save the SERVER-CALCULATED total ---
        $stmt_pay->bind_param('issd', $order_id, $payment_method, $payment_id_ref, $total_amount);
        $stmt_pay->execute();
        $stmt_pay->close();
        
        error_log("Payment details saved");
        
        // COMMIT TRANSACTION
        $conn->commit();
        error_log("SUCCESS: Transaction committed! Order #" . $order_number . " created.");

    } catch (Exception $e) {
        $conn->rollback();
        error_log("FATAL DB ERROR: " . $e->getMessage());
        http_response_code(500);
        die('Database error');
    }
} else {
    error_log("Ignoring event type: " . $event_type);
}

http_response_code(200);
echo json_encode(['status' => 'success']);
error_log("=== WEBHOOK COMPLETE ===");
?>