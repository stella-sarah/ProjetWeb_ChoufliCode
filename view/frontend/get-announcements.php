<?php
// Activer l'affichage des erreurs PHP pour faciliter le débogage
ini_set('display_errors', 1);  // Afficher les erreurs
error_reporting(E_ALL);        // Rapport complet des erreurs

// Définir l'en-tête Content-Type pour JSON
header('Content-Type: application/json');

// Configurer le fuseau horaire UTC pour une gestion cohérente des dates
date_default_timezone_set('UTC');

try {
    // Inclure la configuration de la base de données
    require_once '../../config.php'; 

    // Obtenir la connexion à la base de données
    $pdo = Config::getConnexion();

    // Requête SQL pour récupérer uniquement les annonces publiées
    $query = 'SELECT id, title, content, author, created_at, publish_at, is_deleted 
              FROM announcements 
              WHERE is_deleted = 0 
              AND (publish_at IS NULL OR publish_at <= NOW())';
    
    // Exécuter la requête
    $stmt = $pdo->query($query);
    
    // Récupérer toutes les annonces sous forme de tableau associatif
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des annonces ont été trouvées
    if (empty($announcements)) {
        // Si aucune annonce n'est trouvée, renvoyer un message
        echo json_encode([
            'success' => true,
            'message' => 'Aucune annonce disponible'
        ]);
    } else {
        // Si des annonces sont trouvées, renvoyer les résultats
        echo json_encode([
            'success' => true,
            'announcements' => $announcements
        ]);
    }

} catch (Exception $e) {
    // Log des erreurs dans un fichier de log pour le débogage
    error_log("Erreur lors de la récupération des annonces : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    
    // Envoi d'une réponse d'erreur HTTP
    http_response_code(400);
    
    // Réponse JSON pour l'erreur
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>
