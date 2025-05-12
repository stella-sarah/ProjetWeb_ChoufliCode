<?php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Inclure la configuration
require_once '../../config.php';

// Vérifier l'identité de l'utilisateur
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    error_log('Session invalide ou user_id absent');
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Config::getConnexion();

    $stmt = $pdo->prepare("SELECT id, nom, prenom, email FROM users WHERE id = ? AND active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'name' => trim($user['prenom'] . ' ' . $user['nom']),
                'email' => $user['email']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        error_log('Utilisateur non trouvé ou inactif');
        echo json_encode([
            'success' => false,
            'message' => 'Session invalide ou utilisateur inactif'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    error_log('Erreur PDO: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ], JSON_UNESCAPED_UNICODE);
}
?>