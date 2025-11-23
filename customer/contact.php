<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/db_connect.php'; // Provides $conn

// --- Contact Form Logic ---
$success_message = '';
$error_message = '';
$form_data = [
    'fullname' => '',
    'phone' => '',
    'email' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $form_data['fullname'] = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $form_data['phone'] = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $form_data['email'] = htmlspecialchars(trim($_POST['email'] ?? ''));
    $form_data['message'] = htmlspecialchars(trim($_POST['message'] ?? ''));

    // Simple validation
    if (empty($form_data['fullname']) || empty($form_data['phone']) || empty($form_data['email']) || empty($form_data['message'])) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please provide a valid email address.';
    } else {
        
        // --- START: Email Sending Logic (PHPMailer) ---
        // Load Composer's autoloader or manually require the files
        // Make sure these paths are correct relative to this file
        require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
        require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // Server Settings
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     // Gmail SMTP server
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'bentesaislomi.26@gmail.com';     // YOUR GMAIL ADDRESS
            $mail->Password   = 'gqzk qvow jxee kkns';                // YOUR GMAIL APP PASSWORD (NOT your login password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
            $mail->Port       = 587;                                    

            // Recipients
            $mail->setFrom('bentesaislomi.26@gmail.com', 'Bente Sais Lomi House'); // Sender
            $mail->addAddress('bentesaislomi.26@gmail.com');      // Where to send the inquiry (You)
            $mail->addReplyTo($form_data['email'], $form_data['fullname']); // Reply to the customer

            // Content
            $mail->isHTML(false);                                     
            $mail->Subject = "New Website Inquiry: " . $form_data['fullname'];
            
            $emailBody = "You have received a new message from the website contact form.\n\n";
            $emailBody .= "Name: " . $form_data['fullname'] . "\n";
            $emailBody .= "Phone: " . $form_data['phone'] . "\n";
            $emailBody .= "Email: " . $form_data['email'] . "\n\n";
            $emailBody .= "Message:\n" . $form_data['message'] . "\n";
            
            $mail->Body = $emailBody;

            $mail->send();
            // Email sent successfully
            
        } catch (Exception $e) {
            // Log error but don't stop execution; we still want to save to DB
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
        // --- END: Email Sending Logic ---

        // --- START: Database Insertion Logic ---
        try {
            $submitted_at = date('Y-m-d H:i:s');

            $stmt = $conn->prepare(
                "INSERT INTO contact_submissions (fullname, phone, email, message, submitted_at, is_processed) 
                 VALUES (?, ?, ?, ?, ?, 0)"
            );
            
            $stmt->bind_param(
                "sssss",
                $form_data['fullname'],
                $form_data['phone'],
                $form_data['email'],
                $form_data['message'],
                $submitted_at
            );

            if ($stmt->execute()) {
                // Success!
                $success_message = "Thank you, " . $form_data['fullname'] . "! Your message has been received. We'll get back to you shortly.";
                // Clear form data on success
                $form_data = ['fullname' => '', 'phone' => '', 'email' => '', 'message' => ''];
            } else {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $stmt->close();

        } catch (Exception $e) {
            $error_message = "An error occurred while submitting your message. Please try again later.";
            error_log("CRITICAL Contact form DB error: " . $e->getMessage());
        }
        // --- END: Database Insertion Logic ---
    }
}
?>
<!DOCTYPE html>
<html class="cms4-page" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

  <title>Contact | Bente Sais Lomi House</title>
  <meta name="description" content="Call us, message us, or drop by Bente Sais Lomi House for pickup, delivery, or bulk orders."/>
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
    .contact-hero {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 120px 0 80px;
      position: relative;
      overflow: hidden;
    }

    .contact-hero::before {
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

    .contact-hero-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .contact-hero h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      color: #343a40;
    }

    .contact-hero h1 span {
      color: var(--accent);
    }

    .contact-hero p {
      font-size: 1.2rem;
      line-height: 1.7;
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto 2rem;
    }

    /* Contact Section */
    .contact-section {
      padding: 100px 0;
      background: #fff;
    }

    .contact-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: start;
    }

    /* Contact Info Cards */
    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .contact-card {
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid #f1f3f4;
    }

    .contact-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .contact-card h2 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .contact-card h2 i {
      color: var(--accent);
      font-size: 1.5rem;
    }

    .contact-card p {
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 0;
    }

    .contact-card p strong {
      color: #343a40;
      font-weight: 600;
    }

    .hours-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .hours-list li {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px dashed #e9ecef;
      font-size: 0.95rem;
    }

    .hours-list li:last-child {
      border-bottom: none;
    }

    .hours-day {
      font-weight: 500;
      color: #343a40;
    }

    .hours-time {
      color: var(--text-muted);
    }

    /* Contact Form */
    .contact-form-container {
      background: #f8f9fa;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      position: relative;
      border: 1px solid #f1f3f4;
    }

    .contact-form-container h3 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #343a40;
    }

    .contact-form-container > p {
      color: var(--text-muted);
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .contact-form-group {
      margin-bottom: 1.5rem;
    }

    .contact-form-label {
      display: block;
      font-size: 0.9rem;
      font-weight: 500;
      color: #343a40;
      margin-bottom: 0.5rem;
    }

    .contact-form-control {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #fff;
    }

    .contact-form-control:focus {
      outline: none;
      border-color: #adb5bd;
      box-shadow: 0 0 0 3px rgba(173, 181, 189, 0.2);
    }

    .contact-form-control textarea {
      resize: vertical;
      min-height: 120px;
    }

    .contact-submit-btn {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      color: #000;
      border: none;
      padding: 14px 28px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      box-shadow: 0 5px 15px rgba(92, 250, 99, 0.3);
    }

    .contact-submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(92, 250, 99, 0.4);
    }

    .contact-submit-btn:active {
      transform: translateY(0);
    }

    /* Alert Styles */
    .contact-alert {
      padding: 1rem;
      border-radius: 8px;
      font-size: 0.95rem;
      margin-bottom: 1.5rem;
      border: 1px solid transparent;
    }

    .contact-alert-success {
      background-color: #d1e7dd;
      color: #0f5132;
      border-color: #badbcc;
    }

    .contact-alert-danger {
      background-color: #f8d7da;
      color: #842029;
      border-color: #f5c2c7;
    }

    /* Map & Info Section */
    .contact-map-section {
      padding: 80px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .contact-map-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
    }

    .map-info-card {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      height: 100%;
      border: 1px solid #f1f3f4;
    }

    .map-info-card h4 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #343a40;
    }

    .map-info-card p {
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .map-info-card p:last-child {
      margin-bottom: 0;
    }

    .map-container {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      height: 400px;
      border: 1px solid #f1f3f4;
    }

    .map-container iframe {
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
      .contact-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .contact-map-inner {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .contact-hero,
      .contact-section,
      .contact-map-section {
        padding: 60px 0;
      }
    }

    @media (max-width: 768px) {
      .contact-form-container {
        padding: 30px 25px;
      }

      .contact-card {
        padding: 25px 20px;
      }

      .map-info-card {
        padding: 30px 25px;
      }
    }

    @media (max-width: 480px) {
      .contact-hero {
        padding: 100px 0 60px;
      }

      .contact-form-container {
        padding: 25px 20px;
      }

      .contact-card {
        padding: 20px;
      }

      .map-info-card {
        padding: 25px 20px;
      }

      .map-container {
        height: 300px;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="contact-hero">
    <div class="contact-hero-inner">
      <h1>Get in <span>Touch</span></h1>
      <p>Have questions about our food, want to place a large order, or need delivery? We're here to help!</p>
    </div>
  </section>

  <section class="contact-section">
    <div class="contact-inner">

      <div class="contact-info">
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
            Brgy. San Roque, Nasugbu, Batangas<br/>
            Bente Sais Lomi House
          </p>
        </div>

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
            We cook in batches all day. If you're planning a big order (silog sets, pancit trays),
            send us a message so we can prep fresh.
          </p>
        </div>

        <div class="contact-card">
          <h2>
            <i class="bi bi-cash-stack"></i>
            Payment Options
          </h2>
          <p>
            <strong>Cash on pickup / delivery</strong><br/>
            <strong>GCash</strong> available for both walk-in and online orders.<br/>
            (We'll confirm payment before we send out deliveries.)
          </p>
        </div>
      </div>

      <div class="contact-form-container" id="contact-form">
        <h3>Send us a message</h3>
        <p>
          Want delivery? Need pancit for 12 people? Ask here and we'll get back to you.
        </p>
        
        <?php if ($success_message): ?>
          <div class="contact-alert contact-alert-success">
            <?= $success_message ?>
          </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
          <div class="contact-alert contact-alert-danger">
            <?= $error_message ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>#contact-form">
          <div class="contact-form-group">
            <label class="contact-form-label">Full name</label>
            <input type="text" class="contact-form-control" name="fullname" placeholder="Juan Dela Cruz" value="<?= $form_data['fullname'] ?>" required>
          </div>

          <div class="contact-form-group">
            <label class="contact-form-label">Phone number</label>
            <input type="text" class="contact-form-control" name="phone" placeholder="0912 345 6789" value="<?= $form_data['phone'] ?>" required>
          </div>

          <div class="contact-form-group">
            <label class="contact-form-label">Email address</label>
            <input type="email" class="contact-form-control" name="email" placeholder="you@example.com" value="<?= $form_data['email'] ?>" required>
          </div>

          <div class="contact-form-group">
            <label class="contact-form-label">Message / Request</label>
            <textarea class="contact-form-control" name="message" rows="4" placeholder="Example: Can I reserve 2 lomi + 1 pancit tray for pickup at 6PM?" required><?= $form_data['message'] ?></textarea>
          </div>

          <button type="submit" class="contact-submit-btn">Send inquiry</button>
        </form>

        <p style="margin-top:16px;font-size:12px;color:#6c757d;">
          For urgent same-day orders, calling or messaging via phone is faster.
        </p>
      </div>
    </div>
  </section>

  <section class="contact-map-section">
    <div class="contact-map-inner">
      <div class="map-info-card">
        <h4>Delivery / Coverage</h4>
        <p>
          We deliver within our nearby area in Nasugbu, Batangas.
          If you're a bit farther, send us your exact location —
          we'll confirm if we can send a rider or schedule a pickup.
        </p>
        <p>
          Pro tip:<br/>
          Pancit trays + silog sets are perfect for office overtime, barkada inuman,
          or family dinners. We can label per person.
        </p>
      </div>

      <div class="map-container">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3870.781896172986!2d120.6258135758509!3d14.067379986345645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd97a50b59e595%3A0xb22497f3b410de6e!2sBente%20Sais%20Lomi%20House%20-%20Nasugbu!5e0!3m2!1sen!2sph!4v1729535712345!5m2!1sen!2sph" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>