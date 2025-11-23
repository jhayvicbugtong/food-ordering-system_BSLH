<?php
// customer/profile.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to login if not a customer
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $BASE_URL = rtrim(preg_replace('#/customer(/.*)?$#', '', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')), '/');
    if ($BASE_URL === '/') $BASE_URL = '';
    $next = $BASE_URL . '/customer/profile.php';
    header('Location: ' . $BASE_URL . '/customer/auth/login.php?next=' . urlencode($next));
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Get user data
$user_id = (int)$_SESSION['user_id'];
$user_data = [];
$success_message = '';
$error_message = '';

// Fetch user data
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, phone, created_at FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        try {
            $conn->begin_transaction();
            
            // Check if email already exists (for other users)
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_stmt->bind_param('si', $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "This email address is already registered.";
            } else {
                // Update basic info
                $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE user_id = ?");
                $update_stmt->bind_param('ssssi', $first_name, $last_name, $email, $phone, $user_id);
                $update_stmt->execute();
                
                // Handle password change if provided
                if (!empty($current_password)) {
                    if (empty($new_password)) {
                        $error_message = "Please enter a new password.";
                    } elseif ($new_password !== $confirm_password) {
                        $error_message = "New passwords do not match.";
                    } else {
                        // Verify current password
                        $verify_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                        $verify_stmt->bind_param('i', $user_id);
                        $verify_stmt->execute();
                        $verify_result = $verify_stmt->get_result();
                        $user = $verify_result->fetch_assoc();
                        
                        if (password_verify($current_password, $user['password'])) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                            $pass_stmt->bind_param('si', $hashed_password, $user_id);
                            $pass_stmt->execute();
                            $pass_stmt->close();
                            $success_message = "Profile and password updated successfully!";
                        } else {
                            $error_message = "Current password is incorrect.";
                        }
                        $verify_stmt->close();
                    }
                } else {
                    $success_message = "Profile updated successfully!";
                }
                
                $update_stmt->close();
                
                // Update session name
                $_SESSION['name'] = $first_name . ' ' . $last_name;
                
                // Refresh user data
                $refresh_stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, phone, created_at FROM users WHERE user_id = ?");
                $refresh_stmt->bind_param('i', $user_id);
                $refresh_stmt->execute();
                $refresh_result = $refresh_stmt->get_result();
                $user_data = $refresh_result->fetch_assoc();
                $refresh_stmt->close();
            }
            
            $check_stmt->close();
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "An error occurred while updating your profile. Please try again.";
        }
    }
}

// $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0"/>
  <title>My Profile | Bente Sais Lomi House</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE_URL) ?>/assets/css/customer.css"/>

  <style>
    :root {
        --primary-color: #5cfa63;
        --primary-dark: #4cd853;
        --primary-light: #7cff82;
    }
    
    .profile-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin: 0 auto 1rem;
        border: 4px solid white;
        box-shadow: 0 2px 10px rgba(92, 250, 99, 0.3);
    }
    
    .profile-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
        background: white;
    }
    
    .profile-card .card-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .profile-card .card-body {
        padding: 1.5rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control {
        border-radius: 8px;
        border: 1px solid #e1e5e9;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(92, 250, 99, 0.1);
    }
    
    .btn-primary {
        background: var(--primary-color);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        color: #000;
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(92, 250, 99, 0.3);
        color: #000;
    }
    
    .btn-primary:active {
        background: var(--primary-dark) !important;
        transform: translateY(0);
        color: #000 !important;
    }
    
    .stats-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    
    .stats-number {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--primary-color);
    }
    
    .stats-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .nav-pills .nav-link {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .nav-pills .nav-link.active {
        background: var(--primary-color);
        color: #000;
        border-color: var(--primary-color);
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
        color: #495057;
        border-color: #e9ecef;
    }
    
    .password-toggle {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s ease;
        background: #f8f9fa;
        border: 1px solid #e1e5e9;
        border-left: none;
    }
    
    .password-toggle:hover {
        color: var(--primary-color);
        background: #f1f3f4;
    }
    
    .alert-success {
        border-left: 4px solid var(--primary-color);
    }
    
    @media (max-width: 768px) {
        .profile-card .card-body {
            padding: 1.25rem;
        }
        
        .profile-header {
            padding: 1.5rem 0;
        }
        
        .stats-card {
            padding: 1rem;
        }
        
        .stats-number {
            font-size: 1.25rem;
        }
    }
  </style>
</head>
<body class="page-profile">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="ms-4">
                        <h1 class="h4 fw-bold mb-1">My Profile</h1>
                        <p class="text-muted mb-0">Manage your account information</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="text-muted small">
                    <i class="bi bi-calendar me-1"></i>
                    Member since <?php echo date('M Y', strtotime($user_data['created_at'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="container py-4" style="min-height: 60vh;">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="profile-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                    
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">0</div>
                                <div class="stats-label">Orders</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">0</div>
                                <div class="stats-label">Favorites</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                        <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab">
                            <i class="bi bi-person me-2"></i>Profile Information
                        </button>
                        <button class="nav-link" id="v-pills-password-tab" data-bs-toggle="pill" data-bs-target="#v-pills-password" type="button" role="tab">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Profile Information Tab -->
                <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel">
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="profileForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                        <div class="form-text">Optional</div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="v-pills-password" role="tabpanel">
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="passwordForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                        <span class="input-group-text password-toggle" onclick="togglePassword('current_password')">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <span class="input-group-text password-toggle" onclick="togglePassword('new_password')">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                    </div>
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentNode.querySelector('.bi');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (currentPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match. Please try again.');
                return false;
            }
            
            if (currentPassword && newPassword.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long.');
                return false;
            }
        });
    }
});
</script>

</body>
</html>