<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');
// 
// ADD THIS LINE
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css"> </head>
</head>
<body>

<header class="site-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      
      <div class="d-flex align-items-center">
        
        <button class="navbar-toggler d-flex d-lg-none me-2" type="button" id="sidebarToggle" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon d-flex align-items-center justify-content-center">
            <i class="bi bi-list" style="font-size: 28px; color: #fff;"></i>
          </span>
        </button>
        <div class="brand">
          <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/logo/logo.png" alt="Avocado Logo">
          <span>
            Bente Sais Lomi House<br>
            <small style="color:#adb5bd;font-weight:400;font-size:12px;line-height:1;">
              Operations Portal
            </small>
          </span>
        </div>
      </div>

      <div class="user-area">
        <span><?= htmlspecialchars(get_user_name() ?? 'Admin User') ?></span>
        <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php">Logout</a>
      </div>
    </div>
  </div>
</header>