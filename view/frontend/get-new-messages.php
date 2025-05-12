<?php
header('Content-Type: application/json; charset=UTF-8');

// Inclure la configuration
require_once '../../config.php';

// Authentification
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentification requise',
        'session_status' => session_status(),
        'session_id' => session_id()
    ]);
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];

// Validation des paramètres GET
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : -1;

if ($conversation_id <= 0 || $last_id < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $pdo = Config::getConnexion();

    // Vérification de l'accès à la conversation
    $stmt = $pdo->prepare("
        SELECT 1 FROM conversations 
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
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
        exit;
    }

    // Récupération des nouveaux messages
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
        AND m.id > :last_id
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([
        ':conversation_id' => $conversation_id,
        ':last_id' => $last_id
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'messages' => array_map(function($m) {
            return [
                'id' => (int)$m['id'],
                'sender_id' => (int)$m['sender_id'],
                'sender_name' => trim($m['sender_name'] ?? 'Utilisateur'),
                'content' => $m['content'],
                'sent_at' => $m['sent_at'],
                'is_read' => (bool)$m['is_read']
            ];
        }, $messages)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO Error (new-messages): " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
}
?>