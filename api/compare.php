<?php

/**
 * Resume vs Job Description Comparison API
 * Analyzes resume match against job description using NLP
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');

// Verify authentication
$auth = Auth::getInstance();
if (!$auth->isAuthenticated()) {
    errorJson("Unauthorized", 401);
}

$userId = $auth->getUserId();
$db = Database::getInstance();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorJson("Method not allowed", 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['resume_id']) || empty($input['job_description'])) {
        errorJson("Resume ID and job description required", 400);
    }

    $resumeId = (int)$input['resume_id'];
    $jobDescription = sanitize($input['job_description']);

    // Verify resume belongs to user
    $resume = $db->fetchOne(
        "SELECT resume_text FROM resumes WHERE id = ? AND user_id = ?",
        [$resumeId, $userId]
    );

    if (!$resume) {
        errorJson("Resume not found", 404);
    }

    // Perform analysis
    $analysis = performAnalysis($resume['resume_text'], $jobDescription, $db);

    // Store analysis in database
    $analysisId = $db->insert(
        "INSERT INTO analysis (resume_id, user_id, job_description, match_score, ats_score, matched_skills, missing_skills, suggestions, skill_gap_analysis, nlp_model) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $resumeId,
            $userId,
            $jobDescription,
            $analysis['match_score'],
            $analysis['ats_score'],
            json_encode($analysis['matched_skills']),
            json_encode($analysis['missing_skills']),
            json_encode($analysis['suggestions']),
            json_encode($analysis['skill_gap']),
            'local-nlp'
        ]
    );

    // Log event
    logEvent($db, $userId, 'analysis_created', [
        'analysis_id' => $analysisId,
        'resume_id' => $resumeId,
        'match_score' => $analysis['match_score']
    ]);

    successJson("Analysis complete", [
        'analysis_id' => $analysisId,
        'match_score' => $analysis['match_score'],
        'ats_score' => $analysis['ats_score'],
        'matched_skills' => $analysis['matched_skills'],
        'missing_skills' => $analysis['missing_skills'],
        'suggestions' => $analysis['suggestions'],
        'skill_gap' => $analysis['skill_gap']
    ]);

} catch (Exception $e) {
    logMessage("Comparison error: " . $e->getMessage(), 'error');
    errorJson($e->getMessage(), 500);
}

/**
 * Perform resume vs job description analysis
 */
function performAnalysis($resumeText, $jobDescription, $db)
{
    // Extract requirements from job description
    $jobSkills = extractSkillsFromText($jobDescription, $db);
    $jobRequirements = extractRequirements($jobDescription);

    // Get resume skills
    $resumeWords = preg_split('/\s+/', strtolower($resumeText));
    $resumeSkills = extractSkillsFromText($resumeText, $db);

    // Calculate skill matches
    $matchedSkills = [];
    $missingSkills = [];

    foreach ($jobSkills as $skill => $data) {
        if (isset($resumeSkills[$skill])) {
            $matchedSkills[$skill] = [
                'category' => $data['category'],
                'proficiency' => $resumeSkills[$skill]['confidence'] ?? 0.5
            ];
        } else {
            $missingSkills[$skill] = [
                'category' => $data['category'],
                'importance' => 'high'
            ];
        }
    }

    // Calculate match score
    $totalSkills = count($jobSkills);
    $matchScore = $totalSkills > 0 ? (count($matchedSkills) / $totalSkills) * 100 : 0;

    // Calculate ATS score (simulated)
    $atsScore = calculateAtsScore($resumeText, $jobRequirements, $matchedSkills);

    // Generate suggestions
    $suggestions = generateSuggestions($resumeText, $jobDescription, $matchedSkills, $missingSkills);

    // Skill gap analysis
    $skillGap = analyzeSkillGap($matchedSkills, $missingSkills);

    return [
        'match_score' => round($matchScore, 2),
        'ats_score' => round($atsScore, 2),
        'matched_skills' => $matchedSkills,
        'missing_skills' => $missingSkills,
        'suggestions' => $suggestions,
        'skill_gap' => $skillGap
    ];
}

/**
 * Extract skills from text
 */
function extractSkillsFromText($text, $db)
{
    $text = strtolower($text);
    $foundSkills = [];

    $skillCategories = $db->fetchAll("SELECT skill_name, category FROM skill_categories");

    foreach ($skillCategories as $skill) {
        $skillLower = strtolower($skill['skill_name']);

        if (preg_match('/\b' . preg_quote($skillLower) . '\b/', $text)) {
            $frequency = substr_count($text, $skillLower);
            $confidence = min(1, $frequency / 3);

            $foundSkills[$skill['skill_name']] = [
                'category' => $skill['category'],
                'frequency' => $frequency,
                'confidence' => $confidence
            ];
        }
    }

    return $foundSkills;
}

/**
 * Extract job requirements
 */
