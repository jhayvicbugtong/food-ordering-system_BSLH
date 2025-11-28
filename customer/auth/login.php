<?php
// customer/auth/login.php
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL and starts session

// --- Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

// Determine where to redirect after login (for customers)
$next = isset($_GET['next']) && $_GET['next'] !== '' 
    ? $_GET['next'] 
    : $BASE_URL . '/customer/menu.php';

// --- CHECK IF ALREADY LOGGED IN ---
if (!empty($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: " . $BASE_URL . "/admin/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'staff') {
        header("Location: " . $BASE_URL . "/staff/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'customer') {
        header("Location: " . $next);
        exit;
    }
}

$err = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $email_val = $email;

    if ($email === '' || $pass === '') {
        $err = 'Please enter both email and password.';
    } else {
        // --- UPDATED QUERY: Removed "AND role = 'customer'" to allow all roles ---
        $stmt = $conn->prepare("SELECT user_id, first_name, password, role, is_active FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // 1. Verify Password
            if (password_verify($pass, $user['password'])) {
                
                // 2. CHECK IF ACCOUNT IS ACTIVE
                if ($user['is_active'] == 0) {
                    // If it's a customer, send them to verify email
                    if ($user['role'] === 'customer') {
                        $_SESSION['verify_email'] = $email;
                        header("Location: verify_email.php?next=" . urlencode($next));
                        exit;
                    } else {
                        // If Admin/Staff is inactive, show error (they don't use the OTP flow)
                        $err = 'Your account is deactivated. Please contact the administrator.';
                    }
                } else {
                    // 3. Login Success
                    $_SESSION['user_id'] = (int)$user['user_id'];
                    $_SESSION['name']    = $user['first_name'];
                    $_SESSION['email']   = $email;
                    $_SESSION['role']    = $user['role'];

                    // --- REDIRECT BASED ON ROLE ---
                    if ($user['role'] === 'admin') {
                        header("Location: " . $BASE_URL . "/admin/index.php");
                    } elseif ($user['role'] === 'staff') {
                        header("Location: " . $BASE_URL . "/staff/index.php");
                    } else {
                        // Default to Customer
                        header("Location: " . $next);
                    }
                    exit;
                }

            } else {
                $err = 'Invalid email or password.';
            }
        } else {
            $err = 'Invalid email or password.';
        }
        $stmt->close();
    }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>Sign in • <?= h($store_name) ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  
  <style>
    /* Matches register.php style perfectly */
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
      min-height: 550px;
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
      justify-content: space-between;
      position: relative;
      background-image: radial-gradient(circle at 10% 90%, rgba(92, 250, 99, 0.05) 0%, transparent 40%);
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
    .brand-block { display: flex; flex-direction: column; gap: 16px; }
    .brand-logo {
      height: 64px; width: 64px; border-radius: 12px; object-fit: cover;
      background: transparent; border: 2px solid rgba(255, 255, 255, 0.1);
    }
    .brand-text h1 { margin: 0; font-size: 24px; font-weight: 700; color: #fff; line-height: 1.2; }
    .brand-text p { margin: 4px 0 0; font-size: 14px; color: rgba(255,255,255,0.6); font-weight: 500; }
    .intro-text { margin-top: 32px; font-size: 14px; line-height: 1.6; color: rgba(255,255,255,0.8); }
    
    .auth-main {
      flex: 1; padding: 48px 50px; background: var(--bg-card);
      display: flex; flex-direction: column; justify-content: center;
    }
    .auth-header { margin-bottom: 32px; }
    .auth-header h2 { margin: 0; font-size: 28px; font-weight: 700; color: var(--text-main); }
    .auth-header p { margin: 8px 0 0; font-size: 15px; color: var(--text-dim); }

    .alert-danger { 
      font-size: 14px; padding: 12px 16px; border-radius: 10px; border: none;
      background-color: #fee2e2; color: #991b1b; margin-bottom: 24px;
    }
    .form-label { font-size: 13px; font-weight: 600; color: #343a40; margin-bottom: 6px; }
    .form-control { 
      font-size: 14px; border-radius: 10px; padding: 12px 14px; 
      border: 1px solid #dee2e6; transition: all 0.2s ease; background-color: #fcfcfc;
    }
    .form-control:focus {
      border-color: var(--accent); box-shadow: 0 0 0 3px rgba(92, 250, 99, 0.15); background-color: #fff;
    }
    .btn-login {
      background-color: var(--accent); border: 0; width: 100%; border-radius: 10px; padding: 14px;
      font-size: 16px; font-weight: 600; color: #052e06; cursor: pointer; margin-top: 8px;
      transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-login:hover { background-color: var(--accent-hover); transform: translateY(-2px); }
    
    .extra-links { text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-dim); }
    .extra-links a { color: var(--text-main); text-decoration: none; font-weight: 600; }
    .extra-links a:hover { text-decoration: underline; }
    .divider { margin: 0 8px; color: #dee2e6; }
    
    .nav-back { display: inline-block; margin-top: 12px; font-size: 13px; color: #adb5bd; text-decoration: none; }
    .nav-back:hover { color: #6c757d; }

    @media (max-width: 768px) {
      .auth-shell { flex-direction: column; width: 100%; min-height: 100vh; border-radius: 0; border: none; }
      .auth-aside { width: 100%; padding: 30px 24px; flex: 0 0 auto; min-height: auto; border-radius: 0 0 24px 24px; }
      .auth-aside::before { display: none; }
      .intro-text { display: none; }
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
            <p>Welcome back</p>
          </div>
        </div>
        <div class="intro-text">
          Sign in to access your order history, reorder your favorites, and manage your delivery address.
        </div>
      </div>
    </aside>

    <main class="auth-main">
      <div class="auth-header">
        <h2>Sign in</h2>
        <p>Please enter your details to continue.</p>
      </div>

      <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email address</label>
          <input class="form-control" type="email" name="email" value="<?= h($email_val) ?>" required placeholder="you@example.com">
        </div>
        
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <label class="form-label">Password</label>
            <a href="forgot_password.php" style="font-size:12px; text-decoration:none; color:#6c757d;">Forgot password?</a>
          </div>
          <input class="form-control" type="password" name="password" required placeholder="Enter your password">
        </div>

        <button class="btn-login" type="submit">Sign in</button>

        <div class="extra-links">
          Don't have an account? 
          <a href="register.php?next=<?= urlencode($next) ?>">Create account</a>
          <br>
          <a href="<?= htmlspecialchars($BASE_URL) ?>/index.php" class="nav-back">← Back to Home Page</a>
        </div>
      </form>
    </main>
  </div>

</body>
</html>