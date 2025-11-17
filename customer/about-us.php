<?php require_once __DIR__ . '/../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>About Us | Bente Sais Lomi House</title>
  <meta name="description" content="The story of Bente Sais Lomi House — where we started, what we cook, and why people keep coming back."/>
  <meta name="robots" content="index,follow"/>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="hero-about">
    <div class="section-inner">
      <div class="hero-left">
        <h1>
          We cook the kind of food
          <span>you grew up with.</span>
        </h1>

        <p>
          Bente Sais Lomi House is built on something simple:
          comfort food that’s warm, heavy, and full of flavor — the kind of meal
          you want when you’re pagod, gutom, or with the barkada.
          From our Batangas-style lomi to our silog meals and pancit trays,
          everything is cooked fresh and served fast.
        </p>

        <p style="font-size:13px;color:#adb5bd;line-height:1.4;">
          Serving the community, one bowl at a time.
        </p>
      </div>

      <div class="hero-right">
        <img class="hero-img"
             src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/person.jpg?v=0"
             alt="Owner / kitchen">
      </div>
    </div>
  </section>

  <section class="story-section">
    <div class="section-inner" style="flex-direction: row-reverse;">
      <div class="story-left">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan2.jpg?v=0" alt="Kitchen / prep">
      </div>

      <div class="story-right">
        <h2>Our Story</h2>
        <h3>
          Started as a simple lomi spot —
          now a go-to place for silog, pancit, and kwentuhan.
        </h3>

        <p>
          We didn’t start as a fancy restaurant.
          We started because people kept saying:
          “Saan masarap mag-lomi dito?”
          That’s still our standard. If it’s not something we’d proudly serve
          to a jeepney driver on break and a night-shift nurse on the same table,
          we don’t serve it.
        </p>

        <p>
          Our menu stayed faithful to Filipino comfort:
          thick lomi noodles with toppings,
          garlic rice with egg and ulam,
          pancit that tastes like family gatherings.
        </p>

        <div class="emphasis-line">
          “Busog, sulit, masarap” isn’t just a tagline.
          It’s the rule.
        </div>
      </div>
    </div>
  </section>

  <section class="quality-section">
    <div class="quality-inner">

      <div class="quality-col">
        <div class="quality-block">
          <h4>Freshly Prepared</h4>
          <p>
            We cook in batches all day so your lomi is hot and not reheated.
            No weird shortcuts, no fake thickener overload.
          </p>
        </div>
      </div>

      <div class="quality-col quality-img-wrap">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan.jpg?v=0"
             alt="Signature bowl">
      </div>

      <div class="quality-col">
        <div class="quality-block">
          <h4>Local Flavor First</h4>
          <p>
            We source from nearby suppliers whenever we can.
            We keep it affordable because food should fill you,
            not empty your wallet.
          </p>
        </div>
      </div>

    </div>
  </section>


  <section class="cta-section">
    <div class="cta-inner">
      <div class="cta-left">
        <div class="cta-photos">
          <div class="cta-photo-card">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan_AFouckhG.jpg?v=0" alt="Silog meal">
          </div>
          <div class="cta-photo-card">
            <img src="httpsG://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan3.jpg?v=0" alt="Group order / tray">
          </div>
        </div>
      </div>

      <div class="cta-right">
        <h2>Want to order for pickup, delivery, or a group?</h2>
        <p>
          We accept advance orders for delivery, and we prep party trays /
          silog sets for sharing. Message us, or order directly from the site.
        </p>

        <a class="cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          Order online
        </a>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>