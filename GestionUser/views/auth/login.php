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
            padding: 30px 35px 30px 30px;
            border: 1px solid rgba(201, 168, 108, 0.1);
            overflow: hidden;
        }
        
        .logo-header {
            background-color: var(--dark-bg);
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        }
        
        .logo-image {
            margin-bottom: 15px;
        }
        
        .logo-image img {
            height: 60px;
        }
        
        .brand-text {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--gold-primary);
            margin: 10px 0 5px;
        }
        
        .connexion-text {
            color: var(--gold-light);
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .error-message {
            background-color: var(--dark-bg);
            color: var(--gold-light);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid var(--gold-primary);
            font-size: 14px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gold-light);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.3);
            border-radius: 4px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 2px rgba(201, 168, 108, 0.2);
        }
        
        .login-btn {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .login-btn:hover {
            background-color: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gold-light);
        }

        .signup-link a {
            color: var(--gold-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: var(--gold-light);
            text-decoration: underline;
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
        
        <div class="login-form">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php?controller=auth&action=login" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <button type="submit" class="login-btn">Se connecter</button>
            </form>

            <div class="signup-link">
                Vous n'avez pas de compte ? <a href="index.php?controller=auth&action=showSignupForm">Créer un compte</a>
            </div>
        </div>
    </div>
</body>
</html>