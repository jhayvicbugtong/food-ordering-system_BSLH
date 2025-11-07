<?php
// customer/menu.php — DB-driven menu (only available items)
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

// If you have friendly names for categories, map them here:
$CATEGORY_NAMES = [
   6  => 'Lomi Bowls',
   7  => 'Silog Meals',
   8  => 'Party Trays',
   9  => 'Drinks',
   10 => 'Sides',
   11 => 'Panghimagas',
];

// Detect actual table name: prefer "product", fallback to "products"
$table = 'product';
$chk = $conn->query("SHOW TABLES LIKE 'product'");
if (!$chk || $chk->num_rows === 0) {
  $chk2 = $conn->query("SHOW TABLES LIKE 'products'");
  if ($chk2 && $chk2->num_rows > 0) $table = 'products';
}

// Helpers
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n){ return '₱' . number_format((float)$n, 2); }
function slugify($s){ return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $s)); }

// --------- FETCH ITEMS (available only) ----------
$sql = "SELECT
          {$COL['id']}   AS id,
          {$COL['cat']}  AS category_id,
          {$COL['name']} AS name,
          {$COL['desc']} AS description,
          {$COL['price']} AS price,
          {$COL['img']}   AS image_url
        FROM `$table`
        WHERE {$COL['available']} = 1
        ORDER BY {$COL['cat']} ASC, {$COL['name']} ASC";

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
foreach ($items as $it) {
  $catId = (int)$it['category_id'];
  $grouped[$catId][] = $it;
}

// Build a list with friendly category names (or “Category {id}”)
$categories = [];
foreach ($grouped as $catId => $_rows) {
  $label = $CATEGORY_NAMES[$catId] ?? ('Category ' . $catId);
  $categories[] = ['id' => $catId, 'label' => $label, 'slug' => slugify($label)];
}
usort($categories, fn($a,$b)=>strcmp($a['label'],$b['label'])); // sort by label

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Order Online | Bente Sais Lomi House</title>

  <!-- Fonts + Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <!-- Shared CSS (keep your path) -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
