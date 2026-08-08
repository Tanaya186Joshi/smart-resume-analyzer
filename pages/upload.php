<?php
require_once '../config/helpers.php';
loadEnv('../.env');

require_once '../config/database.php';
require_once '../config/auth.php';

$pageTitle = 'Upload & Analyze';

// Check authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    redirect('/pages/login.php');
}

$user = $auth->getUser();
$userId = $auth->getUserId();
$db = Database::getInstance();

// Get CSRF token
$csrfToken = generateCsrfToken();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <div class="row" data-aos="fade-down">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-cloud-upload-alt" style="color: #7c3aed;"></i>
                    Upload & Analyze Resume
                </h1>
                <p class="text-muted">Upload your PDF resume and compare it with a job description</p>
            </div>
        </div>

        <div class="row mt-5">
            <!-- Upload Section -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i>
                            Step 1: Upload Resume
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                            <!-- File Upload Area -->
                            <div class="upload-area border-2 border-dashed rounded-3 p-5 text-center mb-4" 
                                 style="background: rgba(124, 58, 237, 0.05); border-color: #d1d5db; cursor: pointer; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #7c3aed; margin-bottom: 1rem;"></i>
                                <h6 class="fw-bold mb-2">Drag & drop your PDF resume here</h6>
                                <p class="text-muted mb-3">or click to select a file</p>
                                <input type="file" id="resumeFile" name="resume" class="d-none" accept=".pdf" required>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('resumeFile').click()">
                                    <i class="fas fa-browse me-2"></i> Choose File
                                </button>
                                <p class="text-muted small mt-3 mb-0">
                                    Maximum file size: 5MB • Format: PDF only
                                </p>
                            </div>

                            <!-- File Info -->
                            <div id="fileInfo" class="alert alert-info d-none" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf me-3" style="font-size: 1.5rem; color: #ef4444;"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1" id="fileName"></h6>
                                        <small id="fileSize"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="d-none mb-4">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="progressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Uploading... <span id="progressText">0%</span>
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="uploadBtn">
                                <i class="fas fa-upload me-2"></i> Upload Resume
                            </button>
                        </form>

                        <!-- Uploaded Resumes List -->
                        <div id="resumesList" class="mt-5">
                            <h6 class="fw-bold mb-3">Your Resumes</h6>
                            <div id="resumesContainer" class="list-group">
                                <p class="text-muted text-center py-3">Loading resumes...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analysis Section -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-search me-2" style="color: #06b6d4;"></i>
                            Step 2: Analyze Job Match
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="analyzeForm">
                            <div class="mb-3">
                                <label for="resumeSelect" class="form-label fw-500">
                                    <i class="fas fa-file me-2"></i> Select Resume
                                </label>
                                <select class="form-select form-select-lg" id="resumeSelect" required>
                                    <option value="">-- Choose a resume --</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    Select a previously uploaded resume or upload a new one first
                                </small>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="jobDescription" class="form-label fw-500">
                                    <i class="fas fa-briefcase me-2"></i> Job Description
                                </label>
                                <textarea class="form-control form-control-lg" id="jobDescription" name="job_description" 
                                         rows="8" placeholder="Paste the job description here..." required></textarea>
                                <small class="text-muted d-block mt-2">
                                    Copy and paste the entire job description for better analysis
                                </small>
                            </div>

                            <!-- Analysis Options -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="detailedAnalysis" checked>
                                    <label class="form-check-label" for="detailedAnalysis">
                                        Detailed analysis (includes suggestions)
                                    </label>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div id="analyzeProgress" class="d-none mb-4">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Analyzing...</span>
                                </div>
                                <span class="text-muted">Analyzing your resume against the job description...</span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="analyzeBtn">
                                <i class="fas fa-magic me-2"></i> Analyze Resume
                            </button>
                        </form>

                        <!-- Results Preview -->
                        <div id="resultsPreview" class="d-none mt-4 p-4 rounded-3" style="background: rgba(16, 185, 129, 0.05);">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; margin-right: 1rem;"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Analysis Complete!</h6>
                                    <small class="text-muted">Click "View Detailed Results" to see full analysis</small>
                                </div>
                            </div>
                            <a href="#" id="viewResultsBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-chart-bar me-1"></i> View Detailed Results
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="card border-0 shadow-sm mt-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(6, 182, 212, 0.1));">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2" style="color: #3b82f6;"></i>
                            How This Works
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check me-2" style="color: #10b981;"></i>
                                <strong>AI Analysis:</strong> Uses advanced NLP to compare your resume with the job description
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check me-2" style="color: #10b981;"></i>
                                <strong>Skill Matching:</strong> Identifies matched and missing skills
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check me-2" style="color: #10b981;"></i>
                                <strong>ATS Score:</strong> Evaluates resume format for Applicant Tracking Systems
                            </li>
                            <li>
                                <i class="fas fa-check me-2" style="color: #10b981;"></i>
                                <strong>Suggestions:</strong> Get actionable tips to improve your match
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .upload-area:hover {
        background: rgba(124, 58, 237, 0.1) !important;
        border-color: #7c3aed !important;
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

    .list-group-item {
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: rgba(124, 58, 237, 0.05);
        border-color: #7c3aed;
    }
</style>

<script>
    const uploadForm = document.getElementById('uploadForm');
    const analyzeForm = document.getElementById('analyzeForm');
    const resumeSelect = document.getElementById('resumeSelect');
    const uploadArea = document.querySelector('.upload-area');
    const fileInput = document.getElementById('resumeFile');
    const fileInfo = document.getElementById('fileInfo');
    const uploadBtn = document.getElementById('uploadBtn');
    const analyzeBtn = document.getElementById('analyzeBtn');

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.style.borderColor = '#7c3aed';
            uploadArea.style.background = 'rgba(124, 58, 237, 0.1)';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.style.borderColor = '#d1d5db';
            uploadArea.style.background = 'rgba(124, 58, 237, 0.05)';
        });
    });

    uploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        displayFileInfo(files[0]);
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            displayFileInfo(e.target.files[0]);
        }
    });

    function displayFileInfo(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        fileInfo.classList.remove('d-none');
    }

    function resetFile() {
        fileInput.value = '';
        fileInfo.classList.add('d-none');
    }

    // Upload form submission
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(uploadForm);
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

        try {
            const response = await fetch('/api/extract.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Resume uploaded successfully!', 'success');
                uploadForm.reset();
                fileInfo.classList.add('d-none');
                loadResumes();
                
                // Show uploaded resume in select
                setTimeout(() => {
                    loadResumes();
                }, 500);
            } else {
                showNotification(result.message || 'Upload failed', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Upload failed. Please try again.', 'error');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i> Upload Resume';
        }
    });

    // Load resumes
    async function loadResumes() {
        try {
            const response = await fetch('/api/extract.php?id=');
            const result = await response.json();

            if (result.success && result.data.resumes.length > 0) {
                resumeSelect.innerHTML = '<option value="">-- Choose a resume --</option>';
                const resumesContainer = document.getElementById('resumesContainer');
                resumesContainer.innerHTML = '';

                result.data.resumes.forEach((resume, index) => {
                    resumeSelect.innerHTML += `<option value="${resume.id}">${resume.original_filename}</option>`;
                    
                    resumesContainer.innerHTML += `
                        <div class="list-group-item p-3 mb-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf me-3" style="font-size: 1.5rem; color: #ef4444;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${resume.original_filename}</h6>
                                    <small class="text-muted">${resume.file_size_formatted} • ${resume.time_ago}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="resumeSelect.value=${resume.id}; document.querySelector('#jobDescription').focus();">
                                    <i class="fas fa-arrow-down me-1"></i> Use
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
        } catch (error) {
            console.error('Error loading resumes:', error);
        }
    }

    // Analyze form submission
    analyzeForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const resumeId = resumeSelect.value;
        const jobDescription = document.getElementById('jobDescription').value;

        if (!resumeId) {
            showNotification('Please select a resume first', 'warning');
            return;
        }

        if (!jobDescription.trim()) {
            showNotification('Please enter a job description', 'warning');
            return;
        }

        document.getElementById('analyzeProgress').classList.remove('d-none');
        analyzeBtn.disabled = true;

        try {
            const response = await fetch('/api/compare.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    resume_id: resumeId,
                    job_description: jobDescription
                })
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Analysis complete!', 'success');
                
                // Show results preview
                document.getElementById('resultsPreview').classList.remove('d-none');
                document.getElementById('viewResultsBtn').href = `/pages/results.php?id=${result.data.analysis_id}`;
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = `/pages/results.php?id=${result.data.analysis_id}`;
                }, 2000);
            } else {
                showNotification(result.message || 'Analysis failed', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Analysis failed. Please try again.', 'error');
        } finally {
            document.getElementById('analyzeProgress').classList.add('d-none');
            analyzeBtn.disabled = false;
        }
    });

    // Load resumes on page load
    loadResumes();
</script>

<?php include '../includes/footer.php'; ?>
