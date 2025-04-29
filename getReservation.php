<?php
require_once '../../controller/transportcontroller.php';
header('Content-Type: application/json');

$transportController = new TransportController();
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $reservation = $transportController->getReservationById($id);
    if ($reservation) {
        echo json_encode($reservation);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Réservation non trouvée']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID invalide']);
}
?>