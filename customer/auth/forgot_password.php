<?php
// customer/auth/forgot_password.php
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL and starts session

// --- NEW: Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $err = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);

            // Update user record with OTP and refresh updated_at for expiry check
            $update = $conn->prepare("UPDATE users SET verification_code = ?, updated_at = NOW() WHERE user_id = ?");
            $update->bind_param('ii', $otp, $user['user_id']);
            
            if ($update->execute()) {
                $update->close();

                // Send Email
                require_once __DIR__ . '/../../includes/PHPMailer/Exception.php';
                require_once __DIR__ . '/../../includes/PHPMailer/PHPMailer.php';
                require_once __DIR__ . '/../../includes/PHPMailer/SMTP.php';

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'bentesaislomi.26@gmail.com';
                    $mail->Password   = 'gqzk qvow jxee kkns';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Use dynamic store name as sender
                    $mail->setFrom('bentesaislomi.26@gmail.com', $store_name);
                    $mail->addAddress($email, $user['first_name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Reset Your Password';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                            <h2 style='color: #2e8503;'>Password Reset Request</h2>
                            <p>Hi <strong>" . htmlspecialchars($user['first_name']) . "</strong>,</p>
                            <p>You requested to reset your password. Use the code below to proceed. This code is valid for <strong>15 minutes</strong>.</p>
                            <div style='background: #f8f9fa; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                                <span style='font-size: 24px; letter-spacing: 5px; font-weight: bold; color: #000;'>" . $otp . "</span>
                            </div>
                            <p style='font-size: 12px; color: #666;'>If you did not request this, please ignore this email.</p>
                        </div>
                    ";

                    $mail->send();

                    // Store email in session for the next step
                    $_SESSION['reset_email'] = $email;
                    header("Location: reset_password.php");
                    exit;

                } catch (Exception $e) {
                    $err = "Could not send email. Please try again later. Error: " . $mail->ErrorInfo;
                }
            } else {
                $err = "Database error. Please try again.";
            }
        } else {
            $err = "We couldn't find an account with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Forgot Password | <?= htmlspecialchars($store_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --bg-dark: #212529;
      --bg-card: #ffffff;
      --text-main: #212529;
      --text-dim: #6c757d;
      --accent: #5cfa63; 
      --accent-hover: #4ae052;
      --radius-lg: 16px;
      --shadow-soft: 0 10px 40px rgba(0,0,0,0.08);
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-main);
    }

    .auth-shell {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-soft);
      display: flex;
      width: 900px;
      max-width: 95%;
      min-height: 500px;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.04);
    }

    /* Sidebar */
    .auth-aside {
      background-color: var(--bg-dark);
      color: #f8f9fa;
      padding: 48px 40px;
      width: 40%;
      min-width: 300px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      background-image: radial-gradient(circle at 10% 10%, rgba(92, 250, 99, 0.05) 0%, transparent 40%);
    }

    .auth-aside::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      height: 5px;
      width: 100%;
      background: var(--accent);
    }

    .brand-block {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .brand-logo {
      height: 48px;
      width: 48px;
      border-radius: 12px;
      background: radial-gradient(circle at 30% 30%, #5cfa63 0%, #1c1f1f 70%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #000;
      font-size: 18px;
      line-height: 1;
      box-shadow: 0 0 20px rgba(92,250,99,0.4);
    }

    .brand-text h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      color: #fff;
      line-height: 1.2;
    }

    .brand-text p {
      margin: 4px 0 0;
      font-size: 14px;
      color: rgba(255,255,255,0.6);
      font-weight: 500;
    }

    .intro-text {
      margin-top: 32px;
      font-size: 14px;
      line-height: 1.6;
      color: rgba(255,255,255,0.7);
    }

    /* Main Content */
    .auth-main {
      flex: 1;
      padding: 48px 50px;
      background: var(--bg-card);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-header { margin-bottom: 32px; }

    .auth-header h2 {
      margin: 0;
      font-size: 26px;
      font-weight: 700;
      color: var(--text-main);
      line-height: 1.2;
    }

    .auth-header p {
      margin: 8px 0 0;
      font-size: 15px;
      color: var(--text-dim);
    }

    .form-control {
      font-size: 15px;
      border-radius: 10px;
      padding: 12px 16px;
      border: 1px solid #dee2e6;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.15);
    }

    .btn-primary {
      background-color: var(--accent);
      border: none;
      width: 100%;
      border-radius: 10px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      color: #052e06;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 10px;
    }
    
    .btn-primary:hover {
      background-color: var(--accent-hover);
      color: #000;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(92, 250, 99, 0.2);
    }

    .alert-danger {
      background-color: #fee2e2;
      border: none;
      color: #991b1b;
      border-radius: 10px;
      font-size: 14px;
      padding: 12px 16px;
    }

    .back-link {
      margin-top: 24px;
      text-align: center;
      font-size: 14px;
    }
    
    .back-link a {
      color: var(--text-dim);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s;
    }
    
    .back-link a:hover {
      color: var(--text-main);
    }

    @media (max-width: 768px) {
      .auth-shell { 
        flex-direction: column; 
        width: 100%; 
        min-height: 100vh;
        border-radius: 0; 
      }
      .auth-aside { 
        width: 100%; 
        padding: 30px 24px;
        flex: 0 0 auto;
        min-height: auto;
        border-radius: 0 0 24px 24px;
      }
      .auth-aside::before { display: none; }
      .intro-text { display: none; }
      .auth-main { padding: 40px 24px; }
      .brand-logo { height: 40px; width: 40px; font-size: 16px; }
      .brand-text h1 { font-size: 20px; }   
    }
  </style>
</head>
<body>
  <div class="auth-shell">
    <aside class="auth-aside">
      <div class="brand-block">
        <div class="brand-logo">BS</div>
        <div class="brand-text">
          <h1><?= htmlspecialchars($store_name) ?></h1>
          <p>Account Recovery</p> 
        </div>
      </div>
      <div class="intro-text">
        Don't worry, it happens to the best of us. Enter your email address and we'll send you a code to reset your password.
      </div>
    </aside>

    <main class="auth-main">
      <div class="auth-header">
        <h2>Forgot Password?</h2>
        <p>No worries! Enter your email to get a reset code.</p>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($err) ?>
        </div>
      <?php endif; ?>

      <form method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='Sending...'">
        <div class="mb-4">
          <label class="form-label fw-semibold text-muted small text-uppercase">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <button type="submit" class="btn btn-primary">Send Code</button>
      </form>

      <div class="back-link">
        <a href="login.php">
            <i class="bi bi-arrow-left"></i> Back to Login
        </a>
      </div>
    </main>
  </div>
</body>
</html>