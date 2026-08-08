<?php

/**
 * Database Configuration & Connection Handler
 * Manages MySQLi database connections with proper error handling
 */

class Database
{
    private static $instance = null;
    private $connection = null;
    private $host;
    private $user;
    private $password;
    private $database;
    private $charset;

    /**
     * Constructor - Initialize database configuration from environment
     */
    private function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->database = getenv('DB_NAME') ?: 'resume_analyzer';
        $this->charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect()
    {
        $this->connection = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );

        if ($this->connection->connect_error) {
            $this->logError("Connection failed: " . $this->connection->connect_error);
            throw new Exception("Database connection failed. Please try again later.");
        }

        // Set charset
        if (!$this->connection->set_charset($this->charset)) {
            $this->logError("Error setting charset: " . $this->connection->error);
        }

        // Set timezone
        $this->connection->query("SET time_zone = '+00:00'");
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
     * Get MySQLi connection
     */
    public function getConnection()
    {
       if ($this->connection === null) {
    $this->connect();
     }
        return $this->connection;
    }

    /**
     * Execute prepared statement
     */
    public function prepare($query)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            $this->logError("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare error");
        }

        return $stmt;
    }

    /**
     * Execute query and return result
     */
    public function query($query, $params = [])
    {
        $stmt = $this->prepare($query);

        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->logError("Execute failed: " . $stmt->error);
            throw new Exception("Database query error");
        }

        return $stmt->get_result();
    }

    /**
     * Fetch single row as associative array
     */
    public function fetchOne($query, $params = [])
    {
        $result = $this->query($query, $params);
        return $result->fetch_assoc();
    }

    /**
     * Fetch all rows as associative array
     */
    public function fetchAll($query, $params = [])
    {
        $result = $this->query($query, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Insert and return last insert ID
     */
    public function insert($query, $params = [])
    {
        $stmt = $this->prepare($query);

        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->logError("Insert failed: " . $stmt->error);
            throw new Exception("Insert failed");
        }

        return $this->getConnection()->insert_id;
    }

    /**
     * Update query and return affected rows
     */
    public function update($query, $params = [])
    {
        $stmt = $this->prepare($query);

        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->logError("Update failed: " . $stmt->error);
            throw new Exception("Update failed");
        }

        return $this->getConnection()->affected_rows;
    }

    /**
     * Delete query and return affected rows
     */
    public function delete($query, $params = [])
    {
        return $this->update($query, $params);
    }

    /**
     * Get parameter types for binding
     */
    private function getParamTypes($params)
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->getConnection()->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Get last error
     */
    public function lastError()
    {
        return $this->getConnection()->error;
    }

    /**
     * Close connection
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Log errors to file
     */
    private function logError($message)
    {
        $logDir = getenv('LOG_PATH') ?: './logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";

        error_log($logMessage, 3, $logFile);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialize
     */
    public function __wakeup() {}
}
