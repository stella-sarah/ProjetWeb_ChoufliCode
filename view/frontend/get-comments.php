<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../../config.php'; // Include database configuration

$pdo = config::getConnexion();

$post_id = $_GET['post_id'] ?? null;

if ($post_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$post_id]);
        $comments = $stmt->fetchAll();

        echo json_encode(["success" => true, "comments" => $comments]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erreur: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "post_id manquant"]);
}