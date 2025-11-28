<?php
// 1. Fetch System Settings from Database
if (!isset($settings)) {
    $settings = [];
    // Check if database connection exists
    if (isset($conn)) {
        $query = $conn->query("SELECT * FROM system_settings");
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
}

// 2. Assign variables (use defaults if database is empty)
$store_name     = !empty($settings['store_name']) ? $settings['store_name'] : 'Bente Sais Lomi House';
$store_phone    = !empty($settings['store_phone']) ? $settings['store_phone'] : '0912-345-6789';
$store_location = !empty($settings['store_location']) ? $settings['store_location'] : 'Brgy. San Roque, Batangas';
?>

<footer class="site-footer">
  <div>
    <strong><?= htmlspecialchars($store_name) ?></strong><br/>
    Comfort food. Local flavor.
  </div>

  <div class="footer-nav">
    <a href="<?= isset($HOME) ? htmlspecialchars($HOME) : 'index.php' ?>">Home</a>
    <a href="<?= isset($ABOUT) ? htmlspecialchars($ABOUT) : 'about-us.php' ?>">About us</a>
    <a href="<?= isset($GALLERY) ? htmlspecialchars($GALLERY) : 'gallery.php' ?>">Gallery</a>
    <a href="<?= isset($CONTACT) ? htmlspecialchars($CONTACT) : 'contact.php' ?>">Contact</a>
    <a href="<?= isset($ORDER_BTN_LINK) ? htmlspecialchars($ORDER_BTN_LINK) : 'menu.php' ?>">Order online</a>
  </div>

  <div class="footer-contact">
    <?php if($store_phone): ?>
        <div><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($store_phone) ?></div>
    <?php endif; ?>
    
    <?php if($store_location): ?>
        <div><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($store_location) ?></div>
    <?php endif; ?>
  </div>

  <div style="margin-top:16px; font-size:12px; color:#adb5bd;">
    &copy; <?php echo date('Y'); ?> <?= htmlspecialchars($store_name) ?>. All rights reserved.
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>