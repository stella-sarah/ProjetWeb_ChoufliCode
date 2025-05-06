<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID de réservation non spécifié']);
    exit;
}

$reservation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

try {
    $pdo = Config::getConnexion();
    
    $sql = "SELECT r.*, e.title as event_title 
            FROM reservations r 
            JOIN events e ON r.event_id = e.id 
            WHERE r.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservation) {
        // Formater la date
        $reservation['created_at'] = date('d/m/Y H:i', strtotime($reservation['created_at']));
        echo json_encode($reservation);
    } else {
        echo json_encode(['error' => 'Réservation non trouvée']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur lors de la récupération des détails : ' . $e->getMessage()]);
} 