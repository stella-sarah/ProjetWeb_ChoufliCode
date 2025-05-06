<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réinitialiser le mot de passe</title>
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
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 60px;
        }
        .logo h1 {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            color: var(--gold-primary);
        }
        .logo p {
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
        .submit-btn {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            border: none;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gold-light);
        }
        .back-link a {
            color: var(--gold-primary);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/images/logo.png" alt="TuniFy Logo">
            <h1>TuniFy</h1>
            <p>RÉINITIALISER LE MOT DE PASSE</p>
        </div>

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
        
        <form action="index.php?controller=auth&action=resetPassword" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div class="form-group">
                <label for="mot_de_passe">Nouveau mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre nouveau mot de passe">
            </div>
            
            <div class="form-group">
                <label for="confirm_mot_de_passe">Confirmer le mot de passe</label>
                <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" placeholder="Confirmez votre mot de passe">
            </div>
            
            <button type="submit" class="submit-btn">Réinitialiser le mot de passe</button>
        </form>

        <div class="back-link">
            <a href="index.php?controller=auth&action=showLoginForm">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>