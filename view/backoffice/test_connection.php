<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $host = 'localhost';
    $dbname = 'tunify';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Database connection successful!<br>";
    
    // Test if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'reservations'");
    if ($stmt->rowCount() > 0) {
        echo "Reservations table exists!<br>";
        
        // Test if we can read from the table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
        $result = $stmt->fetch();
        echo "Number of reservations: " . $result['count'] . "<br>";
    } else {
        echo "Reservations table does not exist!<br>";
        echo "Please run the database.sql file to create the table.<br>";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
}
?> 