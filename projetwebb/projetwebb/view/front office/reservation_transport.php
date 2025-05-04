<?php
require_once '../../config.php';
require_once '../../model/reservation_transport.php';
require_once '../../controller/reservation_transport_controller.php';

$controller = new ReservationTransportController();

// Vérifier si un ID de transport est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erreur : Aucun transport sélectionné.");
}

$transport_id = (int)$_GET['id'];

// Récupérer les informations du transport
require_once '../../controller/transportcontroller.php';
$transportController = new TransportController();
$transport = $transportController->getTransportById($transport_id);

if (!$transport) {
    die("Erreur : Transport non trouvé.");
}

// Liste des 24 gouvernorats de la Tunisie
$gouvernorats = [
    "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
    "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
    "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_moyen = $transport_id;
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $depart = htmlspecialchars($_POST['depart']);
    $destination = htmlspecialchars($_POST['destination']);
    $paiement = htmlspecialchars($_POST['paiement']);
    $cin = (int)$_POST['cin'];
    $email = htmlspecialchars($_POST['email']);
    $datedebut = $_POST['datedebut'];
    $datefin = $_POST['datefin'];

    // Validation simple
    if (empty($nom) || empty($prenom) || empty($depart) || empty($destination) || empty($paiement) || empty($cin) || empty($email) || empty($datedebut) || empty($datefin)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (!in_array($depart, $gouvernorats) || !in_array($destination, $gouvernorats)) {
        $error = "Gouvernorat de départ ou de destination invalide.";
    } else {
        try {
            // Vérifier le stock avant d'ajouter la réservation
            if (!$controller->checkStock($id_moyen)) {
                $error = "Désolé, ce moyen de transport n'est plus disponible (stock épuisé).";
            } else {
                // Ajouter la réservation via le contrôleur
                $result = $controller->ajouterReservation($id_moyen, $nom, $prenom, $depart, $destination, $paiement, $cin, $email, $datedebut, $datefin);
                if ($result) {
                    // Décrémenter le stock après une réservation réussie
                    $stockUpdated = $transportController->decrementStock($id_moyen);
                    if ($stockUpdated) {
                        $success = "Réservation effectuée avec succès ! Le stock a été mis à jour.";
                    } else {
                        $error = "Réservation effectuée, mais erreur lors de la mise à jour du stock.";
                    }
                } else {
                    $error = "Erreur lors de l'enregistrement de la réservation.";
                }
            }
        } catch (Exception $e) {
            $error = "Erreur lors de la réservation: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation de Transport - TuniFy Village</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../../styleres.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header / Navigation -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="../../image/tunify.png" alt="tunify logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="pagetransport.php">Transport</a></li>
                    <li><a href="#gallery">Hebergement</a></li>
                    <li><a href="#features">Restauration</a></li>
                    <li><a href="#contact">Reclamations</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Book a Tour</button>
        </div>
    </header>

    <!-- Formulaire de réservation -->
    <section class="reservation-section">
        <div class="container reservation-container">
            <div class="reservation-header">
                <p class="section-subtitle">Réservation</p>
                <h2 class="section-title">Réserver le transport : <span class="animated-word"><?= htmlspecialchars($transport['nom']) ?></span></h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="reservation_transport.php?id=<?= $transport_id ?>" method="POST" class="reservation-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="depart">Lieu de départ</label>
                        <select id="depart" name="depart" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($gouvernorats as $gouvernorat): ?>
                                <option value="<?= htmlspecialchars($gouvernorat) ?>"><?= htmlspecialchars($gouvernorat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="destination">Destination</label>
                        <select id="destination" name="destination" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($gouvernorats as $gouvernorat): ?>
                                <option value="<?= htmlspecialchars($gouvernorat) ?>"><?= htmlspecialchars($gouvernorat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="paiement">Mode de paiement</label>
                        <select id="paiement" name="paiement" required>
                            <option value="">Sélectionner...</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cin">CIN</label>
                        <input type="number" id="cin" name="cin" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="datedebut">Date de début</label>
                        <input type="date" id="datedebut" name="datedebut" required>
                    </div>
                    <div class="form-group">
                        <label for="datefin">Date de fin</label>
                        <input type="date" id="datefin" name="datefin" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-check"></i> Confirmer la réservation
                    </button>
                    <a href="pagetransport.php" class="cancel-btn">Annuler</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-about">
                    <div class="logo">
                        <img src="../../image/tunify.png" alt="Jullanar Logo">
                        <div class="logo-text">
                            <h1>TuniFy</h1>
                            <p>Village</p>
                        </div>
                    </div>
                    <p>TuniFy Village is the epitome of luxury living, offering an exclusive residential experience with premium amenities and exceptional service in a stunning setting.</p>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#gallery">Properties</a></li>
                        <li><a href="#features">Amenities</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Our Services</h3>
                    <ul>
                        <li><a href="#">Property Sales</a></li>
                        <li><a href="#">Property Management</a></li>
                        <li><a href="#">Interior Design</a></li>
                        <li><a href="#">Landscaping</a></li>
                        <li><a href="#">Concierge Services</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3 class="footer-heading">Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Luxury Boulevard, Golden District, City</p>
                    <p><i class="fas fa-phone"></i> +123 456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@TuniFyvillage.com</p>
                    <p><i class="fas fa-clock"></i> Mon-Sat: 9:00 AM - 6:00 PM</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    © 2025 TuniFy Village. All Rights Reserved. Designed by <a href="#">Kaptin</a>
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