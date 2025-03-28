<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private static $instance = null;
    
    private function __construct()
    {
        // Load database configuration
        $config = require_once 'config/database.php';
        
        $this->host = $config['host'];
        $this->db_name = $config['database'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        
        $this->connect();
    }
    
    // Singleton pattern to get database instance
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }
    
    // Connect to the database
    private function connect()
    {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Set charset to utf8mb4 for emoji and special character support
            $this->conn->exec("set names utf8mb4");
            
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
    
    // Get database connection
    public function getConnection()
    {
        return $this->conn;
    }
    
    // Execute a query and return the statement
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Execute a query and return a single row
    public function single($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        
        if($stmt) {
            return $stmt->fetch();
        }
        
        return false;
    }
    
    // Execute a query and return all rows
    public function all($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        
        if($stmt) {
            return $stmt->fetchAll();
        }
        
        return false;
    }
    
    // Execute a query and return the row count
    public function count($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        
        if($stmt) {
            return $stmt->rowCount();
        }
        
        return 0;
    }
    
    // Get the last inserted ID
    public function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }
    
    // Begin a transaction
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }
    
    // Commit a transaction
    public function commit()
    {
        return $this->conn->commit();
    }
    
    // Rollback a transaction
    public function rollback()
    {
        return $this->conn->rollBack();
    }
}
