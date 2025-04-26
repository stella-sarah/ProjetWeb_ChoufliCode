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

// Récupération des données
$content_id = $_POST['post_id'] ?? 0;
$reason = $_POST['reason'] ?? '';
$details = $_POST['details'] ?? '';
$reporter = $_POST['reporter'] ?? 'Anonyme';

// Validation
if (empty($content_id) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();

    // 1. Vérifier que le post existe
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
    $stmt->execute([$content_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Le post spécifié n'existe pas");
    }

    // 2. Enregistrer le signalement
    $stmt = $pdo->prepare("
        INSERT INTO reports 
        (content_type, content_id, reporter, reason, details, status, created_at) 
        VALUES ('post', ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$content_id, $reporter, $reason, $details]);

    // 3. Masquer le post signalé
    $stmt = $pdo->prepare("UPDATE posts SET hidden = TRUE WHERE id = ?");
    $stmt->execute([$content_id]);

    // Valider la transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Signalement enregistré et post masqué avec succès',
        'post_id' => $content_id
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur: ' . $e->getMessage(),
        'post_id' => $content_id
    ]);
}