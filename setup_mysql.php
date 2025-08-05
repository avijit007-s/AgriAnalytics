<?php
// MySQL Setup Script for XAMPP
echo "Setting up MySQL database for Agricultural Analysis System...\n";

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agricultural_analysis";

try {
    // Create connection without database first
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to MySQL successfully.\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database '$dbname' created successfully or already exists.\n";
    } else {
        echo "Error creating database: " . $conn->error . "\n";
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Read and execute the schema file
    $schema_file = __DIR__ . '/sql/mysql_schema.sql';
    if (file_exists($schema_file)) {
        $sql_content = file_get_contents($schema_file);
        
        // Split the SQL content by semicolons to execute each statement separately
        $statements = explode(';', $sql_content);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                if ($conn->query($statement) === TRUE) {
                    echo "Statement executed successfully.\n";
                } else {
                    echo "Error executing statement: " . $conn->error . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }
        
        echo "Schema setup completed.\n";
    } else {
        echo "Schema file not found: $schema_file\n";
    }
    
    // Verify tables were created
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        echo "\nTables created:\n";
        while($row = $result->fetch_assoc()) {
            echo "- " . $row["Tables_in_$dbname"] . "\n";
        }
    }
    
    // Check if admin user exists
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Insert admin user with hashed password
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username_val, $hashed_password, $email_val, $role_val);
        
        $username_val = "admin";
        $email_val = "admin@example.com";
        $role_val = "admin";
        
        if ($stmt->execute()) {
            echo "\nAdmin user created successfully.\n";
        } else {
            echo "\nError creating admin user: " . $conn->error . "\n";
        }
        $stmt->close();
    } else {
        echo "\nAdmin user already exists.\n";
    }
    
    $conn->close();
    echo "\nMySQL setup completed successfully!\n";
    echo "You can now use the application with XAMPP.\n";
    echo "Login credentials: admin / admin123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

