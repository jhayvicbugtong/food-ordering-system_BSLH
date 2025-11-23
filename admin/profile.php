<?php
include __DIR__ . '/includes/header.php';

// Get current user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $errors[] = "First name, last name, and email are required.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Check if email already exists (excluding current user)
    $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $email_check->bind_param("si", $email, $user_id);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        $errors[] = "Email address is already in use.";
    }

    // Handle password change if provided
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
    }

    if (empty($errors)) {
        // Update user data
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, updated_at = NOW() WHERE user_id = ?");
            $update_query->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $user_id);
        } else {
            $update_query = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE user_id = ?");
            $update_query->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
        }

        if ($update_query->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            // Refresh user data
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user = $user_result->fetch_assoc();
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
    }
}
?>

<div class="container-fluid">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header mb-6">
            <h1 class="page-title">Profile Settings</h1>
            <p class="page-subtitle">Manage your account information and preferences</p>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="profileForm">
                            <!-- Personal Information -->
                            <div class="section mb-6">
                                <h5 class="section-title">Personal Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" 
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" 
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+63</span>
                                            <input type="text" class="form-control" name="phone" 
                                                   value="<?= htmlspecialchars(str_replace('+63', '', $user['phone'] ?? '')) ?>" 
                                                   placeholder="912 345 6789" maxlength="10">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change -->
                            <div class="section mb-6">
                                <h5 class="section-title">Change Password</h5>
                                <p class="text-muted mb-4">Leave blank if you don't want to change your password</p>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" 
                                               placeholder="Enter your current password">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" 
                                               placeholder="Enter new password">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" 
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    Save Changes
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Account Summary -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Account Summary</h6>
                        
                        <div class="account-info">
                            <div class="info-row">
                                <span class="info-label">Role</span>
                                <span class="info-value"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value"><?= date('M j, Y', strtotime($user['updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Quick Links</h6>
                        
                        <div class="quick-links">
                            <a href="<?= htmlspecialchars($BASE_URL) ?>/admin/manage_orders.php" class="quick-link">
                                <i class="bi bi-cart"></i>
                                <span>View Orders</span>
                            </a>
                            
                            <a href="<?= htmlspecialchars($BASE_URL) ?>/admin/manage_staff.php" class="quick-link">
                                <i class="bi bi-people"></i>
                                <span>Staff Management</span>
                            </a>
                            
                            <a href="<?= htmlspecialchars($BASE_URL) ?>/auth/logout.php" class="quick-link text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Page Header */
.page-header {
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

/* Card Styles */
.card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    background: #ffffff;
}

.card-body {
    padding: 2rem;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
}

/* Sections */
.section {
    padding-bottom: 2rem;
    border-bottom: 1px solid #f3f4f6;
}

.section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1.5rem;
}

/* Form Styles */
.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

.input-group-text {
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-right: none;
    color: #6b7280;
    font-size: 0.9rem;
}

.input-group .form-control {
    border-left: none;
}

/* Account Info */
.account-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #6b7280;
    font-size: 0.9rem;
}

.info-value {
    font-weight: 500;
    color: #111827;
    font-size: 0.9rem;
}

/* Quick Links */
.quick-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.quick-link:hover {
    background: #f9fafb;
    color: #111827;
}

.quick-link i {
    width: 16px;
    text-align: center;
    color: #6b7280;
}

.quick-link.text-danger i {
    color: #dc2626;
}

.quick-link.text-danger:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-start;
    padding-top: 1.5rem;
    border-top: 1px solid #f3f4f6;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #4f46e5;
    border: 1px solid #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    transform: translateY(-1px);
}

/* Loading State */
.btn-loading {
    position: relative;
    color: transparent;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-right-color: transparent;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Spacing Utilities */
.mb-6 {
    margin-bottom: 2.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const submitBtn = document.getElementById('submitBtn');
    const phoneInput = form.querySelector('input[name="phone"]');

    // Check for success/error messages from PHP and show SweetAlert
    <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= $_SESSION['success_message'] ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            position: 'center'
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_messages'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            html: `<?= implode('<br>', $_SESSION['error_messages']) ?>`,
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#4f46e5'
        });
        <?php unset($_SESSION['error_messages']); ?>
    <?php endif; ?>

    // Phone number validation
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    // Form submission with SweetAlert confirmation
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate form
        const newPassword = form.querySelector('input[name="new_password"]').value;
        const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
        const currentPassword = form.querySelector('input[name="current_password"]').value;

        let validationErrors = [];

        // If any password field is filled, validate all
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                validationErrors.push('Current password is required to change password');
            }

            if (!newPassword) {
                validationErrors.push('New password is required');
            } else if (newPassword.length < 6) {
                validationErrors.push('New password must be at least 6 characters long');
            }

            if (newPassword !== confirmPassword) {
                validationErrors.push('New passwords do not match');
            }
        }

        if (validationErrors.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                html: validationErrors.join('<br>'),
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        // Show confirmation dialog
        Swal.fire({
            title: 'Update Profile?',
            text: 'Are you sure you want to update your profile information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;

                // Submit the form
                form.submit();
            }
        });
    });

    // Reset button handler
    form.querySelector('button[type="reset"]').addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Form?',
            text: 'This will clear all unsaved changes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.reset();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Form Reset',
                    text: 'All changes have been cleared.',
                    showConfirmButton: false,
                    timer: 1500,
                    position: 'center'
                });
            }
        });
    });

    // Real-time password validation feedback
    const passwordFields = form.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            // Clear any existing validation styling
            this.classList.remove('is-invalid', 'is-valid');
            
            const errorElement = this.parentNode.querySelector('.invalid-feedback');
            if (errorElement) {
                errorElement.remove();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>