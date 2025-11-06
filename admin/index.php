<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">Admin Dashboard</h2>

    <!-- HIGH-LEVEL STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Orders Today</h5>
          <div class="value">128</div>
          <div class="hint">Online + Walk-in</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Revenue Today</h5>
          <div class="value">₱12,540.00</div>
          <div class="hint">Cash + GCash</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Pending Orders</h5>
          <div class="value">9</div>
          <div class="hint">Need action now</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
          <h5>Completed Today</h5>
          <div class="value">119</div>
          <div class="hint">Served / Delivered</div>
        </div>
      </div>
    </div>

    <!-- ORDER PIPELINE OVERVIEW -->
    <span id="top"></span>
    <div class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Order Pipeline</h2>
          <p>Live status of all active orders</p>
        </div>
        <!-- <div class="right">
          <a class="btn btn-success" href="food-ordering-system_BSLH/admin/reports.php">
            <i class="bi bi-graph-up"></i> View Reports
          </a>
        </div> -->
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
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
                <small class="text-muted">mark@example.com</small>
              </td>
              <td>
                Delivery<br>
                <small class="text-muted">Brgy. Central</small>
              </td>
              <td>GCash</td>
              <td><span class="badge badge-warning">Preparing</span></td>
              <td>Kitchen • Carlo</td>
            </tr>
            <tr>
              <td>#2040</td>
              <td>
                Ana Cruz<br>
                <small class="text-muted">ana@example.com</small>
              </td>
              <td>
                Pickup<br>
                <small class="text-muted">ASAP</small>
              </td>
              <td>Cash</td>
              <td><span class="badge badge-success">Ready</span></td>
              <td>Front Desk • Janelle</td>
            </tr>
            <tr>
              <td>#2039</td>
              <td>
                Walk-in POS<br>
                <small class="text-muted">no email</small>
              </td>
              <td>POS / Dine-in</td>
              <td>Cash</td>
              <td><span class="badge badge-success">Completed</span></td>
              <td>Cashier • Erika</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TOP SELLERS + PAYMENT BREAKDOWN -->
    <div class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Business Snapshot</h2>
          <p>What's selling and how people pay</p>
        </div>
        <div class="right">
          <a class="btn btn-success" href="reports.php">
            <i class="bi bi-graph-up"></i> View Reports
          </a>
        </div>
      </div>

      <div class="row g-4">
        <!-- Top Selling Items -->
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Top Items (Today)</th>
                  <th>Qty Sold</sup></th>
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
          <div class="table-responsive">
            <table class="table table-hover">
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
    </div>
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
