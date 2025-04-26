<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

// Récupération des données du formulaire
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$author = 'Anonyme';
$region = $_POST['region'] ?? 'Non spécifiée';
$imagePath = '';
$videoPath = '';

// Configuration du répertoire d'upload
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Gestion de l'upload d'image
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePath = $uploadDir . $imageName;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'upload de l'image"]);
        exit;
    }
}

// Gestion de l'upload de vidéo
if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
    $videoName = uniqid() . '_' . basename($_FILES['video']['name']);
    $videoPath = $uploadDir . $videoName;
    if (!move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'upload de la vidéo"]);
        exit;
    }
}

// Validation des données minimales
if (empty($title) || (empty($content) && empty($imagePath) && empty($videoPath))) {
    echo json_encode([
        "success" => false,
        "message" => "Le titre est obligatoire et vous devez ajouter du contenu, une image ou une vidéo"
    ]);
    exit;
}

try {
    // Préparation et exécution de la requête SQL
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, author, region, image_url, video_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $author, $region, $imagePath, $videoPath]);
    
    $postId = $pdo->lastInsertId();
    
    // Récupérer le post créé pour le renvoyer
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    echo json_encode([
        "success" => true,
        "post" => [
            "id" => $post['id'],
            "title" => $post['title'],
            "content" => $post['content'],
            "author" => $post['author'],
            "region" => $post['region'],
            "image_url" => $post['image_url'],
            "video_url" => $post['video_url'],
            "created_at" => $post['created_at']
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur d'insertion : " . $e->getMessage()
    ]);
}