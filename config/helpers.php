<?php

// Guard to prevent redeclaration of functions
if (defined('HELPERS_INCLUDED')) {
    return;
}
define('HELPERS_INCLUDED', true);

/**
 * Helper Functions
 * Get environment variable with fallback
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

/**
 * Load environment variables from .env file
 */
function loadEnv($filePath = '.env')
{
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes if present
        if (in_array($value[0] ?? '', ['"', "'"])) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }

    return true;
}

/**
 * Sanitize string input
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }

    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate random token
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if user is authenticated
 */
function isAuthenticated()
{
    $auth = Auth::getInstance();
    return $auth->isAuthenticated();
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    $auth = Auth::getInstance();
    return $auth->isAdmin();
}

/**
 * Get current user
 */
function getUser()
{
    $auth = Auth::getInstance();
    return $auth->getUser();
}

/**
 * Get current user ID
 */
function getUserId()
{
    $auth = Auth::getInstance();
    return $auth->getUserId();
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Get JSON response
 */
function jsonResponse($data, $statusCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Get error JSON response
 */
function errorJson($message, $statusCode = 400, $data = null)
{
    return jsonResponse([
        'success' => false,
        'message' => $message,
        'data' => $data
    ], $statusCode);
}

/**
 * Get success JSON response
 */
function successJson($message, $data = null, $statusCode = 200)
{
    return jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], $statusCode);
}

/**
 * Format bytes to human-readable size
 */
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get file MIME type
 */
function getMimeType($filePath)
{
    if (function_exists('mime_content_type')) {
        return mime_content_type($filePath);
    }

    $mimeTypes = [
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return $mimeTypes[$ext] ?? 'application/octet-stream';
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName, $prefix = '')
{
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    $timestamp = time();
    $random = substr(uniqid(), -5);

    return "{$prefix}{$name}_{$timestamp}_{$random}.{$ext}";
}

/**
 * Get file hash
 */
function getFileHash($filePath)
{
    return hash_file('sha256', $filePath);
}

/**
 * Create directory if not exists
 */
function ensureDirectory($path)
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
    return $path;
}

/**
 * Log message
 */
function logMessage($message, $level = 'info')
{
    $logDir = env('LOG_PATH', './logs');
    ensureDirectory($logDir);

    $timestamp = date('Y-m-d H:i:s');
    $logFile = $logDir . '/application.log';
    $logEntry = "[$timestamp] [$level] $message\n";

    error_log($logEntry, 3, $logFile);
}

/**
 * Parse duration string to seconds
 */
function parseDuration($duration)
{
    $units = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400
    ];

    if (preg_match('/^(\d+)([smhd])$/', $duration, $matches)) {
        return $matches[1] * ($units[$matches[2]] ?? 1);
    }

    return (int)$duration;
}

/**
 * Get time ago string
 */
function timeAgo($timestamp)
{
    $now = time();
    $diff = $now - strtotime($timestamp);

    if ($diff < 60) {
        return 'Just now';
    }

    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    }

    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }

    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }

    return date('M j, Y', strtotime($timestamp));
}

/**
 * Format number to percentage
 */
function formatPercent($value, $decimals = 2)
{
    return number_format($value * 100, $decimals) . '%';
}

/**
 * Paginate array or query result
 */
function paginate($items, $page = 1, $perPage = 10)
{
    $page = max(1, (int)$page);
    $perPage = max(1, (int)$perPage);

    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = min($page, $totalPages);

    $offset = ($page - 1) * $perPage;
    $items = array_slice($items, $offset, $perPage);

    return [
        'items' => $items,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
        'hasNext' => $page < $totalPages,
        'hasPrev' => $page > 1
    ];
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Clean text for display
 */
function cleanText($text)
{
    $text = preg_replace('/\s+/', ' ', $text);
    $text = preg_replace('/[^\w\s.,!?-]/', '', $text);
    return trim($text);
}

/**
 * Extract domain from email
 */
function getEmailDomain($email)
{
    $parts = explode('@', $email);
    return isset($parts[1]) ? $parts[1] : null;
}

/**
 * Check if debug mode is enabled
 */
function isDebugMode()
{
    return env('APP_DEBUG') === 'true' || env('APP_DEBUG') === '1';
}

/**
 * Get database instance
 */
function getDB()
{
    return Database::getInstance();
}

/**
 * Validate file upload
 */
function validateUpload($file, $maxSize = null, $allowedTypes = [])
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception("No file uploaded");
    }

    $maxSize = $maxSize ?? (int)env('MAX_RESUME_SIZE', 5242880);

    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds maximum of " . formatBytes($maxSize));
    }

    if (!empty($allowedTypes)) {
        $mimeType = getMimeType($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("File type not allowed");
        }
    }

    return true;
}

/**
 * Safe JSON encode
 */
function safeJsonEncode($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Safe JSON decode
 */
function safeJsonDecode($json, $assoc = true)
{
    return json_decode($json, $assoc);
}

/**
 * Array to CSV
 */
function arrayToCsv($array, $delimiter = ',')
{
    $csv = '';
    foreach ($array as $row) {
        foreach ($row as $value) {
            $csv .= '"' . str_replace('"', '""', $value) . '"' . $delimiter;
        }
        $csv = rtrim($csv, $delimiter) . "\n";
    }
    return $csv;
}

/**
 * Convert string to slug
 */
function toSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Rate limit check
 */
function checkRateLimit($key, $maxAttempts = 10, $windowSeconds = 3600)
{
    $store = new RateLimitStore();
    return $store->checkLimit($key, $maxAttempts, $windowSeconds);
}

/**
 * Get system memory usage
 */
function getMemoryUsage()
{
    return [
        'used' => formatBytes(memory_get_usage(true)),
        'peak' => formatBytes(memory_get_peak_usage(true)),
        'percent' => round((memory_get_usage(true) / memory_get_usage()) * 100, 2)
    ];
}
