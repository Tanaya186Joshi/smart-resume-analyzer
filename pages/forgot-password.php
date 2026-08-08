<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Forgot Password';

$auth = Auth::getInstance();
if ($auth->isAuthenticated()) {
    redirect('/pages/dashboard.php');
}

$error = '';
$success = '';
$step = 'email'; // email, token, reset

// Handle reset token
$resetToken = $_GET['token'] ?? null;
if ($resetToken) {
    $step = 'reset';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['step'])) {
            if ($_POST['step'] === 'email') {
                $email = $_POST['email'] ?? '';

                if (empty($email) || !isValidEmail($email)) {
                    throw new Exception('Please enter a valid email address');
                }

                $token = $auth->requestPasswordReset($email);
                $success = 'If an account exists with this email, you will receive a password reset link.';
                $step = 'check_email';

            } elseif ($_POST['step'] === 'reset') {
                $token = $_POST['token'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                $auth->resetPassword($token, $newPassword, $confirmPassword);
                $success = 'Password reset successfully! Redirecting to login...';
                
                sleep(2);
                redirect('/pages/login.php?reset=true');
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
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-lg" data-aos="zoom-in">
                    <div class="card-body p-5">
                        <?php if ($step === 'email'): ?>
                            <!-- Email Step -->
                            <h2 class="card-title text-center fw-bold mb-2">
                                <i class="fas fa-lock" style="color: #7c3aed;"></i>
                            </h2>
                            <h3 class="text-center fw-bold mb-2">Forgot Password?</h3>
                            <p class="text-center text-muted mb-4">
                                Enter your email address and we'll send you a link to reset your password.
                            </p>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo sanitize($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="step" value="email">

                                <div class="mb-4">
                                    <label for="email" class="form-label fw-500">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                           placeholder="Enter your email" required autofocus>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-envelope me-2"></i> Send Reset Link
                                </button>

                                <div class="text-center">
                                    <a href="/pages/login.php" class="text-decoration-none" style="color: #7c3aed;">
                                        Back to Login
                                    </a>
                                </div>
                            </form>

                        <?php elseif ($step === 'check_email'): ?>
                            <!-- Check Email Step -->
                            <div class="text-center">
                                <div style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Check Your Email</h3>
                                <p class="text-muted mb-4">
                                    We've sent a password reset link to your email address. 
                                    The link will expire in 1 hour.
                                </p>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <strong>Didn't receive the email?</strong>
                                <br>
                                <small>Check your spam folder or try again with a different email address.</small>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="step" value="email">

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-redo me-2"></i> Try Again
                                </button>

                                <div class="text-center">
                                    <a href="/pages/login.php" class="text-decoration-none" style="color: #7c3aed;">
                                        Back to Login
                                    </a>
                                </div>
                            </form>

                        <?php elseif ($step === 'reset'): ?>
                            <!-- Reset Password Step -->
                            <h2 class="card-title text-center fw-bold mb-4">
                                <i class="fas fa-key" style="color: #7c3aed;"></i>
                                Reset Password
                            </h2>

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
                                <input type="hidden" name="step" value="reset">
                                <input type="hidden" name="token" value="<?php echo sanitize($resetToken); ?>">

                                <div class="mb-4">
                                    <label for="new_password" class="form-label fw-500">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="new_password" 
                                               name="new_password" placeholder="At least 8 characters" required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-500">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="confirm_password" 
                                               name="confirm_password" placeholder="Confirm password" required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-key me-2"></i> Reset Password
                                </button>

                                <div class="text-center">
                                    <a href="/pages/login.php" class="text-decoration-none" style="color: #7c3aed;">
                                        Back to Login
                                    </a>
                                </div>
                            </form>

                            <script>
                                document.getElementById('toggleNewPassword').addEventListener('click', function() {
                                    const input = document.getElementById('new_password');
                                    const icon = this.querySelector('i');
                                    if (input.type === 'password') {
                                        input.type = 'text';
                                        icon.classList.remove('fa-eye');
                                        icon.classList.add('fa-eye-slash');
                                    } else {
                                        input.type = 'password';
                                        icon.classList.remove('fa-eye-slash');
                                        icon.classList.add('fa-eye');
                                    }
                                });

                                document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                                    const input = document.getElementById('confirm_password');
                                    const icon = this.querySelector('i');
                                    if (input.type === 'password') {
                                        input.type = 'text';
                                        icon.classList.remove('fa-eye');
                                        icon.classList.add('fa-eye-slash');
                                    } else {
                                        input.type = 'password';
                                        icon.classList.remove('fa-eye-slash');
                                        icon.classList.add('fa-eye');
                                    }
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="alert alert-info mt-3 small" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Security Tip:</strong> Never share your password reset link with anyone.
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .form-control-lg {
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        font-weight: 600;
        transition: transform 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(124, 58, 237, 0.4);
    }

    .card {
        border-radius: 1rem;
        overflow: hidden;
    }

    .input-group .btn-outline-secondary {
        border-color: #e5e7eb;
    }

    .input-group .btn-outline-secondary:hover {
        background-color: transparent;
        border-color: #7c3aed;
        color: #7c3aed;
    }
</style>

<?php include '../includes/footer.php'; ?>
