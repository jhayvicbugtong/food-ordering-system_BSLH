<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="content">
    <h2 class="mb-4">Welcome, <?= $_SESSION['name'] ?> 👋</h2>
    <p class="lead">Here are your assigned orders for today:</p>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>#001</td>
          <td>John Doe</td>
          <td><span class="badge bg-warning text-dark">Preparing</span></td>
          <td>2025-10-27</td>
        </tr>
        <tr>
          <td>#002</td>
          <td>Mary Cruz</td>
          <td><span class="badge bg-success">Delivered</span></td>
          <td>2025-10-27</td>
        </tr>
      </tbody>
    </table>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
