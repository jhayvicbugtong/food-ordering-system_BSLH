<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="content">
    <h2 class="mb-4">Admin Dashboard</h2>

    <!-- HIGH-LEVEL STAT CARDS -->
    <section class="dashboard-row">
      <div class="stat-card">
        <h5>Orders Today</h5>
        <div class="value">128</div>
        <div class="hint">Online + Walk-in</div>
      </div>

      <div class="stat-card">
        <h5>Revenue Today</h5>
        <div class="value">₱12,540.00</div>
        <div class="hint">Cash + GCash</div>
      </div>

      <div class="stat-card">
        <h5>Pending Orders</h5>
        <div class="value">9</div>
        <div class="hint">Need action now</div>
      </div>

      <div class="stat-card">
        <h5>Completed Today</h5>
        <div class="value">119</div>
        <div class="hint">Served / Delivered</div>
      </div>
    </section>

    <!-- ORDER PIPELINE OVERVIEW -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Pipeline</h2>
          <p>Live status of all active orders</p>
        </div>
        <div class="right">
          <button class="btn-primary">View All Orders</button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Handled By</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#2041</td>
              <td>
                Mark Santos<br>
                <small>mark@example.com</small>
              </td>
              <td>
                Delivery<br>
                <small>Brgy. Central</small>
              </td>
              <td>GCash</td>
              <td><span class="badge badge-warning">Preparing</span></td>
              <td>Kitchen • Carlo</td>
            </tr>

            <tr>
              <td>#2040</td>
              <td>
                Ana Cruz<br>
                <small>ana@example.com</small>
              </td>
              <td>
                Pickup<br>
                <small>ASAP</small>
              </td>
              <td>Cash</td>
              <td><span class="badge badge-success">Ready</span></td>
              <td>Front Desk • Janelle</td>
            </tr>

            <tr>
              <td>#2039</td>
              <td>
                Walk-in POS<br>
                <small>no email</small>
              </td>
              <td>POS / Dine-in</td>
              <td>Cash</td>
              <td><span class="badge badge-success">Completed</span></td>
              <td>Cashier • Erika</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- TOP SELLERS + PAYMENT BREAKDOWN -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Business Snapshot</h2>
          <p>What’s selling and how people pay</p>
        </div>
        <div class="right">
          <button class="btn-primary">View Reports</button>
        </div>
      </div>

      <div class="row g-4">
        <!-- Top Selling Items -->
        <div class="col-md-6">
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Top Items (Today)</th>
                  <th>Qty Sold</th>
                  <th>₱ Sales</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Lomi Special Bowl</td>
                  <td>54</td>
                  <td>₱6,480</td>
                </tr>
                <tr>
                  <td>Tokwa't Baboy Combo</td>
                  <td>37</td>
                  <td>₱3,330</td>
                </tr>
                <tr>
                  <td>Extra Chicharon</td>
                  <td>22</td>
                  <td>₱660</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Payment Mix -->
        <div class="col-md-6">
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Payment Method</th>
                  <th>Count</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Cash</td>
                  <td>82</td>
                  <td>₱7,950</td>
                </tr>
                <tr>
                  <td>GCash</td>
                  <td>39</td>
                  <td>₱4,320</td>
                </tr>
                <tr>
                  <td>POS / Walk-in</td>
                  <td>7</td>
                  <td>₱270</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
