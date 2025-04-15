<?php
require_once 'models/User.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    // Show login form
    public function showLoginForm() {
        if(isset($_SESSION['user_id'])) {
            // If already logged in, redirect based on role
            if($_SESSION['user_role'] === 'admin') {
                header("Location: index.php?controller=user&action=index");
            } else {
                header("Location: index.php?controller=user&action=profile");
            }
            exit;
        }
        require_once 'views/auth/login.php';
    }
    
    // Show signup form
    public function showSignupForm() {
        require_once 'views/auth/signup.php';
    }
    
    // Process login
    public function login() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }

        // Debug: Log session state before login attempt
        error_log("Session state before login attempt: " . print_r($_SESSION, true));

        // Validate form data
        if(!isset($_POST['email']) || !isset($_POST['mot_de_passe'])) {
            $_SESSION['error'] = "Veuillez fournir un email et un mot de passe";
            error_log("Login failed: Missing email or password");
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }
        
        $email = $_POST['email'];
        $password = $_POST['mot_de_passe'];
        
        // Debug: Vérifier les données reçues
        error_log("Tentative de connexion - Email: " . $email);
        
        // Attempt login
        $user = $this->user->login($email, $password);
        
        if($user) {
            // Debug: Log successful user data before setting session
            error_log("User data before setting session: " . print_r($user, true));
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user'] = [
                'photo' => $user['photo']
            ];
            
            // Debug: Log session state after setting variables
            error_log("Session state after setting variables: " . print_r($_SESSION, true));
            
            error_log("Connexion réussie pour l'utilisateur: " . $user['email']);
            
            // Redirect based on role
            if($user['role'] === 'admin') {
                header("Location: index.php?controller=user&action=index");
            } else {
                header("Location: index.php?controller=user&action=profile");
            }
            exit;
        } else {
            error_log("Échec de connexion pour l'email: " . $email);
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header("Location: index.php?controller=auth&action=showLoginForm");
            exit;
        }
    }
    
    // Process signup
    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'],
                'mot_de_passe' => $_POST['mot_de_passe'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'genre' => $_POST['genre'],
                'date_naissance' => $_POST['date_naissance'],
                'telephone' => $_POST['telephone'],
                'role' => 'user', // Default role for new users
                'newsletter' => isset($_POST['newsletter']) ? 1 : 0,
                'accepte_conditions' => isset($_POST['accepte_conditions']) ? 1 : 0
            ];
            
            // Check if email already exists
            if($this->user->emailExists($data['email'])) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
                header("Location: index.php?controller=auth&action=showSignupForm");
                exit;
            }
            
            if ($this->user->create($data)) {
                $_SESSION['success'] = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
                header("Location: index.php?controller=auth&action=showLoginForm");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la création du compte";
                header("Location: index.php?controller=auth&action=showSignupForm");
                exit;
            }
        }
    }
    
    // Process logout
    public function logout() {
        // Debug: Log session state before logout
        error_log("Session state before logout - Session ID: " . session_id());
        error_log("Session data before logout: " . print_r($_SESSION, true));
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Debug: Log session destruction
        error_log("Session destroyed - Previous Session ID was: " . session_id());
        
        header("Location: index.php?controller=auth&action=showLoginForm");
        exit;
    }
}
