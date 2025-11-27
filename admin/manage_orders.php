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
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;

// get total rows
$count_sql = "
    SELECT COUNT(DISTINCT o.order_id) AS total
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    WHERE {$whereSql}
";
$count_result = $conn->query($count_sql);
$total_rows = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, (int)ceil($total_rows / $perPage));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $perPage;

// Pagination Link Builder
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$paginationBaseUrl = 'manage_orders.php' . ($baseQuery ? '?' . $baseQuery . '&' : '?');

// ----- FETCH ORDERS (Initial Load) -----
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
    margin-left: 220px; /* Default for desktop */
    transition: margin-left 0.3s ease;
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

  /* Responsive Media Queries */
  @media (max-width: 992px) {
    .main-content {
      margin-left: 0;
    }
  }

  @media (max-width: 768px) {
    .content-card-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .content-card-header .right {
      width: 100%;
    }
    
    .content-card-header .right .btn {
      width: 100%;
    }
    
    .filter-form .d-flex.justify-content-sm-end {
      justify-content: flex-start !important;
      margin-top: 10px;
    }
    
    .filter-form .btn {
      width: 100%;
    }
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
      
      </div>
      <p class="text-muted small mb-0">
        Use the filters below to narrow orders by type or date range.
      </p>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Incoming Orders</h2>
          <p>Pending, confirmed, and in-progress orders.</p>
        </div>
      </div>

      <form class="row g-3 mb-4 filter-form" method="get">
        <input type="hidden" name="page" value="1">
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label mb-1">Order Type</label>
          <select class="form-select form-select-sm" name="order_type">
            <option value="">All</option>
            <option value="pickup" <?= $order_type_filter === 'pickup' ? 'selected' : '' ?>>Pickup</option>
            <option value="delivery" <?= $order_type_filter === 'delivery' ? 'selected' : '' ?>>Delivery</option>
          </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label mb-1">Date From</label>
          <input type="date" class="form-control form-control-sm" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label mb-1">Date To</label>
          <input type="date" class="form-control form-control-sm" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-end justify-content-sm-end">
          <a href="manage_orders.php" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto">Clear filters</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover modern-table">
          <thead>
            <tr>
              <th class="text-nowrap">Order</th>
              <th class="text-nowrap">Customer</th>
              <th class="text-nowrap">Order Type</th>
              <th class="text-nowrap">Payment</th> 
              <th class="text-nowrap">Total (₱)</th>
              <th class="text-nowrap">Status</th>
              <th class="text-end text-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody id="orders-table-body">
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
              <?php while($order = $orders_result->fetch_assoc()): ?>
                <?php
                  // ... (Pre-existing render logic strictly kept) ...
                  $status = $order['status'];
                  $status_map = ['pending'=>'status-pending', 'confirmed'=>'status-confirmed', 'preparing'=>'status-preparing', 'ready'=>'status-ready', 'out_for_delivery'=>'status-out-for-delivery', 'delivered'=>'status-delivered', 'completed'=>'status-completed', 'cancelled'=>'status-cancelled'];
                  $status_class = $status_map[$status] ?? '';
                  $customer_name = htmlspecialchars(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
                  if (trim($customer_name) === '') $customer_name = 'Walk-in Customer';
                  $pay_status = $order['payment_status'] ?? 'unpaid';
                  $pay_badge = 'bg-secondary-subtle text-secondary';
                  if ($pay_status === 'paid') $pay_badge = 'bg-success-subtle text-success';
                  elseif ($pay_status === 'failed') $pay_badge = 'bg-danger-subtle text-danger';
                  elseif ($pay_status === 'refunded') $pay_badge = 'bg-info-subtle text-info';
                ?>
                <tr data-row-id="<?= (int)$order['order_id'] ?>">
                  <td class="text-nowrap">
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong><br>
                    <small class="text-muted"><?= date('Y-m-d g:i A', strtotime($order['created_at'])) ?></small>
                  </td>
                  <td class="text-nowrap">
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
                  <td><span class="badge <?= $pay_badge ?> badge-rounded"><?= ucfirst($pay_status ?: 'Pending') ?></span></td>
                  <td class="text-nowrap">₱<?= number_format((float)$order['total_amount'], 2) ?></td>
                  <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span></td>
                  <td class="text-end text-nowrap">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary btn-view" data-order-id="<?= (int)$order['order_id'] ?>">
                        <i class="bi bi-eye"></i> View
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No orders found for this filter.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav aria-label="Orders pagination">
          <ul class="pagination justify-content-center justify-content-sm-end mt-3 flex-wrap">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . ($page - 1)) ?>">&laquo;</a>
            </li>
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
              <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . $p) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= htmlspecialchars($paginationBaseUrl . 'page=' . ($page + 1)) ?>">&raquo;</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </section>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Order Details — <span id="od-order-number"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <div class="modal-body">
            <div class="row mb-3 g-3">
              <div class="col-md-6"><p class="mb-1"><strong>Status:</strong> <span id="od-status"></span></p><p class="mb-1"><strong>Type:</strong> <span id="od-type"></span></p><p class="mb-1"><strong>Placed:</strong> <span id="od-created-at"></span></p><p class="mb-1"><strong>Address:</strong> <span id="od-address"></span></p></div>
              <div class="col-md-6"><p class="mb-1"><strong>Customer:</strong> <span id="od-customer"></span></p><p class="mb-1"><strong>Phone:</strong> <span id="od-phone"></span></p><p class="mb-1"><strong>Payment:</strong> <span id="od-payment"></span></p></div>
            </div><hr><h6>Items</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th></tr></thead><tbody id="od-items-body"></tbody><tfoot><tr><th colspan="3" class="text-end">Grand Total</th><th class="text-end" id="od-total">₱0.00</th></tr></tfoot></table></div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // --- REAL-TIME POLLING ---
  const tbody = document.getElementById('orders-table-body');
  
  function fetchUpdates() {
      // Get current URL search params to respect filters & pagination
      const params = window.location.search;
      
      fetch('actions/fetch_orders_updates.php' + params)
          .then(res => res.json())
          .then(data => {
              if (data.html) {
                  // Only update DOM if HTML changed (avoids selection reset)
                  if (tbody.innerHTML !== data.html) {
                      tbody.innerHTML = data.html;
                  }
              }
          })
          .catch(err => console.error('Polling error:', err));
  }

  // Poll every 1 seconds
  setInterval(fetchUpdates, 1000);

  // Manual Refresh
  document.getElementById('btn-refresh')?.addEventListener('click', () => {
    location.reload(); // Force full reload to get fresh pagination etc
  });

  // --- ORDER DETAILS MODAL (Using Event Delegation) ---
  const odModalEl = document.getElementById('orderDetailsModal');
  const odModal = odModalEl ? new bootstrap.Modal(odModalEl) : null;
  const odOrderNumber = document.getElementById('od-order-number');
  const odStatus      = document.getElementById('od-status');
  const odType        = document.getElementById('od-type');
  const odCustomer    = document.getElementById('od-customer');
  const odPhone       = document.getElementById('od-phone');
  const odCreatedAt   = document.getElementById('od-created-at');
  const odPayment     = document.getElementById('od-payment');
  const odAddress     = document.getElementById('od-address');
  const odItemsBody   = document.getElementById('od-items-body');
  const odTotal       = document.getElementById('od-total');

  // Use event delegation on tbody instead of attaching to .btn-view directly
  tbody.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-view');
      if (!btn || !odModal) return;

      const row = btn.closest('tr');
      const orderId = btn.dataset.orderId;

      // Initial Placeholder Values
      odOrderNumber.textContent = '#' + row.dataset.rowId;
      odStatus.textContent      = 'Loading...';
      odType.textContent        = 'Loading...';
      odCustomer.textContent    = 'Loading...';
      odPhone.textContent       = 'Loading...';
      odCreatedAt.textContent   = 'Loading...';
      odAddress.textContent     = 'Loading...';
      odPayment.textContent     = 'Loading...';
      odItemsBody.innerHTML     = '<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>';
      odTotal.textContent       = '₱0.00';

      odModal.show();

      try {
        const res  = await fetch('actions/get_order_details.php?order_id=' + encodeURIComponent(orderId));
        const data = await res.json();

        if (data.status !== 'ok') {
          throw new Error(data.message || 'Failed to load order details');
        }

        const o = data.order;
        odOrderNumber.textContent = o.order_number;
        odStatus.textContent      = o.status;
        odType.textContent        = o.type;
        odCustomer.textContent    = o.customer;
        odPhone.textContent       = o.phone;
        odCreatedAt.textContent   = o.created_at;
        odAddress.textContent     = o.delivery_address || 'N/A (Pickup or Walk-in)';
        odTotal.textContent       = '₱' + o.total_amount.toFixed(2);

        // Payment Layout Helper
        function toTitle(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase() : ''; }

        if (data.payment && data.payment.details) {
          const p = data.payment.details;
          const status = toTitle(p.status || '');
          const method = toTitle(p.method || '');
          const mainParts = [];
          if (status) mainParts.push(status);
          if (method) mainParts.push(method);

          const metaParts = [];
          if (p.amount_paid != null) metaParts.push(`Amount: ₱${Number(p.amount_paid).toFixed(2)}`);
          if (p.change_amount != null) metaParts.push(`Change: ₱${Number(p.change_amount).toFixed(2)}`);
          if (p.paid_at) metaParts.push(`Paid: ${p.paid_at}`);

          let html = '';
          if (mainParts.length) html += `<span class="od-payment-main">${mainParts.join(' — ')}</span>`;
          if (metaParts.length) html += `<span class="od-payment-meta">${metaParts.join(' • ')}</span>`;
          odPayment.innerHTML = html || 'No payment recorded';
        } else {
          odPayment.textContent = 'No payment recorded';
        }

        // Items
        odItemsBody.innerHTML = '';
        if (!data.items || !data.items.length) {
          odItemsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>';
        } else {
          data.items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${it.name}</td><td class="text-end">${it.qty}</td><td class="text-end">₱${Number(it.price).toFixed(2)}</td><td class="text-end">₱${Number(it.total).toFixed(2)}</td>`;
            odItemsBody.appendChild(tr);
          });
        }
      } catch (err) {
        console.error(err);
        odItemsBody.innerHTML = '<tr><td colspan="4" class="text-danger text-center">Error: ' + err.message + '</td></tr>';
      }
  });

  // Filter Auto-Submit
  const filterForm = document.querySelector('.filter-form');
  if (filterForm) {
    filterForm.querySelectorAll('select, input[type="date"]').forEach(el => {
      el.addEventListener('change', () => {
        const pageInput = filterForm.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = 1;
        filterForm.submit();
      });
    });
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>