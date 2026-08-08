<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Profile';

// Check authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    redirect('/pages/login.php');
}

$user = $auth->getUser();
$userId = $auth->getUserId();
$db = Database::getInstance();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_profile') {
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $location = $_POST['location'] ?? '';

            if (empty($name)) {
                throw new Exception('Name is required');
            }

            if (strlen($name) < 2 || strlen($name) > 255) {
                throw new Exception('Name must be between 2 and 255 characters');
            }

            $db->update(
                "UPDATE users SET name = ?, phone = ?, bio = ?, location = ? WHERE id = ?",
                [$name, $phone, $bio, $location, $userId]
            );

            $success = 'Profile updated successfully!';
            $user = $auth->getUser(); // Refresh user data

        } elseif ($_POST['action'] === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $auth->changePassword($currentPassword, $newPassword, $confirmPassword);
            $success = 'Password changed successfully!';

        } elseif ($_POST['action'] === 'delete_account') {
            $confirmDelete = $_POST['confirm_delete'] ?? '';

            if ($confirmDelete !== 'DELETE') {
                throw new Exception('Please type DELETE to confirm');
            }

            // Delete user and related data
            $db->beginTransaction();

            try {
                $db->delete("DELETE FROM analysis WHERE user_id = ?", [$userId]);
                $db->delete("DELETE FROM resumes WHERE user_id = ?", [$userId]);
                $db->delete("DELETE FROM users WHERE id = ?", [$userId]);

                $db->commit();

                $auth->logout();
                redirect('/index.php?deleted=true');

            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <!-- Header -->
        <div class="row mb-5" data-aos="fade-down">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-user-circle" style="color: #7c3aed;"></i>
                    My Profile
                </h1>
                <p class="text-muted">Manage your account settings and preferences</p>
            </div>
        </div>

        <!-- Content -->
        <div class="row">
            <!-- Profile Tab Navigation -->
            <div class="col-12 mb-4">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" 
                                type="button" role="tab" aria-controls="profile" aria-selected="true">
                            <i class="fas fa-user me-2"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" 
                                type="button" role="tab" aria-controls="security" aria-selected="false">
                            <i class="fas fa-lock me-2"></i> Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" 
                                type="button" role="tab" aria-controls="settings" aria-selected="false">
                            <i class="fas fa-cog me-2"></i> Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="danger-tab" data-bs-toggle="tab" data-bs-target="#danger" 
                                type="button" role="tab" aria-controls="danger" aria-selected="false">
                            <i class="fas fa-exclamation-triangle me-2" style="color: #ef4444;"></i> Danger Zone
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="col-12">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="card border-0 shadow-sm" data-aos="fade-up">
                            <div class="card-body p-5">
                                <h5 class="card-title fw-bold mb-4">Edit Profile Information</h5>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo sanitize($error); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?php echo $success; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-500">Full Name</label>
                                            <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                                   value="<?php echo sanitize($user['name']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-500">Email Address</label>
                                            <input type="email" class="form-control form-control-lg" id="email" 
                                                   value="<?php echo sanitize($user['email']); ?>" disabled>
                                            <small class="text-muted">Email cannot be changed</small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label fw-500">Phone Number</label>
                                            <input type="tel" class="form-control form-control-lg" id="phone" name="phone" 
                                                   value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="location" class="form-label fw-500">Location</label>
                                            <input type="text" class="form-control form-control-lg" id="location" name="location" 
                                                   value="<?php echo sanitize($user['location'] ?? ''); ?>" placeholder="City, Country">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="bio" class="form-label fw-500">Bio</label>
                                        <textarea class="form-control form-control-lg" id="bio" name="bio" rows="4" 
                                                  placeholder="Tell us about yourself..."><?php echo sanitize($user['bio'] ?? ''); ?></textarea>
                                        <small class="text-muted">Max 500 characters</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <div class="card border-0 shadow-sm" data-aos="fade-up">
                            <div class="card-body p-5">
                                <h5 class="card-title fw-bold mb-4">Change Password</h5>

                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="mb-4">
                                        <label for="current_password" class="form-label fw-500">Current Password</label>
                                        <input type="password" class="form-control form-control-lg" id="current_password" 
                                               name="current_password" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="new_password" class="form-label fw-500">New Password</label>
                                        <input type="password" class="form-control form-control-lg" id="new_password" 
                                               name="new_password" required minlength="8">
                                        <small class="text-muted">Must be at least 8 characters</small>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label fw-500">Confirm Password</label>
                                        <input type="password" class="form-control form-control-lg" id="confirm_password" 
                                               name="confirm_password" required minlength="8">
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-lock me-2"></i> Update Password
                                    </button>
                                </form>

                                <hr class="my-5">

                                <h6 class="fw-bold mb-3">Account Activity</h6>
                                <div class="alert alert-info" role="alert">
                                    <strong>Last Login:</strong>
                                    <br>
                                    <?php echo $user['last_login'] ? date('F d, Y at H:i', strtotime($user['last_login'])) : 'Never'; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                        <div class="card border-0 shadow-sm" data-aos="fade-up">
                            <div class="card-body p-5">
                                <h5 class="card-title fw-bold mb-4">Preferences</h5>

                                <form method="POST">
                                    <div class="mb-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                            <label class="form-check-label" for="email_notifications">
                                                <strong>Email Notifications</strong>
                                                <br>
                                                <small class="text-muted">Receive email updates about new features and tips</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="marketing_emails">
                                            <label class="form-check-label" for="marketing_emails">
                                                <strong>Marketing Emails</strong>
                                                <br>
                                                <small class="text-muted">Receive promotional offers and special deals</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="analytics" checked>
                                            <label class="form-check-label" for="analytics">
                                                <strong>Analytics & Performance</strong>
                                                <br>
                                                <small class="text-muted">Help us improve by sharing anonymous usage data</small>
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i> Save Preferences
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="tab-pane fade" id="danger" role="tabpanel" aria-labelledby="danger-tab">
                        <div class="card border-0 shadow-sm border border-danger" data-aos="fade-up">
                            <div class="card-body p-5">
                                <h5 class="card-title fw-bold mb-4 text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Delete Account
                                </h5>

                                <div class="alert alert-danger" role="alert">
                                    <strong>Warning!</strong> This action cannot be undone. All your data including resumes and analyses will be permanently deleted.
                                </div>

                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone!');">
                                    <input type="hidden" name="action" value="delete_account">

                                    <div class="mb-4">
                                        <label for="confirm_delete" class="form-label fw-500">
                                            Type <strong>DELETE</strong> to confirm account deletion
                                        </label>
                                        <input type="text" class="form-control form-control-lg border-danger" id="confirm_delete" 
                                               name="confirm_delete" placeholder="DELETE" required>
                                    </div>

                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-trash me-2"></i> Delete My Account
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #7c3aed;
        border-bottom-color: #7c3aed;
    }

    .nav-tabs .nav-link.active {
        color: #7c3aed;
        background-color: transparent;
        border-bottom-color: #7c3aed;
    }

    .form-control-lg, .form-select-lg {
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
    }

    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(124, 58, 237, 0.4);
    }
</style>

<?php include '../includes/footer.php'; ?>
