<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(
        ['success' => false, 'message' => 'Non authentifié'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if (!isset($_GET['conversation_id'])) {
    echo json_encode(
        ['success' => false, 'message' => 'ID de conversation manquant'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    $pdo = config::getConnexion();
    $pdo->exec("SET NAMES utf8mb4");
    
    // Vérifier que l'utilisateur fait bien partie de la conversation
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE id = :conversation_id 
        AND (user1_id = :user_id OR user2_id = :user_id)
    ");
    $stmt->execute([
        ':conversation_id' => $_GET['conversation_id'],
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$stmt->fetch()) {
        echo json_encode(
            ['success' => false, 'message' => 'Conversation non trouvée'],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    // Récupérer les messages de la conversation
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
    $stmt->execute([':conversation_id' => $_GET['conversation_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marquer les messages comme lus
    $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE conversation_id = :conversation_id 
        AND sender_id != :user_id
        AND is_read = 0
    ")->execute([
        ':conversation_id' => $_GET['conversation_id'],
        ':user_id' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(
        ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}
?>