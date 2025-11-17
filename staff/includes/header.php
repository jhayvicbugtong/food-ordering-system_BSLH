<?php
// Ensure session is started and user is authenticated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include auth functions
require_once __DIR__ . '/../../includes/auth.php';
// --- MODIFIED: Allow 'staff' AND 'driver' roles ---
require_role(['staff', 'driver']);

// Include database connection
require_once __DIR__ . '/../../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Portal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/admin_dashboard.css">
</head>
<body>

<header class="site-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="brand">
          <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/logo/logo.png" alt="Avocado Logo">
          <span>
            Bente Sais Lomi House<br>
            <small style="color:#adb5bd;font-weight:400;font-size:12px;line-height:1;">
              Staff Portal
            </small>
          </span>
        </div>
      </div>
      <div class="user-area">
        <span><?= htmlspecialchars(get_user_name() ?? 'Staff User') ?></span>
        <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php">Logout</a>
      </div>
    </div>
  </div>
</header>