<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <i class="bi bi-person-badge"></i>
      </div>
      <span class="brand-text">Staff Panel</span>
    </div>
    <button class="sidebar-close" id="sidebarClose">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="sidebar-content">
    <ul class="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
           href="<?= htmlspecialchars($BASE_URL) ?>/staff/index.php">
          <div class="nav-icon">
            <i class="bi bi-speedometer2"></i>
          </div>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'view_orders.php' ? 'active' : ''; ?>" 
           href="<?= htmlspecialchars($BASE_URL) ?>/staff/view_orders.php">
          <div class="nav-icon">
            <i class="bi bi-cart4"></i>
          </div>
          <span class="nav-text">Orders Queue</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'deliveries.php' ? 'active' : ''; ?>" 
           href="<?= htmlspecialchars($BASE_URL) ?>/staff/deliveries.php">
          <div class="nav-icon">
            <i class="bi bi-truck"></i>
          </div>
          <span class="nav-text">Deliveries</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'active' : ''; ?>" 
           href="<?= htmlspecialchars($BASE_URL) ?>/staff/menu.php">
          <div class="nav-icon">
            <i class="bi bi-book"></i>
          </div>
          <span class="nav-text">Menu</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'order_history.php' ? 'active' : ''; ?>" 
           href="<?= htmlspecialchars($BASE_URL) ?>/staff/order_history.php">
          <div class="nav-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <span class="nav-text">Order History</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-footer">
      <div class="user-quick-profile">
        <div class="user-avatar-sm">
          <?= strtoupper(substr(get_user_name() ?? 'S', 0, 1)) ?>
        </div>
        <div class="user-info-sm">
          <div class="user-name-sm"><?= htmlspecialchars(get_user_name() ?? 'Staff User') ?></div>
          <div class="user-status">Online</div>
        </div>
      </div>
      
      <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php" class="logout-btn">
        <i class="bi bi-box-arrow-right"></i>
        <span>Log out</span>
      </a>
    </div>
  </div>
</nav>

<style>
  /* Enhanced Sidebar Styles - Copied from Admin */
  .sidebar {
    background: linear-gradient(180deg, #212529 0%, #1a1d21 100%);
    width: 220px;
    min-height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    color: #fff;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    z-index: 1030;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  }

  .sidebar-header {
    padding: 6px 16px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .brand-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #5cfa63, #4ae052);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-size: 14px;
    font-weight: 600;
  }

  .brand-text {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .sidebar-close {
    display: none;
    width: 28px;
    height: 28px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: #fff;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 12px;
  }

  .sidebar-close:hover {
    background: rgba(255, 255, 255, 0.15);
  }

  .sidebar-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 16px 0;
  }

  .sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
  }

  .nav-item {
    margin: 2px 12px;
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    color: #adb5bd;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid transparent;
  }

  .nav-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
  }

  .nav-link.active {
    background: linear-gradient(135deg, rgba(92, 250, 99, 0.15), rgba(74, 224, 82, 0.1));
    color: #5cfa63;
    border-color: rgba(92, 250, 99, 0.2);
    box-shadow: 0 2px 8px rgba(92, 250, 99, 0.15);
  }

  .nav-link.active::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 50%;
    background: #5cfa63;
    border-radius: 0 2px 2px 0;
  }

  .nav-icon {
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .nav-link.active .nav-icon {
    transform: scale(1.05);
  }

  .nav-text {
    flex: 1;
    font-weight: 500;
  }

  .sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .user-quick-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    margin-bottom: 10px;
  }

  .user-avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5cfa63, #4ae052);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-weight: 600;
    font-size: 12px;
  }

  .user-info-sm {
    flex: 1;
  }

  .user-name-sm {
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    line-height: 1.2;
  }

  .user-status {
    font-size: 10px;
    color: #5cfa63;
    font-weight: 500;
  }

  .logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    color: #adb5bd;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid transparent;
  }

  .logout-btn:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-color: rgba(220, 53, 69, 0.2);
  }

  /* Mobile Responsive */
  @media (max-width: 992px) {
    .sidebar {
      transform: translateX(-100%);
    }
    
    .sidebar.show {
      transform: translateX(0);
    }
    
    .sidebar-close {
      display: flex;
    }
  }
</style>