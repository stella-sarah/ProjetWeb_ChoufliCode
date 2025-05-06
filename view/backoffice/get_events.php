<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = Config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT id, title, event_date FROM events ORDER BY event_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des événements : ' . $e->getMessage()]);
}
?> 