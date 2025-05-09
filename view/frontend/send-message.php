<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_POST['conversation_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

$conversation_id = (int)$_POST['conversation_id'];
$content = trim($_POST['content']);

// Validation
if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Conversation invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message trop long (max 1000 caractères)'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = config::getConnexion();
    
    // Vérifier que l'utilisateur fait partie de la conversation
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE id = ? 
        AND (user1_id = ? OR user2_id = ?)
        LIMIT 1
    ");
    $stmt->execute([$conversation_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Conversation non trouvée'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Insérer le message
    $stmt = $pdo->prepare("
        INSERT INTO messages (conversation_id, sender_id, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $conversation_id,
        $_SESSION['user_id'],
        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
    ]);

    // Mettre à jour la conversation
    $pdo->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?")
        ->execute([$conversation_id]);

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}