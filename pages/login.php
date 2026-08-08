<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Login';

$auth = Auth::getInstance();
if ($auth->isAuthenticated()) {
    redirect('/pages/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } else {
            $user = $auth->login($email, $password, $rememberMe);
            $success = 'Login successful! Redirecting...';
            redirect('/pages/dashboard.php');
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
                        <h2 class="card-title text-center fw-bold mb-4">
                            <i class="fas fa-sign-in-alt" style="color: #7c3aed;"></i> Login
                        </h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo sanitize($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-500">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       placeholder="Enter your email" required autofocus>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-500">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" 
                                           name="password" placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please provide your password.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Remember me for 30 days
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>

                            <hr>

                            <div class="text-center mb-3">
                                <a href="/pages/forgot-password.php" class="text-decoration-none">
                                    Forgot your password?
                                </a>
                            </div>

                            <p class="text-center text-muted mb-0">
                                Don't have an account? 
                                <a href="/pages/register.php" class="text-decoration-none fw-bold" style="color: #7c3aed;">
                                    Register here
                                </a>
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Demo Credentials -->
                <div class="alert alert-info mt-3" role="alert">
                    <small>
                        <strong>Demo Account:</strong><br>
                        Email: demo@example.com<br>
                        Password: Demo@12345
                    </small>
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

<script>
    // Password visibility toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

<?php include '../includes/footer.php'; ?>
