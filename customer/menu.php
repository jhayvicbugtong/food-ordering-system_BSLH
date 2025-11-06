<?php
// TEMPORARY SAMPLE MENU DATA.
// Later: replace this array with a SELECT query from your menu table.
$menuData = [
  "Lomi Bowls" => [
    [
      "name" => "Original Bente Sais Lomi",
      "desc" => "Thick noodles, rich broth, egg, chicharon, and crispy toppings.",
      "price" => 89.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan.jpg?v=0"
    ],
    [
      "name" => "Extra Crispy Lomi",
      "desc" => "Double crunch toppings — pang-lamig, pang-puyat.",
      "price" => 99.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan2.jpg?v=0"
    ]
  ],
  "Silog Meals" => [
    [
      "name" => "Tapsilog",
      "desc" => "Garlic rice, fried egg, tapa. Classic, walang mintis.",
      "price" => 110.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan3.jpg?v=0"
    ],
    [
      "name" => "Longsilog",
      "desc" => "Sweet longganisa + egg + sinangag.",
      "price" => 100.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/hero.png?v=0"
    ]
  ],
  "Party Trays" => [
    [
      "name" => "Pancit Tray (Good for 6-8)",
      "desc" => "Perfect for handaan, barkada, inuman, overtime sa office.",
      "price" => 480.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan_AFouckhG.jpg?v=0"
    ]
  ],
  "Drinks" => [
    [
      "name" => "Iced Gulaman",
      "desc" => "Matamis, malamig, pambanlaw after lomi.",
      "price" => 35.00,
      "img" => "https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/person.jpg?v=0"
    ]
  ],
];
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

  <!-- Shared CSS -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
</head>
<body class="menu-page">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="menu-layout">
  <!-- LEFT: categories list -->
  <aside class="menu-categories">
    <h2>Menu</h2>
    <ul class="category-list">
      <?php $first = true; foreach ($menuData as $catName => $items): ?>
        <?php $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$catName)); ?>
        <li>
          <a class="category-link <?= $first ? 'active' : '' ?>" href="#<?= $slug ?>">
            <?= htmlspecialchars($catName) ?>
          </a>
        </li>
      <?php $first = false; endforeach; ?>
    </ul>
  </aside>

  <!-- MIDDLE: items in each category -->
  <section class="menu-items-area">
    <?php foreach ($menuData as $catName => $items): ?>
      <?php $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$catName)); ?>
      <div class="menu-category-block" id="<?= $slug ?>">
        <div class="menu-category-header">
          <h3><?= htmlspecialchars($catName) ?></h3>
        </div>

        <?php foreach ($items as $item): ?>
          <div class="menu-item-card">
            <div class="menu-item-imgwrap">
              <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"/>
            </div>

            <div class="menu-item-main">
              <div class="menu-item-toprow">
                <h4 class="menu-item-name"><?= htmlspecialchars($item['name']) ?></h4>
                <div class="menu-item-price">
                  ₱<?= number_format($item['price'], 2) ?>
                </div>
              </div>

              <div class="menu-item-desc">
                <?= htmlspecialchars($item['desc']) ?>
              </div>

              <div class="menu-item-actions">
                <div class="qty-control">
                  <button class="qty-btn minus" type="button">-</button>
                  <div class="qty-value">1</div>
                  <button class="qty-btn plus" type="button">+</button>
                </div>

                <button class="add-btn" type="button">
                  <i class="bi bi-plus-circle-fill" style="font-size:14px;color:#000;"></i>
                  Add
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- RIGHT: delivery / pickup + cart -->
  <?php include __DIR__ . '/includes/delivery-cart.php'; ?>
</main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Address Modal + JS -->
  <?php include __DIR__ . '/includes/address-modal.php'; ?>

  <script>

          function getCart() {
  try { return JSON.parse(localStorage.getItem('bslh_cart')) || { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; }
  catch(e){ return { items: [], subtotal: 0, deliveryFee: 0, total: 0 }; }
}
function saveCart(cart) {
  localStorage.setItem('bslh_cart', JSON.stringify(cart));
}
function clearCart() {
  localStorage.removeItem('bslh_cart');
}


    document.querySelectorAll('.category-link').forEach(link => {
  link.addEventListener('click', function() {
    // Remove 'active' class from all
    document.querySelectorAll('.category-link').forEach(el => el.classList.remove('active'));
    // Add 'active' to clicked one
    this.classList.add('active');
  });
});
document.addEventListener("DOMContentLoaded", function() {
  const categoryLinks = document.querySelectorAll('.category-link');
  const sections = document.querySelectorAll('.menu-category-block');

  // 🟩 CLICK: Set clicked link as active
  categoryLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      categoryLinks.forEach(el => el.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // 🟩 SCROLL: Detect which section is in view
  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 120; // adjust offset for your layout
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

  // 🟩 OPTIONAL: HOVER over items to highlight their category
  sections.forEach(section => {
    section.addEventListener('mouseenter', () => {
      categoryLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + section.id) {
          link.classList.add('active');
        }
      });
    });
  });
});

