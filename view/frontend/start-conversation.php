<?php
header('Content-Type: application/json; charset=UTF-8');

// Inclure la configuration
require_once '../../config.php';

// Vérification d'authentification renforcée
if (empty($_SESSION['user_id'])) {
    error_log("Erreur d'authentification - Session: ".print_r($_SESSION, true));
    echo json_encode([
        'success' => false, 
        'message' => 'Non authentifié',
        'session_status' => session_status(),
        'session_id' => session_id()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validation des entrées
$required_fields = ['recipient_id', 'content'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Champ $field manquant"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$recipient_id = (int)$_POST['recipient_id'];
$content = trim($_POST['content']);

// Validations supplémentaires
if ($recipient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID destinataire invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Message vide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message trop long'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Config::getConnexion();
    
    // Vérification du destinataire
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND id != ? AND active = 1");
    $stmt->execute([$recipient_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Destinataire introuvable'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Gestion de la conversation
    $conversationId = getOrCreateConversation($pdo, $_SESSION['user_id'], $recipient_id);
    
    // Insertion du message
    $messageId = insertMessage($pdo, $conversationId, $_SESSION['user_id'], $content);

    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId,
        'message_id' => $messageId
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("PDO Error: ".$e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Trouve ou crée une conversation entre deux utilisateurs
 */
function getOrCreateConversation($pdo, $userId, $recipientId) {
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?) 
        OR (user1_id = ? AND user2_id = ?) 
        LIMIT 1
    ");
    $stmt->execute([$userId, $recipientId, $recipientId, $userId]);
    
    if ($conversation = $stmt->fetch()) {
        return $conversation['id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $recipientId]);
    return $pdo->lastInsertId();
}

/**
 * Insère un nouveau message dans une conversation
 */
function insertMessage($pdo, $conversationId, $userId, $content) {
    $stmt = $pdo->prepare("
        INSERT INTO messages (conversation_id, sender_id, content, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$conversationId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8')]);
    return $pdo->lastInsertId();
}
?>