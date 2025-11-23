<?php
// customer/includes/delivery-cart.php
require_once __DIR__ . '/../../includes/db_connect.php';

// 1. Default Values
$cart_store_status = 'open';
$cart_store_name   = $store_name ?? 'Bente Sais Lomi House';
$opening_time      = '08:00';
$closing_time      = '22:00';

// 2. Fetch Settings
if (isset($conn) && $conn instanceof mysqli) {
    $keys = "'store_status', 'store_name', 'opening_time', 'closing_time'";
    $res = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($keys)");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_key'] === 'store_status') $cart_store_status = $row['setting_value'];
            if ($row['setting_key'] === 'store_name' && empty($store_name))   $cart_store_name = $row['setting_value'];
            if ($row['setting_key'] === 'opening_time') $opening_time      = $row['setting_value'];
            if ($row['setting_key'] === 'closing_time') $closing_time      = $row['setting_value'];
        }
    }
}

// 3. Logic: Store Status overrides Time
// The store is OPEN if the admin setting is 'open', regardless of the time.
$is_open = ($cart_store_status === 'open');

// Format times for display
$open_display  = date('g:i A', strtotime($opening_time));
$close_display = date('g:i A', strtotime($closing_time));

// Status Label
if ($is_open) {
    $status_label = "Open";
    $status_class = "text-success";
    $closed_msg   = "";
} else {
    // If manually closed
    $status_label = "Closed";
    $status_class = "text-danger";
    $closed_msg   = "We are currently closed for orders.";
}
?>
<aside class="cart-sidebar card shadow-sm border-0 position-sticky" style="top: 86px; z-index: 1020; max-height: calc(100vh - 106px); display: flex; flex-direction: column;">
  
  <div class="store-card card-body border-bottom" style="flex-shrink: 0;">
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
  </div>

  <div class="card-body d-flex flex-column flex-grow-1" style="overflow: hidden;">
    <div class="cart-header d-flex justify-content-between align-items-center mb-2" style="flex-shrink: 0;">
      <h5 class="card-title h6 fw-bold mb-0">
        <i class="bi bi-cart3 me-1"></i>
        Your order
      </h5>
    </div>

    <div class="cart-items-container flex-grow-1 mb-3" id="cartItems" style="overflow-y: auto; min-height: 0;">
      <div class="cart-empty text-center p-3">
        <div class="text-muted small">Your cart is empty</div>
      </div>
    </div>

    <div class="cart-summary-block pt-3 border-top" style="flex-shrink: 0;">
      <div class="cart-summary-row d-flex justify-content-between small mb-2">
        <span class="text-muted">Sub-total</span>
        <span id="cartSubtotal" class="fw-medium">₱0.00</span>
      </div>
      <div class="cart-summary-row d-flex justify-content-between small mb-2">
        <span class="text-muted">Delivery</span>
        <span id="cartDeliveryFee" class="fw-medium">₱0.00</span>
      </div>
      <div class="cart-summary-row total d-flex justify-content-between pt-2 mt-2 border-top">
        <span class="fw-bold">Total</span>
        <span id="cartTotal" class="fw-bold fs-6">₱0.00</span>
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