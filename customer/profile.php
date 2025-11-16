<?php
// customer/profile.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to login if not a customer
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $BASE_URL = rtrim(preg_replace('#/customer(/.*)?$#', '', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')), '/');
    if ($BASE_URL === '/') $BASE_URL = '';
    $next = $BASE_URL . '/customer/profile.php';
    header('Location: ' . $BASE_URL . '/customer/auth/login.php?next=' . urlencode($next));
    exit;
}

// --- START OF CORRECTION ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>My Profile | Bente Sais Lomi House</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="../assets/css/customer.css"/>
</head>
<body>

<?php 
// Include the header *inside* the body
include __DIR__ . '/includes/header.php'; 
?>

<main class="container py-4" style="min-height: 60vh;">
  <div class="row">
    <div class="col-md-8 offset-md-2">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
          <h2 class="h4 fw-bold mb-3">My Profile</h2>
          <p class="text-muted">
            This page is a placeholder. You can add a form here to allow users to update their
            name, email, phone number, and password.
          </p>
          </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
<?php // --- END OF CORRECTION --- ?>