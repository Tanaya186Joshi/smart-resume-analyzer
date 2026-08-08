<?php

/**
 * Helper Functions - With function existence checks to prevent redeclaration
 * Common utility functions used throughout the application
 */

// Guard to prevent redeclaration of the entire file
if (defined('HELPERS_INCLUDED')) {
    return;
}
define('HELPERS_INCLUDED', true);

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('loadEnv')) {
    function loadEnv($filePath = '.env') {
        if (!file_exists($filePath)) {
            return false;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (in_array($value[0] ?? '', ['"', "'"])) {
                $value = substr($value, 1, -1);
            }
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
        return true;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('getUser')) {
    function getUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('errorJson')) {
    function errorJson($message, $statusCode = 400, $data = null) {
        $response = ['success' => false, 'message' => $message];
        if ($data) $response['data'] = $data;
        jsonResponse($response, $statusCode);
    }
}

if (!function_exists('successJson')) {
    function successJson($message, $data = null, $statusCode = 200) {
        $response = ['success' => true, 'message' => $message];
        if ($data) $response['data'] = $data;
        jsonResponse($response, $statusCode);
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('getMimeType')) {
    function getMimeType($filePath) {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}

if (!function_exists('generateUniqueFilename')) {
    function generateUniqueFilename($originalName, $prefix = '') {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $unique = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        return $unique;
    }
}

if (!function_exists('getFileHash')) {
    function getFileHash($filePath) {
        return hash_file('sha256', $filePath);
    }
}

if (!function_exists('ensureDirectory')) {
    function ensureDirectory($path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return is_dir($path);
    }
}

if (!function_exists('logMessage')) {
    function logMessage($level, $message, $file = 'app.log') {
        $timestamp = date('Y-m-d H:i:s');
        $logDir = 'logs';
        ensureDirectory($logDir);
        $logFile = $logDir . '/' . $file;
        $logLine = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        if ($diff < 60) return $diff . ' seconds ago';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('M d, Y', $time);
    }
}

if (!function_exists('validateUpload')) {
    function validateUpload($file, $maxSize = 5242880, $allowedTypes = ['application/pdf']) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload failed'];
        }
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File too large'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }
        return ['success' => true];
    }
}

if (!function_exists('paginate')) {
    function paginate($total, $perPage = 10, $currentPage = 1) {
        $totalPages = ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        return [
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'hasNext' => $currentPage < $totalPages,
            'hasPrev' => $currentPage > 1
        ];
    }
}

if (!function_exists('truncate')) {
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('toSlug')) {
    function toSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}