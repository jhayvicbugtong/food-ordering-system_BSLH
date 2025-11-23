<?php
include __DIR__ . '/includes/header.php'; // Includes db_connect.php

// ----- FILTERS -----
$order_type_filter = $_GET['order_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

// Build WHERE clause based on filters
$whereClauses = ["1=1"];

if ($order_type_filter === 'pickup' || $order_type_filter === 'delivery') {
    $order_type_esc = $conn->real_escape_string($order_type_filter);
    $whereClauses[] = "o.order_type = '{$order_type_esc}'";
}

if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $date_from_esc = $conn->real_escape_string($date_from);
    $whereClauses[] = "DATE(o.created_at) >= '{$date_from_esc}'";
}

if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $date_to_esc = $conn->real_escape_string($date_to);
    $whereClauses[] = "DATE(o.created_at) <= '{$date_to_esc}'";
}

$whereSql = implode(' AND ', $whereClauses);

// ----- PAGINATION SETTINGS -----
$perPage = 10;

// current page from ?page=, default 1
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0
    ? (int)$_GET['page']
    : 1;

// get total rows for this filter
$count_sql = "
    SELECT COUNT(DISTINCT o.order_id) AS total
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    WHERE {$whereSql}
";
$count_result = $conn->query($count_sql);
$total_rows = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;

$total_pages = max(1, (int)ceil($total_rows / $perPage));

// clamp page in valid range
if ($page > $total_pages) {
    $page = $total_pages;
}

// compute offset
$offset = ($page - 1) * $perPage;

// base link for pagination (preserve filters, change only page)
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$paginationBaseUrl = 'manage_orders.php' . ($baseQuery ? '?' . $baseQuery . '&' : '?');

// ----- FETCH ORDERS -----
// Joined order_payment_details to get payment_status
$orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.total_amount, 
        o.status,
        o.created_at,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        ocd.customer_phone,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE {$whereSql}
    GROUP BY o.order_id
    ORDER BY 
        CASE o.status
            WHEN 'pending' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'preparing' THEN 3
            WHEN 'ready' THEN 4
            WHEN 'out_for_delivery' THEN 5
            ELSE 6
        END,
        o.created_at DESC
    LIMIT {$perPage} OFFSET {$offset};
";
$orders_result = $conn->query($orders_query);
?>

