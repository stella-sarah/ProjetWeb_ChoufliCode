<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: http://localhost"); // Remplacer par votre domaine
header("Access-Control-Allow-Credentials: true");

// Configuration des erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Inclure la configuration
require_once __DIR__ . '/../../config.php';

// À des fins de test uniquement
$_SESSION['user_id'] = 1; // Supprimez après résolution

try {
    // Vérification d'authentification
    if (empty($_SESSION['user_id'])) {
        error_log("Erreur: Aucun user_id en session");
        throw new Exception('Authentification requise', 401);
    }

    $current_user_id = (int)$_SESSION['user_id'];
    error_log("Tentative de chargement des conversations pour user ID: $current_user_id");

    $pdo = Config::getConnexion();

    // Vérification que l'utilisateur existe et est actif
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE id = ? AND active = 1 LIMIT 1");
    $stmt->execute([$current_user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Utilisateur invalide ou inactif', 403);
    }

    // Requête optimisée avec jointures
    $sql = "
        SELECT 
            c.id,
            IF(c.user1_id = :user_id, c.user2_id, c.user1_id) AS recipient_id,
            COALESCE(
                IF(c.user1_id = :user_id, 
                    CONCAT(u2.prenom, ' ', u2.nom), 
                    CONCAT(u1.prenom, ' ', u1.nom)
                ),
                'Utilisateur'
            ) AS recipient_name,
            m.content AS last_message,
            m.created_at AS last_message_time,
            COALESCE((
                SELECT COUNT(*) 
                FROM messages m2 
                WHERE m2.conversation_id = c.id 
                  AND m2.sender_id != :user_id 
                  AND m2.is_read = 0
            ), 0) AS unread_count
        FROM conversations c
        LEFT JOIN users u1 ON c.user1_id = u1.id
        LEFT JOIN users u2 ON c.user2_id = u2.id
        LEFT JOIN (
            SELECT conversation_id, content, created_at
            FROM messages
            WHERE id IN (
                SELECT MAX(id)
                FROM messages
                GROUP BY conversation_id
            )
        ) m ON c.id = m.conversation_id
        WHERE c.user1_id = :user_id OR c.user2_id = :user_id
        ORDER BY COALESCE(m.created_at, c.created_at) DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();

    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage de la réponse
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
    error_log("Erreur PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>