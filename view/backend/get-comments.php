<?php
// get-comments.php - Fetch comments from TuniFy database

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Include database configuration

    // Get database connection
    $pdo = Config::getConnexion();

    $isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
    
    $query = 'SELECT id, post_id, author, content, created_at, is_deleted, last_updated 
              FROM comments';
    
    if (!$isAdmin) {
        $query .= ' WHERE is_deleted = 0';
    }
    
    $query .= ' ORDER BY created_at DESC';
    
    $stmt = $pdo->query($query);
    $comments = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching comments: " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching comments: ' . $e->getMessage()
    ]);
}
?>