<?php
include __DIR__ . '/includes/header.php'; // Includes auth and db_connect

// --- Fetch Delivery Stats ---
$stats_ready = $conn->query("
    SELECT COUNT(order_id) as total 
    FROM orders 
    WHERE status = 'ready' AND order_type = 'delivery'
")->fetch_assoc()['total'] ?? 0;

$stats_out = $conn->query("
    SELECT COUNT(order_id) as total 
    FROM orders 
    WHERE status = 'out_for_delivery' AND order_type = 'delivery'
")->fetch_assoc()['total'] ?? 0;

$stats_total = $stats_ready + $stats_out;

// --- Fetch delivery orders ---
// UPDATED: Sort strictly by created_at ASC (oldest first)
$delivery_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.status,
        o.created_at,
        o.total_amount,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        ocd.customer_phone,
        
        -- Address fields
        oa.street,
        oa.barangay,
        oa.city,
        oa.province,
        oa.floor_number,
        oa.apt_landmark,
        
        -- Payment fields
        opd.payment_method,
        opd.payment_status
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_addresses oa ON o.order_id = oa.order_id
    LEFT JOIN order_payment_details opd ON o.order_id = opd.order_id
    WHERE o.order_type = 'delivery' 
      AND o.status IN ('ready', 'out_for_delivery', 'confirmed')
    ORDER BY o.created_at ASC;
";
$delivery_result = $conn->query($delivery_query);
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

  /* Stat cards */
  .stat-card {
    padding: 14px 16px;
    border-radius: 16px;
    background: linear-gradient(135deg, #eef2ff, #f9fafb);
    border: 1px solid rgba(129, 140, 248, 0.25);
    box-shadow: 0 12px 30px rgba(31, 41, 55, 0.06);
    transition: transform 0.12s ease-out, box-shadow 0.12s ease-out;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  }
  .stat-card h5 {
    margin: 0;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
  }
  .stat-card .value {
    font-size: 1.3rem;
    font-weight: 600;
    margin-top: 4px;
  }
  .stat-card .hint {
    font-size: 0.8rem;
    color: #9ca3af;
    margin-top: 2px;
  }

  /* Table */
  .delivery-table {
    margin-bottom: 0;
    /* Fix responsiveness: Force min width to trigger scroll on mobile */
    min-width: 900px;
  }

  .delivery-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
  }

  .delivery-table th,
  .delivery-table td {
    font-size: 0.9rem;
    white-space: nowrap !important; /* Prevent wrapping */
    vertical-align: top;
    padding: 12px 10px;
  }

  .delivery-table td small {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Pills */
  .status-badge, .payment-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .status-badge.badge-primary, .status-badge.bg-primary {
    background: #dbeafe; color: #1d4ed8; border: 1px solid rgba(37, 99, 235, 0.2);
  }
  .status-badge.badge-success, .status-badge.bg-success {
    background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, 0.15);
  }
  .status-badge.badge-info, .status-badge.bg-info {
    background: #e0f2fe; color: #0369a1; border: 1px solid rgba(3, 105, 161, 0.18);
  }
  .status-badge.badge-secondary, .status-badge.bg-secondary {
    background: #e5e7eb; color: #374151; border: 1px solid rgba(55, 65, 81, 0.12);
  }

  /* Payment Badges */
  .payment-badge.badge-success { background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, 0.15); }
  .payment-badge.badge-warning { background: #fef3c7; color: #92400e; border: 1px solid rgba(146, 64, 14, 0.12); }
  .payment-badge.badge-secondary { background: #e5e7eb; color: #374151; border: 1px solid rgba(55, 65, 81, 0.12); }

  .address-main {
    font-size: 0.9rem;
  }

  .address-meta {
    font-size: 0.8rem;
    color: #6b7280;
    display: block;
    margin-top: 2px;
  }

  .actions-cell .btn {
    white-space: nowrap;
  }

  @media (max-width: 576px) {
    .content-card {
      padding: 14px 10px;
    }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <div class="content-card mb-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
          <h2 class="page-title mb-1">Active Deliveries</h2>
          <p class="page-subtitle mb-1">Live view of all delivery orders still in progress.</p>
          <p class="meta-text mb-0">
            Sorted by time placed (Oldest first). Use this to coordinate riders and track progress.
          </p>
        </div>
        <div class="text-end">
          <button class="btn btn-success btn-sm" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Ready for Delivery</h5>
            <div class="value"><?= $stats_ready ?></div>
            <div class="hint">Packed, still in store</div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Out For Delivery</h5>
            <div class="value"><?= $stats_out ?></div>
            <div class="hint">Staff on the way</div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <h5>Total Active Runs</h5>
            <div class="value"><?= $stats_total ?></div>
            <div class="hint">Ready + out on road</div>
          </div>
        </div>
      </div>
    </div>

    <section class="content-card">
      <div class="content-card-header">
        <div>
          <h2>Delivery Management</h2>
          <p>Move orders from ready ➝ out for delivery ➝ delivered.</p>
        </div>
        <div class="text-end meta-text">
          <span class="d-block">Includes confirmed, ready, and out-for-delivery orders.</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover delivery-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Dropoff Address</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($delivery_result && $delivery_result->num_rows > 0): ?>
              <?php while($order = $delivery_result->fetch_assoc()): ?>
                <?php
                  $status   = $order['status'];
                  $order_id = (int)$order['order_id'];
                  $total    = (float)($order['total_amount'] ?? 0);

                  // Status Badge
                  $status_map = [
                    'confirmed'        => 'badge-primary',
                    'ready'            => 'badge-success',
                    'out_for_delivery' => 'badge-info',
                  ];
                  $status_class = $status_map[$status] ?? 'badge-secondary';

                  // Customer Name
                  $customer_name = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
                  if ($customer_name === '') {
                    $customer_name = 'Delivery Customer';
                  }

                  // --- Address Logic (Street + Barangay ONLY) ---
                  $addr_parts = [];
                  if (!empty($order['street']))    $addr_parts[] = $order['street'];
                  if (!empty($order['barangay']))  $addr_parts[] = 'Brgy. ' . $order['barangay'];
                  // REMOVED: City and Province (Nasugbu, Batangas)
                  
                  $full_address = implode(', ', $addr_parts);
                  if ($full_address === '') $full_address = 'N/A';

                  // Extra instructions (Floor, landmark)
                  $extras = [];
                  if (!empty($order['floor_number'])) $extras[] = 'Floor: ' . $order['floor_number'];
                  if (!empty($order['apt_landmark'])) $extras[] = 'Landmark: ' . $order['apt_landmark'];
                  $extra_text = implode('; ', $extras);


                  // --- Payment Logic ---
                  $payment_method = $order['payment_method'] ?? null;
                  $payment_status = $order['payment_status'] ?? null;

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
                      $payment_badge_class = ($payment_status === 'paid') ? 'badge-success' : 'badge-warning';
                  }
                ?>
                <tr data-order-id="<?= $order_id ?>">
                  <td>
                    <strong><?= htmlspecialchars($order['order_number'] ?? $order_id) ?></strong><br>
                    <?php if (!empty($order['created_at'])): ?>
                      <span class="meta-text">
                        Placed: <?= htmlspecialchars(date('g:i A', strtotime($order['created_at']))) ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= htmlspecialchars($customer_name) ?><br>
                    <small class="text-muted">
                      <?= htmlspecialchars($order['customer_phone'] ?: 'No phone') ?>
                    </small>
                  </td>
                  <td>
                    <span class="address-main"><?= htmlspecialchars($full_address) ?></span>
                    <?php if ($extra_text): ?>
                      <span class="address-meta">
                        <i class="bi bi-info-circle"></i> <?= htmlspecialchars($extra_text) ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span style="font-weight:600; white-space:nowrap;">
                        ₱<?= number_format($total, 2) ?>
                    </span>
                  </td>
                  <td>
                    <span class="payment-badge badge <?= $payment_badge_class ?>">
                      <?= htmlspecialchars($payment_label) ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-badge badge <?= $status_class ?>">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <?php if ($status == 'confirmed' || $status == 'ready'): ?>
                      <button 
                        class="btn btn-sm btn-outline-primary btn-action" 
                        data-action="out_for_delivery" 
                        data-id="<?= $order_id ?>">
                        Out for Delivery
                      </button>

                    <?php elseif ($status == 'out_for_delivery'): ?>
                      <button 
                        class="btn btn-sm btn-outline-success btn-action" 
                        data-action="delivered" 
                        data-id="<?= $order_id ?>">
                        Mark Delivered
                      </button>

                    <?php else: ?>
                      <span class="text-muted">No actions</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
               <tr>
                <td colspan="7" class="text-center text-muted">No active deliveries.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const staffUserId = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;

  // Handle all delivery actions (out_for_delivery / delivered)
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const button = e.currentTarget;
      const orderId = button.dataset.id;
      const action  = button.dataset.action;

      let newStatus = '';
      if (action === 'out_for_delivery') newStatus = 'out_for_delivery';
      if (action === 'delivered')       newStatus = 'delivered';

      if (!newStatus) return;

      updateStatus(button, orderId, newStatus, staffUserId);
    });
  });

  async function updateStatus(button, orderId, newStatus, handlerId) {
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    try {
      const res = await fetch('actions/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderId,
          new_status: newStatus,
          handler_id: handlerId
        })
      });

      const data = await res.json();

      if (data.success) {
        location.reload(); // simple refresh to show new state
      } else {
        throw new Error(data.message || 'Failed to update status');
      }
    } catch (err) {
      alert('Error: ' + err.message);
      button.disabled = false;
      button.innerHTML = 'Retry';
    }
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>