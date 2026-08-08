<?php
// Load environment and dependencies
if (!file_exists('.env')) {
    die('Configuration file not found. Please create .env from .env.example');
}

require_once 'config/helpers.php';
loadEnv('.env');

require_once 'config/database.php';
require_once 'config/auth.php';

$pageTitle = 'Home';
$additionalCSS = ['/assets/css/landing.css'];

// If authenticated, redirect to dashboard
$auth = Auth::getInstance();
if ($auth->isAuthenticated()) {
    redirect('/pages/dashboard.php');
}
?>

<?php include 'includes/header.php'; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold" href="/">
            <span style="color: #7c3aed;">
                <i class="fas fa-file-pdf"></i> Smart Resume Analyzer
            </span>
        </a>
        <div class="ms-auto">
            <a href="/pages/login.php" class="btn btn-outline-light me-2">Login</a>
            <a href="/pages/register.php" class="btn btn-primary">Get Started</a>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section -->
    <section class="hero-section pt-5 pb-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">
                        Optimize Your Resume in Seconds
                    </h1>
                    <p class="lead mb-4">
                        Get AI-powered insights on your resume match with job descriptions. 
                        Improve your ATS score and land more interviews.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="/pages/register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i> Start Free
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            Learn More
                        </a>
                    </div>
                    <p class="text-light mt-3">
                        <small>✨ No credit card required • Free for 5 analyses • Takes 2 minutes</small>
                    </p>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div style="text-align: center;">
                        <i class="fas fa-file-chart-line" style="font-size: 300px; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Powerful Features</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="feature-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-upload" style="font-size: 2.5rem; color: #7c3aed;"></i>
                            </div>
                            <h5 class="card-title fw-bold">Easy Upload</h5>
                            <p class="card-text text-muted">
                                Upload your PDF resume securely. We support files up to 5MB.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-brain" style="font-size: 2.5rem; color: #7c3aed;"></i>
                            </div>
                            <h5 class="card-title fw-bold">AI Analysis</h5>
                            <p class="card-text text-muted">
                                Advanced NLP technology analyzes your resume content.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-chart-line" style="font-size: 2.5rem; color: #7c3aed;"></i>
                            </div>
                            <h5 class="card-title fw-bold">Match Score</h5>
                            <p class="card-text text-muted">
                                Get ATS match percentage for any job description.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-lightbulb" style="font-size: 2.5rem; color: #7c3aed;"></i>
                            </div>
                            <h5 class="card-title fw-bold">Suggestions</h5>
                            <p class="card-text text-muted">
                                Get actionable tips to improve your resume.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">How It Works</h2>
            <div class="row g-4">
                <div class="col-md-4 text-center" data-aos="fade-up">
                    <div class="step-number mb-3" style="width: 60px; height: 60px; margin: 0 auto; background: #7c3aed; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                        1
                    </div>
                    <h5 class="fw-bold mb-3">Upload Resume</h5>
                    <p class="text-muted">
                        Upload your PDF resume. It takes less than a minute.
                    </p>
                </div>

                <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-number mb-3" style="width: 60px; height: 60px; margin: 0 auto; background: #7c3aed; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                        2
                    </div>
                    <h5 class="fw-bold mb-3">Paste Job Description</h5>
                    <p class="text-muted">
                        Add the job description you want to match against.
                    </p>
                </div>

                <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-number mb-3" style="width: 60px; height: 60px; margin: 0 auto; background: #7c3aed; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                        3
                    </div>
                    <h5 class="fw-bold mb-3">Get Results</h5>
                    <p class="text-muted">
                        Receive detailed analysis and improvement suggestions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center" data-aos="zoom-in">
            <h2 class="fw-bold mb-4">Ready to Improve Your Resume?</h2>
            <p class="lead mb-4">
                Join thousands of job seekers who've already improved their chances.
            </p>
            <a href="/pages/register.php" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i> Create Free Account
            </a>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Frequently Asked Questions</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item" data-aos="fade-up">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Is my resume data safe?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, your resume data is encrypted and stored securely. We never share your data with third parties.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How accurate is the analysis?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Our AI analysis is trained on thousands of resumes and job descriptions. Average accuracy is 92%.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Can I delete my account?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, you can delete your account anytime from your settings. All your data will be permanently removed.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    What file formats are supported?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Currently, we support PDF files. Word documents (DOCX) support coming soon.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .hero-section {
        position: relative;
        overflow: hidden;
    }

    .feature-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .step-number {
        font-size: 1.5rem;
    }

    .accordion-button:not(.collapsed) {
        background-color: #7c3aed;
        color: white;
    }
</style>

<?php include 'includes/footer.php'; ?>
