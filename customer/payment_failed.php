<?php
// customer/payment_failed.php
require_once __DIR__ . '/../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Payment Failed | Bente Sais Lomi House</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/checkout.css">
</head>
<body class="checkout-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="checkout-progress-bar" id="progressBar">
  <div class="cstep-container">
    <div class="cstep active"><div class="cstep-num">1</div><div class="cstep-label">Order</div></div>
    <div class="cstep"><div class="cstep-num">2</div><div class="cstep-label">Confirmation</div></div>
  </div>
</div>

<section class="confirm-wrapper" id="confirmScreen">
  <div class="confirm-panel" style="border-left: 4px solid #dc3545;">
    <div class="confirm-icon">❌</div>
    <div class="confirm-main">
      <h2 id="confirmTitle">Payment Failed or Cancelled</h2>
      <p id="confirmSubtitle">Your payment was not completed. Your order has not been placed.</p>
    </div>
  </div>

  <div class="summary-grid-block" id="confirmSummary">
    <h3 class="summary-grid-title">What to do next?</h3>
    <div class="light-blue-box">
      <div class="bubble-icon" style="color: #dc3545; border-color: #f8d7da;">!</div>
      <div>
        If you cancelled, you can go back to the menu to modify your cart.
        If your payment failed, please try again. Your cart is still saved.
        <br><br>
        <a href="<?= htmlspecialchars($BASE_URL) ?>/customer/menu.php" class="btn btn-sm btn-secondary" style="font-weight:600;">Back to Menu</a>
        <a href="<?= htmlspecialchars($BASE_URL) ?>/customer/checkout.php" class="btn btn-sm btn-success" style="color:black; font-weight:600;">Try Checkout Again</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>