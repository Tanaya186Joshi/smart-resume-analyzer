<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Analysis Results';

// Check authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    redirect('/pages/login.php');
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$analysisId = $_GET['id'] ?? null;
if (!$analysisId) {
    redirect('/pages/dashboard.php');
}

// Get analysis data
$analysis = $db->fetchOne(
    "SELECT a.*, r.original_filename 
     FROM analysis a 
     JOIN resumes r ON a.resume_id = r.id 
     WHERE a.id = ? AND a.user_id = ?",
    [$analysisId, $userId]
);

if (!$analysis) {
    redirect('/pages/dashboard.php');
}

// Parse JSON data
$matchedSkills = json_decode($analysis['matched_skills'], true) ?? [];
$missingSkills = json_decode($analysis['missing_skills'], true) ?? [];
$suggestions = json_decode($analysis['suggestions'], true) ?? [];
$skillGap = json_decode($analysis['skill_gap_analysis'], true) ?? [];
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <!-- Header -->
        <div class="row mb-5" data-aos="fade-down">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h1 class="display-5 fw-bold mb-0">
                        <i class="fas fa-chart-bar" style="color: #7c3aed;"></i>
                        Analysis Results
                    </h1>
                    <a href="/pages/upload.php" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i> New Analysis
                    </a>
                </div>
                <p class="text-muted">
                    <i class="fas fa-file-pdf me-2"></i>
                    <?php echo sanitize($analysis['original_filename']); ?> 
                    • 
                    <small><?php echo timeAgo($analysis['created_at']); ?></small>
                </p>
            </div>
        </div>

        <!-- Score Cards -->
        <div class="row mb-5" data-aos="fade-up">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <div class="score-circle mx-auto mb-3" 
                             style="width: 120px; height: 120px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); display: flex; align-items: center; justify-content: center;">
                            <div>
                                <h3 class="mb-0 fw-bold" style="color: #7c3aed;">
                                    <?php echo round($analysis['match_score'], 0); ?>%
                                </h3>
                                <small class="text-muted">Match Score</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <div class="score-circle mx-auto mb-3" 
                             style="width: 120px; height: 120px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center;">
                            <div>
                                <h3 class="mb-0 fw-bold" style="color: #10b981;">
                                    <?php echo round($analysis['ats_score'], 0); ?>%
                                </h3>
                                <small class="text-muted">ATS Score</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <div class="score-circle mx-auto mb-3" 
                             style="width: 120px; height: 120px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center;">
                            <div>
                                <h3 class="mb-0 fw-bold" style="color: #3b82f6;">
                                    <?php echo count($matchedSkills); ?>
                                </h3>
                                <small class="text-muted">Skills Matched</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Matched Skills -->
            <div class="col-lg-6 mb-5" data-aos="fade-right">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                            Matched Skills (<?php echo count($matchedSkills); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($matchedSkills)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($matchedSkills as $skill => $data): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>
                                        <?php echo sanitize($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3 mb-0">
                                <i class="fas fa-inbox"></i> No matched skills found
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Missing Skills -->
            <div class="col-lg-6 mb-5" data-aos="fade-left">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-exclamation-circle me-2" style="color: #ef4444;"></i>
                            Missing Skills (<?php echo count($missingSkills); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($missingSkills)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($missingSkills as $skill => $data): ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>
                                        <?php echo sanitize($skill); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3 mb-0">
                                <i class="fas fa-check-circle" style="color: #10b981;"></i> Perfect match!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skill Gap Analysis -->
        <?php if (!empty($skillGap)): ?>
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="fas fa-chart-pie me-2" style="color: #3b82f6;"></i>
                                Skill Gap Analysis
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category</th>
                                            <th>Matched</th>
                                            <th>Total</th>
                                            <th>Coverage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($skillGap as $category => $stats): ?>
                                            <tr>
                                                <td class="fw-500"><?php echo sanitize($category); ?></td>
                                                <td><?php echo $stats['matched']; ?></td>
                                                <td><?php echo $stats['total']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1" style="height: 20px; min-width: 100px;">
                                                            <div class="progress-bar" 
                                                                 style="width: <?php echo $stats['percentage']; ?>%">
                                                            </div>
                                                        </div>
                                                        <span class="ms-2 badge" 
                                                              style="background: rgba(124, 58, 237, 0.2); color: #7c3aed;">
                                                            <?php echo round($stats['percentage'], 0); ?>%
                                                        </span>
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
        <?php endif; ?>

        <!-- Suggestions -->
        <?php if (!empty($suggestions)): ?>
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="fas fa-lightbulb me-2" style="color: #f59e0b;"></i>
                                Improvement Suggestions
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($suggestions as $index => $suggestion): ?>
                                <div class="alert mb-3" 
                                     style="background: rgba(<?php 
                                        echo $suggestion['priority'] === 'high' ? '239, 68, 68' : ($suggestion['priority'] === 'medium' ? '245, 158, 11' : '6, 182, 212');
                                     ?>, 0.1); border: 1px solid rgba(<?php 
                                        echo $suggestion['priority'] === 'high' ? '239, 68, 68' : ($suggestion['priority'] === 'medium' ? '245, 158, 11' : '6, 182, 212');
                                     ?>, 0.3);" role="alert">
                                    <div class="d-flex">
                                        <div>
                                            <i class="fas fa-circle-info me-2" 
                                               style="color: <?php 
                                                   echo $suggestion['priority'] === 'high' ? '#ef4444' : ($suggestion['priority'] === 'medium' ? '#f59e0b' : '#06b6d4');
                                               ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2">
                                                <span class="badge" 
                                                      style="background: <?php 
                                                          echo $suggestion['priority'] === 'high' ? '#ef4444' : ($suggestion['priority'] === 'medium' ? '#f59e0b' : '#06b6d4');
                                                      ?>; font-size: 0.7rem;">
                                                    <?php echo ucfirst($suggestion['priority']); ?>
                                                </span>
                                            </h6>
                                            <p class="mb-0"><?php echo sanitize($suggestion['message']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="text-center">
                    <a href="/pages/upload.php" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-plus me-2"></i> New Analysis
                    </a>
                    <a href="/pages/history.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-history me-2"></i> View History
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
    }

    .progress {
        background-color: #e5e7eb;
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
</style>

<?php include '../includes/footer.php'; ?>
