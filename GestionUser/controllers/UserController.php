<?php
require_once 'models/User.php';

class UserController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    // Check if user is logged in and has admin role
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }
    
    // List all users (admin only)
    public function index() {
        $this->checkAdminAccess();
        
        $users = $this->user->getAll();
        require_once 'views/users/index.php';
    }
    
    // Show user profile
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        $user = $this->user->getById($_SESSION['user_id']);
        require_once 'views/users/profile.php';
    }
    
    // Show create user form (admin only)
    public function create() {
        $this->checkAdminAccess();
        require_once 'views/users/create.php';
    }
    
    // Store new user (admin only)
    public function store() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'],
                'mot_de_passe' => $_POST['mot_de_passe'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'genre' => $_POST['genre'],
                'date_naissance' => $_POST['date_naissance'],
                'telephone' => $_POST['telephone'],
                'role' => $_POST['role'],
                'newsletter' => isset($_POST['newsletter']) ? 1 : 0,
                'accepte_conditions' => isset($_POST['accepte_conditions']) ? 1 : 0,
                'photo' => null
            ];
            
            // Handle photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                // Read the file content and convert to base64
                $imageData = file_get_contents($_FILES['photo']['tmp_name']);
                $base64Image = 'data:' . mime_content_type($_FILES['photo']['tmp_name']) . ';base64,' . base64_encode($imageData);
                $data['photo'] = $base64Image;
            }
            
            if ($this->user->create($data)) {
                $_SESSION['success'] = "Utilisateur créé avec succès";
                header("Location: index.php?controller=user&action=index");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l'utilisateur";
                header("Location: index.php?controller=user&action=create");
                exit;
            }
        }
    }
    
    // Show edit user form
    public function edit($id) {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $id)) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        $user = $this->user->getById($id);
        require_once 'views/users/edit.php';
    }
    
    // Update user
    public function update($id) {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $id)) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'genre' => $_POST['genre'],
                'date_naissance' => $_POST['date_naissance'],
                'telephone' => $_POST['telephone'],
                'newsletter' => isset($_POST['newsletter']) ? 1 : 0
            ];
            
            // Only admin can change role
            if ($_SESSION['user_role'] === 'admin') {
                $data['role'] = $_POST['role'];
            }
            
            // Update password only if provided
            if (!empty($_POST['mot_de_passe'])) {
                $data['mot_de_passe'] = $_POST['mot_de_passe'];
            }
            
            // Handle photo removal
            if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
                $data['photo'] = null;
            }
            // Handle new photo upload
            elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                // Read the file content and convert to base64
                $imageData = file_get_contents($_FILES['photo']['tmp_name']);
                $base64Image = 'data:' . mime_content_type($_FILES['photo']['tmp_name']) . ';base64,' . base64_encode($imageData);
                $data['photo'] = $base64Image;
            }
            
            if ($this->user->update($id, $data)) {
                $_SESSION['success'] = "Utilisateur mis à jour avec succès";
                if ($_SESSION['user_role'] === 'admin') {
                    header("Location: index.php?controller=user&action=index");
                } else {
                    header("Location: index.php?controller=user&action=profile");
                }
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur";
                header("Location: index.php?controller=user&action=edit&id=" . $id);
                exit;
            }
        }
    }
    
    // Delete user (admin only)
    public function delete($id) {
        $this->checkAdminAccess();
        
        if ($this->user->delete($id)) {
            $_SESSION['success'] = "Utilisateur supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur";
        }
        
        header("Location: index.php?controller=user&action=index");
        exit;
    }
}