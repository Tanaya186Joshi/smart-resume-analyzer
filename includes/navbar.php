<?php
$auth = Auth::getInstance();
$user = $auth->getUser();
$isAuthenticated = $auth->isAuthenticated();
$isAdmin = $auth->isAdmin();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo $isAuthenticated ? '/pages/dashboard.php' : '/index.php'; ?>">
            <span style="color: #7c3aed;">
                <i class="fas fa-file-pdf"></i> Smart Resume Analyzer
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($isAuthenticated): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/dashboard.php">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/pages/upload.php">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Resume
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/pages/history.php">
                            <i class="fas fa-history"></i> History
                        </a>
                    </li>

                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name']); ?>" 
                                 alt="<?php echo $user['name']; ?>" 
                                 class="rounded-circle me-2" 
                                 style="width: 30px; height: 30px;">
                            <?php echo $user['name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/pages/profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="/pages/profile.php#settings">
                                <i class="fas fa-cog"></i> Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar-brand {
    font-size: 1.4rem;
    letter-spacing: 0.5px;
}

.navbar-brand:hover {
    color: #7c3aed !important;
}

.nav-link {
    transition: color 0.3s ease;
    margin: 0 5px;
}

.nav-link:hover {
    color: #7c3aed !important;
}

.nav-link.active {
    color: #7c3aed !important;
    border-bottom: 2px solid #7c3aed;
}
</style>
