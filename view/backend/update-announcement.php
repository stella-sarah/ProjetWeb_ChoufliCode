<?php
// update-announcement.php - Mettre à jour une annonce existante dans la base de données TuniFy

header('Content-Type: application/json');

// Set UTC timezone for consistent date handling
date_default_timezone_set('UTC');

try {
    // Include database configuration
    require_once '../../config.php';

    // Get database connection
    $pdo = Config::getConnexion();

    // Get POST data
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $publish_at = isset($_POST['publish_at']) ? trim($_POST['publish_at']) : '';

    // Validate required fields
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'L\'ID de l\'annonce est requis']);
        exit;
    }
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Le titre est requis']);
        exit;
    }
    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Le contenu est requis']);
        exit;
    }
    if (empty($author)) {
        echo json_encode(['success' => false, 'message' => 'L\'auteur est requis']);
        exit;
    }

    // Validate publish_at if provided
    if ($publish_at) {
        // Ensure publish_at is in correct format (YYYY-MM-DD HH:MM:SS)
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $publish_at)) {
            echo json_encode(['success' => false, 'message' => 'Format de date invalide (attendu : YYYY-MM-DD HH:MM:SS)']);
            exit;
        }

        // Check if publish_at is in the future (1-minute buffer)
        $publish_timestamp = strtotime($publish_at);
        if ($publish_timestamp === false || $publish_timestamp <= time() + 60) {
            echo json_encode(['success' => false, 'message' => 'Erreur : La date de publication doit être dans le futur (au moins 1 minute)']);
            exit;
        }
    }

    // Check if announcement exists
    $stmt = $pdo->prepare('SELECT id FROM announcements WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Annonce non trouvée']);
        exit;
    }

    // Prepare SQL statement
    $sql = 'UPDATE announcements SET title = ?, content = ?, author = ?, publish_at = ? WHERE id = ?';
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->execute([
        $title,
        $content,
        $author,
        $publish_at ?: null, // Use null if publish_at is empty
        $id
    ]);

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Annonce mise à jour avec succès']);

} catch (Exception $e) {
    // Log error to file
    error_log("Erreur lors de la mise à jour de l'annonce : " . $e->getMessage(), 3, __DIR__ . '/error.log');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>