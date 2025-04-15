<?php
// Reservation-maisonhote.php - Form for booking a maison d'hôte

// Get maison type from URL parameters
$maisonType = isset($_GET['type']) ? $_GET['type'] : '';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'maison';

// Maison d'hôte details based on type
$maisonDetails = [
    'dar' => [
        'type' => 'Dar Traditionnel',
        'chambres' => '2',
        'surface' => '150',
        'prix_nuit' => '350',
        'capacite' => '4 personnes'
    ],
    'riad' => [
        'type' => 'Riad Luxueux',
        'chambres' => '3',
        'surface' => '220',
        'prix_nuit' => '550',
        'capacite' => '6 personnes'
    ],
    'villa' => [
        'type' => 'Villa d\'Hôtes',
        'chambres' => '4',
        'surface' => '300',
        'prix_nuit' => '750',
        'capacite' => '8 personnes'
    ]
];

// Default to Dar Traditionnel if type not specified or invalid
if (!array_key_exists($maisonType, $maisonDetails)) {
    $maisonType = 'dar';
}

$details = $maisonDetails[$maisonType];

// Handle form submission
$formSubmitted = false;
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form data here
    // In a real application, you would validate inputs and save to database
    
    // Simple validation
    if (empty($_POST['nom']) || empty($_POST['email']) || empty($_POST['telephone']) || empty($_POST['date_debut']) || empty($_POST['date_fin'])) {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $formSubmitted = true;
        
        // In a real application, you would:
        // 1. Sanitize inputs
        // 2. Save to database
        // 3. Perhaps send confirmation email
        // 4. Redirect to thank you page or display confirmation
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réservation de Maison d'Hôtes</title>
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

    <!-- Reservation Form Section -->
    <section class="contact" style="padding-top: 120px;">
        <div class="container contact-container">
            <?php if ($formSubmitted): ?>
                <!-- Success Message -->
                <div style="text-align: center; max-width: 800px; margin: 0 auto; padding: 50px 20px;">
                    <div style="font-size: 60px; color: var(--gold-primary); margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="section-title">Réservation <span>Confirmée</span></h2>
                    <p class="about-text" style="margin-bottom: 30px;">
                        Merci pour votre réservation du <?php echo htmlspecialchars($details['type']); ?>. 
                        Un membre de notre équipe vous contactera prochainement pour confirmer les détails de votre séjour.
                    </p>
                    <a href="index.php" class="hero-cta" style="display: inline-block;">Retour à l'Accueil</a>
                </div>
            <?php else: ?>
                <div class="contact-info">
                    <p class="section-subtitle">Formulaire de Réservation</p>
                    <h2 class="section-title">Réserver votre <span>Maison d'Hôtes</span></h2>
                    <p class="about-text">Remplissez le formulaire ci-dessous pour réserver votre séjour dans notre <?php echo htmlspecialchars($details['type']); ?>.</p>
                    
                    <div class="villa-summary" style="margin-top: 30px; background: rgba(201, 168, 108, 0.1); padding: 25px; border-radius: 5px; border: 1px solid rgba(201, 168, 108, 0.3);">
                        <h3 style="color: var(--gold-primary); margin-bottom: 15px; font-size: 20px;">Détails de la Maison d'Hôtes</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><strong>Type</strong></td>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><?php echo htmlspecialchars($details['type']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><strong>Nombre de Chambres</strong></td>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><?php echo htmlspecialchars($details['chambres']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><strong>Surface</strong></td>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><?php echo htmlspecialchars($details['surface']); ?> m²</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><strong>Capacité</strong></td>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><?php echo htmlspecialchars($details['capacite']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Prix par nuit</strong></td>
                                <td style="padding: 10px 0; color: var(--gold-primary); font-weight: bold;"><?php echo htmlspecialchars($details['prix_nuit']); ?> DT</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="contact-social" style="margin-top: 40px;">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="contact-form">
                    <?php if (!empty($errorMessage)): ?>
                        <div style="background-color: rgba(255, 99, 132, 0.1); border: 1px solid rgba(255, 99, 132, 0.3); color: #ff6384; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?type=' . htmlspecialchars($maisonType); ?>" method="post">
                        <div class="form-group">
                            <input type="text" name="nom" class="form-control" placeholder="Votre Nom Complet *" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Votre Email *" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="telephone" class="form-control" placeholder="Votre Téléphone *" required>
                        </div>
                        
                        <!-- Hidden fields to store maison information -->
                        <input type="hidden" name="maison_type" value="<?php echo htmlspecialchars($details['type']); ?>">
                        <input type="hidden" name="type_logement" value="maison">
                        
                        <div class="form-group">
                            <label for="date_debut" style="display: block; margin-bottom: 8px; color: rgba(248, 245, 235, 0.7);">Date d'arrivée *</label>
                            <input type="date" id="date_debut" name="date_debut" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin" style="display: block; margin-bottom: 8px; color: rgba(248, 245, 235, 0.7);">Date de départ *</label>
                            <input type="date" id="date_fin" name="date_fin" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <select name="nb_personnes" class="form-control" required>
                                <option value="" disabled selected>Nombre de personnes *</option>
                                <?php
                                $max_capacite = intval($details['capacite']);
                                for ($i = 1; $i <= $max_capacite; $i++) {
                                    echo "<option value=\"$i\">$i personne" . ($i > 1 ? "s" : "") . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" class="form-control" placeholder="Demandes spéciales (Optionnel)"></textarea>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 30px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="privacy" name="privacy" required style="width: auto; margin: 0;">
                                <label for="privacy" style="margin: 0; font-size: 14px; color: rgba(248, 245, 235, 0.7);">
                                    J'accepte les <a href="#" style="color: var(--gold-primary);">conditions générales</a> et la <a href="#" style="color: var(--gold-primary);">politique de confidentialité</a> *
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="form-submit animated-word">Confirmer la Réservation</button>
                    </form>
                </div>
            <?php endif; ?>
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

    <!-- JavaScript -->
    <script>
        // Scroll Header Effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Validate dates
        document.addEventListener('DOMContentLoaded', function() {
            const dateDebut = document.getElementById('date_debut');
            const dateFin = document.getElementById('date_fin');
            
            if (dateDebut && dateFin) {
                dateDebut.addEventListener('change', function() {
                    // Ensure the end date is at least the day after the start date
                    const nextDay = new Date(dateDebut.value);
                    nextDay.setDate(nextDay.getDate() + 1);
                    
                    const formattedDate = nextDay.toISOString().split('T')[0];
                    dateFin.min = formattedDate;
                    
                    // If the current end date is before the new start date, update it
                    if (dateFin.value && new Date(dateFin.value) <= new Date(dateDebut.value)) {
                        dateFin.value = formattedDate;
                    }
                });
            }
        });
    </script>
</body>
</html>