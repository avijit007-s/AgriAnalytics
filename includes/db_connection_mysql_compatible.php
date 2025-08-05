<?php
// Database configuration - Switch between SQLite and MySQL
$use_mysql = true; // Set to false for SQLite, true for MySQL

if ($use_mysql) {
    // MySQL Database configuration for XAMPP
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "agricultural_analysis";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
    // Create PDO connection for compatibility
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("PDO Connection failed: " . $e->getMessage());
    }
    
} else {
    // SQLite Database configuration
    $db_path = __DIR__ . '/../database/agricultural_analysis.db';
    
    // Create database directory if it doesn't exist
    $db_dir = dirname($db_path);
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }
    
    try {
        // Create PDO connection to SQLite
        $pdo = new PDO("sqlite:$db_path");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // For compatibility with existing mysqli code, create a wrapper
        class SQLiteWrapper {
            private $pdo;
            
            public function __construct($pdo) {
                $this->pdo = $pdo;
            }
            
            public function query($sql) {
                $stmt = $this->pdo->query($sql);
                return new SQLiteResultWrapper($stmt, $this->pdo);
            }
            
            public function prepare($sql) {
                return new SQLitePreparedWrapper($this->pdo->prepare($sql), $this->pdo);
            }
            
            public function set_charset($charset) {
                // SQLite doesn't need charset setting
                return true;
            }
            
            public $connect_error = null;
        }
        
        class SQLiteResultWrapper {
            private $stmt;
            private $pdo;
            public $num_rows = 0;
            private $data = [];
            private $position = 0;
            
            public function __construct($stmt, $pdo) {
                $this->stmt = $stmt;
                $this->pdo = $pdo;
                if ($stmt) {
                    $this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->num_rows = count($this->data);
                }
            }
            
            public function fetch_assoc() {
                if ($this->position < count($this->data)) {
                    return $this->data[$this->position++];
                }
                return false;
            }
            
            public function fetch_all($mode = null) {
                return $this->data;
            }
            
            public function data_seek($offset) {
                $this->position = $offset;
                return true;
            }
            
            public function rowCount() {
                return $this->num_rows;
            }
        }
        
        class SQLitePreparedWrapper {
            private $stmt;
            private $pdo;
            private $params = [];
            
            public function __construct($stmt, $pdo) {
                $this->stmt = $stmt;
                $this->pdo = $pdo;
            }
            
            public function bind_param($types, ...$params) {
                $this->params = $params;
                return true;
            }
            
            public function execute($params = null) {
                if ($params !== null) {
                    return $this->stmt->execute($params);
                } elseif (!empty($this->params)) {
                    return $this->stmt->execute($this->params);
                }
                return $this->stmt->execute();
            }
            
            public function get_result() {
                return new SQLiteResultWrapper($this->stmt, $this->pdo);
            }
            
            public function close() {
                return true;
            }
            
            public function rowCount() {
                return $this->stmt->rowCount();
            }
        }
        
        $conn = new SQLiteWrapper($pdo);
        
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>

