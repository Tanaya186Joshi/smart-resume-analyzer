<?php

/**
 * Authentication & Session Management
 * Handles user login, logout, registration, and session validation
 */

class Auth
{
    private static $instance = null;
    private $db;
    private const SESSION_KEY = 'user_id';
    private const REMEMBER_DURATION = 2592000; // 30 days

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->initializeSession();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize session with proper settings
     */
    private function initializeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieSettings = [
                'lifetime' => (int)getenv('SESSION_LIFETIME') * 60 ?: 7200,
                'path' => '/',
                'domain' => '',
                'secure' => getenv('SESSION_COOKIE_SECURE') === 'true',
                'httponly' => getenv('SESSION_COOKIE_HTTPONLY') !== 'false',
                'samesite' => getenv('SESSION_COOKIE_SAMESITE') ?: 'Strict',
            ];

            session_set_cookie_params($cookieSettings);
            session_name('resume_analyzer');
            session_start();

            // Validate session
            $this->validateSession();
        }
    }

    /**
     * Validate session security
     */
    private function validateSession()
    {
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $this->getClientIp();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        // Check for session hijacking
        if ($_SESSION['ip_address'] !== $this->getClientIp() ||
            $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            $this->logout();
            throw new Exception("Session validation failed");
        }
    }

    /**
     * Register new user
     */
    public function register($name, $email, $password, $confirmPassword)
    {
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if (strlen($name) < 2 || strlen($name) > 255) {
            throw new Exception("Name must be between 2 and 255 characters");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        // Check if email exists
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );

        if ($existingUser) {
            throw new Exception("Email already registered");
        }

        // Create user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $verificationToken = bin2hex(random_bytes(32));

        $userId = $this->db->insert(
            "INSERT INTO users (name, email, password, verification_token, role) VALUES (?, ?, ?, ?, ?)",
            [$name, $email, $hashedPassword, $verificationToken, 'user']
        );

        // Log event
        $this->logEvent('user_registered', ['user_id' => $userId, 'email' => $email]);

        return [
            'id' => $userId,
            'verification_token' => $verificationToken
        ];
    }

    /**
     * Login user
     */
    public function login($email, $password, $rememberMe = false)
    {
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }

        $user = $this->db->fetchOne(
            "SELECT id, password, role, is_active FROM users WHERE email = ?",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $this->logEvent('login_failed', ['email' => $email]);
            throw new Exception("Invalid email or password");
        }

        if (!$user['is_active']) {
            throw new Exception("Account is disabled");
        }

        // Set session
        $_SESSION[self::SESSION_KEY] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Update last login
        $this->db->update(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );

        // Handle remember me
        if ($rememberMe) {
            $this->setRememberToken($user['id']);
        }

        // Log event
        $this->logEvent('user_logged_in', ['user_id' => $user['id']]);

        return $user;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return isset($_SESSION[self::SESSION_KEY]) && !empty($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->isAuthenticated() && ($_SESSION['user_role'] ?? null) === 'admin';
    }

    /**
     * Get current user ID
     */
    public function getUserId()
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Get current user data
     */
    public function getUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->db->fetchOne(
            "SELECT id, name, email, role, avatar, phone, bio, location, is_active FROM users WHERE id = ?",
            [$this->getUserId()]
        );
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $userId = $this->getUserId();

        if ($userId) {
            // Clear remember token
            $this->db->update(
                "UPDATE users SET remember_token = NULL WHERE id = ?",
                [$userId]
            );

            $this->logEvent('user_logged_out', ['user_id' => $userId]);
        }

        // Destroy session
        $_SESSION = [];
        session_destroy();

        // Clear cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail($token)
    {
        $user = $this->db->fetchOne(
            "SELECT id, email FROM users WHERE verification_token = ? AND email_verified = 0",
            [$token]
        );

        if (!$user) {
            throw new Exception("Invalid or expired verification token");
        }

        $this->db->update(
            "UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?",
            [$user['id']]
        );

        $this->logEvent('email_verified', ['user_id' => $user['id']]);

        return $user;
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email)
    {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );

        if (!$user) {
            // Don't reveal if email exists
            return true;
        }

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $this->db->update(
            "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?",
            [$token, $expiry, $user['id']]
        );

        $this->logEvent('password_reset_requested', ['user_id' => $user['id']]);

        return $token;
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword, $confirmPassword)
    {
        if (empty($newPassword) || empty($confirmPassword)) {
            throw new Exception("Password fields are required");
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($newPassword) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );

        if (!$user) {
            throw new Exception("Invalid or expired reset token");
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->db->update(
            "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?",
            [$hashedPassword, $user['id']]
        );

        $this->logEvent('password_reset', ['user_id' => $user['id']]);

        return true;
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword($currentPassword, $newPassword, $confirmPassword)
    {
        if (!$this->isAuthenticated()) {
            throw new Exception("Not authenticated");
        }

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new Exception("All password fields are required");
        }

        $user = $this->db->fetchOne(
            "SELECT password FROM users WHERE id = ?",
            [$this->getUserId()]
        );

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }

        if (strlen($newPassword) < 8) {
            throw new Exception("New password must be at least 8 characters");
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->db->update(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $this->getUserId()]
        );

        $this->logEvent('password_changed', ['user_id' => $this->getUserId()]);

        return true;
    }

    /**
     * Set remember me cookie
     */
    private function setRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32));

        $this->db->update(
            "UPDATE users SET remember_token = ? WHERE id = ?",
            [$token, $userId]
        );

        setcookie(
            'remember_token',
            $token,
            time() + self::REMEMBER_DURATION,
            '/',
            '',
            getenv('SESSION_COOKIE_SECURE') === 'true',
            true
        );
    }

    /**
     * Login with remember token
     */
    public function loginWithRememberToken($token)
    {
        $user = $this->db->fetchOne(
            "SELECT id, role FROM users WHERE remember_token = ? AND is_active = 1",
            [$token]
        );

        if (!$user) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        $this->logEvent('auto_login', ['user_id' => $user['id']]);

        return true;
    }

    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Log authentication event
     */
    private function logEvent($eventType, $eventData)
    {
        try {
            $this->db->insert(
                "INSERT INTO analytics (user_id, event_type, event_data, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
                [
                    $eventData['user_id'] ?? null,
                    $eventType,
                    json_encode($eventData),
                    $this->getClientIp(),
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]
            );
        } catch (Exception $e) {
            // Log silently to avoid disrupting auth flow
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialize
     */
    private function __wakeup() {}
}
