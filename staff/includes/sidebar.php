<nav class="sidebar" id="sidebar">
  <div class="section-label">My Work</div>
  <ul>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/staff/index.php"> <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'view_orders.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/staff/view_orders.php"> <i class="bi bi-cart4"></i>
        <span>Orders Queue</span>
      </a>
    </li>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'deliveries.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/staff/deliveries.php"> <i class="bi bi-truck"></i>
        <span>Deliveries</span>
      </a>
    </li>

    <li class="<?= basename($_SERVER['PHP_SELF']) === 'order_history.php' ? 'active' : '' ?>">
  <a href="order_history.php">
    <i class="bi bi-clock-history"></i> Order History
  </a>
</li>

  </ul>
  <div class="logout">
    <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php"> Log out
    </a>
  </div>
</nav>