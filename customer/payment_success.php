<?php
// customer/payment_success.php
require_once __DIR__ . '/../includes/db_connect.php';
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
      <p id="confirmSubtitle">Your order is being confirmed. Please check your email/phone for updates.</p>
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