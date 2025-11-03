<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Bente Sais Lomi House</title>
  <meta name="description" content="Comfort food, done right. Hot lomi, silog meals, and pancit — available for delivery or pickup."/>
  <meta name="robots" content="index,follow"/>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <!-- Our landing styles -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
</head>

<body>

  <!-- NAV / HEADER -->
  <?php include __DIR__ . '/customer/includes/header.php'; ?>

  <!-- HERO -->
  <section class="hero-section">
    <div class="section-inner">
      <div class="hero-left">
        <h1>
          Hot, filling,
          <span>LOMI!</span>
        </h1>
        <h4>
          Enjoy our Batangas-style lomi, silog meals, and pancit —
          available for delivery or pickup, Monday to Sunday.
        </h4>

        <a class="hero-cta" href="/food-ordering-system_BSLH/customer/index.php">
          Order online 
        </a>
      </div>

      <div class="hero-right">
        <img class="hero-img"
             src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/hero.png?v=0"
             alt="Featured Dish">
      </div>
    </div>
  </section>

  <!-- ABOUT / FIT SECTION -->
  <section class="about-section">
    <div class="section-inner" style="flex-direction: row-reverse;">
      <div class="about-img-col">
        <div class="about-img-wrap">
          <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan2.jpg?v=0" alt="Food Image">
        </div>
      </div>

      <div class="about-text-col">
        <h4>Looking for something delicious and sulit?</h4>
        <h2>Welcome to Bente Sais Lomi House — here you’ll always eat something tasty</h2>
        <p>
          We’re all about comfort food you actually crave. Think rich lomi broth,
          garlic rice, crispy toppings, silog meals, and pancit for sharing.
          Everything is cooked fresh, served fast, and priced for barkada.
        </p>
        <a class="about-cta" href="/food-ordering-system_BSLH/customer/index.php">Order now</a>
      </div>
    </div>
  </section>

  <!-- FEATURES SECTION -->
  <section class="features-section">
    <div class="section-inner" style="flex-direction: column; text-align:center;">
      <div class="features-grid">

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-cup-hot-fill"></i></div>
          <div class="feature-title">Mainit na sabaw</div>
          <div class="feature-desc">
            Rich, flavorful lomi broth made fresh. Comfort in a bowl.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-emoji-sunglasses-fill"></i></div>
          <div class="feature-title">Chill & Tambay</div>
          <div class="feature-desc">
            Sit, eat, kwento. Our goal is that you forget your stress for a bit.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-egg-fried"></i></div>
          <div class="feature-title">Silog Favorites</div>
          <div class="feature-desc">
            Garlic rice + egg + ulam. The Filipino breakfast
            you can eat any time of day.
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- MIDDLE SPLIT SECTION (like the avocado "healthy / desserts / coffee / fresh") -->
  <section class="middle-split-section">
    <div class="middle-split-inner">
      <div class="col-split split-text-block">
        <div class="split-title">Sulit at busog</div>
        <p>
          Lomi that actually fills you up. Generous toppings,
          thick noodles, flavorful broth — this is the kind of bowl
          you brag about to friends.
        </p>
      </div>

      <div class="col-split middle-img-wrap">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan.jpg?v=0"
             alt="Signature Dish">
      </div>

      <div class="col-split split-text-block">
        <div class="split-title">Always fresh</div>
        <p>
          We cook in small batches, source fresh ingredients,
          and keep it honest. Walang pa-star, just real food done right.
        </p>
      </div>
    </div>
  </section>

  <!-- CATERING / BULK / MEAL PLAN -->
  <section class="cta-section">
    <div class="cta-block">

      <div class="cta-img-col">
        <div class="cta-img-grid">
          <div class="cta-img-card">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan_AFouckhG.jpg?v=0" alt="Combo A">
          </div>
          <div class="cta-img-card">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan3.jpg?v=0" alt="Combo B">
          </div>
        </div>
      </div>

      <div class="cta-text-col">
        <h2>Need food for pickup or delivery?</h2>
        <h4>
          We also accept advance orders for group meals, catering-style trays,
          and silog packs. Message us, and we'll prep based on your schedule.
        </h4>
        <div class="cta-center-btn">
          <a class="cta-btn" href="/food-ordering-system_BSLH/customer/index.php">
            Order online
          </a>
        </div>
      </div>

    </div>
  </section>

  <!-- FOOTER -->
  <?php include __DIR__ . '/customer/includes/footer.php'; ?>

</body>
</html>
