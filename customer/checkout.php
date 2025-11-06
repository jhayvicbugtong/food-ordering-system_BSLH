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
$customer_first = "";
$customer_last  = "";
$customer_phone = "";
$customer_email = "";

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
  <link rel="stylesheet" href="../assets/css/checkout.css">

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
            <!-- <option>Cavite</option>
            <option>Laguna</option> -->
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
        <!-- <div class="col">
          <label class="form-label">Company name</label>
          <input class="form-input" type="text" placeholder="Company / Building">
        </div> -->
      </div>
    </div>

    <!-- CONTACT -->
    <div class="checkout-card">
      <h2 class="checkout-block-title">Contact</h2>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">First name *</label>
          <input class="form-input" type="text" placeholder="first name" value="<?php echo htmlspecialchars($customer_first); ?>">
        </div>
        <div class="col">
          <label class="form-label">Last name *</label>
          <input class="form-input" type="text" placeholder="last name" value="<?php echo htmlspecialchars($customer_last); ?>">
        </div>
      </div>

      <div class="form-row two-col">
        <div class="col">
          <label class="form-label">Phone *</label>
          <input class="form-input" type="text" placeholder="+63 956 244 6616" value="<?php echo htmlspecialchars($customer_phone); ?>">
        </div>
        <div class="col">
          <label class="form-label">Email *</label>
          <input class="form-input" type="email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($customer_email); ?>">
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

  <div class="summary-items" id="summaryItems"><!-- filled by JS --></div>

  <div class="summary-subrows">
    <div class="rowline">
      <span>Sub-total</span>
      <span id="summarySubtotal">₱0.00</span>
    </div>
    <div class="rowline">
      <span>Delivery</span>
      <span id="summaryDelivery">₱0.00</span>
    </div>

    <div class="rowline tip-row">
      <div class="tip-left">
        <span>Tip</span>
        <small>optional</small>
      </div>
      <div class="tip-buttons" id="tipButtons">
        <button class="tip-btn active" data-tip="0">0%</button>
        <button class="tip-btn" data-tip="10">10%</button>
        <button class="tip-btn" data-tip="20">20%</button>
        <button class="tip-btn" data-tip="other">Other</button>
      </div>
    </div>

    <div class="rowline total-row">
      <span class="total-label">TOTAL</span>
      <span class="total-price" id="summaryTotal">₱0.00</span>
    </div>
  </div>

  <button class="placeorder-btn" id="placeOrderBtn">Create order</button>

  <div class="coupon-link">
    <a href="#" id="editCartLink">I want to edit my order</a>
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

  <!-- order summary block -->
<div class="summary-grid-block" id="confirmSummary">
  <h3 class="summary-grid-title">Your order</h3>

  <div class="confirm-flex">
    <!-- LEFT side: items and totals -->
    <div class="confirm-left">
      <div id="confirmItems"><!-- filled by JS --></div>

      <div class="confirm-row">
        <div class="confirm-row-label">Sub-total</div>
        <div id="confirmSubtotal">₱0.00</div>
      </div>

      <div class="confirm-row">
        <div class="confirm-row-label">Tip</div>
        <div id="confirmTip">₱0.00</div>
      </div>

      <div class="confirm-row" style="font-weight:600; font-size:16px; margin-top:10px;">
        <div class="confirm-row-label" style="font-weight:600;">Total</div>
        <div id="confirmTotal">₱0.00</div>
      </div>
    </div>

    <!-- RIGHT side: order meta -->
    <div class="confirm-right">
      <div class="order-type-block">
        <div class="confirm-row-label">Order type</div>
        <div id="confirmOrderType">Pickup</div>
        <div class="order-type-extra" id="confirmOrderTime"></div>
      </div>

      <div style="margin-top:16px;">
        <div class="confirm-row-label">Payment</div>
        <div id="confirmPayment">—</div>
      </div>

      <div class="customer-block">
        <div class="confirm-row-label" style="margin-top:16px;">Customer</div>
        <div id="confirmCustomerName"></div>
        <div id="confirmCustomerPhone"></div>
        <div id="confirmCustomerEmail"></div>
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
/* ===========================
   CART + CHECKOUT RENDERING
   =========================== */

