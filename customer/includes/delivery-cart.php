<aside class="cart-sidebar">

  <!-- STORE CARD -->
  <div class="store-card">
    <div class="store-card-row">
      <div class="store-card-left">
        <div class="store-name">Bente Sais Lomi House</div>
        <div class="store-status">
          <span class="open-dot">●</span>
          Open
          <span class="caret-up">⌃</span>
        </div>
        <div class="store-hours">
          <div class="label">Mon. - Sun.</div>
          <div class="time">12:00 AM - 11:59 PM</div>
        </div>
      </div>

      <div class="store-card-right">
        <div class="acct-link">
          Account
          <i class="bi bi-caret-down-fill"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- FULFILLMENT BOX (delivery / pickup + address / pickup time) -->
  <div class="fulfillment-card">
    <!-- mode dropdown row -->
    <div class="fulfillment-row">
      <button class="fulfillment-mode-btn" id="fulfillmentModeBtn">
        <i class="bi bi-truck" id="fulfillmentModeIcon"></i>
        <span id="fulfillmentModeText">Delivery</span>
        <i class="bi bi-caret-down-fill caret"></i>
      </button>

      <!-- dropdown menu -->
      <div class="fulfillment-dropdown" id="fulfillmentDropdown" style="display:none;">
        <button class="fulfillment-option active" data-mode="delivery">
          <i class="bi bi-truck"></i>
          <span>Delivery</span>
        </button>
        <button class="fulfillment-option" data-mode="pickup">
          <i class="bi bi-bag-check"></i>
          <span>Pickup</span>
        </button>
      </div>
    </div>

    <!-- address / pickup section -->
    <div class="fulfillment-extra" id="fulfillmentExtra">
      <!-- Delivery mode view (shown by default) -->
      <button class="address-btn" id="addressBtn">
        <i class="bi bi-geo-alt-fill"></i>
        <span id="addressText">Enter delivery address</span>
      </button>

      <!-- Pickup mode view (hidden initially) -->
      <div class="pickup-info" id="pickupInfo" style="display:none;">
        <div class="pickup-label">
          <i class="bi bi-shop"></i>
          <div class="pickup-lines">
            <div class="pickup-title">Pickup</div>
            <div class="pickup-time">ASAP • Ready in ~15 min</div>
          </div>
        </div>
        <button class="pickup-change-btn" id="pickupChangeBtn">
          Change
        </button>
      </div>
    </div>
  </div>

  <!-- CART -->
  <div class="cart-header">
    <div>Your order</div>
    <span class="small-note" style="font-size:12px;line-height:1.2;font-weight:500;color:#6c757d;">
    </span>
  </div>

  <div class="cart-items" id="cartItems">
    <!-- Example cart line -->
    
  </div>

  <div class="cart-summary">
    <div class="cart-summary-row">
      <div>Sub-total</div>
      <div id="cartSubtotal">₱0.00</div>
    </div>
    <div class="cart-summary-row">
      <div>Delivery</div>
      <div id="cartDeliveryFee">₱0.00</div>
    </div>
    <div class="cart-summary-row total">
      <div>Total</div>
      <div id="cartTotal">₱0.00</div>
    </div>
  </div>

  <!-- <a class="checkout-btn" href="/food-ordering-system_BSLH/customer/checkout.php">
  Go to checkout
</a> -->
<button type="button" class="checkout-btn" onclick="window.location.href='/food-ordering-system_BSLH/customer/checkout.php'">
  Go to checkout
</button>



</aside>
