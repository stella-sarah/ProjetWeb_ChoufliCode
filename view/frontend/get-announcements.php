<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

try {
    $configPath = '../../config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Config file not found at ' . $configPath);
    }
    require_once $configPath;

    if (!class_exists('Config')) {
        throw new Exception('Config class not defined in config.php');
    }

    $pdo = Config::getConnexion();
    if (!$pdo) {
        throw new Exception('Failed to get PDO connection');
    }

    $query = 'SHOW TABLES LIKE "announcements"';
    $stmt = $pdo->query($query);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Table "announcements" does not exist');
    }

    $query = 'SELECT id, title, content, author, created_at FROM announcements WHERE is_deleted = 0 ORDER BY created_at DESC';
    $stmt = $pdo->query($query);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    error_log("Erreur lors de la récupération des annonces : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>