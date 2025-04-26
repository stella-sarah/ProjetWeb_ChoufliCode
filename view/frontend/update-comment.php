<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$comment_id = intval($_POST['comment_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($comment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de commentaire invalide']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Le contenu ne peut pas être vide']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE comments SET content = ?, last_updated = NOW() WHERE id = ?");
    $stmt->execute([$content, $comment_id]);
    
    echo json_encode(['success' => true, 'message' => 'Commentaire mis à jour avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}