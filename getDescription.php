<?php
require_once '../../config.php';
require_once '../../controller/transportcontroller.php';

header('Content-Type: application/json');

$transportController = new TransportController();

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID manquant', 'status' => 'error']);
    exit;
}

$id = intval($_GET['id']);

try {
    $transport = $transportController->getTransportById($id);
    
    if ($transport) {
        echo json_encode([
            'vitesse' => $transport['vitesse'],
            'nb_places' => $transport['nb_places'],
            'batterie' => $transport['batterie'],
            'status' => 'success'
        ]);
    } else {
        echo json_encode([
            'error' => 'Transport non trouvé',
            'status' => 'error'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erreur: ' . $e->getMessage(),
        'status' => 'error'
    ]);
}
?>