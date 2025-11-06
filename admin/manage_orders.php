<?php
include __DIR__ . '/includes/header.php';
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
          <button class="btn btn-success" id="btn-refresh">
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

            <!-- Row: #1001 (has Accept) -->
            <tr>
              <td>
                <strong>#1001</strong><br>
                <small class="text-muted">10:30 AM</small>
              </td>
              <td>
                Juan Dela Cruz<br>
                <small class="text-muted">0917-123-4567</small>
              </td>
              <td>
                <span class="badge badge-success">Delivery</span><br>
                <small class="text-muted">Brgy. San Roque</small>
              </td>
              <td>₱199.00</td>
              <td>
                <span class="badge badge-warning">Pending</span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">View</button>

                  <!-- UPDATED: add .btn-accept + data-* -->
                  <button
                    class="btn btn-outline-success btn-accept"
                    data-order-id="1001"
                    data-total="199.00"
                    data-customer="Juan Dela Cruz"
                  >
                    Accept
                  </button>

                  <button class="btn btn-outline-danger">Reject</button>
                </div>
              </td>
            </tr>

            <!-- Row: #1002 -->
            <tr>
              <td>
                <strong>#1002</strong><br>
                <small class="text-muted">10:28 AM</small>
              </td>
              <td>
                Maria Clara<br>
                <small class="text-muted">0921-765-4321</small>
              </td>
              <td>
                <span class="badge badge-primary">Pickup</span><br>
                <small class="text-muted">ASAP</small>
              </td>
              <td>₱89.00</td>
              <td>
                <span class="badge badge-success">Preparing</span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">View</button>
                  <button class="btn btn-outline-success">Mark as Ready</button>
                </div>
              </td>
            </tr>

            <!-- Row: #1003 -->
            <tr>
              <td>
                <strong>#1003</strong><br>
                <small class="text-muted">10:25 AM</small>
              </td>
              <td>
                Jose Rizal<br>
                <small class="text-muted">0999-111-2222</small>
              </td>
              <td>
                <span class="badge badge-success">Delivery</span><br>
                <small class="text-muted">Brgy. Poblacion</small>
              </td>
              <td>₱480.00</td>
              <td>
                <span class="badge badge-info">Out for Delivery</span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">View</button>
                  <button class="btn btn-outline-success">Mark Delivered</button>
                </div>
              </td>
            </tr>

            <!-- Row: #1004 -->
            <tr>
              <td>
                <strong>#1004</strong><br>
                <small class="text-muted">10:15 AM</small>
              </td>
              <td>
                Andres Bonifacio<br>
                <small class="text-muted">0918-333-4444</small>
              </td>
              <td>
                <span class="badge badge-primary">Pickup</span><br>
                <small class="text-muted">10:15 AM</small>
              </td>
              <td>₱220.00</td>
              <td>
                <span class="badge badge-secondary">Completed</span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">View</button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== Payment Confirmation Modal ===================== -->
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
                  <option value="BankTransfer">Bank Transfer</option>
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
    <!-- ===================================================================== -->

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('paymentModal');
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
      const total = (b.dataset.total || (row.querySelector('td:nth-child(4)')?.textContent || '')).replace(/[₱,\s,]/g,'');
      const customer = b.dataset.customer || (row.querySelector('td:nth-child(2)')?.childNodes[0].nodeValue.trim());

      orderCodeEl.textContent = '#' + id;
      orderIdInput.value = id;
      totalInput.value = total;
      customerInput.value = customer || '';

      form.dataset.rowId = id;                 // remember row id to update UI later
      row.setAttribute('data-row-id', id);

      paidRadio.checked = true;
      toggleMethod();

      paymentModal.show();
    });
  });

  // Submit accept + payment
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);

    try {
      const res = await fetch('actions/accept_order.php', {
        method: 'POST',
        body: fd
      });
      const json = await res.json();

      if (json.status !== 'ok') {
        throw new Error(json.message || 'Failed to accept order');
      }

      // Update UI for the row to "Preparing"
      const row = document.querySelector(`[data-row-id="${form.dataset.rowId}"]`);
      if (row) {
        const badge = row.querySelector('td:nth-child(5) .badge');
        if (badge) { badge.className = 'badge badge-success'; badge.textContent = 'Preparing'; }
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
    }
  });

  // Optional: reload button
  document.getElementById('btn-refresh')?.addEventListener('click', () => {
    location.reload();
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
