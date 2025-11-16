<?php
include __DIR__ . '/includes/header.php'; // Includes db_connect.php

// Fetch orders for the table.
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
        ocd.customer_phone
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
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
    LIMIT 50;
";
$orders_result = $conn->query($orders_query);

?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Management</h2>
          <p>View and update incoming orders</p>
        </div>
        <div class="right">
          <button class="btn btn-success" id="btn-refresh" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh Orders
          </button>
        </div>
      </div>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Incoming Orders</h2>
          <p>All pending, confirmed, and in-progress orders</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Order Type</th>
              <th>Total (₱)</th>
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
                    'pending' => 'badge-warning',
                    'confirmed' => 'badge-primary',
                    'preparing' => 'badge-info',
                    'ready' => 'badge-success',
                    'out_for_delivery' => 'badge-info',
                    'delivered' => 'badge-secondary',
                    'completed' => 'badge-secondary',
                    'cancelled' => 'badge-danger',
                  ];
                  $status_class = $status_map[$status] ?? 'badge-light';
                  $customer_name = htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']);
                ?>
                <tr data-row-id="<?= $order['order_id'] ?>">
                  <td>
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></strong><br>
                    <small class="text-muted"><?= date('g:i A', strtotime($order['created_at'])) ?></small>
                  </td>
                  <td>
                    <?= $customer_name ?><br>
                    <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                  </td>
                  <td>
                    <?php if ($order['order_type'] == 'delivery'): ?>
                      <span class="badge badge-success">Delivery</span>
                    <?php else: ?>
                      <span class="badge badge-primary">Pickup</span>
                    <?php endif; ?>
                  </td>
                  <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                  <td>
                    <span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary">View</button>

                      <?php if ($status == 'pending'): ?>
                        <button
                          class="btn btn-outline-success btn-accept"
                          data-order-id="<?= $order['order_id'] ?>"
                          data-total="<?= $order['total_amount'] ?>"
                          data-customer="<?= $customer_name ?>"
                        >
                          Accept
                        </button>
                        <button class="btn btn-outline-danger">Reject</button>
                      <?php elseif ($status == 'confirmed' || $status == 'preparing'): ?>
                        <button class="btn btn-outline-success">Mark as Ready</button>
                      <?php elseif ($status == 'ready' && $order['order_type'] == 'delivery'): ?>
                        <button class="btn btn-outline-success">Mark Out for Delivery</button>
                      <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup'): ?>
                        <button class="btn btn-outline-success">Mark Picked Up</button>
                      <?php elseif ($status == 'out_for_delivery'): ?>
                         <button class="btn btn-outline-success">Mark Delivered</button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No orders found.</td>
              </tr>
            <?php endif; ?>

          </tbody>
        </table>
      </div>
    </section>

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
  const modalEl = document.getElementById('paymentModal');
  if (!modalEl) return; // Guard if modal is missing
  
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

  // Open modal with row data
  document.querySelectorAll('.btn-accept').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const b = e.currentTarget;
      const row = b.closest('tr');
      const id = b.dataset.orderId;
      
      // Use dataset values first, fall back to table text
      const total = (b.dataset.total || '0').replace(/[₱,\s,]/g,'');
      const customer = b.dataset.customer || 'N/A';
      const orderNum = row.querySelector('td:first-child strong')?.textContent || ('#' + id);

      orderCodeEl.textContent = orderNum;
      orderIdInput.value = id;
      totalInput.value = parseFloat(total).toFixed(2);
      customerInput.value = customer;

      form.dataset.rowId = id; // remember row id to update UI later
      
      // Find the row again by its data-row-id attribute (which we set in PHP)
      const dataRow = document.querySelector(`tr[data-row-id="${id}"]`);
      if (dataRow) {
         dataRow.dataset.rowId = id; // Ensure it's set
      }

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
        const badge = row.querySelector('td:nth-child(5) .badge');
        if (badge) { 
          badge.className = 'badge badge-info'; // 'preparing' status
          badge.textContent = 'Preparing'; 
        }
        const actions = row.querySelector('.btn-group');
        if (actions) {
          actions.innerHTML = `
            <button class="btn btn-outline-secondary">View</button>
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

  // Optional: reload button
  document.getElementById('btn-refresh')?.addEventListener('click', () => {
    location.reload();
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>