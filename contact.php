<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Contact | Bente Sais Lomi House</title>
  <meta name="description" content="Call us, message us, or drop by Bente Sais Lomi House for pickup, delivery, or bulk orders."/>
  <meta name="robots" content="index,follow"/>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <!-- Shared styles -->
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
</head>

<body>

  <!-- HEADER -->
  <header class="site-header">
    <div class="site-header-inner">
      <a class="brand-left" href="/food-ordering-system_BSLH/index_public.php">
        <img src="https://cs.cdn-upm.com/themes/98dfb947-4a04-11ed-8bca-525400080621/assets-6/logo.png?v=0" alt="Logo">
        <div>
          <div class="brand-text-title">Bente Sais Lomi House</div>
          <div class="brand-text-sub">Since 26</div>
        </div>
      </a>

      <nav class="nav-links">
        <a href="/food-ordering-system_BSLH/index_public.php">Home</a>
        <a href="/food-ordering-system_BSLH/about-us.php">About us</a>
        <a href="/food-ordering-system_BSLH/gallery.php">Gallery</a>
        <a class="active" href="/food-ordering-system_BSLH/contact.php">Contact</a>
        <a href="/food-ordering-system_BSLH/auth/login.php">Staff / Admin Login</a>
        <a class="order-btn" href="/food-ordering-system_BSLH/customer/index.php">
          <i class="bi bi-bag-fill" style="color:#000;"></i>
          <span>Order online</span>
        </a>
      </nav>
    </div>
  </header>

  <!-- CONTACT CONTENT -->
  <section class="contact-section">
    <div class="contact-inner">

      <!-- LEFT SIDE: INFO CARDS -->
      <div class="contact-left">

        <!-- Phone / Address -->
        <div class="contact-card">
          <h2>
            <i class="bi bi-telephone-fill"></i>
            Call / Pickup
          </h2>
          <p>
            <strong>0912-345-6789</strong><br/>
            Place advance orders / reserve trays / ask if lomi is still available.
          </p>
          <p style="margin-top:14px;">
            <strong>Pickup Location:</strong><br/>
            Brgy. San Roque, Batangas<br/>
            Bente Sais Lomi House
          </p>
        </div>

        <!-- Hours -->
        <div class="contact-card">
          <h2>
            <i class="bi bi-clock-fill"></i>
            Store Hours
          </h2>

          <ul class="hours-list">
            <li>
              <span class="hours-day">Monday - Friday</span>
              <span class="hours-time">9:00 AM – 9:00 PM</span>
            </li>
            <li>
              <span class="hours-day">Saturday</span>
              <span class="hours-time">9:00 AM – 10:00 PM</span>
            </li>
            <li>
              <span class="hours-day">Sunday</span>
              <span class="hours-time">10:00 AM – 8:00 PM</span>
            </li>
          </ul>

          <p style="margin-top:12px;">
            We cook in batches all day. If you’re planning a big order (silog sets, pancit trays),
            send us a message so we can prep fresh.
          </p>
        </div>

        <!-- Payments -->
        <div class="contact-card">
          <h2>
            <i class="bi bi-cash-stack"></i>
            Payment Options
          </h2>
          <p>
            <strong>Cash on pickup / delivery</strong><br/>
            <strong>GCash</strong> available for both walk-in and online orders.<br/>
            (We’ll confirm payment before we send out deliveries.)
          </p>
        </div>

      </div><!-- /contact-left -->

      <!-- RIGHT SIDE: MESSAGE / INQUIRY FORM -->
      <div class="contact-right">
        <h3>Send us a message</h3>
        <p>
          Want delivery? Need pancit for 12 people? Ask here and we’ll get back to you.
        </p>

        <!-- This is just UI. You can wire it later with PHP mail() or insert into DB. -->
        <form method="post" action="#">
          <div class="contact-form-group">
            <label class="contact-form-label">Full name</label>
            <input type="text" class="contact-form-control" name="fullname" placeholder="Juan Dela Cruz" required>
          </div>

          <div class="contact-form-group">
            <label class="contact-form-label">Phone number</label>
            <input type="text" class="contact-form-control" name="phone" placeholder="0912 345 6789" required>
          </div>

          <div class="contact-form-group">
            <label class="contact-form-label">Message / Request</label>
            <textarea class="contact-form-control" name="message" rows="4" placeholder="Example: Can I reserve 2 lomi + 1 pancit tray for pickup at 6PM?"
              required></textarea>
          </div>

          <button type="submit" class="contact-submit-btn">Send inquiry</button>
        </form>

        <p style="margin-top:16px;font-size:12px;color:#6c757d;">
          For urgent same-day orders, calling or messaging via phone is faster.
        </p>
      </div><!-- /contact-right -->

    </div><!-- /contact-inner -->
  </section>

  <!-- MAP / DELIVERY NOTE -->
  <section class="contact-note-area">

    <div class="note-left">
      <div class="note-card">
        <h4>Delivery / Coverage</h4>
        <p>
          We deliver within our nearby area in Batangas.
          If you’re a bit farther, send us your exact location —
          we’ll confirm if we can send a rider or schedule a pickup.
        </p>
        <p style="margin-top:16px;">
          Pro tip:<br/>
          Pancit trays + silog sets are perfect for office overtime, barkada inuman,
          or family dinners. We can label per person.
        </p>
      </div>
    </div>

    <div class="note-right">
      <div class="note-map-box">
        <div>
          <div style="font-weight:600; color:#fff; font-size:14px; line-height:1.4;">
            Map / location preview
          </div>
          <div style="margin-top:8px;">
            Brgy. San Roque, Batangas<br/>
            Bente Sais Lomi House Pickup Point
          </div>
          <small>(Embed Google Maps iframe here later)</small>
        </div>
      </div>
    </div>

  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div>
      <strong>Bente Sais Lomi House</strong><br/>
      Comfort food. Local flavor.
    </div>

    <div class="footer-nav">
      <a href="/food-ordering-system_BSLH/index_public.php">Home</a>
      <a href="/food-ordering-system_BSLH/about-us.php">About us</a>
      <a href="/food-ordering-system_BSLH/gallery.php">Gallery</a>
      <a class="active" href="/food-ordering-system_BSLH/contact.php">Contact</a>
      <a href="/food-ordering-system_BSLH/auth/login.php">Staff / Admin Login</a>
    </div>

    <div class="footer-contact">
      <div><i class="bi bi-telephone-fill"></i> 0912-345-6789</div>
      <div><i class="bi bi-geo-alt-fill"></i> Brgy. San Roque, Batangas</div>
    </div>

    <div style="margin-top:16px; font-size:12px; color:#adb5bd;">
      © <?php echo date('Y'); ?> Bente Sais Lomi House. All rights reserved.
    </div>
  </footer>

</body>
</html>
