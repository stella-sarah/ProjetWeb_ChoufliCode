<?php
require_once __DIR__ . '/../../controller/PlatC.php';

$platC = new PlatC();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: gestion_plats.php');
    exit;
}

$id = intval($_GET['id']);

// Try to delete the dish
if ($platC->supprimerPlat($id)) {
    header('Location: gestion_plats.php?delete_success=1');
} else {
    header('Location: gestion_plats.php?delete_error=1');
}
exit; 