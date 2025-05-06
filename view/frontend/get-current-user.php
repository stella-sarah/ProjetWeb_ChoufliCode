<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Même fichier de configuration que votre exemple

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(
        ['success' => false, 'message' => 'Non authentifié'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    $pdo = config::getConnexion();
    $pdo->exec("SET NAMES utf8mb4");
    
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => trim($user['prenom'] . ' ' . $user['nom']),
                'email' => $user['email']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(
            ['success' => false, 'message' => 'Utilisateur non trouvé'],
            JSON_UNESCAPED_UNICODE
        );
    }
} catch (PDOException $e) {
    echo json_encode(
        ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}
?>