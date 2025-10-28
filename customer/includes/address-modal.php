<!-- ADDRESS / PICKUP MODAL -->
<div class="addr-modal-overlay" id="addrModalOverlay" style="display:none;">
  <div class="addr-modal">
    <div class="addr-modal-header">
      <div class="addr-modal-mode-select">
        <div class="addr-mode-label" id="modalModeLabel">
          Delivery
        </div>
        <button class="addr-mode-toggle" id="modalModeToggle">
          <span id="modalModeText">Delivery</span>
          <i class="bi bi-caret-down-fill"></i>
        </button>

        <!-- tiny dropdown inside modal -->
        <div class="addr-modal-mode-dropdown" id="modalModeDropdown" style="display:none;">
          <button class="addr-modal-mode-option active" data-mode="delivery">Delivery</button>
          <button class="addr-modal-mode-option" data-mode="pickup">Pickup</button>
        </div>
      </div>

      <button class="addr-close-btn" id="addrCloseBtn">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- ADDRESS INPUT (for Delivery mode) -->
    <div class="addr-input-block" id="addrInputBlock">
      <label class="addr-input-label">
        <i class="bi bi-geo-alt-fill"></i>
        <input
          type="text"
          id="addrField"
          class="addr-input"
          placeholder="Enter delivery address"/>
      </label>
    </div>

    <!-- PICKUP STORE CARD (always visible in modal, but it's *the* pickup choice) -->
    <div class="addr-store-card">
      <div class="addr-store-card-top">
        <div class="addr-store-name">Bente Sais Lomi House</div>
        <div class="addr-store-meta">
          <div class="addr-meta-block">
            <div class="addr-meta-label">Delivery cost</div>
            <div class="addr-meta-value">₱0.00</div>
          </div>
          <div class="addr-meta-block">
            <div class="addr-meta-label">Min. order</div>
            <div class="addr-meta-value">₱0.00</div>
          </div>
        </div>
      </div>

      <div class="addr-store-extra">
        <div class="addr-hours-label">
          Mon. - Sun.
        </div>
        <div class="addr-hours-time">
          12:00 AM - 11:59 PM
        </div>
      </div>
    </div>

    <!-- CONFIRM BUTTON -->
    <button class="addr-confirm-btn" id="addrConfirmBtn">
      Use this selection
    </button>
  </div>
</div>

<script>
// --- BASIC STATE ---
let currentMode = 'delivery'; // 'delivery' or 'pickup'
let savedAddress = '';        // pretend this is what user typed

const fulfillmentModeBtn   = document.getElementById('fulfillmentModeBtn');
const fulfillmentDropdown  = document.getElementById('fulfillmentDropdown');
const fulfillmentModeText  = document.getElementById('fulfillmentModeText');
const fulfillmentModeIcon  = document.getElementById('fulfillmentModeIcon');

const fulfillmentExtra     = document.getElementById('fulfillmentExtra');
const addressBtn           = document.getElementById('addressBtn');
const addressText          = document.getElementById('addressText');
const pickupInfo           = document.getElementById('pickupInfo');

const addrModalOverlay     = document.getElementById('addrModalOverlay');
const addrCloseBtn         = document.getElementById('addrCloseBtn');

const modalModeToggle      = document.getElementById('modalModeToggle');
const modalModeDropdown    = document.getElementById('modalModeDropdown');
const modalModeText        = document.getElementById('modalModeText');
const modalModeLabel       = document.getElementById('modalModeLabel');

const addrField            = document.getElementById('addrField');
const addrConfirmBtn       = document.getElementById('addrConfirmBtn');

const addrInputBlock       = document.getElementById('addrInputBlock');

// --- toggle main dropdown (Delivery / Pickup on sidebar) ---
fulfillmentModeBtn.addEventListener('click', () => {
  fulfillmentDropdown.style.display =
    fulfillmentDropdown.style.display === 'block' ? 'none' : 'block';
});

document.querySelectorAll('.fulfillment-option').forEach(opt => {
  opt.addEventListener('click', () => {
    const mode = opt.getAttribute('data-mode');
    setMode(mode);
    fulfillmentDropdown.style.display = 'none';
  });
});

// --- open modal when clicking address / pickup change ---
if (addressBtn) {
  addressBtn.addEventListener('click', openModal);
}
const pickupChangeBtn = document.getElementById('pickupChangeBtn');
if (pickupChangeBtn) {
  pickupChangeBtn.addEventListener('click', openModal);
}

// --- close modal ---
addrCloseBtn.addEventListener('click', closeModal);
addrModalOverlay.addEventListener('click', (e) => {
  // close when clicking the overlay, but NOT if clicking the modal content
  if (e.target === addrModalOverlay) closeModal();
});

// --- modal internal: open dropdown to pick mode in modal ---
modalModeToggle.addEventListener('click', () => {
  modalModeDropdown.style.display =
    modalModeDropdown.style.display === 'block' ? 'none' : 'block';
});

// choose between delivery / pickup in modal
document.querySelectorAll('.addr-modal-mode-option').forEach(btn => {
  btn.addEventListener('click', () => {
    const mode = btn.getAttribute('data-mode');
    setMode(mode);

    // reflect in modal
    modalModeText.textContent  = capitalize(mode);
    modalModeLabel.textContent = capitalize(mode);

    modalModeDropdown.style.display = 'none';
    updateModalUI();
  });
});

// confirm selection in modal
addrConfirmBtn.addEventListener('click', () => {
  if (currentMode === 'delivery') {
    savedAddress = addrField.value.trim();
    addressText.textContent = savedAddress
      ? savedAddress
      : 'Enter delivery address';
  }
  closeModal();
});

// --- helpers ---
function openModal() {
  addrModalOverlay.style.display = 'flex';
  // sync modal fields with current state
  modalModeText.textContent  = capitalize(currentMode);
  modalModeLabel.textContent = capitalize(currentMode);
  addrField.value            = savedAddress;

  updateModalUI();
}

function closeModal() {
  addrModalOverlay.style.display = 'none';
  modalModeDropdown.style.display = 'none';
}

function setMode(mode) {
  currentMode = mode;

  // Update sidebar mode button label + icon
  fulfillmentModeText.textContent = capitalize(mode);
  if (mode === 'delivery') {
    fulfillmentModeIcon.className = 'bi bi-truck';
  } else {
    fulfillmentModeIcon.className = 'bi bi-bag-check';
  }

  // Update sidebar "extra" block
  if (mode === 'delivery') {
    addressBtn.style.display = 'flex';
    pickupInfo.style.display = 'none';
  } else {
    addressBtn.style.display = 'none';
    pickupInfo.style.display = 'flex';
  }
}

function updateModalUI() {
  // show/hide address field depending on mode
  if (currentMode === 'delivery') {
    addrInputBlock.style.display = 'block';
  } else {
    addrInputBlock.style.display = 'none';
  }

  // highlight active option in sidebar dropdown
  document.querySelectorAll('.fulfillment-option').forEach(opt => {
    const mode = opt.getAttribute('data-mode');
    if (mode === currentMode) {
      opt.classList.add('active');
    } else {
      opt.classList.remove('active');
    }
  });

  // highlight active option in modal mode dropdown
  document.querySelectorAll('.addr-modal-mode-option').forEach(opt => {
    const mode = opt.getAttribute('data-mode');
    if (mode === currentMode) {
      opt.classList.add('active');
    } else {
      opt.classList.remove('active');
    }
  });
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// init UI state on load
setMode(currentMode);
updateModalUI();
</script>
