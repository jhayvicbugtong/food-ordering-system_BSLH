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
$delivery_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.status,
        o.created_at,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        ocd.customer_phone,
        oa.street AS delivery_street,
        oa.barangay AS delivery_barangay,
        oa.apt_landmark AS delivery_instructions
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN order_addresses oa ON o.order_id = oa.order_id
    WHERE o.order_type = 'delivery' 
      AND o.status IN ('ready', 'out_for_delivery', 'confirmed')
    ORDER BY 
        CASE o.status
            WHEN 'ready' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'out_for_delivery' THEN 3
            ELSE 4
        END,
        o.created_at ASC;
";
$delivery_result = $conn->query($delivery_query);
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <h2 class="mb-4">Active Deliveries</h2>

    <div class="row g-3 mb-4">
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
          <h5>Total Deliveries</h5>
          <div class="value"><?= $stats_total ?></div>
          <div class="hint">Active runs</div>
        </div>
      </div>
    </div>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Delivery Management</h2>
          <p>Staff can mark orders out for delivery or delivered</p>
        </div>
        <div class="right">
          <button class="btn btn-success" onclick="location.reload();">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Dropoff Address</th>
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

                  $status_map = [
                    'confirmed'        => 'badge-primary',
                    'ready'            => 'badge-success',
                    'out_for_delivery' => 'badge-info',
                  ];
                  $status_class = $status_map[$status] ?? 'badge-secondary';

                  $address = trim(($order['delivery_street'] ?? '') . ', ' . ($order['delivery_barangay'] ?? ''));
                  if ($address === ',') {
                      $address = 'N/A';
                  }
                ?>
                <tr data-order-id="<?= $order_id ?>">
                  <td><strong><?= htmlspecialchars($order['order_number'] ?? $order_id) ?></strong></td>
                  <td>
                    <?= htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?><br>
                    <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                  </td>
                  <td>
                    <?= htmlspecialchars($address) ?><br>
                    <small class="text-muted"><?= htmlspecialchars($order['delivery_instructions'] ?? '') ?></small>
                  </td>
                  <td>
                    <span class="badge <?= $status_class ?> status-badge">
                      <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?>
                    </span>
                  </td>
                  <td class="actions-cell">
                    <?php if ($status == 'confirmed' || $status == 'ready'): ?>
                      <!-- Staff chooses: send out or directly mark delivered -->
                      <div class="btn-group btn-group-sm">
                        <button 
                          class="btn btn-outline-primary btn-action" 
                          data-action="out_for_delivery" 
                          data-id="<?= $order_id ?>">
                          Out for Delivery
                        </button>
                        <button 
                          class="btn btn-outline-success btn-action" 
                          data-action="delivered" 
                          data-id="<?= $order_id ?>">
                          Mark Delivered
                        </button>
                      </div>

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
                <td colspan="5" class="text-center text-muted">No active deliveries.</td>
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
          handler_id: handlerId,
          // driver_id removed – staff handle deliveries now
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
