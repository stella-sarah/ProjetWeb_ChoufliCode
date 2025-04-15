<?php
// delete-photo.php - Script pour supprimer une photo
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Photo.php';

// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
// Si vous avez un système d'authentification, utilisez-le ici
// if(!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Vérifier si l'ID est fourni
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: upload-photo.php?error=id_missing");
    exit();
}

// Initialiser la connexion à la base de données
$db = config::getConnexion();

// Initialiser l'objet Photo
$photo = new Photo($db);

// Récupérer l'ID de la photo
$id = intval($_GET['id']);

// Supprimer la photo
if($photo->deletePhoto($id)) {
    header("Location: upload-photo.php?message=photo_deleted");
} else {
    header("Location: upload-photo.php?error=delete_failed");
}
?>