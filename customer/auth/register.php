<?php
// customer/auth/register.php
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL and starts session

// --- Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// --- Use the $BASE_URL from db_connect.php ---
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : $BASE_URL . '/customer/menu.php';

// Check if already logged in
if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
  header("Location: ".$next);
  exit;
}

$err = '';
$vals = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name  = trim($_POST['last_name'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $phone      = trim($_POST['phone'] ?? '');
  $pass       = $_POST['password'] ?? '';
  $pass2      = $_POST['confirm_password'] ?? '';

  $vals = ['first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone'=>$phone];

  if ($first_name === '' || $last_name === '' || $email === '' || $pass === '') {
    $err = 'Please complete all required fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Please enter a valid email.';
  } elseif (strlen($pass) < 6) {
    $err = 'Password must be at least 6 characters long.';
  } elseif ($pass !== $pass2) {
    $err = 'Passwords do not match.';
  } else {
    // --- UPDATED CHECK LOGIC ---
    // Check if user exists AND get their status
    $stmt = $conn->prepare("SELECT user_id, is_active FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_user = $result->fetch_assoc();
    $stmt->close();

    // If user exists AND is already verified (is_active = 1), block them.
    if ($existing_user && $existing_user['is_active'] == 1) {
      $err = 'Email already registered. Try signing in.';
    } else {
      // Proceed if it's a new user OR an unverified existing user (retry)
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      
      // 1. Generate 6-digit OTP
      $otp = rand(100000, 999999);

      $success_db = false;

      if ($existing_user) {
          // CASE A: User exists but is UNVERIFIED (Retry). Update their info and new OTP.
          $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, password=?, verification_code=? WHERE user_id=?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param('sssssi', $first_name, $last_name, $phone, $hash, $otp, $existing_user['user_id']);
          if ($stmt->execute()) {
              $success_db = true;
          }
          $stmt->close();
      } else {
          // CASE B: Brand new user. Insert them.
          $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role, is_active, verification_code) 
                  VALUES (?, ?, ?, ?, ?, 'customer', 0, ?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param('ssssss', $first_name, $last_name, $email, $phone, $hash, $otp);
          if ($stmt->execute()) {
              $success_db = true;
          }
          $stmt->close();
      }
      
      // If DB operation was successful, send the email
      if ($success_db) {
        
        // 3. Send OTP via Email (PHPMailer)
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

            $mail->setFrom('bentesaislomi.26@gmail.com', $store_name);
            $mail->addAddress($email, "$first_name $last_name");

            $mail->isHTML(true);
            $mail->Subject = 'Verify your email address';
            $mail->Body    = "
                <h2>Welcome to $store_name!</h2>
                <p>Thank you for signing up. Please use the verification code below to complete your registration:</p>
                <h1 style='background: #f0f0f0; padding: 10px; display: inline-block; letter-spacing: 5px;'>$otp</h1>
                <p>This code is valid for your account verification.</p>
            ";
            $mail->AltBody = "Your verification code is: $otp";

            $mail->send();

            // 4. Redirect to Verification Page
            // Store email in session to pre-fill the next page
            $_SESSION['verify_email'] = $email;
            header("Location: verify_email.php?next=" . urlencode($next));
            exit;

        } catch (Exception $e) {
            $err = 'Account created, but email failed to send. Contact admin.';
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

      } else {
        $err = 'Something went wrong. Please try again.';
      }
    }
  }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Create account • <?= h($store_name) ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  
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

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
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
      background-image: radial-gradient(circle at 90% 90%, rgba(92, 250, 99, 0.05) 0%, transparent 40%);
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

    /* UPDATED: Matches header style logic */
    .brand-logo {
      height: 64px;
      width: 64px;
      border-radius: 12px;
      object-fit: cover;
      background: transparent;
      border: 2px solid rgba(255, 255, 255, 0.1);
      box-shadow: none;
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
        color: rgba(255,255,255,0.8);
    }

    .aside-bottom {
      font-size: 13px;
      line-height: 1.5;
      color: rgba(255,255,255,0.5);
    }

    .aside-bottom strong { color: #fff; font-weight: 600; }

    /* Main Content */
    .auth-main {
      flex: 1;
      padding: 48px 50px;
      background: var(--bg-card);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-header { margin-bottom: 28px; }

    .auth-header h2 {
      margin: 0;
      font-size: 28px;
      font-weight: 700;
      color: var(--text-main);
      line-height: 1.2;
    }

    .auth-header p {
      margin: 8px 0 0;
      font-size: 15px;
      color: var(--text-dim);
    }

    .alert-danger { 
      font-size: 14px; 
      padding: 12px 16px; 
      border-radius: 10px; 
      border: none;
      background-color: #fee2e2;
      color: #991b1b;
      margin-bottom: 24px;
    }

    .form-label { 
      font-size: 13px; 
      font-weight: 600; 
      color: #343a40; 
      margin-bottom: 6px; 
    }

    .form-control { 
      font-size: 14px; 
      border-radius: 10px; 
      padding: 10px 14px; 
      border: 1px solid #dee2e6;
      transition: all 0.2s ease;
      background-color: #fcfcfc;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(92, 250, 99, 0.15);
      background-color: #fff;
    }

    .btn-login {
      background-color: var(--accent);
      border: 0;
      width: 100%;
      border-radius: 10px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      color: #052e06;
      cursor: pointer;
      margin-top: 16px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-login:hover { 
      background-color: var(--accent-hover);
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(92, 250, 99, 0.25);
    }
    .btn-login:active { transform: translateY(0); }

    .back-link { text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-dim); }
    .back-link a { color: var(--text-main); text-decoration: none; font-weight: 700; transition: color 0.2s; }
    .back-link a:hover { color: #000; text-decoration: underline; }
    
    .nav-back { display: inline-block; margin-top: 12px; font-size: 13px; color: #adb5bd; text-decoration: none; transition: color 0.2s;}
    .nav-back:hover { color: #6c757d; }

    @media (max-width: 768px) {
      .auth-shell { 
        flex-direction: column; 
        width: 100%; 
        min-height: 100vh;
        border-radius: 0;
        border: none;
        box-shadow: none;
      }
      /* Mobile adjustment: Make aside visible but smaller like Login */
      .auth-aside { 
        width: 100%; 
        padding: 30px 24px;
        flex: 0 0 auto;
        min-height: auto;
        border-radius: 0 0 24px 24px;
      }
      .auth-aside::before { display: none; }
      /* Hide the description text on mobile to save space */
      .intro-text { display: none; }
      .aside-bottom { display: none; }
      
      .auth-main { padding: 40px 24px; }
      .brand-logo { height: 50px; width: 50px; }
      .brand-text h1 { font-size: 20px; }
    }
  </style>
</head>
<body>

  <div class="auth-shell">
    
    <aside class="auth-aside">
      <div>
        <div class="brand-block">
          <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/logo/logo_transparent.png" alt="Store Logo" class="brand-logo">
          
          <div class="brand-text">
            <h1><?= h($store_name) ?></h1>
            <p>Join the family</p>
          </div>
        </div>

        <div class="intro-text">
          Create an account to save your delivery details, track your orders, and enjoy faster checkout.
        </div>
      </div>

      <div class="aside-bottom">
        <div><strong>Note:</strong> All fields marked with * are required.</div>
      </div>
    </aside>

    <main class="auth-main">
      <div class="auth-header">
        <h2>Create account</h2>
        <p>It only takes a minute.</p>
      </div>

      <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>

      <form method="post" novalidate>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">First name *</label>
            <input class="form-control" type="text" name="first_name" value="<?= h($vals['first_name']) ?>" required placeholder="Juan">
          </div>
          <div class="col-md-6">
            <label class="form-label">Last name *</label>
            <input class="form-control" type="text" name="last_name" value="<?= h($vals['last_name']) ?>" required placeholder="Dela Cruz">
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Email address *</label>
          <input class="form-control" type="email" name="email" value="<?= h($vals['email']) ?>" required placeholder="you@example.com">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone (optional)</label>
          <input class="form-control" type="text" name="phone" value="<?= h($vals['phone']) ?>" placeholder="0912 345 6789">
        </div>
        <div class="mb-3">
          <label class="form-label">Password *</label>
          <input class="form-control" type="password" name="password" placeholder="Min. 6 characters" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm password *</label>
          <input class="form-control" type="password" name="confirm_password" placeholder="Repeat password" required>
        </div>

        <button class="btn-login" type="submit">Create Account</button>

        <div class="back-link">
          Already have an account?
          <a href="login.php?next=<?= urlencode($next) ?>">Sign in</a>
          <br>
          <a href="<?= htmlspecialchars($BASE_URL) ?>/index.php" class="nav-back">← Back to Home Page</a>
        </div>
      </form>
    </main>
  </div>

</body>
</html>