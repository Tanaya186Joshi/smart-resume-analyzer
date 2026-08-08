<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Admin Dashboard';

// Check authentication and admin role
$auth = Auth::getInstance();
if (!$auth->isAuthenticated() || !$auth->isAdmin()) {
    redirect('/pages/dashboard.php');
}

$db = Database::getInstance();

// Get statistics
$stats = [];

// Total users
$userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $userCount['count'];

// Total resumes
$resumeCount = $db->fetchOne("SELECT COUNT(*) as count FROM resumes");
$stats['total_resumes'] = $resumeCount['count'];

// Total analyses
$analysisCount = $db->fetchOne("SELECT COUNT(*) as count FROM analysis");
$stats['total_analyses'] = $analysisCount['count'];

// Recent users
$recentUsers = $db->fetchAll(
    "SELECT id, name, email, created_at, last_login FROM users ORDER BY created_at DESC LIMIT 5"
);

// Recent analyses
$recentAnalyses = $db->fetchAll(
    "SELECT a.id, u.name, a.match_score, a.created_at FROM analysis a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5"
);

// Analytics by event type
$analyticsData = $db->fetchAll(
    "SELECT event_type, COUNT(*) as count FROM analytics GROUP BY event_type ORDER BY count DESC LIMIT 10"
);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-5" data-aos="fade-down">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-tachometer-alt" style="color: #7c3aed;"></i>
                    Admin Dashboard
                </h1>
                <p class="text-muted">System analytics and management</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div style="background: rgba(124, 58, 237, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-users" style="font-size: 1.5rem; color: #7c3aed;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Total Users</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['total_users']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div style="background: rgba(16, 185, 129, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #10b981;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Total Resumes</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['total_resumes']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div style="background: rgba(59, 130, 246, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-chart-bar" style="font-size: 1.5rem; color: #3b82f6;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Total Analyses</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['total_analyses']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Recent Users -->
            <div class="col-lg-6 mb-5" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-user-plus me-2" style="color: #7c3aed;"></i>
                            Recent Users
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                        <th>Last Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo sanitize($user['name']); ?></td>
                                            <td><?php echo sanitize($user['email']); ?></td>
                                            <td><small class="text-muted"><?php echo timeAgo($user['created_at']); ?></small></td>
                                            <td><small class="text-muted"><?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'Never'; ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Analyses -->
            <div class="col-lg-6 mb-5" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-chart-line me-2" style="color: #10b981;"></i>
                            Recent Analyses
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAnalyses as $analysis): ?>
                                        <tr>
                                            <td><?php echo sanitize($analysis['name']); ?></td>
                                            <td>
                                                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <?php echo round($analysis['match_score'], 1); ?>%
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?php echo timeAgo($analysis['created_at']); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Events -->
        <div class="row" data-aos="fade-up">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-chart-pie me-2" style="color: #f59e0b;"></i>
                            Analytics Events
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event Type</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalEvents = array_sum(array_map(fn($e) => $e['count'], $analyticsData));
                                    foreach ($analyticsData as $event): 
                                        $percentage = $totalEvents > 0 ? ($event['count'] / $totalEvents) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo sanitize($event['event_type']); ?></td>
                                            <td><?php echo $event['count']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1" style="height: 20px;">
                                                        <div class="progress-bar" style="width: <?php echo round($percentage, 0); ?>%"></div>
                                                    </div>
                                                    <span class="ms-2"><?php echo round($percentage, 1); ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .progress {
        background-color: #e5e7eb;
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
</style>

<?php include '../includes/footer.php'; ?>
