<?php
include __DIR__ . '/includes/header.php'; // Includes auth and db_connect

// Fetch orders for the table
// UPDATED: Added logic to HIDE delivery orders that are already 'ready' or 'out_for_delivery'
// These will be handled in the deliveries.php page instead.
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
    ORDER BY 
        CASE o.status
            WHEN 'pending' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'preparing' THEN 3
            WHEN 'ready' THEN 4
            ELSE 5
        END,
        o.created_at ASC
    LIMIT 50;
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
    align-items: flex-start;
    gap: 0.75rem;
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
  }

  .orders-queue-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
  }

  .orders-queue-table th,
  .orders-queue-table td {
    font-size: 0.9rem;
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    vertical-align: middle; /* Aligns content vertically in the middle */
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

  @media (max-width: 576px) {
    .content-card {
      padding: 14px 14px;
    }
    /* Hide items column on mobile to save space */
    .hide-on-mobile {
        display: none;
    }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <div class="content-card mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <h2 class="page-title mb-1">Orders Queue</h2>
          <p class="page-subtitle mb-1">All active orders that still need action.</p>
          <p class="meta-text mb-0">
            Sorted by status and time placed. Use the actions on the right to move orders through the pipeline.
          </p>
        </div>
        <div class="text-end">
          <button class="btn btn-success btn-sm" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>
    </div>

    <section class="content-card">
      <div class="content-card-header">
        <div>
          <h2>Active Orders</h2>
          <p>Pending, confirmed, preparing, and pickup-ready orders.</p>
        </div>
        <div class="text-end meta-text">
          <span class="d-block">Max 50 latest active orders</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover orders-queue-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th class="d-none d-md-table-cell">Items</th> <th>Customer</th>
              <th>Total</th> <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
              <?php while($order = $orders_result->fetch_assoc()): ?>
                <?php
                  $status = $order['status'];
                  $status_map = [
                    'pending'          => 'badge-warning',
                    'confirmed'        => 'badge-info',
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
                  $total = (float)($order['total_amount'] ?? 0);
                ?>
                <tr data-order-id="<?= $order_id ?>">
                  <td>
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order_id) ?></strong><br>
                    <?php if ($created_time): ?>
                      <span class="meta-text">Placed: <?= htmlspecialchars($created_time) ?></span>
                    <?php endif; ?>
                  </td>
                  
                  <td class="d-none d-md-table-cell">
                    <ul class="list-unstyled mb-0" style="padding-left: 15px; font-size: 0.85em;">
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

                  <td><?= $customer_name ?></td>
                  <td>
                    <span style="font-weight:600; white-space:nowrap;">
                        ₱<?= number_format($total, 2) ?>
                    </span>
                  </td>
                  <td>
                    <span class="source-pill <?= $source_class ?>">
                      <?= htmlspecialchars($source_label) ?>
                    </span><br>
                    <span class="payment-badge badge <?= $payment_badge_class ?> mt-1 d-inline-block">
                      <?= htmlspecialchars($payment_label) ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <div class="d-flex align-items-center gap-2">
                        
                        <button class="btn btn-sm btn-outline-secondary btn-view-details" data-id="<?= $order_id ?>" title="View Details">
                             <i class="bi bi-eye"></i>
                        </button>

                        <div class="btn-group btn-group-sm">
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
              <tr>
                <td colspan="7" class="text-center text-muted">No active orders in the queue.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
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
  const staffUserId = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
  const viewModalEl = document.getElementById('viewOrderModal');
  const viewModal = new bootstrap.Modal(viewModalEl);

  // --- View Details Logic ---
  document.querySelectorAll('.btn-view-details').forEach(btn => {
      btn.addEventListener('click', async (e) => {
          const orderId = e.currentTarget.dataset.id;
          
          // Show modal & loader
          viewModal.show();
          document.getElementById('modalLoader').style.display = 'block';
          document.getElementById('modalContent').style.display = 'none';
          
          try {
              const res = await fetch(`actions/get_order_details.php?order_id=${orderId}`);
              const data = await res.json();
              
              if(data.success) {
                  const o = data.order;
                  
                  // Header Info
                  document.getElementById('modalOrderNumber').textContent = o.order_number;
                  document.getElementById('modalCustomer').textContent = o.customer_name;
                  
                  // Contact Info
                  let contact = o.customer_phone || 'No phone';
                  if(o.customer_email) contact += ` / ${o.customer_email}`;
                  document.getElementById('modalContact').textContent = contact;
                  
                  // Type & Status
                  document.getElementById('modalType').textContent = o.type_label;
                  document.getElementById('modalStatus').innerHTML = 
                      `<span class="badge ${o.status_badge_class}">${o.status_label}</span>`;
                  
                  // Address
                  const addrDiv = document.getElementById('modalAddressContainer');
                  if(o.delivery_address) {
                      addrDiv.style.display = 'block';
                      document.getElementById('modalAddress').textContent = o.delivery_address;
                  } else {
                      addrDiv.style.display = 'none';
                  }
                  
                  // Items
                  const tbody = document.getElementById('modalItemsTable');
                  tbody.innerHTML = '';
                  data.items.forEach(item => {
                      const tr = document.createElement('tr');
                      let instructions = '';
                      if(item.special_instructions) {
                          instructions = `<br><small class="text-danger">Note: ${item.special_instructions}</small>`;
                      }
                      tr.innerHTML = `
                        <td>
                            ${item.product_name}
                            ${instructions}
                        </td>
                        <td class="text-end">${item.unit_price_fmt}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end fw-bold">${item.total_price_fmt}</td>
                      `;
                      tbody.appendChild(tr);
                  });
                  
                  // Totals
                  document.getElementById('modalSubtotal').textContent = o.subtotal_formatted;
                  document.getElementById('modalDeliveryFee').textContent = o.delivery_fee_formatted;
                  document.getElementById('modalTotal').textContent = o.total_formatted;
                  
                  // Payment Badge
                  document.getElementById('modalPaymentBadge').textContent = o.payment_label;
                  
                  // Timestamp
                  document.getElementById('modalTime').textContent = o.created_at;
                  
                  // Reveal Content
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

  // --- Action Button Logic (Existing) ---
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const button = e.currentTarget;
      const orderId = button.dataset.id;
      const action = button.dataset.action;
      
      let newStatus = '';
      if (action === 'confirm')        newStatus = 'confirmed';
      if (action === 'prepare')        newStatus = 'preparing';
      if (action === 'ready')          newStatus = 'ready';
      if (action === 'start_delivery') newStatus = 'out_for_delivery';
      if (action === 'mark_delivered') newStatus = 'delivered';
      if (action === 'complete')       newStatus = 'completed';
      if (action === 'cancel')         newStatus = 'cancelled';
      
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
            handler_id: staffUserId 
          })
        });
        
        const data = await res.json();
        
        if (data.success) {
          const row = button.closest('tr');
          const statusBadge = row.querySelector('.status-badge');

          if (statusBadge) {
            statusBadge.textContent = data.new_status_label;
            statusBadge.className = `status-badge badge ${data.new_status_class}`;
          }

          // Temporarily show "Done" before reload
          // We can also keep it simple and just reload immediately
          setTimeout(() => location.reload(), 500);
        } else {
          throw new Error(data.message || 'Failed to update status');
        }

      } catch (err) {
        alert('Error: ' + err.message);
        button.disabled = false;
        button.innerHTML = 'Retry';
      }
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>