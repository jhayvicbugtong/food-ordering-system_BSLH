<?php
// customer/auth/verify_email.php
require_once __DIR__ . '/../../includes/db_connect.php'; 

// --- Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

$next = isset($_GET['next']) ? $_GET['next'] : $BASE_URL . '/customer/menu.php';
$email = $_SESSION['verify_email'] ?? ''; 
$err = '';
$success = '';

if (!$email) {
    // If they accessed this page directly without registering, send them back to login
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code'] ?? '');
    
    if (empty($entered_code)) {
        $err = "Please enter the verification code.";
    } else {
        // Verify Code
        $stmt = $conn->prepare("SELECT user_id, first_name, role, verification_code FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['verification_code'] === $entered_code) {
                // CODE MATCHES!
                $stmt->close();
                
                // 1. Update User: Set Active, Clear Code, Set Verified Date
                $update = $conn->prepare("UPDATE users SET is_active = 1, verification_code = NULL, email_verified_at = NOW() WHERE user_id = ?");
                $update->bind_param('i', $user['user_id']);
                
                if ($update->execute()) {
                    // 2. Log them in automatically
                    $_SESSION['user_id'] = (int)$user['user_id'];
                    $_SESSION['name']    = $user['first_name'];
                    $_SESSION['email']   = $email;
                    $_SESSION['role']    = $user['role'];
                    
                    // 3. Clean up temp session
                    unset($_SESSION['verify_email']);
                    
                    // 4. Redirect
                    header("Location: " . $next);
                    exit;
                } else {
                    $err = "Database error. Please try again.";
                }
                $update->close();
            } else {
                $err = "Invalid verification code. Please check your email.";
            }
        } else {
            $err = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Verify Email • <?= htmlspecialchars($store_name) ?></title>
  
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
      min-height: 500px;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.04);
    }
    .auth-aside {
      background-color: var(--bg-dark);
      color: #f8f9fa;
      padding: 48px 40px;
      width: 40%;
      min-width: 300px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      background-image: radial-gradient(circle at 50% 100%, rgba(92, 250, 99, 0.05) 0%, transparent 60%);
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

    /* UPDATED: Logo Styling */
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
    .auth-main {
      flex: 1;
      padding: 48px 50px;
      background: var(--bg-card);
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
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
      max-width: 350px;
      margin-left: auto;
      margin-right: auto;
    }
    .alert-danger { 
      font-size: 14px; 
      padding: 12px 16px; 
      border-radius: 10px; 
      background-color: #fee2e2; 
      color: #991b1b; 
      border: none;
      margin-bottom: 24px;
    }
    
    .otp-input {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 8px;
        text-align: center;
        padding: 16px;
        border-radius: 12px;
        border: 2px solid #dee2e6;
        width: 100%;
        max-width: 300px;
        margin: 0 auto 24px auto;
        transition: all 0.2s ease;
    }
    
    .otp-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.15);
        outline: none;
    }
    
    .btn-login {
      background-color: var(--accent);
      border: 0;
      width: 100%;
      max-width: 300px;
      margin: 0 auto;
      border-radius: 10px;
      padding: 14px 12px;
      font-size: 16px;
      font-weight: 600;
      color: #052e06;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-login:hover { 
      background-color: var(--accent-hover);
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(92, 250, 99, 0.25);
    }

    .change-email-link {
        display: inline-block;
        margin-top: 24px;
        font-size: 14px;
        color: var(--text-dim);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .change-email-link:hover {
        color: #000;
        text-decoration: underline;
    }

    @media (max-width: 768px) {
      .auth-shell { 
        flex-direction: column; 
        width: 100%; 
        min-height: 100vh;
        border-radius: 0;
        border: none;
      }
      .auth-aside { 
        width: 100%; 
        padding: 30px 24px;
        flex: 0 0 auto;
        min-height: auto;
        border-radius: 0 0 24px 24px;
        display: flex !important; /* Ensure it's visible */
      }
      .auth-aside::before { display: none; }
      .intro-text { display: none; } /* Hide text on mobile */
      .auth-main { padding: 40px 24px; }
      .brand-logo { height: 50px; width: 50px; }
      .brand-text h1 { font-size: 20px; }
    }
  </style>
</head>
<body>

  <div class="auth-shell">
    
    <aside class="auth-aside">
      <div class="brand-block">
        <img src="<?= htmlspecialchars($BASE_URL) ?>/uploads/logo/logo_transparent.png" alt="Store Logo" class="brand-logo">
        
        <div class="brand-text">
          <h1><?= htmlspecialchars($store_name) ?></h1>
          <p>Security Verification</p>
        </div>
      </div>
      <div class="intro-text">
        We've sent a 6-digit verification code to<br>
        <strong style="color:#fff; font-size:15px;"><?= htmlspecialchars($email) ?></strong>
      </div>
    </aside>

    <main class="auth-main">
      <div class="auth-header">
        <h2>Enter Code</h2>
        <p>Please enter the code from the email we just sent you to verify your account.</p>
      </div>

      <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      
      <form method="post">
        <input class="form-control otp-input" type="text" name="verification_code" placeholder="******" maxlength="6" required autofocus autocomplete="off">
        <button class="btn-login" type="submit">Verify Account</button>
        
        <div>
            <a href="register.php" class="change-email-link">Incorrect email? Register again.</a>
        </div>
      </form>
    </main>
  </div>

</body>
</html>