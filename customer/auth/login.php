<?php
// customer/auth/login.php
session_start();

// --- Build base URL dynamically (works even if the folder name changes) ---
$BASE_URL = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); // e.g. /food-ordering-system_BSLH
if ($BASE_URL === '/') $BASE_URL = '';

// Where to go after login
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : $BASE_URL . '/customer/menu.php';

// DB
require_once __DIR__ . '/../../includes/db_connect.php';

// Already logged in? go
if (!empty($_SESSION['customer_id'])) {
  header('Location: ' . $next);
  exit;
}

$err = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');
  $email_val = $email;

  if ($email === '' || $pass === '') {
    $err = 'Please enter your email and password.';
  } else {
    // Only allow CUSTOMER role here (staff/admin have their own portal)
    $sql = "SELECT id, name, email, password, role
            FROM users
            WHERE LOWER(email) = LOWER(?) AND role = 'customer'
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res  = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    $ok = false;
    if ($user) {
      $stored = (string)($user['password'] ?? '');
      // Accept hashed (bcrypt/argon) OR legacy plaintext
      if ($stored !== '' && $stored[0] === '$') {
        $ok = password_verify($pass, $stored);
      } else {
        $ok = hash_equals($stored, $pass);
      }
    }

    if ($ok) {
      $_SESSION['customer_id']    = (int)$user['id'];
      $_SESSION['customer_name']  = (string)$user['name'];
      $_SESSION['customer_email'] = (string)$user['email'];
      $_SESSION['customer_role']  = 'customer';
      header('Location: ' . $next);
      exit;
    } else {
      $err = 'Invalid email or password, or this email is not a customer account.';
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
  <title>Customer Sign in • Bente Sais Lomi House</title>

  <!-- Optional site css -->
  <link rel="stylesheet" href="<?= h($BASE_URL) ?>/assets/css/customer.css"/>

  <style>
    :root{
      --bg-dark:#0f172a;
      --bg-card:#ffffff;
      --text-light:#e2e8f0;
      --text-dim:#94a3b8;
      --accent:#65f457;
      --radius:14px;
      --border:1px solid rgba(0,0,0,.08);
    }
    body{background:#eef2f7;margin:0;font-family:Inter,system-ui,Segoe UI,Arial}
    .auth-shell{max-width:980px;margin:48px auto;padding:0 16px}
    .auth-card{display:grid;grid-template-columns:340px 1fr;background:#fff;border-radius:var(--radius);box-shadow:0 10px 30px rgba(0,0,0,.08);overflow:hidden;border:var(--border)}
    .auth-left{background:var(--bg-dark);color:var(--text-light);padding:28px;position:relative}
    .auth-left::before{content:"";position:absolute;top:0;left:0;height:4px;width:100%;background:var(--accent)}
    .brand{display:flex;align-items:center;gap:12px;margin-bottom:16px}
    .brand-badge{width:36px;height:36px;border-radius:10px;background:radial-gradient(circle at 30% 30%,#8bff89,#3af13a 40%,#1ea21e 100%);display:flex;align-items:center;justify-content:center;color:#0b2b0b;font-weight:700}
    .auth-left h3{margin:6px 0 2px;font-size:18px}
    .auth-left small{color:var(--text-dim)}
    .auth-right{padding:32px;background:#fff}
    .auth-right h2{margin:0 0 4px}
    .auth-right p{margin:6px 0 16px;color:#64748b}
    .form-group{margin-bottom:12px}
    .form-control{width:100%;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;font:inherit}
    .btn-primary{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:12px 16px;border:0;border-radius:12px;background:var(--accent);color:#0b2b0b;font-weight:600;cursor:pointer;box-shadow:0 8px 24px rgba(101,244,87,.35)}
    .btn-primary:hover{filter:brightness(.98)}
    .auth-links{margin-top:10px;font-size:14px}
    .auth-links a{color:#155e75;text-decoration:none}
    .error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;padding:10px 12px;border-radius:10px;margin-bottom:12px}
    @media(max-width:860px){.auth-card{grid-template-columns:1fr}.auth-left{display:none}}
  </style>
</head>
<body>
  <div class="auth-shell">
    <div class="auth-card">
      <!-- LEFT / BRAND PANEL -->
      <div class="auth-left">
        <div class="brand">
          <div class="brand-badge">BS</div>
          <div>
            <h3>Bente Sais Lomi House</h3>
            <small>Customer Portal</small>
          </div>
        </div>
        <p style="margin-top:10px">
          Order your favorites, track deliveries, and save your address for faster checkout.
        </p>
        <div style="margin-top:auto;font-size:13px;color:#cbd5e1">
          Reminder: Use your customer account. Staff/Admin should use the staff portal.
        </div>
      </div>

      <!-- RIGHT / LOGIN FORM -->
      <div class="auth-right">
        <h2>Sign in</h2>
        <p>Use your registered email and password.</p>

        <?php if ($err): ?><div class="error"><?= h($err) ?></div><?php endif; ?>

        <form method="post" novalidate>
          <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="email" name="email" value="<?= h($email_val) ?>" placeholder="you@example.com" required autofocus>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn-primary" type="submit">Continue</button>

          <div class="auth-links">
            New here?
            <a href="register.php?next=<?= urlencode($next) ?>">Create an account</a>
            &nbsp; • &nbsp;
            <a href="<?= h($BASE_URL) ?>/index.php">← Back to Customer Page</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
