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
            padding: 30px 45px;
            border: 1px solid rgba(201, 168, 108, 0.1);
        }
        
        .logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 30px;
}

.logo img {
    height: 80px;
    margin-bottom: 10px;
}
        .logo h1 {
    color: var(--gold-primary);
    margin: 0;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    letter-spacing: 1px;
    font-size: 28px;
}

.logo p {
    color: var(--gold-light);
    margin-top: 5px;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
}
        
        .error-message {
            background-color: rgba(255, 99, 132, 0.1);
            color: #ff6384;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 99, 132, 0.3);
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
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.3);
            border-radius: 4px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .form-group input:focus,
        .form-group select:focus {
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

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-light);
            font-size: 14px;
        }

        .checkbox-label input[type="checkbox"] {
            width: auto;
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
        
        <form action="index.php?controller=auth&action=signup" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            
            <div class="form-group">
                <label for="genre">Genre</label>
                <select id="genre" name="genre" required>
                    <option value="">Sélectionner le genre</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" required>
            </div>
            
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" required>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="newsletter" value="1">
                    Je souhaite recevoir la newsletter
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="accepte_conditions" value="1" required>
                    J'accepte les conditions d'utilisation
                </label>
            </div>
            
            <button type="submit" class="login-btn">Créer un compte</button>
        </form>

        <div class="signup-link">
            Vous avez déjà un compte ? <a href="index.php?controller=auth&action=showLoginForm">Se connecter</a>
        </div>
    </div>
</body>
</html> 