/* -- Shared helpers -- */
function getCart() {
  try {
    return JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
  } catch (e) {
    return { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
  }
}
function currency(n){ return `₱${(Number(n)||0).toFixed(2)}`; }

/* ===== READ CART (Checkout) ===== */
function renderSummaryFromCart() {
  const cart = getCart();
  const itemsEl = document.getElementById('summaryItems');
  const subtotalEl = document.getElementById('summarySubtotal');
  const deliveryEl = document.getElementById('summaryDelivery');
  const totalEl = document.getElementById('summaryTotal');

  if (!itemsEl || !subtotalEl || !deliveryEl || !totalEl) return; // safe-guard if IDs are missing

  itemsEl.innerHTML = '';

  if (!cart.items.length) {
    itemsEl.innerHTML = `<div class="summary-line"><div class="summary-line-main">Your cart is empty.</div></div>`;
    subtotalEl.textContent = currency(0);
    deliveryEl.textContent = currency(0);
    totalEl.textContent = currency(0);
    return;
  }

  cart.items.forEach(it => {
    const line = document.createElement('div');
    line.className = 'summary-line';
    line.innerHTML = `
      <div class="summary-line-main">
        <div class="summary-line-top">
          <span class="item-name">${it.qty} x ${it.name}</span>
          <span class="item-price">${currency(it.qty * it.unitPrice)}</span>
        </div>
        <div class="summary-edit">
          <a href="#" class="edit-link" data-name="${it.name}">Edit</a>
          <div class="qty-mini">
            <button class="mini-btn" data-action="plus" data-name="${it.name}">+</button>
            <button class="mini-btn" data-action="minus" data-name="${it.name}">-</button>
          </div>
        </div>
      </div>
    `;
    itemsEl.appendChild(line);
  });

  // set amounts (no tip yet)
  subtotalEl.textContent = currency(cart.subtotal || 0);
  deliveryEl.textContent = currency(cart.deliveryFee || 0);

  // default tip % is 0 if .currentTipPercent not set
  const tipPercent = window.currentTipPercent ?? 0;
  const tipValue = (cart.subtotal || 0) * (tipPercent / 100);
  totalEl.textContent = currency((cart.subtotal || 0) + (cart.deliveryFee || 0) + tipValue);

  // wire quantity +/- edits
  itemsEl.querySelectorAll('.mini-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-name');
      const action = btn.getAttribute('data-action');
      const c = getCart();
      const idx = c.items.findIndex(i => i.name === name);
      if (idx === -1) return;

      if (action === 'plus') c.items[idx].qty += 1;
      if (action === 'minus') c.items[idx].qty = Math.max(1, c.items[idx].qty - 1);

      // recompute subtotal/total
      c.subtotal = c.items.reduce((s,i)=> s + i.qty * i.unitPrice, 0);
      c.total = c.subtotal + (c.deliveryFee || 0);
      localStorage.setItem('bslh_cart', JSON.stringify(c));

      renderSummaryFromCart(); // re-render with same tipPercent
      applyTipPercent(window.currentTipPercent ?? 0);
    });
  });

  // clicking "Edit" goes back to menu to adjust
  itemsEl.querySelectorAll('.edit-link').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = "/food-ordering-system_BSLH/customer/menu.php";
    });
  });
}

function applyTipPercent(pct) {
  window.currentTipPercent = Number(pct) || 0;
  const cart = getCart();
  const totalEl = document.getElementById('summaryTotal');
  const subtotal = cart.subtotal || 0;
  const delivery = cart.deliveryFee || 0;
  const tip = subtotal * (window.currentTipPercent / 100);
  if (totalEl) totalEl.textContent = currency(subtotal + delivery + tip);
}

