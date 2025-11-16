<?php
// customer/auth/login.php

// --- THIS IS THE CORRECTED LINE ---
require_once __DIR__ . '/../../includes/db_connect.php';
session_start();

// --- ADDED THIS BLOCK ---
// Dynamically get the base URL and the 'next' redirect parameter
$BASE_URL = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($BASE_URL === '/') $BASE_URL = '';
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : $BASE_URL . '/customer/menu.php';

// If user is already logged in, send them to their destination
if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
  header("Location: ".$next);
  exit;
}

// Helper function for sanitizing output
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
// --- END ADDED BLOCK ---


$error = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $emailVal = $email;

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // NEW SCHEMA: Use user_id, first_name, and check for hashed password
        // This query is already correct for your new schema!
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
                // NEW SCHEMA: Assumes password_verify for hashes.
                if (strpos($stored, '$') === 0) {
                    $ok = password_verify($password, $stored);
                } else {
                    // Fallback for any legacy plaintext passwords
                    $ok = hash_equals($stored, $password);
                }
            }
        }

        if ($ok) {
            $role = strtolower(trim((string)($user['role'] ?? '')));

            // --- FIX: This login is for CUSTOMERS only ---
            if ($role !== 'customer') {
                // This portal is only for customers
                $error = 'Unauthorized role for this portal. Please use the Staff/Admin login.';
            } else {
                // NEW SCHEMA: Set session keys using user_id and first_name
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['name']    = (string)$user['first_name'];
                $_SESSION['role']    = $role;
                $_SESSION['email']   = (string)$user['email']; // Also useful to store email

                // --- FIX: Redirect to the 'next' URL, not a hardcoded one ---
                header("Location: " . $next); // Was: header("Location: ../menu.php");
                exit();
            }
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
  <title>Sign in | Bente Sais Lomi House</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* ... [your existing CSS styles] ... */
    :root {
      --bg-dark: #212529;
      --bg-card: #ffffff;
      --text-light: #f8f9fa;
      --text-dim: #adb5bd;
      --accent: #5cfa63; /* avocado green */
      --border-card: rgba(0,0,0,0.08);
      --radius-lg: 16px;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f5f7fa;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #212529;
    }

    .auth-shell {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      box-shadow: 0 20px 60px rgba(0,0,0,0.18);
      display: flex;
      width: 900px;
      max-width: 95%;
      overflow: hidden;
      border: 1px solid var(--border-card);
    }

    .auth-aside {
      background-color: var(--bg-dark);
      color: var(--text-light);
      padding: 32px 28px;
      width: 40%;
      min-width: 260px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
    }

    .auth-aside::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      height: 4px;
      width: 100%;
      background: var(--accent);
    }

    .brand-block {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .brand-logo {
      height: 36px;
      width: 36px;
      border-radius: 8px;
      background: radial-gradient(circle at 30% 30%, #5cfa63 0%, #1c1f1f 70%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: #000;
      font-size: 14px;
      line-height: 1;
      box-shadow: 0 8px 20px rgba(92,250,99,0.5);
    }

    .brand-text h1 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
      color: #fff;
      line-height: 1.2;
    }

    .brand-text p {
      margin: 2px 0 0;
      font-size: 13px;
      line-height: 1.4;
      color: var(--text-dim);
    }

    .aside-bottom {
      font-size: 12px;
      line-height: 1.4;
      color: var(--text-dim);
    }

    .aside-bottom strong { color: #fff; font-weight: 500; }

    .auth-main {
      flex: 1;
      padding: 32px;
      background: var(--bg-card);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-header { margin-bottom: 24px; }

    .auth-header h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
      color: #212529;
      line-height: 1.2;
    }

    .auth-header p {
      margin: 6px 0 0;
      font-size: 14px;
      color: #6c757d;
      line-height: 1.4;
    }

    .alert-danger { font-size: 14px; padding: 10px 12px; border-radius: 8px; }

    .form-label { font-size: 13px; font-weight: 500; color: #343a40; margin-bottom: 4px; }

    .form-control { font-size: 14px; border-radius: 8px; padding: 10px 12px; }

    .btn-login {
      background-color: var(--accent);
      border: 0;
      width: 100%;
      border-radius: 8px;
      padding: 10px 12px;
      font-size: 15px;
      font-weight: 600;
      color: #000;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(92,250,99,0.4);
    }
    .btn-login:hover { filter: brightness(.92); }

    .back-link { text-align: center; margin-top: 20px; font-size: 13px; }
    .back-link a { color: #6c757d; text-decoration: none; }
    .back-link a:hover { color: #000; }

    @media (max-width: 700px) {
      .auth-shell { flex-direction: column; width: 420px; max-width: 94%; }
      .auth-aside { width: 100%; min-width: 100%; border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
      .auth-main { width: 100%; }
      .site-footer { margin-top: 24px; }
    }
  </style>
</head>
<body>

<div class="auth-shell">
  
  <aside class="auth-aside">
    <div>
      <div class="brand-block">
        <div class="brand-logo">BS</div>
        <div class="brand-text">
          <h1>Bente Sais Lomi House</h1>
          <p>Customer Portal</p> 
        </div>
      </div>

      <div style="margin-top:24px; font-size:13px; line-height:1.5; color:#dee2e6;">
        Sign in to place orders, track deliveries, and view your history.
      </div>
    </div>

    <div class="aside-bottom">
      <div><strong>Note:</strong> Staff and Admins must use the Staff Portal.</div>
    </div>
  </aside>

  <main class="auth-main">
    <div class="auth-header">
      <h2>Customer Sign in</h2>
      <p>Use your registered email and password.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input
          type="email"
          name="email"
          class="form-control"
          required
          value="<?= htmlspecialchars($emailVal) ?>"
          placeholder="you@example.com">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input
          type="password"
          name="password"
          class="form-control"
          required
          placeholder="••••••••">
      </div>

      <button type="submit" class="btn-login">Continue</button>
    </form>

    <div class="back-link" style="text-align: center; margin-top: 20px; font-size: 13px;">
      Don't have an account? 
      <a href="register.php?next=<?= h(urlencode($next)) ?>" style="color:#0b2b0b; font-weight: 600;">Register here</a>
      <div style="margin-top: 12px;">
        <a href="../../index.php" style="color: #6c757d;">← Back to Home Page</a>
      </div>
    </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>