<?php require_once __DIR__ . '/../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Gallery | Bente Sais Lomi House</title>
  <meta name="description" content="A look at our lomi bowls, silog meals, pancit trays, and more from Bente Sais Lomi House."/>
  <meta name="robots" content="index,follow"/>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="gallery-section">
    <div class="gallery-inner">

      <div class="gallery-header">
        <h1>Our Food, Our Pride</h1>
        <p>
          Bowls that steam, plates that crunch, trays that feed the whole barkada.
          This is what we serve every day at Bente Sais Lomi House.
        </p>
      </div>

      <div class="gallery-grid">

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan.jpg?v=0"
                 alt="Signature Lomi Bowl">
          </div>
          <div class="gallery-card-body">
            <h3>Signature Lomi</h3>
            <p>Thick noodles, rich broth, crispy toppings. Our bestseller.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan2.jpg?v=0"
                 alt="Silog Combo">
          </div>
          <div class="gallery-card-body">
            <h3>Silog Meals</h3>
            <p>Garlic rice + egg + ulam. Pang-breakfast, pang-dinner, pang-lahat.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan3.jpg?v=0"
                 alt="Pancit Tray for Sharing">
          </div>
          <div class="gallery-card-body">
            <h3>Party Trays</h3>
            <p>Pancit + toppings. Good for sharing, perfect for handaan.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/person.jpg?v=0"
                 alt="Kitchen / Prep / Garnish">
          </div>
          <div class="gallery-card-body">
            <h3>Freshly Prepared</h3>
            <p>No stale pans, no reheat drama. We prep throughout the day.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan_AFouckhG.jpg?v=0"
                 alt="Add-ons / Toppings / Extras">
          </div>
          <div class="gallery-card-body">
            <h3>Toppings & Add-ons</h3>
            <p>Crunch, egg, chicharon, special sauce. Customize the bowl.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/hero.png?v=0"
                 alt="Takeout and Delivery Packaging">
          </div>
          <div class="gallery-card-body">
            <h3>Ready for Delivery</h3>
            <p>Pickup or delivery? Your call. We pack it tight and hot.</p>
          </div>
        </div>

      </div><div class="gallery-cta-block">
        <h2>Craving something you saw here?</h2>
        <p>
          You can order bowls, silog, pancit trays, and more online.
          We’ll prep it fresh for pickup or delivery.
        </p>
        <a class="gallery-cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          Order online
        </a>
      </div>

    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>