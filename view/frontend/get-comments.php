<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php';

// Force UTF-8 pour toutes les fonctions mb_*
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

try {
    $pdo = config::getConnexion();
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $post_id = $_GET['post_id'] ?? null;

    if ($post_id) {
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$post_id]);
        $comments = $stmt->fetchAll();

        // Nettoyage des données
        array_walk_recursive($comments, function(&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                $value = htmlspecialchars_decode($value, ENT_QUOTES | ENT_HTML5);
            }
        });

        echo json_encode([
            'success' => true,
            'comments' => $comments
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'post_id manquant']);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}