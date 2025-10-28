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
                    <button class="qty-btn" type="button">-</button>
                    <div class="qty-value">1</div>
                    <button class="qty-btn" type="button">+</button>
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

</body>
</html>
