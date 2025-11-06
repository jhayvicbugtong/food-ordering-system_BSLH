<!-- ADDRESS / PICKUP MODAL -->
<div class="addr-modal-overlay" id="addrModalOverlay" style="display:none;">
  <div class="addr-modal">
    <div class="addr-modal-header">
      <div class="addr-modal-mode-select">
        <div class="addr-mode-label" id="modalModeLabel">Delivery</div>
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

    <!-- ADDRESS INPUT (Delivery mode) -->
    <div class="addr-input-block" id="addrInputBlock">
      <label class="addr-input-label">
        <i class="bi bi-geo-alt-fill"></i>
        <input
          type="text"
          id="addrField"
          class="addr-input"
          placeholder="Enter delivery address"
          autocomplete="off"/>
      </label>

      <!-- small controls row -->
      <div class="addr-controls-row" style="display:flex; gap:8px; margin-top:8px;">
        <button type="button" class="addr-small-btn" id="useMyLocationBtn">
          <i class="bi bi-crosshair"></i> Use my location
        </button>
      </div>

      <!-- Suggestions dropdown -->
      <div id="addrSuggest" class="addr-suggest" style="display:none;"></div>
    </div>

    <!-- PICKUP STORE CARD (always visible) -->
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
        <div class="addr-hours-label">Mon. - Sun.</div>
        <div class="addr-hours-time">12:00 AM - 11:59 PM</div>
      </div>
    </div>

    <!-- CONFIRM BUTTON -->
    <button class="addr-confirm-btn" id="addrConfirmBtn">
      Use this selection
    </button>
  </div>
</div>

<!-- optional tiny styles for suggestions -->
<style>
  .addr-suggest {
    position: relative;
  }
  .addr-suggest-list {
    position: absolute;
    z-index: 30;
    top: 4px;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    max-height: 220px;
    overflow: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  }
  .addr-suggest-item {
    padding: 10px 12px;
    cursor: pointer;
    font-size: 14px;
  }
  .addr-suggest-item:hover,
  .addr-suggest-item.active {
    background: #F3F4F6;
  }
  .addr-small-btn {
    display: inline-flex; align-items: center; gap: 6px;
    border: 1px solid #D1D5DB; background: #fff; border-radius: 6px;
    padding: 6px 10px; font-size: 13px; cursor: pointer;
  }
</style>



<script>
/* ===========================
   DELIVERY / PICKUP + INTELLISENSE (NO MAPS)
   Uses OpenStreetMap Nominatim (no API key)
   =========================== */

/* --- BASIC STATE --- */
let currentMode = 'delivery'; // 'delivery' or 'pickup'
let savedAddress = '';        // chosen address string
let savedCoords  = null;      // { lat, lng }

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

const addrInputBlock       = document.getElementById('addrInputBlock');
const addrField            = document.getElementById('addrField');
const addrSuggest          = document.getElementById('addrSuggest');
const useMyLocationBtn     = document.getElementById('useMyLocationBtn');
const addrConfirmBtn       = document.getElementById('addrConfirmBtn');

/* Persist fulfillment choice for checkout page */
function persistFulfillment(obj) {
  localStorage.setItem('bslh_fulfillment', JSON.stringify(obj || {}));
}

/* ===========================
   NOMINATIM AUTOCOMPLETE
   =========================== */

let suggestOpen = false;
let activeIndex = -1; // keyboard selection index
let resultsCache = []; // store last results for keyboard nav
let debounceTimer;

function debounce(fn, ms) {
  return function(...args) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fn.apply(this, args), ms);
  };
}

