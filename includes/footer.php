    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Page-specific JavaScript (if needed) -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ((array)$additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>

    <footer class="bg-dark text-light mt-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <span style="color: #7c3aed;">
                            <i class="fas fa-file-pdf"></i> Smart Resume Analyzer
                        </span>
                    </h5>
                    <p class="text-muted">
                        AI-powered resume analysis and ATS matching to help you land your dream job.
                    </p>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/pages/dashboard.php" class="text-decoration-none text-muted hover-primary">Dashboard</a></li>
                        <li><a href="/pages/upload.php" class="text-decoration-none text-muted hover-primary">Upload Resume</a></li>
                        <li><a href="/pages/history.php" class="text-decoration-none text-muted hover-primary">Analysis History</a></li>
                        <li><a href="#" class="text-decoration-none text-muted hover-primary">FAQ</a></li>
                    </ul>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Contact</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:support@resumeanalyzer.local" class="text-decoration-none text-muted hover-primary">
                            support@resumeanalyzer.local
                        </a>
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-muted hover-primary me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted hover-primary me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-muted hover-primary me-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-muted hover-primary"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted small mb-0">
                        &copy; 2024 Smart Resume Analyzer. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-decoration-none text-muted small hover-primary me-3">Privacy Policy</a>
                    <a href="#" class="text-decoration-none text-muted small hover-primary">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .hover-primary:hover {
            color: #7c3aed !important;
            transition: color 0.3s ease;
        }

        footer {
            margin-top: auto;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }
    </style>

    <!-- Notification Toast Template -->
    <div id="notificationContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11;">
    </div>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Global notification function
        function showNotification(message, type = 'info', duration = 3000) {
            const container = document.getElementById('notificationContainer');
            const toastId = 'toast-' + Date.now();
            
            const bgClass = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-info'
            }[type] || 'bg-info';

            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header ${bgClass} text-white">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <strong class="me-auto">Notification</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            if (duration > 0) {
                setTimeout(() => {
                    toastElement.remove();
                }, duration);
            }
        }

        // CSRF Token helper
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : null;
        }

        // API helper function
        async function apiCall(endpoint, method = 'GET', data = null) {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(endpoint, options);
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'API Error');
                }

                return result;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }
    </script>
</body>
</html>
