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

// Fetch user data (Enhanced to include role, status, updated_at)
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, phone, role, is_active, created_at, updated_at FROM users WHERE user_id = ?");
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
        $error_message = "First name, last name, and email are required.";
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
                // Prepare base update query
                $update_sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()";
                $types = "ssss";
                $params = [$first_name, $last_name, $email, $phone];

                // Handle password change if provided
                if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                     if (empty($current_password)) {
                        throw new Exception("Current password is required to change password.");
                     }
                     
                     // Verify current password
                     $verify_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                     $verify_stmt->bind_param('i', $user_id);
                     $verify_stmt->execute();
                     $curr_user = $verify_stmt->get_result()->fetch_assoc();
                     $verify_stmt->close();

                     if (!password_verify($current_password, $curr_user['password'])) {
                         throw new Exception("Current password is incorrect.");
                     }

                     if (empty($new_password)) {
                         throw new Exception("New password cannot be empty.");
                     }
                     if (strlen($new_password) < 6) {
                         throw new Exception("New password must be at least 6 characters.");
                     }
                     if ($new_password !== $confirm_password) {
                         throw new Exception("New passwords do not match.");
                     }

                     // Add password to update
                     $update_sql .= ", password = ?";
                     $types .= "s";
                     $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                }

                $update_sql .= " WHERE user_id = ?";
                $types .= "i";
                $params[] = $user_id;

                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param($types, ...$params);
                
                if ($update_stmt->execute()) {
                     $success_message = "Profile updated successfully!";
                     // Update session name
                     $_SESSION['name'] = $first_name; // usually just first name in header
                     
                     // Refresh data
                     $refresh_stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, phone, role, is_active, created_at, updated_at FROM users WHERE user_id = ?");
                     $refresh_stmt->bind_param('i', $user_id);
                     $refresh_stmt->execute();
                     $user_data = $refresh_stmt->get_result()->fetch_assoc();
                     $refresh_stmt->close();
                } else {
                    throw new Exception("Database update failed.");
                }
                $update_stmt->close();
            }
            $check_stmt->close();
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}
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
    }
    
    body.page-profile {
        background-color: #f8f9fa;
    }

    /* Header */
    .profile-header {
        background: #ffffff;
        border-bottom: 1px solid #e9ecef;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    .profile-avatar-lg {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color) 0%, #212529 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Cards */
    .profile-card {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .profile-card .card-body {
        padding: 2rem;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #f1f3f5;
        padding-bottom: 1rem;
    }
    .section-title:last-child {
        border-bottom: 0;
    }

    /* Form Elements */
    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    }
    .form-control {
        border-radius: 8px;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        border-color: #dee2e6;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(92, 250, 99, 0.15);
    }

    /* Buttons */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #000;
        font-weight: 600;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
    }
    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    
    /* Sidebar Info */
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0;
        border-bottom: 1px solid #f1f3f5;
        font-size: 0.9rem;
    }
    .info-row:last-child { border-bottom: 0; }
    .info-label { color: #6c757d; }
    .info-value { font-weight: 600; color: #212529; }

    /* Quick Links */
    .quick-link {
        display: flex;
        align-items: center;
        padding: 0.8rem 1rem;
        color: #495057;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s;
        font-weight: 500;
    }
    .quick-link:hover {
        background-color: #f8f9fa;
        color: #000;
        transform: translateX(3px);
    }
    .quick-link i {
        font-size: 1.2rem;
        margin-right: 0.8rem;
        color: #adb5bd;
    }
    .quick-link:hover i { color: var(--primary-color); }
    
    .quick-link.text-danger:hover i { color: #dc3545; }
    .quick-link.text-danger:hover { background-color: #fff5f5; }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-header { padding: 1.5rem 0; text-align: center; }
        .profile-header .row { flex-direction: column; gap: 1rem; }
        .profile-header .d-flex { justify-content: center; }
        .profile-header .text-md-end { text-align: center !important; }
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
                    <div class="ms-4 text-start">
                        <h1 class="h3 fw-bold mb-1">My Profile</h1>
                        <p class="text-muted mb-0">Manage your personal information and security</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                 </div>
        </div>
    </div>
</div>

<main class="container pb-5">
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" id="profileForm">
                <div class="profile-card">
                    <div class="card-body">
                        
                        <h5 class="section-title">Personal Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">+63</span>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars(str_replace('+63', '', $user_data['phone'] ?? '')); ?>" 
                                           placeholder="912 345 6789" maxlength="10">
                                </div>
                            </div>
                        </div>

                        <h5 class="section-title mt-5">Change Password</h5>
                        <p class="text-muted small mb-3">Leave these fields blank if you do not wish to change your password.</p>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" placeholder="Required only if changing password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Min. 6 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Re-type new password">
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                Reset
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="profile-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Account Summary</h6>
                    
                    <div class="info-row">
                        <span class="info-label">Role</span>
                        <span class="info-value badge bg-light text-dark border"><?php echo ucfirst(htmlspecialchars($user_data['role'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                <?php echo $user_data['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Update</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($user_data['updated_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="card-body p-2">
                    <h6 class="fw-bold m-3 mb-2">Quick Links</h6>
                    <div class="d-flex flex-column gap-1">
                        <a href="orders.php" class="quick-link">
                            <i class="bi bi-receipt"></i> My Orders
                        </a>
                        <a href="menu.php" class="quick-link">
                            <i class="bi bi-egg-fried"></i> Browse Menu
                        </a>
                        <div class="border-top my-1"></div>
                        <a href="auth/logout.php" class="quick-link text-danger">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const phoneInput = form.querySelector('input[name="phone"]');

    // Phone number input restriction
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    // Form submission confirmation
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Save Changes?',
            text: "Are you sure you want to update your profile?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5cfa63',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, save it!',
            color: '#000'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

</body>
</html>