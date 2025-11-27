<?php
// customer/auth/login.php

require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL

// --- Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

// Default destination for customers
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : $BASE_URL . '/customer/menu.php';

// If user is already logged in, check role and redirect accordingly
if (!empty($_SESSION['user_id'])) {
    $r = $_SESSION['role'] ?? 'customer';
    if ($r === 'admin') {
        header("Location: " . $BASE_URL . "/admin/index.php");
    } elseif ($r === 'staff') {
        header("Location: " . $BASE_URL . "/staff/index.php");
    } else {
        header("Location: " . $next);
    }
    exit;
}

// Helper function for sanitizing output
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$error = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $emailVal = $email;

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // Fetch user details including role
        $stmt = $conn->prepare("SELECT user_id, first_name, email, password, role FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        $ok = false;
        if ($user) {
            $stored = (string)($user['password'] ?? '');

            if ($stored === '' || $stored === 'NULL') {
                $error = 'This account has no password set. Ask an admin to set one.';
            } else {
                // Verify hash or fallback to plaintext
                if (strpos($stored, '$') === 0) {
                    $ok = password_verify($password, $stored);
                } else {
                    $ok = hash_equals($stored, $password);
                }
            }
        }

        if ($ok) {
            $role = strtolower(trim((string)($user['role'] ?? '')));

            // Set session variables
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['name']    = (string)$user['first_name'];
            $_SESSION['role']    = $role;
            $_SESSION['email']   = (string)$user['email'];

            // --- Redirect based on Role ---
            if ($role === 'admin') {
                header("Location: " . $BASE_URL . "/admin/index.php");
            } elseif ($role === 'staff') {
                header("Location: " . $BASE_URL . "/staff/index.php");
            } else {
                // Default for customers
                header("Location: " . $next);
            }
            exit();

        } elseif (!$error) {
            $error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign in | <?= h($store_name) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

  <style>
    :root {
      --bg-dark: #212529;
      --bg-card: #ffffff;
      --text-main: #212529;
      --text-dim: #6c757d;
      --accent: #5cfa63; /* avocado green */
      --accent-hover: #4ae052;
      --border-color: #e9ecef;
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

    /* Left Sidebar */
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
      font-size: 14px; 
      font-weight: 600; 
      color: #343a40; 
      margin-bottom: 8px; 
    }

    .form-control { 
      font-size: 15px; 
      border-radius: 10px; 
      padding: 12px 16px; 
      border: 1px solid #dee2e6;
      transition: all 0.2s ease;
      background-color: #fcfcfc;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.15);
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
      margin-top: 12px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-login:hover { 
      background-color: var(--accent-hover);
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(92, 250, 99, 0.25);
    }
    .btn-login:active {
      transform: translateY(0);
    }

    .back-link { text-align: center; margin-top: 30px; font-size: 14px; color: var(--text-dim); }
    .back-link a { color: var(--text-main); text-decoration: none; font-weight: 600; transition: color 0.2s; }
    .back-link a:hover { color: #000; text-decoration: underline; }
    .nav-back { display: inline-block; margin-top: 16px; font-size: 13px; color: #adb5bd; text-decoration: none; transition: color 0.2s;}
    .nav-back:hover { color: #6c757d; }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .auth-shell { 
        flex-direction: column; 
        width: 100%; 
        min-height: 100vh;
        border-radius: 0;
        box-shadow: none;
        border: none;
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
        <h1><?= h($store_name) ?></h1>
        <p>Customer Portal</p> 
      </div>
    </div>
    <div class="intro-text">
      Sign in to access your account, track your current orders, view history, or update your profile.
    </div>
  </aside>

  <main class="auth-main">
    <div class="auth-header">
      <h2>Welcome back</h2>
      <p>Please enter your details to sign in.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-4">
        <label class="form-label">Email</label>
        <input
          type="email"
          name="email"
          class="form-control"
          required
          value="<?= htmlspecialchars($emailVal) ?>"
          placeholder="Enter your email">
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <input
          type="password"
          name="password"
          class="form-control"
          required
          placeholder="••••••••">
      </div>
      <a href="forgot_password.php" class="small text-decoration-none text-muted">Forgot password?</a>

      <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="back-link">
      Don't have an account? 
      <a href="register.php?next=<?= h(urlencode($next)) ?>">Register</a>
      <br>
      <a href="<?= htmlspecialchars($BASE_URL) ?>/index.php" class="nav-back">← Back to Home Page</a>
    </div>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>