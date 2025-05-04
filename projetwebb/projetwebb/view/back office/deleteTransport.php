<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée ou ID manquant']);
    exit;
}

$id = intval($_GET['id']);
$pdo = config::getConnexion();

try {
    // Récupérer le nom de l'image pour la supprimer du serveur
    $query = $pdo->prepare('SELECT image FROM transport WHERE id = ?');
    $query->execute([$id]);
    $transport = $query->fetch();
    
    if ($transport) {
        // Supprimer l'image si ce n'est pas l'image par défaut
        if ($transport['image'] !== 'default.jpg') {
            $imagePath = '../../uploads/' . $transport['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Supprimer le transport de la base de données
        $deleteQuery = $pdo->prepare('DELETE FROM transport WHERE id = ?');
        $deleteQuery->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Transport supprimé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transport non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>