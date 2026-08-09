<?php

/**
 * Resume PDF Extraction API
 * Extracts text from PDF resumes and stores parsed content
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/helpers.php';

// Load autoloader if using Composer
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}

use Smalot\PdfParser\Parser;

header('Content-Type: application/json');

// Verify authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    errorJson("Unauthorized", 401);
}

$userId = $auth->getUserId();
$db = Database::getInstance();

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        handleUpload($db, $auth, $userId);
    } elseif ($method === 'GET') {
        handleFetch($db, $userId);
    } else {
        errorJson("Method not allowed", 405);
    }

} catch (Exception $e) {
    logMessage("API Error in extract.php: " . $e->getMessage(), 'error');
    errorJson($e->getMessage(), 500);
}

/**
 * Handle resume upload
 */
function handleUpload($db, $auth, $userId)
{
    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? null;
    if (!$csrfToken || !verifyCsrfToken($csrfToken)) {
        errorJson("Invalid CSRF token", 403);
    }

    // Check file upload
    if (!isset($_FILES['resume'])) {
        errorJson("No file uploaded", 400);
    }

    $file = $_FILES['resume'];
    $maxSize = (int)env('MAX_RESUME_SIZE', 5242880);
    $uploadDir = env('UPLOAD_DIRECTORY', './uploads');
    ensureDirectory($uploadDir);

    // Validate upload
    try {
        validateUpload($file, $maxSize, ['application/pdf']);
    } catch (Exception $e) {
        errorJson($e->getMessage(), 400);
    }

    // Check for duplicate
    $fileHash = hash_file('sha256', $file['tmp_name']);
    $existingResume = $db->fetchOne(
        "SELECT id FROM resumes WHERE user_id = ? AND file_hash = ?",
        [$userId, $fileHash]
    );

    if ($existingResume) {
        errorJson("This resume has already been uploaded", 409);
    }

    // Check user resume limit
    $resumeCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM resumes WHERE user_id = ?",
        [$userId]
    );

    $maxResumes = (int)env('MAX_RESUMES_PER_USER', 10);
    if ($resumeCount['count'] >= $maxResumes) {
        errorJson("Maximum resume limit reached", 429);
    }

    // Generate unique filename
    $uniqueFilename = generateUniqueFilename($file['name'], 'resume_');
    $filePath = $uploadDir . '/' . $uniqueFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        errorJson("Failed to save file", 500);
    }

    try {
        // Extract PDF text
        $resumeText = extractPdfText($filePath);

        if (empty($resumeText)) {
            unlink($filePath);
            errorJson("Failed to extract text from PDF", 400);
        }

        // Store in database
        $resumeId = $db->insert(
            "INSERT INTO resumes (user_id, filename, original_filename, resume_text, file_size, file_hash, is_parsed, parsed_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$userId, $uniqueFilename, $file['name'], $resumeText, $file['size'], $fileHash, true]
        );

        // Extract skills
        $skills = extractSkills($resumeText, $db);
        storeSkills($resumeId, $skills, $db);

        // Log event
        logEvent($db, $userId, 'resume_uploaded', [
            'resume_id' => $resumeId,
            'filename' => $file['name'],
            'size' => $file['size']
        ]);

        successJson("Resume uploaded successfully", [
            'resume_id' => $resumeId,
            'filename' => $file['name'],
            'size' => formatBytes($file['size']),
            'skills_detected' => count($skills)
        ]);

    } catch (Exception $e) {
        unlink($filePath);
        logMessage("PDF extraction error: " . $e->getMessage(), 'error');
        errorJson("Failed to process resume: " . $e->getMessage(), 500);
    }
}

/**
 * Fetch resume details
 */
