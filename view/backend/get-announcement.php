<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../config.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID manquant');
    }

    $id = (int)$_GET['id'];
    $pdo = Config::getConnexion();
    
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        throw new Exception('Annonce non trouvée');
    }

    echo json_encode([
        'success' => true,
        'announcement' => $announcement
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}