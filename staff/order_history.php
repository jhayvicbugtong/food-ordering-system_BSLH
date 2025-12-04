<?php
// staff/order_history.php
include __DIR__ . '/includes/header.php';
?>

<style>
  body {
    background-color: #f3f4f6;
  }

  .main-content {
    min-height: 100vh;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }

  /* Modern card */
  .content-card {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
    padding: 18px 20px;
    margin-bottom: 1.5rem;
  }

  .content-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    padding-bottom: 10px;
    margin-bottom: 12px;
  }

  .content-card-header h2 {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 4px;
  }

  .content-card-header p {
    font-size: 0.8rem;
    margin-bottom: 0;
    color: #6b7280;
  }

  .page-title {
    font-weight: 600;
    font-size: 1.3rem;
  }

  .page-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
  }

  .meta-text {
    font-size: 0.8rem;
    color: #9ca3af;
  }

  /* Filters in header */
  .filter-form .form-control,
  .filter-form .form-select {
    font-size: 0.85rem;
    border-radius: 999px;
  }

  .filter-form .form-control:focus,
  .filter-form .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.15);
  }

  .filter-form .input-group-text {
    border-radius: 999px 0 0 999px;
    border-color: #e5e7eb;
    background: #f9fafb;
    font-size: 0.85rem;
    color: #6b7280;
  }

  /* Table styling */
  .dashboard-table {
    margin-bottom: 0;
    width: 100%;
  }

  .dashboard-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
  }

  .dashboard-table th,
  .dashboard-table td {
    font-size: 0.9rem;
    white-space: nowrap !important; /* Prevent wrapping */
    vertical-align: middle;
    padding: 12px 10px;
  }

  .dashboard-table td small {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  .status-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid transparent;
  }

  /* Map the bootstrap-ish badge classes to softer pill colors */
  .status-badge.badge-success,
  .status-badge.bg-success {
    background: #dcfce7;
    color: #166534;
    border-color: rgba(22, 101, 52, 0.18);
  }

  .status-badge.badge-warning,
  .status-badge.bg-warning {
    background: #fef3c7;
    color: #92400e;
    border-color: rgba(146, 64, 14, 0.18);
  }

  .status-badge.badge-primary,
  .status-badge.bg-primary {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: rgba(37, 99, 235, 0.2);
  }

  .status-badge.badge-danger,
  .status-badge.bg-danger {
    background: #fee2e2;
    color: #b91c1c;
    border-color: rgba(185, 28, 28, 0.22);
  }

  .status-badge.badge-secondary,
  .status-badge.bg-secondary {
    background: #e5e7eb;
    color: #374151;
    border-color: rgba(55, 65, 81, 0.16);
  }

  /* Modal Detail Styles */
  .detail-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 2px;
  }
  .detail-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 12px;
  }

  /* Pagination */
  .pagination .page-link {
    font-size: 0.8rem;
    border-radius: 999px !important;
  }

  .pagination .page-item.active .page-link {
    background-color: #4f46e5;
    border-color: #4f46e5;
  }

  /* Mobile Responsive Table (Cards) */
  @media (max-width: 768px) {
    .dashboard-table thead {
        display: none;
    }
    .dashboard-table tbody tr {
        display: block;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        padding: 1rem;
    }
    .dashboard-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border: none;
        text-align: right;
        flex-wrap: wrap;
        white-space: normal !important;
    }
    .dashboard-table td::before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6b7280;
        margin-right: 1rem;
        text-align: left;
    }
    .dashboard-table td:last-child {
        border-top: 1px solid #f3f4f6;
        margin-top: 0.5rem;
        padding-top: 1rem;
        justify-content: flex-end;
    }
    .dashboard-table td:last-child::before {
        display: none;
    }
    
    .content-card-header {
        flex-direction: column;
        align-items: stretch;
    }
    .right {
        width: 100%;
    }
    .filter-form {
        flex-direction: column;
    }
    .filter-form .col-auto {
        width: 100%;
    }
    .filter-form .input-group, 
    .filter-form select, 
    .filter-form button {
        width: 100%;
    }
  }

  @media (max-width: 576px) {
    .content-card {
      padding: 14px 14px;
    }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="content-card mb-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <h2 class="page-title mb-1">Order History</h2>
          <p class="page-subtitle mb-1">Lookup completed, delivered, and cancelled orders.</p>
          <p class="meta-text mb-0" id="record-count">Loading…</p>
        </div>
      </div>
    </div>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Past Orders</h2>
          <p>Filter by status, type, or search by order number.</p>
        </div>

        <div class="right">
          <form id="filter-form" class="row g-2 filter-form" onsubmit="return false;">
            <div class="col-auto">
              <div class="input-group input-group-sm">
                <span class="input-group-text">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  type="text"
                  name="q"
                  class="form-control"
                  placeholder="Search order # or ID"
                >
              </div>
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
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="history-body">
            </tbody>
        </table>
      </div>

      <nav aria-label="Order history pages">
        <ul class="pagination pagination-sm justify-content-end" id="pagination">
          </ul>
      </nav>
    </section>
  </main>
</div>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
        <h5 class="modal-title" style="font-weight: 600;">Order details</h5>
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
  const delay = 250; 

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
          <td colspan="7" class="text-center text-muted">
            No orders match your filters.
          </td>
        </tr>`;
      return;
    }

    bodyEl.innerHTML = rows.map(r => `
      <tr>
        <td data-label="Order #">${escapeHtml(r.order_number)}</td>
        <td data-label="Customer">${escapeHtml(r.customer)}</td>
        <td data-label="Type">${escapeHtml(r.type_label)}</td>
        <td data-label="Total">${escapeHtml(r.total_formatted)}</td>
        <td data-label="Status">
          <span class="badge ${escapeHtml(r.status_badge_class)} status-badge">
            ${escapeHtml(r.status_label)}
          </span>
        </td>
        <td data-label="Placed At">${escapeHtml(r.created_at)}</td>
        <td data-label="Actions">
          <button type="button"
                  class="btn btn-sm btn-outline-primary btn-view-details"
                  data-order-id="${encodeURIComponent(r.order_id)}"
                  data-order-number="${escapeHtml(r.order_number)}">
            <i class="bi bi-eye"></i> View
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
          <tr><td colspan="7" class="text-center text-danger">
            Failed to load data.
          </td></tr>`;
        pagination.innerHTML = '';
        recordCount.textContent = 'Error';
        return;
      }

      const data = await res.json();
      if (!data.success) {
        bodyEl.innerHTML = `
          <tr><td colspan="7" class="text-center text-danger">
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
        <tr><td colspan="7" class="text-center text-danger">
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
    modalBody.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';

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

      // Items HTML
      let itemsHtml = '';
      if (it.length) {
        itemsHtml = `
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-end" width="100">Price</th>
                        <th class="text-center" width="80">Qty</th>
                        <th class="text-end" width="100">Total</th>
                    </tr>
                </thead>
                <tbody>
                  ${it.map(row => `
                    <tr>
                      <td>
                        ${escapeHtml(row.product_name)}
                        ${row.special_instructions
                          ? '<br><small class="text-danger">Note: ' +
                            escapeHtml(row.special_instructions) +
                            '</small>'
                          : ''}
                      </td>
                      <td class="text-end">${escapeHtml(row.unit_price_fmt)}</td>
                      <td class="text-center">${escapeHtml(row.quantity)}</td>
                      <td class="text-end fw-bold">${escapeHtml(row.total_price_fmt)}</td>
                    </tr>
                  `).join('')}
                </tbody>
            </table>
          </div>
        `;
      } else {
        itemsHtml = '<p class="text-muted">No items found for this order.</p>';
      }

      // Timeline
      const timelineBits = [];
      if (o.created_at)          timelineBits.push('<strong>Placed:</strong> ' + escapeHtml(o.created_at));
      if (o.confirmed_at)        timelineBits.push('<strong>Confirmed:</strong> ' + escapeHtml(o.confirmed_at));
      if (o.preparing_at)        timelineBits.push('<strong>Preparing:</strong> ' + escapeHtml(o.preparing_at));
      if (o.ready_at)            timelineBits.push('<strong>Ready:</strong> ' + escapeHtml(o.ready_at));
      if (o.out_for_delivery_at) timelineBits.push('<strong>Out for delivery:</strong> ' + escapeHtml(o.out_for_delivery_at));
      if (o.delivered_at)        timelineBits.push('<strong>Delivered:</strong> ' + escapeHtml(o.delivered_at));
      if (o.cancelled_at)        timelineBits.push('<strong>Cancelled:</strong> ' + escapeHtml(o.cancelled_at));

      // Contact
      let contactInfo = '';
      if (o.customer_phone) contactInfo += escapeHtml(o.customer_phone);
      if (o.customer_email) {
          if (contactInfo) contactInfo += ' / ';
          contactInfo += escapeHtml(o.customer_email);
      }
      if (!contactInfo) contactInfo = 'No contact info';

      // Address Box
      let addressHtml = '';
      if (o.delivery_address) {
          addressHtml = `
            <div class="mb-3 p-3 bg-light rounded border">
                <div class="detail-label"><i class="bi bi-geo-alt-fill"></i> Delivery Address</div>
                <div class="detail-value mb-0">${escapeHtml(o.delivery_address)}</div>
            </div>
          `;
      }

      modalBody.innerHTML = `
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="detail-label">Customer</div>
            <div class="detail-value">${escapeHtml(o.customer_name)}</div>
            
            <div class="detail-label">Contact</div>
            <div class="detail-value">${contactInfo}</div>
          </div>
          <div class="col-md-6">
            <div class="detail-label">Order Type</div>
            <div class="detail-value">${escapeHtml(o.type_label)}</div>

            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="badge ${escapeHtml(o.status_badge_class)}">${escapeHtml(o.status_label)}</span>
            </div>
          </div>
        </div>

        ${addressHtml}

        <h6 class="border-bottom pb-2 mb-3 mt-4">Items Ordered</h6>
        ${itemsHtml}

        <div class="row justify-content-end mt-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-1">
                    <span>Subtotal:</span>
                    <span class="fw-bold">${escapeHtml(o.subtotal_formatted)}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 text-muted">
                    <span>Delivery Fee:</span>
                    <span>${escapeHtml(o.delivery_fee_formatted)}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 text-muted">
                    <span>Tip:</span>
                    <span>${escapeHtml(o.tip_formatted)}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-weight: 700; font-size: 1.1rem; border-top: 2px solid #e5e7eb; padding-top: 10px; margin-top: 10px;">
                    <span>Total:</span>
                    <span class="text-primary">${escapeHtml(o.total_formatted)}</span>
                </div>
                 <div class="mt-2 text-end">
                    <span class="badge bg-secondary">${escapeHtml(o.payment_label)}</span>
                </div>
            </div>
        </div>

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