<?php
// Ensure that session_start() is called before any HTML output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the file where the role check function is defined (auth.php)
require_once __DIR__ . '/../../includes/auth.php';

// Verify that the user has the correct role ('staff' in this case)
require_role('staff');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bente Sais Lomi House | Staff Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Dashboard theme -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/dashboard.css">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<header class="site-header">
  <div class="brand">
    <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/logo.png?v=0" alt="Logo">
    <span>
      Bente Sais Lomi House<br>
      <small style="color:#adb5bd;font-weight:400;font-size:12px;line-height:1;">
        Operations Portal
      </small>
    </span>
  </div>

  <div class="user-area">
    <span><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></span>
    <a href="/food-ordering-system_BSLH/auth/logout.php">Logout</a>
  </div>
</header>
