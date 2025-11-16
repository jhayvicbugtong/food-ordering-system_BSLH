<aside class="cart-sidebar card shadow-sm border-0 position-sticky" style="top: 86px; z-index: 1020; max-height: calc(100vh - 106px); display: flex; flex-direction: column;">
  
  <div class="store-card card-body border-bottom" style="flex-shrink: 0;">
    <div class="d-flex justify-content-between align-items-start">
      <div class="flex-grow-1">
        <div class="store-name fw-bold fs-6">Bente Sais Lomi House</div>
        <div class="store-status text-success small fw-semibold d-flex align-items-center mt-1">
          <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
          Open
        </div>
        <div class="store-hours small text-muted mt-2">
          <div class="d-flex">
            <span class="fw-medium me-2" style="min-width: 80px;">Mon. - Sun.</span>
            <span>12:00 AM - 11:59 PM</span>
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

    <button type="button" class="checkout-btn btn w-100 py-2 mt-3 fw-semibold" style="background-color: var(--accent); color: #000; flex-shrink: 0;">
      <i class="bi bi-bag-check me-2"></i>
      Go to checkout
    </button>
  </div>
</aside>