<?php
// customer/actions/create_payment_session.php
header('Content-Type: application/json');

// 1. SETUP
// --- MODIFIED: This now provides $BASE_URL ---
require_once __DIR__ . '/../../includes/db_connect.php'; 

// --- !! PUT YOUR TEST SECRET KEY HERE !! ---
define('PAYMONGO_SECRET_KEY', 'sk_test_MVV2EXZhRxpfiQmM16c18aM7'); 

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}
// --- FIX: Get the user_id from the session ---
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

try {
    // 3. SERVER-SIDE VALIDATION
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

    $subtotal = $server_subtotal;
    $delivery_fee = (float)$details->deliveryFee;
    $tip_amount = (float)$details->tipAmount;
    $total_amount = $subtotal + $delivery_fee + $tip_amount;

    if (abs($total_amount - (float)$details->totalAmount) > 0.01) {
        throw new Exception("Total amount mismatch. Please refresh and try again.");
    }

    // 4. PREPARE PAYMONGO PAYLOAD
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}";
    // --- FIXED: Use $BASE_URL instead of hardcoded folder ---
    $project_folder = $BASE_URL;
    
    $line_items = array_map(function($item) use ($db_prices) {
        return [
            'currency' => 'PHP',
            'amount' => (int)($db_prices[$item->id] * 100), // Amount in centavos
            'name' => $item->name,
            'quantity' => (int)$item->qty
        ];
    }, $cartItems);

    if ($delivery_fee > 0) {
        $line_items[] = ['currency' => 'PHP', 'amount' => (int)($delivery_fee * 100), 'name' => 'Delivery Fee', 'quantity' => 1];
    }
    if ($tip_amount > 0) {
         $line_items[] = ['currency' => 'PHP', 'amount' => (int)($tip_amount * 100), 'name' => 'Tip', 'quantity' => 1];
    }
    
    // --- FIX: Add the user_id to the metadata ---
    $metadata = [
        'order_data' => json_encode($data), // Store the *entire* original order
        'user_id' => $user_id               // Store the logged-in user's ID
    ];

    $paymongo_payload = [
        'data' => [
            'attributes' => [
                'billing' => [
                    'name' => "{$details->firstName} {$details->lastName}",
                    'email' => $details->email,
                    'phone' => $details->phone
                ],
                'send_email_receipt' => true,
                'show_description' => true,
                'show_line_items' => true,
                'line_items' => $line_items,
                'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay', 'dob_ubp'],
                'success_url' => "{$base_url}{$project_folder}/customer/payment_success.php?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "{$base_url}{$project_folder}/customer/payment_failed.php",
                'description' => "Order from Bente Sais Lomi House",
                'metadata' => $metadata // Attach our order data and user_id
            ]
        ]
    ];

    // 5. CALL PAYMONGO API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymongo_payload));
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $paymongo_data = json_decode($response_body, true);

    if ($http_code !== 200 || empty($paymongo_data['data']['id'])) {
        throw new Exception("PayMongo API Error: " . ($paymongo_data['errors'][0]['detail'] ?? 'Failed to create payment session.'));
    }

    // 6. RETURN SUCCESS (with redirect URL)
    echo json_encode([
        'success' => true,
        'paymentMethod' => 'paymongo',
        'checkoutUrl' => $paymongo_data['data']['attributes']['checkout_url']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>