<?php
// CAPTCHA is now set in AuthController.php showLoginForm method
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Connexion</title>
    <link rel="stylesheet" href="assets/css/styleback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--darker-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: var(--dark-bg);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 400px;
            padding: 30px;
            border: 1px solid rgba(201, 168, 108, 0.1);
        }
        .logo-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-image img {
            height: 60px;
        }
        .brand-text {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            color: var(--gold-primary);
        }
        .connexion-text {
            color: var(--gold-light);
            font-size: 14px;
            text-transform: uppercase;
        }
        .error-message {
            background-color: rgba(255, 99, 132, 0.1);
            color: #ff6384;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: rgba(75, 192, 192, 0.1);
            color: #4bc0c0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            color: var(--gold-light);
            margin-bottom: 8px;
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.3);
            border-radius: 4px;
            color: var(--light-text);
        }
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .captcha-code {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .captcha-refresh {
            background: none;
            border: none;
            color: var(--gold-light);
            cursor: pointer;
        }
        .login-btn {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            border: none;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
        }
        .signup-link, .forgot-password-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gold-light);
        }
        .signup-link a, .forgot-password-link a {
            color: var(--gold-primary);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-header">
            <div class="logo-image">
                <img src="assets/images/logo.png" alt="TuniFy Logo">
            </div>
            <h1 class="brand-text">TuniFy</h1>
            <p class="connexion-text">CONNEXION</p>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['errors'])): ?>
            <div class="error-message">
                <ul>
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <form action="index.php?controller=auth&action=login" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe"

 name="mot_de_passe">
            </div>
            
            <div class="form-group">
                <label for="captcha">Code CAPTCHA</label>
                <div class="captcha-container">
                    <span class="captcha-code"><?php echo $_SESSION['captcha']; ?></span>
                    <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <input type="text" id="captcha" name="captcha" placeholder="Entrez le code">
            </div>
            
            <button type="submit" class="login-btn">Se connecter</button>
        </form>

        <div class="forgot-password-link">
            <a href="index.php?controller=auth&action=showForgotPasswordForm">Mot de passe oublié ?</a>
        </div>

        <div class="signup-link">
            Pas de compte ? <a href="index.php?controller=auth&action=showSignupForm">Créer un compte</a>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            fetch('index.php?controller=auth&action=refreshCaptcha', { method: 'POST' })
                .then(response => response.text())
                .then(data => {
                    document.querySelector('.captcha-code').textContent = data;
                });
        }
    </script>
</body>
</html>