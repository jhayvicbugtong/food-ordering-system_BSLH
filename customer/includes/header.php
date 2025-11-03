<?php
// figure out which page is being viewed
$currentPage = basename($_SERVER['PHP_SELF']); 
// ex: "about-us.php", "menu.php", etc.
?>

<header class="site-header">
  <div class="site-header-inner">
    <a class="brand-left" href="/food-ordering-system_BSLH/customer/index.php">
      <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/logo.png?v=0" alt="Logo">
      <div>
        <div class="brand-text-title">Bente Sais Lomi House</div>
        <div class="brand-text-sub">Since 26</div>
      </div>
    </a>

    <nav class="nav-links">

      <a
        href="/food-ordering-system_BSLH/index.php"
        class="<?php echo ($currentPage === 'index.php' ? 'active' : ''); ?>"
      >
        Home
      </a>

      <a
        href="/food-ordering-system_BSLH/customer/about-us.php"
        class="<?php echo ($currentPage === 'about-us.php' ? 'active' : ''); ?>"
      >
        About us
      </a>

      <a
        href="/food-ordering-system_BSLH/customer/gallery.php"
        class="<?php echo ($currentPage === 'gallery.php' ? 'active' : ''); ?>"
      >
        Gallery
      </a>

      <a
        href="/food-ordering-system_BSLH/customer/contact.php"
        class="<?php echo ($currentPage === 'contact.php' ? 'active' : ''); ?>"
      >
        Contact
      </a>

      <a
        class="order-btn <?php echo ($currentPage === 'menu.php' ? 'active' : ''); ?>"
        href="/food-ordering-system_BSLH/customer/menu.php"
      >
        <i class="bi bi-bag-fill" style="margin-right:6px;color:#000;"></i>
        Order online
      </a>

    </nav>
  </div>
</header>
