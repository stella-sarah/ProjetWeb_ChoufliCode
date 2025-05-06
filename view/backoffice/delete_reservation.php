<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de réservation non spécifié']);
    exit;
}

$reservation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

try {
    $pdo = Config::getConnexion();
    
    // Vérifier si la réservation existe
    $sql = "SELECT id FROM reservations WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $reservation_id]);
    
    if ($stmt->fetch()) {
        // Supprimer la réservation
        $sql = "DELETE FROM reservations WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $reservation_id]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Réservation non trouvée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
} 