<?php require_once __DIR__ . '/../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>About Us | Bente Sais Lomi House</title>
  <meta name="description" content="Discover the story behind Bente Sais Lomi House - from humble beginnings to becoming Batangas' favorite comfort food destination."/>
  <meta name="robots" content="index,follow"/>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  
  <style>
    :root {
      --accent: #5cfa63;
      --accent-hover: #4ade80;
      --dark: #1a1a1a;
      --text-muted: #6c757d;
    }
    
    body {
        background-color: #f8f9fa;
        color: var(--dark);
        font-family: 'Inter', sans-serif;
    }

    /* --- HERO SECTION --- */
    .hero-about {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 100px 0 80px;
      position: relative;
      overflow: hidden;
    }
    .hero-about::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
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

    .hero-left { flex: 1; }
    .hero-left h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      color: var(--dark);
      letter-spacing: -1px;
    }
    .hero-left h1 span { color: #5cfa63; display: block; }
    .hero-left p {
      font-size: 1.2rem;
      line-height: 1.6;
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
      border-radius: 24px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
      transform: perspective(1000px) rotateY(-5deg);
      transition: transform 0.3s ease;
    }
    .hero-img:hover {
      transform: perspective(1000px) rotateY(-2deg) scale(1.01);
    }

    /* --- STATS SECTION --- */
    .stats-section {
      padding: 60px 0;
      background: var(--dark); /* Solid Dark */
      color: #fff;
    }
    .stats-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 40px;
      text-align: center;
    }
    .stat-card { padding: 20px; }
    .stat-number {
      font-size: 3rem;
      font-weight: 800;
      color: var(--accent);
      margin-bottom: 0.5rem;
      display: block;
      line-height: 1;
    }
    .stat-label {
      font-size: 1rem;
      color: #adb5bd;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
    }

    /* --- STORY SECTION --- */
    .story-section {
      padding: 100px 0;
      background: #fff;
    }
    .story-left { flex: 1; }
    .story-left img {
      width: 100%;
      height: 500px;
      object-fit: cover;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    }
    .story-right { flex: 1; padding: 0 40px; }
    
    .story-right h2 {
      font-size: 1rem;
      font-weight: 700;
      color: #16a34a;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }
    .story-right h3 {
      font-size: clamp(2rem, 3vw, 2.5rem);
      font-weight: 800;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: var(--dark);
    }
    .story-right p {
      font-size: 1.1rem;
      line-height: 1.7;
      color: var(--text-muted);
      margin-bottom: 1.5rem;
    }
    
    .emphasis-line {
      background: #f0fdf4;
      border-left: 4px solid var(--accent);
      padding: 25px;
      border-radius: 0 12px 12px 0;
      font-size: 1.15rem;
      font-weight: 600;
      color: #14532d;
      line-height: 1.5;
      margin-top: 2rem;
    }

    /* --- VALUES SECTION --- */
    .values-section {
      padding: 100px 0;
      background: #f8f9fa;
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
      margin-top: 50px;
    }
    
    .value-card {
      padding: 40px 30px;
      border-radius: 24px;
      background: #fff;
      border: 1px solid rgba(0,0,0,0.03);
      box-shadow: 0 4px 20px rgba(0,0,0,0.02);
      transition: all 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .value-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    }
    
    /* Modern Icon Box (No Gradient) */
    .value-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 1.5rem;
      background: #f0fdf4;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: #16a34a;
      transition: transform 0.3s ease;
    }
    .value-card:hover .value-icon {
      transform: scale(1.1);
      background: var(--accent);
      color: #000;
    }
    
    .value-title {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--dark);
    }
    .value-desc {
      color: var(--text-muted);
      line-height: 1.6;
      font-size: 0.95rem;
    }

    /* --- QUALITY SECTION --- */
    .quality-section {
      padding: 100px 0;
      background: #fff;
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
    
    .quality-block h4 {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .quality-block h4 i { color: #16a34a; font-size: 1.4rem; }
    
    .quality-block p {
      color: var(--text-muted);
      line-height: 1.6;
      margin: 0;
    }
    
    .quality-img-wrap img {
      width: 320px;
      height: 420px;
      object-fit: cover;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    /* --- CTA SECTION --- */
    .cta-section {
      padding: 100px 0;
      background: #f8f9fa;
    }
    .cta-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      gap: 60px;
      align-items: center;
    }
    .cta-left { flex: 1; }
    
    .cta-photos {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .cta-photo-card {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    }
    .cta-photo-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .cta-photo-card:hover img { transform: scale(1.05); }

    .cta-right { flex: 1; padding: 0 20px; }
    .cta-right h2 {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 1.5rem;
      color: var(--dark);
      line-height: 1.1;
    }
    .cta-right p {
      font-size: 1.15rem;
      line-height: 1.6;
      color: var(--text-muted);
      margin-bottom: 2.5rem;
    }

    /* Solid Green Button */
    .cta-btn {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background-color: var(--accent); /* Solid */
      color: #000;
      padding: 16px 36px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.2s ease;
      box-shadow: 0 8px 25px rgba(92, 250, 99, 0.3);
    }
    .cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(92, 250, 99, 0.45);
      filter: brightness(0.95);
    }
    .cta-btn:active { transform: translateY(0); }

    /* Responsive */
    @media (max-width: 991px) {
      .section-inner, .cta-inner { flex-direction: column; text-align: center; gap: 40px; }
      .hero-right, .story-left { order: -1; }
      .quality-inner { grid-template-columns: 1fr; gap: 40px; text-align: center; }
      .quality-col { gap: 30px; }
      .quality-block h4 { justify-content: center; }
      .values-grid { grid-template-columns: 1fr; gap: 20px; }
      .story-right, .cta-right { padding: 0; }
      .hero-img { transform: none; }
    }
    @media (max-width: 768px) {
      .hero-about, .story-section, .quality-section, .cta-section { padding: 60px 0; }
      .stats-inner { grid-template-columns: repeat(2, 1fr); gap: 30px; }
      .cta-photos { grid-template-columns: 1fr; }
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
          comfort food that's warm, heavy, and full of flavor.
          From our Batangas-style lomi to our silog meals and pancit trays,
          everything is cooked fresh and served fast.
        </p>
        <p class="small text-muted">
          <i class="bi bi-geo-alt-fill me-1"></i> Serving the community since 2012
        </p>
      </div>
      <div class="hero-right">
        <img class="hero-img"
             src="../uploads/about/chami.jpg"
             alt="Bente Sais Kitchen">
      </div>
    </div>
  </section>

  <section class="stats-section">
    <div class="stats-inner">
      <div class="stat-card">
        <span class="stat-number">12+</span>
        <div class="stat-label">Years of Service</div>
      </div>
      <div class="stat-card">
        <span class="stat-number">40+</span>
        <div class="stat-label">Food Choices</div>
      </div>
      <div class="stat-card">
        <span class="stat-number">1</span>
        <div class="stat-label">Generation</div>
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
        <img src="../uploads/about/lomi.jpg" alt="Preparation of Lomi">
      </div>
      <div class="story-right text-start">
        <h2>Our Humble Beginning</h2>
        <h3>
          Started as a simple lomi spot —
          now a go-to place for kwentuhan.
        </h3>
        <p>
          We didn't start as a fancy restaurant. We started because people kept asking:
          "Saan masarap mag-lomi dito?" That's still our standard.
        </p>
        <p>
          Our menu stayed faithful to Filipino comfort:
          thick lomi noodles with toppings, garlic rice with egg and ulam,
          and pancit that tastes like family gatherings.
        </p>
        <div class="emphasis-line">
          "Busog, sulit, masarap" isn't just a tagline. It's the rule.
        </div>
      </div>
    </div>
  </section>

  <section class="values-section">
    <div class="values-inner">
      <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; color: #1a1a1a;">
        Our Core Values
      </h2>
      <p style="font-size: 1.15rem; color: #6c757d; max-width: 600px; margin: 0 auto;">
        These principles guide everything we do in our kitchen.
      </p>
      
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon"><i class="bi bi-heart-fill"></i></div>
          <div class="value-title">Authentic Flavors</div>
          <div class="value-desc">
            We preserve traditional Batangas recipes while maintaining the authentic 
            taste that our customers love and remember.
          </div>
        </div>

        <div class="value-card">
          <div class="value-icon"><i class="bi bi-people-fill"></i></div>
          <div class="value-title">Community First</div>
          <div class="value-desc">
            We're more than a restaurant. We support local suppliers 
            and create spaces for people to connect over good food.
          </div>
        </div>

        <div class="value-card">
          <div class="value-icon"><i class="bi bi-star-fill"></i></div>
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
          <h4><i class="bi bi-fire"></i> Freshly Prepared</h4>
          <p>
            We cook in batches all day so your lomi is hot and not reheated.
            No weird shortcuts. Just honest, freshly prepared comfort food.
          </p>
        </div>
        <div class="quality-block">
          <h4><i class="bi bi-journal-bookmark-fill"></i> Family Recipes</h4>
          <p>
            Our recipes have been passed down through generations, 
            preserving the authentic flavors that make our dishes special.
          </p>
        </div>
      </div>

      <div class="quality-col quality-img-wrap">
        <img src="../uploads/about/tokwa.jpg"
             alt="Signature Dish">
      </div>

      <div class="quality-col">
        <div class="quality-block">
          <h4><i class="bi bi-shop"></i> Local Flavor First</h4>
          <p>
            We source from nearby suppliers whenever we can.
            We keep it affordable because food should fill you,
            not empty your wallet.
          </p>
        </div>
        <div class="quality-block">
          <h4><i class="bi bi-emoji-smile-fill"></i> Customer Love</h4>
          <p>
            We treat every customer like family. Your satisfaction 
            is our priority, and we're here to make your experience memorable.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-inner">
      <div class="cta-left">
        <div class="cta-photos">
          <div class="cta-photo-card"><img src="../uploads/about/tapsi.jpg" alt="Silog"></div>
          <div class="cta-photo-card"><img src="../uploads/about/liver.jpg" alt="Tray"></div>
        </div>
      </div>

      <div class="cta-right">
        <h2>Ready to eat?</h2>
        <p>
          We accept advance orders for delivery, and we prep party trays for sharing. 
          Message us or order directly from the site.
        </p>
        <a class="cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-bag-check-fill"></i>
          Order Online
        </a>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>