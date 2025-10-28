<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="content">
    <h2 class="mb-4">Staff Dashboard</h2>
    <p class="mb-4 text-muted" style="font-size:14px;">
      Logged in as <strong><?= $_SESSION['name'] ?></strong>. Below are tasks for your shift.
    </p>

    <!-- SHIFT SNAPSHOT -->
    <section class="dashboard-row">
      <div class="stat-card">
        <h5>Orders To Prepare</h5>
        <div class="value">6</div>
        <div class="hint">Kitchen queue</div>
      </div>

      <div class="stat-card">
        <h5>For Pickup / Ready</h5>
        <div class="value">2</div>
        <div class="hint">Waiting at counter</div>
      </div>

      <div class="stat-card">
        <h5>Out for Delivery</h5>
        <div class="value">2</div>
        <div class="hint">1 delayed</div>
      </div>

      <div class="stat-card">
        <h5>POS (Walk-in) Sales Today</h5>
        <div class="value">₱2,130</div>
        <div class="hint">Cash / GCash</div>
      </div>
    </section>

    <!-- ACTIVE ORDERS (ONLINE + POS) -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Active Orders</h2>
          <p>Online and walk-in (POS)</p>
        </div>
        <div class="right">
          <button class="btn-primary">Open POS</button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Source</th>
              <th>Items</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Next Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#3321</td>
              <td>
                Online Delivery<br>
                <small>Brgy. Central</small>
              </td>
              <td>
                Lomi Special x2<br>
                Tokwa't Baboy x1
              </td>
              <td>GCash</td>
              <td><span class="badge badge-warning">Preparing</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Mark Ready</button>
              </td>
            </tr>

            <tr>
              <td>#3320</td>
              <td>
                Pickup ASAP<br>
                <small>Ana Cruz</small>
              </td>
              <td>
                Lomi Special x1
              </td>
              <td>Cash</td>
              <td><span class="badge badge-success">Ready</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Customer Picked Up</button>
              </td>
            </tr>

            <tr>
              <td>#POS-118</td>
              <td>
                Walk-in POS<br>
                <small>Dine-in</small>
              </td>
              <td>
                Lomi + Softdrinks
              </td>
              <td>Cash</td>
              <td><span class="badge badge-success">Completed</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary" disabled>Done</button>
              </td>
            </tr>

            <tr>
              <td>#3319</td>
              <td>
                Delivery<br>
                <small>Phase 2, Palm Drive</small>
              </td>
              <td>
                Lomi Special x1<br>
                Chicharon x2
              </td>
              <td>GCash</td>
              <td><span class="badge badge-danger">Driver issue</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Call Driver</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- PICKUP / COUNTER QUEUE -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Pickup Counter Queue</h2>
          <p>Customers waiting in store</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Name</th>
              <th>Items</th>
              <th>Time Ready</th>
              <th>Status</th>
              <th>Complete</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#3320</td>
              <td>Ana Cruz</td>
              <td>
                Lomi Special x1
              </td>
              <td>11:42 AM</td>
              <td><span class="badge badge-success">Ready</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Picked Up ✔</button>
              </td>
            </tr>

            <tr>
              <td>#3317</td>
              <td>Walk-in POS</td>
              <td>
                Tokwa't Baboy x1
              </td>
              <td>11:20 AM</td>
              <td><span class="badge badge-success">Ready</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Served ✔</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
