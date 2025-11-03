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
          <button class="btn btn-success">
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
                  <button class="btn btn-outline-success">Accept</button>
                  <button class="btn btn-outline-danger">Reject</button>
                </div>
              </td>
            </tr>
            
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
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>