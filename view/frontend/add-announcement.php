<?php
// add-announcement.php - Ajouter une nouvelle annonce dans la base de données TuniFy

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Vérifier les paramètres POST requis
    if (!isset($_POST['title'], $_POST['content'], $_POST['author'])) {
        throw new Exception('Le titre, le contenu et l\'auteur sont requis');
    }

    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);

    if (empty($title) || empty($content) || empty($author)) {
        throw new Exception('Le titre, le contenu et l\'auteur ne peuvent pas être vides');
    }

    $query = 'INSERT INTO announcements (title, content, author, created_at) VALUES (:title, :content, :author, NOW())';
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'author' => $author
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