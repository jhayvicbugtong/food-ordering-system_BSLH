<?php
// later you'll pull real order/cart/session data
$cartItems = [
  [
    "name" => "Appetizer 1 (450g) / Appetizers",
    "qty" => 1,
    "price" => 21.00,
  ],
];
$subtotal = 21.00;
$deliveryFee = 0.00;
$tip = 2.10; // example tip for the confirmation screenshot
$total = $subtotal + $deliveryFee + $tip;

// dummy customer info for confirmation block (replace w/ POST data later)
$customer_first = "dfgdfsg";
$customer_last  = "gjvasahdas";
$customer_phone = "+63 956 244 6616";
$customer_email = "sdfgsdg@gmail.com";

$order_type     = "Pickup"; // or "Delivery"
$order_time_msg = "For your chosen time:\n2025-10-29 01:00 AM"; // shown in red in screenshot
$payment_label  = "Credit/Debit Card (On Delivery or Pickup)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Checkout | Bente Sais Lomi House</title>

  <!-- fonts + icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <!-- shared theme -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>

  <style>
    .hidden { display:none !important; }

    /* ===== Progress bar / steps ===== */
    .checkout-progress-bar {
      background: #fff;
      border-bottom: 1px solid #dee2e6;
      padding: 16px 20px;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    }

    .cstep-container {
      display:flex;
      flex-wrap:wrap;
      gap:32px;
      align-items:flex-start;
      line-height:1.3;
    }

    .cstep {
      display:flex;
      align-items:center;
      gap:8px;
      font-size:15px;
      font-weight:500;
      color:#6c757d;
    }
    .cstep-num {
      width:24px;
      height:24px;
      border-radius:50%;
      border:1px solid #37d438;
      color:#37d438;
      font-size:13px;
      font-weight:600;
      line-height:22px;
      text-align:center;
    }
    .cstep.active {
      color:#212529;
      font-weight:600;
    }
    .cstep.done {
      color:#37d438;
      font-weight:600;
    }
    .cstep.done .cstep-num {
      background-color:#37d438;
      color:#fff;
      border-color:#37d438;
    }

    .checkout-close-btn {
      background:transparent;
      border:none;
      color:#000;
      font-size:18px;
      line-height:1;
      cursor:pointer;
    }
    .checkout-close-btn:hover {
      color:#dc3545;
      transform:rotate(90deg);
      transition:all .15s ease;
    }

    /* ===== Checkout layout (step 1 screen) ===== */
    .checkout-layout {
      max-width:1300px;
      margin:0 auto;
      padding:20px;
      display:grid;
      grid-template-columns:1fr 360px;
      gap:24px;
      background-color:#f8f9fa;
      min-height:calc(100vh - 80px);
      font-family:"Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    }
    .checkout-left { min-width:0; }

    .checkout-card {
      background:#fff;
      border:1px solid #dee2e6;
      border-radius:4px;
      box-shadow:0 8px 24px rgba(0,0,0,0.05);
      padding:20px;
      margin-bottom:20px;
    }
    .checkout-card-head {
      display:flex;
      justify-content:space-between;
      flex-wrap:wrap;
      align-items:flex-start;
      gap:12px;
      margin-bottom:16px;
    }

    .checkout-block-title {
      margin:0;
      font-size:16px;
      font-weight:600;
      color:#212529;
      line-height:1.3;
    }

    .location-btn {
      background:transparent;
      border:0;
      color:#37d438;
      font-size:13px;
      line-height:1.4;
      font-weight:500;
      cursor:pointer;
      display:flex;
      align-items:center;
      gap:6px;
      padding:0;
    }

    /* radio styles */
    .radio-block {
      border:1px solid #ced4da;
      border-radius:4px;
      padding:16px;
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:16px;
      cursor:pointer;
      margin-bottom:12px;
      font-size:15px;
      line-height:1.4;
      font-weight:500;
      color:#212529;
    }
    .radio-block input[type="radio"] {
      width:18px;
      height:18px;
      accent-color:#37d438;
      flex-shrink:0;
      margin-top:2px;
    }
    .radio-block.selected {
      border:2px solid #37d438;
      box-shadow:0 0 0 3px rgba(92,250,99,0.3);
    }
    .radio-block.small { padding:14px; font-size:14px; }

    .radio-left {
      display:flex;
      align-items:flex-start;
      gap:10px;
      flex:1;
      color:inherit;
    }
    .radio-right {
      font-size:22px;
      line-height:1;
      color:#212529;
    }

    .split-two {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:16px;
    }

    /* form inputs */
    .form-row { margin-bottom:16px; }
    .form-row.two-col {
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:16px;
    }
    .form-label {
      font-size:14px;
      font-weight:500;
      color:#212529;
      line-height:1.3;
      margin-bottom:6px;
      display:block;
    }
    .form-input {
      width:100%;
      font-size:14px;
      line-height:1.4;
      font-family:inherit;
      padding:10px 12px;
      border:1px solid #ced4da;
      border-radius:4px;
      outline:none;
    }
    .form-input:focus {
      border-color:#37d438;
      box-shadow:0 0 0 3px rgba(92,250,99,0.3);
    }

    /* right summary (step 1) */
    .checkout-right {
      background:#fff;
      border:1px solid #dee2e6;
      border-radius:4px;
      box-shadow:0 8px 24px rgba(0,0,0,0.05);
      height:fit-content;
      padding:20px;
      position:sticky;
      top:20px;
    }

    .summary-title {
      margin:0 0 16px;
      font-size:16px;
      font-weight:600;
      line-height:1.3;
      color:#212529;
    }
    .summary-items { margin-bottom:16px; font-size:14px; }
    .summary-line { margin-bottom:12px; }
    .summary-line-top {
      display:flex;
      justify-content:space-between;
      flex-wrap:nowrap;
      gap:12px;
      font-size:14px;
      font-weight:500;
      line-height:1.4;
      color:#212529;
    }
    .summary-edit {
      display:flex;
      align-items:center;
      flex-wrap:wrap;
      gap:12px;
      margin-top:6px;
      font-size:13px;
      line-height:1.4;
    }
    .edit-link {
      color:#198754;
      text-decoration:none;
      font-weight:500;
    }
    .qty-mini { display:flex; gap:8px; }
    .mini-btn {
      background:transparent;
      border:0;
      font-size:16px;
      line-height:1;
      cursor:pointer;
    }
    .summary-subrows .rowline {
      display:flex;
      justify-content:space-between;
      margin-bottom:10px;
      font-size:14px;
      line-height:1.4;
      color:#212529;
    }

    .tip-row .tip-left { display:flex; flex-direction:column; }
    .tip-left small { font-size:12px; color:#6c757d; }

    .tip-buttons {
      display:flex;
      flex-wrap:nowrap;
      border:1px solid #212529;
      border-radius:4px;
      overflow:hidden;
    }
    .tip-btn {
      background:#fff;
      border:0;
      border-right:1px solid #212529;
      padding:8px 14px;
      font-size:13px;
      line-height:1.2;
      font-weight:500;
      cursor:pointer;
    }
    .tip-btn:last-child { border-right:0; }
    .tip-btn.active { background-color:#212529; color:#fff; }

    .total-row {
      border-top:1px solid #dee2e6;
      padding-top:12px;
      font-weight:600;
      font-size:16px;
    }

    .placeorder-btn {
      width:100%;
      background-color:#37d438;
      border:0;
      border-radius:4px;
      padding:14px;
      font-size:16px;
      font-weight:600;
      line-height:1.3;
      color:#fff;
      cursor:pointer;
      margin-top:12px;
      margin-bottom:12px;
      text-align:center;
    }
    .coupon-link {
      font-size:13px;
      text-align:center;
    }
    .coupon-link a {
      color:#198754;
      text-decoration:none;
      font-weight:500;
    }

    @media(max-width:980px){
      .checkout-layout { grid-template-columns:1fr; }
      .checkout-right { position:relative; top:0; }
    }

    /* ===== CONFIRMATION VIEW (step 2 screen) ===== */
    .confirm-wrapper {
      max-width:1300px;
      margin:0 auto;
      padding:20px;
      background:#f8f9fa;
      min-height:calc(100vh - 80px);
      font-family:"Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    }

    .confirm-panel {
      background:#fff;
      border:1px solid #dee2e6;
      border-radius:4px;
      box-shadow:0 8px 24px rgba(0,0,0,0.05);
      margin-bottom:20px;
      padding:24px;
      display:flex;
      flex-wrap:wrap;
      align-items:flex-start;
      gap:20px;
    }

    .confirm-icon {
      font-size:48px;
      line-height:1;
      color:#212529;
    }
    .confirm-main h2 {
      margin:0 0 6px;
      font-size:20px;
      font-weight:600;
      line-height:1.3;
      color:#212529;
    }
    .confirm-main p {
      margin:0;
      font-size:14px;
      line-height:1.4;
      color:#6c757d;
    }

    .summary-grid-block {
      background:#fff;
      border:1px solid #dee2e6;
      border-radius:4px;
      box-shadow:0 8px 24px rgba(0,0,0,0.05);
      padding:24px;
    }

    .summary-grid-title {
      margin:0 0 16px;
      font-size:16px;
      font-weight:600;
      color:#212529;
      line-height:1.3;
    }

    .confirm-flex {
      display:flex;
      flex-wrap:wrap;
      gap:24px;
    }

    .confirm-left {
      flex:1 1 320px;
      min-width:260px;
      border-right:1px solid #dee2e6;
      padding-right:24px;
    }
    .confirm-right {
      flex:1 1 320px;
      min-width:260px;
    }

    .confirm-row {
      display:flex;
      justify-content:space-between;
      font-size:14px;
      line-height:1.4;
      margin-bottom:10px;
      color:#212529;
    }
    .confirm-row-label {
      font-weight:500;
      color:#212529;
    }

    .order-type-block {
      font-size:14px;
      line-height:1.4;
      color:#212529;
    }
    .order-type-extra {
      font-size:13px;
      line-height:1.4;
      color:#dc3545; /* red text like screenshot */
      margin-top:4px;
      white-space:pre-line;
    }

    .customer-block {
      font-size:14px;
      line-height:1.5;
      color:#212529;
      margin-top:12px;
    }
    .customer-block div {
      margin-bottom:4px;
    }

    .light-blue-box {
      background:#e9f4ff;
      border:1px solid #bcdcff;
      border-radius:4px;
      margin-top:24px;
      padding:16px;
      font-size:14px;
      line-height:1.4;
      color:#212529;
      display:flex;
      align-items:flex-start;
      gap:8px;
    }
    .light-blue-box .bubble-icon {
      width:20px;
      height:20px;
      border-radius:50%;
      background:#fff;
      border:1px solid #bcdcff;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:12px;
      line-height:1;
      color:#0d6efd;
      flex-shrink:0;
    }
  </style>
</head>

<body class="checkout-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- PROGRESS BAR -->
<div class="checkout-progress-bar" id="progressBar">
  <div class="cstep-container">
    <!-- Step 1 -->
    <div class="cstep done" id="step1Dot">
      <div class="cstep-num">✔</div>
      <div class="cstep-label">Order</div>
    </div>

    <!-- Step 2 -->
    <div class="cstep" id="step2Dot">
      <div class="cstep-num">2</div>
      <div class="cstep-label">Confirmation</div>
    </div>

    <!-- Step 3 -->
    <div class="cstep" id="step3Dot">
      <div class="cstep-num">3</div>
      <div class="cstep-label">Ready</div>
    </div>
  </div>

  <button class="checkout-close-btn" type="button" onclick="cancelCheckout()">
    <i class="bi bi-x-lg"></i>
  </button>
</div>

<!-- STEP 1 VIEW: CHECKOUT FORM -->
<main class="checkout-layout" id="checkoutScreen">
  <section class="checkout-left">

    <!-- PAYMENT -->
    <div class="checkout-card">
      <h2 class="checkout-block-title">Payment</h2>

      <label class="radio-block selected">
        <div class="radio-left">
          <input type="radio" name="payment_method" value="card" checked>
          <span>Credit/Debit Card (On Delivery or Pickup)</span>
        </div>
        <div class="radio-right">
          <i class="bi bi-credit-card"></i>
        </div>
      </label>

      <label class="radio-block">
        <div class="radio-left">
          <input type="radio" name="payment_method" value="cash">
          <span>Cash</span>
        </div>
        <div class="radio-right">
          <i class="bi bi-cash-stack"></i>
        </div>
      </label>
    </div>

    <!-- ORDER TYPE + WHEN -->
    <div class="checkout-card split-two">
      <div class="split-col">
        <h2 class="checkout-block-title">Order type</h2>

        <label class="radio-block small selected" id="radioDelivery">
          <div class="radio-left">
            <input type="radio" name="ordertype" value="delivery" checked>
            <span>Delivery</span>
          </div>
        </label>

        <label class="radio-block small" id="radioPickup">
          <div class="radio-left">
            <input type="radio" name="ordertype" value="pickup">
            <span>Pickup</span>
          </div>
        </label>
      </div>

      <div class="split-col">
        <h2 class="checkout-block-title">When</h2>

        <label class="radio-block small selected">
          <div class="radio-left">
            <input type="radio" name="when" value="asap" checked>
            <span>As soon as possible</span>
          </div>
        </label>

        <label class="radio-block small">
          <div class="radio-left">
            <input type="radio" name="when" value="specific">
            <span>Specific time</span>
          </div>
        </label>
      </div>
    </div>

    <!-- ADDRESS -->
    <div class="checkout-card" id="addressCard">
      <div class="checkout-card-head">
        <h2 class="checkout-block-title">Address</h2>
        <button class="location-btn" type="button">
          <i class="bi bi-crosshair"></i>
          <span>Use my location</span>
        </button>
      </div>

      <div class="form-row">
        <label class="form-label">Street *</label>
        <input class="form-input" type="text" placeholder="Street / purok / house no.">
      </div>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">City *</label>
          <input class="form-input" type="text" placeholder="City / Barangay">
        </div>
        <div class="col">
          <label class="form-label">ZIP / Postal code</label>
          <input class="form-input" type="text" placeholder="ZIP / postal">
        </div>
      </div>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">State / Province *</label>
          <select class="form-input">
            <option>- select -</option>
            <option>Batangas</option>
            <option>Cavite</option>
            <option>Laguna</option>
          </select>
        </div>
        <div class="col">
          <label class="form-label">Floor number</label>
          <input class="form-input" type="text" placeholder="Optional">
        </div>
      </div>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">Apt. / Landmark</label>
          <input class="form-input" type="text" placeholder="Apartment, landmark, etc.">
        </div>
        <div class="col">
          <label class="form-label">Company name</label>
          <input class="form-input" type="text" placeholder="Company / Building">
        </div>
      </div>
    </div>

    <!-- CONTACT -->
    <div class="checkout-card">
      <h2 class="checkout-block-title">Contact</h2>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">First name *</label>
          <input class="form-input" type="text" value="<?php echo htmlspecialchars($customer_first); ?>">
        </div>
        <div class="col">
          <label class="form-label">Last name *</label>
          <input class="form-input" type="text" value="<?php echo htmlspecialchars($customer_last); ?>">
        </div>
      </div>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">Phone *</label>
          <input class="form-input" type="text" value="<?php echo htmlspecialchars($customer_phone); ?>">
        </div>
        <div class="col">
          <label class="form-label">Email *</label>
          <input class="form-input" type="email" value="<?php echo htmlspecialchars($customer_email); ?>">
        </div>
      </div>

      <div class="form-row">
        <label class="form-label">Order notes (optional)</label>
        <textarea class="form-input" rows="3" placeholder="Ex: no chili, call before arriving"></textarea>
      </div>
    </div>

  </section>

  <!-- RIGHT: order summary + button -->
  <aside class="checkout-right">
    <h2 class="summary-title">Your order</h2>

    <div class="summary-items">
      <?php foreach ($cartItems as $item): ?>
        <div class="summary-line">
          <div class="summary-line-main">
            <div class="summary-line-top">
              <span class="item-name">
                <?php echo htmlspecialchars($item['qty']); ?> x
                <?php echo htmlspecialchars($item['name']); ?>
              </span>
              <span class="item-price">
                ₱<?php echo number_format($item['price'],2); ?>
              </span>
            </div>
            <div class="summary-edit">
              <a href="#" class="edit-link">Edit</a>
              <div class="qty-mini">
                <button class="mini-btn">+</button>
                <button class="mini-btn">-</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="summary-subrows">
      <div class="rowline">
        <span>Sub-total</span>
        <span>₱<?php echo number_format($subtotal,2); ?></span>
      </div>
      <div class="rowline">
        <span>Delivery</span>
        <span>₱<?php echo number_format($deliveryFee,2); ?></span>
      </div>

      <div class="rowline tip-row">
        <div class="tip-left">
          <span>Tip</span>
          <small>optional</small>
        </div>
        <div class="tip-buttons">
          <button class="tip-btn active">0%</button>
          <button class="tip-btn">10%</button>
          <button class="tip-btn">20%</button>
          <button class="tip-btn">Other</button>
        </div>
      </div>

      <div class="rowline total-row">
        <span class="total-label">TOTAL</span>
        <span class="total-price">
          ₱<?php echo number_format($total,2); ?>
        </span>
      </div>
    </div>

    <button class="placeorder-btn" id="placeOrderBtn">
      Create order
    </button>

    <div class="coupon-link">
      <a href="#">I have a coupon</a>
    </div>
  </aside>
</main>


<!-- STEP 2 VIEW: CONFIRMATION SCREEN -->
<section class="confirm-wrapper hidden" id="confirmScreen">
  <!-- top status panel -->
  <div class="confirm-panel">
    <div class="confirm-icon">
      <!-- hourglass icon substitute -->
      ⏳
    </div>
    <div class="confirm-main">
      <h2>Sent to the restaurant</h2>
      <p>Wait until we confirm it</p>
    </div>
  </div>

  <!-- order summary block similar to screenshot -->
  <div class="summary-grid-block">
    <h3 class="summary-grid-title">Your order</h3>

    <div class="confirm-flex">
      <!-- LEFT side: items and totals -->
      <div class="confirm-left">
        <?php foreach ($cartItems as $item): ?>
          <div class="confirm-row">
            <div>
              <?php echo htmlspecialchars($item['qty']); ?> x&nbsp;
              <?php echo htmlspecialchars($item['name']); ?>
            </div>
            <div>
              $<?php echo number_format($item['price'],2); ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="confirm-row">
          <div class="confirm-row-label">Sub-total</div>
          <div>$<?php echo number_format($subtotal,2); ?></div>
        </div>

        <div class="confirm-row">
          <div class="confirm-row-label">Tip</div>
          <div>$<?php echo number_format($tip,2); ?></div>
        </div>

        <div class="confirm-row" style="font-weight:600; font-size:16px; margin-top:10px;">
          <div class="confirm-row-label" style="font-weight:600;">Total</div>
          <div>$<?php echo number_format($total,2); ?></div>
        </div>
      </div>

      <!-- RIGHT side: order meta -->
      <div class="confirm-right">

        <div class="order-type-block">
          <div class="confirm-row-label">Order type</div>
          <div><?php echo htmlspecialchars($order_type); ?></div>
          <div class="order-type-extra">
            <?php echo nl2br(htmlspecialchars($order_time_msg)); ?>
          </div>
        </div>

        <div style="margin-top:16px;">
          <div class="confirm-row-label">Payment</div>
          <div><?php echo htmlspecialchars($payment_label); ?></div>
        </div>

        <div class="customer-block">
          <div class="confirm-row-label" style="margin-top:16px;">Customer</div>
          <div><?php echo htmlspecialchars($customer_first . ' ' . $customer_last); ?></div>
          <div><?php echo htmlspecialchars($customer_phone); ?></div>
          <div><?php echo htmlspecialchars($customer_email); ?></div>
        </div>

      </div>
    </div>

    <!-- bottom notice / info box -->
    <div class="light-blue-box">
      <div class="bubble-icon">i</div>
      <div>
        We'll update you once the restaurant confirms your order.
        You can stay on this page.
      </div>
    </div>
  </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  // Cancel / exit back to menu
  function cancelCheckout() {
    if (confirm("Are you sure you want to leave checkout and go back to the menu?")) {
      window.location.href = "/food-ordering-system_BSLH/customer/menu.php";
    }
  }

  // Delivery vs Pickup toggle (step1)
  const radioDelivery = document.getElementById('radioDelivery');
  const radioPickup   = document.getElementById('radioPickup');
  const addressCard   = document.getElementById('addressCard');

  function activateDelivery() {
    radioDelivery.classList.add('selected');
    radioDelivery.querySelector('input').checked = true;

    radioPickup.classList.remove('selected');
    radioPickup.querySelector('input').checked = false;

    addressCard.classList.remove('hidden');
  }

  function activatePickup() {
    radioPickup.classList.add('selected');
    radioPickup.querySelector('input').checked = true;

    radioDelivery.classList.remove('selected');
    radioDelivery.querySelector('input').checked = false;

    addressCard.classList.add('hidden');
  }

  radioDelivery.addEventListener('click', activateDelivery);
  radioPickup.addEventListener('click', activatePickup);

  // Create order -> show Confirmation screen (step2)
  const placeOrderBtn  = document.getElementById('placeOrderBtn');
  const checkoutScreen = document.getElementById('checkoutScreen');
  const confirmScreen  = document.getElementById('confirmScreen');

  const step1Dot = document.getElementById('step1Dot');
  const step2Dot = document.getElementById('step2Dot');

  placeOrderBtn.addEventListener('click', function () {
    // hide checkout form, show confirmation
    checkoutScreen.classList.add('hidden');
    confirmScreen.classList.remove('hidden');

    // mark step1 as "done", step2 as "active"
    step1Dot.classList.add('done');
    step1Dot.classList.remove('active');

    step2Dot.classList.add('active');
  });
</script>

</body>
</html>
