<?php
// customer/auth/register.php
session_start();

$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : '/food-ordering-system_BSLH/customer/menu.php';

require_once __DIR__ . '/../../includes/db_connect.php';

if (!empty($_SESSION['customer_id'])) {
  header("Location: ".$next);
  exit;
}

$err = '';
$vals = ['name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $pass2 = $_POST['confirm_password'] ?? '';

  $vals = ['name'=>$name,'email'=>$email,'phone'=>$phone];

  if ($name === '' || $email === '' || $pass === '') {
    $err = 'Please complete all required fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Please enter a valid email.';
  } elseif ($pass !== $pass2) {
    $err = 'Passwords do not match.';
  } else {
    // unique email across users
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    if ($exists) {
      $err = 'Email already registered. Try signing in.';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('sss', $name, $email, $hash);
      if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        // auto-login
        $_SESSION['customer_id']    = (int)$id;
        $_SESSION['customer_name']  = $name;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_role']  = 'customer';
        header("Location: ".$next);
        exit;
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
  <title>Create account • Bente Sais Lomi House</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="/food-ordering-system_BSLH/assets/css/customer.css"/>
  <style>
    body{background:#eef2f7;}
    .auth-shell{max-width:980px;margin:48px auto;padding:0 16px;}
    .auth-card{display:grid;grid-template-columns:340px 1fr;background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.08);overflow:hidden;}
    .auth-left{background:#0f172a;color:#e2e8f0;padding:28px;}
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
          Create an account to save your details and track orders easily.
        </p>
      </div>

      <div class="auth-right">
        <h2>Create account</h2>
        <p>It only takes a minute.</p>

        <?php if ($err): ?><div class="error"><?= h($err) ?></div><?php endif; ?>

        <form method="post" novalidate>
          <div class="form-group">
            <label>Full name</label>
            <input class="form-control" type="text" name="full_name" value="<?= h($vals['name']) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="email" name="email" value="<?= h($vals['email']) ?>" required>
          </div>
          <div class="form-group">
            <label>Phone (optional)</label>
            <input class="form-control" type="text" name="phone" value="<?= h($vals['phone']) ?>" placeholder="09xx xxx xxxx">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <div class="form-group">
            <label>Confirm password</label>
            <input class="form-control" type="password" name="confirm_password" required>
          </div>

          <button class="btn-primary" type="submit">Create account</button>

          <div class="auth-links">
            Already have an account?
            <a href="login.php?next=<?= urlencode($next) ?>">Sign in</a>
            &nbsp; • &nbsp;
            <a href="/food-ordering-system_BSLH/index.php">← Back to Customer Page</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
