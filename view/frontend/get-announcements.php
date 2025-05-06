<?php
// get-announcements.php - Récupérer les annonces publiques depuis la base de données TuniFy

header('Content-Type: application/json');

// Set UTC timezone for consistent date handling
date_default_timezone_set('UTC');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Query to fetch only published announcements
    $query = 'SELECT id, title, content, author, created_at, publish_at, is_deleted 
              FROM announcements 
              WHERE is_deleted = 0 
              AND (publish_at IS NULL OR publish_at <= NOW())';
    $stmt = $pdo->query($query);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de la récupération des annonces : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>