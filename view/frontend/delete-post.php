<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupération et validation des données
$post_id = intval($_POST['post_id'] ?? 0);

// Validation
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de post invalide']);
    exit;
}

try {
    // Supprimer le post et ses commentaires sans vérification d'auteur
    $pdo->beginTransaction();
    
    // Supprimer les commentaires associés
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    
    // Supprimer le post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Post supprimé avec succès']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}