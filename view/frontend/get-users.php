<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *"); // À remplacer par votre domaine en production
header("Access-Control-Allow-Credentials: true");

require_once __DIR__.'/../../config.php';

// Activer le logging des erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Début de get-users.php");

try {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Utilisateur non authentifié', 401);
    }

    $current_user_id = $_SESSION['user_id'];
    error_log("User ID: ".$current_user_id);

    $pdo = config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête optimisée avec gestion des valeurs NULL
    $sql = "SELECT 
                id, 
                email,
                CASE 
                    WHEN CONCAT(COALESCE(prenom,''), ' ', COALESCE(nom,'')) = ' ' THEN email
                    ELSE CONCAT(COALESCE(prenom,''), ' ', COALESCE(nom,''))
                END AS display_name
            FROM users 
            WHERE id != ? 
            AND email IS NOT NULL
            ORDER BY display_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Nombre d'utilisateurs trouvés: ".count($users));

    if (empty($users)) {
        error_log("Aucun utilisateur trouvé (sauf l'utilisateur courant)");
    }

    echo json_encode([
        'success' => true,
        'users' => array_map(function($user) {
            return [
                'id' => (int)$user['id'],
                'name' => $user['display_name'],
                'email' => $user['email']
            ];
        }, $users)
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO: ".$e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erreur générale: ".$e->getMessage());
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}