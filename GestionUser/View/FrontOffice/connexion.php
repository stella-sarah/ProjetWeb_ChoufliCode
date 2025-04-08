<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="/GestionUser/Assets/css/style.css">
</head>
<body>
    <section class="about">
        <div class="container">
            <!-- Formulaire de connexion -->
            <form action="/GestionUser/Controller/FrontOffice/ConnexionController.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
                <button type="submit" class="form-submit">Se connecter</button>
            </form>
        </div>
    </section>
</body>
</html>