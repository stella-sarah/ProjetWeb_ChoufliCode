<?php
// add-announcement.php - Ajouter une nouvelle annonce dans la base de données TuniFy

header('Content-Type: application/json');
date_default_timezone_set('UTC');

try {
    require_once '../../config.php';
    $pdo = Config::getConnexion();

    // Vérifier les paramètres POST requis
    if (!isset($_POST['title'], $_POST['content'], $_POST['author'])) {
        throw new Exception('Le titre, le contenu et l\'auteur sont requis');
    }

    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);
    $publish_at = isset($_POST['publish_at']) ? trim($_POST['publish_at']) : null;

    if (empty($title) || empty($content) || empty($author)) {
        throw new Exception('Le titre, le contenu et l\'auteur ne peuvent pas être vides');
    }

    // Valider la date de publication si fournie
    if ($publish_at) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $publish_at)) {
            throw new Exception('Format de date invalide (attendu : YYYY-MM-DD HH:MM:SS)');
        }

        if (strtotime($publish_at) <= time()) {
            throw new Exception('La date de publication doit être dans le futur');
        }
    }

    $query = 'INSERT INTO announcements (title, content, author, created_at, publish_at) 
              VALUES (:title, :content, :author, NOW(), :publish_at)';
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'author' => $author,
        'publish_at' => $publish_at ?: null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Annonce ajoutée avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de l'ajout de l'annonce : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>