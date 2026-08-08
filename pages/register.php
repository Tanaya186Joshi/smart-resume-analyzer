<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Register';

$auth = Auth::getInstance();
if ($auth->isAuthenticated()) {
    redirect('/pages/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!isset($_POST['terms'])) {
            throw new Exception('You must accept the terms and conditions');
        }

        $result = $auth->register($name, $email, $password, $confirmPassword);
        $success = 'Registration successful! Redirecting to login...';
        
        // Automatically login
        sleep(1);
        redirect('/pages/login.php?registered=true');

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
                            <i class="fas fa-user-plus" style="color: #7c3aed;"></i> Create Account
                        </h2>

                        <p class="text-center text-muted mb-4">
                            Join thousands of job seekers improving their resumes
                        </p>

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

                        <form method="POST" class="needs-validation">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-500">Full Name</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                       placeholder="Enter your full name" required autofocus 
                                       minlength="2" maxlength="255">
                                <div class="invalid-feedback">
                                    Please provide a valid name (2-255 characters).
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-500">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       placeholder="Enter your email" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-500">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" 
                                           name="password" placeholder="At least 8 characters" required 
                                           minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Must be at least 8 characters long
                                </small>
                                <div class="invalid-feedback">
                                    Password must be at least 8 characters.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-500">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" 
                                           name="confirm_password" placeholder="Confirm password" required 
                                           minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Passwords must match.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none" style="color: #7c3aed;">Terms of Service</a> 
                                    and <a href="#" class="text-decoration-none" style="color: #7c3aed;">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must accept the terms and conditions.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>

                            <hr>

                            <p class="text-center text-muted mb-0">
                                Already have an account? 
                                <a href="/pages/login.php" class="text-decoration-none fw-bold" style="color: #7c3aed;">
                                    Login here
                                </a>
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="small text-muted">
                            <div class="mb-2">
                                <i class="fas fa-check" style="color: #10b981;"></i>
                                <strong>Free forever</strong> - No credit card required
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-check" style="color: #10b981;"></i>
                                <strong>Instant analysis</strong> - Get results in seconds
                            </div>
                            <div>
                                <i class="fas fa-check" style="color: #10b981;"></i>
                                <strong>Privacy first</strong> - Your data is secure
                            </div>
                        </div>
                    </div>
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

    .form-check-input:checked {
        background-color: #7c3aed;
        border-color: #7c3aed;
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

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('confirm_password');
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
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;

                    if (password !== confirmPassword) {
                        event.preventDefault();
                        event.stopPropagation();
                        document.getElementById('confirm_password').classList.add('is-invalid');
                    }

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