/* Tip buttons (checkout right panel) */
(function initTipButtons(){
  const group = document.getElementById('tipButtons');
  if (!group) return;
  group.querySelectorAll('.tip-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      group.querySelectorAll('.tip-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      if (btn.dataset.tip === 'other') {
        const val = prompt('Enter tip percent (e.g., 12 for 12%)');
        const pct = Math.max(0, Number(val || 0));
        applyTipPercent(pct);
      } else {
        applyTipPercent(Number(btn.dataset.tip || 0));
      }
    });
  });
})();

/* Edit link under summary */
const editLink = document.getElementById('editCartLink');
if (editLink) {
  editLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = "/food-ordering-system_BSLH/customer/menu.php";
  });
}

/* ===========================
   UI TOGGLES / RADIO GROUPS
   =========================== */

/* Cancel / exit back to menu */
function cancelCheckout() {
  if (confirm("Are you sure you want to leave checkout and go back to the menu?")) {
    window.location.href = "/food-ordering-system_BSLH/customer/menu.php";
  }
}

/* Highlight selected radios within their groups */
document.querySelectorAll('.split-col, .checkout-card').forEach(group => {
  const radios = group.querySelectorAll('input[type="radio"]');
  radios.forEach(radio => {
    radio.addEventListener('change', function() {
      group.querySelectorAll('.radio-block').forEach(lbl => lbl.classList.remove('selected'));
      this.closest('.radio-block').classList.add('selected');
    });
  });
});

/* Delivery vs Pickup toggle (step1) */
const radioDelivery = document.getElementById('radioDelivery');
const radioPickup   = document.getElementById('radioPickup');
const addressCard   = document.getElementById('addressCard');

function activateDelivery() {
  if (!radioDelivery || !radioPickup || !addressCard) return;
  radioDelivery.classList.add('selected');
  radioDelivery.querySelector('input').checked = true;

  radioPickup.classList.remove('selected');
  radioPickup.querySelector('input').checked = false;

  addressCard.classList.remove('hidden');
}

function activatePickup() {
  if (!radioDelivery || !radioPickup || !addressCard) return;
  radioPickup.classList.add('selected');
  radioPickup.querySelector('input').checked = true;

  radioDelivery.classList.remove('selected');
  radioDelivery.querySelector('input').checked = false;

  addressCard.classList.add('hidden');
}

if (radioDelivery) radioDelivery.addEventListener('click', activateDelivery);
if (radioPickup)   radioPickup.addEventListener('click', activatePickup);

/* ===========================
   CONFIRMATION (STEP 2)
   =========================== */

