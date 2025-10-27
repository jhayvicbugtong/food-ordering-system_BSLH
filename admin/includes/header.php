<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo '/food-ordering-system_BSLH/assets/css/style.css'; ?>">
</head>
<body>
<header class="site-header">
  <h4 class="mb-0">Admin Dashboard</h4>
  <div>
    <span><?= $_SESSION['name'] ?></span> |
    <a href="<?php echo '/food-ordering-system_BSLH/auth/logout.php'; ?>" class="text-light">Logout</a>
  </div>
</header>
