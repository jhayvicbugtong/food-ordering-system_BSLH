<?php
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
        
        // --- START: Email Sending Logic ---
        // We attempt to send the email first, but we won't stop if it fails,
        // as the database save is more important.
        
        $to = "johnaldriebaquiran51@gmail.com"; 
        $subject = "New Contact Inquiry from: " . $form_data['fullname'];
        
        // Build the email body
        $body = "You have a new message from the website contact form:\n\n";
        $body .= "Name: " . $form_data['fullname'] . "\n";
        $body .= "Phone: " . $form_data['phone'] . "\n";
        $body .= "Email: " . $form_data['email'] . "\n\n";
        $body .= "Message:\n" . $form_data['message'] . "\n";
        
        // SIMPLIFIED HEADERS
        $headers = "From: no-reply@bentesaislomi.com\r\n";
        $headers .= "Reply-To: " . $form_data['email'] . "\r\n";
        
        if (function_exists('mail')) {
            @mail($to, $subject, $body, $headers); // Use @ to suppress errors if it fails
        }
        // --- END: Email Sending Logic ---


        // --- START: Database Insertion Logic ---
        // This is the primary and most reliable record of the submission.
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
                // Database execution failed
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $stmt->close();

        } catch (Exception $e) {
            // Catch any database errors
            $error_message = "An error occurred while submitting your message. Please try again later.";
            // Log this critical error
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

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>

  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>
  
  <style>
    .contact-alert {
      padding: 1rem;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
    }
    .contact-alert-success {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
    .contact-alert-danger {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <section class="contact-section">
    <div class="contact-inner">

      <div class="contact-left">

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
            We cook in batches all day. If you’re planning a big order (silog sets, pancit trays),
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
            (We’ll confirm payment before we send out deliveries.)
          </p>
        </div>

      </div><div class="contact-right" id="contact-form">
        <h3>Send us a message</h3>
        <p>
          Want delivery? Need pancit for 12 people? Ask here and we’ll get back to you.
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
      </div></div></section>

  <section class="contact-note-area">

    <div class="note-left">
      <div class="note-card">
        <h4>Delivery / Coverage</h4>
        <p>
          We deliver within our nearby area in Nasugbu, Batangas.
          If you’re a bit farther, send us your exact location —
          we’ll confirm if we can send a rider or schedule a pickup.
        </p>
        <p style="margin-top:16px;">
          Pro tip:<br/>
          Pancit trays + silog sets are perfect for office overtime, barkada inuman,
          or family dinners. We can label per person.
        </smp>
      </div>
    </div>

    <div class="note-right">
      <div class="note-map-box">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3870.781896172986!2d120.6258135758509!3d14.067379986345645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd97a50b59e595%3A0xb22497f3b410de6e!2sBente%20Sais%20Lomi%20House%20-%20Nasugbu!5e0!3m2!1sen!2sph!4v1729535712345!5m2!1sen!2sph" 
          width="100%" 
          height="100%" 
          style="border:0;" 
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