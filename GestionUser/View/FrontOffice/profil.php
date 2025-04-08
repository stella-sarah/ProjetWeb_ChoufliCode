<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/GestionUser/Assets/CSS/style.css">
<body>
    <section class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Mon Profil</h2>
            </div>
            <div class="contact-form">
                <form action="/ProfilController/modifierProfil" method="POST">
                    <div class="form-group">
                        <input type="text" name="nom" value="<?= $user->getNom() ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" value="<?= $user->getEmail() ?>" class="form-control">
                    </div>
                    <button type="submit" class="form-submit">Mettre à jour</button>
                </form>
                <div class="historique-reservations">
                    <h3>Historique des réservations</h3>
                    <!-- Boucle PHP pour afficher les réservations -->
                </div>
            </div>
        </div>
    </section>
</body>
</html>
