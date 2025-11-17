<?php
// --- FIX 1: Added a '/' before ../ ---
require_once __DIR__ . '/../includes/db_connect.php';

$error = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $emailVal = $email;

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // --- FIX 2: Use new schema columns (user_id, first_name) ---
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
                // Use new schema's hashed password
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

            // Updated to include 'driver' as a valid staff role
            if ($role !== 'admin' && $role !== 'staff' && $role !== 'driver') {
                // This portal is only for staff/admin
                $error = 'Unauthorized role for this portal. Please use the customer login.';
            } else {
                // --- FIX 3: Set session keys using new column names ---
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['name']    = (string)$user['first_name'];
                $_SESSION['role']    = $role;
                $_SESSION['email']   = (string)$user['email'];

                // --- FIXED: Use $BASE_URL for redirect ---
                if ($role === 'admin') {
                    header("Location: " . $BASE_URL . "/admin/index.php");
                } else { // staff or driver
                    header("Location: " . $BASE_URL . "/staff/index.php");
                }
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
          <p>Staff & Admin Portal</p>
        </div>
      </div>

      <div style="margin-top:24px; font-size:13px; line-height:1.5; color:#dee2e6;">
        Streamline orders, track payments, and manage
        kitchen / delivery flow in one dashboard.
      </div>
    </div>

    <div class="aside-bottom">
      <div><strong>Reminder:</strong> Authorized personnel only.</div>
      <div>All activity is logged.</div>
    </div>
  </aside>

  <main class="auth-main">
    <div class="auth-header">
      <h2>Sign in</h2>
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

    <div class="back-link">
      <a href="<?= htmlspecialchars($BASE_URL) ?>/index.php">← Back to Customer Page</a>
    </div>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>