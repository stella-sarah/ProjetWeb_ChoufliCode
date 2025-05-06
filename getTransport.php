<?php
require_once '../../config.php';
require_once '../../model/transport.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$id = intval($_GET['id']);
$pdo = config::getConnexion();

try {
    $query = $pdo->prepare('SELECT * FROM transport WHERE id = ?');
    $query->execute([$id]);
    $transport = $query->fetch();
    
    if ($transport) {
        echo json_encode($transport);
    } else {
        echo json_encode(['error' => 'Transport non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>