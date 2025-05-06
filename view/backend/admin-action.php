<?php
// admin-action.php - Gérer les actions administratives (supprimer, restaurer, etc.) dans la base de données TuniFy

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Inclure la configuration de la base de données

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Vérifier les paramètres POST requis
    if (!isset($_POST['type'], $_POST['action'], $_POST['id'])) {
        throw new Exception('Le type, l\'action et l\'ID sont requis');
    }

    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($type) || empty($action) || empty($id)) {
        throw new Exception('Le type, l\'action et l\'ID ne peuvent pas être vides');
    }

    // Valider le type et l'action
    $validTypes = ['post', 'comment', 'report', 'announcement'];
    $validActions = ['delete', 'restore', 'hide', 'resolve', 'reject'];
    if (!in_array($type, $validTypes) || !in_array($action, $validActions)) {
        throw new Exception('Type ou action invalide');
    }

    // Déterminer la table en fonction du type
    $tableMap = [
        'post' => 'posts',
        'comment' => 'comments',
        'report' => 'reports',
        'announcement' => 'announcements'
    ];
    $table = $tableMap[$type];

    // Gérer les actions
    if ($action === 'delete') {
        $query = "UPDATE $table SET is_deleted = 1 WHERE id = :id";
    } elseif ($action === 'restore') {
        $query = "UPDATE $table SET is_deleted = 0 WHERE id = :id";
    } elseif ($action === 'hide' && $type === 'post') {
        $query = "UPDATE $table SET hidden = NOT hidden WHERE id = :id";
    } elseif ($action === 'resolve' && $type === 'report') {
        $query = "UPDATE $table SET status = 'resolved' WHERE id = :id";
    } elseif ($action === 'reject' && $type === 'report') {
        $query = "UPDATE $table SET status = 'rejected' WHERE id = :id";
    } else {
        throw new Exception('Action non applicable pour ce type');
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Aucune entrée trouvée avec cet ID');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Action effectuée avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de l'exécution de l'action : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>