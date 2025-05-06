<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__.'/../../config.php';

// Gestion des requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuration du rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Initialisation de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Mettez à true en production avec HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

try {
    // Récupération des données
    $input = file_get_contents('php://input');
    if ($input === false) {
        throw new Exception('Impossible de lire les données d\'entrée');
    }
    
    $data = json_decode($input, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Format JSON invalide: ' . json_last_error_msg());
    }
    
    $recipient_id = $data['recipient_id'] ?? null;
    $content = $data['content'] ?? null;

    // Validation des données
    if ($recipient_id === null || $content === null) {
        throw new Exception('Données manquantes');
    }
    
    $recipient_id = (int)$recipient_id;
    $content = trim($content);

    if ($recipient_id <= 0) {
        throw new Exception('Destinataire invalide');
    }

    if (empty($content)) {
        throw new Exception('Le message ne peut pas être vide');
    }

    if (strlen($content) > 1000) {
        throw new Exception('Message trop long (max 1000 caractères)');
    }

    // Protection XSS
    $content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Connexion à la base de données
    $pdo = Config::getConnexion();
    
    // Vérification de la connexion
    if ($pdo === null) {
        throw new Exception('Impossible de se connecter à la base de données');
    }

    $pdo->beginTransaction();

    try {
        // 1. Vérification du destinataire
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$recipient_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Destinataire introuvable');
        }

        // 2. Gestion de la conversation
        $sender_id = $_SESSION['user_id'] ?? null;
        
        // Si pas d'expéditeur (message anonyme), on utilise le destinataire comme conversation
        if ($sender_id === null) {
            $conversation_id = null;
        } else {
            // Détermination des IDs pour la conversation
            $id1 = min($sender_id, $recipient_id);
            $id2 = max($sender_id, $recipient_id);

            // Recherche de conversation existante
            $stmt = $pdo->prepare("
                SELECT id FROM conversations 
                WHERE user1_id = ? AND user2_id = ?
                LIMIT 1
            ");
            $stmt->execute([$id1, $id2]);
            $conversation = $stmt->fetch();

            if (!$conversation) {
                // Création d'une nouvelle conversation
                $stmt = $pdo->prepare("
                    INSERT INTO conversations (user1_id, user2_id, created_at, updated_at) 
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$id1, $id2]);
                $conversation_id = $pdo->lastInsertId();
            } else {
                $conversation_id = $conversation['id'];
            }
        }

        // 3. Envoi du message
        $stmt = $pdo->prepare("
            INSERT INTO messages (conversation_id, sender_id, content, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$conversation_id, $sender_id, $content]);
        $messageId = $pdo->lastInsertId();

        // Mise à jour de la conversation si elle existe
        if ($conversation_id !== null) {
            $stmt = $pdo->prepare("
                UPDATE conversations SET updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
        }

        $pdo->commit();

        // Réponse succès
        echo json_encode([
            'success' => true,
            'message_id' => $messageId,
            'conversation_id' => $conversation_id,
            'message' => 'Message envoyé avec succès'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => Config::DEBUG_MODE ? $e->getMessage() : null
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => Config::DEBUG_MODE ? $e->getMessage() : null
    ]);
}