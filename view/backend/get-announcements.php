<?php
ini_set('display_errors', 0); // Désactiver l'affichage HTML des erreurs
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../config.php'; // Chemin absolu

    if (!class_exists('Config')) {
        throw new Exception("Classe Config manquante");
    }

    $pdo = Config::getConnexion();
    if (!$pdo) {
        throw new Exception("Connexion DB échouée");
    }

    // Vérifie si la colonne is_deleted existe
    $stmt = $pdo->query("DESCRIBE announcements");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_deleted', $columns)) {
        throw new Exception("La colonne is_deleted n'existe pas");
    }

    // Récupère les annonces (y compris les supprimées pour l'admin)
    $stmt = $pdo->query("
        SELECT id, title, content, author, created_at, is_deleted
        FROM announcements 
        ORDER BY created_at DESC
    ");
    
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>