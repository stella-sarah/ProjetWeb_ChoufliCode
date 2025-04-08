<!DOCTYPE html>
<html>
<head>
    <!-- Titre de la page -->
    <title>Inscription</title>
    <!-- Lien vers le fichier CSS -->
    <link rel="stylesheet" href="/GestionUser/Assets/css/style.css">
</head>
<body>
    <!-- Section principale -->
    <section class="hero">
        <div class="container">
            <!-- Formulaire d'inscription -->
            <form action="/GestionUser/Controller/FrontOffice/InscriptionController.php" method="POST">
                <div class="form-group">
                    <input type="text" name="nom" placeholder="Votre nom" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <button type="submit" class="hero-cta">S'inscrire</button>
            </form>
        </div>
    </section>
</body>
</html>