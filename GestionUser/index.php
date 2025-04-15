<?php
session_start();

// Include database and object files
require_once "config/Database.php";
require_once "controllers/UserController.php";

// Get controller and action from URL parameters
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'showLoginForm';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// List of public actions that don't require authentication
$publicActions = ['showLoginForm', 'login', 'showSignupForm', 'signup'];

// If not logged in and trying to access protected pages, redirect to login
if(!$isLoggedIn && $controller != 'auth' && !in_array($action, $publicActions)) {
    header("Location: index.php?controller=auth&action=showLoginForm");
    exit;
}

// Route the request to the appropriate controller and action
switch($controller) {
    case 'user':
        $userController = new UserController();
        
        switch($action) {
            case 'index':
                $userController->index();
                break;
            case 'create':
                $userController->create();
                break;
            case 'store':
                $userController->store();
                break;
            case 'edit':
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                $userController->edit($id);
                break;
            case 'update':
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                $userController->update($id);
                break;
            case 'profile':
                $userController->profile();
                break;
            case 'delete':
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                $userController->delete($id);
                break;
            default:
                $userController->index();
                break;
        }
        break;
        
    case 'auth':
    default:
        require_once "controllers/AuthController.php";
        $authController = new AuthController();
        
        switch($action) {
            case 'showLoginForm':
                $authController->showLoginForm();
                break;
            case 'login':
                $authController->login();
                break;
            case 'showSignupForm':
                $authController->showSignupForm();
                break;
            case 'signup':
                $authController->signup();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                $authController->showLoginForm();
                break;
        }
        break;
}
?>