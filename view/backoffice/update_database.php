<?php
require_once 'config.php';

try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create events table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id VARCHAR(50) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        venue VARCHAR(255) NOT NULL,
        max_capacity INT NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Add latitude and longitude columns if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('latitude', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN latitude DECIMAL(10, 8) AFTER venue");
    }
    
    if (!in_array('longitude', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN longitude DECIMAL(11, 8) AFTER latitude");
    }
    
    echo "La base de données a été mise à jour avec succès.";
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour de la base de données : " . $e->getMessage();
} 