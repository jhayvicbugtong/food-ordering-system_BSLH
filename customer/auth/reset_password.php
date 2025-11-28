<?php
// customer/auth/reset_password.php
require_once __DIR__ . '/../../includes/db_connect.php';

// --- Fetch Store Name ---
$store_name = "Bente Sais Lomi House";
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'store_name' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $store_name = $res->fetch_assoc()['setting_value'];
    }
}

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Ensure we have an email to work with
if (empty($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$err = '';
$success_msg = '';

// Check for "Resent" flag from redirect
if (isset($_GET['resent']) && $_GET['resent'] == 1) {
    $success_msg = "A new verification code has been sent to your email.";
}

// Determine current step (1 = Enter Code, 2 = Set Password)
$step = isset($_SESSION['reset_code_verified']) && $_SESSION['reset_code_verified'] === true ? 2 : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ACTION: RESEND CODE ---
    if (isset($_POST['action']) && $_POST['action'] === 'resend_code') {
        // 1. Fetch User
        $stmt = $conn->prepare("SELECT user_id, first_name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user) {
            // 2. Generate New OTP
            $otp = rand(100000, 999999);

            // 3. Update DB
            $update = $conn->prepare("UPDATE users SET verification_code = ?, updated_at = NOW() WHERE user_id = ?");
            $update->bind_param('ii', $otp, $user['user_id']);
            
            if ($update->execute()) {
                $update->close();

                // 4. Send Email
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

                    // Use dynamic store name
                    $mail->setFrom('bentesaislomi.26@gmail.com', $store_name);
                    $mail->addAddress($email, $user['first_name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'New Verification Code';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                            <h2 style='color: #2e8503;'>New Verification Code</h2>
                            <p>Hi <strong>" . htmlspecialchars($user['first_name']) . "</strong>,</p>
                            <p>You requested a new code. Here it is:</p>
                            <div style='background: #f8f9fa; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                                <span style='font-size: 24px; letter-spacing: 5px; font-weight: bold; color: #000;'>" . $otp . "</span>
                            </div>
                            <p style='font-size: 12px; color: #666;'>Valid for 15 minutes.</p>
                        </div>
                    ";

                    $mail->send();

                    // Redirect to self to prevent resubmission on refresh
                    header("Location: reset_password.php?resent=1");
                    exit;

                } catch (Exception $e) {
                    $err = "Could not send email. Please try again later.";
                }
            } else {
                $err = "Database error. Please try again.";
            }
        } else {
            $err = "User not found.";
        }
    }

    // --- ACTION: VERIFY CODE (STEP 1) ---
    elseif (isset($_POST['action']) && $_POST['action'] === 'verify_code') {
        $code = trim($_POST['code'] ?? '');

        if (empty($code)) {
            $err = "Please enter the verification code.";
        } else {
            $stmt = $conn->prepare("SELECT user_id, verification_code, updated_at FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if ($user) {
                // Check expiry (15 mins = 900 seconds)
                $updated_at = strtotime($user['updated_at']);
                $time_diff = time() - $updated_at;

                if ((string)$user['verification_code'] !== $code) {
                    $err = "Invalid verification code. Please try again.";
                } elseif ($time_diff > 900) {
                    $err = "Code expired. Please request a new one.";
                } else {
                    // Success! Mark session as verified and store ID for next step
                    $_SESSION['reset_code_verified'] = true;
                    $_SESSION['reset_user_id'] = $user['user_id'];
                    
                    // Redirect to self to switch to Step 2 clean
                    header("Location: reset_password.php");
                    exit;
                }
            } else {
                $err = "User not found.";
            }
        }
    } 
    
    // --- ACTION: UPDATE PASSWORD (STEP 2) ---
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['confirm_password'] ?? '';
        $user_id = $_SESSION['reset_user_id'] ?? 0;

        if (empty($pass) || empty($pass2)) {
            $err = "Please fill in all fields.";
        } elseif ($pass !== $pass2) {
            $err = "Passwords do not match.";
        } elseif (strlen($pass) < 6) {
            $err = "Password must be at least 6 characters.";
        } elseif ($user_id === 0) {
            $err = "Session invalid. Please start over.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            
            // Update DB: Set new password AND clear verification code
            $upd = $conn->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE user_id = ?");
            $upd->bind_param('si', $hash, $user_id);
            
            if ($upd->execute()) {
                // Clear all session reset variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_code_verified']);
                unset($_SESSION['reset_user_id']);
                
                // Success View
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Password Updated</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body style="background-color: #f8f9fa;">
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Reset!',
                            text: 'Your password has been successfully updated. You can now login.',
                            confirmButtonText: 'Go to Login',
                            confirmButtonColor: '#5cfa63',
                            allowOutsideClick: false
                        }).then((result) => {
                            window.location.href = 'login.php';
                        });
                    </script>
                </body>
                </html>
                <?php
                exit;
            } else {
                $err = "Database update failed. Please try again.";
            }
            $upd->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Reset Password | <?= htmlspecialchars($store_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Segoe UI", Arial, sans-serif;
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

    .auth-header { margin-bottom: 24px; }

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
    }

    .form-label {
      font-size: 13px;
      font-weight: 600;
      color: #343a40;
      margin-bottom: 6px;
    }

    .form-control {
      font-size: 15px;
      border-radius: 10px;
      padding: 12px 16px;
      border: 1px solid #dee2e6;
    }
    
    .otp-input {
        letter-spacing: 5px;
        text-align: center;
        font-weight: 700;
        font-size: 20px;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(92, 250, 99, 0.15);
    }
    
    .input-group-text {
      border-radius: 0 10px 10px 0;
      background: #f8f9fa;
      border-left: 0;
      cursor: pointer;
    }
    .input-group .form-control {
      border-right: 0;
      border-radius: 10px 0 0 10px;
    }

    .btn-primary {
      background-color: var(--accent);
      border: none;
      width: 100%;
      border-radius: 10px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      color: #052e06;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 16px;
    }
    
    .btn-primary:hover {
      background-color: var(--accent-hover);
      color: #000;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(92, 250, 99, 0.2);
    }
    
    /* --- NEW LINK STYLES --- */
    .btn-link-resend {
        background: none;
        border: none;
        color: var(--text-dim);
        text-decoration: underline;
        font-size: 14px;
        cursor: pointer;
        padding: 0;
    }
    .btn-link-resend:hover { color: #000; }

    .link-change-email {
        color: var(--text-dim);
        text-decoration: none;
        font-size: 14px;
    }
    .link-change-email:hover {
        text-decoration: underline;
        color: var(--text-main);
    }
    /* ----------------------- */

    .alert-danger {
      background-color: #fee2e2;
      border: none;
      color: #991b1b;
      border-radius: 10px;
      font-size: 14px;
      padding: 12px 16px;
      margin-bottom: 24px;
    }

    .back-link {
      text-align: center;
      margin-top: 24px;
      font-size: 14px;
      color: var(--text-dim);
    }

    @media (max-width: 768px) {
      .auth-shell { 
        flex-direction: column; 
        width: 100%; 
        min-height: 100vh;
        border-radius: 0; 
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
      .brand-logo { height: 40px; width: 40px; font-size: 16px; }
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
          <p>Security Check</p> 
        </div>
      </div>
      <div class="intro-text">
        <?php if ($step === 1): ?>
            Please check your email inbox. We've sent a verification code to confirm it's really you.
        <?php else: ?>
            Success! Your code is verified. Now, please create a strong new password for your account.
        <?php endif; ?>
      </div>
    </aside>

    <main class="auth-main">
      
      <?php if ($step === 1): ?>
        <div class="auth-header">
          <h2>Verify It's You</h2>
          <p>Enter the 6-digit code sent to <strong><?= htmlspecialchars($email) ?></strong></p>
        </div>

        <?php if ($err): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($err) ?>
          </div>
        <?php endif; ?>
        
        <?php if ($success_msg): ?>
          <script>
            Swal.fire({
                icon: 'success',
                title: 'Code Resent',
                text: '<?= htmlspecialchars($success_msg) ?>',
                timer: 2500,
                showConfirmButton: false
            });
          </script>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="action" value="verify_code">
          <div class="mb-3">
            <label class="form-label">Verification Code</label>
            <input type="text" name="code" class="form-control otp-input" maxlength="6" required placeholder="000000" autofocus autocomplete="off">
          </div>

          <button type="submit" class="btn btn-primary">Verify Code</button>
        </form>

        <div class="back-link">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="resend_code">
                Didn't receive it? <button type="submit" class="btn-link-resend">Resend Code</button>
            </form>
            <span class="mx-1 text-muted">|</span>
            <a href="forgot_password.php" class="link-change-email">Change Email</a>
        </div>

      <?php else: ?>
        <div class="auth-header">
          <h2>Reset Password</h2>
          <p>Create a new password for your account.</p>
        </div>

        <?php if ($err): ?>
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($err) ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="action" value="update_password">
          
          <div class="mb-3 text-start">
            <label class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" name="password" class="form-control" id="pass1" required placeholder="Min. 6 characters">
              <span class="input-group-text" onclick="togglePass('pass1', this)">
                  <i class="bi bi-eye-slash"></i>
              </span>
            </div>
          </div>

          <div class="mb-3 text-start">
            <label class="form-label">Confirm Password</label>
            <div class="input-group">
               <input type="password" name="confirm_password" class="form-control" id="pass2" required placeholder="Repeat password">
               <span class="input-group-text" onclick="togglePass('pass2', this)">
                  <i class="bi bi-eye-slash"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
        
      <?php endif; ?>

    </main>
  </div>

  <script>
    function togglePass(inputId, iconContainer) {
        const input = document.getElementById(inputId);
        const icon = iconContainer.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            input.type = "password";
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    }
  </script>
</body>
</html>