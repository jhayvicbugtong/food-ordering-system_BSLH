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
    .gallery-hero {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 120px 0 80px;
      position: relative;
      overflow: hidden;
    }

    .gallery-hero::before {
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

    .gallery-hero-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .gallery-hero h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .gallery-hero h1 span {
      color: var(--accent);
    }

    .gallery-hero p {
      font-size: 1.2rem;
      line-height: 1.7;
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto 2rem;
    }

    /* Gallery Section */
    .gallery-section {
      padding: 100px 0;
      background: #fff;
    }

    .gallery-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* Gallery Grid */
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 30px;
      margin-bottom: 60px;
    }

    .gallery-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid #f1f3f4;
    }

    .gallery-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .gallery-card-imgwrap {
      position: relative;
      width: 100%;
      height: 280px;
      overflow: hidden;
      background: #000;
    }

    .gallery-card-imgwrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .gallery-card:hover .gallery-card-imgwrap img {
      transform: scale(1.05);
    }

    .gallery-card-body {
      padding: 24px;
      text-align: center;
    }

    .gallery-card-body h3 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: #343a40;
      line-height: 1.3;
    }

    .gallery-card-body p {
      color: var(--text-muted);
      line-height: 1.6;
      margin: 0;
      font-size: 0.95rem;
    }

    /* CTA Section */
    .gallery-cta-section {
      padding: 80px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .gallery-cta-inner {
      max-width: 800px;
      margin: 0 auto;
      padding: 0 20px;
      text-align: center;
    }

    .gallery-cta-card {
      background: #fff;
      padding: 60px 40px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      border: 1px solid #f1f3f4;
    }

    .gallery-cta-card h2 {
      font-size: clamp(2rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
      color: #343a40;
      line-height: 1.2;
    }

    .gallery-cta-card p {
      font-size: 1.1rem;
      line-height: 1.6;
      color: var(--text-muted);
      margin-bottom: 2rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    .gallery-cta-btn {
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

    .gallery-cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 40px rgba(92, 250, 99, 0.4);
    }

    /* Stats Section */
    .gallery-stats {
      padding: 80px 0;
      background: #fff;
    }

    .gallery-stats-inner {
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

    .stat-icon {
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

    .stat-card:hover .stat-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: #343a40;
      margin-bottom: 0.5rem;
      display: block;
    }

    .stat-label {
      font-size: 1rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
      .gallery-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
      }

      .gallery-hero,
      .gallery-section,
      .gallery-cta-section,
      .gallery-stats {
        padding: 60px 0;
      }

      .gallery-cta-card {
        padding: 50px 30px;
      }
    }

    @media (max-width: 768px) {
      .gallery-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .gallery-card-imgwrap {
        height: 250px;
      }

      .gallery-card-body {
        padding: 20px;
      }

      .gallery-stats-inner {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
      }

      .gallery-cta-card {
        padding: 40px 25px;
      }
    }

    @media (max-width: 480px) {
      .gallery-hero {
        padding: 100px 0 60px;
      }

      .gallery-grid {
        grid-template-columns: 1fr;
      }

      .gallery-card-imgwrap {
        height: 220px;
      }

      .gallery-stats-inner {
        grid-template-columns: 1fr;
      }

      .stat-card {
        padding: 30px 20px;
      }

      .stat-icon {
        width: 70px;
        height: 70px;
        font-size: 1.8rem;
      }

      .stat-number {
        font-size: 2rem;
      }

      .gallery-cta-btn {
        padding: 14px 28px;
        font-size: 1rem;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="gallery-hero">
    <div class="gallery-hero-inner">
      <h1>Our Food, <span>Our Pride</span></h1>
      <p>
        Bowls that steam, plates that crunch, trays that feed the whole barkada.
        This is what we serve every day at Bente Sais Lomi House.
      </p>
    </div>
  </section>

  <section class="gallery-section">
    <div class="gallery-inner">
      <div class="gallery-grid">
        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="../uploads/gallery/lomi.jpg"
                 alt="Signature Lomi Bowl - Thick noodles, rich broth, crispy toppings">
          </div>
          <div class="gallery-card-body">
            <h3>Signature Lomi</h3>
            <p>Thick noodles, rich broth, crispy toppings. Our bestseller that keeps customers coming back.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="../uploads/gallery/silog.jpg"
                 alt="Silog Combo - Garlic rice, egg, and your choice of ulam">
          </div>
          <div class="gallery-card-body">
            <h3>Silog Meals</h3>
            <p>Garlic rice + egg + ulam. Perfect for breakfast, dinner, or any time you need comfort food.</p>
          </div>
        </div>

        <div class="gallery-card">
          <div class="gallery-card-imgwrap">
            <img src="../uploads/gallery/pancit.jpg"
                 alt="Pancit Tray for Sharing - Perfect for parties and gatherings">
          </div>
          <div class="gallery-card-body">
            <h3>Party Trays</h3>
            <p>Pancit + toppings. Good for sharing, perfect for handaan and family celebrations.</p>
          </div>
        </div>

       
      </div>
    </div>
  </section>


  <section class="gallery-cta-section">
    <div class="gallery-cta-inner">
      <div class="gallery-cta-card">
        <h2>Craving something you saw here?</h2>
        <p>
          You can order bowls, silog, pancit trays, and more online.
          We'll prep it fresh for pickup or delivery within Nasugbu area.
        </p>
        <a class="gallery-cta-btn" href="<?= htmlspecialchars($ORDER_BTN_LINK) ?>">
          <i class="bi bi-bag-check-fill"></i>
          Order online now
        </a>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>