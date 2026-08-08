<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Dashboard';

// Check authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    redirect('/pages/login.php');
}

$user = $auth->getUser();
$userId = $auth->getUserId();
$db = Database::getInstance();

// Get statistics
$stats = [];

// Total resumes
$resumeCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM resumes WHERE user_id = ?",
    [$userId]
);
$stats['resumes'] = $resumeCount['count'];

// Total analyses
$analysisCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM analysis WHERE user_id = ?",
    [$userId]
);
$stats['analyses'] = $analysisCount['count'];

// Average match score
$avgScore = $db->fetchOne(
    "SELECT AVG(match_score) as avg FROM analysis WHERE user_id = ?",
    [$userId]
);
$stats['avg_score'] = $avgScore['avg'] ? round($avgScore['avg'], 2) : 0;

// Recent analyses
$recentAnalyses = $db->fetchAll(
    "SELECT a.id, a.match_score, a.ats_score, a.created_at, r.original_filename 
     FROM analysis a 
     JOIN resumes r ON a.resume_id = r.id 
     WHERE a.user_id = ? 
     ORDER BY a.created_at DESC 
     LIMIT 5",
    [$userId]
);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-5" data-aos="fade-down">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-chart-line" style="color: #7c3aed;"></i>
                    Welcome back, <?php echo sanitize($user['name']); ?>!
                </h1>
                <p class="text-muted">Manage your resumes and analyze job matches</p>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="row mb-5">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: rgba(124, 58, 237, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #7c3aed;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Resumes Uploaded</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['resumes']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <a href="/pages/upload.php" class="card-footer text-decoration-none bg-light">
                        <small>Upload a new resume <i class="fas fa-arrow-right ms-2"></i></small>
                    </a>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-chart-bar" style="font-size: 1.5rem; color: #10b981;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Total Analyses</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['analyses']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <a href="/pages/history.php" class="card-footer text-decoration-none bg-light">
                        <small>View all analyses <i class="fas fa-arrow-right ms-2"></i></small>
                    </a>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); border-radius: 0.5rem; padding: 1rem;">
                                <i class="fas fa-star" style="font-size: 1.5rem; color: #3b82f6;"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="text-muted mb-0">Average Match Score</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['avg_score']; ?>%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Analyses Section -->
        <div class="row" data-aos="fade-up">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-history me-2" style="color: #7c3aed;"></i>
                            Recent Analyses
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentAnalyses)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                                <p class="text-muted">No analyses yet. Upload a resume to get started!</p>
                                <a href="/pages/upload.php" class="btn btn-primary">
                                    <i class="fas fa-cloud-upload-alt me-2"></i> Upload Resume
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Resume</th>
                                            <th>Match Score</th>
                                            <th>ATS Score</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAnalyses as $analysis): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i>
                                                        <span><?php echo truncate($analysis['original_filename'], 20); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: rgba(124, 58, 237, 0.2); color: #7c3aed;">
                                                        <?php echo round($analysis['match_score'], 1); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo round($analysis['ats_score'], 1); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo timeAgo($analysis['created_at']); ?></small>
                                                </td>
                                                <td>
                                                    <a href="/pages/results.php?id=<?php echo $analysis['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-lightning-bolt me-2" style="color: #f59e0b;"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="/pages/upload.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload Resume
                        </a>
                        <a href="/pages/upload.php" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i> Analyze Job Match
                        </a>
                        <a href="/pages/profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Tips Section -->
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(6, 182, 212, 0.1));">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-lightbulb me-2" style="color: #f59e0b;"></i>
                            Pro Tips
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                                Use clear section headers for better ATS parsing
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                                Include quantifiable achievements
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                                Tailor your resume for each job
                            </li>
                            <li>
                                <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                                Keep it to 1-2 pages for best results
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(124, 58, 237, 0.05);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(124, 58, 237, 0.4);
    }
</style>

<?php include '../includes/footer.php'; ?>