async function nominatimSearch(q) {
  // Restrict to PH for better results; limit to 8; no key required.
  const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&countrycodes=ph&limit=8&addressdetails=1&q=${encodeURIComponent(q)}`;
  const res = await fetch(url, {
    headers: {
      // Nominatim polite header guidance; referrer is sent automatically by browser
      'Accept': 'application/json'
    }
  });
  if (!res.ok) throw new Error('Search failed');
  return res.json();
}

async function nominatimReverse(lat, lon) {
  const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if (!res.ok) throw new Error('Reverse failed');
  return res.json();
}

function renderSuggestions(list) {
  resultsCache = list || [];
  activeIndex = -1;

  if (!list.length) {
    addrSuggest.style.display = 'none';
    addrSuggest.innerHTML = '';
    suggestOpen = false;
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.className = 'addr-suggest-list';

  list.forEach((item, idx) => {
    const el = document.createElement('div');
    el.className = 'addr-suggest-item';
    el.textContent = item.display_name;
    el.dataset.idx = idx;
    el.addEventListener('click', () => selectSuggestion(idx));
    wrapper.appendChild(el);
  });

  addrSuggest.innerHTML = '';
  addrSuggest.appendChild(wrapper);
  addrSuggest.style.display = 'block';
  suggestOpen = true;
}

function selectSuggestion(idx) {
  const item = resultsCache[idx];
  if (!item) return;
  savedAddress = item.display_name;
  savedCoords  = { lat: parseFloat(item.lat), lng: parseFloat(item.lon) };
  addrField.value = savedAddress;
  hideSuggestions();
}

function hideSuggestions() {
  addrSuggest.style.display = 'none';
  addrSuggest.innerHTML = '';
  suggestOpen = false;
  activeIndex = -1;
}

const onAddrInput = debounce(async function(e) {
  const val = e.target.value.trim();
  if (val.length < 3) { hideSuggestions(); return; }
  try {
    const data = await nominatimSearch(val);
    renderSuggestions(data);
  } catch(err) {
    console.warn('Search error:', err);
    hideSuggestions();
  }
}, 250);

addrField.addEventListener('input', onAddrInput);

// keyboard navigation for suggestions
addrField.addEventListener('keydown', (e) => {
  if (!suggestOpen) return;

  const items = addrSuggest.querySelectorAll('.addr-suggest-item');
  if (!items.length) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    activeIndex = (activeIndex + 1) % items.length;
    updateActive(items);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    activeIndex = (activeIndex - 1 + items.length) % items.length;
    updateActive(items);
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (activeIndex >= 0) selectSuggestion(activeIndex);
  } else if (e.key === 'Escape') {
    hideSuggestions();
  }
});

function updateActive(items) {
  items.forEach(i => i.classList.remove('active'));
  if (activeIndex >= 0 && items[activeIndex]) {
    items[activeIndex].classList.add('active');
    items[activeIndex].scrollIntoView({ block: 'nearest' });
  }
}

document.addEventListener('click', (e) => {
  if (!addrSuggest.contains(e.target) && e.target !== addrField) {
    hideSuggestions();
  }
});

/* ===========================
   GEOLOCATION (Use my location)
   =========================== */

if (useMyLocationBtn) {
  useMyLocationBtn.addEventListener('click', async () => {
    if (!navigator.geolocation) {
      alert('Geolocation is not supported by your browser.');
      return;
    }
    navigator.geolocation.getCurrentPosition(async (pos) => {
      const lat = pos.coords.latitude;
      const lon = pos.coords.longitude;
      try {
        const rev = await nominatimReverse(lat, lon);
        savedCoords = { lat, lng: lon };
        savedAddress = rev.display_name || `${lat.toFixed(5)}, ${lon.toFixed(5)}`;
        addrField.value = savedAddress;
        hideSuggestions();
      } catch (err) {
        console.warn('Reverse geocode failed:', err);
        savedCoords = { lat, lng: lon };
        savedAddress = `${lat.toFixed(5)}, ${lon.toFixed(5)}`;
        addrField.value = savedAddress;
      }
    }, (err) => {
      console.warn('Geolocation error:', err);
      alert('Unable to fetch your location.');
    }, { enableHighAccuracy: true, timeout: 10000 });
  });
}

/* ===========================
   STATEFUL SIDEBAR + MODAL
   =========================== */

/* toggle main dropdown (Delivery / Pickup on sidebar) */
if (fulfillmentModeBtn && fulfillmentDropdown) {
  fulfillmentModeBtn.addEventListener('click', () => {
    fulfillmentDropdown.style.display =
      fulfillmentDropdown.style.display === 'block' ? 'none' : 'block';
  });
}

document.querySelectorAll('.fulfillment-option').forEach(opt => {
  opt.addEventListener('click', () => {
    const mode = opt.getAttribute('data-mode');
    setMode(mode);
    if (fulfillmentDropdown) fulfillmentDropdown.style.display = 'none';
  });
});

/* open modal when clicking address / pickup change */
if (addressBtn)  addressBtn.addEventListener('click', openModal);
const pickupChangeBtn = document.getElementById('pickupChangeBtn');
if (pickupChangeBtn) pickupChangeBtn.addEventListener('click', openModal);

/* close modal */
if (addrCloseBtn) addrCloseBtn.addEventListener('click', closeModal);
if (addrModalOverlay) {
  addrModalOverlay.addEventListener('click', (e) => {
    if (e.target === addrModalOverlay) closeModal(); // only when clicking the overlay
  });
}

/* modal internal: open dropdown to pick mode in modal */
if (modalModeToggle && modalModeDropdown) {
  modalModeToggle.addEventListener('click', () => {
    modalModeDropdown.style.display =
      modalModeDropdown.style.display === 'block' ? 'none' : 'block';
  });
}

/* choose between delivery / pickup in modal */
document.querySelectorAll('.addr-modal-mode-option').forEach(btn => {
  btn.addEventListener('click', () => {
    const mode = btn.getAttribute('data-mode');
    setMode(mode);

    // reflect in modal
    modalModeText.textContent  = capitalize(mode);
    modalModeLabel.textContent = capitalize(mode);

    if (modalModeDropdown) modalModeDropdown.style.display = 'none';
    updateModalUI();
  });
});

/* confirm selection in modal */
if (addrConfirmBtn) {
  addrConfirmBtn.addEventListener('click', () => {
    if (currentMode === 'delivery') {
      savedAddress = addrField.value.trim();
      if (!savedAddress) {
        alert('Please enter an address (or use My location).');
        return;
      }
      if (addressText) addressText.textContent = savedAddress;
      persistFulfillment({ mode: currentMode, address: savedAddress, coords: savedCoords });
    } else {
      persistFulfillment({ mode: currentMode });
    }
    closeModal();
  });
}

/* helpers */
function openModal() {
  if (!addrModalOverlay) return;
  addrModalOverlay.style.display = 'flex';

  // sync modal fields with current state
  modalModeText.textContent  = capitalize(currentMode);
  modalModeLabel.textContent = capitalize(currentMode);
  addrField.value            = savedAddress;

  updateModalUI();
}

function closeModal() {
  if (!addrModalOverlay) return;
  addrModalOverlay.style.display = 'none';
  if (modalModeDropdown) modalModeDropdown.style.display = 'none';
  hideSuggestions();
}

function setMode(mode) {
  currentMode = mode;

  // Update sidebar mode button label + icon
  if (fulfillmentModeText) fulfillmentModeText.textContent = capitalize(mode);
  if (fulfillmentModeIcon) {
    fulfillmentModeIcon.className = (mode === 'delivery') ? 'bi bi-truck' : 'bi bi-bag-check';
  }

  // Update sidebar "extra" block
  if (addressBtn && pickupInfo) {
    if (mode === 'delivery') {
      addressBtn.style.display = 'flex';
      pickupInfo.style.display = 'none';
    } else {
      addressBtn.style.display = 'none';
      pickupInfo.style.display = 'flex';
    }
  }
}

function updateModalUI() {
  // show/hide address field depending on mode
  if (addrInputBlock) {
    addrInputBlock.style.display = (currentMode === 'delivery') ? 'block' : 'none';
  }

  // highlight active option in sidebar dropdown
  document.querySelectorAll('.fulfillment-option').forEach(opt => {
    const mode = opt.getAttribute('data-mode');
    opt.classList.toggle('active', mode === currentMode);
  });

  // highlight active option in modal mode dropdown
  document.querySelectorAll('.addr-modal-mode-option').forEach(opt => {
    const mode = opt.getAttribute('data-mode');
    opt.classList.toggle('active', mode === currentMode);
  });
}

function capitalize(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

/* init UI state on load */
setMode(currentMode);
updateModalUI();

/* ===========================
   (Your existing checkout/cart JS stays as is)
   =========================== */
</script>


