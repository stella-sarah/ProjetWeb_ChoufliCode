<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    $pdo = Config::getConnexion();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Action non reconnue'];
        
        switch ($action) {
            case 'add':
                $client = $_POST['client'] ?? '';
                $service = $_POST['service'] ?? '';
                $date = $_POST['date'] ?? '';
                $time = $_POST['time'] ?? '';
                $status = $_POST['status'] ?? 'En attente';
                $notes = $_POST['notes'] ?? '';
                
                if (empty($client) || empty($service) || empty($date) || empty($time)) {
                    throw new Exception('Tous les champs obligatoires doivent être remplis');
                }
                
                $datetime = $date . ' ' . $time;
                
                $stmt = $pdo->prepare("INSERT INTO ev_back (client, service, date, status, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$client, $service, $datetime, $status, $notes]);
                
                $response = [
                    'success' => true,
                    'message' => 'Réservation ajoutée avec succès',
                    'id' => $pdo->lastInsertId()
                ];
                break;
                
            case 'edit':
                $id = $_POST['reservation_id'] ?? 0;
                $client = $_POST['client'] ?? '';
                $service = $_POST['service'] ?? '';
                $date = $_POST['date'] ?? '';
                $time = $_POST['time'] ?? '';
                $status = $_POST['status'] ?? '';
                $notes = $_POST['notes'] ?? '';
                
                if (empty($id) || empty($client) || empty($service) || empty($date) || empty($time)) {
                    throw new Exception('Tous les champs obligatoires doivent être remplis');
                }
                
                $datetime = $date . ' ' . $time;
                
                $stmt = $pdo->prepare("UPDATE ev_back SET client = ?, service = ?, date = ?, status = ?, notes = ? WHERE id = ?");
                $stmt->execute([$client, $service, $datetime, $status, $notes, $id]);
                
                $response = [
                    'success' => true,
                    'message' => 'Réservation mise à jour avec succès'
                ];
                break;
                
            case 'delete':
                $id = $_POST['reservation_id'] ?? 0;
                
                if (empty($id)) {
                    throw new Exception('ID de réservation manquant');
                }
                
                $stmt = $pdo->prepare("DELETE FROM ev_back WHERE id = ?");
                $stmt->execute([$id]);
                
                $response = [
                    'success' => true,
                    'message' => 'Réservation supprimée avec succès'
                ];
                break;
        }
        
        echo json_encode($response);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?> 