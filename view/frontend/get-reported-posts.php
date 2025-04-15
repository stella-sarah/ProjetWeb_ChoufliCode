<?php
header('Content-Type: application/json');
require_once 'config.php';

$pdo = config::getConnexion();

try {
    $stmt = $pdo->query("
        SELECT p.*, r.reason, r.details, r.reporter, r.created_at as report_date 
        FROM posts p
        JOIN reports r ON p.id = r.content_id
        WHERE p.reported = 1
        ORDER BY r.created_at DESC
    ");
    $reportedPosts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'reported_posts' => $reportedPosts]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}