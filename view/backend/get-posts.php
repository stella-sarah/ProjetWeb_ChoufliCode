<?php
// get-posts.php - Fetch posts from TuniFy database

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Include database configuration

    // Get database connection
    $pdo = Config::getConnexion();

    $isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
    
    $query = 'SELECT id, title, content, author, image_url, video_url, created_at, hidden, is_deleted, last_updated, original_id 
              FROM posts';
    
    if (!$isAdmin) {
        $query .= ' WHERE is_deleted = 0 AND hidden = 0';
    }
    
    $query .= ' ORDER BY created_at DESC';
    
    $stmt = $pdo->query($query);
    $posts = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching posts: " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching posts: ' . $e->getMessage()
    ]);
}
?>