<?php
// customer/includes/header.php
// Start session (safe even if already started)
if (session_status() === PHP_SESSION_NONE) session_start();

/*
  Work out the base URL of the project dynamically.
*/
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$BASE      = preg_replace('#/customer(/.*)?$#', '', $scriptDir);
if ($BASE === '/') $BASE = '';

// Current page name for "active" highlight
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Common paths
$HOME    = $BASE . '/index.php';
$CUSTIDX = $BASE . '/customer/index.php';
$ABOUT   = $BASE . '/customer/about-us.php';
$GALLERY = $BASE . '/customer/gallery.php';
$CONTACT = $BASE . '/customer/contact.php';
$MENU    = $BASE . '/customer/menu.php';

// Auth paths
$LOGIN_URL   = $BASE . '/customer/auth/login.php?next=' . urlencode($MENU);
// --- NEW: Added SIGNUP_URL ---
$SIGNUP_URL  = $BASE . '/customer/auth/register.php?next=' . urlencode($MENU);
$LOGOUT  = $BASE . '/customer/auth/logout.php?next=' . urlencode($HOME);
$PROFILE = $BASE . '/customer/profile.php';
$MY_ORDERS = $BASE . '/customer/orders.php';

// --- FIX: Determine the correct link for the "Order" button ---
$is_logged_in_customer = (!empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'customer'));

if ($is_logged_in_customer) {
    $ORDER_BTN_LINK = $MENU; // Already logged in, just go to menu
} else {
    $ORDER_BTN_LINK = $LOGIN_URL; // Not logged in, go to login first
}
// --- END FIX ---

// Helpers
function isActive($names, $current) {
  foreach ((array)$names as $n) if ($n === $current) return 'active';
  return '';
}
?>
<header class="site-header">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  
  <div class="site-header-inner">
    <a class="brand-left" href="<?= htmlspecialchars($HOME) ?>">
      <div class="logo-container">
        <img src="/food-ordering-system_BSLH/uploads/logo/logo.png" alt="Logo">
        <div class="logo-glow"></div>
      </div>
      <div class="brand-text">
        <div class="brand-text-title">Bente Sais Lomi House</div>
        <div class="brand-text-sub">Since 26</div>
      </div>
    </a>

    <nav class="nav-links">
      <a
        href="<?= htmlspecialchars($HOME) ?>"
        class="nav-link <?= isActive(['index.php'], $currentPage) ?>"
      >
        <i class="bi bi-house-door"></i>
        <span>Home</span>
      </a>

      <a
        href="<?= htmlspecialchars($ABOUT) ?>"
        class="nav-link <?= isActive(['about-us.php'], $currentPage) ?>"
      >
        <i class="bi bi-info-circle"></i>
        <span>About us</span>
      </a>

      <a
        href="<?= htmlspecialchars($GALLERY) ?>"
        class="nav-link <?= isActive(['gallery.php'], $currentPage) ?>"
      >
        <i class="bi bi-images"></i>
        <span>Gallery</span>
      </a>

      <a
        href="<?= htmlspecialchars($CONTACT) ?>"
        class="nav-link <?= isActive(['contact.php'], $currentPage) ?>"
      >
        <i class="bi bi-telephone"></i>
        <span>Contact</span>
      </a>

      <a
        class="order-btn <?= isActive(['menu.php'], $currentPage) ?>"
        href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
        <i class="bi bi-bag-fill"></i>
        <span>Order online</span>
      </a>
      
      <?php if ($is_logged_in_customer): ?>
        <div class="nav-item dropdown user-dropdown">
          <a class="nav-link dropdown-toggle user-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i>
            <span>Hi, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="<?= htmlspecialchars($PROFILE) ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="<?= htmlspecialchars($MY_ORDERS) ?>"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= htmlspecialchars($LOGOUT) ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      <?php else: ?>
        <div class="nav-item dropdown user-dropdown">
          <a class="nav-link dropdown-toggle user-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i>
            <span>Account</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
            <li>
              <a class="dropdown-item" href="<?= htmlspecialchars($LOGIN_URL) ?>">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= htmlspecialchars($SIGNUP_URL) ?>">
                <i class="bi bi-person-plus me-2"></i>Register
              </a>
            </li>
          </ul>
        </div>
      <?php endif; ?>
    </nav>
    
    <!-- Mobile menu toggle -->
    <button class="mobile-menu-toggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<style>
