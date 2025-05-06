<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    $pdo = Config::getConnexion();
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    switch ($action) {
        case 'add':
            $stmt = $pdo->prepare("INSERT INTO ev_back (client, service, date, time, status, notes) 
                                  VALUES (:client, :service, :date, :time, :status, :notes)");
            $stmt->execute([
                ':client' => htmlspecialchars($_POST['client']),
                ':service' => htmlspecialchars($_POST['service']),
                ':date' => $_POST['date'],
                ':time' => $_POST['time'],
                ':status' => $_POST['status'],
                ':notes' => $_POST['notes'] ?? null
            ]);
            $response['success'] = true;
            $response['message'] = 'Réservation ajoutée avec succès';
            break;
            
        case 'edit':
            $stmt = $pdo->prepare("UPDATE ev_back SET 
                                 client = :client, 
                                 service = :service, 
                                 date = :date, 
                                 time = :time, 
                                 status = :status, 
                                 notes = :notes 
                                 WHERE id = :id");
            $stmt->execute([
                ':client' => htmlspecialchars($_POST['client']),
                ':service' => htmlspecialchars($_POST['service']),
                ':date' => $_POST['date'],
                ':time' => $_POST['time'],
                ':status' => $_POST['status'],
                ':notes' => $_POST['notes'] ?? null,
                ':id' => $_POST['reservation_id']
            ]);
            $response['success'] = true;
            $response['message'] = 'Réservation mise à jour avec succès';
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM ev_back WHERE id = :id");
            $stmt->execute([':id' => $_POST['reservation_id']]);
            $response['success'] = true;
            $response['message'] = 'Réservation supprimée avec succès';
            break;
            
        default:
            $response['message'] = 'Action non reconnue';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
}

echo json_encode($response);