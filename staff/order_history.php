<?php
// staff/order_history.php
include __DIR__ . '/includes/header.php';
?>

<style>
  .dashboard-table th,
  .dashboard-table td {
    font-size: 14px;
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    vertical-align: top;
  }

  .dashboard-table td small {
    font-size: 12px;
  }

  .status-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .content-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .content-card-header .filter-form .form-control,
  .content-card-header .filter-form .form-select {
    font-size: 0.85rem;
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">Order History</h2>
    

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Past Orders</h2>
          <p id="record-count">Loading…</p>
        </div>

        <!-- SEARCH + FILTERS (NO SUBMIT NEEDED) -->
        <div class="right">
          <form id="filter-form" class="row g-2 filter-form" onsubmit="return false;">
            <div class="col-auto">
              <input
                type="text"
                name="q"
                class="form-control form-control-sm"
                placeholder="Search order # or ID"
              >
            </div>

            <div class="col-auto">
              <select name="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                <option value="completed">Completed</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="col-auto">
              <select name="type" class="form-select form-select-sm">
                <option value="">All types</option>
                <option value="delivery">Delivery</option>
                <option value="pickup">Pickup</option>
              </select>
            </div>

            <div class="col-auto">
              <button type="button" id="reset-filters" class="btn btn-sm btn-outline-secondary">
                Reset
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover dashboard-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Total</th>
              <th>Status</th>
              <th>Placed At</th>
              <th>Last Update</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="history-body">
            <!-- filled by JS -->
          </tbody>
        </table>
      </div>

      <!-- PAGINATION (handled by JS) -->
      <nav aria-label="Order history pages">
        <ul class="pagination pagination-sm justify-content-end" id="pagination">
          <!-- filled by JS -->
        </ul>
      </nav>
    </section>
  </main>
</div>

<!-- ORDER DETAILS MODAL -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-0">Loading…</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const form        = document.getElementById('filter-form');
  const bodyEl      = document.getElementById('history-body');
  const pagination  = document.getElementById('pagination');
  const recordCount = document.getElementById('record-count');
  const resetBtn    = document.getElementById('reset-filters');

  const searchInput  = form.querySelector('input[name="q"]');
  const statusSelect = form.querySelector('select[name="status"]');
  const typeSelect   = form.querySelector('select[name="type"]');

  let currentPage = 1;
  let typingTimer = null;
  const delay = 250; // ms – feels instant but not spammy

  function getFilters() {
    return {
      q:      searchInput.value.trim(),
      status: statusSelect.value,
      type:   typeSelect.value,
      page:   currentPage
    };
  }

  function buildQuery(params) {
    const qs = new URLSearchParams();
    Object.keys(params).forEach(k => {
      if (params[k] !== '' && params[k] !== null && params[k] !== undefined) {
        qs.append(k, params[k]);
      }
    });
    return qs.toString();
  }

  function renderRows(rows) {
    if (!rows || !rows.length) {
      bodyEl.innerHTML = `
        <tr>
          <td colspan="8" class="text-center text-muted">
            No orders match your filters.
          </td>
        </tr>`;
      return;
    }

    bodyEl.innerHTML = rows.map(r => `
      <tr>
        <td>${escapeHtml(r.order_number)}</td>
        <td>${escapeHtml(r.customer)}</td>
        <td>${escapeHtml(r.type_label)}</td>
        <td>${escapeHtml(r.total_formatted)}</td>
        <td>
          <span class="badge ${escapeHtml(r.status_badge_class)} status-badge">
            ${escapeHtml(r.status_label)}
          </span>
        </td>
        <td>${escapeHtml(r.created_at)}</td>
        <td>${escapeHtml(r.updated_at)}</td>
        <td>
          <button type="button"
                  class="btn btn-sm btn-outline-primary btn-view-details"
                  data-order-id="${encodeURIComponent(r.order_id)}"
                  data-order-number="${escapeHtml(r.order_number)}">
            View details
          </button>
        </td>
      </tr>
    `).join('');
  }

  function renderPagination(page, totalPages) {
    if (!totalPages || totalPages <= 1) {
      pagination.innerHTML = '';
      return;
    }

    let html = '';

    const disabledPrev = page <= 1 ? ' disabled' : '';
    html += `
      <li class="page-item${disabledPrev}">
        <button class="page-link" data-page="${page - 1}" ${disabledPrev ? 'tabindex="-1"' : ''}>« Prev</button>
      </li>
    `;

    for (let p = 1; p <= totalPages; p++) {
      const active = p === page ? ' active' : '';
      html += `
        <li class="page-item${active}">
          <button class="page-link" data-page="${p}">${p}</button>
        </li>
      `;
    }

    const disabledNext = page >= totalPages ? ' disabled' : '';
    html += `
      <li class="page-item${disabledNext}">
        <button class="page-link" data-page="${page + 1}" ${disabledNext ? 'tabindex="-1"' : ''}>Next »</button>
      </li>
    `;

    pagination.innerHTML = html;
  }

  function updateRecordCount(totalRows) {
    recordCount.textContent = totalRows + ' record(s) found';
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function loadOrders(page = 1) {
    currentPage = page;

    const params = getFilters();
    params.page  = currentPage;

    const qs = buildQuery(params);
    try {
      const res = await fetch('actions/order_history_api.php?' + qs, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) {
        bodyEl.innerHTML = `
          <tr><td colspan="8" class="text-center text-danger">
            Failed to load data.
          </td></tr>`;
        pagination.innerHTML = '';
        recordCount.textContent = 'Error';
        return;
      }

      const data = await res.json();
      if (!data.success) {
        bodyEl.innerHTML = `
          <tr><td colspan="8" class="text-center text-danger">
            ${escapeHtml(data.message || 'Error loading orders.')}
          </td></tr>`;
        pagination.innerHTML = '';
        recordCount.textContent = 'Error';
        return;
      }

      renderRows(data.rows);
      renderPagination(data.page, data.total_pages);
      updateRecordCount(data.total_rows);
    } catch (e) {
      bodyEl.innerHTML = `
        <tr><td colspan="8" class="text-center text-danger">
          Network error.
        </td></tr>`;
      pagination.innerHTML = '';
      recordCount.textContent = 'Error';
    }
  }

  // Live search on typing
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => loadOrders(1), delay);
    });
  }

  // Filters change -> reload first page
  if (statusSelect) {
    statusSelect.addEventListener('change', () => loadOrders(1));
  }
  if (typeSelect) {
    typeSelect.addEventListener('change', () => loadOrders(1));
  }

  // Reset button
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      searchInput.value  = '';
      statusSelect.value = '';
      typeSelect.value   = '';
      loadOrders(1);
    });
  }

  // Pagination click (event delegation)
  pagination.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.parentElement.classList.contains('disabled')) return;

    const page = parseInt(btn.getAttribute('data-page'), 10);
    if (!isNaN(page) && page >= 1) {
      loadOrders(page);
    }
  });

  // View details (modal) – event delegation on table body
  bodyEl.addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-view-details');
    if (!btn) return;

    const orderId     = btn.getAttribute('data-order-id');
    const orderNumber = btn.getAttribute('data-order-number') || '';

    const modalEl   = document.getElementById('orderDetailsModal');
    const modalBody = modalEl.querySelector('.modal-body');
    const modalTitle= modalEl.querySelector('.modal-title');

    modalTitle.textContent = 'Order ' + orderNumber;
    modalBody.innerHTML = '<p class="text-muted mb-0">Loading…</p>';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
      const res = await fetch('actions/get_order_details.php?order_id=' + encodeURIComponent(orderId), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) {
        modalBody.innerHTML = '<p class="text-danger mb-0">Failed to load order details.</p>';
        return;
      }

      const data = await res.json();
      if (!data.success) {
        modalBody.innerHTML = '<p class="text-danger mb-0">' +
          escapeHtml(data.message || 'Error loading order details.') +
          '</p>';
        return;
      }

      const o  = data.order;
      const it = data.items || [];

      let itemsHtml = '';
      if (it.length) {
        itemsHtml = `
          <table class="table table-sm align-middle mb-3">
            <thead>
              <tr>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              ${it.map(row => `
                <tr>
                  <td>
                    ${escapeHtml(row.product_name)}
                    ${row.special_instructions
                      ? '<br><small class="text-muted">Note: ' +
                        escapeHtml(row.special_instructions) +
                        '</small>'
                      : ''}
                  </td>
                  <td class="text-end">${escapeHtml(row.quantity)}</td>
                  <td class="text-end">${escapeHtml(row.unit_price_fmt)}</td>
                  <td class="text-end">${escapeHtml(row.total_price_fmt)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;
      } else {
        itemsHtml = '<p class="text-muted">No items found for this order.</p>';
      }

      const timelineBits = [];
      if (o.created_at)          timelineBits.push('<strong>Placed:</strong> ' + escapeHtml(o.created_at));
      if (o.confirmed_at)        timelineBits.push('<strong>Confirmed:</strong> ' + escapeHtml(o.confirmed_at));
      if (o.preparing_at)        timelineBits.push('<strong>Preparing:</strong> ' + escapeHtml(o.preparing_at));
      if (o.ready_at)            timelineBits.push('<strong>Ready:</strong> ' + escapeHtml(o.ready_at));
      if (o.out_for_delivery_at) timelineBits.push('<strong>Out for delivery:</strong> ' + escapeHtml(o.out_for_delivery_at));
      if (o.delivered_at)        timelineBits.push('<strong>Delivered:</strong> ' + escapeHtml(o.delivered_at));
      if (o.cancelled_at)        timelineBits.push('<strong>Cancelled:</strong> ' + escapeHtml(o.cancelled_at));

      modalBody.innerHTML = `
        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="fw-bold">Customer</h6>
            <p class="mb-1">${escapeHtml(o.customer_name)}</p>
            ${o.customer_email
              ? '<p class="mb-1"><small>' + escapeHtml(o.customer_email) + '</small></p>'
              : ''}
            ${o.customer_phone
              ? '<p class="mb-1"><small>' + escapeHtml(o.customer_phone) + '</small></p>'
              : ''}
            ${
              (o.delivery_address && o.status_label === 'Delivered')
                ? '<p class="mb-0"><small><strong>Address:</strong> ' +
                    escapeHtml(o.delivery_address) + '</small></p>'
                : ''
            }
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold">Order</h6>
            <p class="mb-1"><strong>Type:</strong> ${escapeHtml(o.type_label)}</p>
            <p class="mb-1">
              <strong>Status:</strong>
              <span class="badge ${escapeHtml(o.status_badge_class)} status-badge">
                ${escapeHtml(o.status_label)}
              </span>
            </p>
            <p class="mb-1"><strong>Payment:</strong> ${escapeHtml(o.payment_label)}</p>
          </div>
        </div>

        <h6 class="fw-bold">Items</h6>
        ${itemsHtml}

        <h6 class="fw-bold">Totals</h6>
        <p class="mb-1"><strong>Subtotal:</strong> ${escapeHtml(o.subtotal_formatted)}</p>
        <p class="mb-1"><strong>Delivery fee:</strong> ${escapeHtml(o.delivery_fee_formatted)}</p>
        <p class="mb-1"><strong>Tip:</strong> ${escapeHtml(o.tip_formatted)}</p>
        <p class="mb-0"><strong>Total:</strong> ${escapeHtml(o.total_formatted)}</p>

        ${timelineBits.length
          ? '<hr><h6 class="fw-bold">Timeline</h6><p class="mb-0">' +
            timelineBits.join('<br>') + '</p>'
          : ''}
      `;
    } catch (err) {
      modalBody.innerHTML = '<p class="text-danger mb-0">Network error while loading order details.</p>';
    }
  });

  // Initial load
  loadOrders(1);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
