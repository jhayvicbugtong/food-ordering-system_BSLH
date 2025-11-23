<?php require_once __DIR__ . '/includes/db_connect.php'; ?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Bente Sais Lomi House - Batangas Style Comfort Food</title>
  <meta name="description" content="Authentic Batangas-style lomi, silog meals, and pancit — freshly prepared for delivery or pickup. Comfort food done right."/>
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

    /* Modern Hero Section */
    .hero-section {
      background: 
        url('https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/wave2.png?v=0');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 80%, rgba(92, 250, 99, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(92, 250, 99, 0.05) 0%, transparent 50%);
      animation: pulse 6s ease-in-out infinite;
      pointer-events: none;
    }

    @keyframes pulse {
      0%, 100% { opacity: 0.3; }
      50% { opacity: 0.6; }
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
      color: #343a40;
    }

    .hero-left h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #343a40 0%, var(--accent) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-left h1 span {
      color: var(--accent);
      -webkit-text-fill-color: var(--accent);
    }

    .hero-left h4 {
      font-size: clamp(1.1rem, 2vw, 1.3rem);
      font-weight: 400;
      line-height: 1.6;
      margin-bottom: 2.5rem;
      color: #6c757d;
      max-width: 500px;
    }

    .hero-cta {
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
      position: relative;
      overflow: hidden;
      border: none;
      cursor: pointer;
    }

    .hero-cta::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .hero-cta:hover::before {
      left: 100%;
    }

    .hero-cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(92, 250, 99, 0.4);
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
      max-height: 600px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
      transition: transform 0.3s ease;
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { 
        transform: perspective(1000px) rotateY(-5deg) rotateX(5deg) translateY(0px); 
      }
      50% { 
        transform: perspective(1000px) rotateY(-5deg) rotateX(5deg) translateY(-20px); 
      }
    }

    /* Modern About Section with Image Box */
    .about-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .about-img-col {
      position: relative;
    }

    .image-box-container {
      position: relative;
      max-width: 500px;
      margin: 0 auto;
    }

    .image-box {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      transform: perspective(1000px) rotateY(5deg) rotateX(2deg);
      transition: all 0.4s ease;
      border: 8px solid #fff;
      background: #fff;
    }

    .image-box:hover {
      transform: perspective(1000px) rotateY(5deg) rotateX(2deg) translateY(-10px);
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
    }

    .image-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(92, 250, 99, 0.1) 0%, transparent 60%);
      z-index: 1;
      pointer-events: none;
    }

    .image-box img {
      width: 100%;
      height: 500px;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .image-box:hover img {
      transform: scale(1.05);
    }

    .image-box-decoration {
      position: absolute;
      z-index: 2;
    }

    .decoration-1 {
      top: -15px;
      left: -15px;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 12px;
      transform: rotate(45deg);
      opacity: 0.8;
      animation: float-decoration 4s ease-in-out infinite;
    }

    .decoration-2 {
      bottom: -10px;
      right: -10px;
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 8px;
      transform: rotate(-30deg);
      opacity: 0.6;
      animation: float-decoration 3s ease-in-out infinite reverse;
    }

    .decoration-3 {
      top: 50%;
      left: -20px;
      width: 30px;
      height: 30px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 6px;
      transform: rotate(15deg);
      opacity: 0.4;
      animation: float-decoration 5s ease-in-out infinite;
    }

    @keyframes float-decoration {
      0%, 100% { transform: translateY(0px) rotate(45deg); }
      50% { transform: translateY(-10px) rotate(45deg); }
    }

    .image-box-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      padding: 8px 16px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.9rem;
      z-index: 3;
      box-shadow: 0 5px 15px rgba(92, 250, 99, 0.3);
      animation: pulse-badge 2s ease-in-out infinite;
    }

    @keyframes pulse-badge {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .about-text-col {
      padding: 0 40px;
    }

    .about-text-col h4 {
      color: var(--accent);
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .about-text-col h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .about-text-col p {
      font-size: 1.1rem;
      line-height: 1.7;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    .about-cta {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: var(--accent);
      padding: 12px 24px;
      border: 2px solid var(--accent);
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .about-cta:hover {
      background: var(--accent);
      color: #000;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(92, 250, 99, 0.3);
    }

    /* Enhanced Features Section */
    .features-section {
      padding: 80px 0;
      background: #fff;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
      max-width: 1000px;
      margin: 0 auto;
    }

    .feature-card {
      text-align: center;
      padding: 40px 30px;
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: 1px solid #f1f3f4;
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: #000;
      transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .feature-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .feature-desc {
      color: var(--text-muted);
      line-height: 1.6;
    }

    /* Modern Split Section */
    .middle-split-section {
      padding: 100px 0;
      background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
      color: #fff;
    }

    .middle-split-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      gap: 60px;
      align-items: center;
    }

    .split-text-block {
      text-align: center;
    }

    .split-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .split-text-block p {
      color: #adb5bd;
      line-height: 1.6;
    }

    .middle-img-wrap {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
    }

    .middle-img-wrap:hover {
      transform: scale(1.05);
    }

    .middle-img-wrap img {
      width: 300px;
      height: 300px;
      object-fit: cover;
      border-radius: 20px;
      transition: transform 0.3s ease;
    }

    /* Enhanced CTA Section - No Oval Shapes */
    .cta-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .cta-block {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
    }

    .cta-img-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .cta-img-card {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
      background: #fff;
      border: none;
      position: relative;
    }

    .cta-img-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .cta-img-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      transition: transform 0.3s ease;
      border-radius: 0;
    }

    .cta-img-card:hover img {
      transform: scale(1.1);
    }

    .cta-text-col h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .cta-text-col h4 {
      font-size: 1.2rem;
      font-weight: 400;
      line-height: 1.6;
      margin-bottom: 2rem;
      color: var(--text-muted);
    }

    .cta-center-btn {
      text-align: left;
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

    /* Responsive Design */
    @media (max-width: 968px) {
      .section-inner {
        flex-direction: column;
        text-align: center;
        gap: 40px;
      }

      .middle-split-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .cta-block {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
      }

      .about-text-col {
        padding: 0 20px;
      }

      .hero-img {
        transform: none;
        animation: none;
        max-height: 400px;
      }

      .cta-center-btn {
        text-align: center;
      }

      .image-box {
        transform: none;
      }

      .image-box:hover {
        transform: translateY(-10px);
      }

      .decoration-1,
      .decoration-2,
      .decoration-3 {
        display: none;
      }
    }

    @media (max-width: 768px) {
      .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .cta-img-grid {
        grid-template-columns: 1fr;
      }

      .hero-section,
      .about-section,
      .features-section,
      .middle-split-section,
      .cta-section {
        padding: 60px 0;
      }

      .section-inner {
        padding: 0 15px;
      }

      .image-box img {
        height: 400px;
      }
    }

    @media (max-width: 480px) {
      .hero-cta,
      .about-cta,
      .cta-btn {
        padding: 14px 28px;
        font-size: 1rem;
      }

      .feature-card {
        padding: 30px 20px;
      }

      .middle-img-wrap img {
        width: 250px;
        height: 250px;
      }

      .image-box img {
        height: 300px;
      }

      .image-box-badge {
        top: 10px;
        right: 10px;
        font-size: 0.8rem;
        padding: 6px 12px;
      }

      .cta-img-card img {
        height: 180px;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/customer/includes/header.php'; ?>

  <section class="hero-section">
    <div class="section-inner">
      <div class="hero-left">
        <h1>
          Hot, filling,
          <span>LOMI!</span>
        </h1>
        <h4>
          Enjoy our authentic Batangas-style lomi, silog meals, and pancit — 
          freshly prepared for delivery or pickup, Monday to Sunday.
        </h4>

        <a class="hero-cta" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-bag-check-fill"></i>
          Order online
        </a>
      </div>

      <div class="hero-right">
        <img class="hero-img"
             src="uploads/landing_page/11401354.png"
             alt="Signature Lomi Bowl - Bente Sais Lomi House">
      </div>
    </div>
  </section>

  <section class="about-section">
    <div class="section-inner" style="flex-direction: row-reverse;">
      <div class="about-img-col">
        <div class="image-box-container">
          <div class="image-box">
            <div class="image-box-badge">
              <i class="bi bi-star-fill"></i> Fresh Daily
            </div>
            <img src="uploads/landing_page/5312810.jpg" alt="Freshly Prepared Lomi - Bente Sais Lomi House">
          </div>
          <div class="image-box-decoration decoration-1"></div>
          <div class="image-box-decoration decoration-2"></div>
          <div class="image-box-decoration decoration-3"></div>
        </div>
      </div>

      <div class="about-text-col">
        <h4>Authentic Batangas Flavor</h4>
        <h2>Experience the taste of home with every bowl</h2>
        <p>
          We're passionate about serving authentic Batangas-style comfort food. 
          From our rich, flavorful lomi broth to our perfectly cooked silog meals 
          and pancit for sharing — every dish is prepared with fresh ingredients 
          and traditional recipes passed down through generations.
        </p>

        <a class="about-cta" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-arrow-right"></i>
          Explore our menu
        </a>
      </div>
    </div>
  </section>

  <section class="features-section">
    <div class="section-inner" style="flex-direction: column; text-align:center;">
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-cup-hot-fill"></i></div>
          <div class="feature-title">Rich & Flavorful Broth</div>
          <div class="feature-desc">
            Our signature lomi broth is slow-cooked for hours, packed with authentic 
            Batangas flavor and served piping hot in every bowl.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-emoji-sunglasses-fill"></i></div>
          <div class="feature-title">Perfect for Gatherings</div>
          <div class="feature-desc">
            Whether it's family dinner or barkada hangout, our cozy atmosphere 
            and delicious food create the perfect setting for making memories.
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-egg-fried"></i></div>
          <div class="feature-title">Silog Classics</div>
          <div class="feature-desc">
            Traditional Filipino breakfast favorites available all day. 
            Perfect garlic rice, sunny-side-up egg, and your choice of ulam — 
            comfort in every bite.
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="middle-split-section">
    <div class="middle-split-inner">
      <div class="col-split split-text-block">
        <div class="split-title">Quality & Value</div>
        <p>
          We believe that great food should be accessible to everyone. 
          Our generous portions and affordable prices ensure you leave 
          satisfied without breaking the bank.
        </p>
      </div>

      <div class="col-split middle-img-wrap">
        <img src="uploads/landing_page/f57s_6t51_230518.jpg"
             alt="Fresh Ingredients - Bente Sais Lomi House">
      </div>

      <div class="col-split split-text-block">
        <div class="split-title">Fresh Daily</div>
        <p>
          We prepare everything in small batches throughout the day using 
          the freshest local ingredients. No shortcuts, no compromises — 
          just honest, delicious food.
        </p>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-block">
      <div class="cta-img-col">
        <div class="cta-img-grid">
          <div class="cta-img-card">
            <img src="uploads/landing_page/thai-food-noodles-spicy-boil-with-pork-boil-egg.jpg" alt="Silog Meal Combo">
          </div>
          <div class="cta-img-card">
            <img src="uploads/landing_page/thai-food-noodles-spicy-boil-with-seafood-pork-hot-pot.jpg" alt="Party Tray Pancit">
          </div>
        </div>
      </div>

      <div class="cta-text-col">
        <h2>Ready to experience authentic Batangas flavor?</h2>
        <h4>
          Place your order for pickup or delivery. We also cater to groups 
          and special occasions with our party trays and silog packs. 
          Message us for bulk orders and custom arrangements.
        </h4>
        <div class="cta-center-btn">
          <a class="cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
            <i class="bi bi-bag-check-fill"></i>
            Order now
          </a>
        </div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/customer/includes/footer.php'; ?>

</body>
</html>