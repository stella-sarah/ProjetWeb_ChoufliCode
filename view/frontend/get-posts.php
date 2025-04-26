<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

try {
    // Récupération des posts non masqués et non supprimés
    $stmt = $pdo->query("
        SELECT * FROM posts 
        WHERE hidden = FALSE AND is_deleted = FALSE 
        ORDER BY created_at DESC
    ");
    $posts = $stmt->fetchAll();

    // Pour chaque post, récupérer les commentaires non supprimés
    foreach ($posts as &$post) {
        $stmt = $pdo->prepare("
            SELECT * FROM comments 
            WHERE post_id = ? AND is_deleted = FALSE 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$post['id']]);
        $post['comments'] = $stmt->fetchAll();
    }

    echo json_encode(["success" => true, "posts" => $posts]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur: " . $e->getMessage()]);
}