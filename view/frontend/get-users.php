<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *"); // À remplacer par votre domaine en production
header("Access-Control-Allow-Credentials: true");

// Configuration du logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
$logFile = __DIR__ . '/../../logs/users.log';
file_put_contents($logFile, PHP_EOL . date('Y-m-d H:i:s') . " - Début de la requête", FILE_APPEND);

// Inclure la configuration
require_once __DIR__ . '/../../config.php';

try {
    if (empty($_SESSION['user_id'])) {
        file_put_contents($logFile, PHP_EOL . "Erreur: Session utilisateur non trouvée", FILE_APPEND);
        throw new Exception('Session expirée ou invalide', 401);
    }

    $userId = (int)$_SESSION['user_id'];
    file_put_contents($logFile, PHP_EOL . "Traitement pour l'utilisateur ID: $userId", FILE_APPEND);

    $pdo = Config::getConnexion();

    // Vérifier si l'utilisateur existe et est actif
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE id = ? AND active = 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        file_put_contents($logFile, PHP_EOL . "Erreur: Utilisateur $userId inactif ou inexistant", FILE_APPEND);
        throw new Exception('Utilisateur non trouvé', 404);
    }

    // Requête principale
    $sql = "
        SELECT 
            id, 
            email,
            CASE 
                WHEN TRIM(CONCAT(COALESCE(prenom,''), ' ', COALESCE(nom,''))) = '' THEN email
                ELSE CONCAT(COALESCE(prenom,''), ' ', COALESCE(nom,''))
            END AS display_name
        FROM users 
        WHERE active = 1 AND id != :current_user_id
        ORDER BY display_name ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':current_user_id' => $userId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        file_put_contents($logFile, PHP_EOL . "Aucun autre utilisateur trouvé", FILE_APPEND);
    }

    $response = [
        'success' => true,
        'users' => array_map(function($user) {
            return [
                'id' => (int)$user['id'],
                'name' => $user['display_name'],
                'email' => $user['email']
            ];
        }, $users)
    ];

    file_put_contents($logFile, PHP_EOL . "Réponse envoyée: " . json_encode($response), FILE_APPEND);
    echo json_encode($response);

} catch (PDOException $e) {
    $errorMsg = "Erreur PDO: " . $e->getMessage();
    file_put_contents($logFile, PHP_EOL . $errorMsg, FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    $errorMsg = "Erreur: " . $e->getMessage();
    file_put_contents($logFile, PHP_EOL . $errorMsg, FILE_APPEND);
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}

file_put_contents($logFile, PHP_EOL . date('Y-m-d H:i:s') . " - Fin de la requête", FILE_APPEND);
?>