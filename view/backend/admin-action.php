<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../config.php';

try {
    // Debugging - Enregistrez la requête reçue
    file_put_contents('admin_actions.log', date('Y-m-d H:i:s')." - ".print_r($_POST, true)."\n", FILE_APPEND);

    $pdo = Config::getConnexion();
    
    // Validation renforcée
    $required = ['type', 'action', 'id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Champ manquant: $field");
        }
    }

    $type = $_POST['type'];
    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    // Whitelist des actions autorisées
    $validTypes = ['post', 'comment', 'report', 'announcement'];
    $validActions = ['delete', 'restore', 'hide', 'resolve', 'reject'];

    if (!in_array($type, $validTypes) || !in_array($action, $validActions)) {
        throw new Exception("Type ou action non valide");
    }

    // Requête préparée sécurisée
    switch ("$type:$action") {
        case 'announcement:delete':
            $query = "UPDATE announcements SET is_deleted = 1, last_updated = NOW() WHERE id = ?";
            break;
        case 'announcement:restore':
            $query = "UPDATE announcements SET is_deleted = 0, last_updated = NOW() WHERE id = ?";
            break;
        default:
            throw new Exception("Combinaison type/action non supportée");
    }

    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([$id]);

    echo json_encode([
        'success' => $success,
        'affected_rows' => $stmt->rowCount(),
        'message' => $success ? 'Action réussie' : 'Aucune modification'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString() // À désactiver en production
    ]);
}