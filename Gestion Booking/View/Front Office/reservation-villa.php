<?php
// Reservation-form.php - Form for booking a villa

// Get villa type and name from URL parameters
$villaType = isset($_GET['type']) ? $_GET['type'] : '';
$villaNom = isset($_GET['nom']) ? $_GET['nom'] : '';

// Villa details based on type
$villaDetails = [
    's3' => [
        'type' => 'S+3',
        'chambres' => '3',
        'surface' => '215',
        'prix' => '650,000'
    ],
    's4' => [
        'type' => 'S+4',
        'chambres' => '4',
        'surface' => '280',
        'prix' => '850,000'
    ],
    's5' => [
        'type' => 'S+5',
        'chambres' => '5',
        'surface' => '350',
        'prix' => '1,050,000'
    ]
];

// Default to S+3 if type not specified or invalid
if (!array_key_exists($villaType, $villaDetails)) {
    $villaType = 's3';
    $villaNom = 'KMAR';
}

$details = $villaDetails[$villaType];

// Handle form submission
$formSubmitted = false;
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form data here
    // In a real application, you would validate inputs and save to database
    
    // Simple validation
    if (empty($_POST['nom']) || empty($_POST['email']) || empty($_POST['telephone'])) {
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
    <title>TuniFy Village - Réservation de Villa</title>
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
            <button class="contact-btn">Réserver une Visite</button>
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
                        Merci pour votre réservation de la villa <?php echo htmlspecialchars($details['type'] . ' - ' . $villaNom); ?>. 
                        Un membre de notre équipe vous contactera prochainement pour confirmer la date de votre visite.
                    </p>
                    <a href="index.php" class="hero-cta" style="display: inline-block;">Retour à l'Accueil</a>
                </div>
            <?php else: ?>
                <div class="contact-info">
                    <p class="section-subtitle">Formulaire de Réservation</p>
                    <h2 class="section-title">Réserver votre <span>Villa</span></h2>
                    <p class="about-text">Remplissez le formulaire ci-dessous pour réserver une visite de la villa <?php echo htmlspecialchars($details['type'] . ' - ' . $villaNom); ?>.</p>
                    
                    <div class="villa-summary" style="margin-top: 30px; background: rgba(201, 168, 108, 0.1); padding: 25px; border-radius: 5px; border: 1px solid rgba(201, 168, 108, 0.3);">
                        <h3 style="color: var(--gold-primary); margin-bottom: 15px; font-size: 20px;">Détails de la Villa</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><strong>Modèle</strong></td>
                                <td style="padding: 10px 0; border-bottom: 1px solid rgba(201, 168, 108, 0.2);"><?php echo htmlspecialchars($details['type'] . ' - ' . $villaNom); ?></td>
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
                                <td style="padding: 10px 0;"><strong>Prix</strong></td>
                                <td style="padding: 10px 0; color: var(--gold-primary); font-weight: bold;"><?php echo htmlspecialchars($details['prix']); ?> DT</td>
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

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?type=' . htmlspecialchars($villaType) . '&nom=' . htmlspecialchars($villaNom); ?>" method="post">
                        <div class="form-group">
                            <input type="text" name="nom" class="form-control" placeholder="Votre Nom Complet *" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Votre Email *" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="telephone" class="form-control" placeholder="Votre Téléphone *" required>
                        </div>
                        
                        <!-- Hidden fields to store villa information -->
                        <input type="hidden" name="villa_type" value="<?php echo htmlspecialchars($details['type']); ?>">
                        <input type="hidden" name="villa_nom" value="<?php echo htmlspecialchars($villaNom); ?>">
                        
                        <div class="form-group">
                            <select name="date_visite" class="form-control" required>
                                <option value="" disabled selected>Date de Visite Souhaitée *</option>
                                <?php
                                // Generate date options for the next 14 days
                                $today = new DateTime();
                                for ($i = 1; $i <= 14; $i++) {
                                    $date = clone $today;
                                    $date->modify("+$i day");
                                    $formattedDate = $date->format('d/m/Y');
                                    echo "<option value=\"" . htmlspecialchars($formattedDate) . "\">" . htmlspecialchars($formattedDate) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <select name="heure_visite" class="form-control" required>
                                <option value="" disabled selected>Heure de Visite Souhaitée *</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" class="form-control" placeholder="Vos Remarques (Optionnel)"></textarea>
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
    </script>
</body>
</html>