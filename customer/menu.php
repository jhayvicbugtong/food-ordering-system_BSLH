<?php
// customer/menu.php
session_start(); // Add session start for reorder check
// DB-driven menu (Bootstrap 5)
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
    
    // Verify ownership to prevent ID guessing
    $stmtCheck = $conn->prepare("SELECT 1 FROM orders WHERE order_id = ? AND user_id = ?");
    $stmtCheck->bind_param("ii", $reorderId, $userId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        // Fetch items: Join with products to get current price and availability
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

// --------- FETCH ITEMS (available only) ----------
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
if (!$res) {
  die('Query error: ' . h($conn->error));
}

$items = [];
while ($row = $res->fetch_assoc()) {
  $items[] = $row;
}

// Group by category_id
$grouped = [];
$categories_map = []; // To store category info dynamically

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

// Convert map to list and sort by display_order
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
</head>
<body class="menu-page d-flex flex-column min-vh-100">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="container py-4 flex-grow-1">
    <div class="row g-4">
      
     <div class="col-lg-2">
        <aside class="menu-categories card shadow-sm border-0" style="position: sticky; top: 86px; align-self: start;">
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
              <?php
                $catId = $c['id'];
                $label = $c['label'];
                $slug  = $c['slug'];
              ?>
              <div class="menu-category-block mb-5" id="<?= h($slug) ?>">
                <div class="menu-category-header mb-3">
                  <h3 class="h4 fw-bold mb-0"><?= h($label) ?></h3>
                </div>
                
                <div class="d-flex flex-column gap-3">
                  <?php foreach ($grouped[$catId] as $it): ?>
                    <?php
                      $imgRel = $it['image_url'] ?: '';
                      $imgWeb = '../' . ltrim($imgRel, '/');
                      $placeholder = '../assets/images/placeholder.png';
                    ?>
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
                            <div class="menu-item-desc text-muted small mb-2">
                              <?= h($it['description']) ?>
                            </div>
                            <div class="menu-item-actions text-end">
                              <button class="add-btn btn btn-sm fw-semibold" type="button" data-id="<?= (int)$it['id'] ?>" style="background-color: var(--accent); color: #000;">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </div>

      <div class="col-lg-3">
        <?php include __DIR__ . '/includes/delivery-cart.php'; ?>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
  <?php include __DIR__ . '/includes/address-modal.php'; ?>

  

  <script>
  // ------- cart persistence -------
    function getCart() {
      try {
        const cart = JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
        if (!Array.isArray(cart.items)) {
          cart.items = [];
        }
        cart.items = cart.items.filter(item => item && (item.id !== null && item.id !== undefined));
        return cart;
      } 
      catch(e){ 
        return { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; 
      }
    }
    function saveCart(cart) { localStorage.setItem('bslh_cart', JSON.stringify(cart)); }
    function clearCart() { localStorage.removeItem('bslh_cart'); }
    function currency(n){ return `₱${(Number(n)||0).toFixed(2)}`; }

    
    // --- Sidebar category active state (scroll spy) ---
    document.addEventListener("DOMContentLoaded", function() {
      const categoryLinks = document.querySelectorAll('.category-link');
      const sections = document.querySelectorAll('.menu-category-block');
      const stickyHeaderOffset = 100;

      categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            const targetPosition = targetElement.offsetTop - stickyHeaderOffset;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
          }
        });
      });

      window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
          const sectionTop = section.offsetTop - stickyHeaderOffset - 20;
          if (window.scrollY >= sectionTop) {
            current = section.getAttribute('id');
          }
        });
        categoryLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
          }
        });
      });
    });


    // --- BOOTSTRAP-BASED CART LOGIC ---
    document.addEventListener('DOMContentLoaded', function() {
      
      // --- REORDER LOGIC INJECTION ---
      const reorderData = <?= json_encode($reorderItems) ?>;
      if (reorderData && reorderData.length > 0) {
          const cart = { items: [], subtotal: 0, deliveryFee: 0, total: 0 };
          
          reorderData.forEach(item => {
              cart.items.push({
                  id: item.id,
                  name: item.name,
                  unitPrice: item.unitPrice,
                  qty: item.qty
              });
          });
          
          saveCart(cart);
          renderCartFromStorage();
          showQuickMessage('✅ Re-ordered items added to cart!');
          
          // Clean URL
          if (window.history.replaceState) {
              const url = new URL(window.location);
              url.searchParams.delete('reorder');
              window.history.replaceState({}, document.title, url.pathname);
          }
      }
      // --- END REORDER LOGIC ---

      function addItemToCart(id, name, unitPrice, addQty) {
        const cart = getCart();
        const existingIdx = cart.items.findIndex(i => i.id === id); 

        if (existingIdx > -1) {
          cart.items[existingIdx].qty = Math.min(cart.items[existingIdx].qty + addQty, 50);
        } else {
          cart.items.push({ id, name, unitPrice, qty: addQty });
        }
        
        saveCart(cart);
        renderCartFromStorage();
        showQuickMessage('🍜 Item added to cart!');
      }
      
      function updateCartQuantity(id, newQty) {
          const cart = getCart();
          const existingIdx = cart.items.findIndex(i => i.id === id);
          if (existingIdx === -1) return;

          if (newQty <= 0) {
              const cartLine = document.querySelector(`.cart-line[data-id="${id}"]`);
              animateRemoveItem(id, cartLine);
              return;
          }

          if (newQty > 50) {
              newQty = 50;
              showQuickMessage('Max quantity is 50');
          }

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
        const itemsEl = document.getElementById('cartItems');
        const subtotalEl = document.getElementById('cartSubtotal');
        const totalEl = document.getElementById('cartTotal');
        const deliveryFeeEl = document.getElementById('cartDeliveryFee');
        const deliveryFeeText = deliveryFeeEl ? (deliveryFeeEl.textContent || '0') : '0';
        const deliveryFee = parseFloat(deliveryFeeText.replace(/[₱,]/g, '')) || 0;

        if (!itemsEl || !subtotalEl || !totalEl) return;
        itemsEl.innerHTML = ''; 

        const validItems = cart.items.filter(item => item && (item.id !== null && item.id !== undefined));

        if (validItems.length === 0) {
          itemsEl.innerHTML = `
            <div class="cart-empty text-center p-3" style="flex-shrink: 0;">
              <div class="text-muted small">Your cart is empty</div>
            </div>
          `;
          if (cart.items.length > 0) {
            cart.items = [];
            saveCart(cart);
          }
          updateCartTotals(0, deliveryFee, subtotalEl, totalEl, deliveryFeeEl);
          return;
        }

        let subtotal = 0;
        validItems.forEach(item => { 
          const lineTotal = item.unitPrice * item.qty;
          subtotal += lineTotal;

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
                  <button class="qty-btn minus btn btn-outline-secondary" type="button" data-id="${item.id}">
                    <i class="bi bi-dash-lg"></i>
                  </button>
                  <span class="qty-value btn btn-light disabled" style="width: 2.5rem;">${item.qty}</span>
                  <button class="qty-btn plus btn btn-outline-secondary" type="button" data-id="${item.id}"
                          ${item.qty >= 50 ? 'disabled' : ''}>
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
                <button class="cart-delete-btn btn btn-sm btn-outline-danger border-0" type="button" title="Remove" data-id="${item.id}">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          `;
          itemsEl.appendChild(cartLine);
        });
        
        attachCartEventListeners();
        updateCartTotals(subtotal, deliveryFee, subtotalEl, totalEl, deliveryFeeEl);
      }
      
      function h(str) {
          if (!str) return '';
          return str.toString()
              .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
      }

      function attachCartEventListeners() {
        const itemsEl = document.getElementById('cartItems');
        if (!itemsEl) return;

        itemsEl.querySelectorAll('.cart-delete-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const cartLine = btn.closest('.cart-line');
            animateRemoveItem(id, cartLine);
          });
        });
        
        itemsEl.querySelectorAll('.qty-btn.minus').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const qtyEl = btn.closest('.btn-group').querySelector('.qty-value');
            const newQty = (parseInt(qtyEl.textContent) || 0) - 1;
            animateQuantityChange(qtyEl);
            updateCartQuantity(id, newQty);
          });
        });
        
        itemsEl.querySelectorAll('.qty-btn.plus').forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = Number(btn.dataset.id);
            const qtyEl = btn.closest('.btn-group').querySelector('.qty-value');
            const newQty = (parseInt(qtyEl.textContent) || 0) + 1;
            animateQuantityChange(qtyEl);
            updateCartQuantity(id, newQty);
          });
        });
      }

      function animateRemoveItem(id, element) {
        if (!element) {
          removeItemFromCart(id); 
          return;
        }
        element.classList.add('removing');
        element.addEventListener('transitionend', () => {
          removeItemFromCart(id);
          showQuickMessage('🗑️ Item removed');
        }, { once: true });
      }

      function animateQuantityChange(qtyEl) {
        qtyEl.classList.add('changing');
        qtyEl.addEventListener('animationend', () => {
          qtyEl.classList.remove('changing');
        }, { once: true });
      }

      function updateCartTotals(subtotal, deliveryFee, subtotalEl, totalEl, deliveryFeeEl) {
        const cart = getCart();
        cart.subtotal = subtotal;
        cart.deliveryFee = deliveryFee || 0;
        cart.total = subtotal + cart.deliveryFee;

        subtotalEl.textContent = currency(cart.subtotal);
        totalEl.textContent = currency(cart.total);
        if (deliveryFeeEl) deliveryFeeEl.textContent = currency(cart.deliveryFee);

        highlightTotal();
        saveCart(cart);
      }

      function highlightTotal() {
        const totalRow = document.querySelector('.cart-summary-row.total');
        if (totalRow) {
          totalRow.classList.add('highlight');
          totalRow.addEventListener('animationend', () => {
            totalRow.classList.remove('highlight');
          }, { once: true });
        }
      }

      function showQuickMessage(message) {
        const existingToast = document.querySelector('.bs-toast-container');
        if (existingToast) existingToast.remove();
        
        const toastContainer = document.createElement('div');
        toastContainer.className = 'bs-toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = "1056";
        
        const toastHTML = `
          <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <strong class="me-auto">Bente Sais Cart</strong>
              <button type"button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
              ${message}
            </div>
          </div>
        `;
        toastContainer.innerHTML = toastHTML;
        document.body.appendChild(toastContainer);
        
        const toastEl = toastContainer.querySelector('.toast');
        const bsToast = new bootstrap.Toast(toastEl, { delay: 2000 });
        
        toastEl.addEventListener('hidden.bs.toast', () => {
          toastContainer.remove();
        });
        
        bsToast.show();
      }

      function initAddButtons() {
        document.querySelectorAll('.add-btn').forEach(button => {
          button.addEventListener('click', () => {
            const card = button.closest('.menu-item-card');
            const id = Number(button.dataset.id); 
            const name = card.querySelector('.menu-item-name').textContent.trim();
            const price = parseFloat(card.querySelector('.menu-item-price').textContent.replace(/[₱,]/g, ''));
            const qty = 1;

            if (!id || !name || isNaN(price)) return;
            addItemToCart(id, name, price, qty);
          });
        });

        const checkoutBtn = document.querySelector('.checkout-btn');
        if (checkoutBtn) {
          checkoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const hasItems = (getCart().items || []).length > 0;
            if (!hasItems) {
              showQuickMessage('🛒 Your cart is empty!');
              return;
            }
            window.location.href = window.BASE_URL + "/customer/checkout.php";
          });
        }
      }

      // --- INITIALIZE ---
      initAddButtons();
      renderCartFromStorage();
    });
  </script>
  
</body>
</html>