<style>
  /* Global background to match other modern pages */
  body {
    background-color: #f3f4f6;
  }

  .main-content {
    min-height: 100vh;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }

  /* Modern cards */
  .content-card {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
    padding: 18px 20px;
  }

  .content-card-header {
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    padding-bottom: 10px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
  }

  .content-card-header .left h2 {
    font-size: 1.15rem;
    font-weight: 600;
    margin-bottom: 2px;
  }

  .content-card-header .left p {
    margin: 0;
    font-size: 0.8rem;
    color: #6b7280;
  }

  .content-card-header .right .btn {
    border-radius: 999px;
    font-size: 0.85rem;
  }

  /* Status badges */
  .status-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .status-pending          { background:#fef3c7; color:#92400e; }
  .status-confirmed        { background:#dbeafe; color:#1d4ed8; }
  .status-preparing        { background:#e0f2fe; color:#0369a1; }
  .status-ready            { background:#dcfce7; color:#15803d; }
  .status-out-for-delivery { background:#e0f2fe; color:#0369a1; }
  .status-delivered,
  .status-completed        { background:#e5e7eb; color:#111827; }
  .status-cancelled        { background:#fee2e2; color:#b91c1c; }

  /* Filter section */
  .filter-form .form-label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
  }

  .filter-form .form-control,
  .filter-form .form-select {
    font-size: 0.85rem;
    border-radius: 999px;
    border-color: #e5e7eb;
  }

  .filter-form .form-control:focus,
  .filter-form .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.15);
  }

  .filter-form .btn {
    border-radius: 999px;
  }

  /* Table */
  .modern-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
  }

  .modern-table tbody td {
    font-size: 0.9rem;
    vertical-align: middle;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Pills for order type badges */
  .badge-rounded {
    border-radius: 999px;
    padding: 0.25rem 0.6rem;
    font-size: 0.75rem;
  }

  /* Pagination */
  .pagination .page-link {
    border-radius: 999px !important;
    font-size: 0.8rem;
  }

  /* Payment section in Order Details modal */
  #od-payment {
    line-height: 1.3;
  }
  .od-payment-main {
    display: block;
    font-weight: 600;
  }
  .od-payment-meta {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 2px;
    text-transform: capitalize;
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Management</h2>
          <p>Monitor, review, and manage all active orders.</p>
        </div>
        <div class="right">
          <button class="btn btn-success" id="btn-refresh">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>
      <p class="text-muted small mb-0">
        Use the filters below to narrow orders by type or date range. This list focuses on live and recent activity.
      </p>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Incoming Orders</h2>
          <p>Pending, confirmed, and in-progress orders.</p>
        </div>
      </div>

      <form class="row g-2 mb-3 filter-form" method="get">
        <input type="hidden" name="page" value="1">

        <div class="col-md-3 col-sm-6">
          <label class="form-label mb-1">Order Type</label>
          <select class="form-select form-select-sm" name="order_type">
            <option value="">All</option>
            <option value="pickup"   <?= $order_type_filter === 'pickup'   ? 'selected' : '' ?>>Pickup</option>
            <option value="delivery" <?= $order_type_filter === 'delivery' ? 'selected' : '' ?>>Delivery</option>
          </select>
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label mb-1">Date From</label>
          <input type="date"
                 class="form-control form-control-sm"
                 name="date_from"
                 value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label mb-1">Date To</label>
          <input type="date"
                 class="form-control form-control-sm"
                 name="date_to"
                 value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="col-md-3 col-sm-6 d-flex align-items-end justify-content-sm-end">
          <a href="manage_orders.php" class="btn btn-outline-secondary btn-sm">
            Clear filters
          </a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover modern-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Customer</th>
              <th>Order Type</th>
              <th>Payment</th> <th>Total (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>

            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
              <?php while($order = $orders_result->fetch_assoc()): ?>
                <?php
                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'status-pending',
                    'confirmed'        => 'status-confirmed',
                    'preparing'        => 'status-preparing',
                    'ready'            => 'status-ready',
                    'out_for_delivery' => 'status-out-for-delivery',
                    'delivered'        => 'status-delivered',
                    'completed'        => 'status-completed',
                    'cancelled'        => 'status-cancelled',
                  ];
                  $status_class = $status_map[$status] ?? '';
                  $customer_name = htmlspecialchars(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
                  if (trim($customer_name) === '') {
                    $customer_name = 'Walk-in Customer';
                  }
                  
                  // Payment status logic
                  $pay_status = $order['payment_status'] ?? 'unpaid';
                  $pay_badge = 'bg-secondary-subtle text-secondary'; // default
                  if ($pay_status === 'paid') {
                      $pay_badge = 'bg-success-subtle text-success';
                  } elseif ($pay_status === 'failed') {
                      $pay_badge = 'bg-danger-subtle text-danger';
                  } elseif ($pay_status === 'refunded') {
                      $pay_badge = 'bg-info-subtle text-info';
                  }
                ?>
                <tr data-row-id="<?= (int)$order['order_id'] ?>">
                  <td>
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong><br>
                    <small class="text-muted"><?= date('Y-m-d g:i A', strtotime($order['created_at'])) ?></small>
                  </td>
                  <td>
                    <?= $customer_name ?><br>
                    <?php if (!empty($order['customer_phone'])): ?>
                      <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($order['order_type'] == 'delivery'): ?>
                      <span class="badge bg-success-subtle text-success badge-rounded">Delivery</span>
                    <?php else: ?>
                      <span class="badge bg-primary-subtle text-primary badge-rounded">Pickup</span>
                    <?php endif; ?>
                  </td>
                  <td>
                      <span class="badge <?= $pay_badge ?> badge-rounded">
                          <?= ucfirst($pay_status ?: 'Pending') ?>
                      </span>
                  </td>
                  <td>₱<?= number_format((float)$order['total_amount'], 2) ?></td>
                  <td>
                    <span class="status-badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary btn-view"
                              data-order-id="<?= (int)$order['order_id'] ?>">
                        <i class="bi bi-eye"></i> View
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  No orders found for this filter.
                </td>
              </tr>
            <?php endif; ?>

          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav aria-label="Orders pagination">
          <ul class="pagination justify-content-end mt-3">

            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <?php if ($page <= 1): ?>
                <span class="page-link" aria-label="Previous">&laquo;</span>
              <?php else: ?>
                <a class="page-link"
                   href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . ($page - 1)) ?>"
                   aria-label="Previous">&laquo;</a>
              <?php endif; ?>
            </li>

            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
              <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a class="page-link"
                   href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . $p) ?>">
                  <?= $p ?>
                </a>
              </li>
            <?php endfor; ?>

            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
              <?php if ($page >= $total_pages): ?>
                <span class="page-link" aria-label="Next">&raquo;</span>
              <?php else: ?>
                <a class="page-link"
                   href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . ($page + 1)) ?>"
                   aria-label="Next">&raquo;</a>
              <?php endif; ?>
            </li>

          </ul>
        </nav>
      <?php endif; ?>

    </section>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Order Details — <span id="od-order-number"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Status:</strong> <span id="od-status"></span></p>
                <p class="mb-1"><strong>Type:</strong> <span id="od-type"></span></p>
                <p class="mb-1"><strong>Placed:</strong> <span id="od-created-at"></span></p>
                <p class="mb-1"><strong>Address:</strong> <span id="od-address"></span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Customer:</strong> <span id="od-customer"></span></p>
                <p class="mb-1"><strong>Phone:</strong> <span id="od-phone"></span></p>
                <p class="mb-1"><strong>Payment:</strong> <span id="od-payment"></span></p>
              </div>
            </div>
            <hr>
            <h6>Items</h6>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody id="od-items-body"></tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-end">Grand Total</th>
                    <th class="text-end" id="od-total">₱0.00</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="paymentForm">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Payment — <span id="pm-order-code"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="order_id" id="pm-order-id">

            <div class="mb-3">
              <label class="form-label">Customer</label>
              <input class="form-control" id="pm-customer" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Total Amount (₱)</label>
              <input class="form-control" id="pm-total" name="amount" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Payment status</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_status" id="pm-paid" value="paid" checked>
                <label class="form-check-label" for="pm-paid">Paid now (payment received)</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_status" id="pm-unpaid" value="unpaid">
                <label class="form-check-label" for="pm-unpaid">Pay on delivery (Unpaid/COD)</label>
              </div>
            </div>

            <div id="pm-method-wrap" class="row g-2">
              <div class="col-6">
                <label class="form-label">Method</label>
                <select class="form-select" name="payment_method">
                  <option value="Cash">Cash</option>
                  <option value="GCash">GCash</option>
                  <option value="Card">Card</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Reference # (optional)</label>
                <input class="form-control" name="reference_no" placeholder="e.g. GCash Ref">
              </div>
            </div>

            <div class="form-text">
              After confirmation, order status will move to <b>Preparing</b>.
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Confirm & Accept</button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // ----- REFRESH -----
  document.getElementById('btn-refresh')?.addEventListener('click', () => {
    location.reload();
  });

  // ----- ORDER DETAILS MODAL (VIEW) -----
  const odModalEl = document.getElementById('orderDetailsModal');
  const odModal = odModalEl ? new bootstrap.Modal(odModalEl) : null;
  const odOrderNumber = document.getElementById('od-order-number');
  const odStatus      = document.getElementById('od-status');
  const odType        = document.getElementById('od-type');
  const odCustomer    = document.getElementById('od-customer');
  const odPhone       = document.getElementById('od-phone');
  const odCreatedAt   = document.getElementById('od-created-at');
  const odPayment     = document.getElementById('od-payment');
  const odAddress     = document.getElementById('od-address'); // NEW
  const odItemsBody   = document.getElementById('od-items-body');
  const odTotal       = document.getElementById('od-total');

  document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const row = e.currentTarget.closest('tr');
      if (!row || !odModal) return;

      const orderId = e.currentTarget.dataset.orderId;

      // Initial values from row (placeholder)
      odOrderNumber.textContent = '#' + row.dataset.rowId;
      odStatus.textContent      = '';
      odType.textContent        = '';
      odCustomer.textContent    = '';
      odPhone.textContent       = '';
      odCreatedAt.textContent   = '';
      odAddress.textContent     = 'Loading...'; // NEW
      odPayment.textContent     = 'Loading payment...';

      odItemsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>';
      odTotal.textContent   = '₱0.00';

      odModal.show();

      try {
        const res  = await fetch('actions/get_order_details.php?order_id=' + encodeURIComponent(orderId));
        const text = await res.text();
        
        let data;
        try {
             data = JSON.parse(text);
        } catch (jsonErr) {
             console.error('JSON Parse error:', jsonErr, 'Raw:', text);
             throw new Error('Invalid server response');
        }

        if (data.status !== 'ok') {
          throw new Error(data.message || 'Failed to load order details');
        }

        const o = data.order;

        // Overwrite with real data from server
        odOrderNumber.textContent = o.order_number;
        odStatus.textContent      = o.status;
        odType.textContent        = o.type;
        odCustomer.textContent    = o.customer;
        odPhone.textContent       = o.phone;
        odCreatedAt.textContent   = o.created_at;
        // Populate address
        odAddress.textContent     = o.delivery_address || 'N/A (Pickup or Walk-in)';
        
        odTotal.textContent       = '₱' + o.total_amount.toFixed(2);

        // PAYMENT LAYOUT
        function toTitle(str) {
          if (!str) return '';
          return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }

        if (data.payment && data.payment.details) {
          const p = data.payment.details;

          const status = toTitle(p.status || '');
          const method = toTitle(p.method || '');

          const mainParts = [];
          if (status) mainParts.push(status);
          if (method) mainParts.push(method);

          const metaParts = [];
          if (p.amount_paid != null) {
            metaParts.push(`Amount: ₱${Number(p.amount_paid).toFixed(2)}`);
          }
          if (p.change_amount != null) {
            metaParts.push(`Change: ₱${Number(p.change_amount).toFixed(2)}`);
          }
          if (p.paid_at) {
            metaParts.push(`Paid: ${p.paid_at}`);
          }

          let html = '';

          if (mainParts.length) {
            html += `<span class="od-payment-main">${mainParts.join(' — ')}</span>`;
          }

          if (metaParts.length) {
            html += `<span class="od-payment-meta">${metaParts.join(' • ')}</span>`;
          }

          odPayment.innerHTML = html || 'No payment recorded';
        } else {
          odPayment.textContent = 'No payment recorded';
        }

        // ITEMS
        odItemsBody.innerHTML = '';
        if (!data.items || !data.items.length) {
          odItemsBody.innerHTML =
            '<tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>';
        } else {
          data.items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${it.name}</td>
              <td class="text-end">${it.qty}</td>
              <td class="text-end">₱${Number(it.price).toFixed(2)}</td>
              <td class="text-end">₱${Number(it.total).toFixed(2)}</td>
            `;
            odItemsBody.appendChild(tr);
          });
        }
      } catch (err) {
        console.error(err);
        odItemsBody.innerHTML =
          '<tr><td colspan="4" class="text-danger text-center">Error loading items: '
          + err.message + '</td></tr>';
        odPayment.textContent = 'Error loading payment';
        odAddress.textContent = 'Error';
      }
    });
  });

  // ----- PAYMENT MODAL (existing logic) -----
  const modalEl = document.getElementById('paymentModal');
  if (!modalEl) return;
  
  const paymentModal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('paymentForm');
  const orderCodeEl = document.getElementById('pm-order-code');
  const orderIdInput = document.getElementById('pm-order-id');
  const totalInput = document.getElementById('pm-total');
  const customerInput = document.getElementById('pm-customer');
  const paidRadio = document.getElementById('pm-paid');
  const unpaidRadio = document.getElementById('pm-unpaid');
  const methodWrap = document.getElementById('pm-method-wrap');

  function toggleMethod() {
    methodWrap.style.display = paidRadio.checked ? '' : 'none';
  }
  paidRadio.addEventListener('change', toggleMethod);
  unpaidRadio.addEventListener('change', toggleMethod);
  toggleMethod();

  // Open payment modal with row data
  document.querySelectorAll('.btn-accept').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const b = e.currentTarget;
      const row = b.closest('tr');
      const id = b.dataset.orderId;
      const total = (b.dataset.total || '0').replace(/[₱,\s]/g,'');
      const customer = b.dataset.customer || 'N/A';
      const orderNum = row.querySelector('td:first-child strong')?.textContent || ('#' + id);

      orderCodeEl.textContent = orderNum;
      orderIdInput.value = id;
      totalInput.value = parseFloat(total).toFixed(2);
      customerInput.value = customer;

      form.dataset.rowId = id;
      paidRadio.checked = true;
      toggleMethod();

      paymentModal.show();
    });
  });

  // Submit accept + payment
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Accepting...';
    
    const fd = new FormData(form);

    try {
      const res = await fetch('actions/accept_order.php', {
        method: 'POST',
        body: fd
      });
      
      const responseText = await res.text();
      let json;
      try {
        json = JSON.parse(responseText);
      } catch (jsonErr) {
        throw new Error('Server returned invalid response: ' + responseText);
      }

      if (json.status !== 'ok') {
        throw new Error(json.message || 'Failed to accept order');
      }

      // Update UI for the row to "Preparing"
      const row = document.querySelector(`tr[data-row-id="${form.dataset.rowId}"]`);
      if (row) {
        const badge = row.querySelector('td:nth-child(5) .status-badge'); // Adjusted index if columns shifted, but likely safest to use class
        // Actually, we just added a column, so status-badge is further right.
        // Better selector:
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) { 
          statusBadge.className = 'status-badge status-preparing';
          statusBadge.textContent = 'Preparing'; 
        }
        const actions = row.querySelector('.btn-group');
        if (actions) {
          actions.innerHTML = `
            <button class="btn btn-outline-secondary btn-view" data-order-id="${form.dataset.rowId}"><i class="bi bi-eye"></i> View</button>
            <button class="btn btn-outline-success">Mark as Ready</button>
          `;
        }
      }

      paymentModal.hide();
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
       submitBtn.disabled = false;
       submitBtn.innerHTML = 'Confirm & Accept';
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const filterForm = document.querySelector('.filter-form');
  if (!filterForm) return;

  // Auto-submit when ANY filter changes
  filterForm.querySelectorAll('select, input[type="date"]').forEach(el => {
    el.addEventListener('change', () => {
      // Always reset page to 1 when filters change
      const pageInput = filterForm.querySelector('input[name="page"]');
      if (pageInput) pageInput.value = 1;

      filterForm.submit();
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>