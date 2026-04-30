<?php
// customer/contact.php

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/db_connect.php'; // Provides $conn

// --- 1. Fetch System Settings from Database ---
$settings = [];
if (isset($conn)) {
    $q = $conn->query("SELECT * FROM system_settings");
    if ($q) {
        while ($row = $q->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// --- 2. Set Dynamic Variables (with fallbacks) ---
$store_name     = !empty($settings['store_name']) ? $settings['store_name'] : 'Bente Sais Lomi House';
$store_phone    = !empty($settings['store_phone']) ? $settings['store_phone'] : '0912-345-6789';
$store_email    = !empty($settings['store_email']) ? $settings['store_email'] : 'bentesaislomi.26@gmail.com';
$store_location = !empty($settings['store_location']) ? $settings['store_location'] : 'Brgy. San Roque, Batangas';

// Format Store Hours
$open_time_raw  = !empty($settings['opening_time']) ? $settings['opening_time'] : '08:00';
$close_time_raw = !empty($settings['closing_time']) ? $settings['closing_time'] : '22:00';
$formatted_hours = date('g:i A', strtotime($open_time_raw)) . ' – ' . date('g:i A', strtotime($close_time_raw));


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
        require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
        require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // Server Settings
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'bentesaislomi.26@gmail.com';     // SMTP Username
            $mail->Password   = 'gqzk qvow jxee kkns';                // SMTP App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
            $mail->Port       = 587;                                    

            // Recipients
            $mail->setFrom('bentesaislomi.26@gmail.com', $store_name); 
            $mail->addAddress($store_email);      
            $mail->addReplyTo($form_data['email'], $form_data['fullname']); 

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
            
        } catch (Exception $e) {
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
                $success_message = "Thank you, " . $form_data['fullname'] . "! Your message has been received.";
                $form_data = ['fullname' => '', 'phone' => '', 'email' => '', 'message' => ''];
            } else {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $stmt->close();

        } catch (Exception $e) {
            $error_message = "An error occurred while submitting your message. Please try again later.";
            error_log("CRITICAL Contact form DB error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Contact Us | <?= htmlspecialchars($store_name) ?></title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
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
    .contact-hero {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 80px 0 60px;
      position: relative;
      overflow: hidden;
      text-align: center;
    }
    .contact-hero::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: 
        radial-gradient(circle at 10% 20%, rgba(92, 250, 99, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 90% 80%, rgba(92, 250, 99, 0.03) 0%, transparent 50%);
      pointer-events: none;
    }
    .contact-hero h1 {
      font-size: clamp(2.5rem, 5vw, 3.5rem);
      font-weight: 800;
      margin-bottom: 1rem;
      color: var(--dark);
      letter-spacing: -1px;
    }
    .contact-hero h1 span {
      color: #5cfa63; /* Text accent color */
    }
    .contact-hero p {
      font-size: 1.1rem;
      line-height: 1.6;
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto;
    }

    /* --- MAIN LAYOUT --- */
    .contact-section {
      padding: 40px 0 80px;
    }
    .contact-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* --- TOP ROW: INFO CARDS --- */
    .info-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      margin-bottom: 40px;
    }
    
    .info-card {
      background: #fff;
      padding: 30px;
      border-radius: 20px;
      border: 1px solid rgba(0,0,0,0.04);
      box-shadow: 0 4px 20px rgba(0,0,0,0.02);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
      height: 100%;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    
    .icon-box {
      width: 60px; height: 60px;
      border-radius: 50%;
      background: #f0fdf4;
      color: #16a34a;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
      margin-bottom: 20px;
    }
    
    .info-card h3 {
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 10px;
      color: var(--dark);
    }
    .info-card p, .info-card li {
      color: var(--text-muted);
      font-size: 0.95rem;
      line-height: 1.5;
      margin: 0;
    }
    .info-card strong { color: var(--dark); font-weight: 600; }

    /* --- BOTTOM ROW: FORM (Left) & MAP (Right) --- */
    .content-split {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      align-items: stretch;
    }

    /* Form Card */
    .form-card {
      background: #fff;
      padding: 40px;
      border-radius: 24px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.06);
      border: 1px solid rgba(0,0,0,0.03);
      height: 100%;
    }
    .form-header { margin-bottom: 30px; }
    .form-header h2 {
      font-size: 1.8rem;
      font-weight: 800;
      margin-bottom: 10px;
      color: var(--dark);
    }
    .form-header p { color: var(--text-muted); }

    .form-group { margin-bottom: 20px; }
    .form-label {
      font-size: 0.9rem; font-weight: 600;
      margin-bottom: 8px; display: block; color: #343a40;
    }
    .form-control-custom {
      width: 100%; padding: 14px 18px;
      border-radius: 12px; border: 1px solid #e9ecef;
      background: #f9fafb; font-size: 1rem;
      transition: all 0.2s ease; font-family: inherit;
    }
    .form-control-custom:focus {
      outline: none; border-color: var(--accent);
      background: #fff; box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.15);
    }
    textarea.form-control-custom { resize: vertical; min-height: 160px; }

    .btn-submit {
      width: 100%; background-color: var(--accent); color: #000;
      font-weight: 700; font-size: 1.1rem; padding: 16px;
      border: none; border-radius: 50px; cursor: pointer;
      box-shadow: 0 8px 20px rgba(92, 250, 99, 0.3);
      transition: all 0.2s ease;
      display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(92, 250, 99, 0.4);
      filter: brightness(0.95);
    }
    .btn-submit:active { transform: translateY(0); }

    /* Map Card */
    .map-card {
      background: #fff;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0,0,0,0.06);
      border: 1px solid rgba(0,0,0,0.03);
      height: 100%;
      min-height: 600px; /* Match typical form height */
      position: relative;
    }
    .map-card iframe {
      width: 100%; height: 100%; border: none;
    }
    .map-overlay {
      position: absolute; bottom: 20px; left: 20px; right: 20px;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      padding: 20px; border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .map-overlay h5 { font-size: 1.1rem; font-weight: 700; margin: 0 0 5px; }
    .map-overlay p { font-size: 0.9rem; color: var(--text-muted); margin: 0; }

    /* Alerts */
    .alert-custom {
      padding: 15px; border-radius: 12px; margin-bottom: 25px;
      font-size: 0.95rem; display: flex; align-items: center; gap: 10px;
    }
    .alert-success { background: #dcfce7; color: #14532d; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #7f1d1d; border: 1px solid #fecaca; }

    /* Responsive */
    @media (max-width: 991px) {
      .info-row { grid-template-columns: 1fr; gap: 20px; }
      .content-split { grid-template-columns: 1fr; }
      .map-card { min-height: 400px; }
      .contact-hero { padding: 60px 0 40px; }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="contact-hero">
    <div class="container">
      <h1>Let's <span>Connect</span></h1>
      <p>Have a question, feedback, or a large order? We'd love to hear from you.</p>
    </div>
  </section>

  <section class="contact-section">
    <div class="contact-container">
      
      <div class="info-row">
        
        <div class="info-card">
          <div class="icon-box"><i class="bi bi-geo-alt-fill"></i></div>
          <h3>Visit Us</h3>
          <p><?= htmlspecialchars($store_location) ?></p>
          <p style="margin-top: 5px;" class="small">We are located near the main road.</p>
        </div>

        <div class="info-card">
          <div class="icon-box"><i class="bi bi-telephone-fill"></i></div>
          <h3>Call or Text</h3>
          <p class="mb-2">For urgent orders:</p>
          <p style="font-size: 1.1rem;"><strong><?= htmlspecialchars($store_phone) ?></strong></p>
        </div>

        <div class="info-card">
          <div class="icon-box"><i class="bi bi-clock-fill"></i></div>
          <h3>Store Hours</h3>
          <p><strong>Daily:</strong> <?= htmlspecialchars($formatted_hours) ?></p>
          <p class="small mt-2 text-muted">Kitchen closes 30 mins prior.</p>
        </div>

      </div>

      <div class="content-split">
        
        <div class="form-card" id="contact-form">
          <div class="form-header">
            <h2>Send us a Message</h2>
            <p>Fill out the form below and we'll get back to you soon.</p>
          </div>

          <?php if ($success_message): ?>
            <div class="alert-custom alert-success">
              <i class="bi bi-check-circle-fill" style="font-size: 1.4rem;"></i>
              <span><?= $success_message ?></span>
            </div>
          <?php endif; ?>

          <?php if ($error_message): ?>
            <div class="alert-custom alert-error">
              <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.4rem;"></i>
              <span><?= $error_message ?></span>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>#contact-form">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control-custom" name="fullname" placeholder="e.g. Juan Dela Cruz" value="<?= $form_data['fullname'] ?>" required>
            </div>

            <div class="row">
              <div class="col-md-6 form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control-custom" name="phone" placeholder="0912 345 6789" value="<?= $form_data['phone'] ?>" required>
              </div>
              <div class="col-md-6 form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control-custom" name="email" placeholder="juan@example.com" value="<?= $form_data['email'] ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Your Message</label>
              <textarea class="form-control-custom" name="message" placeholder="How can we help you today?" required><?= $form_data['message'] ?></textarea>
            </div>

            <button type="submit" class="btn-submit">
              <span>Send Message</span>
              <i class="bi bi-send-fill"></i>
            </button>
          </form>
        </div>

        <div class="map-card">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3870.781896172986!2d120.6258135758509!3d14.067379986345645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd97a50b59e595%3A0xb22497f3b410de6e!2sBente%20Sais%20Lomi%20House%20-%20Nasugbu!5e0!3m2!1sen!2sph!4v1729535712345!5m2!1sen!2sph" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
          <div class="map-overlay">
            <h5><i class="bi bi-bicycle me-2" style="color:var(--accent-hover);"></i> Delivery Area</h5>
            <p>We deliver to nearby barangays in Nasugbu. Message us to confirm your location.</p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>