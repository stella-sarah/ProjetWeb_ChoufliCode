<?php
// visitevilla.php - Page de réservation d'une visite de villa

// Inclure les connexions et classes nécessaires
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Visite.php';

// Initialiser la session
session_start();

// Initialiser la connexion à la base de données
$db = config::getConnexion();

// Initialiser l'objet Visite
$visite = new Visite($db);

// Variables pour les messages
$message = '';
$error = '';

// Récupérer les informations de la villa
$type_villa = isset($_GET['type']) ? $_GET['type'] : '';
$nom_villa = isset($_GET['nom']) ? $_GET['nom'] : '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id_cin = htmlspecialchars(strip_tags($_POST['id_cin']));
    $nom_complet = htmlspecialchars(strip_tags($_POST['nom_complet']));
    $date_visite = htmlspecialchars(strip_tags($_POST['date_visite']));
    $heure_visite = htmlspecialchars(strip_tags($_POST['heure_visite']));
    
    // Récupérer les paramètres GET qui pourraient être perdus lors de la soumission du formulaire
    $type_villa = isset($_GET['type']) ? $_GET['type'] : '';
    $nom_villa = isset($_GET['nom']) ? $_GET['nom'] : '';
    
    // Validation des données
    if (empty($id_cin) || empty($nom_complet) || empty($date_visite) || empty($heure_visite)) {
        $error = "Tous les champs sont obligatoires";
    } else {
        // Définir les valeurs de l'objet visite
        $visite->setCin($id_cin);
        $visite->setNomComplet($nom_complet);
        $visite->setDateVisite($date_visite);
        $visite->setHeureVisite($heure_visite);
        
        // Ajouter la visite dans la base de données
        if ($visite->ajouterVisite()) {
            $message = "Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmer le rendez-vous.";
            
            // Rediriger vers la page de confirmation après 3 secondes
            header("refresh:3;url=reservation.php");
        } else {
            $error = "Une erreur s'est produite lors de l'enregistrement de votre demande.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réserver une Visite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header / Navigation -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php#home">Accueil</a></li>
                    <li><a href="index.php#about">À Propos</a></li>
                    <li><a href="index.php#gallery">Galerie</a></li>
                    <li><a href="index.php#features">Services</a></li>
                    <li><a href="reservation.php" class="active">Réservation</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="contact-btn">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="contact-btn">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <section class="contact-section" style="padding-top: 120px; padding-bottom: 80px;">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h2>Réserver une Visite</h2>
                    <p>Complétez le formulaire ci-dessous pour planifier une visite de la propriété</p>
                </div>
                
                <?php if(!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <div class="villa-info">
                    <h3>Propriété sélectionnée</h3>
                    <p><strong>Type:</strong> Villa <?php echo htmlspecialchars($type_villa ?? ''); ?></p>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($nom_villa ?? ''); ?></p>
                </div>
                
                <?php if(empty($message)): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?type=<?php echo urlencode($type_villa); ?>&nom=<?php echo urlencode($nom_villa); ?>" method="POST">
                    <!-- Champs cachés pour préserver les paramètres de l'URL -->
                    <input type="hidden" name="type_villa" value="<?php echo htmlspecialchars($type_villa); ?>">
                    <input type="hidden" name="nom_villa" value="<?php echo htmlspecialchars($nom_villa); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_cin">Numéro CIN</label>
                            <input type="text" id="id_cin" name="id_cin" class="form-control" placeholder="Entrez votre numéro CIN" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom_complet">Nom Complet</label>
                            <input type="text" id="nom_complet" name="nom_complet" class="form-control" placeholder="Entrez votre nom complet" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_visite">Date de Visite Souhaitée</label>
                            <input type="date" id="date_visite" name="date_visite" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="heure_visite">Heure de Visite Souhaitée</label>
                            <select id="heure_visite" name="heure_visite" class="form-control" required>
                                <option value="">Sélectionnez une heure</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="contact-btn">Réserver ma visite</button>
                    </div>
                </form>
                <?php else: ?>
                <div class="form-group">
                    <a href="reservation.php" class="contact-btn" style="display: block; text-align: center; text-decoration: none;">
                        Retour aux propriétés
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-about">
                    <div class="logo">
                        <img src="logo.png" alt="TuniFy Logo">
                        <div class="logo-text">
                            <h1>TuniFy</h1>
                            <p>Village</p>
                        </div>
                    </div>
                    <p>TuniFy Village est l'incarnation du luxe, offrant une expérience résidentielle exclusive avec des équipements haut de gamme et un service exceptionnel dans un cadre magnifique.</p>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Liens Rapides</h3>
                    <ul>
                        <li><a href="index.php#home">Accueil</a></li>
                        <li><a href="index.php#about">À Propos</a></li>
                        <li><a href="index.php#gallery">Propriétés</a></li>
                        <li><a href="index.php#features">Services</a></li>
                        <li><a href="reservation.php">Réservation</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Nos Services</h3>
                    <ul>
                        <li><a href="#">Vente Immobilière</a></li>
                        <li><a href="#">Gestion de Propriété</a></li>
                        <li><a href="#">Design d'Intérieur</a></li>
                        <li><a href="#">Aménagement Paysager</a></li>
                        <li><a href="#">Services de Conciergerie</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3 class="footer-heading">Informations de Contact</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Boulevard du Luxe, Quartier Doré, Ville</p>
                    <p><i class="fas fa-phone"></i> +216 12 345 678</p>
                    <p><i class="fas fa-envelope"></i> info@tunifyvillage.com</p>
                    <p><i class="fas fa-clock"></i> Lun-Sam: 9:00 - 18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; 2025 TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Validation supplémentaire du formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const cinInput = document.getElementById('id_cin');
            
            form.addEventListener('submit', function(e) {
                // Valider le format du CIN (8 chiffres pour la Tunisie)
                if (!/^\d{8}$/.test(cinInput.value)) {
                    e.preventDefault();
                    alert('Le numéro CIN doit contenir exactement 8 chiffres.');
                    cinInput.focus();
                }
            });
        });
    </script>
</body>
</html>