<?php
header('Content-Type: application/json; charset=UTF-8');

// Inclure la configuration
require_once '../../config.php';

// Vérification d'authentification
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Validation des champs requis
$required_fields = ['conversation_id', 'content'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Champ manquant : $field"]);
        exit;
    }
}

$conversation_id = (int)$_POST['conversation_id'];
$content = trim($_POST['content']);

// Validation logique
if ($conversation_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID conversation invalide']);
    exit;
}

if ($content === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message vide']);
    exit;
}

if (mb_strlen($content) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message trop long (max 1000 caractères)']);
    exit;
}

try {
    $pdo = Config::getConnexion();

    // Vérifier que l'utilisateur appartient à la conversation
    $stmt = $pdo->prepare("
        SELECT id FROM conversations
        WHERE id = :conv_id AND (user1_id = :uid OR user2_id = :uid)
    ");
    $stmt->execute([
        ':conv_id' => $conversation_id,
        ':uid' => $user_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé à la conversation']);
        exit;
    }

    // Insérer le message
    $insert = $pdo->prepare("
        INSERT INTO messages (conversation_id, sender_id, content, created_at)
        VALUES (:conv_id, :sender_id, :content, NOW())
    ");
    $insert->execute([
        ':conv_id' => $conversation_id,
        ':sender_id' => $user_id,
        ':content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
    ]);

    // Mettre à jour la date de mise à jour de la conversation
    $pdo->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = :conv_id")
        ->execute([':conv_id' => $conversation_id]);

    // Réponse succès avec ID du message
    echo json_encode([
        'success' => true,
        'message_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO - send-message.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
}
?>