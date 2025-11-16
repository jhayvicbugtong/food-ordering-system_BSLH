<?php
include __DIR__ . '/includes/header.php'; // Includes auth and db_connect

// Fetch orders for the table
$orders_query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_type, 
        o.status,
        o.created_at,
        ocd.customer_first_name, 
        ocd.customer_last_name,
        u.first_name as handler_name
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    LEFT JOIN users u ON o.handler_id = u.user_id
    WHERE o.status NOT IN ('completed', 'delivered', 'cancelled')
    ORDER BY 
        CASE o.status
            WHEN 'pending' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'preparing' THEN 3
            WHEN 'ready' THEN 4
            WHEN 'out_for_delivery' THEN 5
            ELSE 6
        END,
        o.created_at ASC
    LIMIT 50;
";
$orders_result = $conn->query($orders_query);
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Orders Queue</h2>
          <p>All active orders right now</p>
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
              <th>Placed</th>
              <th>Items</th>
              <th>Customer</th>
              <th>Pickup / Delivery</th>
              <th>Status</th>
              <th>Handler</th>
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
                  ];
                  $status_class = $status_map[$status] ?? 'badge-secondary';
                  $customer_name = htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']);
                  $order_id = (int)$order['order_id'];
                ?>
                <tr data-order-id="<?= $order_id ?>">
                  <td><strong><?= htmlspecialchars($order['order_number'] ?? $order_id) ?></strong></td>
                  <td><?= date('g:i A', strtotime($order['created_at'])) ?></td>
                  <td>
                    <ul classl="list-unstyled mb-0" style="padding-left: 15px; font-size: 0.9em;">
                      <?php
                        // Fetch items for this order
                        $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ?");
                        $items_stmt->bind_param('i', $order_id);
                        $items_stmt->execute();
                        $items_result = $items_stmt->get_result();
                        while($item = $items_result->fetch_assoc()):
                      ?>
                        <li><?= htmlspecialchars($item['product_name']) ?> x <strong><?= $item['quantity'] ?></strong></li>
                      <?php endwhile; $items_stmt->close(); ?>
                    </ul>
                  </td>
                  <td><?= $customer_name ?></td>
                  <td>
                    <span class="badge <?= $order['order_type'] == 'delivery' ? 'badge-success' : 'badge-primary' ?>">
                      <?= htmlspecialchars(ucfirst($order['order_type'])) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?= $status_class ?> status-badge"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status))) ?></span>
                  </td>
                  <td><?= htmlspecialchars($order['handler_name'] ?? 'N/A') ?></td>
                  <td class="actions-cell">
                    <div class="btn-group btn-group-sm">
                      <?php if ($status == 'pending'): ?>
                        <button class="btn btn-outline-success btn-action" data-action="confirm" data-id="<?= $order_id ?>">Accept</button>
                        <button class="btn btn-outline-danger btn-action" data-action="cancel" data-id="<?= $order_id ?>">Reject</button>
                      <?php elseif ($status == 'confirmed'): ?>
                        <button class="btn btn-outline-primary btn-action" data-action="prepare" data-id="<?= $order_id ?>">Start Prep</button>
                      <?php elseif ($status == 'preparing'): ?>
                        <button class="btn btn-outline-success btn-action" data-action="ready" data-id="<?= $order_id ?>">Mark Ready</button>
                      <?php elseif ($status == 'ready' && $order['order_type'] == 'delivery'): ?>
                        <button class="btn btn-outline-info btn-action" data-action="deliver" data-id="<?= $order_id ?>">Assign Driver</button>
                      <?php elseif ($status == 'ready' && $order['order_type'] == 'pickup'): ?>
                        <button class="btn btn-outline-success btn-action" data-action="complete" data-id="<?= $order_id ?>">Mark Picked Up</button>
                      <?php elseif ($status == 'out_for_delivery'): ?>
                         <button class="btn btn-outline-success btn-action" data-action="deliver" data-id="<?= $order_id ?>">Mark Delivered</button>
                      <?php else: ?>
                         <button class="btn btn-sm btn-outline-secondary" disabled>View</button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted">No active orders in the queue.</td>
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
  
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const button = e.currentTarget;
      const orderId = button.dataset.id;
      const action = button.dataset.action;
      
      let newStatus = '';
      if (action === 'confirm') newStatus = 'confirmed';
      if (action === 'prepare') newStatus = 'preparing';
      if (action === 'ready') newStatus = 'ready';
      if (action === 'deliver' && "<?php echo get_user_role(); ?>" === 'driver') newStatus = 'out_for_delivery';
      if (action === 'deliver' && "<?php echo get_user_role(); ?>" !== 'driver') {
        alert('You must be a Driver to start a delivery. Please assign this in the Deliveries tab.');
        return;
      }
      if (action === 'complete') newStatus = 'completed';
      if (action === 'cancel') newStatus = 'cancelled';
      
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
          // Update UI
          const row = button.closest('tr');
          row.querySelector('.status-badge').textContent = data.new_status_label;
          row.querySelector('.status-badge').className = `badge ${data.new_status_class} status-badge`;
          row.querySelector('.actions-cell').innerHTML = `<span class="text-success fw-bold">Done</span>`;
          // Optionally, reload the page after a delay
          setTimeout(() => location.reload(), 1500);
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