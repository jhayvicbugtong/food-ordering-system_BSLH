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

  <style>
  .input-error { border-color:#EF4444 !important; outline-color:#EF4444 !important; }
  .input-err-msg { color:#B91C1C; font-size:12px; margin-top:6px; }
  .field-wrap { display:flex; flex-direction:column; }
</style>


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

  <!-- GCash -->
  <label class="radio-block selected" id="rbGcash">
    <div class="radio-left">
      <input type="radio" name="payment_method" value="gcash" checked>
      <span>GCash</span>
    </div>
    <div class="radio-right">
      <i class="bi bi-credit-card"></i>
    </div>
  </label>

  <!-- Cash -->
  <label class="radio-block" id="rbCash">
    <div class="radio-left">
      <input type="radio" name="payment_method" value="cash">
      <span>Cash</span>
    </div>
    <div class="radio-right">
      <i class="bi bi-cash-stack"></i>
    </div>
  </label>
</div>

<!-- GCASH PAYMENT MODAL -->
<div class="addr-modal-overlay" id="gcashModalOverlay" style="display:none;">
  <div class="addr-modal" role="dialog" aria-labelledby="gcashTitle" aria-modal="true">
    <div class="addr-modal-header">
      <div class="addr-modal-mode-select">
        <div class="addr-mode-label" id="gcashTitle">GCash Payment</div>
        <button class="addr-mode-toggle" type="button" disabled>
          <span>Details</span>
          <i class="bi bi-wallet2"></i>
        </button>
      </div>
      <button class="addr-close-btn" id="gcashCloseBtn" type="button" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="addr-input-block">
      <label class="addr-input-label" style="gap:8px;">
        <i class="bi bi-person"></i>
        <input type="text" id="gcashName" class="addr-input" placeholder="GCash name of the sender" autocomplete="off">
      </label>

      <label class="addr-input-label" style="gap:8px; margin-top:8px;">
        <i class="bi bi-cash-coin"></i>
        <input type="number" id="gcashAmount" class="addr-input" min="0" step="0.01" placeholder="Amount sent (incl. ₱5 fee)">
      </label>
      <small style="color:#6B7280; display:block; margin:6px 0 0 34px;">
        Note: Please add <b>₱5.00</b> for the transaction fee. Suggested: <b id="gcashSuggested">—</b>
      </small>

      <label class="addr-input-label" style="gap:8px; margin-top:8px;">
        <i class="bi bi-upc-scan"></i>
        <input type="text" id="gcashRef" class="addr-input" placeholder="GCash reference number" autocomplete="off">
      </label>

      <div id="gcashError" style="display:none; margin-top:10px; color:#B91C1C; background:#FEF2F2; border:1px solid #FECACA; padding:8px 10px; border-radius:8px; font-size:13px;"></div>
    </div>

    <button class="addr-confirm-btn" id="gcashConfirmBtn" type="button">
      Save GCash details
    </button>
  </div>
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
<!-- DELIVERY TIME MODAL (reusing your addr-modal styles) -->
<div class="addr-modal-overlay" id="timeModalOverlay" style="display:none;">
  <div class="addr-modal">
    <div class="addr-modal-header">
      <div class="addr-modal-mode-select">
        <div class="addr-mode-label">When</div>
        <button class="addr-mode-toggle" type="button" disabled>
          <span>Specific time</span>
          <i class="bi bi-clock-history"></i>
        </button>
      </div>

      <button class="addr-close-btn" id="timeCloseBtn" type="button" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- TIME PICKER CONTENT -->
    <div class="addr-input-block">
      <label class="addr-input-label" style="gap:8px;">
        <i class="bi bi-calendar-date"></i>
        <input type="date" id="timeDate" class="addr-input" />
      </label>

      <label class="addr-input-label" style="gap:8px; margin-top:8px;">
        <i class="bi bi-alarm"></i>
        <input type="time" id="timeTime" class="addr-input" step="300"/>
      </label>

      <!-- quick picks -->
      <div class="addr-controls-row" style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
        <button type="button" class="addr-small-btn time-chip" data-mins="30">+30 min</button>
        <button type="button" class="addr-small-btn time-chip" data-mins="60">+1 hr</button>
        <button type="button" class="addr-small-btn time-chip" data-mins="90">+1.5 hr</button>
        <button type="button" class="addr-small-btn time-chip" data-mins="120">+2 hr</button>
        <small id="timeNote" style="color:#6B7280; margin-left:4px;">Lead time: <b>15 min</b></small>
      </div>

      <!-- inline error -->
      <div id="timeError" style="display:none; margin-top:8px; color:#B91C1C; background:#FEF2F2; border:1px solid #FECACA; padding:8px 10px; border-radius:8px; font-size:13px;"></div>
    </div>

    <!-- CONFIRM BUTTON -->
    <button class="addr-confirm-btn" id="timeConfirmBtn" type="button">
      Use this time
    </button>
  </div>
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
          <label class="form-label">Floor number</label>
          <input class="form-input" type="text" placeholder="Optional">
        </div>
        <div class="col">
          <label class="form-label">Apt. / Landmark</label>
          <input class="form-input" type="text" placeholder="Apartment, landmark, etc.">
        </div>
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
   GCash Modal + Integration
   =========================== */

/* ---- helpers from your page ---- */
function currency(n){ return `₱${(Number(n)||0).toFixed(2)}`; }
function getCart() {
  try { return JSON.parse(localStorage.getItem('bslh_cart')) || { items:[], subtotal:0, deliveryFee:0, total:0 }; }
  catch(e){ return { items:[], subtotal:0, deliveryFee:0, total:0 }; }
}
function getCurrentTipPercent(){ return Number(window.currentTipPercent ?? 0); }
function computeCurrentTotalPlusFee(){
  const cart = getCart();
  const sub = cart.subtotal || 0;
  const del = cart.deliveryFee || 0;
  const tip = sub * (getCurrentTipPercent()/100);
  const total = sub + del + tip;
  return { total, suggested: total + 5 };
}

/* ---- DOM ---- */
const rbGcash = document.querySelector('input[type="radio"][name="payment_method"][value="gcash"]');
const rbCash  = document.querySelector('input[type="radio"][name="payment_method"][value="cash"]');

const gcashModalOverlay = document.getElementById('gcashModalOverlay');
const gcashCloseBtn     = document.getElementById('gcashCloseBtn');
const gcashConfirmBtn   = document.getElementById('gcashConfirmBtn');

const gcashName   = document.getElementById('gcashName');
const gcashAmount = document.getElementById('gcashAmount');
const gcashRef    = document.getElementById('gcashRef');
const gcashError  = document.getElementById('gcashError');
const gcashSuggEl = document.getElementById('gcashSuggested');

/* ---- storage ---- */
function saveGcash(obj){ localStorage.setItem('bslh_gcash_payment', JSON.stringify(obj||{})); }
function getGcash(){
  try { return JSON.parse(localStorage.getItem('bslh_gcash_payment')) || null; }
  catch(e){ return null; }
}

/* ---- modal open/close ---- */
function openGcashModal(prefillFromTotals=true){
  if (prefillFromTotals) {
    const { suggested } = computeCurrentTotalPlusFee();
    if (gcashSuggEl) gcashSuggEl.textContent = currency(suggested);
    if (!gcashAmount.value) gcashAmount.value = suggested.toFixed(2);
  }
  gcashError.style.display='none';
  gcashModalOverlay.style.display='flex';
}
function closeGcashModal(){ gcashModalOverlay.style.display='none'; }

if (gcashCloseBtn) gcashCloseBtn.addEventListener('click', closeGcashModal);
if (gcashModalOverlay) {
  gcashModalOverlay.addEventListener('click', (e)=>{ if (e.target === gcashModalOverlay) closeGcashModal(); });
}

/* ---- always-allow reopen by clicking selected GCash card ---- */
(function wireGcashCardAlwaysOpens(){
  const block = document.getElementById('rbGcash');
  if (block) {
    block.addEventListener('click', (e)=>{
      if (rbGcash && rbGcash.checked) { // already on GCash -> reopen to edit
        e.preventDefault();
        openGcashModal(true);
      }
    });
  }
})();

/* ---- open modal when GCash chosen the first time ---- */
if (rbGcash) {
  rbGcash.addEventListener('change', ()=>{
    if (rbGcash.checked) {
      const existing = getGcash();
      if (!existing) openGcashModal(true);
    }
  });
}

/* ---- optional: clear saved gcash when switching to cash ---- */
if (rbCash) {
  rbCash.addEventListener('change', ()=>{
    if (rbCash.checked) {
      localStorage.removeItem('bslh_gcash_payment');
    }
  });
}

/* ---- confirm validation + save ---- */
if (gcashConfirmBtn) {
  gcashConfirmBtn.addEventListener('click', ()=>{
    const name = (gcashName.value || '').trim();
    const ref  = (gcashRef.value || '').trim();
    const amt  = Number(gcashAmount.value || 0);

    const { suggested } = computeCurrentTotalPlusFee();

    if (!name)  return showGcashError('Please enter the GCash sender name.');
    if (!amt || isNaN(amt)) return showGcashError('Please enter the amount sent.');
    if (amt + 1e-6 < suggested) return showGcashError(`Amount must include ₱5 fee. Suggested: ${currency(suggested)}.`);
    if (ref.length < 6) return showGcashError('Please enter a valid GCash reference number.');

    const payload = { name, amount: amt, reference: ref, suggested };
    saveGcash(payload);

    // Let other code refresh displays if needed
    document.dispatchEvent(new CustomEvent('gcash:updated', { detail: payload }));

    closeGcashModal();
  });
}
function showGcashError(msg){
  gcashError.textContent = msg;
  gcashError.style.display = 'block';
}

/* ---- guard Create order if GCash is selected but data missing ---- */
(function guardPlaceOrderForGcash(){
  const placeOrderBtn  = document.getElementById('placeOrderBtn');
  if (!placeOrderBtn) return;

  placeOrderBtn.addEventListener('click', (e)=>{
    const selectedPay = document.querySelector('input[name="payment_method"]:checked')?.value || '';
    if (selectedPay !== 'gcash') return; // nothing to do

    const data = getGcash();
    const { suggested } = computeCurrentTotalPlusFee();

    if (!data || !data.name || !data.reference || !data.amount || (Number(data.amount) + 1e-6 < suggested)) {
      // open modal and block order creation
      e.preventDefault();
      openGcashModal(true);
      return;
    }

    // else: continue; your existing “Create order” handler will run
  }, { capture: true });
})();

/* ---- if your totals change (tip %, etc.), keep suggested amount fresh ---- */
document.addEventListener('DOMContentLoaded', ()=>{
  if (rbGcash?.checked) {
    const { suggested } = computeCurrentTotalPlusFee();
    if (gcashSuggEl) gcashSuggEl.textContent = currency(suggested);
  }
});
document.addEventListener('gcash:updated', ()=>{/* hook for UI if desired */});

  /* ===== Specific-time modal config (adjust if needed) ===== */
const STORE_OPEN_24  = "00:00"; // HH:MM (24h)
const STORE_CLOSE_24 = "23:59";
const LEAD_MINUTES   = 15;

/* ===== Elements ===== */
const timeModalOverlay = document.getElementById('timeModalOverlay');
const timeCloseBtn     = document.getElementById('timeCloseBtn');
const timeConfirmBtn   = document.getElementById('timeConfirmBtn');
const timeDateInput    = document.getElementById('timeDate');
const timeTimeInput    = document.getElementById('timeTime');
const timeError        = document.getElementById('timeError');

/* ===== Helpers ===== */
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
  timeDateInput.min = `${y}-${m}-${d}`;
  if (!timeDateInput.value) timeDateInput.value = `${y}-${m}-${d}`;
}
function defaultRoundedTimePlusLead(){
  const now = new Date(Date.now() + LEAD_MINUTES*60000);
  const roundedMs = Math.ceil(now.getTime()/(5*60000))*(5*60000);
  const rounded = new Date(roundedMs);
  timeTimeInput.value = `${pad(rounded.getHours())}:${pad(rounded.getMinutes())}`;
}
function clampToStoreHours(dateObj){
  const [oh, om] = STORE_OPEN_24.split(':').map(Number);
  const [ch, cm] = STORE_CLOSE_24.split(':').map(Number);
  const d = new Date(dateObj);
  const open = new Date(d);  open.setHours(oh, om, 0, 0);
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
function showTimeError(msg){ timeError.style.display='block'; timeError.textContent = msg; }
function clearTimeError(){ timeError.style.display='none'; timeError.textContent=''; }
function getSavedSpecificTime(){
  try { return JSON.parse(localStorage.getItem('bslh_delivery_time')) || null; } catch(e){ return null; }
}

/* ===== Open/close modal ===== */
function openTimeModal(){
  setMinDateToday();
  defaultRoundedTimePlusLead();
  clearTimeError();
  if (timeModalOverlay) timeModalOverlay.style.display='flex';
}
function closeTimeModal(){ if (timeModalOverlay) timeModalOverlay.style.display='none'; }

/* overlay click & close button */
if (timeModalOverlay) {
  timeModalOverlay.addEventListener('click', (e)=>{ if (e.target === timeModalOverlay) closeTimeModal(); });
}
if (timeCloseBtn) timeCloseBtn.addEventListener('click', closeTimeModal);

/* quick chips */
document.querySelectorAll('.time-chip').forEach(chip=>{
  chip.addEventListener('click', ()=>{
    const mins = Number(chip.dataset.mins || 0);
    const base = new Date();
    const t = new Date(base.getTime() + Math.max(mins, LEAD_MINUTES)*60000);
    const y = t.getFullYear(), m = pad(t.getMonth()+1), d = pad(t.getDate());
    timeDateInput.value = `${y}-${m}-${d}`;
    timeTimeInput.value = `${pad(t.getHours())}:${pad(t.getMinutes())}`;
    clearTimeError();
  });
});

/* confirm -> validate & save */
/* confirm -> validate & save (REPLACEMENT) */
if (timeConfirmBtn) {
  timeConfirmBtn.addEventListener('click', ()=>{
    clearTimeError();
    const d = timeDateInput.value;
    const t = timeTimeInput.value;
    if (!d || !t) { showTimeError('Please choose both date and time.'); return; }

    const adj = applyLeadTimeIfToday(d, t);
    if (adj !== t) timeTimeInput.value = adj;

    const selected = new Date(`${d}T${timeTimeInput.value}:00`);
    if (!clampToStoreHours(selected)) {
      showTimeError(`Selected time is outside store hours (${STORE_OPEN_24}–${STORE_CLOSE_24}).`);
      return;
    }

    const payload = {
      type: 'scheduled',
      iso: `${d}T${timeTimeInput.value}:00${tzOffsetISO(selected)}`,
      readable: selected.toLocaleString([], { dateStyle:'medium', timeStyle:'short' })
    };
    localStorage.setItem('bslh_delivery_time', JSON.stringify(payload));

    // announce update so other parts can refresh labels
    document.dispatchEvent(new CustomEvent('deliverytime:updated', { detail: payload }));

    // If you show a label like #whenText elsewhere, update it now (safe if it doesn't exist)
    const whenText = document.getElementById('whenText');
    if (whenText) whenText.textContent = payload.readable;

    closeTimeModal();
  });
}


/* ===== AUTO-TRIGGER WHEN "SPECIFIC" IS CHOSEN ===== */
(function autoTriggerSpecificRadio(){
  const radios = document.querySelectorAll('input[type="radio"][name="when"]');
  if (!radios.length) return;

  let prev = document.querySelector('input[name="when"]:checked')?.value || null;

  function onUpdate() {
    // highlight the cards if you already do that elsewhere
    radios.forEach(r=>{
      const block = r.closest('.radio-block');
      if (block) block.classList.toggle('selected', r.checked);
    });

    const current = document.querySelector('input[name="when"]:checked')?.value;
    const turnedSpecific = (prev !== 'specific' && current === 'specific');
    prev = current;

    if (turnedSpecific) {
      const hasSaved = !!getSavedSpecificTime();
      if (!hasSaved) openTimeModal();
    }
  }

  radios.forEach(r=>{
    r.addEventListener('change', onUpdate);

    // make the whole label clickable (optional / matches your pattern)
    const block = r.closest('.radio-block');
    if (block) {
      block.addEventListener('click', ()=>{
        const input = block.querySelector('input[type="radio"]');
        if (input && !input.checked) {
          input.checked = true;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    }
  });

  // initial state + open on load if specific is already checked and no saved time yet
  onUpdate();
  document.addEventListener('DOMContentLoaded', ()=>{
    const current = document.querySelector('input[name="when"]:checked')?.value;
    if (current === 'specific' && !getSavedSpecificTime()) openTimeModal();
  });
})();


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

  if (!itemsEl || !subtotalEl || !deliveryEl || !totalEl) return;

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

  subtotalEl.textContent = currency(cart.subtotal || 0);
  deliveryEl.textContent = currency(cart.deliveryFee || 0);

  const tipPercent = window.currentTipPercent ?? 0;
  const tipValue = (cart.subtotal || 0) * (tipPercent / 100);
  totalEl.textContent = currency((cart.subtotal || 0) + (cart.deliveryFee || 0) + tipValue);

  itemsEl.querySelectorAll('.mini-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-name');
      const action = btn.getAttribute('data-action');
      const c = getCart();
      const idx = c.items.findIndex(i => i.name === name);
      if (idx === -1) return;

      if (action === 'plus') c.items[idx].qty += 1;
      if (action === 'minus') c.items[idx].qty = Math.max(1, c.items[idx].qty - 1);

      c.subtotal = c.items.reduce((s,i)=> s + i.qty * i.unitPrice, 0);
      c.total = c.subtotal + (c.deliveryFee || 0);
      localStorage.setItem('bslh_cart', JSON.stringify(c));

      renderSummaryFromCart();
      applyTipPercent(window.currentTipPercent ?? 0);
    });
  });

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

/* Tip buttons */
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

/* ===========================
   RADIO HIGHLIGHTS (by name)
   =========================== */
/* This makes each group independent: Payment, Order type, When */
(function initRadioHighlights(){
  const addressCard = document.getElementById('addressCard');

  function bindRadioGroupByName(name, options = {}) {
    const radios = Array.from(document.querySelectorAll(`input[type="radio"][name="${name}"]`));
    if (!radios.length) return;

    const update = () => {
      radios.forEach(r => {
        const block = r.closest('.radio-block');
        if (block) block.classList.toggle('selected', r.checked);
      });

      // optional per-group side effects
      if (options.afterUpdate) options.afterUpdate();
    };

    radios.forEach(r => {
      r.addEventListener('change', update);

      // Make the whole label clickable + update highlight instantly
      const block = r.closest('.radio-block');
      if (block) {
        block.addEventListener('click', (e) => {
          const input = block.querySelector('input[type="radio"]');
          if (input && !input.checked) {
            input.checked = true;
            input.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
      }
    });

    // initial state
    update();
  }

  // Payment group
  bindRadioGroupByName('payment_method');

  // Order type group (also toggles the Address card visibility)
  bindRadioGroupByName('ordertype', {
    afterUpdate: () => {
      const selected = document.querySelector(`input[name="ordertype"]:checked`);
      if (addressCard) {
        addressCard.classList.toggle('hidden', !selected || selected.value !== 'delivery');
      }
    }
  });

  // When group
  bindRadioGroupByName('when');
})();

/* ===========================
   CONFIRMATION (STEP 2)
   =========================== */

function hydrateConfirmation() {
  const last = JSON.parse(localStorage.getItem('bslh_last_order') || '{}');
  const cart = last.cart || getCart();

  const itemsEl = document.getElementById('confirmItems');
  const subEl   = document.getElementById('confirmSubtotal');
  const tipEl   = document.getElementById('confirmTip');
  const totEl   = document.getElementById('confirmTotal');

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

    const tipPercent = Number(window.currentTipPercent ?? 0);
    const tipValue   = (cart.subtotal || 0) * (tipPercent / 100);

    const paymentLabel = document.querySelector('input[name="payment_method"]:checked')
      ?.closest('.radio-left')?.querySelector('span')?.textContent?.trim() || '';

    const orderType = (document.querySelector('input[name="ordertype"]:checked')?.value === 'delivery')
      ? 'Delivery' : 'Pickup';

    const whenVal = document.querySelector('input[name="when"]:checked')?.value || 'asap';
    const orderTimeMsg = (whenVal === 'asap') ? 'As soon as possible' : 'For your chosen time';

    const firstName = document.querySelector('input[placeholder="first name"]')?.value || '';
    const lastName  = document.querySelector('input[placeholder="last name"]')?.value  || '';
    const phone     = document.querySelector('input[placeholder="+63 956 244 6616"]')?.value || '';
    const email     = document.querySelector('input[type="email"]')?.value || '';

    const snapshot = { cart, tipPercent, tipValue, paymentLabel, orderType, orderTimeMsg, firstName, lastName, phone, email };
    localStorage.setItem('bslh_last_order', JSON.stringify(snapshot));

    if (checkoutScreen) checkoutScreen.classList.add('hidden');
    if (confirmScreen)  confirmScreen.classList.remove('hidden');

    if (step1Dot) { step1Dot.classList.add('done'); step1Dot.classList.remove('active'); }
    if (step2Dot) step2Dot.classList.add('active');

    hydrateConfirmation();
  });
}

/* Boot */
document.addEventListener('DOMContentLoaded', function () {
  renderSummaryFromCart();
  if (confirmScreen && !confirmScreen.classList.contains('hidden')) {
    hydrateConfirmation();
  }
});

  /* ===== Make "Specific time" open the modal ALWAYS, and keep UI in sync ===== */
(function specificTimeAlwaysOpen(){
  // helper
  function getSavedSpecificTime(){
    try { return JSON.parse(localStorage.getItem('bslh_delivery_time')) || null; }
    catch(e){ return null; }
  }
  function updateWhenTextFromStorage(){
    const lbl = document.getElementById('whenText');
    if (!lbl) return;
    const t = getSavedSpecificTime();
    lbl.textContent = (t && t.readable) ? t.readable : '—';
  }
  function openTimePicker(){
    // handle whichever name you used for the opener
    if (typeof window.openDeliveryTimeModal === 'function') {
      window.openDeliveryTimeModal();
    } else if (typeof window.openTimeModal === 'function') {
      window.openTimeModal();
    }
  }

  const inputSpecific = document.querySelector('input[name="when"][value="specific"]');
  const inputAsap     = document.querySelector('input[name="when"][value="asap"]');
  const blockSpecific = inputSpecific ? inputSpecific.closest('.radio-block') : null;

  if (!inputSpecific) return;

  // 1) Open modal when switching to "specific" and no time saved yet
  inputSpecific.addEventListener('change', ()=>{
    if (inputSpecific.checked && !getSavedSpecificTime()) {
      openTimePicker();
    }
  });

  // 2) Open modal even if "specific" is already selected (to edit/change)
  if (blockSpecific) {
    blockSpecific.addEventListener('click', (e)=>{
      // If you clicked the same selected block, let you edit the time
      if (inputSpecific.checked) {
        e.preventDefault(); // don't toggle anything, just open editor
        openTimePicker();
      }
    });
    // Keyboard accessibility (Enter/Space on the block)
    blockSpecific.addEventListener('keydown', (e)=>{
      if ((e.key === 'Enter' || e.key === ' ') && inputSpecific.checked) {
        e.preventDefault();
        openTimePicker();
      }
    });
  }

  // 3) Optional: if switching back to ASAP, clear saved time
  if (inputAsap) {
    inputAsap.addEventListener('change', ()=>{
      if (inputAsap.checked) {
        localStorage.removeItem('bslh_delivery_time');
        updateWhenTextFromStorage();
      }
    });
  }

  // 4) Keep any label in sync whenever time changes
  document.addEventListener('deliverytime:updated', updateWhenTextFromStorage);

  // 5) On load: if "specific" already selected but time missing, open once;
  //             otherwise just update any label
  document.addEventListener('DOMContentLoaded', ()=>{
    updateWhenTextFromStorage();
    if (inputSpecific.checked && !getSavedSpecificTime()) {
      openTimePicker();
    }
  });
})();

  /* ===========================
   REQUIRED-FIELD VALIDATION
   Blocks "Create order" until valid
   =========================== */

(function checkoutRequiredValidation(){
  const placeOrderBtn = document.getElementById('placeOrderBtn');
  if (!placeOrderBtn) return;

  // --- helpers ---
  function $(sel, root=document){ return root.querySelector(sel); }
  function createMsg(msg){
    const s = document.createElement('div');
    s.className = 'input-err-msg';
    s.textContent = msg;
    return s;
  }
  function clearError(el){
    if (!el) return;
    el.classList.remove('input-error');
    const next = el.nextElementSibling;
    if (next && next.classList.contains('input-err-msg')) next.remove();
  }
  function setError(el, msg){
    if (!el) return;
    el.classList.add('input-error');
    // remove old message if any
    const next = el.nextElementSibling;
    if (next && next.classList.contains('input-err-msg')) next.remove();
    el.insertAdjacentElement('afterend', createMsg(msg));
  }
  function scrollToFirstError(errEls){
    if (!errEls.length) return;
    errEls[0].scrollIntoView({behavior:'smooth', block:'center'});
    errEls[0].focus?.();
  }
  function getScheduledTime(){
    try { return JSON.parse(localStorage.getItem('bslh_delivery_time')) || null; }
    catch(e){ return null; }
  }
  function getGcash(){
    try { return JSON.parse(localStorage.getItem('bslh_gcash_payment')) || null; }
    catch(e){ return null; }
  }
  function computeCurrentTotalPlusFee(){
    // uses your existing getCart & currentTipPercent if present
    const cart = (typeof getCart === 'function') ? getCart() : {subtotal:0, deliveryFee:0};
    const sub = cart.subtotal || 0;
    const del = cart.deliveryFee || 0;
    const tipPct = Number(window.currentTipPercent ?? 0);
    const tipVal = sub * (tipPct/100);
    const total = sub + del + tipVal;
    return { total, suggested: total + 5 };
  }

  // Validate email/phone lightly
  const emailOk = (v)=> /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v||'').trim());
  const phoneOk = (v)=> String(v||'').replace(/[^\d]/g,'').length >= 10; // allow +63 or local; min 10 digits

  // Main gate
  placeOrderBtn.addEventListener('click', function(e){
    const errs = [];

    // Clear old inline errors
    [
      $('input[placeholder="Street / purok / house no."]'),
      $('input[placeholder="first name"]'),
      $('input[placeholder="last name"]'),
      $('input[placeholder="+63 956 244 6616"]'),
      $('input[type="email"]'),
    ].forEach(clearError);

    // 1) Required basics
    const orderType = $('input[name="ordertype"]:checked')?.value || 'delivery';
    const street    = $('input[placeholder="Street / purok / house no."]');
    const firstName = $('input[placeholder="first name"]');
    const lastName  = $('input[placeholder="last name"]');
    const phone     = $('input[placeholder="+63 956 244 6616"]');
    const email     = $('input[type="email"]');

    // Street required only if Delivery
    if (orderType === 'delivery') {
      if (!street || !street.value.trim()) { setError(street, 'Street is required.'); errs.push(street); }
    }

    if (!firstName || !firstName.value.trim()) { setError(firstName, 'First name is required.'); errs.push(firstName); }
    if (!lastName  || !lastName.value.trim())  { setError(lastName,  'Last name is required.');  errs.push(lastName); }
    if (!phone || !phone.value.trim())         { setError(phone,     'Phone is required.');      errs.push(phone); }
    else if (!phoneOk(phone.value))            { setError(phone,     'Please enter a valid phone number.'); errs.push(phone); }

    if (!email || !email.value.trim())         { setError(email,     'Email is required.');      errs.push(email); }
    else if (!emailOk(email.value))            { setError(email,     'Please enter a valid email address.'); errs.push(email); }

    // 2) WHEN validation (specific requires chosen time)
    const whenVal = $('input[name="when"]:checked')?.value || 'asap';
    if (whenVal === 'specific') {
      const t = getScheduledTime();
      if (!t || !t.readable) {
        // Open your time modal & block
        if (typeof window.openTimeModal === 'function') window.openTimeModal();
        errs.push($('input[name="when"][value="specific"]') || $('input[name="when"]'));
      }
    }

    // 3) Payment validation if GCash
    const payVal = $('input[name="payment_method"]:checked')?.value || '';
    if (payVal === 'gcash') {
      const data = getGcash();
      const { suggested } = computeCurrentTotalPlusFee();

      if (!data || !data.name || !data.reference || !data.amount || (Number(data.amount) + 1e-6 < suggested)) {
        // Open GCash modal & block
        if (typeof window.openGcashModal === 'function') window.openGcashModal(true);
        const gcashBlock = document.getElementById('rbGcash') || $('input[name="payment_method"][value="gcash"]');
        if (gcashBlock) errs.push(gcashBlock);
      }
    }

    // If any errors, stop the default flow (prevents proceeding to step 2)
    if (errs.length) {
      e.preventDefault();
      e.stopImmediatePropagation(); // stop other click handlers for this click
      scrollToFirstError(errs);
      return false;
    }
    // else allow your existing "Create order" handler to continue
  }, { capture: true }); // capture ensures we run before your existing handler
})();



</script>



</body>
</html>
