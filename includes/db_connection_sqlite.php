<?php
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
            return new SQLiteResultWrapper($stmt);
        }
        
        public function prepare($sql) {
            return new SQLitePreparedWrapper($this->pdo->prepare($sql));
        }
        
        public function set_charset($charset) {
            // SQLite doesn't need charset setting
            return true;
        }
        
        public $connect_error = null;
    }
    
    class SQLiteResultWrapper {
        private $stmt;
        public $num_rows = 0;
        
        public function __construct($stmt) {
            $this->stmt = $stmt;
            if ($stmt) {
                $this->num_rows = $stmt->rowCount();
            }
        }
        
        public function fetch_assoc() {
            if ($this->stmt) {
                return $this->stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        }
        
        public function fetch_all($mode = MYSQLI_ASSOC) {
            if ($this->stmt) {
                return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }
        
        public function data_seek($offset) {
            // SQLite doesn't support data_seek, we'll need to re-execute
            return true;
        }
    }
    
    class SQLitePreparedWrapper {
        private $stmt;
        
        public function __construct($stmt) {
            $this->stmt = $stmt;
        }
        
        public function bind_param($types, ...$params) {
            for ($i = 0; $i < count($params); $i++) {
                $this->stmt->bindValue($i + 1, $params[$i]);
            }
            return true;
        }
        
        public function execute() {
            return $this->stmt->execute();
        }
        
        public function get_result() {
            return new SQLiteResultWrapper($this->stmt);
        }
        
        public function close() {
            return true;
        }
    }
    
    $conn = new SQLiteWrapper($pdo);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

