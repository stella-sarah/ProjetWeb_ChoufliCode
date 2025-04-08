<?php
/**
 * Gère l'inscription des utilisateurs
 */
class InscriptionController {
    
    // Affiche le formulaire d'inscription
    public function showForm() {
        include '../../View/FrontOffice/inscription.php';
    }

    // Traite le formulaire (simulation sans base de données)
    public function processForm() {
        // Simulation de sauvegarde
        $userData = [
            'nom' => $_POST['nom'],
            'email' => $_POST['email']
        ];
        
        // Redirige vers la page de connexion
        header('Location: /GestionUser/View/FrontOffice/connexion.php');
    }
}

// Utilisation basique
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new InscriptionController();
    $controller->processForm();
} else {
    $controller = new InscriptionController();
    $controller->showForm();
}
?>