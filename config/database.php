<?php
/**
 * Database Configuration
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'pos_system';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Connection error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function __clone() {
        throw new Exception("Cannot clone Database instance");
    }
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize Database instance");
    }
}