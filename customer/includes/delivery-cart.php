<?php
// customer/includes/delivery-cart.php
require_once __DIR__ . '/../../includes/db_connect.php';

<<<<<<< HEAD
=======
// ... (Existing PHP logic for settings) ...
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
$cart_store_status = 'open';
$cart_store_name   = $store_name ?? 'Bente Sais Lomi House';
$opening_time      = '08:00';
$closing_time      = '22:00';

if (isset($conn) && $conn instanceof mysqli) {
<<<<<<< HEAD
    $res = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('store_status', 'store_name', 'opening_time', 'closing_time')");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_key'] === 'store_status') $cart_store_status = $row['setting_value'];
            if ($row['setting_key'] === 'store_name')   $cart_store_name = $row['setting_value'];
=======
    $keys = "'store_status', 'store_name', 'opening_time', 'closing_time'";
    $res = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($keys)");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_key'] === 'store_status') $cart_store_status = $row['setting_value'];
            if ($row['setting_key'] === 'store_name' && empty($store_name))   $cart_store_name = $row['setting_value'];
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
            if ($row['setting_key'] === 'opening_time') $opening_time      = $row['setting_value'];
            if ($row['setting_key'] === 'closing_time') $closing_time      = $row['setting_value'];
        }
    }
}

$is_open = ($cart_store_status === 'open');
$open_display  = date('g:i A', strtotime($opening_time));
$close_display = date('g:i A', strtotime($closing_time));
<<<<<<< HEAD
?>

<div class="card shadow border-0 h-100 d-flex flex-column rounded-4 overflow-hidden" style="background: #fff; border: 1px solid rgba(0,0,0,0.03) !important;">
  
  <div class="card-header border-bottom bg-white p-3">
    <div class="d-flex align-items-center gap-2 mb-1">
      <i class="bi bi-shop text-dark fs-5"></i>
      <span class="fw-bold text-dark text-truncate"><?= htmlspecialchars($cart_store_name) ?></span>
    </div>
    <div class="d-flex justify-content-between align-items-center small">
      <?php if($is_open): ?>
         <div class="d-flex align-items-center gap-1">
            <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"></span>
            <span class="text-success fw-medium">Open Now</span>
         </div>
      <?php else: ?>
         <div class="d-flex align-items-center gap-1">
            <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px;"></span>
            <span class="text-danger fw-medium">Closed</span>
         </div>
      <?php endif; ?>
      <span class="text-muted fw-medium"><?= $open_display ?> - <?= $close_display ?></span>
    </div>
  </div>

  <div class="card-body p-3 overflow-auto flex-grow-1 js-cart-items custom-scrollbar" style="min-height: 200px;">
     </div>

  <div class="card-footer bg-white border-top p-3">
    <div class="d-flex justify-content-between small text-muted mb-2">
       <span>Subtotal</span>
       <span class="js-cart-subtotal fw-semibold text-dark">₱0.00</span>
    </div>
    <div class="d-flex justify-content-between small text-muted mb-3">
       <span>Delivery Fee</span>
       <span class="js-cart-delivery fw-semibold text-dark">₱0.00</span>
    </div>
    <div class="d-flex justify-content-between align-items-end mb-3">
       <span class="fw-bold text-dark fs-5">Total</span>
       <span class="js-cart-total fw-bold fs-4 text-dark">₱0.00</span>
    </div>

    <?php if ($is_open): ?>
      <a href="<?= htmlspecialchars($BASE_URL) ?>/customer/checkout.php" 
         class="btn btn-custom-accent w-100 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm"
         style="background-color: #5cfa63 !important; color: #000 !important; border: none;">
        <span>Proceed to Checkout</span>
        <i class="bi bi-arrow-right-short fs-5"></i>
      </a>
    <?php else: ?>
      <button class="btn btn-secondary w-100 py-3 rounded-pill fw-bold" disabled>
         Store is Closed
      </button>
      <div class="text-center mt-2 small text-danger fw-medium">
          <i class="bi bi-exclamation-circle me-1"></i> Ordering is unavailable
      </div>
    <?php endif; ?>
=======

if ($is_open) {
    $status_label = "Open";
    $status_class = "text-success";
    $closed_msg   = "";
} else {
    $status_label = "Closed";
    $status_class = "text-danger";
    $closed_msg   = "We are currently closed for orders.";
}
?>

<aside class="cart-sidebar card shadow-sm border-0 d-flex flex-column h-100" style="overflow: hidden;">
  
  <div class="store-card card-body border-bottom" style="flex-shrink: 0; flex-grow: 0 !important; height: auto;">
    <div class="d-flex justify-content-between align-items-start">
      <div class="flex-grow-1">
        <div class="store-name fw-bold fs-6"><?= htmlspecialchars($cart_store_name) ?></div>
        
        <div class="store-status <?= $status_class ?> small fw-semibold d-flex align-items-center mt-1">
          <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
          <?= htmlspecialchars($status_label) ?>
        </div>

        <div class="store-hours small text-muted mt-2">
          <div class="d-flex">
            <span class="fw-medium me-2" style="min-width: 80px;">Mon. - Sun.:</span>
            <span><?= $open_display ?> - <?= $close_display ?></span>
          </div>
        </div>
      </div>
    </div>
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
  </div>
</div>

<<<<<<< HEAD
<style>
/* Refined Cart Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ced4da; }
</style>
=======
  <div class="card-body d-flex flex-column flex-grow-1" style="overflow: hidden;">
    <div class="cart-header d-flex justify-content-between align-items-center mb-2" style="flex-shrink: 0;">
      <h5 class="card-title h6 fw-bold mb-0">
        <i class="bi bi-cart3 me-1"></i>
        Your order
      </h5>
    </div>

    <div class="cart-items-container js-cart-items flex-grow-1 mb-3" style="overflow-y: auto; min-height: 0; overscroll-behavior: contain;">
      <div class="cart-empty text-center p-3">
        <div class="text-muted small">Your cart is empty</div>
      </div>
    </div>

    <div class="cart-summary-block pt-3 border-top" style="flex-shrink: 0;">
      <div class="cart-summary-row d-flex justify-content-between small mb-2">
        <span class="text-muted">Sub-total</span>
        <span class="js-cart-subtotal fw-medium">₱0.00</span>
      </div>
      <div class="cart-summary-row d-flex justify-content-between small mb-2">
        <span class="text-muted">Delivery</span>
        <span class="js-cart-delivery fw-medium">₱0.00</span>
      </div>
      <div class="cart-summary-row total d-flex justify-content-between pt-2 mt-2 border-top">
        <span class="fw-bold">Total</span>
        <span class="js-cart-total fw-bold fs-6">₱0.00</span>
      </div>
    </div>

    <?php if ($is_open): ?>
        <button type="button" class="checkout-btn btn w-100 py-2 mt-3 fw-semibold" style="background-color: var(--accent); color: #000; flex-shrink: 0;">
          <i class="bi bi-bag-check me-2"></i>
          Go to checkout
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-secondary w-100 py-2 mt-3 fw-semibold" disabled style="flex-shrink: 0; opacity: 0.6;">
          <i class="bi bi-clock-history me-2"></i>
          Store Closed
        </button>
        <div class="text-danger small text-center mt-2" style="flex-shrink: 0; font-size: 0.8rem;">
            <?= htmlspecialchars($closed_msg) ?>
        </div>
    <?php endif; ?>
  </div>
</aside>
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
