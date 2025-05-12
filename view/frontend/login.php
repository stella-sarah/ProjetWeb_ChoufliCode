<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config.php';

try {
    $pdo = Config::getConnexion();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe manquant']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, mot_de_passe FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // À remplacer par password_verify() en production
    if ($user && $user['mot_de_passe'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        error_log("Login réussi - UserID: ".$user['id'].", SessionID: ".session_id());
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
} catch (PDOException $e) {
    error_log("Erreur PDO: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
?>