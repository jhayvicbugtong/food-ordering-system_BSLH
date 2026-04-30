<?php
// customer/menu.php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// ---------- CONFIG ----------
$COL = [
  'id'        => 'product_id',
  'cat'       => 'category_id',
  'name'      => 'name',
  'desc'      => 'description',
  'price'     => 'base_price',
  'img'       => 'image_url',
  'available' => 'is_available',
];

$table = 'products';
$cat_table = 'categories';

// Helpers
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n){ return '₱' . number_format((float)$n, 2); }
function slugify($s){ return strtolower(preg_replace('/[^a-z0-9]+/i', '-', preg_replace('/\s+/', '-', $s))); }

// --------- REORDER LOGIC ----------
$reorderItems = [];
if (isset($_GET['reorder']) && !empty($_SESSION['user_id'])) {
    $reorderId = (int)$_GET['reorder'];
    $userId = (int)$_SESSION['user_id'];
    
    $stmtCheck = $conn->prepare("SELECT 1 FROM orders WHERE order_id = ? AND user_id = ?");
    $stmtCheck->bind_param("ii", $reorderId, $userId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        $stmtItems = $conn->prepare("
            SELECT p.product_id, p.name, p.base_price, oi.quantity
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ? AND p.is_available = 1
        ");
        $stmtItems->bind_param("i", $reorderId);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();
        while ($row = $resItems->fetch_assoc()) {
            $reorderItems[] = [
                'id' => (int)$row['product_id'],
                'name' => $row['name'],
                'unitPrice' => (float)$row['base_price'],
                'qty' => (int)$row['quantity']
            ];
        }
        $stmtItems->close();
    }
    $stmtCheck->close();
}

// --------- FETCH ITEMS ----------
$sql = "SELECT
          p.{$COL['id']}   AS id,
          p.{$COL['cat']}  AS category_id,
          p.{$COL['name']} AS name,
          p.{$COL['desc']} AS description,
          p.{$COL['price']} AS price,
          p.{$COL['img']}   AS image_url,
          c.category_name,
          c.display_order
        FROM `$table` p
        JOIN `$cat_table` c ON p.{$COL['cat']} = c.category_id
        WHERE p.{$COL['available']} = 1 AND c.is_active = 1
        ORDER BY c.display_order ASC, p.{$COL['name']} ASC";

$res = $conn->query($sql);
if (!$res) { die('Query error: ' . h($conn->error)); }

$items = [];
while ($row = $res->fetch_assoc()) { $items[] = $row; }

$grouped = [];
$categories_map = [];

foreach ($items as $it) {
  $catId = (int)$it['category_id'];
  $grouped[$catId][] = $it;

  if (!isset($categories_map[$catId])) {
    $categories_map[$catId] = [
      'id' => $catId,
      'label' => $it['category_name'],
      'slug' => slugify($it['category_name']),
      'order' => (int)$it['display_order']
    ];
  }
}

$categories = array_values($categories_map);
usort($categories, fn($a,$b)=> $a['order'] <=> $b['order']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Order Online | Bente Sais Lomi House</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
<<<<<<< HEAD
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  <style>
      :root {
          --accent: #5cfa63;
          --accent-hover: #4ade80;
          --dark: #1a1a1a;
      }
      body.menu-page { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
      
      /* --- HERO SECTION ANIMATIONS --- */
      .menu-hero {
          background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
          padding: 80px 0 60px;
          position: relative;
          overflow: hidden;
      }
      .menu-hero::before {
          content: '';
          position: absolute;
          top: 0; left: 0; right: 0; bottom: 0;
          background: 
            radial-gradient(circle at 10% 20%, rgba(92, 250, 99, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 90% 80%, rgba(92, 250, 99, 0.03) 0%, transparent 50%);
          pointer-events: none;
      }

      @keyframes float {
          0% { transform: translateY(0px) rotateY(-5deg); }
          50% { transform: translateY(-15px) rotateY(-5deg); }
          100% { transform: translateY(0px) rotateY(-5deg); }
      }
      
      .hero-image-wrap img {
          border-radius: 20px;
          box-shadow: 0 25px 60px rgba(0,0,0,0.12);
          transform: perspective(1000px) rotateY(-5deg);
          animation: float 6s ease-in-out infinite;
          max-width: 100%;
          width: 420px;
          height: 300px;
          object-fit: cover;
      }

      /* Mobile adjustment for Hero Image */
      @media (max-width: 991px) {
          .menu-hero { padding: 40px 0; }
          .hero-image-wrap img {
              width: 80%;
              max-width: 300px;
              height: auto;
              animation: float 5s ease-in-out infinite;
          }
      }

      /* --- CATEGORY NAV --- */
      .category-sidebar {
          position: sticky;
          top: 100px;
          border-radius: 16px;
          background: white;
          border: 1px solid rgba(0,0,0,0.04);
          box-shadow: 0 4px 20px rgba(0,0,0,0.02);
      }
      .cat-nav-link {
          display: flex; align-items: center; justify-content: space-between;
          padding: 14px 20px; color: #4b5563; font-weight: 500;
          text-decoration: none; border-left: 4px solid transparent;
          transition: all 0.2s;
      }
      .cat-nav-link:hover { background-color: #f9fafb; color: #111; }
      
      /* Active State & Chevron Animation */
      .cat-nav-link.active {
          background-color: #f0fdf4; color: #15803d;
          border-left-color: var(--accent); font-weight: 600;
      }
      .cat-nav-link .bi-chevron-right {
          opacity: 0; 
          transform: translateX(-10px);
          transition: all 0.3s ease;
      }
      .cat-nav-link.active .bi-chevron-right {
          opacity: 1;
          transform: translateX(0);
          color: var(--accent-hover); 
      }

      /* Mobile Nav */
      .mobile-cat-nav {
          position: sticky; top: 60px; z-index: 900;
          background: rgba(255, 255, 255, 0.9);
          backdrop-filter: blur(12px);
          border-bottom: 1px solid rgba(0,0,0,0.05);
      }
      .mobile-cat-pill {
          background: #f3f4f6; color: #4b5563;
          border-radius: 50px; padding: 8px 18px;
          font-size: 0.9rem; font-weight: 500;
          white-space: nowrap; text-decoration: none;
          transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      }
      .mobile-cat-pill.active {
          background: var(--accent); color: #000;
          box-shadow: 0 4px 12px rgba(92, 250, 99, 0.3);
          font-weight: 600;
      }

      /* --- MENU CARD ENHANCEMENTS --- */
      .menu-card {
          border: 1px solid rgba(0,0,0,0.05);
          background: white;
          border-radius: 20px;
          overflow: hidden;
          transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s;
          height: 100%;
          display: flex; flex-direction: column;
      }
      .menu-card:hover {
          transform: translateY(-8px);
          box-shadow: 0 20px 40px rgba(0,0,0,0.08);
          border-color: rgba(92, 250, 99, 0.5);
      }
      
      .menu-img-container {
          height: 200px; overflow: hidden;
          background: #f3f4f6; position: relative;
      }
      .menu-img-container img {
          width: 100%; height: 100%; object-fit: cover;
          transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
      }
      .menu-card:hover .menu-img-container img { transform: scale(1.08); }

      /* Fade Up Animation */
      @keyframes fadeInUp {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
      }
      .search-item { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
      
      .initial-load .search-item:nth-child(1) { animation-delay: 0.1s; }
      .initial-load .search-item:nth-child(2) { animation-delay: 0.2s; }
      .initial-load .search-item:nth-child(3) { animation-delay: 0.3s; }
      .initial-load .search-item:nth-child(4) { animation-delay: 0.4s; }

      /* --- SOLID GREEN BUTTON FIX --- */
      /* More specific selector to override Bootstrap */
      .btn.btn-custom-accent {
          background-color: #5cfa63 !important; /* Force Solid Green */
          background: #5cfa63 !important;
          color: #000 !important;
          border: none !important; 
          font-weight: 700;
          border-radius: 50px; 
          padding: 10px 20px;
          box-shadow: 0 4px 15px rgba(92, 250, 99, 0.3);
          transition: all 0.3s ease;
          letter-spacing: 0.5px;
      }
      .btn.btn-custom-accent:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 25px rgba(92, 250, 99, 0.45);
          filter: brightness(0.95);
      }
      .btn.btn-custom-accent:active { transform: translateY(0); }

      /* --- FIXED SEARCH BAR STYLING --- */
      .search-bar-styled {
          border: 1px solid transparent;
          box-shadow: 0 8px 30px rgba(0,0,0,0.06);
          padding: 16px 20px 16px 60px !important; 
          border-radius: 50px;
          font-size: 1.05rem;
          transition: all 0.3s;
          background: white;
      }
      .search-bar-styled:focus {
          box-shadow: 0 10px 40px rgba(92, 250, 99, 0.15);
          border-color: rgba(92, 250, 99, 0.5);
          outline: none;
      }

      /* Cart Container (Desktop) */
      .cart-sticky-wrapper {
          position: sticky; top: 100px;
          height: calc(100vh - 120px); z-index: 800;
      }
      
      /* Toast Notification */
      .custom-toast {
          background: rgba(33, 37, 41, 0.95);
          backdrop-filter: blur(10px);
          color: white;
          border-radius: 12px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.2);
          border: 1px solid rgba(255,255,255,0.1);
      }
=======
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  <style>
      /* Smooth scrolling for the cart container */
      .cart-sticky-container {
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        overflow: hidden; /* Prevents the container itself from scrolling */
        display: flex;    /* Ensures the inner cart fills the height properly */
        flex-direction: column; 
        z-index: 1020;
    }
      /* Webkit scrollbar styling (Chrome/Safari) */
      .cart-sticky-container::-webkit-scrollbar { width: 6px; }
      .cart-sticky-container::-webkit-scrollbar-track { background: transparent; }
      .cart-sticky-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 10px; }
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
  </style>
</head>
<body class="menu-page d-flex flex-column min-vh-100">

  <?php include __DIR__ . '/includes/header.php'; ?>

<<<<<<< HEAD
  <section class="menu-hero">
    <div class="container">
      <div class="row align-items-center justify-content-center text-center text-lg-start">
        <div class="col-lg-6 mb-4 mb-lg-0 order-1 order-lg-1">
          <h1 class="display-4 fw-bold text-dark mb-3" style="letter-spacing: -1px;">
            Taste the Authentic <br>
            <span style="color: var(--accent); text-shadow: 0 2px 10px rgba(92,250,99,0.2);">Batangas Flavor</span>
          </h1>
          <p class="lead text-muted mb-4" style="line-height: 1.6;">
            From our sizzling plates to our hearty lomi bowls. <br class="d-none d-lg-block">
            Freshly cooked comfort food, delivered to your door.
          </p>
          <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
              <a href="#menuContainer" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm">
                  View Menu
              </a>
              <div class="d-flex align-items-center gap-2 text-muted small">
                  <i class="bi bi-star-fill text-warning"></i>
                  <span>Favorite by locals</span>
              </div>
          </div>
        </div>
        
        <div class="col-lg-5 offset-lg-1 text-center order-0 order-lg-2 mb-4 mb-lg-0">
            <div class="hero-image-wrap">
                <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/gallery/lomi.jpg" 
                     alt="Special Lomi"
                     onerror="this.src='<?= htmlspecialchars($BASE_URL) ?>/assets/images/placeholder.png'">
            </div>
        </div>
      </div>
    </div>
  </section>

  <div class="mobile-cat-nav d-lg-none">
    <div class="container-fluid d-flex flex-nowrap gap-2 py-3 overflow-auto no-scrollbar px-3">
      <?php $first = true; foreach ($categories as $c): ?>
        <a class="mobile-cat-pill <?= $first ? 'active' : '' ?>" href="#<?= h($c['slug']) ?>">
            <?= h($c['label']) ?>
        </a>
      <?php $first = false; endforeach; ?>
    </div>
  </div>

  <main class="container py-5 flex-grow-1 mb-5 mb-lg-0">
    <div class="row g-5">
      
      <div class="col-lg-2 d-none d-lg-block">
        <aside class="category-sidebar">
          <div class="p-3 border-bottom bg-light">
            <h6 class="fw-bold text-uppercase text-muted small mb-0 ls-1">Categories</h6>
          </div>
          <nav class="d-flex flex-column py-2">
            <?php $first = true; foreach ($categories as $c): ?>
              <a class="cat-nav-link <?= $first ? 'active' : '' ?>" href="#<?= h($c['slug']) ?>">
                <span><?= h($c['label']) ?></span>
                <i class="bi bi-chevron-right small"></i>
              </a>
            <?php $first = false; endforeach; ?>
          </nav>
        </aside>
      </div>

      <div class="col-lg-7">
        
        <div class="mb-5 position-relative">
             <i class="bi bi-search position-absolute text-muted" style="left: 20px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; z-index: 5;"></i>
             <input type="text" id="menuSearchInput" class="form-control search-bar-styled" placeholder="Search for lomi, silog, or toppings...">
        </div>

        <div id="menuContainer" class="initial-load">
          <?php if (empty($categories)): ?>
            <div class="text-center py-5">
               <div class="mb-3 opacity-25"><i class="bi bi-basket3 display-1 text-muted"></i></div>
               <h5 class="text-muted">Menu is currently empty.</h5>
            </div>
          <?php else: ?>
            <?php foreach ($categories as $c): ?>
              <div class="menu-category-block mb-5 scroll-margin-top" id="<?= h($c['slug']) ?>" style="scroll-margin-top: 140px;">
                <div class="d-flex align-items-center mb-4">
                  <h3 class="h4 fw-bold mb-0 text-dark"><?= h($c['label']) ?></h3>
                  <span class="badge bg-light text-muted border ms-2 rounded-pill"><?= count($grouped[$c['id']]) ?></span>
                  <div class="ms-3 flex-grow-1 border-bottom"></div>
                </div>
                
                <div class="row row-cols-1 row-cols-md-2 g-4">
=======
  <div class="mobile-category-nav d-lg-none">
    <div class="container-fluid d-flex flex-nowrap gap-2 py-2 overflow-auto no-scrollbar px-3">
      <?php $first = true; foreach ($categories as $c): ?>
        <a class="category-link-mobile btn btn-sm rounded-pill text-nowrap <?= $first ? 'active' : '' ?>" href="#<?= h($c['slug']) ?>">
            <?= h($c['label']) ?>
        </a>
      <?php $first = false; endforeach; ?>
    </div>
  </div>

  <main class="container py-4 flex-grow-1 mb-5 mb-lg-0">
    <div class="row g-4">
      
     <div class="col-lg-2 d-none d-lg-block">
        <aside class="menu-categories card shadow-sm border-0" style="position: sticky; top: 100px; align-self: start;">
          <div class="card-body">
            <h2 class="h6 fw-bold mb-3 text-uppercase">Menu</h2>
            <ul class="category-list list-unstyled">
              <?php $first = true; foreach ($categories as $c): ?>
                <li class="mb-1">
                  <a class="category-link d-block px-3 py-2 rounded-3 text-decoration-none <?= $first ? 'active' : '' ?>" href="#<?= h($c['slug']) ?>">
                    <?= h($c['label']) ?>
                  </a>
                </li>
              <?php $first = false; endforeach; ?>
              <?php if (empty($categories)): ?>
                <li class="text-muted px-3 py-2 small">No available items.</li>
              <?php endif; ?>
            </ul>
          </div>
        </aside>
      </div>

      <div class="col-lg-7">
        <section class="menu-items-area">
          <?php if (empty($categories)): ?>
            <div class="card shadow-sm border-0">
              <div class="card-body text-center py-5">
                <h3 class="h5 text-muted">No available menu items</h3>
                <p class="text-muted mb-0">Please check back later.</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($categories as $c): ?>
              <div class="menu-category-block mb-5" id="<?= h($c['slug']) ?>">
                <div class="menu-category-header mb-3">
                  <h3 class="h4 fw-bold mb-0"><?= h($c['label']) ?></h3>
                </div>
                <div class="d-flex flex-column gap-3">
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
                  <?php foreach ($grouped[$c['id']] as $it): ?>
                    <?php
                      $imgRel = $it['image_url'] ?: '';
                      $imgWeb = '../' . ltrim($imgRel, '/');
                      $placeholder = '../assets/images/placeholder.png';
                    ?>
<<<<<<< HEAD
                    <div class="col search-item">
                        <div class="menu-card h-100 position-relative">
                           <div class="menu-img-container">
                               <img src="<?= h($imgWeb) ?>" 
                                    alt="<?= h($it['name']) ?>"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='<?= h($placeholder) ?>';">
                               <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-to-t" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                                   <span class="badge bg-white text-dark shadow fw-bold px-3 py-2 rounded-pill">
                                       <?= h(peso($it['price'])) ?>
                                   </span>
                               </div>
                           </div>
                           <div class="p-4 d-flex flex-column flex-grow-1">
                               <div class="mb-3">
                                   <h5 class="fw-bold fs-5 mb-2 text-dark menu-item-name"><?= h($it['name']) ?></h5>
                                   <p class="text-muted small mb-0 menu-item-desc opacity-75" style="line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                       <?= h($it['description']) ?>
                                   </p>
                               </div>
                               <div class="mt-auto">
                                   <button class="btn btn-custom-accent w-100 add-btn d-flex align-items-center justify-content-center gap-2" type="button" data-id="<?= (int)$it['id'] ?>">
                                       Add to Order <i class="bi bi-plus-lg"></i>
                                   </button>
                                   <span class="d-none menu-item-price"><?= $it['price'] ?></span>
                               </div>
                           </div>
                        </div>
=======
                    <div class="menu-item-card card shadow-sm border-0 h-100">
                      <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="menu-item-imgwrap rounded overflow-hidden" style="width: 80px; height: 80px; flex-shrink: 0;">
                            <img src="<?= h($imgWeb) ?>" 
                                 alt="<?= h($it['name']) ?>" 
                                 class="img-fluid h-100 w-100"
                                 style="object-fit: cover;"
                                 onerror="this.onerror=null;this.src='<?= h($placeholder) ?>';">
                          </div>
                          <div class="menu-item-main flex-grow-1">
                            <div class="menu-item-toprow d-flex justify-content-between align-items-start mb-1">
                              <h4 class="menu-item-name h6 fw-bold mb-0 me-2"><?= h($it['name']) ?></h4>
                              <div class="menu-item-price fw-bold text-dark text-nowrap"><?= h(peso($it['price'])) ?></div>
                            </div>
                            <div class="menu-item-desc text-muted small mb-2"><?= h($it['description']) ?></div>
                            <div class="menu-item-actions text-end">
                              <button class="add-btn btn btn-sm fw-semibold" type="button" data-id="<?= (int)$it['id'] ?>" style="background-color: var(--accent); color: #000;">
                                <i class="bi bi-plus-circle me-1"></i> Add
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
<<<<<<< HEAD

          <div id="noResultsMsg" class="text-center py-5 d-none">
              <i class="bi bi-search display-4 text-muted mb-3 opacity-25"></i>
              <h5 class="text-muted">No matching items found.</h5>
          </div>
        </div>
      </div>

      <div class="col-lg-3 d-none d-lg-block">
         <div class="cart-sticky-wrapper">
=======
        </section>
      </div>

      <div class="col-lg-3 d-none d-lg-block">
         <div class="cart-sticky-container">
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
            <?php include __DIR__ . '/includes/delivery-cart.php'; ?>
         </div>
      </div>

    </div>
  </main>
  
  <div class="mobile-cart-floating d-lg-none">
    <div class="d-flex flex-column justify-content-center">
<<<<<<< HEAD
      <div class="cart-floating-count text-muted small"><span class="js-cart-count fw-bold text-dark">0</span> items</div>
      <div class="cart-floating-total js-cart-total-bar text-dark fs-5 fw-bold">₱0.00</div>
    </div>
    <button class="btn btn-custom-accent rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-lg border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartOffcanvas">
      <span class="fw-bold text-dark">View Cart</span>
      <i class="bi bi-bag-fill text-dark"></i>
    </button>
  </div>

  <div class="offcanvas offcanvas-bottom h-85 rounded-top-4" tabindex="-1" id="mobileCartOffcanvas">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold">Your Order</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 bg-light">
       <div class="h-100">
=======
      <div class="cart-floating-count"><span class="js-cart-count">0</span> items</div>
      <div class="cart-floating-total js-cart-total-bar">₱0.00</div>
    </div>
    <button class="btn btn-accent-floating rounded-pill px-4 d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCartOffcanvas">
      <span class="fw-bold">View Cart</span>
      <i class="bi bi-cart3"></i>
    </button>
  </div>

  <div class="offcanvas offcanvas-bottom h-75 rounded-top-4" tabindex="-1" id="mobileCartOffcanvas" aria-labelledby="mobileCartOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold" id="mobileCartOffcanvasLabel">Your Order</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 bg-light">
       <div class="h-100 p-3">
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
         <?php include __DIR__ . '/includes/delivery-cart.php'; ?>
       </div>
    </div>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>
  <?php include __DIR__ . '/includes/address-modal.php'; ?>
<<<<<<< HEAD
  
  <script>
    // Remove initial load class after animation
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            const container = document.getElementById('menuContainer');
            if(container) container.classList.remove('initial-load');
        }, 1000);
    });

    // --- JS HELPERS ---
    function h(str) { if (!str) return ''; return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function currency(n){ return `₱${(Number(n)||0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`; }
    
    // --- CART STATE ---
=======
  <script>
    function h(str) {
        if (!str) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function currency(n){ return `₱${(Number(n)||0).toFixed(2)}`; }
    
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
    function getCart() {
      try {
        const cart = JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
        if (!Array.isArray(cart.items)) cart.items = [];
        return cart;
      } catch(e){ return { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; }
    }
    function saveCart(cart) { localStorage.setItem('bslh_cart', JSON.stringify(cart)); }
<<<<<<< HEAD

    // --- FIXED SEARCH LOGIC ---
    document.getElementById('menuSearchInput').addEventListener('keyup', function() {
        const term = this.value.toLowerCase().trim();
        const categories = document.querySelectorAll('.menu-category-block');
        let hasGlobalResults = false;

        categories.forEach(cat => {
            const items = cat.querySelectorAll('.search-item');
            let hasVisibleItems = false;
            
            items.forEach(item => {
                const nameEl = item.querySelector('.menu-item-name');
                const descEl = item.querySelector('.menu-item-desc');
                
                const name = nameEl ? nameEl.textContent.toLowerCase() : '';
                const desc = descEl ? descEl.textContent.toLowerCase() : '';
                
                const matches = name.includes(term) || desc.includes(term);
                const isHidden = item.classList.contains('d-none');
                
                if (matches) {
                    if (isHidden) {
                        item.classList.remove('d-none');
                        item.style.animation = 'none';
                        item.offsetHeight; // trigger reflow
                        item.style.animation = 'fadeInUp 0.5s ease-out forwards';
                    }
                    hasVisibleItems = true;
                    hasGlobalResults = true;
                } else {
                    if (!isHidden) {
                        item.classList.add('d-none');
                    }
                }
            });
            
            if (hasVisibleItems) {
                cat.classList.remove('d-none');
            } else {
                cat.classList.add('d-none');
            }
        });
        
        const noRes = document.getElementById('noResultsMsg');
        if (hasGlobalResults) {
            noRes.classList.add('d-none');
        } else {
            noRes.classList.remove('d-none');
        }
    });

    // --- SCROLLSPY ---
    const navLinks = document.querySelectorAll('.cat-nav-link, .mobile-cat-pill');
    const sections = document.querySelectorAll('.menu-category-block');
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            if (section.style.display === 'none' || section.classList.contains('d-none')) return; 
            const sectionTop = section.offsetTop - 200;
            if (window.scrollY >= sectionTop) current = section.getAttribute('id');
        });
        navLinks.forEach(link => {
            link.classList.remove('active');
            const icon = link.querySelector('.bi-chevron-right');
            if(icon) icon.classList.add('opacity-50');

            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
                if(icon) icon.classList.remove('opacity-50');
                if(link.classList.contains('mobile-cat-pill')) {
                     link.closest('.overflow-auto').scrollTo({
                         left: link.offsetLeft - 20, behavior: 'smooth'
                     });
                }
            }
        });
    });

    // --- CART ACTIONS ---
    function addItemToCart(id, name, unitPrice, addQty) {
=======

    // --- Scroll Spy & Sticky Nav (With Fixes) ---
    document.addEventListener("DOMContentLoaded", function() {
      const allLinks = document.querySelectorAll('.category-link, .category-link-mobile');
      const sections = document.querySelectorAll('.menu-category-block');
      const offset = 120; 

      let lastActiveSection = null;

      allLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            const targetPosition = targetElement.offsetTop - offset;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
          }
        });
      });

      function updateActiveSection() {
        let current = '';
        sections.forEach(section => {
          const sectionTop = section.offsetTop - offset - 50;
          if (window.scrollY >= sectionTop) current = section.getAttribute('id');
        });

        if (current !== lastActiveSection) {
            lastActiveSection = current;

            document.querySelectorAll('.category-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) link.classList.add('active');
            });

            document.querySelectorAll('.category-link-mobile').forEach(link => {
                 const isActive = link.getAttribute('href') === '#' + current;
                 if (isActive) {
                     link.classList.add('active');
                     // Fix: Scroll Container only
                     const navContainer = link.closest('.overflow-auto'); 
                     if(navContainer) {
                         const linkRect = link.getBoundingClientRect();
                         const containerRect = navContainer.getBoundingClientRect();
                         const currentScrollLeft = navContainer.scrollLeft;
                         const linkCenter = linkRect.left + (linkRect.width / 2);
                         const containerCenter = containerRect.left + (containerRect.width / 2);
                         const diff = linkCenter - containerCenter;

                         navContainer.scrollTo({
                             left: currentScrollLeft + diff,
                             behavior: 'smooth'
                         });
                     }
                 } else {
                     link.classList.remove('active');
                 }
            });
        }
      }

      window.addEventListener('scroll', updateActiveSection);
      updateActiveSection();
    });

    // --- MAIN CART RENDER LOGIC ---
    document.addEventListener('DOMContentLoaded', function() {
      
      const reorderData = <?= json_encode($reorderItems) ?>;
      if (reorderData && reorderData.length > 0) {
          const cart = { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
          reorderData.forEach(item => {
              cart.items.push({ id: item.id, name: item.name, unitPrice: item.unitPrice, qty: item.qty });
          });
          saveCart(cart);
          renderCartFromStorage();
          showQuickMessage('✅ Re-ordered items added to cart!');
          if (window.history.replaceState) {
              const url = new URL(window.location);
              url.searchParams.delete('reorder');
              window.history.replaceState({}, document.title, url.pathname);
          }
      }

      function addItemToCart(id, name, unitPrice, addQty) {
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
        const cart = getCart();
        const existingIdx = cart.items.findIndex(i => i.id === id); 
        if (existingIdx > -1) {
          cart.items[existingIdx].qty = Math.min(cart.items[existingIdx].qty + addQty, 50);
        } else {
          cart.items.push({ id, name, unitPrice, qty: addQty });
        }
<<<<<<< HEAD
        saveCart(cart);
        renderCartFromStorage();
        showToast(`<b>${h(name)}</b> added to cart`);
    }

    function updateCartQuantity(id, newQty) {
        const cart = getCart();
        const idx = cart.items.findIndex(i => i.id === id);
        if (idx === -1) return;

        if (newQty <= 0) {
            cart.items.splice(idx, 1);
        } else {
            cart.items[idx].qty = Math.min(newQty, 50);
        }
        saveCart(cart);
        renderCartFromStorage();
    }

    function renderCartFromStorage() {
        const cart = getCart();
        let subtotal = 0;
        let count = 0;
        cart.items.forEach(i => { subtotal += i.unitPrice * i.qty; count += i.qty; });
        
        const containers = document.querySelectorAll('.js-cart-items');
        containers.forEach(container => {
            if(cart.items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 opacity-50">
                        <i class="bi bi-cart-x display-4 text-muted mb-3"></i>
                        <h6 class="text-muted">Your cart is empty</h6>
                        <p class="small text-muted">Hungry? Add some items!</p>
                    </div>`;
            } else {
                container.innerHTML = cart.items.map(item => `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom cart-item-anim">
                        <div class="flex-grow-1 pe-2">
                            <div class="fw-semibold text-dark">${h(item.name)}</div>
                            <div class="text-muted small">${currency(item.unitPrice)}</div>
                        </div>
                        <div class="d-flex align-items-center bg-white shadow-sm rounded-pill border px-1">
                            <button class="btn btn-sm btn-link text-dark p-1 px-2 border-0" onclick="updateCartQuantity(${item.id}, ${item.qty - 1})"><i class="bi bi-dash"></i></button>
                            <span class="fw-bold small px-2" style="min-width: 20px; text-align: center;">${item.qty}</span>
                            <button class="btn btn-sm btn-link text-dark p-1 px-2 border-0" onclick="updateCartQuantity(${item.id}, ${item.qty + 1})"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                `).join('');
            }
        });

        document.querySelectorAll('.js-cart-subtotal').forEach(el => el.textContent = currency(subtotal));
        document.querySelectorAll('.js-cart-total').forEach(el => el.textContent = currency(subtotal));
        document.querySelectorAll('.js-cart-count').forEach(el => el.textContent = count);
        document.querySelectorAll('.js-cart-total-bar').forEach(el => el.textContent = currency(subtotal));
    }

    function showToast(msg) {
        const existing = document.querySelector('.custom-toast-container');
        if(existing) existing.remove();

        const div = document.createElement('div');
        div.className = 'custom-toast-container position-fixed bottom-0 start-50 translate-middle-x p-3';
        div.style.zIndex = 2000;
        div.innerHTML = `<div class="toast show custom-toast p-3"><div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> <span>${msg}</span></div>
                            <button type="button" class="btn-close btn-close-white small" data-bs-dismiss="toast"></button>
                         </div></div>`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 2500);
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        const reorderData = <?= json_encode($reorderItems) ?>;
        if (reorderData.length) {
            const cart = { items: reorderData, subtotal: 0, deliveryFee: 0, total: 0 };
            saveCart(cart);
            window.history.replaceState({}, document.title, window.location.pathname);
            showToast("Reorder items added!");
        }
        
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.menu-card');
                const id = Number(this.dataset.id);
                const name = card.querySelector('.menu-item-name').textContent;
                const price = parseFloat(card.querySelector('.menu-item-price').textContent);
                addItemToCart(id, name, price, 1);
            });
        });

        renderCartFromStorage();
=======
        saveCart(cart);
        renderCartFromStorage();
        showQuickMessage('🍜 Item added to cart!');
      }
      
      function updateCartQuantity(id, newQty) {
          const cart = getCart();
          const existingIdx = cart.items.findIndex(i => i.id === id);
          if (existingIdx === -1) return;

          if (newQty <= 0) {
              const lines = document.querySelectorAll(`.cart-line[data-id="${id}"]`);
              lines.forEach(l => animateRemoveItem(l));
              setTimeout(() => { removeItemFromCart(id); }, 400); 
              return;
          }

          if (newQty > 50) { newQty = 50; showQuickMessage('Max quantity is 50'); }
          cart.items[existingIdx].qty = newQty;
          saveCart(cart);
          renderCartFromStorage();
      }

      function removeItemFromCart(id) {
        const cart = getCart();
        cart.items = cart.items.filter(i => i.id !== id);
        saveCart(cart);
        renderCartFromStorage();
      }

      function renderCartFromStorage() {
        const cart = getCart();
        let subtotal = 0;
        let itemCount = 0;
        cart.items.forEach(item => { 
             subtotal += (item.unitPrice * item.qty);
             itemCount += item.qty;
        });
        const deliveryFee = cart.deliveryFee || 0;
        const total = subtotal + deliveryFee;

        const containers = document.querySelectorAll('.js-cart-items');
        containers.forEach(container => {
            container.innerHTML = '';
            if (cart.items.length === 0) {
                container.innerHTML = `<div class="cart-empty text-center p-3"><div class="text-muted small">Your cart is empty</div></div>`;
            } else {
                cart.items.forEach(item => {
                    const lineTotal = item.unitPrice * item.qty;
                    const cartLine = document.createElement('div');
                    cartLine.className = 'cart-line card shadow-sm border-0 mb-2';
                    cartLine.dataset.id = item.id;
                    cartLine.innerHTML = `
                        <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="cart-line-name small fw-semibold me-2">${h(item.name)}</div>
                            <div class="cart-line-price small fw-bold text-nowrap">${currency(lineTotal)}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group btn-group-sm" role="group">
                            <button class="qty-btn minus btn btn-outline-secondary" type="button" data-id="${item.id}"><i class="bi bi-dash-lg"></i></button>
                            <span class="qty-value btn btn-light disabled" style="width: 2.5rem;">${item.qty}</span>
                            <button class="qty-btn plus btn btn-outline-secondary" type="button" data-id="${item.id}" ${item.qty >= 50 ? 'disabled' : ''}><i class="bi bi-plus-lg"></i></button>
                            </div>
                            <button class="cart-delete-btn btn btn-sm btn-outline-danger border-0" type="button" title="Remove" data-id="${item.id}"><i class="bi bi-trash3"></i></button>
                        </div>
                        </div>
                    `;
                    container.appendChild(cartLine);
                });
            }
        });

        document.querySelectorAll('.js-cart-subtotal').forEach(el => el.textContent = currency(subtotal));
        document.querySelectorAll('.js-cart-delivery').forEach(el => el.textContent = currency(deliveryFee));
        document.querySelectorAll('.js-cart-total').forEach(el => el.textContent = currency(total));
        document.querySelectorAll('.js-cart-count').forEach(el => el.textContent = itemCount);
        document.querySelectorAll('.js-cart-total-bar').forEach(el => el.textContent = currency(total));

        attachCartEventListeners();
        cart.subtotal = subtotal;
        cart.total = total;
        saveCart(cart);
      }

      function attachCartEventListeners() {
        document.querySelectorAll('.cart-delete-btn').forEach(btn => {
          btn.onclick = (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const lines = document.querySelectorAll(`.cart-line[data-id="${id}"]`);
            lines.forEach(l => animateRemoveItem(l));
            setTimeout(() => removeItemFromCart(id), 400);
          };
        });
        document.querySelectorAll('.qty-btn.minus').forEach(btn => {
          btn.onclick = (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const qtyEl = btn.closest('.btn-group').querySelector('.qty-value');
            updateCartQuantity(id, (parseInt(qtyEl.textContent) || 0) - 1);
          };
        });
        document.querySelectorAll('.qty-btn.plus').forEach(btn => {
          btn.onclick = (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const qtyEl = btn.closest('.btn-group').querySelector('.qty-value');
            updateCartQuantity(id, (parseInt(qtyEl.textContent) || 0) + 1);
          };
        });
        document.querySelectorAll('.checkout-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                if ((getCart().items || []).length === 0) { showQuickMessage('🛒 Your cart is empty!'); return; }
                window.location.href = window.BASE_URL + "/customer/checkout.php";
            };
        });
      }

      function animateRemoveItem(element) { if (element) element.classList.add('removing'); }

      function showQuickMessage(message) {
        const existingToast = document.querySelector('.bs-toast-container');
        if (existingToast) existingToast.remove();
        const toastContainer = document.createElement('div');
        toastContainer.className = 'bs-toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = "1056";
        toastContainer.innerHTML = `<div class="toast fade show"><div class="toast-header"><strong class="me-auto">Bente Sais</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div><div class="toast-body">${message}</div></div>`;
        document.body.appendChild(toastContainer);
        const toastEl = toastContainer.querySelector('.toast');
        const bsToast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toastEl.addEventListener('hidden.bs.toast', () => toastContainer.remove());
        bsToast.show();
      }

      function initAddButtons() {
        document.querySelectorAll('.add-btn').forEach(button => {
          button.addEventListener('click', () => {
            const card = button.closest('.menu-item-card');
            const id = Number(button.dataset.id); 
            const name = card.querySelector('.menu-item-name').textContent.trim();
            const price = parseFloat(card.querySelector('.menu-item-price').textContent.replace(/[₱,]/g, ''));
            if (!id || !name || isNaN(price)) return;
            addItemToCart(id, name, price, 1);
          });
        });
      }

      initAddButtons();
      renderCartFromStorage();
>>>>>>> 5fbad8b569a9db41eb67099029ed6d08fed544e0
    });
  </script>
</body>
</html>