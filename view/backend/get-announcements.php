<?php
// get-announcements.php - Récupérer toutes les annonces depuis la base de données TuniFy

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    $query = 'SELECT id, title, content, author, created_at, publish_at, is_deleted FROM announcements';
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