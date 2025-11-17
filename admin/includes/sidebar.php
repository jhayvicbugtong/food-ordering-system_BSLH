<nav class="sidebar" id="sidebar">
  <div class="section-label">Operations</div>
  <ul>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/admin/index.php"> <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_orders.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/admin/manage_orders.php"> <i class="bi bi-cart4"></i>
        <span>Orders</span>
      </a>
    </li>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_menu.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/admin/manage_menu.php"> <i class="bi bi-list-ul"></i>
        <span>Menu</span>
      </a>
    </li>
    <li>
      <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_staff.php' ? 'active' : ''; ?>" 
         href="<?= htmlspecialchars($BASE_URL) ?>/admin/manage_staff.php"> <i class="bi bi-people"></i>
        <span>Staff</span>
      </a>
    </li>
  </ul>
  <div class="logout">
    <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php"> Log out
    </a>
  </div>
</nav>