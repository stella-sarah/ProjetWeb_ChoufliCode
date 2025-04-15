<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$pdo = config::getConnexion();

$post_id = $_POST['post_id'] ?? 0;
$content = $_POST['content'] ?? '';
$author = 'Anonyme';

if (empty($content)) {
    echo json_encode(["success" => false, "message" => "Le contenu du commentaire est vide"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$post_id, $author, $content]);
    
    $comment_id = $pdo->lastInsertId();
    
    // Récupérer le commentaire complet pour l'affichage
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    echo json_encode([
        "success" => true,
        "comment" => $comment
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur d'insertion : " . $e->getMessage()
    ]);
}