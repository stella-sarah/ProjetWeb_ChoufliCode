<?php
// update-announcement.php - Mettre à jour une annonce dans la base de données TuniFy

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Vérifier les paramètres POST requis
    if (!isset($_POST['id'], $_POST['title'], $_POST['content'], $_POST['author'])) {
        throw new Exception('L\'ID, le titre, le contenu et l\'auteur sont requis');
    }

    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);
    $publish_at = isset($_POST['publish_at']) ? filter_var($_POST['publish_at'], FILTER_SANITIZE_STRING) : null;

    if (empty($id) || empty($title) || empty($content) || empty($author)) {
        throw new Exception('L\'ID, le titre, le contenu et l\'auteur ne peuvent pas être vides');
    }

    // Valider le format de publish_at si fourni
    if ($publish_at) {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $publish_at);
        if (!$dateTime || $dateTime->format('Y-m-d H:i:s') !== $publish_at) {
            throw new Exception('Format de date de publication invalide');
        }
        if ($dateTime <= new DateTime()) {
            throw new Exception('La date de publication doit être dans le futur');
        }
    }

    $query = 'UPDATE announcements SET title = :title, content = :content, author = :author, publish_at = :publish_at WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'id' => $id,
        'title' => $title,
        'content' => $content,
        'author' => $author,
        'publish_at' => $publish_at
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Aucune annonce trouvée avec cet ID');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Annonce mise à jour avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de la mise à jour de l'annonce : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>