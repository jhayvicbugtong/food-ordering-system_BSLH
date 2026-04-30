<?php
// customer/includes/delivery-cart.php
require_once __DIR__ . '/../../includes/db_connect.php';

$cart_store_status = 'open';
$cart_store_name   = $store_name ?? 'Bente Sais Lomi House';
$opening_time      = '08:00';
$closing_time      = '22:00';

if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('store_status', 'store_name', 'opening_time', 'closing_time')");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_key'] === 'store_status') $cart_store_status = $row['setting_value'];
            if ($row['setting_key'] === 'store_name')   $cart_store_name = $row['setting_value'];
            if ($row['setting_key'] === 'opening_time') $opening_time      = $row['setting_value'];
            if ($row['setting_key'] === 'closing_time') $closing_time      = $row['setting_value'];
        }
    }
}

$is_open = ($cart_store_status === 'open');
$open_display  = date('g:i A', strtotime($opening_time));
$close_display = date('g:i A', strtotime($closing_time));
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
  </div>
</div>

<style>
/* Refined Cart Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ced4da; }
</style>