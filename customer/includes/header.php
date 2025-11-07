<?php
// Start session (safe even if already started)
if (session_status() === PHP_SESSION_NONE) session_start();

/*
  Work out the base URL of the project dynamically.
  Examples:
    /food-ordering-system_BSLH
    /myproject
*/
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');         // e.g. /food-ordering-system_BSLH or /food-ordering-system_BSLH/customer
$BASE      = preg_replace('#/customer(/.*)?$#', '', $scriptDir);   // strip /customer... if present
if ($BASE === '/') $BASE = '';                                     // normalize

// Current page name for "active" highlight
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Common paths
$HOME    = $BASE . '/index.php';
$CUSTIDX = $BASE . '/customer/index.php';
$ABOUT   = $BASE . '/customer/about-us.php';
$GALLERY = $BASE . '/customer/gallery.php';
$CONTACT = $BASE . '/customer/contact.php';
$MENU    = $BASE . '/customer/menu.php';

$LOGIN   = $BASE . '/customer/auth/login.php?next=' . urlencode($MENU);
$LOGOUT  = $BASE . '/customer/auth/logout.php?next=' . urlencode($HOME);

// Helpers
function isActive($names, $current) {
  foreach ((array)$names as $n) if ($n === $current) return 'active';
  return '';
}
?>
<header class="site-header">
  <div class="site-header-inner">
    <a class="brand-left" href="<?= htmlspecialchars($CUSTIDX) ?>">
      <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/logo.png?v=0" alt="Logo">
      <div>
        <div class="brand-text-title">Bente Sais Lomi House</div>
        <div class="brand-text-sub">Since 26</div>
      </div>
    </a>

    <nav class="nav-links">

      <a
        href="<?= htmlspecialchars($HOME) ?>"
        class="<?= isActive(['index.php'], $currentPage) ?>"
      >
        Home
      </a>

      <a
        href="<?= htmlspecialchars($ABOUT) ?>"
        class="<?= isActive(['about-us.php'], $currentPage) ?>"
      >
        About us
      </a>

      <a
        href="<?= htmlspecialchars($GALLERY) ?>"
        class="<?= isActive(['gallery.php'], $currentPage) ?>"
      >
        Gallery
      </a>

      <a
        href="<?= htmlspecialchars($CONTACT) ?>"
        class="<?= isActive(['contact.php'], $currentPage) ?>"
      >
        Contact
      </a>

      <!-- Order button always goes through LOGIN first, then redirects to menu -->
      <a
        class="order-btn <?= isActive(['menu.php'], $currentPage) ?>"
        href="<?= htmlspecialchars($LOGIN) ?>">
        <i class="bi bi-bag-fill" style="margin-right:6px;color:#000;"></i>
        Order online
      </a>

      <!-- Auth link: show Login or Logout + greeting -->
      <?php if (!empty($_SESSION['customer_id']) && (($_SESSION['customer_role'] ?? '') === 'customer')): ?>
        <span class="nav-user">Hi, <?= htmlspecialchars($_SESSION['customer_name'] ?? 'Customer') ?></span>
        <a class="nav-link" href="<?= htmlspecialchars($LOGOUT) ?>">Logout</a>
      <?php else: ?>
        <a class="nav-link" href="<?= htmlspecialchars($LOGIN) ?>">Login</a>
      <?php endif; ?>

    </nav>
  </div>
</header>