function handleFetch($db, $userId)
{
    $resumeId = $_GET['id'] ?? null;

    if (!$resumeId) {
        // Fetch all resumes for user
        $resumes = $db->fetchAll(
            "SELECT id, original_filename, file_size, uploaded_at FROM resumes WHERE user_id = ? ORDER BY uploaded_at DESC",
            [$userId]
        );

        foreach ($resumes as &$resume) {
            $resume['file_size_formatted'] = formatBytes($resume['file_size']);
            $resume['time_ago'] = timeAgo($resume['uploaded_at']);
        }

        successJson("Resumes retrieved", ['resumes' => $resumes]);
        return;
    }

    // Fetch specific resume
    $resume = $db->fetchOne(
        "SELECT id, original_filename, file_size, uploaded_at, is_parsed FROM resumes WHERE id = ? AND user_id = ?",
        [$resumeId, $userId]
    );

    if (!$resume) {
        errorJson("Resume not found", 404);
    }

    // Get detected skills
    $skills = $db->fetchAll(
        "SELECT skill_name, category, proficiency_level, confidence_score FROM detected_skills WHERE resume_id = ? ORDER BY confidence_score DESC",
        [$resumeId]
    );

    $resume['file_size_formatted'] = formatBytes($resume['file_size']);
    $resume['skills'] = $skills;

    successJson("Resume retrieved", ['resume' => $resume]);
}

/**
 * Extract text from PDF
 */
function extractPdfText($filePath)
{
    if (!class_exists('Smalot\PdfParser\Parser')) {
        // Fallback to command-line tool if library not available
        return extractPdfTextCli($filePath);
    }

    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $text = '';
        foreach ($pdf->getPages() as $page) {
            $text .= $page->getText() . "\n";
        }

        // Clean and normalize text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[^\w\s.,!?\-@+()\\/]/', '', $text);
        $text = trim($text);

        return $text;

    } catch (Exception $e) {
        logMessage("PDF parsing error: " . $e->getMessage(), 'error');
        return extractPdfTextCli($filePath);
    }
}

/**
 * Extract PDF text using command-line tools
 */
function extractPdfTextCli($filePath)
{
    // Check if pdftotext is available
    exec('which pdftotext', $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception("PDF extraction tools not available");
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
    $command = "pdftotext -layout " . escapeshellarg($filePath) . " " . escapeshellarg($tempFile);

    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        @unlink($tempFile);
        throw new Exception("Failed to extract PDF text");
    }

    $text = file_get_contents($tempFile);
    @unlink($tempFile);

    // Clean text
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    return $text;
}

/**
 * Extract skills from resume text
 */
function extractSkills($text, $db)
{
    $text = strtolower($text);
    $skills = [];

    // Get skill categories
    $skillCategories = $db->fetchAll("SELECT skill_name, category, aliases FROM skill_categories");

    foreach ($skillCategories as $skill) {
        $patterns = [$skill['skill_name']];

        // Add aliases
        if ($skill['aliases']) {
            $aliases = json_decode($skill['aliases'], true);
            $patterns = array_merge($patterns, $aliases);
        }

        foreach ($patterns as $pattern) {
            $pattern = strtolower($pattern);
            // Word boundary matching
            if (preg_match('/\b' . preg_quote($pattern) . '\b/i', $text)) {
                $frequency = substr_count($text, $pattern);
                $confidence = min(1, $frequency / 5); // Confidence increases with frequency

                $skills[$skill['skill_name']] = [
                    'category' => $skill['category'],
                    'frequency' => $frequency,
                    'confidence' => $confidence
                ];

                break; // Use first match
            }
        }
    }

    return $skills;
}

/**
 * Store detected skills
 */
function storeSkills($resumeId, $skills, $db)
{
    foreach ($skills as $skillName => $skillData) {
        try {
            $db->insert(
                "INSERT INTO detected_skills (resume_id, skill_name, category, frequency, confidence_score) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE frequency = frequency + 1, confidence_score = ?",
                [
                    $resumeId,
                    $skillName,
                    $skillData['category'],
                    $skillData['frequency'],
                    $skillData['confidence'],
                    $skillData['confidence']
                ]
            );
        } catch (Exception $e) {
            logMessage("Error storing skill $skillName: " . $e->getMessage(), 'warning');
        }
    }
}

/**
 * Log event
 */
function logEvent($db, $userId, $eventType, $eventData)
{
    try {
        $db->insert(
            "INSERT INTO analytics (user_id, event_type, event_data, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
            [
                $userId,
                $eventType,
                json_encode($eventData),
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]
        );
    } catch (Exception $e) {
        logMessage("Error logging event: " . $e->getMessage(), 'warning');
    }
}