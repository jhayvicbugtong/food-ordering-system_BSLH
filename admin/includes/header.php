<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL

// --- NEW: Fetch Store Name from Database ---
$store_name = "Bente Sais Lomi House"; // Default fallback
$settings_result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
if ($settings_result && $settings_result->num_rows > 0) {
    $store_name = $settings_result->fetch_assoc()['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">

  <style>
  /* Status Badges */
  .status-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .status-pending { background-color: #fef3c7; color: #92400e; }
  .status-confirmed { background-color: #dbeafe; color: #1d4ed8; }
  .status-preparing { background-color: #e0f2fe; color: #0369a1; }
  .status-ready { background-color: #dcfce7; color: #15803d; }
  .status-out-for-delivery { background-color: #e0f2fe; color: #0369a1; }
  .status-delivered, .status-completed { background-color: #e5e7eb; color: #111827; }
  .status-cancelled { background-color: #fee2e2; color: #b91c1c; }
  
  /* Header Styles */
  .site-header {
    background: linear-gradient(135deg, #343a40 0%, #2c3034 100%);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 12px 0;
    position: sticky;
    top: 0;
    z-index: 1020;
  }
  
  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 15px;
  }
  
  .brand-container {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
  }
  
  .brand-logo {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
  }
  
  .brand-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  
  .brand-main {
    font-weight: 700;
    font-size: 18px;
    color: #fff;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .brand-sub {
    font-size: 11px;
    color: #adb5bd;
    font-weight: 500;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .user-nav {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
  }
  
  .user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    min-width: 0;
    position: relative;
  }
  
  .user-profile:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-1px);
  }
  
  .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5cfa63, #4ae052);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
  }
  
  .user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  
  .user-name {
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .user-role {
    font-size: 11px;
    color: #adb5bd;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
    flex-shrink: 0;
  }
  
  .nav-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-1px);
    color: #fff;
  }
  
  .nav-btn i {
    font-size: 16px;
  }
  
  /* Dropdown Menu */
  .user-dropdown { position: relative; }
  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.08);
    min-width: 200px;
    padding: 8px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
  }
  .dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    color: #495057;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
  }
  .dropdown-item:hover { background: #f8f9fa; color: #212529; }
  .dropdown-item i { width: 16px; text-align: center; color: #6c757d; }
  .dropdown-divider { height: 1px; background: #e9ecef; margin: 6px 0; }
  
  /* Chevron animation */
  .user-profile .bi-chevron-down { transition: transform 0.3s ease; }
  .user-profile.dropdown-open .bi-chevron-down { transform: rotate(180deg); }

  /* Responsive Design */
  @media (max-width: 768px) {
    .header-content { padding: 0 12px; }
    .brand-container { gap: 8px; flex: 1; min-width: 0; }
    .brand-logo { width: 32px; height: 32px; }
    .brand-main { font-size: 16px; }
    .brand-sub { font-size: 10px; }
    .user-info { display: none; }
    .user-profile { padding: 6px 8px; gap: 8px; }
    .user-avatar { width: 28px; height: 28px; font-size: 12px; }
    .nav-actions { gap: 6px; }
    .nav-btn { width: 36px; height: 36px; }
    .nav-btn i { font-size: 14px; }
    .dropdown-menu { min-width: 180px; right: -10px; }
  }
  @media (max-width: 576px) {
    .site-header { padding: 19px 0; }
    .header-content { padding: 0 10px; }
    .brand-main { font-size: 14px; }
    .brand-sub { display: none; }
    .brand-logo { width: 28px; height: 28px; }
    .user-profile { padding: 4px 6px; }
    .user-avatar { width: 24px; height: 24px; font-size: 11px; }
    .nav-btn { width: 32px; height: 32px; }
    .nav-btn i { font-size: 13px; }
    .nav-actions { gap: 4px; }
    .dropdown-menu { min-width: 160px; right: -15px; }
    .dropdown-item { padding: 8px 10px; font-size: 13px; }
  }
  </style>
</head>
<body>

<header class="site-header">
  <div class="container-fluid">
    <div class="header-content">
      <div class="brand-container">
        <button class="navbar-toggler d-flex d-lg-none me-2" type="button" id="sidebarToggle" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon d-flex align-items-center justify-content-center">
            <i class="bi bi-list" style="font-size: 20px; color: #fff;"></i>
          </span>
        </button>
        
        <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/logo/logo_transparent.png" alt="Logo" class="brand-logo">
        
        <div class="brand-text">
          <div class="brand-main"><?= htmlspecialchars($store_name) ?></div>
          <div class="brand-sub">Operations Portal</div>
        </div>
      </div>

      <div class="user-nav">
        <div class="nav-actions">
          <a href="<?= htmlspecialchars($BASE_URL) ?>/admin/settings.php" class="nav-btn" title="Settings">
            <i class="bi bi-gear"></i>
          </a>
        </div>
        
        <div class="user-dropdown">
          <div class="user-profile" id="userDropdown">
            <div class="user-avatar">
              <?= strtoupper(substr(get_user_name() ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-info">
              <div class="user-name"><?= htmlspecialchars(get_user_name() ?? 'Admin User') ?></div>
              <div class="user-role">Administrator</div>
            </div>
            <i class="bi bi-chevron-down" style="font-size: 12px; color: #adb5bd;"></i>
          </div>
          
          <div class="dropdown-menu" id="profileDropdown">
            <a href="<?= htmlspecialchars($BASE_URL) ?>/admin/profile.php" class="dropdown-item">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php" class="dropdown-item text-danger">
              <i class="bi bi-box-arrow-right"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>