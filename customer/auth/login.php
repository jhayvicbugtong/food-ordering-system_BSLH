<?php
// customer/auth/login.php
session_start();

// Where to go after login
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : '/food-ordering-system_BSLH/customer/menu.php';

// DB
require_once __DIR__ . '/../../includes/db_connect.php';

// Already logged in? go
if (!empty($_SESSION['customer_id'])) {
  header("Location: ".$next);
  exit;
}

$err = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
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
      $stored = (string)$user['password'];

      // Accept both hashed and legacy plaintext
      if ($stored && str_starts_with($stored, '$')) {
        $ok = password_verify($pass, $stored);
      } else {
        $ok = hash_equals($stored, $pass);
      }
    }

    if ($ok) {
      $_SESSION['customer_id']    = (int)$user['id'];
      $_SESSION['customer_name']  = $user['name'];
      $_SESSION['customer_email'] = $user['email'];
      $_SESSION['customer_role']  = 'customer';
      header("Location: ".$next);
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
  <style>
    body{background:#eef2f7;}
    .auth-shell{max-width:980px;margin:48px auto;padding:0 16px;}
    .auth-card{display:grid;grid-template-columns:340px 1fr;background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.08);overflow:hidden;}
    .auth-left{background:#0f172a;color:#e2e8f0;padding:28px}
    .brand{display:flex;align-items:center;gap:12px;margin-bottom:16px}
    .brand-badge{width:36px;height:36px;border-radius:10px;background:radial-gradient(circle at 30% 30%,#8bff89,#3af13a 40%,#1ea21e 100%);display:flex;align-items:center;justify-content:center;color:#0b2b0b;font-weight:700}
    .auth-left h3{margin:6px 0 2px;font-size:18px;color:#e2e8f0}
    .auth-left small{color:#94a3b8}
    .auth-right{padding:32px}
    .auth-right h2{margin:0 0 4px}
    .auth-right p{margin:0 0 16px;color:#64748b}
    .form-group{margin-bottom:12px}
    .form-control{width:100%;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;font:inherit}
    .btn-primary{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:12px 16px;border:0;border-radius:12px;background:#65f457;color:#0b2b0b;font-weight:600;cursor:pointer;box-shadow:0 8px 24px rgba(101,244,87,.35)}
    .btn-primary:hover{filter:brightness(.98)}
    .auth-links{margin-top:10px;font-size:14px}
    .auth-links a{color:#155e75;text-decoration:none}
    .error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;padding:8px 12px;border-radius:10px;margin-bottom:10px}
    @media(max-width:860px){.auth-card{grid-template-columns:1fr}.auth-left{display:none}}
  </style>
</head>
<body>
  <div class="auth-shell">
    <div class="auth-card">
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
        <div class="auth-note" style="margin-top:auto;font-size:13px;color:#cbd5e1">
          Reminder: Use your customer account. Staff/Admin should use the staff portal.
        </div>
      </div>

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
            <a href="/food-ordering-system_BSLH/index.php">← Back to Customer Page</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
