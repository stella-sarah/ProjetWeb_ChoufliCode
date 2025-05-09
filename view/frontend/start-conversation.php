<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérification des données POST
if (!isset($_POST['recipient_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

$recipient_id = (int)$_POST['recipient_id'];
$content = trim($_POST['content']);

// Validation des données
if ($recipient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Destinataire invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Le message est trop long (max 1000 caractères)'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Config::getConnexion();
    
    // Vérification du destinataire
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND id != ?");
    $stmt->execute([$recipient_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Destinataire invalide'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Recherche de conversation existante
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?)
        OR (user1_id = ? AND user2_id = ?)
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id'], $recipient_id, $recipient_id, $_SESSION['user_id']]);
    
    if ($conversation = $stmt->fetch()) {
        $conversationId = $conversation['id'];
    } else {
        // Création nouvelle conversation
        $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $recipient_id]);
        $conversationId = $pdo->lastInsertId();
    }

    // Insertion du message (avec protection XSS)
    $stmt = $pdo->prepare("
        INSERT INTO messages (conversation_id, sender_id, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $conversationId, 
        $_SESSION['user_id'], 
        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
    ]);

    // Mise à jour timestamp conversation
    $pdo->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?")
        ->execute([$conversationId]);

    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}