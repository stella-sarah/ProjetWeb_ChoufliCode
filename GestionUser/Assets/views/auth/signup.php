<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Inscription</title>
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
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.3);
            border-radius: 4px;
            color: var(--light-text);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-light);
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
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gold-light);
        }
        .signup-link a {
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
            <p>INSCRIPTION</p>
        </div>

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
        
        <form action="index.php?controller=auth&action=signup" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe">
            </div>
            
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom">
            </div>
            
            <div class="form-group">
                <label for="genre">Genre</label>
                <select id="genre" name="genre">
                    <option value="">Sélectionner le genre</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="text" id="date_naissance" name="date_naissance" placeholder="YYYY-MM-DD">
            </div>
            
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="newsletter" value="1">
                    Je souhaite recevoir la newsletter
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="accepte_conditions" value="1">
                    J'accepte les conditions d'utilisation
                </label>
            </div>
            
            <button type="submit" class="login-btn">Créer un compte</button>
        </form>

        <div class="signup-link">
            Déjà un compte ? <a href="index.php?controller=auth&action=showLoginForm">Se connecter</a>
        </div>
    </div>
</body>
</html>