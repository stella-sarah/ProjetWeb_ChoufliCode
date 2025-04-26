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
$comment_id = intval($_POST['comment_id'] ?? 0);

// Validation
if ($comment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de commentaire invalide']);
    exit;
}

try {
    // Supprimer le commentaire
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    
    echo json_encode(['success' => true, 'message' => 'Commentaire supprimé avec succès']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}