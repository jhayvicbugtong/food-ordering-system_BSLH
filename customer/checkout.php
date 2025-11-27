<?php
// customer/checkout.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php'; // For auth check

// Redirect to login if not a customer
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $BASE_URL = preg_replace('#/customer(/.*)?$#', '', $scriptDir);
    if ($BASE_URL === '/') $BASE_URL = '';
    
    $next = $BASE_URL . '/customer/checkout.php';
    header('Location: '. $BASE_URL . '/customer/auth/login.php?next=' . urlencode($next));
    exit;
}

// Pre-fill user data from session
$customer_first = $_SESSION['name'] ?? '';
$customer_email = $_SESSION['email'] ?? '';
$customer_id = $_SESSION['user_id'] ?? 0;
$customer_last = '';
$customer_phone = '';

if ($customer_id) {
    $stmt = $conn->prepare("SELECT phone, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($user_data = $res->fetch_assoc()) {
        $customer_phone = $user_data['phone'] ?? '';
        $customer_last = $user_data['last_name'] ?? '';
    }
    $stmt->close();
}

// --- Fetch deliverable barangays AND their fees ---
$barangay_fees_php = []; // This will be the JS map: 'wawa' => 20.00
$barangays_query = $conn->query("SELECT barangay_name, delivery_fee FROM deliverable_barangays WHERE is_active = 1 ORDER BY barangay_name ASC");
if ($barangays_query) {
    while ($row = $barangays_query->fetch_assoc()) {
        $barangay_fees_php[strtolower($row['barangay_name'])] = (float)$row['delivery_fee'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Checkout | Bente Sais Lomi House</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/checkout.css">
  <style>
    /* ... (CSS styles are unchanged) ... */
    .input-error { border-color:#EF4444 !important; outline-color:#EF4444 !important; }
    .input-err-msg { color:#B91C1C; font-size:12px; margin-top:6px; }
    .field-wrap { display:flex; flex-direction:column; }
    .placeorder-btn.loading { cursor: not-allowed; opacity: 0.7; }
    .spinner {
      width: 1.2rem; height: 1.2rem; border: 2px solid #fff;
      border-bottom-color: transparent; border-radius: 50%;
      display: inline-block; animation: rotation 1s linear infinite;
      margin-right: 8px; vertical-align: middle; margin-top: -3px;
    }
    @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
  
    .location-btn.loading { cursor: not-allowed; opacity: 0.7; }
    .location-btn .spinner-border-sm {
      width: 1rem; height: 1rem; border-width: .2em; margin-right: 0.5rem;
    }
  </style>
</head>
<body class="checkout-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="checkout-progress-bar" id="progressBar">
  <div class="cstep-container">
    <div class="cstep active" id="step1Dot"><div class="cstep-num">1</div><div class="cstep-label">Order</div></div>
    <div class="cstep" id="step2Dot"><div class="cstep-num">2</div><div class="cstep-label">Confirmation</div></div>
  </div>
  <button class="checkout-close-btn" type="button" onclick="cancelCheckout()"><i class="bi bi-x-lg"></i></button>
</div>

<main class="checkout-layout" id="checkoutScreen">
  <form class="checkout-left" id="checkoutForm">
    <div class="checkout-card">
      <h2 class="checkout-block-title">Payment</h2>
      <label class="radio-block selected" id="rbPayMongo">
        <div class="radio-left">
          <input type="radio" name="payment_method" value="paymongo" checked>
          <span>Pay Online (GCash, Card, etc.)</span>
        </div>
        <div class="radio-right"><i class="bi bi-credit-card"></i></div>
      </label>
      <label class="radio-block" id="rbCash">
        <div class="radio-left">
          <input type="radio" name="payment_method" value="cash">
          <span>Cash on Delivery / Pickup</span>
        </div>
        <div class="radio-right"><i class="bi bi-cash-stack"></i></div>
      </label>
    </div>

    <div class="checkout-card split-two">
      <div class="split-col">
        <h2 class="checkout-block-title">Order type</h2>
        <label class="radio-block small selected" id="radioDelivery">
          <div class="radio-left"><input type="radio" name="ordertype" value="delivery" checked><span>Delivery</span></div>
        </label>
        <label class="radio-block small" id="radioPickup">
          <div class="radio-left"><input type="radio" name="ordertype" value="pickup"><span>Pickup</span></div>
        </label>
      </div>
      <div class="split-col">
        <h2 class="checkout-block-title">When</h2>
        <label class="radio-block small selected">
          <div class="radio-left"><input type="radio" name="when" value="asap" checked><span>As soon as possible</span></div>
        </label>
        <label class="radio-block small">
          <div class="radio-left"><input type="radio" name="when" value="specific"><span>Specific time</span></div>
        </label>
      </div>
    </div>
    
    <div class="checkout-card" id="addressCard">
      <div class="checkout-card-head">
        <h2 class="checkout-block-title">Address</h2>
        <button class="location-btn" type="button" id="useLocationBtn">
          <i class="bi bi-crosshair"></i><span>Use my location</span>
        </button>
      </div>

      <div class="form-row two-col">
        <div class="col field-wrap">
            <label class="form-label">Street *</label>
            <input class="form-input" type="text" name="street" placeholder="Street / purok / house no.">
        </div>
        <div class="col field-wrap">
            <label class="form-label">Barangay *</label>
            <input class="form-input" type="text" name="barangay" id="barangayInput" placeholder="e.g., Wawa, Bucana" required>
        </div>
      </div>

      <div class="form-row two-col">
         <div class="col field-wrap">
            <label class="form-label">City *</label>
            <input class="form-input" type="text" name="city" placeholder="e.g., Nasugbu">
        </div>
         <div class="col field-wrap">
            <label class="form-label">Province *</label>
            <input class="form-input" type="text" name="province" placeholder="e.g., Batangas">
        </div>
      </div>

       <div class="form-row two-col">
        <div class="col field-wrap">
            <label class="form-label">Apt. / Landmark</label>
            <input class="form-input" type="text" name="apt_landmark" placeholder="Apartment, landmark, etc.">
        </div>
        <div class="col field-wrap">
            <label class="form-label">Floor number</label>
            <input class="form-input" type="text" name="floor_number" placeholder="Optional">
        </div>
      </div>
    </div>
    <div class="checkout-card">
      <h2 class="checkout-block-title">Contact</h2>
      <div class="form-row two-col">
        <div class="col field-wrap"><label class="form-label">First name *</label><input class="form-input" type="text" name="first_name" placeholder="first name" value="<?php echo htmlspecialchars($customer_first); ?>"></div>
        <div class="col field-wrap"><label class="form-label">Last name *</label><input class="form-input" type="text" name="last_name" placeholder="last name" value="<?php echo htmlspecialchars($customer_last ?? ''); ?>"></div>
      </div>
      <div class="form-row two-col">
        <div class="col field-wrap"><label class="form-label">Phone *</label><input class="form-input" type="text" name="phone" placeholder="+63 956 244 6616" value="<?php echo htmlspecialchars($customer_phone ?? ''); ?>"></div>
        <div class="col field-wrap"><label class="form-label">Email *</label><input class="form-input" type="email" name="email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($customer_email); ?>"></div>
      </div>
      <div class="form-row field-wrap"><label class="form-label">Order notes (optional)</label><textarea class="form-input" name="order_notes" rows="3" placeholder="Ex: no chili, call before arriving"></textarea></div>
    </div>
  </form>

  <aside class="checkout-right">
    <h2 class="summary-title">Your order</h2>
    <div class="summary-items" id="summaryItems"></div>
    <div class="summary-subrows">
      <div class="rowline"><span>Sub-total</span><span id="summarySubtotal">₱0.00</span></div>
      <div class="rowline"><span>Delivery</span><span id="summaryDelivery">₱0.00</span></div>
      <div class="rowline tip-row">
        <div class="tip-left"><span>Tip</span><small>optional</small></div>
        <div class="tip-buttons" id="tipButtons">
          <button class="tip-btn active" data-tip="0">0%</button>
          <button class="tip-btn" data-tip="10">10%</button>
          <button class="tip-btn" data-tip="15">15%</button>
          <button class="tip-btn" data-tip="other">Other</button>
        </div>
      </div>
      <div class="rowline total-row"><span class="total-label">TOTAL</span><span class="total-price" id="summaryTotal">₱0.00</span></div>
    </div>
    <button class="placeorder-btn" id="placeOrderBtn" type="button">Create order</button>
    <div id="formError" style="display:none; text-align:center; font-size:13px; color:#B91C1C; margin-bottom:10px;"></div>
    <div class="coupon-link"><a href="<?php echo htmlspecialchars($MENU); ?>" id="editCartLink">I want to edit my order</a></div>
  </aside>
</main>


<section class="confirm-wrapper hidden" id="confirmScreen">
  <div class="confirm-panel">
    <div class="confirm-icon">✅</div>
    <div class="confirm-main">
      <h2 id="confirmTitle">Order Placed!</h2>
      <p id="confirmSubtitle">Your order #<span id="confirmOrderNumber"></span> has been placed. Please wait for the store to confirm.</p>
    </div>
  </div>
  <div class="summary-grid-block" id="confirmSummary">
    <h3 class="summary-grid-title">Your order</h3>
    <div class="confirm-flex">
      <div class="confirm-left">
        <div id="confirmItems"></div>
        <div class="confirm-row"><div class="confirm-row-label">Sub-total</div><div id="confirmSubtotal">₱0.00</div></div>
        <div class="confirm-row"><div class="confirm-row-label">Delivery</div><div id="confirmDelivery">₱0.00</div></div>
        <div class="confirm-row"><div class="confirm-row-label">Tip</div><div id="confirmTip">₱0.00</div></div>
        <div class="confirm-row" style="font-weight:600; font-size:16px; margin-top:10px;"><div class="confirm-row-label" style="font-weight:600;">Total</div><div id="confirmTotal">₱0.00</div></div>
      </div>
      <div class="confirm-right">
        <div class="order-type-block">
          <div class="confirm-row-label">Order type</div>
          <div id="confirmOrderType">Pickup</div>
          <div class="order-type-extra" id="confirmOrderTime"></div>
        </div>
        <div style="margin-top:16px;"><div class="confirm-row-label">Payment</div><div id="confirmPayment">—</div></div>
        <div class="customer-block">
          <div class="confirm-row-label" style="margin-top:16px;">Customer</div>
          <div id="confirmCustomerName"></div>
          <div id="confirmCustomerPhone"></div>
          <div id="confirmCustomerEmail"></div>
        </div>
      </div>
    </div>
    <div class="light-blue-box">
      <div class="bubble-icon">i</div>
      <div>
        We've received your order. We'll send updates to your email and phone.
        <br><br>
        <a href="<?php echo htmlspecialchars($MENU); ?>" class="btn btn-sm btn-success" style="color:white; font-weight:600;">Back to Menu</a>
      </div>
    </div>
  </div>
</section>

<?php 
// *** MODAL IS NOW INCLUDED ***
include __DIR__ . '/includes/footer.php'; 
include __DIR__ . '/includes/delivery_time_modal.php'; 
?>

<script>
  // --- START OF SCRIPT ---
  
  // =================================================================
  // !! PASTE YOUR OPENCAGE API KEY HERE !!
  // =================================================================
  const OPENCAGE_API_KEY = "c8efbf57adb44ed195a99bffec41260d"; // <-- REPLACE THIS
  // =================================================================
  
  
  // =================================================================
  // !! THIS MAP IS NOW POWERED BY THE DATABASE !!
  // =================================================================
  // Example: { 'wawa': 20.00, 'bucana': 20.00, 'poblacion': 0.00 }
  const BARANGAY_FEES_MAP = <?php echo json_encode($barangay_fees_php); ?>;
  // This array is just for validation: [ 'wawa', 'bucana', 'poblacion' ]
  const ALLOWED_DELIVERY_BARANGAYS = Object.keys(BARANGAY_FEES_MAP);
  // =================================================================


  /* ===========================
   SHARED HELPERS
   =========================== */
  function getCart() {
    try {
      const cart = JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
      if (!Array.isArray(cart.items)) {
        cart.items = [];
      }
      cart.items = cart.items.filter(item => item && (item.id !== null && item.id !== undefined));
      cart.deliveryFee = Number(cart.deliveryFee) || 0;
      return cart;
    } catch (e) {
      return { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; 
    }
  }
  function saveCart(cart) { localStorage.setItem('bslh_cart', JSON.stringify(cart)); }
  function currency(n) { return `₱${(Number(n) || 0).toFixed(2)}`; }
  
  function h(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  const STORE_OPEN_24  = "08:00";
  const STORE_CLOSE_24 = "22:00";
  const LEAD_MINUTES   = 15;

  // Element cache
  const elements = {
    // ... (all other elements are unchanged) ...
    placeOrderBtn: document.getElementById('placeOrderBtn'),
    checkoutScreen: document.getElementById('checkoutScreen'),
    confirmScreen: document.getElementById('confirmScreen'),
    checkoutForm: document.getElementById('checkoutForm'),
    formError: document.getElementById('formError'),
    step1Dot: document.getElementById('step1Dot'),
    step2Dot: document.getElementById('step2Dot'),
    addressCard: document.getElementById('addressCard'),
    summaryItems: document.getElementById('summaryItems'),
    summarySubtotal: document.getElementById('summarySubtotal'),
    summaryDelivery: document.getElementById('summaryDelivery'),
    summaryTotal: document.getElementById('summaryTotal'),
    tipButtons: document.getElementById('tipButtons'),
    editCartLink: document.getElementById('editCartLink'),
    timeModalOverlay: document.getElementById('timeModalOverlay'),
    timeCloseBtn: document.getElementById('timeCloseBtn'),
    timeConfirmBtn: document.getElementById('timeConfirmBtn'),
    timeDateInput: document.getElementById('timeDate'),
    timeTimeInput: document.getElementById('timeTime'),
    timeError: document.getElementById('timeError'),
    timeNote: document.getElementById('timeNote'),
    useLocationBtn: document.getElementById('useLocationBtn') 
  };

  let currentTipPercent = 0;
  let isSubmitting = false;
  
  let timeModalInstance = null;

  /* ===========================
   PAGE INITIALIZATION
   =========================== */
  document.addEventListener('DOMContentLoaded', function () {
    if ((getCart().items || []).length === 0) {
      alert("Your cart is empty. Redirecting to menu.");
      window.location.href = "<?php echo htmlspecialchars($MENU); ?>";
      return;
    }

    if (elements.timeModalOverlay) {
        timeModalInstance = new bootstrap.Modal(elements.timeModalOverlay);
    }
    
    const cart = getCart();
    cart.deliveryFee = 0;
    saveCart(cart);
    
    renderSummaryFromCart();
    initRadioHighlights();
    initTipButtons();
    initTimeModal();
    initPlaceOrderButton();
    initUseLocationButton(); 
    initDeliveryFeeCheck();
    
    if (elements.editCartLink) {
        elements.editCartLink.href = "<?php echo htmlspecialchars($MENU); ?>";
    }
  });

  /* ===========================
   SUMMARY & TIP LOGIC
   =========================== */
  // ... (renderSummaryFromCart, initTipButtons, updateTotals functions are unchanged) ...
    function renderSummaryFromCart() {
    const cart = getCart();
    if (!elements.summaryItems) return;
    elements.summaryItems.innerHTML = '';
    let subtotal = 0;
    const validItems = cart.items.filter(item => item && (item.id !== null && item.id !== undefined));
    if (validItems.length === 0) {
      cart.subtotal = 0;
      saveCart(cart);
      updateTotals();
      return;
    }
    validItems.forEach(it => { 
      const lineTotal = (it.unitPrice || 0) * (it.qty || 0);
      subtotal += lineTotal;
      const line = document.createElement('div');
      line.className = 'summary-line';
      line.innerHTML = `
        <div class="summary-line-main">
          <div class="summary-line-top">
            <span class="item-name">${it.qty} x ${it.name}</span>
            <span class="item-price">${currency(lineTotal)}</span>
          </div>
        </div>
      `;
      elements.summaryItems.appendChild(line);
    });
    cart.subtotal = subtotal;
    saveCart(cart); 
    updateTotals();
  }

  function initTipButtons() {
    if (!elements.tipButtons) return;
    elements.tipButtons.querySelectorAll('.tip-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        elements.tipButtons.querySelectorAll('.tip-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (btn.dataset.tip === 'other') {
          const val = prompt('Enter tip percent (e.g., 12 for 12%)');
          currentTipPercent = Math.max(0, Number(val || 0));
        } else {
          currentTipPercent = Number(btn.dataset.tip || 0);
        }
        updateTotals();
      });
    });
  }

  function updateTotals() {
    const cart = getCart();
    const subtotal = cart.subtotal || 0;
    const delivery = cart.deliveryFee || 0; 
    const tipValue = subtotal * (currentTipPercent / 100);
    const total = subtotal + delivery + tipValue;

    if (elements.summarySubtotal) elements.summarySubtotal.textContent = currency(subtotal);
    if (elements.summaryDelivery) elements.summaryDelivery.textContent = currency(delivery);
    if (elements.summaryTotal) elements.summaryTotal.textContent = currency(total);
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'paymongo';
    if (elements.placeOrderBtn && !isSubmitting) {
      if (paymentMethod === 'paymongo') {
        elements.placeOrderBtn.innerHTML = `Proceed to Pay ${currency(total)}`;
      } else {
        elements.placeOrderBtn.innerHTML = `Place Order ${currency(total)}`;
      }
    }
  }

  /* ===========================
   UI TOGGLES (MODALS, RADIOS)
   =========================== */
  // ... (cancelCheckout, initRadioHighlights, bindRadioGroupByName functions are unchanged) ...
    function cancelCheckout() {
    if (confirm("Are you sure you want to leave checkout and go back to the menu?")) {
      window.location.href = "<?php echo htmlspecialchars($MENU); ?>";
    }
  }

  function initRadioHighlights() {
    bindRadioGroupByName('payment_method', { afterUpdate: () => updateTotals() });
    bindRadioGroupByName('ordertype', {
      afterUpdate: () => {
        const selected = document.querySelector(`input[name="ordertype"]:checked`);
        if (elements.addressCard) {
          elements.addressCard.classList.toggle('hidden', !selected || selected.value !== 'delivery');
        }
        if (selected.value === 'pickup') {
            const cart = getCart();
            cart.deliveryFee = 0;
            saveCart(cart);
            updateTotals();
        }
      }
    });
    bindRadioGroupByName('when');
  }

  function bindRadioGroupByName(name, options = {}) {
    const radios = Array.from(document.querySelectorAll(`input[type="radio"][name="${name}"]`));
    if (!radios.length) return;
    const update = () => {
      radios.forEach(r => {
        r.closest('.radio-block')?.classList.toggle('selected', r.checked);
      });
      if (options.afterUpdate) options.afterUpdate();
    };
    radios.forEach(r => {
      r.addEventListener('change', update);
      const block = r.closest('.radio-block');
      if (block) block.addEventListener('click', () => {
        if (!r.checked) {
          r.checked = true;
          r.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    });
    update();
  }
  
  /* ===========================
   TIME PICKER MODAL
   =========================== */
  // ... (initTimeModal function is unchanged) ...
    function initTimeModal() {
    function pad(n){ return String(n).padStart(2,'0'); }
    function tzOffsetISO(date){
      const off = -date.getTimezoneOffset();
      const sign = off >= 0 ? '+' : '-';
      const hh = pad(Math.floor(Math.abs(off)/60));
      const mm = pad(Math.abs(off)%60);
      return `${sign}${hh}:${mm}`;
    }
    function setMinDateToday(){
      const now = new Date();
      const y = now.getFullYear(), m = pad(now.getMonth()+1), d = pad(now.getDate());
      elements.timeDateInput.min = `${y}-${m}-${d}`;
      if (!elements.timeDateInput.value) elements.timeDateInput.value = `${y}-${m}-${d}`;
    }
    function defaultRoundedTimePlusLead(){
      const now = new Date(Date.now() + LEAD_MINUTES*60000);
      const roundedMs = Math.ceil(now.getTime()/(5*60000))*(5*60000);
      const rounded = new Date(roundedMs);
      elements.timeTimeInput.value = `${pad(rounded.getHours())}:${pad(rounded.getMinutes())}`;
    }
    function clampToStoreHours(dateObj){
      const [oh, om] = STORE_OPEN_24.split(':').map(Number);
      const [ch, cm] = STORE_CLOSE_24.split(':').map(Number);
      const d = new Date(dateObj);
      const open = new Date(d); open.setHours(oh, om, 0, 0);
      const close= new Date(d); close.setHours(ch, cm, 59, 999);
      return d >= open && d <= close;
    }
    function applyLeadTimeIfToday(dateStr, timeStr){
      const now = new Date();
      const selected = new Date(`${dateStr}T${timeStr}:00`);
      const minAllowed = new Date(now.getTime() + LEAD_MINUTES*60000);
      const today = now.toISOString().slice(0,10);
      if (dateStr === today && selected < minAllowed) {
        const roundedMs = Math.ceil(minAllowed.getTime()/(5*60000))*(5*60000);
        const r = new Date(roundedMs);
        return `${pad(r.getHours())}:${pad(r.getMinutes())}`;
      }
      return timeStr;
    }
    function showTimeError(msg){ elements.timeError.style.display='block'; elements.timeError.textContent = msg; }
    function clearTimeError(){ elements.timeError.style.display='none'; elements.timeError.textContent=''; }
    
    function openTimeModal(){
      if (!timeModalInstance) return;
      setMinDateToday();
      defaultRoundedTimePlusLead();
      clearTimeError();
      timeModalInstance.show();
    }
    function closeTimeModal(){ 
      if (timeModalInstance) timeModalInstance.hide();
    }
    
    document.querySelectorAll('.time-chip').forEach(chip=>{
      chip.addEventListener('click', ()=>{
        const mins = Number(chip.dataset.mins || 0);
        const base = new Date();
        const t = new Date(base.getTime() + Math.max(mins, LEAD_MINUTES)*60000);
        const y = t.getFullYear(), m = pad(t.getMonth()+1), d = pad(t.getDate());
        elements.timeDateInput.value = `${y}-${m}-${d}`;
        elements.timeTimeInput.value = `${pad(t.getHours())}:${pad(t.getMinutes())}`;
        clearTimeError();
      });
    });

    if (elements.timeConfirmBtn) {
      elements.timeConfirmBtn.addEventListener('click', ()=>{
        clearTimeError();
        const d = elements.timeDateInput.value;
        const t = elements.timeTimeInput.value;
        if (!d || !t) { showTimeError('Please choose both date and time.'); return; }
        const adj = applyLeadTimeIfToday(d, t);
        if (adj !== t) elements.timeTimeInput.value = adj;
        const selected = new Date(`${d}T${elements.timeTimeInput.value}:00`);
        if (!clampToStoreHours(selected)) {
          showTimeError(`Selected time is outside store hours (${STORE_OPEN_24}–${STORE_CLOSE_24}).`);
          return;
        }
        const payload = {
          type: 'scheduled',
          iso: `${d}T${elements.timeTimeInput.value}:00${tzOffsetISO(selected)}`,
          readable: selected.toLocaleString([], { dateStyle:'medium', timeStyle:'short' })
        };
        localStorage.setItem('bslh_delivery_time', JSON.stringify(payload));
        closeTimeModal();
      });
    }

    const radios = document.querySelectorAll('input[type="radio"][name="when"]');
    radios.forEach(r => {
      const block = r.closest('.radio-block');
      if (block) block.addEventListener('click', (e) => {
        if (r.value === 'specific' && r.checked) { e.preventDefault(); openTimeModal(); }
        else if (r.value === 'specific' && !r.checked) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); openTimeModal(); }
        else if (r.value === 'asap') { localStorage.removeItem('bslh_delivery_time'); }
      });
    });
  }

  // --- MODIFICATION: Updated to handle the <input> element ---
  function initDeliveryFeeCheck() {
    const barangayInput = document.getElementById('barangayInput'); // <-- Use new ID
    if (barangayInput) {
      barangayInput.addEventListener('change', updateDeliveryFee); // 'change' fires on blur
    }
  }
  
  function updateDeliveryFee() {
    const barangayInput = document.getElementById('barangayInput'); // <-- Use new ID
    const barangayVal = barangayInput.value.trim().toLowerCase();
    
    // Look up the fee from our database-powered map
    const fee = BARANGAY_FEES_MAP[barangayVal] ?? 0;

    const cart = getCart();
    cart.deliveryFee = fee;
    saveCart(cart);
    updateTotals();
  }
  // --- END MODIFICATION ---

  /* ===========================
   LOCATION BUTTON LOGIC
   =========================== */
  function initUseLocationButton() {
    // ... (this function is unchanged) ...
    if (!elements.useLocationBtn) return;
    if (OPENCAGE_API_KEY === 'YOUR_OPENCAGE_API_KEY' || !OPENCAGE_API_KEY) {
        setUseLocationButtonState('error', 'API Key is missing.');
        elements.useLocationBtn.disabled = true;
        return;
    }
    if (!navigator.geolocation) {
      setUseLocationButtonState('error', 'Geolocation is not supported.');
      elements.useLocationBtn.disabled = true;
      return;
    }
    elements.useLocationBtn.addEventListener('click', handleLocationClick);
  }

  function handleLocationClick() {
    setUseLocationButtonState('loading');
    clearAllErrors(); // <-- Clear old validation errors
    navigator.geolocation.getCurrentPosition(handleLocationSuccess, handleLocationError, {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    });
  }

  function handleLocationSuccess(position) {
    // ... (this function is unchanged) ...
    const { latitude, longitude } = position.coords;
    reverseGeocode(latitude, longitude);
  }

  function handleLocationError(error) {
    // ... (this function is unchanged) ...
    let msg = 'Could not get location. Please enter manually.';
    if (error.code === error.PERMISSION_DENIED) {
      msg = 'You denied location access. Please enter manually.';
    } else if (error.code === error.POSITION_UNAVAILABLE) {
      msg = 'Location information is unavailable. Please enter manually.';
    } else if (error.code === error.TIMEOUT) {
      msg = 'Getting location timed out. Please enter manually.';
    }
    showAllErrors([{ el: elements.useLocationBtn, msg: msg }]);
    setUseLocationButtonState('default');
  }
  
  // --- MODIFICATION: This function now validates the barangay immediately ---
  async function reverseGeocode(lat, lng) {
    const url = `https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lng}&key=${OPENCAGE_API_KEY}&language=en&countrycode=ph&pretty=1`;
    
    const barangayInput = document.getElementById('barangayInput');
    const streetInput = document.querySelector('input[name="street"]');
    const cityInput = document.querySelector('input[name="city"]');
    const provinceInput = document.querySelector('input[name="province"]');
    
    try {
      const response = await fetch(url);
      const data = await response.json();

      if (data.status.code === 200 && data.results.length > 0) {
        const address = parseOpenCageResponse(data.results[0].components);
        const parsedBarangayName = address.barangay.toLowerCase();

        // --- START OF NEW LOGIC ---
        // 1. ALWAYS fill the fields, no matter what.
        streetInput.value = address.street;
        cityInput.value = address.city || '';
        provinceInput.value = address.province || '';
        barangayInput.value = address.barangay; // Use the original cased name
  
        // 2. Manually trigger the 'change' event on the barangay input.
        // This will automatically call our existing `updateDeliveryFee()` function.
        barangayInput.dispatchEvent(new Event('change'));
  
        // 3. Now, check if the found barangay is valid.
        if (BARANGAY_FEES_MAP[parsedBarangayName] !== undefined) {
          // SUCCESS: We deliver here.
          setUseLocationButtonState('success');
        } else {
          // FAILURE: Barangay not in our list.
          let errorMsg = `Sorry, we do not deliver to '${h(address.barangay)}'.`;
          if (!address.barangay) {
            errorMsg = "Could not find a valid barangay for your location. Please select manually.";
          }
          // Show the error right next to the input
          showAllErrors([{ el: barangayInput, msg: errorMsg }]);
          // Show error on the button itself
          setUseLocationButtonState('error', 'Out of delivery area');
        }
        // --- END OF NEW LOGIC ---

      } else {
        throw new Error(data.status.message || 'No results found');
      }
    } catch (error) {
      console.error('Reverse geocoding failed:', error);
      showAllErrors([{ el: elements.useLocationBtn, msg: 'Could not find a valid address. Please enter manually.' }]);
      setUseLocationButtonState('default');
    }
  }
  
   function parseOpenCageResponse(components) {
    // ... (this function is unchanged) ...
    let streetParts = [];
    if (components.road) streetParts.push(components.road);
    if (components.house_number) streetParts.unshift(components.house_number);
    if (components.neighbourhood) streetParts.push(components.neighbourhood);

    let barangay = components.suburb || components.village || '';
    let city = components.city || components.town || components.city_district || '';
    let province = components.state || ''; // OpenCage uses 'state' for province/region

    return {
      street: streetParts.join(', ') || '',
      barangay: barangay,
      city: city,
      province: province
    };
  }
  
  function setUseLocationButtonState(state, text = '') {
    // ... (this function is unchanged) ...
    if (!elements.useLocationBtn) return;
    elements.useLocationBtn.disabled = false;
    elements.useLocationBtn.classList.remove('loading');

    const icon = elements.useLocationBtn.querySelector('i');
    const span = elements.useLocationBtn.querySelector('span');
    const spinner = elements.useLocationBtn.querySelector('.spinner-border-sm');
    if (spinner) spinner.remove();

    if (state === 'loading') {
      elements.useLocationBtn.disabled = true;
      elements.useLocationBtn.classList.add('loading');
      icon.style.display = 'none';
      elements.useLocationBtn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
      span.textContent = 'Getting location...';
    } else {
      icon.style.display = 'inline-block';
      if (state === 'success') {
        span.textContent = 'Location Found!';
      } else if (state === 'error') {
        span.textContent = text || 'Error';
      } else { // default
        span.textContent = 'Use my location';
      }
      
      if (state === 'success' || state === 'error') {
        setTimeout(() => {
          setUseLocationButtonState('default');
        }, 3000);
      }
    }
  }

  /* ===========================
   VALIDATION & ORDER SUBMISSION
   =========================== */
  // ... (initPlaceOrderButton and setLoading are unchanged) ...
  function initPlaceOrderButton() {
    if (!elements.placeOrderBtn) return;
    
    localStorage.removeItem('bslh_pending_order');

    elements.placeOrderBtn.addEventListener('click', async function(e) {
      e.preventDefault();
      if (isSubmitting) return;

      setLoading(true);
      clearAllErrors();
      
      const validationErrors = validateForm();
      if (validationErrors.length > 0) {
        showAllErrors(validationErrors);
        setLoading(false);
        return;
      }
      
      const orderData = gatherOrderData();
      const paymentMethod = orderData.orderDetails.paymentMethod;
      
      localStorage.setItem('bslh_pending_order', JSON.stringify(orderData));

      const endpoint = (paymentMethod === 'cash')
        ? 'actions/place_order_cash.php'
        : 'actions/create_payment_session.php';

      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(orderData)
        });

        const responseText = await response.text();
        if (!response.ok) {
            let serverMsg = 'Check server logs.';
            try {
                const errJson = JSON.parse(responseText);
                serverMsg = errJson.message || responseText;
            } catch(e) { /* ignore */ }
            throw new Error(`Server error: ${response.statusText}. ${serverMsg}`);
        }

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error("Failed to parse JSON response:", responseText);
            throw new Error(`Server returned an invalid response. Check PHP error logs.`);
        }

        if (result.success) {
          if (paymentMethod === 'paymongo' && result.checkoutUrl) {
            window.location.href = result.checkoutUrl;
          } else if (paymentMethod === 'cash') {
            localStorage.removeItem('bslh_cart');
            localStorage.removeItem('bslh_pending_order');
            hydrateConfirmation(orderData, result.orderNumber);
            showConfirmationScreen();
          }
        } else {
          localStorage.removeItem('bslh_pending_order');
          showAllErrors([{ el: null, msg: result.message || 'An unknown error occurred.' }]);
          setLoading(false);
        }

      } catch (error) {
        localStorage.removeItem('bslh_pending_order');
        showAllErrors([{ el: null, msg: `Network or Server Error: ${error.message}` }]);
        setLoading(false);
      }
    });
  }

  function setLoading(isLoading) {
    // ... (this function is unchanged) ...
    isSubmitting = isLoading;
    if (elements.placeOrderBtn) {
      if (isLoading) {
        elements.placeOrderBtn.classList.add('loading');
        elements.placeOrderBtn.innerHTML = `<span class="spinner"></span> Processing...`;
        elements.placeOrderBtn.disabled = true;
      } else {
        elements.placeOrderBtn.classList.remove('loading');
        elements.placeOrderBtn.disabled = false;
        updateTotals();
      }
    }
  }
  
  // --- MODIFICATION: VALIDATION NOW USES THE DB-POWERED MAP ---
  function validateForm() {
    const errors = [];
    const formData = new FormData(elements.checkoutForm);
    const emailOk = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim());
    const phoneOk = (v) => String(v || '').replace(/[^\d]/g, '').length >= 10;
    
    if (formData.get('ordertype') === 'delivery') {
      const streetEl = document.querySelector('input[name="street"]');
      if (!formData.get('street')?.trim()) errors.push({ el: streetEl, msg: 'Street is required for delivery.' });
      
      // --- BARANGAY CHECK (FROM DB MAP) ---
      const barangayEl = document.getElementById('barangayInput'); // <-- Use new ID
      const barangayVal = barangayEl.value.trim().toLowerCase(); 
      
      if (!barangayVal) {
          errors.push({ el: barangayEl, msg: 'Barangay is required for delivery.' });
      } else if (BARANGAY_FEES_MAP[barangayVal] === undefined) {
          // Check if the key exists in our map
          errors.push({ el: barangayEl, msg: 'Sorry, we do not deliver to this barangay.' });
      }
      // --- END BARANGAY CHECK ---

      const cityEl = document.querySelector('input[name="city"]');
      const cityVal = formData.get('city')?.trim().toLowerCase();
      if (!cityVal) {
          errors.push({ el: cityEl, msg: 'City is required for delivery.' });
      } else if (cityVal.includes('nasugbu') === false) { // A bit more flexible
          errors.push({ el: cityEl, msg: 'Sorry, we only deliver within Nasugbu.' });
      }
      
      const provinceEl = document.querySelector('input[name="province"]');
      if (!formData.get('province')?.trim()) errors.push({ el: provinceEl, msg: 'Province is required for delivery.' });
    }
    
    // ... (rest of the validation) ...
    const firstNameEl = document.querySelector('input[name="first_name"]');
    if (!formData.get('first_name')?.trim()) errors.push({ el: firstNameEl, msg: 'First name is required.' });
    const lastNameEl = document.querySelector('input[name="last_name"]');
    if (!formData.get('last_name')?.trim()) errors.push({ el: lastNameEl, msg: 'Last name is required.' });
    const phoneEl = document.querySelector('input[name="phone"]');
    const phoneVal = formData.get('phone')?.trim();
    if (!phoneVal) errors.push({ el: phoneEl, msg: 'Phone is required.' });
    else if (!phoneOk(phoneVal)) errors.push({ el: phoneEl, msg: 'Please enter a valid phone number.' });
    const emailEl = document.querySelector('input[name="email"]');
    const emailVal = formData.get('email')?.trim();
    if (!emailVal) errors.push({ el: emailEl, msg: 'Email is required.' });
    else if (!emailOk(emailVal)) errors.push({ el: emailEl, msg: 'Please enter a valid email address.' });
    if (formData.get('when') === 'specific' && !localStorage.getItem('bslh_delivery_time')) {
      errors.push({ el: document.querySelector('input[name="when"][value="specific"]'), msg: 'Please select a specific time.'});
      openTimeModal();
    }
    return errors;
  }
  
  // ... (gatherOrderData is unchanged) ...
  function gatherOrderData() {
    const cart = getCart();
    const formData = new FormData(elements.checkoutForm);
    const subtotal = cart.subtotal || 0;
    const deliveryFee = cart.deliveryFee || 0;
    const tipAmount = subtotal * (currentTipPercent / 100);
    const totalAmount = subtotal + deliveryFee + tipAmount;
    const when = formData.get('when');
    const scheduledTime = (when === 'specific') ? JSON.parse(localStorage.getItem('bslh_delivery_time')) : null;
    const floor = formData.get('floor_number')?.trim();
    const landmark = formData.get('apt_landmark')?.trim();

    return {
      cartItems: cart.items,
      orderDetails: {
        firstName: formData.get('first_name')?.trim(),
        lastName: formData.get('last_name')?.trim(),
        phone: formData.get('phone')?.trim(),
        email: formData.get('email')?.trim(),
        orderType: formData.get('ordertype'),
        paymentMethod: formData.get('payment_method'),
        notes: formData.get('order_notes')?.trim(),
        deliveryDetails: {
          street: formData.get('street')?.trim(),
          barangay: formData.get('barangay'), // Get value from text input
          city: formData.get('city')?.trim(),
          province: formData.get('province')?.trim(),
          floor_number: floor,
          apt_landmark: landmark
        },
        preferredTime: (when === 'asap') ? null : scheduledTime?.iso,
        preferredTimeReadable: (when === 'asap') ? 'As soon as possible' : scheduledTime?.readable,
        tipAmount: tipAmount,
        subtotal: subtotal,
        deliveryFee: deliveryFee,
        totalAmount: totalAmount
      }
    };
  }

  // ... (showAllErrors, clearAllErrors, showConfirmationScreen, hydrateConfirmation are unchanged) ...
    function showAllErrors(errors) {
    let generalMessages = [];
    errors.forEach(err => {
      if (err.el) {
        err.el.classList.add('input-error');
        const msgEl = document.createElement('div');
        msgEl.className = 'input-err-msg';
        msgEl.textContent = err.msg;
        err.el.closest('.field-wrap, .form-row, .col, .radio-block, .checkout-card-head')?.appendChild(msgEl);
      } else {
        generalMessages.push(err.msg);
      }
    });
    if (generalMessages.length > 0) {
      elements.formError.innerHTML = generalMessages.join('<br>');
      elements.formError.style.display = 'block';
    }
    const firstError = document.querySelector('.input-error, .radio-block .input-err-msg, .checkout-card-head .input-err-msg');
    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function clearAllErrors() {
    elements.formError.style.display = 'none';
    elements.formError.innerHTML = '';
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    document.querySelectorAll('.input-err-msg').forEach(el => el.remove());
  }

  function showConfirmationScreen() {
    if (elements.checkoutScreen) elements.checkoutScreen.classList.add('hidden');
    if (elements.confirmScreen) elements.confirmScreen.classList.remove('hidden');
    if (elements.step1Dot) { elements.step1Dot.classList.add('done'); elements.step1Dot.classList.remove('active'); }
    if (elements.step2Dot) { elements.step2Dot.classList.add('active'); elements.step2Dot.classList.remove('done');}
    window.scrollTo(0, 0);
  }
  
  function hydrateConfirmation(orderData, orderNumber) {
    try {
      const details = orderData.orderDetails;
      const cart = orderData.cartItems;
      const el = (id) => document.getElementById(id);
      const setText = (id, text) => {
        const e = el(id);
        if (e) e.textContent = text;
      };
      const setHTML = (id, html) => {
        const e = el(id);
        if (e) e.innerHTML = html;
      };
      
      setText('confirmTitle', 'Order Placed!');
      setText('confirmSubtitle', `Your order #${orderNumber} has been placed. Please wait for the store to confirm.`);
      setText('confirmOrderNumber', orderNumber);
      
      const itemsEl = el('confirmItems');
      if (itemsEl) {
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
      }

      setText('confirmSubtotal', currency(details.subtotal));
      setText('confirmDelivery', currency(details.deliveryFee));
      setText('confirmTip', currency(details.tipAmount));
      setText('confirmTotal', currency(details.totalAmount));
      setText('confirmOrderType', details.orderType === 'delivery' ? 'Delivery' : 'Pickup');
      setHTML('confirmOrderTime', (details.preferredTimeReadable || 'As soon as possible').replace(/\n/g,'<br>'));
      setText('confirmPayment', details.paymentMethod === 'cash' ? 'Cash on Delivery / Pickup' : 'Paid Online');
      setText('confirmCustomerName', `${details.firstName} ${details.lastName}`);
      setText('confirmCustomerPhone', details.phone);
      setText('confirmCustomerEmail', details.email);

    } catch (e) {
      console.error('Error hydrating confirmation screen:', e);
    }
  }
  // --- END OF SCRIPT ---
</script>

</body>
</html>