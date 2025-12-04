<?php
include __DIR__ . '/includes/header.php'; // Includes auth and db_connect

// Fetch orders for the table
// Orders are sorted strictly by creation time (Oldest first).
$orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status, 
        o.created_at,
        o.total_amount,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        opd.payment_method,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.status NOT IN ('completed', 'delivered', 'cancelled')
      AND NOT (o.order_type = 'delivery' AND o.status IN ('ready', 'out_for_delivery'))
    ORDER BY o.created_at ASC
    LIMIT 100; 
";
$orders_result = $conn->query($orders_query);
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

  .page-title {
    font-weight: 600;
    font-size: 1.3rem;
  }

  .page-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
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
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    padding-bottom: 10px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
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

  .meta-text {
    font-size: 0.8rem;
    color: #9ca3af;
  }

  /* Table styling */
  .orders-queue-table {
    margin-bottom: 0;
    width: 100%;
  }

  .orders-queue-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
  }

  .orders-queue-table th,
  .orders-queue-table td {
    font-size: 0.9rem;
    vertical-align: middle;
    padding: 12px;
  }

  .orders-queue-table td small {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Pills / chips */
  .status-badge,
  .payment-badge,
  .source-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }

  /* Source pills */
  .source-pill.delivery {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid rgba(37, 99, 235, 0.18);
  }

  .source-pill.pickup {
    background: #ecfdf3;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.18);
  }

  /* Payment pills */
  .payment-badge.badge-success,
  .payment-badge.bg-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.15);
  }

  .payment-badge.badge-warning,
  .payment-badge.bg-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid rgba(146, 64, 14, 0.12);
  }

  .payment-badge.badge-secondary,
  .payment-badge.bg-secondary {
    background: #e5e7eb;
    color: #374151;
    border: 1px solid rgba(55, 65, 81, 0.12);
  }

  /* Status pills */
  /* ADDED: Missing badge-primary class for 'Confirmed' state */
  .status-badge.badge-primary,
  .status-badge.bg-primary {
    background: #dbeafe; 
    color: #1d4ed8; 
    border: 1px solid rgba(37, 99, 235, 0.2);
  }

  .status-badge.badge-warning,
  .status-badge.bg-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid rgba(146, 64, 14, 0.12);
  }

  .status-badge.badge-success,
  .status-badge.bg-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.15);
  }

  .status-badge.badge-secondary,
  .status-badge.bg-secondary {
    background: #e5e7eb;
    color: #374151;
    border: 1px solid rgba(55, 65, 81, 0.12);
  }

  .status-badge.badge-info,
  .status-badge.bg-info {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid rgba(3, 105, 161, 0.15);
  }

  .actions-cell .btn {
    white-space: nowrap;
  }

  /* Search Box Styles */
  .search-box {
    position: relative;
    width: 250px;
  }
  .search-box .form-control {
    padding-left: 2.5rem;
    border-radius: 999px;
    border-color: #e5e7eb;
    font-size: 0.9rem;
  }
  .search-box .form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
  .search-box .bi-search {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
  }

  /* Modal Tweaks */
  #viewOrderModal .modal-header {
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
  }
  #viewOrderModal .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
  }
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
  .modal-total-row {
    font-weight: 700;
    font-size: 1.1rem;
    border-top: 2px solid #e5e7eb;
    padding-top: 10px;
    margin-top: 10px;
  }

  /* Mobile Responsive Table (Cards) */
  @media (max-width: 768px) {
    .orders-queue-table thead {
        display: none;
    }
    .orders-queue-table tbody tr {
        display: block;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        padding: 1rem;
    }
    .orders-queue-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border: none;
        text-align: right;
        flex-wrap: wrap;
    }
    .orders-queue-table td::before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6b7280;
        margin-right: 1rem;
        text-align: left;
    }
    .orders-queue-table td:last-child {
        border-top: 1px solid #f3f4f6;
        margin-top: 0.5rem;
        padding-top: 1rem;
        justify-content: flex-end;
    }
    .orders-queue-table td:last-child::before {
        display: none;
    }
    /* Align lists inside tables properly */
    .orders-queue-table ul {
        text-align: right;
        width: 100%;
    }
    
    .content-card-header {
        flex-direction: column;
        align-items: stretch;
    }
    .search-box {
        width: 100%;
        margin-top: 10px;
    }
    .meta-text {
        display: none; /* Hide meta text on mobile */
    }
  }

  @media (max-width: 576px) {
    .content-card {
      padding: 14px 14px;
    }
  }
  
  /* Fade animation for row updates */
  .fade-in-row { animation: fadeIn 0.5s; }
  @keyframes fadeIn { from { opacity: 0; background-color: #ecfdf3; } to { opacity: 1; background-color: transparent; } }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <div class="content-card mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <h2 class="page-title mb-1">Orders Queue</h2>
          <p class="page-subtitle mb-1">All active orders that still need action.</p>
          <p class="meta-text mb-0">Sorted by time placed (Oldest first).</p>
        </div>
      </div>
    </div>

    <section class="content-card">
      <div class="content-card-header">
        <div>
          <h2>Active Orders</h2>
          <p>Pending, confirmed, preparing, and pickup-ready orders.</p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="orderSearchInput" class="form-control" placeholder="Search order #, customer...">
            </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover orders-queue-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Items</th> 
              <th>Customer</th>
              <th>Total</th> 
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="ordersTableBody">
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
              <?php while($order = $orders_result->fetch_assoc()): ?>
                <?php
                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'badge-warning',
                    'confirmed'        => 'badge-primary', // UPDATED: Match API/JS logic (was badge-info)
                    'preparing'        => 'badge-info',
                    'ready'            => 'badge-success',
                    'out_for_delivery' => 'badge-success',
                  ];
                  $status_class   = $status_map[$status] ?? 'badge-secondary';

                  $customer_name  = htmlspecialchars(trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')));
                  if ($customer_name === '') {
                    $customer_name = 'Walk-in Customer';
                  }

                  $order_id       = (int)$order['order_id'];
                  $order_number   = htmlspecialchars($order['order_number'] ?? $order_id);
                  $payment_status = $order['payment_status'] ?? null;
                  $created_time   = $order['created_at']
                    ? date('g:i A', strtotime($order['created_at']))
                    : '';

                  // Payment display
                  $payment_method = $order['payment_method'] ?? null;
                  if (!$payment_method) {
                    $payment_label = 'Unpaid';
                    $payment_badge_class = 'badge-secondary';
                  } else {
                    $payment_label = strtoupper($payment_method);
                    if ($payment_status && $payment_status !== 'paid') {
                      $payment_label .= ' (' . ucfirst($payment_status) . ')';
                    } else {
                      $payment_label .= ' (Paid)';
                    }
                    $payment_badge_class = ($payment_status === 'paid')
                      ? 'badge-success'
                      : 'badge-warning';
                  }

                  // Source pill class
                  if ($order['order_type'] == 'delivery') {
                    $source_class = 'delivery';
                    $source_label = 'Delivery';
                  } else {
                    $source_class = 'pickup';
                    $source_label = 'Pickup';
                  }

                  // Total
                  $total = ($order['total_amount'] ?? 0);
                ?>
                <tr data-order-id="<?= $order_id ?>" 
                    data-order-type="<?= htmlspecialchars($order['order_type']) ?>" 
                    data-payment-status="<?= htmlspecialchars($payment_status ?? '') ?>"
                    data-status="<?= htmlspecialchars($status) ?>"
                    class="order-row">
                  <td data-label="Order #">
                    <div class="searchable-text">
                        <strong><?= $order_number ?></strong>
                        <?php if ($created_time): ?>
                          <div class="meta-text">Placed: <?= htmlspecialchars($created_time) ?></div>
                        <?php endif; ?>
                    </div>
                  </td>
                  
                  <td data-label="Items">
                    <ul class="list-unstyled mb-0 searchable-text" style="padding-left: 0; font-size: 0.85em;">
                      <?php
                        // Fetch items for this order
                        $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ? LIMIT 3");
                        $items_stmt->bind_param('i', $order_id);
                        $items_stmt->execute();
                        $items_result = $items_stmt->get_result();
                        $item_count = 0;
                        while($item = $items_result->fetch_assoc()):
                          $item_count++;
                      ?>
                        <li><?= htmlspecialchars($item['product_name']) ?> x <strong><?= (int)$item['quantity'] ?></strong></li>
                      <?php endwhile; $items_stmt->close(); ?>
                      <?php if ($item_count >= 3): ?>
                        <li class="text-muted" style="font-size:0.8em;">...and more</li>
                      <?php endif; ?>
                    </ul>
                  </td>

                  <td data-label="Customer" class="searchable-text"><?= $customer_name ?></td>
                  <td data-label="Total">
                    <span style="font-weight:600; white-space:nowrap;">
                        ₱<?= number_format($total, 2) ?>
                    </span>
                  </td>
                  <td data-label="Type / Payment">
                    <div class="d-flex flex-column align-items-end align-items-md-start gap-1">
                        <span class="source-pill <?= $source_class ?>">
                          <?= htmlspecialchars($source_label) ?>
                        </span>
                        <span class="payment-badge badge <?= $payment_badge_class ?>">
                          <?= htmlspecialchars($payment_label) ?>
                        </span>
                    </div>
                  </td>
                  <td data-label="Status">
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <div class="d-flex align-items-center justify-content-end justify-content-md-start gap-2 action-buttons-container">
                        
                        <button class="btn btn-sm btn-outline-secondary btn-view-details" data-id="<?= $order_id ?>" title="View Details">
                             <i class="bi bi-eye"></i>
                        </button>

                        <div class="btn-group btn-group-sm action-group">
                        <?php if ($status == 'pending'): ?>
                            <button class="btn btn-outline-success btn-action" data-action="confirm" data-id="<?= $order_id ?>">Accept</button>
                            <button class="btn btn-outline-danger btn-action" data-action="cancel" data-id="<?= $order_id ?>">Reject</button>

                        <?php elseif ($status == 'confirmed'): ?>
                            <button class="btn btn-outline-primary btn-action" data-action="prepare" data-id="<?= $order_id ?>">Prep</button>

                        <?php elseif ($status == 'preparing'): ?>
                            <button class="btn btn-outline-success btn-action" data-action="ready" data-id="<?= $order_id ?>">Ready</button>

                        <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup' && $payment_status === 'paid'): ?>
                            <button class="btn btn-outline-success btn-action" data-action="complete" data-id="<?= $order_id ?>">Done</button>

                        <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup' && $payment_status !== 'paid'): ?>
                            <a href="pos_payment.php?order_id=<?= $order_id ?>" class="btn btn-outline-success">Pay</a>

                        <?php endif; ?>
                        </div>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr id="no-orders-row">
                <td colspan="7" class="text-center text-muted">No active orders in the queue.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <div id="no-search-results" class="text-center py-4 text-muted" style="display: none;">
            <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin-bottom: 10px;"></i>
            No orders match your search.
        </div>
      </div>
    </section>

  </main>
</div>

<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Details #<span id="modalOrderNumber"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modalLoader" class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        
        <div id="modalContent" style="display:none;">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="detail-label">Customer</div>
                    <div class="detail-value" id="modalCustomer"></div>
                    
                    <div class="detail-label">Contact</div>
                    <div class="detail-value" id="modalContact"></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Order Type</div>
                    <div class="detail-value" id="modalType"></div>
                    
                    <div class="detail-label">Status</div>
                    <div class="detail-value" id="modalStatus"></div>
                </div>
            </div>

            <div class="mb-3 p-3 bg-light rounded border" id="modalAddressContainer">
                <div class="detail-label"><i class="bi bi-geo-alt-fill"></i> Delivery Address</div>
                <div class="detail-value mb-0" id="modalAddress"></div>
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-4">Items Ordered</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-end" width="100">Price</th>
                            <th class="text-center" width="80">Qty</th>
                            <th class="text-end" width="100">Total</th>
                        </tr>
                    </thead>
                    <tbody id="modalItemsTable"></tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <span id="modalSubtotal" class="fw-bold"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-muted">
                        <span>Delivery Fee:</span>
                        <span id="modalDeliveryFee"></span>
                    </div>
                    <div class="d-flex justify-content-between modal-total-row">
                        <span>Total:</span>
                        <span id="modalTotal" class="text-primary"></span>
                    </div>
                    <div class="mt-2 text-end">
                        <span id="modalPaymentBadge" class="badge bg-secondary"></span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <small class="text-muted">Created: <span id="modalTime"></span></small>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const staffUserId = <?php echo ($_SESSION['user_id'] ?? 0); ?>;
  const viewModalEl = document.getElementById('viewOrderModal');
  const viewModal = new bootstrap.Modal(viewModalEl);

  // --- REAL-TIME UPDATES LOGIC ---
  const tableBody = document.getElementById('ordersTableBody');
  const noOrdersRow = document.getElementById('no-orders-row');

  function fetchQueueUpdates() {
      // Don't poll if user is typing search query to avoid jitter
      if (document.getElementById('orderSearchInput').value.length > 0) return;

      fetch('actions/fetch_queue_updates.php')
          .then(response => response.json())
          .then(data => {
              if (!data.orders) return;
              
              const newOrders = data.orders;
              const existingRows = Array.from(tableBody.querySelectorAll('tr.order-row'));
              const existingIds = existingRows.map(row => row.dataset.orderId);
              const newIds = Object.keys(newOrders);

              // 1. Remove rows that are no longer in the queue
              existingRows.forEach(row => {
                  if (!newIds.includes(row.dataset.orderId)) {
                      row.remove();
                  }
              });

              // 2. Add or Update rows
              newIds.forEach(id => {
                  const orderData = newOrders[id];
                  const existingRow = tableBody.querySelector(`tr[data-order-id="${id}"]`);

                  if (!existingRow) {
                      // ADD NEW ROW
                      tableBody.insertAdjacentHTML('beforeend', orderData.html);
                      // Add fade-in animation
                      const newRow = tableBody.querySelector(`tr[data-order-id="${id}"]`);
                      if(newRow) newRow.classList.add('fade-in-row');
                  } else {
                      // UPDATE EXISTING ROW (only if status changed)
                      if (existingRow.dataset.status !== orderData.status) {
                          existingRow.outerHTML = orderData.html;
                      }
                  }
              });

              // 3. Toggle "No Active Orders" message
              if (newIds.length === 0) {
                  if (!document.getElementById('no-orders-row')) {
                      tableBody.innerHTML = '<tr id="no-orders-row"><td colspan="7" class="text-center text-muted">No active orders in the queue.</td></tr>';
                  }
              } else {
                  if (document.getElementById('no-orders-row')) {
                      document.getElementById('no-orders-row').remove();
                  }
              }
          })
          .catch(err => console.error("Polling error:", err));
  }

  // Poll every 1 seconds
  setInterval(fetchQueueUpdates, 1000);

  // --- CLIENT SIDE SEARCH (Unchanged) ---
  const searchInput = document.getElementById('orderSearchInput');
  const noResultsMsg = document.getElementById('no-search-results');

  if(searchInput) {
      searchInput.addEventListener('keyup', function() {
          const query = this.value.toLowerCase().trim();
          const rows = tableBody.querySelectorAll('.order-row');
          let hasVisible = false;

          rows.forEach(row => {
              const searchableElements = row.querySelectorAll('.searchable-text');
              let textContent = "";
              searchableElements.forEach(el => textContent += el.textContent.toLowerCase() + " ");

              if (textContent.includes(query)) {
                  row.style.display = '';
                  hasVisible = true;
              } else {
                  row.style.display = 'none';
              }
          });

          if(noResultsMsg) {
              if (rows.length === 0) {
                  noResultsMsg.style.display = 'none';
              } else {
                  noResultsMsg.style.display = hasVisible ? 'none' : 'block';
              }
          }
      });
  }

  // --- Helper to rebuild action buttons dynamically ---
  function renderActionButtons(status, orderType, paymentStatus, orderId) {
      let html = '';
      if (status === 'pending') {
           html = `<button class="btn btn-outline-success btn-action" data-action="confirm" data-id="${orderId}">Accept</button>
                   <button class="btn btn-outline-danger btn-action" data-action="cancel" data-id="${orderId}">Reject</button>`;
      } else if (status === 'confirmed') {
           html = `<button class="btn btn-outline-primary btn-action" data-action="prepare" data-id="${orderId}">Prep</button>`;
      } else if (status === 'preparing') {
           html = `<button class="btn btn-outline-success btn-action" data-action="ready" data-id="${orderId}">Ready</button>`;
      } else if (status === 'ready') {
           if (orderType === 'pickup') {
               if (paymentStatus === 'paid') {
                   html = `<button class="btn btn-outline-success btn-action" data-action="complete" data-id="${orderId}">Done</button>`;
               } else {
                   html = `<a href="pos_payment.php?order_id=${orderId}" class="btn btn-outline-success">Pay</a>`;
               }
           }
      }
      return html;
  }

  // --- Handle Action Buttons (Event Delegation) ---
  document.querySelector('.orders-queue-table').addEventListener('click', async function(e) {
      const button = e.target.closest('.btn-action');
      if (!button) return;

      const orderId = button.dataset.id;
      const action = button.dataset.action;
      
      let newStatus = '';
      let rejectionReason = '';

      if (action === 'confirm')        newStatus = 'confirmed';
      if (action === 'prepare')        newStatus = 'preparing';
      if (action === 'ready')          newStatus = 'ready';
      if (action === 'complete')       newStatus = 'completed';
      
      if (action === 'cancel') {
          const { value: reason } = await Swal.fire({
              title: 'Reject Order',
              input: 'textarea',
              inputLabel: 'Reason for rejection',
              inputPlaceholder: 'Type your reason here...',
              inputAttributes: {
                  'aria-label': 'Type your reason here'
              },
              showCancelButton: true,
              confirmButtonText: 'Reject',
              confirmButtonColor: '#dc3545'
          });

          if (reason) {
              newStatus = 'cancelled';
              rejectionReason = reason;
          } else {
              return; 
          }
      }
      
      if (!newStatus) return;

      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

      try {
        const res = await fetch('actions/update_order_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            order_id: orderId,
            new_status: newStatus,
            rejection_reason: rejectionReason, 
            handler_id: staffUserId 
          })
        });
        
        const data = await res.json();
        
        if (data.success) {
          const row = button.closest('tr');
          const orderType = row.getAttribute('data-order-type');
          
          if (
              newStatus === 'cancelled' || 
              newStatus === 'completed' || 
              (orderType === 'delivery' && newStatus === 'ready')
          ) {
              row.style.transition = 'opacity 0.3s';
              row.style.opacity = '0';
              setTimeout(() => row.remove(), 300);
              fetchQueueUpdates(); 
              return; 
          }

          const statusBadge = row.querySelector('.status-badge');
          if (statusBadge) {
            statusBadge.textContent = data.new_status_label;
            statusBadge.className = `status-badge badge ${data.new_status_class}`;
          }

          const paymentStatus = row.getAttribute('data-payment-status');
          const actionGroup = row.querySelector('.action-group');
          if (actionGroup) {
             actionGroup.innerHTML = renderActionButtons(newStatus, orderType, paymentStatus, orderId);
          }
          // Update data-status so poll doesn't overwrite it immediately
          row.setAttribute('data-status', newStatus);

        } else {
          throw new Error(data.message || 'Failed to update status');
        }

      } catch (err) {
        alert('Error: ' + err.message);
        button.disabled = false;
        button.innerHTML = 'Retry';
      }
  });

  // --- View Details Modal Logic ---
  document.querySelectorAll('.orders-queue-table').forEach(table => {
      table.addEventListener('click', async (e) => {
          const btn = e.target.closest('.btn-view-details');
          if(!btn) return;
          
          const orderId = btn.dataset.id;
          viewModal.show();
          document.getElementById('modalLoader').style.display = 'block';
          document.getElementById('modalContent').style.display = 'none';
          
          try {
              const res = await fetch(`actions/get_order_details.php?order_id=${orderId}`);
              const data = await res.json();
              
              if(data.success) {
                  const o = data.order;
                  document.getElementById('modalOrderNumber').textContent = o.order_number;
                  document.getElementById('modalCustomer').textContent = o.customer_name;
                  
                  let contact = o.customer_phone || 'No phone';
                  if(o.customer_email) contact += ` / ${o.customer_email}`;
                  document.getElementById('modalContact').textContent = contact;
                  
                  document.getElementById('modalType').textContent = o.type_label;
                  document.getElementById('modalStatus').innerHTML = `<span class="badge ${o.status_badge_class}">${o.status_label}</span>`;
                  
                  const addrDiv = document.getElementById('modalAddressContainer');
                  if(o.delivery_address) {
                      addrDiv.style.display = 'block';
                      document.getElementById('modalAddress').textContent = o.delivery_address;
                  } else {
                      addrDiv.style.display = 'none';
                  }
                  
                  const tbody = document.getElementById('modalItemsTable');
                  tbody.innerHTML = '';
                  data.items.forEach(item => {
                      const tr = document.createElement('tr');
                      let instructions = '';
                      if(item.special_instructions) {
                          instructions = `<br><small class="text-danger">Note: ${item.special_instructions}</small>`;
                      }
                      tr.innerHTML = `
                        <td>${item.product_name}${instructions}</td>
                        <td class="text-end">${item.unit_price_fmt}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end fw-bold">${item.total_price_fmt}</td>
                      `;
                      tbody.appendChild(tr);
                  });
                  
                  document.getElementById('modalSubtotal').textContent = o.subtotal_formatted;
                  document.getElementById('modalDeliveryFee').textContent = o.delivery_fee_formatted;
                  document.getElementById('modalTotal').textContent = o.total_formatted;
                  document.getElementById('modalPaymentBadge').textContent = o.payment_label;
                  document.getElementById('modalTime').textContent = o.created_at;
                  
                  document.getElementById('modalLoader').style.display = 'none';
                  document.getElementById('modalContent').style.display = 'block';
              } else {
                  alert('Failed to load details: ' + data.message);
                  viewModal.hide();
              }
          } catch(err) {
              console.error(err);
              alert('Error loading details');
              viewModal.hide();
          }
      });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>