/* Enhanced Header Styles */
.site-header {
  background-color: #343a40;
  color: #fff;
  padding: 12px 20px;
  position: sticky;
  top: 0;
  z-index: 1030;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.site-header.scrolled {
  padding: 10px 20px;
  background-color: rgba(26, 26, 26, 0.95);
  backdrop-filter: blur(10px);
}

.site-header-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  position: relative;
}

/* Enhanced Brand Logo */
.brand-left {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  transition: transform 0.2s ease;
  z-index: 1031;
}

.brand-left:hover {
  transform: translateY(-1px);
}

.logo-container {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-left img {
  height: 42px;
  width: 42px;
  border-radius: 10px;
  background: radial-gradient(circle at 30% 30%, var(--accent) 0%, #1c1f1f 70%);
  box-shadow: 0 8px 20px rgba(92,250,99,0.5);
  object-fit: cover;
  position: relative;
  z-index: 2;
  transition: all 0.3s ease;
}

.logo-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: radial-gradient(circle, rgba(92,250,99,0.3) 0%, rgba(92,250,99,0) 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.brand-left:hover .logo-glow {
  opacity: 1;
}

.brand-left:hover img {
  box-shadow: 0 10px 25px rgba(92,250,99,0.6);
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-text-title {
  font-size: 16px;
  line-height: 1.2;
  color: #fff;
  font-weight: 700;
  letter-spacing: -0.3px;
}

.brand-text-sub {
  font-size: 11px;
  line-height: 1.2;
  color: var(--accent);
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Enhanced Navigation */
.nav-links {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin: 0;
  padding: 0;
  list-style: none;
}

.nav-link {
  color: #e9ecef;
  font-size: 14px;
  text-decoration: none;
  font-weight: 500;
  line-height: 1.2;
  padding: 8px 12px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  position: relative;
}

.nav-link:hover {
  color: #fff;
  background-color: rgba(255, 255, 255, 0.05);
}

.nav-link.active {
  color: #fff;
  background-color: rgba(92, 250, 99, 0.1);
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 12px;
  right: 12px;
  height: 2px;
  background-color: var(--accent);
  border-radius: 1px;
}

.nav-link i {
  font-size: 16px;
}

/* Enhanced Order Button */
.order-btn {
  background-color: var(--accent);
  color: #000;
  font-weight: 600;
  font-size: 14px;
  border: 0;
  border-radius: 8px;
  padding: 10px 16px;
  text-decoration: none;
  line-height: 1.2;
  box-shadow: 0 10px 20px rgba(92,250,99,0.4);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.order-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.order-btn:hover::before {
  left: 100%;
}

.order-btn:hover {
  filter: brightness(1.05);
  box-shadow: 0 12px 24px rgba(92,250,99,0.5);
  transform: translateY(-1px);
}

.order-btn:active {
  transform: translateY(0);
}

.order-btn.active {
  box-shadow: 0 0 0 2px var(--accent), 0 12px 24px rgba(92,250,99,0.5);
}

.order-btn i {
  font-size: 16px;
}

/* Enhanced User Dropdown */
.user-dropdown {
  position: relative;
}

.user-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px !important;
  border-radius: 6px;
  transition: all 0.2s ease;
  text-decoration: none;
}

.user-toggle:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.user-toggle i {
  font-size: 18px;
}

.user-dropdown .dropdown-menu {
  margin-top: 8px !important;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border-radius: 8px;
  padding: 8px 0;
  min-width: 200px;
}

.user-dropdown .dropdown-item {
  font-size: 14px;
  padding: 8px 16px;
  color: #212529;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.user-dropdown .dropdown-item:hover,
.user-dropdown .dropdown-item:focus {
  color: #1e2125;
  background-color: #f8f9fa;
}

.user-dropdown .dropdown-divider {
  margin: 8px 0;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 30px;
  height: 30px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 1031;
}

.mobile-menu-toggle span {
  display: block;
  width: 20px;
  height: 2px;
  background-color: #fff;
  margin: 2px 0;
  transition: all 0.3s ease;
  transform-origin: center;
}

.mobile-menu-toggle.active span:nth-child(1) {
  transform: translateY(6px) rotate(45deg);
}

.mobile-menu-toggle.active span:nth-child(2) {
  opacity: 0;
}

.mobile-menu-toggle.active span:nth-child(3) {
  transform: translateY(-6px) rotate(-45deg);
}

/* Responsive Design */
@media (max-width: 992px) {
  .nav-links {
    gap: 4px;
  }
  
  .nav-link span,
  .user-toggle span,
  .order-btn span {
    display: none;
  }
  
  .nav-link,
  .user-toggle {
    padding: 10px !important;
  }
  
  .brand-text {
    display: none;
  }
}

/* FIXED MOBILE STYLES - Menu slides from LEFT side */
@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: flex;
  }
  
  .nav-links {
    position: fixed;
    top: 0;
    left: -100%; /* Start completely off-screen to the left */
    width: 280px; /* Fixed width for sidebar */
    height: 100vh;
    background-color: #1a1a1a;
    flex-direction: column;
    padding: 80px 20px 20px 20px;
    gap: 8px;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    z-index: 1020;
    overflow-y: auto;
    margin: 0;
    align-items: flex-start;
  }
  
  .nav-links.active {
    left: 0; /* Slide in from left */
  }
  
  .nav-link,
  .user-toggle {
    width: 100%;
    justify-content: flex-start;
    padding: 12px 16px !important;
    border-radius: 8px;
    font-size: 16px;
    border: none;
    background: transparent;
  }
  
  .nav-link span,
  .user-toggle span,
  .order-btn span {
    display: inline;
    font-size: 16px;
  }
  
  .order-btn {
    width: 100%;
    justify-content: flex-start;
    margin: 10px 0;
    padding: 12px 16px;
    font-size: 16px;
    order: 0; /* Keep natural order */
  }
  
  .user-dropdown {
    width: 100%;
  }
  
  .user-dropdown .dropdown-menu {
    position: static !important;
    transform: none !important;
    width: 100%;
    margin-top: 8px !important;
    box-shadow: none;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background-color: #2a2a2a;
  }
  
  .user-dropdown .dropdown-item {
    color: #fff;
    padding: 10px 16px;
  }
  
  .user-dropdown .dropdown-item:hover,
  .user-dropdown .dropdown-item:focus {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  /* Ensure proper spacing for mobile */
  .site-header-inner {
    padding: 0;
  }
  
  .brand-left {
    flex: 1;
  }
}

@media (max-width: 480px) {
  .site-header {
    padding: 10px 15px;
  }
  
  .site-header-inner {
    gap: 12px;
  }
  
  .brand-left img {
    height: 36px;
    width: 36px;
  }
  
  .nav-links {
    width: 260px; /* Slightly smaller on very small screens */
  }
}

/* Backdrop for mobile menu */
.mobile-menu-backdrop {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1019;
  transition: opacity 0.3s ease;
}

.mobile-menu-backdrop.active {
  display: block;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Prevent body scroll when menu is open */
body.menu-open {
  overflow: hidden;
}
</style>

<script>
// Enhanced header functionality
document.addEventListener('DOMContentLoaded', function() {
  const header = document.querySelector('.site-header');
  const mobileToggle = document.querySelector('.mobile-menu-toggle');
  const navLinks = document.querySelector('.nav-links');
  const body = document.body;
  
  // Create backdrop for mobile menu
  const backdrop = document.createElement('div');
  backdrop.className = 'mobile-menu-backdrop';
  document.body.appendChild(backdrop);
  
  // Header scroll effect
  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
  
  // Mobile menu toggle
  if (mobileToggle && navLinks) {
    mobileToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpening = !this.classList.contains('active');
      
      this.classList.toggle('active');
      navLinks.classList.toggle('active');
      backdrop.classList.toggle('active');
      body.classList.toggle('menu-open', isOpening);
    });
    
    // Close mobile menu when clicking on a link
    const navItems = navLinks.querySelectorAll('a');
    navItems.forEach(item => {
      item.addEventListener('click', function() {
        closeMobileMenu();
      });
    });
    
    // Close mobile menu when clicking on backdrop
    backdrop.addEventListener('click', function() {
      closeMobileMenu();
    });
    
    // Close mobile menu when pressing escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && navLinks.classList.contains('active')) {
        closeMobileMenu();
      }
    });
    
    function closeMobileMenu() {
      mobileToggle.classList.remove('active');
      navLinks.classList.remove('active');
      backdrop.classList.remove('active');
      body.classList.remove('menu-open');
    }
  }
  
  // Add subtle animation to order button on page load
  const orderBtn = document.querySelector('.order-btn');
  if (orderBtn) {
    setTimeout(() => {
      orderBtn.style.transform = 'scale(1.02)';
      setTimeout(() => {
        orderBtn.style.transform = '';
      }, 200);
    }, 1000);
  }
});
</script>