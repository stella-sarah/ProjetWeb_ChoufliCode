<?php
header('Content-Type: application/json');
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

// Récupérer les paramètres de recherche
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

try {
    // Construction de la requête SQL de base avec les mêmes filtres que get-posts.php
    $sql = "SELECT * FROM posts WHERE hidden = FALSE AND is_deleted = FALSE";
    $params = [];
    
    // Ajout des conditions de recherche si une requête est spécifiée
    if (!empty($query)) {
        $searchTerm = "%$query%";
        
        switch ($type) {
            case 'title':
                $sql .= " AND title LIKE ?";
                $params[] = $searchTerm;
                break;
            case 'content':
                $sql .= " AND content LIKE ?";
                $params[] = $searchTerm;
                break;
            case 'author':
                $sql .= " AND author LIKE ?";
                $params[] = $searchTerm;
                break;
            case 'region':
                $sql .= " AND region LIKE ?";
                $params[] = $searchTerm;
                break;
            default: // 'all'
                $sql .= " AND (title LIKE ? OR content LIKE ? OR author LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
    }
    
    // Ajout du tri par date de création (du plus récent au plus ancien)
    $sql .= " ORDER BY created_at DESC";
    
    // Préparation et exécution de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Récupération des résultats
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatage des résultats pour correspondre à get-posts.php
    foreach ($posts as &$post) {
        // Récupérer les commentaires non supprimés
        $stmtComments = $pdo->prepare("
            SELECT * FROM comments 
            WHERE post_id = ? AND is_deleted = FALSE 
            ORDER BY created_at
        ");
        $stmtComments->execute([$post['id']]);
        $post['comments'] = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatage des URLs des médias
        if (!empty($post['image_url'])) {
            $post['image_url'] = str_replace('uploads/', '', $post['image_url']);
        }
        if (!empty($post['video_url'])) {
            $post['video_url'] = str_replace('uploads/', '', $post['video_url']);
        }
    }
    
    // Retourner le même format que get-posts.php
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
}