</head>
<body class="menu-page">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="menu-layout">
    <!-- LEFT: categories list -->
    <aside class="menu-categories">
      <h2>Menu</h2>
      <ul class="category-list">
        <?php $first = true; foreach ($categories as $c): ?>
          <li>
            <a class="category-link <?= $first ? 'active' : '' ?>" href="#<?= h($c['slug']) ?>">
              <?= h($c['label']) ?>
            </a>
          </li>
        <?php $first = false; endforeach; ?>

        <?php if (empty($categories)): ?>
          <li class="text-muted" style="list-style:none;">No available items.</li>
        <?php endif; ?>
      </ul>
    </aside>

    <!-- MIDDLE: items per category -->
    <section class="menu-items-area">
      <?php if (empty($categories)): ?>
        <div class="content-card">
          <div class="content-card-header">
            <div class="left">
              <h3>No available menu items</h3>
              <p class="text-muted">Please check back later.</p>
            </div>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($categories as $c): ?>
          <?php
            $catId = $c['id'];
            $label = $c['label'];
            $slug  = $c['slug'];
          ?>
          <div class="menu-category-block" id="<?= h($slug) ?>">
            <div class="menu-category-header">
              <h3><?= h($label) ?></h3>
            </div>

            <?php foreach ($grouped[$catId] as $it): ?>
              <?php
                // Image URL from DB (e.g., "uploads/products/xxx.jpg")
                $imgRel = $it['image_url'] ?: '';
                // from /customer/menu.php to /uploads/... → prefix "../"
                $imgWeb = '../' . ltrim($imgRel, '/');
                // Use a placeholder if missing; also keep onerror fallback
                $placeholder = '../assets/images/placeholder.png';
              ?>
              <div class="menu-item-card">
                <div class="menu-item-imgwrap">
                  <img src="<?= h($imgWeb) ?>"
                       alt="<?= h($it['name']) ?>"
                       onerror="this.onerror=null;this.src='<?= h($placeholder) ?>';"/>
                </div>

                <div class="menu-item-main">
                  <div class="menu-item-toprow">
                    <h4 class="menu-item-name"><?= h($it['name']) ?></h4>
                    <div class="menu-item-price"><?= h(peso($it['price'])) ?></div>
                  </div>

                  <div class="menu-item-desc">
                    <?= h($it['description']) ?>
                  </div>

                  <div class="menu-item-actions">
                    <div class="qty-control">
                      <button class="qty-btn minus" type="button">-</button>
                      <div class="qty-value">1</div>
                      <button class="qty-btn plus" type="button">+</button>
                    </div>

                    <button class="add-btn" type="button" data-id="<?= (int)$it['id'] ?>">
                      <i class="bi bi-plus-circle-fill" style="font-size:14px;color:#000;"></i>
                      Add
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <!-- RIGHT: delivery/pickup + cart -->
    <?php include __DIR__ . '/includes/delivery-cart.php'; ?>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Address Modal + JS -->
  <?php include __DIR__ . '/includes/address-modal.php'; ?>

  <script>
  // ------- simple cart persistence in localStorage -------
  function getCart() {
    try { return JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; }
    catch(e){ return { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; }
  }
  function saveCart(cart) { localStorage.setItem('bslh_cart', JSON.stringify(cart)); }
  function clearCart() { localStorage.removeItem('bslh_cart'); }

  // Sidebar category active state
  document.querySelectorAll('.category-link').forEach(link => {
    link.addEventListener('click', function() {
      document.querySelectorAll('.category-link').forEach(el => el.classList.remove('active'));
      this.classList.add('active');
    });
  });
  document.addEventListener("DOMContentLoaded", function() {
    const categoryLinks = document.querySelectorAll('.category-link');
    const sections = document.querySelectorAll('.menu-category-block');

    categoryLinks.forEach(link => {
      link.addEventListener('click', function() {
        categoryLinks.forEach(el => el.classList.remove('active'));
        this.classList.add('active');
      });
    });

    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(section => {
        const sectionTop = section.offsetTop - 120;
        if (window.scrollY >= sectionTop) current = section.getAttribute('id');
      });
      categoryLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) link.classList.add('active');
      });
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    // ✅ Quantity controls
    function initQuantityControls() {
      document.querySelectorAll('.qty-control').forEach(control => {
        const minusBtn = control.querySelector('.qty-btn.minus');
        const plusBtn = control.querySelector('.qty-btn.plus');
        const qtyValue = control.querySelector('.qty-value');
        let quantity = parseInt(qtyValue.textContent) || 1;

        minusBtn.addEventListener('click', () => {
          if (quantity > 1) { quantity--; qtyValue.textContent = quantity; }
        });
        plusBtn.addEventListener('click', () => { quantity++; qtyValue.textContent = quantity; });
      });
    }

    // ✅ Add to cart + persist
    function initAddToCart() {
      const addButtons = document.querySelectorAll('.add-btn');
      const cartItemsEl = document.getElementById('cartItems');
      const cartSubtotalEl = document.getElementById('cartSubtotal');
      const cartTotalEl = document.getElementById('cartTotal');
      const deliveryFee = parseFloat((document.getElementById('cartDeliveryFee')?.textContent || '0').replace(/[₱,]/g, ''));

      // rebuild from storage
      const cart = getCart();
if (cart.items.length) {
  cart.items.forEach(it => appendOrUpdateLine(it.name, it.unitPrice, it.qty, { fromStorage: true }));
  updateCartTotals(); // okay to compute/sync totals; no qty changes happen here
}

      addButtons.forEach(button => {
        button.addEventListener('click', () => {
          const card = button.closest('.menu-item-card');
          const name = card.querySelector('.menu-item-name').textContent.trim();
          const price = parseFloat(card.querySelector('.menu-item-price').textContent.replace(/[₱,]/g, ''));
          const qty = parseInt(card.querySelector('.qty-value').textContent) || 1;

          appendOrUpdateLine(name, price, qty);
          updateCartTotals();
          card.querySelector('.qty-value').textContent = '1';
        });
      });

      function appendOrUpdateLine(name, unitPrice, addQty, opts = {}) {
  const fromStorage = !!opts.fromStorage;

  // Find existing DOM line by exact name (avoid `.includes`)
  let existingLine = null;
  cartItemsEl.querySelectorAll('.cart-line').forEach(line => {
    if (line.dataset.name === name) existingLine = line;
  });

  // Find existing cart entry
  const existingIdx = cart.items.findIndex(i => i.name === name);
  const currentCartQty = existingIdx > -1 ? Number(cart.items[existingIdx].qty || 0) : 0;

  // Target quantity to show/write
  // - when rebuilding from storage: the qty we pass in is already the exact qty to display
  // - on user add: increase existing cart qty by addQty
  const targetQty = fromStorage ? addQty : (currentCartQty + addQty);

  if (existingLine) {
    // Update DOM
    const nameElem  = existingLine.querySelector('.cart-line-name');
    const priceElem = existingLine.querySelector('.cart-line-price');
    nameElem.textContent  = `${targetQty}x ${name}`;
    priceElem.textContent = `₱${(unitPrice * targetQty).toFixed(2)}`;

    // Update cart ONLY on user add
    if (!fromStorage && existingIdx > -1) {
      cart.items[existingIdx].qty = targetQty;
      cart.items[existingIdx].unitPrice = unitPrice; // keep price in sync
    }
  } else {
    // Create DOM line
    const cartLine = document.createElement('div');
    cartLine.classList.add('cart-line');
    cartLine.dataset.name = name; // exact key
    const lineTotal = unitPrice * targetQty;
    cartLine.innerHTML = `
      <div class="cart-line-main">
        <div class="cart-line-name">${targetQty}x ${name}</div>
      </div>
      <div class="cart-line-right">
        <div class="cart-line-price">₱${lineTotal.toFixed(2)}</div>
        <button class="cart-delete-btn" type="button" title="Remove">
          <i class="bi bi-trash" style="color:#d00; font-size:14px;"></i>
        </button>
      </div>
    `;
    cartLine.querySelector('.cart-delete-btn').addEventListener('click', () => {
      cartLine.remove();
      const idx = cart.items.findIndex(i => i.name === name);
      if (idx > -1) cart.items.splice(idx, 1);
      updateCartTotals();
    });
    cartItemsEl.appendChild(cartLine);

    // Update cart ONLY on user add
    if (!fromStorage) {
      if (existingIdx > -1) {
        cart.items[existingIdx].qty = targetQty;
        cart.items[existingIdx].unitPrice = unitPrice;
      } else {
        cart.items.push({ name, qty: targetQty, unitPrice });
      }
    }
  }
}


      function updateCartTotals() {
        let subtotal = 0;
        cart.items.forEach(i => { subtotal += i.qty * i.unitPrice; });
        cart.subtotal = subtotal;
        cart.deliveryFee = deliveryFee || 0;
        cart.total = subtotal + cart.deliveryFee;

        if (cartSubtotalEl) cartSubtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
        if (cartTotalEl) cartTotalEl.textContent = `₱${cart.total.toFixed(2)}`;

        saveCart(cart);
      }

      const checkoutBtn = document.getElementById('checkoutBtn');
      if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
          const hasItems = (getCart().items || []).length > 0;
          if (!hasItems) return alert('Your cart is empty.');
          window.location.href = "/food-ordering-system_BSLH/customer/checkout.php";
        });
      }
    }

    initQuantityControls();
    initAddToCart();
  });
  </script>
</body>
</html>
