<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Inclure la configuration
require_once '../../config.php';

// Vérification de l’authentification
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit;
}

// Vérification du paramètre requis
if (empty($_GET['conversation_id']) || !is_numeric($_GET['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètre conversation_id manquant ou invalide']);
    exit;
}

$conversation_id = (int)$_GET['conversation_id'];
$current_user_id = (int)$_SESSION['user_id'];

try {
    $pdo = Config::getConnexion();

    // Vérification de l'accès à la conversation
    $stmt = $pdo->prepare("
        SELECT id FROM conversations
        WHERE id = :conv_id AND (user1_id = :uid OR user2_id = :uid)
    ");
    $stmt->execute([
        ':conv_id' => $conversation_id,
        ':uid' => $current_user_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé à cette conversation']);
        exit;
    }

    // Mise à jour des messages non lus
    $update = $pdo->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE conversation_id = :conv_id
        AND sender_id != :uid
        AND is_read = 0
    ");
    $update->execute([
        ':conv_id' => $conversation_id,
        ':uid' => $current_user_id
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
}
?>