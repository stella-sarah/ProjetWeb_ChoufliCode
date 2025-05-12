<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Inclure la configuration
require_once '../../config.php';

// Vérification authentification
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentification requise'], JSON_UNESCAPED_UNICODE);
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];

// Validation du paramètre GET
if (empty($_GET['conversation_id']) || !is_numeric($_GET['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de conversation manquant ou invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

$conversation_id = (int)$_GET['conversation_id'];

try {
    $pdo = Config::getConnexion();

    // Vérification d'autorisation à accéder à la conversation
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE id = :conversation_id 
        AND (user1_id = :user_id OR user2_id = :user_id)
        LIMIT 1
    ");
    $stmt->execute([
        ':conversation_id' => $conversation_id,
        ':user_id' => $current_user_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé à cette conversation'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Récupération des messages
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.sender_id,
            CONCAT(u.prenom, ' ', u.nom) AS sender_name,
            m.content,
            m.created_at AS sent_at,
            m.is_read
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = :conversation_id
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([':conversation_id' => $conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marquer les messages reçus comme lus
    $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE conversation_id = :conversation_id 
        AND sender_id != :user_id 
        AND is_read = 0
    ")->execute([
        ':conversation_id' => $conversation_id,
        ':user_id' => $current_user_id
    ]);

    // Formatage de la réponse
    echo json_encode([
        'success' => true,
        'messages' => array_map(function ($msg) {
            return [
                'id' => (int)$msg['id'],
                'sender_id' => (int)$msg['sender_id'],
                'sender_name' => $msg['sender_name'] ?: 'Utilisateur',
                'content' => $msg['content'],
                'sent_at' => $msg['sent_at'],
                'is_read' => (bool)$msg['is_read']
            ];
        }, $messages)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO Error (load-messages): " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>