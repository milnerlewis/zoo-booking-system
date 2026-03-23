<?php

/**
 * Load a local .env file into process environment.
 */
function loadEnvFile($filePath)
{
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        if ($key !== '') {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnvFile(__DIR__ . '/.env');

function envValue($key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

function envRequired($key)
{
    $value = envValue($key);
    if ($value === null || $value === '') {
        throw new RuntimeException("Missing required environment variable: {$key}");
    }

    return $value;
}

function dbConfigValues()
{
    return [
        'host' => envRequired('DB_HOST'),
        'port' => envRequired('DB_PORT'),
        'username' => envRequired('DB_USERNAME'),
        'password' => envRequired('DB_PASSWORD'),
        'name' => envRequired('DB_NAME'),
        'charset' => envRequired('DB_CHARSET'),
    ];
}

class DatabaseConfig {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $config = dbConfigValues();
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];

            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get instance of database connection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prepare and execute query with error handling
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            throw new Exception("Database query failed. Please try again.");
        }
    }
    
    /**
     * Start transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Legacy mysqli connection for backward compatibility
$databaseConfig = dbConfigValues();

$conn = new mysqli(
    $databaseConfig['host'],
    $databaseConfig['username'],
    $databaseConfig['password'],
    $databaseConfig['name'],
    (int) $databaseConfig['port']
);

if ($conn->connect_error) {
    error_log("Legacy connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset for security
$conn->set_charset($databaseConfig['charset']);
?>
