<nav class="sidebar">

  <div class="section-label">My Work</div>
  <ul>
    <li>
      <a class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>"
         href="/food-ordering-system_BSLH/staff/index.php">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li>
      <a class="<?= basename($_SERVER['PHP_SELF']) === 'view_orders.php' ? 'active' : ''; ?>"
         href="/food-ordering-system_BSLH/staff/view_orders.php">
        <i class="bi bi-receipt"></i>
        <span>Orders Queue</span>
      </a>
    </li>

    <li>
      <a class="<?= basename($_SERVER['PHP_SELF']) === 'deliveries.php' ? 'active' : ''; ?>"
         href="/food-ordering-system_BSLH/staff/deliveries.php">
        <i class="bi bi-truck"></i>
        <span>Deliveries</span>
      </a>
    </li>

    <li>
      <a class="<?= basename($_SERVER['PHP_SELF']) === 'pos.php' ? 'active' : ''; ?>"
         href="/food-ordering-system_BSLH/staff/pos.php">
        <i class="bi bi-cash-stack"></i>
        <span>POS / Walk-in Sales</span>
      </a>
    </li>
  </ul>

  <div class="logout">
    <a href="/food-ordering-system_BSLH/auth/logout.php">Log out</a>
  </div>
</nav>
