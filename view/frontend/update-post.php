<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$pdo = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$post_id = intval($_POST['post_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de post invalide']);
    exit;
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Le titre ne peut pas être vide']);
    exit;
}

try {
    // Mettre à jour le post sans vérification d'auteur
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, last_updated = NOW() WHERE id = ?");
    $stmt->execute([$title, $content, $post_id]);
    
    echo json_encode(['success' => true, 'message' => 'Post mis à jour avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}