/* Render the confirmation (left and right columns) from snapshot/localStorage */
function hydrateConfirmation() {
  const last = JSON.parse(localStorage.getItem('bslh_last_order') || '{}');
  const cart = last.cart || getCart();

  const itemsEl = document.getElementById('confirmItems');
  const subEl   = document.getElementById('confirmSubtotal');
  const tipEl   = document.getElementById('confirmTip');
  const totEl   = document.getElementById('confirmTotal');

  // If confirmation block isn't on this view, just skip
  if (itemsEl && subEl && tipEl && totEl) {
    itemsEl.innerHTML = '';

    if (!cart.items || !cart.items.length) {
      itemsEl.innerHTML = `<div class="confirm-row"><div>Your cart is empty.</div><div></div></div>`;
      subEl.textContent = currency(0);
      tipEl.textContent = currency(0);
      totEl.textContent = currency(0);
    } else {
      cart.items.forEach(it => {
        const row = document.createElement('div');
        row.className = 'confirm-row';
        row.innerHTML = `
          <div>${it.qty} x&nbsp;${it.name}</div>
          <div>${currency(it.qty * it.unitPrice)}</div>
        `;
        itemsEl.appendChild(row);
      });

      const subtotal   = cart.subtotal || 0;
      const delivery   = cart.deliveryFee || 0;
      const tipValue   = Number(last.tipValue || 0);
      const grandTotal = subtotal + delivery + tipValue;

      subEl.textContent = currency(subtotal);
      tipEl.textContent = currency(tipValue);
      totEl.textContent = currency(grandTotal);
    }
  }

  // Right-side meta (guard each in case IDs are not present yet)
  const orderTypeEl = document.getElementById('confirmOrderType');
  const orderTimeEl = document.getElementById('confirmOrderTime');
  const paymentEl   = document.getElementById('confirmPayment');
  const nameEl      = document.getElementById('confirmCustomerName');
  const phoneEl     = document.getElementById('confirmCustomerPhone');
  const emailEl     = document.getElementById('confirmCustomerEmail');

  if (orderTypeEl) orderTypeEl.textContent = last.orderType || 'Pickup';
  if (orderTimeEl) orderTimeEl.innerHTML   = (last.orderTimeMsg || '').replace(/\n/g,'<br>');
  if (paymentEl)   paymentEl.textContent   = last.paymentLabel || '—';

  const fullName = [last.firstName || '', last.lastName || ''].join(' ').trim();
  if (nameEl)  nameEl.textContent  = fullName;
  if (phoneEl) phoneEl.textContent = last.phone || '';
  if (emailEl) emailEl.textContent = last.email || '';
}

/* Create order -> snapshot + show Confirmation screen (step2) */
const placeOrderBtn  = document.getElementById('placeOrderBtn');
const checkoutScreen = document.getElementById('checkoutScreen');
const confirmScreen  = document.getElementById('confirmScreen');

const step1Dot = document.getElementById('step1Dot');
const step2Dot = document.getElementById('step2Dot');

if (placeOrderBtn) {
  placeOrderBtn.addEventListener('click', function () {
    const cart = getCart();

    // tip from checkout UI
    const tipPercent = Number(window.currentTipPercent ?? 0);
    const tipValue   = (cart.subtotal || 0) * (tipPercent / 100);

    // gather payment / order type / when
    const paymentLabel = document.querySelector('input[name="payment_method"]:checked')
      ?.closest('.radio-left')?.querySelector('span')?.textContent?.trim() || '';

    const orderType = (document.querySelector('input[name="ordertype"]:checked')?.value === 'delivery')
      ? 'Delivery' : 'Pickup';

    const whenVal = document.querySelector('input[name="when"]:checked')?.value || 'asap';
    const orderTimeMsg = (whenVal === 'asap') ? 'As soon as possible' : 'For your chosen time';

    // contact fields
    const firstName = document.querySelector('input[placeholder="first name"]')?.value || '';
    const lastName  = document.querySelector('input[placeholder="last name"]')?.value  || '';
    const phone     = document.querySelector('input[placeholder="+63 956 244 6616"]')?.value || '';
    const email     = document.querySelector('input[type="email"]')?.value || '';

    // snapshot
    const snapshot = { cart, tipPercent, tipValue, paymentLabel, orderType, orderTimeMsg, firstName, lastName, phone, email };
    localStorage.setItem('bslh_last_order', JSON.stringify(snapshot));

    // hide checkout form, show confirmation
    if (checkoutScreen) checkoutScreen.classList.add('hidden');
    if (confirmScreen)  confirmScreen.classList.remove('hidden');

    // progress dots
    if (step1Dot) { step1Dot.classList.add('done'); step1Dot.classList.remove('active'); }
    if (step2Dot) step2Dot.classList.add('active');

    // render confirmation now
    hydrateConfirmation();
  });
}

/* Boot */
document.addEventListener('DOMContentLoaded', function () {
  renderSummaryFromCart();   // fills the right summary on step 1
  // default tip 0% active already

  // If user reloads while already on confirmation (step 2), render it
  if (confirmScreen && !confirmScreen.classList.contains('hidden')) {
    hydrateConfirmation();
  }
});
</script>


</body>
</html>
