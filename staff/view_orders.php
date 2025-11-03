<?php
include __DIR__ . '/includes/header.php';
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
          <button class="btn btn-success">Refresh</button>
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
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#1043</td>
              <td>10:12 AM</td>
              <td>
                Avocado Bowl x1<br>
                Matcha Latte x1
              </td>
              <td>
                Jane Cruz<br>
                <small class="text-muted">jane@example.com</small>
              </td>
              <td>
                <strong>Pickup</strong><br>
                <small class="text-muted">Counter</small>
              </td>
              <td>
                <span class="badge badge-warning">Preparing</span>
              </td>
              <td>
                <?= htmlspecialchars(get_user_name() ?? 'Staff') ?><br>
                <small class="text-muted">(Kitchen)</small>
              </td>
            </tr>
            <tr>
              <td>#1042</td>
              <td>9:58 AM</td>
              <td>
                Keto Wrap x2
              </td>
              <td>
                Leo Santos<br>
                <small class="text-muted">leo@example.com</small>
              </td>
              <td>
                <strong>Delivery</strong><br>
                <small class="text-muted">14 Palm Drive, Phase 2</small>
              </td>
              <td>
                <span class="badge badge-success">Out for delivery</span>
              </td>
              <td>
                Rex P.<br>
                <small class="text-muted">(Rider)</small>
              </td>
            </tr>
            <tr>
              <td>#1041</td>
              <td>9:31 AM</td>
              <td>
                Vegan Pancake Stack x1
              </td>
              <td>
                Ava Lim<br>
                <small class="text-muted">ava@example.com</small>
              </td>
              <td>
                <strong>Pickup</strong><br>
                <small class="text-muted">Counter</small>
              </td>
              <td>
                <span class="badge badge-success">Ready</span>
              </td>
              <td>
                Janelle R.<br>
                <small class="text-muted">(Front Desk)</small>
              </td>
            </tr>
            <tr>
              <td>#1040</td>
              <td>9:05 AM</td>
              <td>
                Protein Smoothie x1<br>
                Avocado Toast x1
              </td>
              <td>
                Chris Dela Cruz<br>
                <small class="text-muted">chrisdc@example.com</small>
              </td>
              <td>
                <strong>Delivery</strong><br>
                <small class="text-muted">9 Horizon Blk 3, Lot 7</small>
              </td>
              <td>
                <span class="badge badge-danger">Driver issue</span>
              </td>
              <td>
                <?= htmlspecialchars(get_user_name() ?? 'Staff') ?><br>
                <small class="text-muted">(Dispatch)</small>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>