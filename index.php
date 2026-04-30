<?php require_once __DIR__ . '/includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Bente Sais Lomi House - Authentic Batangas Lomi</title>
  <meta name="description" content="Authentic Batangas-style lomi, silog meals, and pancit — freshly prepared for delivery or pickup in Nasugbu."/>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  
  <style>
    :root {
      --accent: #5cfa63;
      --accent-hover: #4ade80;
      --dark: #1a1a1a;
      --light-bg: #f8f9fa;
      --card-radius: 20px;
      --section-gap: 100px;
    }
    
    body {
        background-color: #fff;
        color: var(--dark);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* --- UTILITIES --- */
    .container-custom {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
    }
    
    /* Solid Green Buttons */
    .btn-main {
      display: inline-flex; align-items: center; gap: 12px;
      background-color: var(--accent); color: #000;
      padding: 16px 36px; border-radius: 50px;
      font-weight: 700; font-size: 1.05rem; text-decoration: none;
      transition: all 0.3s ease; border: none;
      box-shadow: 0 10px 25px rgba(92, 250, 99, 0.25);
      position: relative; overflow: hidden;
    }
    .btn-main::after {
        content: ''; position: absolute; top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: 0.5s;
    }
    .btn-main:hover::after { left: 100%; }
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(92, 250, 99, 0.4);
      filter: brightness(0.98);
    }
    
    .btn-outline {
        background: transparent; border: 2px solid var(--dark); color: var(--dark);
        box-shadow: none; padding: 14px 34px; 
    }
    .btn-outline:hover {
        background: var(--dark); color: #fff; border-color: var(--dark);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* --- HERO SECTION (Restored "Visually Appealing" Style) --- */
    .hero-section {
      position: relative;
      /* Padding adjusted to accommodate the overlap (180px bottom) */
      padding: 80px 0 180px; 
      background: #fff;
      overflow: hidden;
    }
    
    /* Radial Background Pattern */
    .hero-section::before {
      content: '';
      position: absolute; top: -100px; right: -100px;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(92,250,99,0.08) 0%, transparent 70%);
      border-radius: 50%; pointer-events: none;
    }

    .hero-inner {
      max-width: 1200px; margin: 0 auto; padding: 0 20px;
      display: grid; grid-template-columns: 1.2fr 1fr;
      gap: 40px; align-items: center;
      position: relative; z-index: 2;
    }

    .hero-content h4 {
      font-size: 1rem; font-weight: 700;
      color: #16a34a; letter-spacing: 2px; text-transform: uppercase;
      margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;
    }
    .hero-content h4::before {
        content: ''; width: 40px; height: 2px; background: #16a34a;
    }

    .hero-content h1 {
      font-size: clamp(3.5rem, 7vw, 5.5rem);
      font-weight: 800; line-height: 1;
      margin-bottom: 1.5rem; color: var(--dark);
    }
    
    /* "LOMI" styling - Outline + Solid */
    .text-highlight {
      color: transparent;
      -webkit-text-stroke: 2px var(--dark);
      display: block;
    }
    .text-filled {
        color: var(--accent);
        -webkit-text-stroke: 0;
    }

    .hero-content p {
      font-size: 1.25rem; line-height: 1.6;
      color: #6c757d; margin-bottom: 2.5rem;
      max-width: 500px;
    }

    /* Floating Image Composition */
    .hero-visual {
      position: relative;
      height: 600px;
      display: flex; align-items: center; justify-content: center;
    }
    
    .main-dish-img {
      width: 100%; max-width: 550px;
      filter: drop-shadow(0 30px 50px rgba(0,0,0,0.2));
      animation: floatDish 6s ease-in-out infinite;
      z-index: 2; position: relative;
    }
    
    @keyframes floatDish {
      0%, 100% { transform: translateY(0) rotate(2deg); }
      50% { transform: translateY(-20px) rotate(-2deg); }
    }

    .blob-bg {
      position: absolute; top: 50%; left: 50%;
      width: 500px; height: 500px;
      background: #f0fdf4;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      z-index: 1;
    }

    /* Floating Badge */
    .floating-badge {
      position: absolute; bottom: 100px; left: 0;
      background: #fff; padding: 15px 25px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      display: flex; align-items: center; gap: 15px;
      z-index: 3;
      animation: floatBadge 5s ease-in-out infinite 1s;
    }
    @keyframes floatBadge {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .fb-icon {
        width: 45px; height: 45px; background: var(--dark);
        border-radius: 50%; color: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }

    /* --- HIGHLIGHTS (FLOATING OVERLAP) --- */
    .highlights-wrapper {
        position: relative;
        z-index: 10;
        /* Pull section up to overlap hero */
        margin-top: -100px; 
        padding-bottom: 80px;
    }
    .highlights-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    
    .highlight-card {
        background: #fff; 
        padding: 20px;
        border-radius: var(--card-radius);
        box-shadow: 0 20px 50px rgba(0,0,0,0.08); /* Lifted shadow */
        border: 1px solid rgba(0,0,0,0.02);
        display: flex; align-items: center; gap: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none; color: inherit;
        height: 100%;
    }
    .highlight-card:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 30px 60px rgba(0,0,0,0.12);
        border-color: rgba(92, 250, 99, 0.3);
    }
    
    .hl-thumb {
        width: 90px; height: 90px; flex-shrink: 0;
        border-radius: 16px; overflow: hidden; background: #f1f3f5;
    }
    .hl-thumb img { width: 100%; height: 100%; object-fit: cover; }
    
    .hl-info h3 { font-size: 1.15rem; font-weight: 700; margin: 0 0 6px 0; color: var(--dark); }
    .hl-info p { font-size: 0.9rem; color: #868e96; margin: 0 0 8px 0; line-height: 1.3; }
    .hl-arrow { 
        color: #16a34a; font-weight: 700; font-size: 0.85rem; 
        display: flex; align-items: center; gap: 4px;
        opacity: 0.8; transition: opacity 0.2s;
    }
    .highlight-card:hover .hl-arrow { opacity: 1; gap: 8px; }

    /* --- STORY SPLIT SECTION --- */
    .story-section {
        padding: var(--section-gap) 0;
        background: #fff;
    }
    .split-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 80px; align-items: center;
    }
    
    .story-img-box { position: relative; }
    .story-img-box img {
        width: 100%; border-radius: 30px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.1);
    }
    /* Decorative Circle behind image */
    .story-img-box::before {
        content: ''; position: absolute; bottom: -40px; left: -40px;
        width: 250px; height: 250px; 
        background: radial-gradient(circle, #f0fdf4 0%, transparent 70%);
        z-index: -1;
    }

    .story-label { 
        font-size: 0.9rem; font-weight: 800; color: #16a34a; 
        text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; display: block; 
    }
    .story-title { 
        font-size: 2.8rem; font-weight: 800; margin-bottom: 24px; 
        color: var(--dark); line-height: 1.1; 
    }
    .story-desc { 
        font-size: 1.1rem; line-height: 1.7; color: #6c757d; margin-bottom: 32px; 
    }

    /* --- FEATURES ROW --- */
    .features-section {
        padding: var(--section-gap) 0;
        background: #f8f9fa;
        text-align: center;
    }
    .features-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }
    .feature-item {
        background: #fff; padding: 40px 30px;
        border-radius: var(--card-radius);
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        transition: transform 0.3s;
    }
    .feature-item:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
    
    .feat-icon-circle {
        width: 70px; height: 70px; margin: 0 auto 24px;
        background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; color: #16a34a;
    }
    .feature-item:hover .feat-icon-circle { background: var(--accent); color: #000; }
    
    .feat-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; color: var(--dark); }
    .feat-text { color: #6c757d; line-height: 1.6; font-size: 0.95rem; margin: 0; }

    /* --- DARK PROMO --- */
    .promo-section {
        padding: var(--section-gap) 0;
        background: var(--dark); color: #fff;
        overflow: hidden; position: relative;
    }
    .promo-section::after {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 30px 30px; opacity: 0.5; pointer-events: none;
    }
    
    .promo-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 80px; align-items: center; position: relative; z-index: 2;
    }
    .promo-img-wrap img {
        width: 100%; border-radius: 30px; transform: rotate(-3deg);
        box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        border: 4px solid rgba(255,255,255,0.1);
        transition: transform 0.5s ease;
    }
    .promo-img-wrap:hover img { transform: rotate(0deg) scale(1.02); }
    
    .promo-content h2 { font-size: 3rem; font-weight: 800; margin-bottom: 20px; color: #fff; }
    .promo-content p { font-size: 1.2rem; color: #ced4da; margin-bottom: 35px; line-height: 1.6; }

    /* --- SIMPLE CTA --- */
    .cta-section {
        padding: 120px 0; background: #fff; text-align: center;
    }
    .cta-container { max-width: 700px; margin: 0 auto; }
    .cta-container h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 16px; color: var(--dark); }
    .cta-container p { font-size: 1.15rem; color: #6c757d; margin-bottom: 32px; }

    /* --- RESPONSIVE --- */
    @media (max-width: 991px) {
        .hero-inner { grid-template-columns: 1fr; text-align: center; }
        .hero-content h4 { justify-content: center; }
        .hero-visual { height: 400px; margin-top: 40px; }
        .main-dish-img { max-width: 400px; }
        .floating-badge { bottom: 0; left: 50%; transform: translateX(-50%); animation: none; width: max-content; }
        
        .highlights-wrapper { margin-top: -40px; }
        .highlights-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        
        .split-grid { grid-template-columns: 1fr; gap: 50px; text-align: center; }
        .story-img-box { order: -1; }
        
        .features-grid { grid-template-columns: 1fr; gap: 20px; }
        
        .promo-grid { grid-template-columns: 1fr; text-align: center; gap: 50px; }
        .promo-img-wrap { order: -1; } /* Image on top for mobile */
        .promo-img-wrap img { transform: rotate(0); max-width: 500px; margin: 0 auto; }
    }
    
    @media (max-width: 768px) {
        .hero-content h1 { font-size: 2.8rem; }
        .story-title { font-size: 2.2rem; }
        .promo-content h2 { font-size: 2.2rem; }
        .section-padding { padding: 60px 0; }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/customer/includes/header.php'; ?>

  <section class="hero-section">
    <div class="hero-inner">
      <div class="hero-content">
        <h4><i class="bi bi-fire"></i> Best in Batangas</h4>
        <h1>
          Hot, Filling,
          <span class="text-highlight">AUTHENTIC</span>
          <span class="text-filled">LOMI.</span>
        </h1>
        <p>
          Experience the real taste of Batangas comfort food. 
          Freshly cooked lomi, hearty silog meals, and savory pancit 
          delivered hot to your doorstep.
        </p>
        <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-lg-start">
            <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
              Order Now <i class="bi bi-arrow-right-short fs-4"></i>
            </a>
            <a href="customer/menu.php" class="btn-main btn-outline">
              View Menu
            </a>
        </div>
      </div>

      <div class="hero-visual">
        <div class="blob-bg"></div>
        <img src="uploads/logo/logo_home.png" class="main-dish-img" alt="Bente Sais Lomi Bowl">
        
        <div class="floating-badge">
            <div class="fb-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <strong class="d-block text-dark">Open Daily</strong>
                <small class="text-muted">8:00 AM - 10:00 PM</small>
            </div>
        </div>
      </div>
    </div>
  </section>

  <section class="highlights-wrapper">
    <div class="container-custom">
      <div class="highlights-grid">
        
        <a href="customer/menu.php#signature-lomi" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/lomi.jpg" alt="Lomi">
            </div>
            <div class="hl-info">
                <h3>Signature Lomi</h3>
                <p>Thick noodles, rich broth.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>

        <a href="customer/menu.php#silog-meals" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/silog.jpg" alt="Silog">
            </div>
            <div class="hl-info">
                <h3>Silog Meals</h3>
                <p>Garlic rice + egg + ulam.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>

        <a href="customer/menu.php#party-trays" class="highlight-card">
            <div class="hl-thumb">
                <img src="uploads/gallery/pancit.jpg" alt="Pancit">
            </div>
            <div class="hl-info">
                <h3>Party Trays</h3>
                <p>Perfect for sharing.</p>
                <div class="hl-arrow">Order Now <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>

      </div>
    </div>
  </section>

  <section class="story-section">
    <div class="container-custom">
      <div class="split-grid">
        <div class="story-img-box">
          <img src="uploads/home/store_image.png" alt="Bente Sais Kitchen">
        </div>
        <div class="story-content">
          <span class="story-label">Our Heritage</span>
          <h2 class="story-title">Cooking the food you grew up with.</h2>
          <p class="story-desc">
            We started with a simple goal: serve the kind of lomi that locals look for. 
            Hot, thick, and generous with toppings. Today, we continue that tradition 
            by preparing every bowl fresh upon order, ensuring you get that authentic 
            Batangas flavor every single time.
          </p>
          <a href="customer/about-us.php" class="btn-main btn-outline">
            Read Our Story
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="features-section">
    <div class="container-custom">
      <div class="features-grid">
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-fire"></i></div>
          <h4 class="feat-title">Cooked Fresh</h4>
          <p class="feat-text">No reheated food here. We fire up the wok only when you order.</p>
        </div>
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-wallet2"></i></div>
          <h4 class="feat-title">Affordable</h4>
          <p class="feat-text">Generous servings that fill you up without emptying your wallet.</p>
        </div>
        <div class="feature-item">
          <div class="feat-icon-circle"><i class="bi bi-bicycle"></i></div>
          <h4 class="feat-title">Fast Delivery</h4>
          <p class="feat-text">Hungry? We deliver hot and fresh to nearby barangays in Nasugbu.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="promo-section">
    <div class="container-custom">
      <div class="promo-grid">
        <div class="promo-content">
          <span class="hero-badge" style="background:rgba(255,255,255,0.1); color:var(--accent); border:none;">Best Seller</span>
          <h2>The Ultimate Comfort Combo</h2>
          <p>
            Try our Silog meals paired with a hot bowl of soup. 
            Perfect for breakfast, lunch, or a late-night craving.
          </p>
          <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
            Order for Delivery
          </a>
        </div>
        <div class="promo-img-wrap">
          <img src="uploads/home/bulaklak.jpg" alt="Delicious Silog Meal">
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-container">
      <h2>Ready to eat?</h2>
      <p>Skip the queue. Order online and we'll have your food ready for pickup or delivery.</p>
      <a href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>" class="btn-main">
        Start Your Order
      </a>
    </div>
  </section>

  <?php include __DIR__ . '/customer/includes/footer.php'; ?>

</body>
</html>