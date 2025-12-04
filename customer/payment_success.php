<?php
// customer/payment_success.php
require_once __DIR__ . '/../includes/db_connect.php';

// --- ADDED: Fetch Order Number Logic ---
$order_number_display = null;
$found_order_number = false;

// Define Key (Ensure this matches your other files)
if (!defined('PAYMONGO_SECRET_KEY')) {
    define('PAYMONGO_SECRET_KEY', 'sk_test_MVV2EXZhRxpfiQmM16c18aM7');
}

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];

    // 1. Get Payment ID from PayMongo API
    // We need this because the webhook stores the Payment ID (not always the Session ID) in the database.
    $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions/' . $session_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYMONGO_SECRET_KEY . ':');
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    
    // Logic matches webhook: use payment ID if available, else fallback to session ID
    $payment_id_ref = $data['data']['attributes']['payments'][0]['id'] ?? $session_id;

    // 2. Poll DB for Order Number (max 3 seconds) to handle webhook race condition
    for ($i = 0; $i < 3; $i++) {
        $stmt = $conn->prepare("
            SELECT o.order_number 
            FROM orders o
            JOIN order_payment_details op ON o.order_id = op.order_id
            WHERE op.gcash_reference = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $payment_id_ref);
        $stmt->execute();
        $stmt->bind_result($ord_num);
        
        if ($stmt->fetch()) {
            $order_number_display = $ord_num;
            $found_order_number = true;
            $stmt->close();
            break; // Found it, stop waiting
        }
        $stmt->close();
        sleep(1); // Wait 1 sec for webhook to finish processing
    }
}
// ---------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Payment Successful | Bente Sais Lomi House</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/checkout.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="checkout-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="checkout-progress-bar" id="progressBar">
  <div class="cstep-container">
    <div class="cstep done"><div class="cstep-num">✔</div><div class="cstep-label">Order</div></div>
    <div class="cstep active"><div class="cstep-num">2</div><div class="cstep-label">Confirmation</div></div>
  </div>
</div>

<section class="confirm-wrapper" id="confirmScreen">
  <div class="confirm-panel">
    <div class="confirm-icon">✅</div>
    <div class="confirm-main">
      <h2 id="confirmTitle">Payment Successful!</h2>
      
      <?php if ($found_order_number): ?>
          <div style="margin: 10px 0;">
              <span style="color: #555; font-size: 0.95rem;">Order Reference:</span>
              <div style="font-size: 1.5rem; font-weight: 700; color: #2e7d32; letter-spacing: 0.5px;">
                  <?= htmlspecialchars($order_number_display) ?>
              </div>
          </div>
      <?php elseif (isset($_GET['session_id'])): ?>
          <div style="font-size: 0.9rem; color: #666; margin: 10px 0;">
              Processing Order...<br>
              <small>Ref: <?= htmlspecialchars($_GET['session_id']) ?></small>
          </div>
      <?php endif; ?>
      <p id="confirmSubtitle">Your order has been placed. Please wait for the store to confirm.</p>
    </div>
  </div>

  <div class="summary-grid-block" id="confirmSummary">
    <h3 class="summary-grid-title">Your order summary</h3>
    <div class="confirm-flex">
      <div class="confirm-left">
        <div id="confirmItems"><div class="confirm-row"><div>Loading summary...</div></div></div>
        <div class="confirm-row"><div class="confirm-row-label">Sub-total</div><div id="confirmSubtotal">₱0.00</div></div>
        <div class="confirm-row"><div class="confirm-row-label">Delivery</div><div id="confirmDelivery">₱0.00</div></div>
        <div class="confirm-row"><div class="confirm-row-label">Tip</div><div id="confirmTip">₱0.00</div></div>
        <div class="confirm-row" style="font-weight:600; font-size:16px; margin-top:10px;">
          <div class="confirm-row-label" style="font-weight:600;">Total</div>
          <div id="confirmTotal">₱0.00</div>
        </div>
      </div>
      <div class="confirm-right">
        <div class="order-type-block">
          <div class="confirm-row-label">Order type</div>
          <div id="confirmOrderType">—</div>
          <div class="order-type-extra" id="confirmOrderTime"></div>
        </div>
        <div style="margin-top:16px;"><div class="confirm-row-label">Payment</div><div id="confirmPayment">Paid Online</div></div>
        <div class="customer-block">
          <div class="confirm-row-label" style="margin-top:16px;">Customer</div>
          <div id="confirmCustomerName">—</div>
          <div id="confirmCustomerPhone">—</div>
          <div id="confirmCustomerEmail">—</div>
        </div>
      </div>
    </div>
    <div class="light-blue-box">
      <div class="bubble-icon">i</div>
      <div>
        We've received your payment. Our system is logging your order now.
        <br><br>
        <button onclick="finishOrder()" class="btn btn-sm btn-success" style="color:white; font-weight:600;">Back to Menu</button>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  function currency(n) { return `₱${(Number(n) || 0).toFixed(2)}`; }

  document.addEventListener('DOMContentLoaded', function() {
    // 1. Clear the main cart (because items are bought)
    localStorage.removeItem('bslh_cart');

    // 2. Get the pending order data
    const orderDataJson = localStorage.getItem('bslh_pending_order');
    
    if (!orderDataJson) {
      document.getElementById('confirmItems').innerHTML = '<div class="confirm-row"><div>Order details not found in browser storage.</div></div>';
      return;
    }

    try {
      const orderData = JSON.parse(orderDataJson);
      hydrateConfirmation(orderData);
      
      // --- ADDED: SweetAlert2 Success Popup for Online Payment ---
      Swal.fire({
        icon: 'success',
        title: 'Payment Successful!',
        text: 'Your payment has been received and your order is placed.',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Great!'
      });
      // -----------------------------------------------------------

      // Note: We DO NOT delete 'bslh_pending_order' here immediately. 
      // We delete it when they click "Back to Menu".
    } catch (e) {
      console.error("Could not parse pending order data:", e);
    }
  });

  function hydrateConfirmation(orderData) {
    const details = orderData.orderDetails;
    const cart = orderData.cartItems;
    
    const itemsEl = document.getElementById('confirmItems');
    itemsEl.innerHTML = '';
    if (cart && cart.length > 0) {
      cart.forEach(it => {
        itemsEl.innerHTML += `
          <div class="confirm-row">
            <div>${it.qty} x&nbsp;${it.name}</div>
            <div>${currency(it.qty * it.unitPrice)}</div>
          </div>`;
      });
    }

    document.getElementById('confirmSubtotal').textContent = currency(details.subtotal);
    document.getElementById('confirmDelivery').textContent = currency(details.deliveryFee);
    document.getElementById('confirmTip').textContent = currency(details.tipAmount);
    document.getElementById('confirmTotal').textContent = currency(details.totalAmount);
    document.getElementById('confirmOrderType').textContent = details.orderType === 'delivery' ? 'Delivery' : 'Pickup';
    document.getElementById('confirmOrderTime').innerHTML = (details.preferredTimeReadable || 'As soon as possible').replace(/\n/g,'<br>');
    document.getElementById('confirmPayment').textContent = 'Paid Online (PayMongo)';
    document.getElementById('confirmCustomerName').textContent = `${details.firstName} ${details.lastName}`;
    document.getElementById('confirmCustomerPhone').textContent = details.phone;
    document.getElementById('confirmCustomerEmail').textContent = details.email;
  }

  function finishOrder() {
    // Clean up storage and go home
    localStorage.removeItem('bslh_pending_order');
    window.location.href = window.BASE_URL + "/customer/menu.php";
  }
</script>
</body>
</html>