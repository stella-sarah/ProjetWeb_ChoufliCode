<?php
session_start();

require_once 'config/Database.php';
require_once 'config/Mailer.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/UserController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'showLoginForm';

$db = new Database();
$dbConnection = $db->getConnection();

switch ($controller) {
    case 'auth':
        $authController = new AuthController($dbConnection);
        switch ($action) {
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
            case 'refreshCaptcha':
                $authController->refreshCaptcha();
                break;
            case 'showForgotPasswordForm':
                $authController->showForgotPasswordForm();
                break;
            case 'forgotPassword':
                $authController->forgotPassword();
                break;
            case 'showResetPasswordForm':
                $authController->showResetPasswordForm();
                break;
            case 'resetPassword':
                $authController->resetPassword();
                break;
            default:
                $authController->showLoginForm();
                break;
        }
        break;

    case 'user':
        $userController = new UserController($dbConnection);
        switch ($action) {
            case 'index':
                $userController->index();
                break;
            case 'store':
                $userController->store();
                break;
            case 'edit':
                $userController->edit();
                break;
            case 'update':
                $userController->update();
                break;
            case 'delete':
                $userController->delete();
                break;
            case 'profile':
                $userController->profile();
                break;
            default:
                $userController->index();
                break;
        }
        break;

    default:
        $authController = new AuthController($dbConnection);
        $authController->showLoginForm();
        break;
}
?>