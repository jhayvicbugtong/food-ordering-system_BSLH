<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="content">

    <h2 class="mb-4">Active Deliveries 🚚</h2>

    <!-- DRIVER / RUN SUMMARY -->
    <section class="dashboard-row">
      <div class="stat-card">
        <h5>Out For Delivery</h5>
        <div class="value">2</div>
        <div class="hint">on the road now</div>
      </div>

      <div class="stat-card">
        <h5>Late Drops</h5>
        <div class="value">1</div>
        <div class="hint" style="color:#dc3545;">needs attention</div>
      </div>

      <div class="stat-card">
        <h5>Your Assigned Runs</h5>
        <div class="value">1</div>
        <div class="hint">under your name</div>
      </div>

      <div class="stat-card">
        <h5>Next ETA</h5>
        <div class="value">5 min</div>
        <div class="hint">Palm Drive, Phase 2</div>
      </div>
    </section>

    <!-- DELIVERY TABLE -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Delivery Status</h2>
          <p>Orders currently out for delivery</p>
        </div>
        <div class="right">
          <button class="btn-primary">Refresh</button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Dropoff Address</th>
              <th>Driver</th>
              <th>ETA</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td>#1042</td>
              <td>
                Leo Santos<br>
                <small>leo@example.com</small>
              </td>
              <td>
                14 Palm Drive, Phase 2<br>
                <small>Gate code: 1029</small>
              </td>
              <td>
                Rex P.<br>
                <small>+63 912 345 6789</small>
              </td>
              <td>5 min</td>
              <td><span class="badge badge-success">On time</span></td>
            </tr>

            <tr>
              <td>#1040</td>
              <td>
                Chris Dela Cruz<br>
                <small>chrisdc@example.com</small>
              </td>
              <td>
                9 Horizon Blk 3, Lot 7<br>
                <small>Call on arrival</small>
              </td>
              <td>
                <?= $_SESSION['name'] ?><br>
                <small>You</small>
              </td>
              <td>12 min late</td>
              <td><span class="badge badge-danger">Delayed</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
