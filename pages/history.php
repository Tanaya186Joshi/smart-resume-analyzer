<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Analysis History';

// Check authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    redirect('/pages/login.php');
}

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get pagination
$page = (int)($_GET['page'] ?? 1);
$perPage = (int)env('RESULTS_PER_PAGE', 10);
$offset = ($page - 1) * $perPage;

// Get total count
$totalResult = $db->fetchOne(
    "SELECT COUNT(*) as count FROM analysis WHERE user_id = ?",
    [$userId]
);
$total = $totalResult['count'];
$totalPages = ceil($total / $perPage);

// Get analyses
$analyses = $db->fetchAll(
    "SELECT a.id, a.match_score, a.ats_score, a.created_at, r.original_filename 
     FROM analysis a 
     JOIN resumes r ON a.resume_id = r.id 
     WHERE a.user_id = ? 
     ORDER BY a.created_at DESC 
     LIMIT ? OFFSET ?",
    [$userId, $perPage, $offset]
);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <!-- Header -->
        <div class="row mb-5" data-aos="fade-down">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-history" style="color: #7c3aed;"></i>
                    Analysis History
                </h1>
                <p class="text-muted">View all your previous resume analyses</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4" data-aos="fade-up">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-500">Sort By</label>
                                <select class="form-select" id="sortBy" onchange="applyFilters()">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="score_high">Highest Score</option>
                                    <option value="score_low">Lowest Score</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Filter by Score</label>
                                <select class="form-select" id="scoreFilter" onchange="applyFilters()">
                                    <option value="">All Scores</option>
                                    <option value="80-100">80% - 100%</option>
                                    <option value="60-80">60% - 80%</option>
                                    <option value="40-60">40% - 60%</option>
                                    <option value="0-40">0% - 40%</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Date Range</label>
                                <input type="month" class="form-control" id="dateFilter" onchange="applyFilters()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5" data-aos="fade-up">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="fw-bold" style="color: #7c3aed;"><?php echo $total; ?></h3>
                        <p class="text-muted mb-0">Total Analyses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="fw-bold" style="color: #10b981;">
                            <?php 
                            $maxScore = $db->fetchOne("SELECT MAX(match_score) as max FROM analysis WHERE user_id = ?", [$userId]);
                            echo round($maxScore['max'] ?? 0, 0); 
                            ?>%
                        </h3>
                        <p class="text-muted mb-0">Best Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="fw-bold" style="color: #3b82f6;">
                            <?php 
                            $avgScore = $db->fetchOne("SELECT AVG(match_score) as avg FROM analysis WHERE user_id = ?", [$userId]);
                            echo round($avgScore['avg'] ?? 0, 0);
                            ?>%
                        </h3>
                        <p class="text-muted mb-0">Average Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="fw-bold" style="color: #f59e0b;">
                            <?php 
                            $thisMonth = $db->fetchOne(
                                "SELECT COUNT(*) as count FROM analysis WHERE user_id = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())",
                                [$userId]
                            );
                            echo $thisMonth['count'];
                            ?>
                        </h3>
                        <p class="text-muted mb-0">This Month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyses Table -->
        <div class="row" data-aos="fade-up">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">All Analyses</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($analyses)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                                <p class="text-muted">No analyses found</p>
                                <a href="/pages/upload.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Create Analysis
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
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analyses as $analysis): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i>
                                                        <span><?php echo truncate($analysis['original_filename'], 30); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress" style="width: 100px; height: 25px;">
                                                            <div class="progress-bar" 
                                                                 style="width: <?php echo $analysis['match_score']; ?>%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                                            </div>
                                                        </div>
                                                        <span class="ms-2 fw-bold">
                                                            <?php echo round($analysis['match_score'], 1); ?>%
                                                        </span>
                                                    </div>
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
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4" data-aos="fade-up">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>">Last</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<script>
    function applyFilters() {
        // In a real application, this would apply filters via AJAX
        console.log('Filters applied');
    }
</script>

<?php include '../includes/footer.php'; ?>