function extractRequirements($jobDescription)
{
    $requirements = [
        'education' => null,
        'experience' => null,
        'location' => null,
        'salary' => null
    ];

    // Extract years of experience
    if (preg_match('/(\d+)\+?\s*years?\s*of\s*experience/i', $jobDescription, $matches)) {
        $requirements['experience'] = (int)$matches[1];
    }

    // Extract education level
    if (preg_match('/(bachelor|master|phd|degree|diploma)/i', $jobDescription)) {
        $requirements['education'] = 'degree';
    }

    return $requirements;
}

/**
 * Calculate ATS (Applicant Tracking System) score
 */
function calculateAtsScore($resumeText, $requirements, $matchedSkills)
{
    $score = 50; // Base score

    // Format and structure bonus
    $score += 15;

    // Skill match bonus
    $skillBonus = min(25, count($matchedSkills) * 2.5);
    $score += $skillBonus;

    // Keywords density bonus
    $keywords = ['professional', 'experienced', 'skilled', 'managed', 'developed', 'achieved'];
    $keywordCount = 0;
    foreach ($keywords as $keyword) {
        $keywordCount += substr_count(strtolower($resumeText), strtolower($keyword));
    }
    $keywordBonus = min(10, $keywordCount * 0.5);
    $score += $keywordBonus;

    // Experience match
    if ($requirements['experience'] && preg_match('/' . $requirements['experience'] . '\+?\s*years?/i', $resumeText)) {
        $score += 10;
    }

    // Education match
    if ($requirements['education'] && preg_match('/(bachelor|master|phd|degree)/i', $resumeText)) {
        $score += 10;
    }

    return min(100, $score);
}

/**
 * Generate improvement suggestions
 */
function generateSuggestions($resumeText, $jobDescription, $matchedSkills, $missingSkills)
{
    $suggestions = [];

    // Missing skills suggestions
    if (!empty($missingSkills)) {
        $topMissing = array_slice($missingSkills, 0, 3, true);
        foreach ($topMissing as $skill => $data) {
            $suggestions[] = [
                'type' => 'skill_gap',
                'priority' => 'high',
                'message' => "Add $skill to your resume. This is a key requirement for the position.",
                'skill' => $skill,
                'category' => $data['category']
            ];
        }
    }

    // Format suggestions
    $suggestions[] = [
        'type' => 'formatting',
        'priority' => 'medium',
        'message' => "Use clear section headers (Experience, Education, Skills) for better ATS parsing."
    ];

    // Keyword density suggestions
    $jobKeywords = extractKeywords($jobDescription);
    $missingKeywords = [];

    foreach ($jobKeywords as $keyword) {
        if (stripos($resumeText, $keyword) === false) {
            $missingKeywords[] = $keyword;
        }
    }

    if (!empty($missingKeywords)) {
        $suggestions[] = [
            'type' => 'keywords',
            'priority' => 'medium',
            'message' => "Incorporate key job description terms: " . implode(', ', array_slice($missingKeywords, 0, 5)),
            'keywords' => array_slice($missingKeywords, 0, 5)
        ];
    }

    // Length suggestion
    $words = str_word_count($resumeText);
    if ($words < 200) {
        $suggestions[] = [
            'type' => 'content',
            'priority' => 'low',
            'message' => "Consider adding more details to your resume. Current word count is $words."
        ];
    } elseif ($words > 1000) {
        $suggestions[] = [
            'type' => 'content',
            'priority' => 'low',
            'message' => "Your resume might be too long ($words words). Consider removing less relevant details."
        ];
    }

    // Quantifiable achievements suggestion
    if (!preg_match('/(\d+%|\$\d+|increased|improved|reduced)/i', $resumeText)) {
        $suggestions[] = [
            'type' => 'achievement',
            'priority' => 'medium',
            'message' => "Add quantifiable achievements (e.g., 'increased sales by 30%') to make your impact more convincing."
        ];
    }

    return $suggestions;
}

/**
 * Extract keywords from text
 */
function extractKeywords($text)
{
    $text = strtolower($text);
    
    // Common stop words
    $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'will', 'be'];

    // Split into words
    $words = preg_split('/\s+/', $text);
    $words = array_filter($words, function($word) use ($stopWords) {
        return strlen($word) > 3 && !in_array($word, $stopWords);
    });

    // Get most common words
    $wordCounts = array_count_values($words);
    arsort($wordCounts);

    return array_keys(array_slice($wordCounts, 0, 15));
}

/**
 * Analyze skill gap
 */
function analyzeSkillGap($matchedSkills, $missingSkills)
{
    $categories = [];

    foreach ($matchedSkills as $skill => $data) {
        $cat = $data['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = ['matched' => 0, 'total' => 0];
        }
        $categories[$cat]['matched']++;
        $categories[$cat]['total']++;
    }

    foreach ($missingSkills as $skill => $data) {
        $cat = $data['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = ['matched' => 0, 'total' => 0];
        }
        $categories[$cat]['total']++;
    }

    $gap = [];
    foreach ($categories as $category => $stats) {
        $gap[$category] = [
            'matched' => $stats['matched'],
            'total' => $stats['total'],
            'percentage' => $stats['total'] > 0 ? round(($stats['matched'] / $stats['total']) * 100, 2) : 0
        ];
    }

    return $gap;
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
