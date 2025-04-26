<?php
// get-reports.php - Fetch reports from TuniFy database

header('Content-Type: application/json');

try {
    require_once '../../config.php'; // Include database configuration

    // Get database connection
    $pdo = Config::getConnexion();

    $query = 'SELECT id, content_type, content_id, reporter, reason, details, status, created_at 
              FROM reports 
              ORDER BY created_at DESC';
    
    $stmt = $pdo->query($query);
    $reports = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching reports: " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching reports: ' . $e->getMessage()
    ]);
}
?>