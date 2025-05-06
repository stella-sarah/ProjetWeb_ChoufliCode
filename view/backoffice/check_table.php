<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

try {
    $pdo = Config::getConnexion();
    
    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'ev_back'");
    if ($stmt->rowCount() == 0) {
        echo "La table 'ev_back' n'existe pas.<br>";
        
        // Créer la table
        $sql = "CREATE TABLE ev_back (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client VARCHAR(255) NOT NULL,
            service VARCHAR(255) NOT NULL,
            date DATETIME NOT NULL,
            status ENUM('Confirmée', 'En attente', 'Annulée') DEFAULT 'En attente',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "Table 'ev_back' créée avec succès.<br>";
    } else {
        echo "La table 'ev_back' existe.<br>";
        
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE ev_back");
        echo "<br>Structure de la table :<br>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 