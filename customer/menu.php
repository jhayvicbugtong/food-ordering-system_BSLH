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
            <a class="<?= $first ? 'active' : '' ?>" href="#<?= $slug ?>">
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
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTotal = document.getElementById('cartTotal');
    const deliveryFee = parseFloat(document.getElementById('cartDeliveryFee').textContent.replace(/[₱,]/g, ''));

    addButtons.forEach(button => {
      button.addEventListener('click', () => {
        const menuItem = button.closest('.menu-item-card');
        const name = menuItem.querySelector('.menu-item-name').textContent.trim();
        const price = parseFloat(menuItem.querySelector('.menu-item-price').textContent.replace(/[₱,]/g, ''));
        const qty = parseInt(menuItem.querySelector('.qty-value').textContent);
        const totalLine = price * qty;

        // Check if item already exists in cart
        const existingLine = Array.from(cartItems.querySelectorAll('.cart-line'))
          .find(line => line.querySelector('.cart-line-name').textContent.includes(name));

        if (existingLine) {
          // ✅ Update existing quantity and price
          const nameElem = existingLine.querySelector('.cart-line-name');
          const priceElem = existingLine.querySelector('.cart-line-price');

          // Extract current quantity
          const currentQty = parseInt(nameElem.textContent.match(/^(\d+)x/)?.[1] || 1);
          const newQty = currentQty + qty;
          const newTotal = price * newQty;

          nameElem.textContent = `${newQty}x ${name}`;
          priceElem.textContent = `₱${newTotal.toFixed(2)}`;
        } else {
          // ✅ Create new cart line
          const cartLine = document.createElement('div');
          cartLine.classList.add('cart-line');
          cartLine.innerHTML = `
            <div class="cart-line-main">
              <div class="cart-line-name">${qty}x ${name}</div>
            </div>
            <div class="cart-line-right">
              <div class="cart-line-price">₱${totalLine.toFixed(2)}</div>
              <button class="cart-delete-btn" type="button" title="Remove">
                <i class="bi bi-trash" style="color:#d00; font-size:14px;"></i>
              </button>
            </div>
          `;

          // Attach delete event
          cartLine.querySelector('.cart-delete-btn').addEventListener('click', () => {
            cartLine.remove();
            updateCartTotals();
          });

          cartItems.appendChild(cartLine);
        }

        // Update totals
        updateCartTotals();
      });
    });

    // ✅ Update subtotal + total
    function updateCartTotals() {
      let subtotal = 0;
      const prices = cartItems.querySelectorAll('.cart-line-price');
      prices.forEach(p => {
        subtotal += parseFloat(p.textContent.replace(/[₱,]/g, ''));
      });

      cartSubtotal.textContent = `₱${subtotal.toFixed(2)}`;
      cartTotal.textContent = `₱${(subtotal + deliveryFee).toFixed(2)}`;
    }
  }

  // Initialize everything
  initQuantityControls();
  initAddToCart();
});
</script>
</body>
</html>
