<?php
// customer/auth/register.php
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL and starts session

// --- NEW: Use the $BASE_URL from db_connect.php ---
$next = isset($_GET['next']) && $_GET['next'] !== ''
  ? $_GET['next']
  : $BASE_URL . '/customer/menu.php';
// --- END NEW ---

// --- MODIFIED: Check new session keys ---
if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
  header("Location: ".$next);
  exit;
}

$err = '';
// --- MODIFIED: Use new schema fields ---
$vals = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // --- MODIFIED: Use new field names ---
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
      
      // --- MODIFIED: Insert into new users table structure ---
      $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
              VALUES (?, ?, ?, ?, ?, 'customer')";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('sssss', $first_name, $last_name, $email, $phone, $hash);
      
      if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        
        // --- MODIFIED: Auto-login with new session keys ---
        $_SESSION['user_id'] = (int)$id;
        $_SESSION['name']    = $first_name; // Use first_name for greeting
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'customer';
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
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  
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
      padding: 20px 0;
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
      .auth-aside { display: none; } /* Hide aside on mobile for register form */
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
          Create an account to save your details and track orders easily.
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
            <input class="form-control" type="text" name="first_name" value="<?= h($vals['first_name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last name *</label>
            <input class="form-control" type="text" name="last_name" value="<?= h($vals['last_name']) ?>" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Email *</label>
          <input class="form-control" type="email" name="email" value="<?= h($vals['email']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone (optional)</label>
          <input class="form-control" type="text" name="phone" value="<?= h($vals['phone']) ?>" placeholder="09xx xxx xxxx">
        </div>
        <div class="mb-3">
          <label class="form-label">Password *</label>
          <input class="form-control" type="password" name="password" placeholder="Min. 6 characters" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm password *</label>
          <input class="form-control" type="password" name="confirm_password" required>
        </div>

        <button class="btn-login" type="submit">Create account</button>

        <div class="back-link">
          Already have an account?
          <a href="login.php?next=<?= urlencode($next) ?>" style="color:#0b2b0b; font-weight: 600;">Sign in</a>
          <div style="margin-top: 12px;">
            <a href="<?= htmlspecialchars($BASE_URL) ?>/index.php">← Back to Customer Page</a>
          </div>
        </div>
      </form>
    </main>
  </div>

</body>
</html>