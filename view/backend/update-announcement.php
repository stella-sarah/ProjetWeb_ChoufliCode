<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../config.php';

try {
    // Vérification des champs obligatoires
    $required = ['id', 'title', 'author', 'content'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Champ requis manquant: $field");
        }
    }

    // Nettoyage des données
    $id = (int)$_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $author = htmlspecialchars($_POST['author']);
    $content = htmlspecialchars($_POST['content']);

    // Validation supplémentaire
    if ($id <= 0) throw new Exception("ID invalide");
    if (strlen($title) < 3) throw new Exception("Titre trop court");

    $pdo = Config::getConnexion();
    $stmt = $pdo->prepare("UPDATE announcements 
                          SET title = ?, author = ?, content = ?, last_updated = NOW() 
                          WHERE id = ?");
    $success = $stmt->execute([$title, $author, $content, $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Aucune modification effectuée (ID peut-être invalide)");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mise à jour réussie'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}