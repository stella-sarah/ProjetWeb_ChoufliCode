<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");

// Activer le rapport d'erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';

// Enregistrer les erreurs dans un fichier
file_put_contents('php_error_log.txt', PHP_EOL.date('Y-m-d H:i:s')." - Starting script", FILE_APPEND);

try {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié', 401);
    }

    $pdo = config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            CASE 
                WHEN c.user1_id = :user_id THEN c.user2_id
                ELSE c.user1_id
            END AS recipient_id,
            COALESCE(
                CASE 
                    WHEN c.user1_id = :user_id THEN CONCAT(u2.prenom, ' ', u2.nom)
                    ELSE CONCAT(u1.prenom, ' ', u1.nom)
                END,
                CASE 
                    WHEN c.user1_id = :user_id THEN u2.email
                    ELSE u1.email
                END
            ) AS recipient_name,
            (SELECT content FROM messages 
             WHERE conversation_id = c.id 
             ORDER BY created_at DESC LIMIT 1) AS last_message,
            (SELECT created_at FROM messages 
             WHERE conversation_id = c.id 
             ORDER BY created_at DESC LIMIT 1) AS last_message_time,
            (SELECT COUNT(*) FROM messages 
             WHERE conversation_id = c.id 
             AND sender_id != :user_id 
             AND is_read = 0) AS unread_count
        FROM conversations c
        LEFT JOIN users u1 ON c.user1_id = u1.id
        LEFT JOIN users u2 ON c.user2_id = u2.id
        WHERE c.user1_id = :user_id OR c.user2_id = :user_id
        ORDER BY last_message_time DESC
    ");
    
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Valider et formater les données
    $response = [
        'success' => true,
        'conversations' => array_map(function($conv) {
            return [
                'id' => (int)$conv['id'],
                'recipient_id' => (int)$conv['recipient_id'],
                'recipient_name' => $conv['recipient_name'] ?? 'Utilisateur',
                'last_message' => $conv['last_message'] ?? 'Aucun message',
                'last_message_time' => $conv['last_message_time'] ?? null,
                'unread_count' => (int)($conv['unread_count'] ?? 0)
            ];
        }, $conversations)
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    file_put_contents('php_error_log.txt', PHP_EOL.$e->getMessage(), FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    file_put_contents('php_error_log.txt', PHP_EOL.$e->getMessage(), FILE_APPEND);
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}