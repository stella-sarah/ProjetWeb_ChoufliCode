<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../../config.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    $pdo = config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour récupérer tous les utilisateurs
    $sql = "SELECT 
                id, 
                email,
                COALESCE(NULLIF(CONCAT(TRIM(prenom), ' ', TRIM(nom)), ''), email) AS display_name
            FROM users 
            WHERE email IS NOT NULL
            ORDER BY display_name ASC";

    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($users),
        'users' => array_map(function($user) {
            return [
                'id' => (int)$user['id'],
                'name' => $user['display_name'],
                'email' => $user['email']
            ];
        }, $users)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}