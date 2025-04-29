<?php
header('Content-Type: application/json');
require_once '../../config.php';

$comment_id = $_POST['comment_id'] ?? null;
$action = $_POST['action'] ?? 'like'; // 'like' ou 'unlike'

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'ID de commentaire manquant']);
    exit;
}

try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Commencer une transaction
    $pdo->beginTransaction();
    
    // Récupérer le nombre actuel de likes
    $stmt = $pdo->prepare("SELECT likes FROM comments WHERE id = ? FOR UPDATE");
    $stmt->execute([$comment_id]);
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception("Commentaire non trouvé");
    }
    
    // Calculer le nouveau nombre de likes
    $new_likes = $result['likes'];
    if ($action === 'like') {
        $new_likes++;
    } else {
        $new_likes = max(0, $new_likes - 1); // Empêcher les valeurs négatives
    }
    
    // Mettre à jour le compteur
    $stmt = $pdo->prepare("UPDATE comments SET likes = ? WHERE id = ?");
    $stmt->execute([$new_likes, $comment_id]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'like_count' => $new_likes
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}