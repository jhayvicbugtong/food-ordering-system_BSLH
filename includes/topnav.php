<?php
// copy the top navigation markup (logo/links/user menu) from index_cleaned_full.php
// keep class names exactly so it picks up styles from /assets
$role = $_SESSION['role'] ?? 'guest';
?>
<nav class="topnav">
  <div class="topnav__brand"><a class="brand" href="/index_cleaned_full.php">BSLH</a></div>
  <ul class="topnav__links">
    <?php if ($role === 'admin'): ?>
      <li><a href="/admin/index.php">Dashboard</a></li>
      <li><a href="/admin/orders.php">Orders</a></li>
      <li><a href="/admin/menu.php">Menu</a></li>
      <li><a href="/admin/users.php">Users</a></li>
    <?php elseif ($role === 'staff'): ?>
      <li><a href="/staff/index.php">Dashboard</a></li>
      <li><a href="/staff/orders.php">Orders</a></li>
    <?php else: ?>
      <li><a href="/index_cleaned_full.php">Home</a></li>
    <?php endif; ?>
  </ul>
  <div class="topnav__user">
    <!-- user dropdown / logout -->
    <?php if ($role !== 'guest'): ?>
      <form action="/auth/logout.php" method="post"><button class="btn btn--sm">Logout</button></form>
    <?php endif; ?>
  </div>
</nav>
