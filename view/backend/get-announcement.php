<?php
// get-announcement.php - Récupérer une annonce spécifique depuis la base de données TuniFy

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Vérifier le paramètre GET requis
    if (!isset($_GET['id'])) {
        throw new Exception('L\'ID de l\'annonce est requis');
    }

    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($id)) {
        throw new Exception('L\'ID de l\'annonce ne peut pas être vide');
    }

    $query = 'SELECT id, title, content, author, created_at, publish_at, is_deleted FROM announcements WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        throw new Exception('Annonce non trouvée');
    }

    echo json_encode([
        'success' => true,
        'announcement' => $announcement
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de la récupération de l'annonce : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>