<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="content">
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Orders</h2>
          <p>All active and recent orders</p>
        </div>
        <div class="right">
          <button class="btn-primary">New Manual Order</button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Address</th>
              <th>Items</th>
              <th>Total</th>
              <th>Placed</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>

            <tr>
              <td>#1043</td>
              <td>
                Jane Cruz<br>
                <small>jane@example.com</small>
              </td>
              <td>
                221 Mango St, Brgy. Central<br>
                <small>Pickup: No</small>
              </td>
              <td>
                Avocado Bowl x1<br>
                Matcha Latte x1
              </td>
              <td>$18.90</td>
              <td>10:12 AM</td>
              <td><span class="badge badge-warning">Preparing</span></td>
            </tr>

            <tr>
              <td>#1042</td>
              <td>
                Leo Santos<br>
                <small>leo@example.com</small>
              </td>
              <td>
                14 Palm Drive, Phase 2<br>
                <small>Pickup: Yes</small>
              </td>
              <td>
                Keto Wrap x2
              </td>
              <td>$22.00</td>
              <td>9:58 AM</td>
              <td><span class="badge badge-success">Out for delivery</span></td>
            </tr>

            <tr>
              <td>#1041</td>
              <td>
                Ava Lim<br>
                <small>ava@example.com</small>
              </td>
              <td>
                77 Perea St, Unit 5C<br>
                <small>Pickup: No</small>
              </td>
              <td>
                Vegan Pancake Stack x1
              </td>
              <td>$12.50</td>
              <td>9:31 AM</td>
              <td><span class="badge badge-success">Delivered</span></td>
            </tr>

            <tr>
              <td>#1040</td>
              <td>
                Chris Dela Cruz<br>
                <small>chrisdc@example.com</small>
              </td>
              <td>
                9 Horizon Blk 3, Lot 7<br>
                <small>Pickup: No</small>
              </td>
              <td>
                Protein Smoothie x1<br>
                Avocado Toast x1
              </td>
              <td>$16.00</td>
              <td>9:05 AM</td>
              <td><span class="badge badge-danger">Driver issue</span></td>
            </tr>

          </tbody>
        </table>
      </div>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Delivery summary</h2>
          <p>Quick glance at dispatch status</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Driver</th>
              <th>Current Order</th>
              <th>ETA</th>
              <th>Contact</th>
              <th>Late?</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Rex P.</td>
              <td>#1042 (Leo Santos)</td>
              <td>5 mins</td>
              <td>+63 912 345 6789</td>
              <td><span class="badge badge-success">On time</span></td>
            </tr>
            <tr>
              <td>Janelle R.</td>
              <td>#1040 (Chris Dela Cruz)</td>
              <td>12 mins</td>
              <td>+63 921 222 1111</td>
              <td><span class="badge badge-danger">Late</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
