<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="content">
    <h2 class="mb-4">Welcome, <?= $_SESSION['name'] ?> 👋</h2>
    <div class="row text-center">
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Total Orders</h5>
            <p class="display-6 text-primary">128</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Total Menu Items</h5>
            <p class="display-6 text-success">42</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Active Staff</h5>
            <p class="display-6 text-warning">6</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
