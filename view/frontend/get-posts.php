<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

try {
    // Vérifiez d'abord la connexion
    $pdo->exec("SET NAMES utf8mb4");
    
    // Récupération des posts
    $stmt = $pdo->query("
        SELECT * FROM posts 
        WHERE hidden = FALSE AND is_deleted = FALSE 
        ORDER BY created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque post, récupérer les commentaires
    foreach ($posts as &$post) {
        $stmt = $pdo->prepare("
            SELECT * FROM comments 
            WHERE post_id = ? AND is_deleted = FALSE 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$post['id']]);
        $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajoutez JSON_UNESCAPED_UNICODE pour préserver les emojis
    echo json_encode(
        ["success" => true, "posts" => $posts],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    
} catch (PDOException $e) {
    echo json_encode(
        ["success" => false, "message" => "Erreur: " . $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}
?>