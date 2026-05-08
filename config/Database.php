<?php
/**
 * Database Configuration and Connection Class
 * Health Performance Monitoring System
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'health_monitoring';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Initialize database connection with PDO
     */
    public function connect() {
        $this->conn = null;

        try {
            // Check for environment variables first
            if (getenv('DB_HOST')) {
                $this->host = getenv('DB_HOST');
                $this->db_name = getenv('DB_NAME') ?: 'health_monitoring';
                $this->username = getenv('DB_USER') ?: 'root';
                $this->password = getenv('DB_PASSWORD') ?: '';
            }

            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set timezone to UTC for consistent timestamp handling
            $this->conn->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your database credentials in config/Database.php");
        }

        return $this->conn;
    }

    /**
     * Get connection instance
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