document.addEventListener('DOMContentLoaded', function() {

  // ✅ Quantity controls
  function initQuantityControls() {
    const qtyControls = document.querySelectorAll('.qty-control');

    qtyControls.forEach(control => {
      const minusBtn = control.querySelector('.qty-btn.minus');
      const plusBtn = control.querySelector('.qty-btn.plus');
      const qtyValue = control.querySelector('.qty-value');
      let quantity = parseInt(qtyValue.textContent);

      minusBtn.addEventListener('click', () => {
        if (quantity > 1) {
          quantity--;
          qtyValue.textContent = quantity;
        }
      });

      plusBtn.addEventListener('click', () => {
        quantity++;
        qtyValue.textContent = quantity;
      });
    });
  }

  // ✅ Add to cart, update qty if existing, and delete
  function initAddToCart() {
  const addButtons = document.querySelectorAll('.add-btn');
  const cartItemsEl = document.getElementById('cartItems');
  const cartSubtotalEl = document.getElementById('cartSubtotal');
  const cartTotalEl = document.getElementById('cartTotal');
  const deliveryFee = parseFloat(document.getElementById('cartDeliveryFee').textContent.replace(/[₱,]/g, ''));

  // load persisted cart -> rebuild the mini-cart UI on page load
  const cart = getCart();
  if (cart.items.length) {
    cart.items.forEach(it => appendOrUpdateLine(it.name, it.unitPrice, it.qty));
    updateCartTotals();
  }

  addButtons.forEach(button => {
    button.addEventListener('click', () => {
      const menuItem = button.closest('.menu-item-card');
      const name = menuItem.querySelector('.menu-item-name').textContent.trim();
      const price = parseFloat(menuItem.querySelector('.menu-item-price').textContent.replace(/[₱,]/g, ''));
      const qty = parseInt(menuItem.querySelector('.qty-value').textContent);

      appendOrUpdateLine(name, price, qty);
      updateCartTotals();

      // reset the displayed qty on the menu card back to 1 (optional UX)
      menuItem.querySelector('.qty-value').textContent = '1';
    });
  });

  function appendOrUpdateLine(name, unitPrice, addQty) {
    // update DOM
    const existingLine = Array.from(cartItemsEl.querySelectorAll('.cart-line'))
      .find(line => line.querySelector('.cart-line-name').textContent.includes(name));

    // update cart object
    const existingIdx = cart.items.findIndex(i => i.name === name);

    if (existingLine) {
      const nameElem = existingLine.querySelector('.cart-line-name');
      const priceElem = existingLine.querySelector('.cart-line-price');

      const currentQty = parseInt(nameElem.textContent.match(/^(\d+)x/)?.[1] || 1);
      const newQty = currentQty + addQty;
      const newTotal = unitPrice * newQty;

      nameElem.textContent = `${newQty}x ${name}`;
      priceElem.textContent = `₱${newTotal.toFixed(2)}`;

      cart.items[existingIdx] = { name, qty: newQty, unitPrice };
    } else {
      const cartLine = document.createElement('div');
      cartLine.classList.add('cart-line');
      const lineTotal = unitPrice * addQty;
      cartLine.innerHTML = `
        <div class="cart-line-main">
          <div class="cart-line-name">${addQty}x ${name}</div>
        </div>
        <div class="cart-line-right">
          <div class="cart-line-price">₱${lineTotal.toFixed(2)}</div>
          <button class="cart-delete-btn" type="button" title="Remove">
            <i class="bi bi-trash" style="color:#d00; font-size:14px;"></i>
          </button>
        </div>
      `;
      cartLine.querySelector('.cart-delete-btn').addEventListener('click', () => {
        // remove from DOM
        cartLine.remove();
        // remove from cart object
        const idx = cart.items.findIndex(i => i.name === name);
        if (idx > -1) cart.items.splice(idx, 1);
        updateCartTotals();
      });
      cartItemsEl.appendChild(cartLine);

      if (existingIdx > -1) {
        cart.items[existingIdx].qty += addQty;
      } else {
        cart.items.push({ name, qty: addQty, unitPrice });
      }
    }
  }

  function updateCartTotals() {
    let subtotal = 0;
    cart.items.forEach(i => { subtotal += i.qty * i.unitPrice; });

    cart.subtotal = subtotal;
    cart.deliveryFee = deliveryFee || 0;
    cart.total = subtotal + cart.deliveryFee;

    cartSubtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
    cartTotalEl.textContent = `₱${cart.total.toFixed(2)}`;

    saveCart(cart);
  }

  // If your delivery-cart has a "Checkout" button, give it id="checkoutBtn"
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      // (optional) validate cart not empty
      const hasItems = (getCart().items || []).length > 0;
      if (!hasItems) return alert('Your cart is empty.');

      // go to checkout
      window.location.href = "/food-ordering-system_BSLH/customer/checkout.php";
    });
  }
}

  // Initialize everything
  initQuantityControls();
  initAddToCart();
});
</script>
</body>
</html>
