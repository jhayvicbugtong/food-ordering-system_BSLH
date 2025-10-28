<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>

  <!-- Bootstrap (still allowed for grids/forms/buttons you already use) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Your old global styles if you still need them -->
  <link rel="stylesheet" href="<?php echo '/food-ordering-system_BSLH/assets/css/style.css'; ?>">

  <!-- New unified dashboard theme -->
  <link rel="stylesheet" href="<?php echo '/food-ordering-system_BSLH/assets/css/dashboard.css'; ?>">

  <!-- Optionally: Bootstrap Icons for the sidebar icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<header class="site-header">
  <div class="brand">
    <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/logo.png?v=0" alt="Avocado Logo">
    <span>
      Bente Sais Lomi House<br>
      <small style="color:#adb5bd;font-weight:400;font-size:12px;line-height:1;">
        Operations Portal
      </small>
    </span>
  </div>

  <div class="user-area">
    <span><?= $_SESSION['name'] ?></span>
    <a href="<?php echo '/food-ordering-system_BSLH/auth/logout.php'; ?>">Logout</a>
  </div>
</header>
