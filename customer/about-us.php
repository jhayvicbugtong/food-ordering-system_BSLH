<?php require_once __DIR__ . '/../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>About Us | Bente Sais Lomi House - Our Story & Heritage</title>
  <meta name="description" content="Discover the story behind Bente Sais Lomi House - from humble beginnings to becoming Batangas' favorite comfort food destination. Learn about our passion for authentic flavors."/>
  <meta name="robots" content="index,follow"/>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  
  <style>
    :root {
      --accent: #5cfa63;
      --accent-light: #7cf484;
      --dark: #1a1a1a;
      --dark-light: #2d2d2d;
      --text-light: #e9ecef;
      --text-muted: #6c757d;
    }

    /* Modern Hero About Section */
    .hero-about {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 120px 0 80px;
      position: relative;
      overflow: hidden;
    }

    .hero-about::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 10% 20%, rgba(92, 250, 99, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 90% 80%, rgba(92, 250, 99, 0.03) 0%, transparent 50%);
      pointer-events: none;
    }

    .section-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      align-items: center;
      gap: 60px;
      position: relative;
      z-index: 2;
    }

    .hero-left {
      flex: 1;
    }

    .hero-left h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .hero-left h1 span {
      color: var(--accent);
      display: block;
      margin-top: 0.5rem;
    }

    .hero-left p {
      font-size: 1.2rem;
      line-height: 1.7;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    .hero-right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .hero-img {
      max-width: 100%;
      height: auto;
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      transform: perspective(1000px) rotateY(-5deg);
      transition: transform 0.3s ease;
    }

    .hero-img:hover {
      transform: perspective(1000px) rotateY(-5deg) scale(1.02);
    }

    /* Story Section */
    .story-section {
      padding: 100px 0;
      background: #fff;
    }

    .story-left {
      flex: 1;
      position: relative;
    }

    .story-left img {
      width: 100%;
      height: 500px;
      object-fit: cover;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .story-left img:hover {
      transform: scale(1.02);
    }

    .story-right {
      flex: 1;
      padding: 0 40px;
    }

    .story-right h2 {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--accent);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }

    .story-right h3 {
      font-size: clamp(1.8rem, 3vw, 2.2rem);
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .story-right p {
      font-size: 1.1rem;
      line-height: 1.7;
      color: var(--text-muted);
      margin-bottom: 1.5rem;
    }

    .emphasis-line {
      background: linear-gradient(135deg, rgba(92, 250, 99, 0.1) 0%, rgba(92, 250, 99, 0.05) 100%);
      border-left: 4px solid var(--accent);
      padding: 25px;
      border-radius: 0 12px 12px 0;
      font-size: 1.2rem;
      font-weight: 600;
      color: #343a40;
      line-height: 1.5;
      margin-top: 2rem;
    }

    /* Quality Section */
    .quality-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .quality-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      gap: 60px;
      align-items: center;
    }

    .quality-col {
      display: flex;
      flex-direction: column;
      gap: 40px;
    }

    .quality-block {
      background: #fff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease;
      border-left: 4px solid var(--accent);
    }

    .quality-block:hover {
      transform: translateY(-5px);
    }

    .quality-block h4 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .quality-block p {
      color: var(--text-muted);
      line-height: 1.6;
    }

    .quality-img-wrap {
      position: relative;
    }

    .quality-img-wrap img {
      width: 300px;
      height: 400px;
      object-fit: cover;
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      transition: transform 0.3s ease;
    }

    .quality-img-wrap:hover img {
      transform: scale(1.05);
    }

    /* CTA Section */
    .cta-section {
      padding: 100px 0;
      background: #fff;
    }

    .cta-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      gap: 60px;
      align-items: center;
    }

    .cta-left {
      flex: 1;
    }

    .cta-photos {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .cta-photo-card {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .cta-photo-card:hover {
      transform: translateY(-10px);
    }

    .cta-photo-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .cta-photo-card:hover img {
      transform: scale(1.1);
    }

    .cta-right {
      flex: 1;
      padding: 0 20px;
    }

    .cta-right h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .cta-right p {
      font-size: 1.2rem;
      line-height: 1.6;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    .cta-btn {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      padding: 16px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(92, 250, 99, 0.3);
    }

    .cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(92, 250, 99, 0.4);
    }

    /* Stats Section */
    .stats-section {
      padding: 80px 0;
      background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
      color: #fff;
    }

    .stats-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      text-align: center;
    }

    .stat-card {
      padding: 40px 20px;
    }

    .stat-number {
      font-size: 3rem;
      font-weight: 700;
      color: var(--accent);
      margin-bottom: 1rem;
      display: block;
    }

    .stat-label {
      font-size: 1.1rem;
      color: #adb5bd;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Values Section - Updated for single row */
    .values-section {
      padding: 100px 0;
      background: #fff;
    }

    .values-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      text-align: center;
    }

    .values-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      margin-top: 60px;
    }

    .value-card {
      padding: 40px 25px;
      border-radius: 20px;
      background: #f8f9fa;
      transition: all 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .value-card:hover {
      transform: translateY(-10px);
      background: #fff;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    }

    .value-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: #000;
      transition: transform 0.3s ease;
    }

    .value-card:hover .value-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .value-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .value-desc {
      color: var(--text-muted);
      line-height: 1.6;
      font-size: 0.95rem;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
      .section-inner,
      .cta-inner {
        flex-direction: column;
        text-align: center;
        gap: 40px;
      }

      .quality-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .story-right {
        padding: 0 20px;
      }

      .cta-photos {
        grid-template-columns: 1fr;
      }

      .hero-img {
        transform: none;
      }

      .hero-img:hover {
        transform: scale(1.02);
      }

      .values-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
      }
    }

    @media (max-width: 768px) {
      .hero-about,
      .story-section,
      .quality-section,
      .cta-section,
      .stats-section,
      .values-section {
        padding: 60px 0;
      }

      .section-inner {
        padding: 0 15px;
      }

      .values-grid {
        grid-template-columns: 1fr;
        gap: 25px;
      }

      .stats-inner {
        grid-template-columns: repeat(2, 1fr);
      }

      .quality-img-wrap img {
        width: 250px;
        height: 350px;
      }

      .cta-photo-card img {
        height: 200px;
      }

      .value-card {
        padding: 30px 20px;
      }

      .value-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .stats-inner {
        grid-template-columns: 1fr;
      }

      .stat-number {
        font-size: 2.5rem;
      }

      .quality-block,
      .value-card {
        padding: 25px 20px;
      }

      .cta-btn {
        padding: 14px 28px;
        font-size: 1rem;
      }

      .emphasis-line {
        padding: 20px;
        font-size: 1.1rem;
      }

      .values-grid {
        gap: 20px;
      }
    }
  </style>
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
          comfort food that's warm, heavy, and full of flavor — the kind of meal
          you want when you're pagod, gutom, or with the barkada.
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
             alt="Bente Sais Lomi House Kitchen - Preparing authentic Batangas comfort food">
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="stats-inner">
      <div class="stat-card">
        <span class="stat-number">26+</span>
        <div class="stat-label">Years of Service</div>
      </div>
      <div class="stat-card">
        <span class="stat-number">50K+</span>
        <div class="stat-label">Happy Customers</div>
      </div>
      <div class="stat-card">
        <span class="stat-number">3</span>
        <div class="stat-label">Generations</div>
      </div>
      <div class="stat-card">
        <span class="stat-number">100%</span>
        <div class="stat-label">Fresh Ingredients</div>
      </div>
    </div>
  </section>

  <section class="story-section">
    <div class="section-inner" style="flex-direction: row-reverse;">
      <div class="story-left">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan2.jpg?v=0" alt="Bente Sais Lomi House kitchen preparation and cooking process">
      </div>

      <div class="story-right">
        <h2>Our Humble Beginning</h2>
        <h3>
          Started as a simple lomi spot —
          now a go-to place for silog, pancit, and kwentuhan.
        </h3>

        <p>
          We didn't start as a fancy restaurant.
          We started because people kept saying:
          "Saan masarap mag-lomi dito?"
          That's still our standard. If it's not something we'd proudly serve
          to a jeepney driver on break and a night-shift nurse on the same table,
          we don't serve it.
        </p>

        <p>
          Our menu stayed faithful to Filipino comfort:
          thick lomi noodles with toppings,
          garlic rice with egg and ulam,
          pancit that tastes like family gatherings.
        </p>

        <div class="emphasis-line">
          "Busog, sulit, masarap" isn't just a tagline.
          It's the rule.
        </div>
      </div>
    </div>
  </section>

  <!-- Values Section - Now in one row -->
  <section class="values-section">
    <div class="values-inner">
      <h2 style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 1rem; color: #343a40;">
        Our Core Values
      </h2>
      <p style="font-size: 1.2rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 3rem;">
        These principles guide everything we do, from recipe development to customer service.
      </p>
      
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-heart-fill"></i>
          </div>
          <div class="value-title">Authentic Flavors</div>
          <div class="value-desc">
            We preserve traditional Batangas recipes while maintaining the authentic 
            taste that our customers love and remember.
          </div>
        </div>

        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="value-title">Community First</div>
          <div class="value-desc">
            We're more than a restaurant - we're part of the community. 
            We support local suppliers and create spaces for people to connect.
          </div>
        </div>

        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="value-title">Quality Commitment</div>
          <div class="value-desc">
            Every dish is prepared with the highest quality ingredients 
            and attention to detail, ensuring consistency in every bite.
          </div>
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
            No weird shortcuts, no fake thickener overload. Just honest, 
            freshly prepared comfort food.
          </p>
        </div>
        <div class="quality-block">
          <h4>Family Recipes</h4>
          <p>
            Our recipes have been passed down through generations, 
            preserving the authentic Batangas flavors that make our 
            lomi and other dishes truly special.
          </p>
        </div>
      </div>

      <div class="quality-col quality-img-wrap">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan.jpg?v=0"
             alt="Signature Bente Sais Lomi Bowl with fresh ingredients">
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
        <div class="quality-block">
          <h4>Customer Love</h4>
          <p>
            We treat every customer like family. Your satisfaction 
            is our priority, and we're always here to make your 
            dining experience memorable.
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
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan_AFouckhG.jpg?v=0" alt="Bente Sais Silog Meal - Perfect for any time of day">
          </div>
          <div class="cta-photo-card">
            <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/vegan3.jpg?v=0" alt="Group order party tray - Perfect for gatherings">
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
          <i class="bi bi-bag-check-fill"></i>
          Order online
        </a>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>