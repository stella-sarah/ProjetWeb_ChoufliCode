<?php
require_once __DIR__ . '/config.php';
$pdo = Config::getConnexion();

// Initialize variables for form feedback
$reservation_message = '';
$inscription_message = '';
$contact_message = '';

// Fetch events from the database
try {
    $sql = "SELECT id, title, event_date, venue, max_capacity, description, image_url FROM events ORDER BY event_date ASC";
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    echo "Erreur lors de la récupération des événements : " . $e->getMessage();
}

// Handle Reservation Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_submit'])) {
    $event_id = filter_input(INPUT_POST, 'evenement', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);

    if ($event_id && $name && filter_var($email, FILTER_VALIDATE_EMAIL) && $phone) {
        try {
            $pdo = Config::getConnexion();

            // Check if the user has already reserved this event
            $sql = "SELECT COUNT(*) FROM reservations WHERE event_id = :event_id AND email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':event_id' => $event_id, ':email' => $email]);
            $reservation_count = $stmt->fetchColumn();

            if ($reservation_count > 0) {
                $sql = "SELECT title FROM events WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $event_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                $event_label = $event ? $event['title'] : $event_id;
                $reservation_message = "Erreur : Vous avez déjà réservé pour $event_label.";
            } else {
                // Insert the reservation
                $sql = "INSERT INTO reservations (event_id, name, email, phone)
                        VALUES (:event_id, :name, :email, :phone)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':event_id' => $event_id,
                    ':name'     => $name,
                    ':email'    => $email,
                    ':phone'    => $phone
                ]);

                // Fetch event title for success message
                $sql = "SELECT title FROM events WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $event_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                $event_label = $event ? $event['title'] : $event_id;
                $reservation_message = "Réservation pour $event_label effectuée avec succès !";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $sql = "SELECT title FROM events WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $event_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                $event_label = $event ? $event['title'] : $event_id;
                $reservation_message = "Erreur : Vous avez déjà réservé pour $event_label.";
            } else {
                $reservation_message = "Erreur lors de la réservation : " . $e->getMessage();
            }
        }
    } else {
        $reservation_message = "Erreur : Veuillez remplir tous les champs correctement.";
    }
}

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if ($nom && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        $contact_message = "Message envoyé avec succès !";
    } else {
        $contact_message = "Erreur : Veuillez remplir tous les champs correctement.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Events - Gestion d'Événements</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .gallery-item .emoji {
            font-size: 6rem;
            display: block;
            text-align: center;
            padding: 20px;
        }
        .form-submit-container {
            text-align: center;
            margin-top: 20px;
        }
        .book-now-btn {
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .book-now-btn:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        /* Validation Styles */
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        .form-group.invalid .form-control {
            border: 1px solid red;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            display: none;
            margin-top: 5px;
        }
        .gallery-item-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
            align-items: center;
        }
        .gallery-item-buttons .cta-primary {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            text-align: center;
        }
        .like-dislike-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center;
        }
        .like-btn, .dislike-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .like-btn {
            color: var(--gold-primary);
        }
        .dislike-btn {
            color: var(--gold-primary);
        }
        .like-btn:hover, .dislike-btn:hover {
            transform: scale(1.1);
            color: var(--gold-light);
        }
        .like-btn.active {
            color: var(--gold-light);
        }
        .dislike-btn.active {
            color: var(--gold-light);
        }
        .like-count, .dislike-count {
            font-size: 0.9rem;
            color: var(--light-text);
        }
        .cta-secondary {
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #007bff;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .cta-secondary:hover {
            background-color: #0056b3;
        }
        #locationModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .location-popup-content {
            background-color: black;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        #map {
            width: 100%;
            height: 400px;
            border-radius: 5px;
            margin-top: 15px;
            z-index: 1;
        }
        .close-map {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
            background: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
        }
        .transport-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .event-image-container {
            width: 100%;
            max-height: 300px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(201, 168, 108, 0.2);
        }
        .event-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .event-info {
            padding: 20px;
            background-color: var(--dark-bg);
            border-radius: 8px;
            border: 1px solid rgba(201, 168, 108, 0.2);
        }
        .event-info h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--gold-primary);
            font-family: 'Cinzel', serif;
        }
        .event-info p {
            margin-bottom: 10px;
            line-height: 1.6;
            color: var(--light-text);
        }
        .event-info strong {
            color: var(--gold-light);
        }
        .location-popup-content h2 {
            color: var(--gold-primary);
            font-family: 'Cinzel', serif;
            margin-bottom: 20px;
        }
        .favorites-icon {
            color: var(--gold-primary);
            font-size: 1.2rem;
            transition: color 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }
        .favorites-icon:hover {
            color: var(--gold-light);
        }
        .favorites-icon.active {
            color: #ff4444;
        }
        .favorites-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff4444;
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .favorites-section {
            display: none;
            padding: 20px;
        }
        .favorites-section.active {
            display: block;
        }
        .no-favorites {
            text-align: center;
            padding: 40px;
            color: var(--light-text);
        }
        .back-to-all {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--gold-primary);
            cursor: pointer;
            text-decoration: none;
        }
        .back-to-all:hover {
            color: var(--gold-light);
        }
        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: none;
            border: none;
            color: var(--gold-primary);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .favorite-btn:hover {
            color: var(--gold-light);
            transform: scale(1.1);
        }
        .favorite-btn.active {
            color: #ff4444;
        }
        .gallery-item {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>ÉVÉNEMENTS</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#accueil">Accueil</a></li>
                    <li><a href="#evenements">Événements</a></li>
                    <li><a href="#reservations">Réservations</a></li>
                    <li><a href="#inscriptions">Inscriptions</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <a href="#" class="favorites-icon" onclick="showFavorites(event)">
                    <i class="fas fa-heart"></i>
                    <span class="favorites-count">0</span>
                </a>
                <button class="contact-btn" onclick="openReservationModal()">Réserver</button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="accueil" class="hero">
        <div class="hero-content">
            <h3 class="hero-subtitle">Vivez l'Exceptionnel</h3>
            <h1 class="hero-title">Découvrez des <span>Événements</span> Uniques</h1>
            <p class="hero-description">Participez aux plus grands événements culturels, sportifs et artistiques de Tunisie.</p>
            <a href="#evenements" class="hero-cta">Découvrir les événements</a>
        </div>
    </section>

    <!-- Events Section (Gallery) -->
    <section id="evenements" class="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <h3 class="section-subtitle">Nos Événements</h3>
                <h2 class="section-title">Explorez Nos <span>Événements</span></h2>
            </div>
            <div class="gallery-grid">
                <?php if (empty($events)): ?>
                    <p>Aucun événement disponible pour le moment.</p>
                <?php else: ?>
                    <?php 
                    $emojis = ['🥁', '🎤', '⚽', '🎨', '🏃'];
                    $emoji_index = 0;
                    foreach ($events as $event): 
                        $event_date = date('d F Y', strtotime($event['event_date']));
                    ?>
                        <div class="gallery-item">
                            <button class="favorite-btn" onclick="toggleFavorite('<?= htmlspecialchars($event['id']) ?>')">
                                <i class="fas fa-heart"></i>
                            </button>
                            <span class="emoji"><?= $emojis[$emoji_index % count($emojis)] ?></span>
                            <div class="gallery-item-content">
                                <h3 class="gallery-item-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <p class="gallery-item-subtitle"><?= htmlspecialchars($event['venue']) ?>, <?= $event_date ?></p>
                                <div class="gallery-item-buttons">
                                    <button class="cta-primary" onclick="openReservationModal('<?= htmlspecialchars($event['id']) ?>')">Réserver</button>
                                    <button class="cta-primary" onclick="openDetailsModal('<?= htmlspecialchars($event['id']) ?>', '<?= htmlspecialchars($event['title']) ?>', '<?= htmlspecialchars($event['venue']) ?>', '<?= htmlspecialchars($event['event_date']) ?>', '<?= htmlspecialchars($event['max_capacity']) ?>', '<?= htmlspecialchars($event['description']) ?>', '<?= htmlspecialchars($event['image_url']) ?>')">Détails</button>
                                </div>
                                <div class="like-dislike-buttons">
                                    <button class="like-btn" onclick="toggleLike('<?= htmlspecialchars($event['id']) ?>')">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="like-count">0</span>
                                    </button>
                                    <button class="dislike-btn" onclick="toggleDislike('<?= htmlspecialchars($event['id']) ?>')">
                                        <i class="fas fa-thumbs-down"></i>
                                        <span class="dislike-count">0</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $emoji_index++;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Reservations Section -->
    <section id="reservations" class="cta">
        <div class="container cta-container">
            <?php if ($reservation_message): ?>
                <div class="alert <?= strpos($reservation_message, 'Erreur') === false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($reservation_message) ?>
                </div>
            <?php endif; ?>
            <h2 class="cta-title">Réservez Votre Place Aujourd'hui</h2>
            <p class="cta-text">Ne manquez pas l'opportunité de vivre des moments inoubliables avec TuniFy Events.</p>
            <div class="cta-buttons">
                <button class="cta-primary" onclick="openReservationModal()">Réserver Maintenant</button>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container contact-container">
            <div class="contact-info">
                <h3 class="section-subtitle">Nous Contacter</h3>
                <h2 class="section-title">Restez en <span>Contact</span></h2>
                <p>Nous sommes là pour répondre à toutes vos questions.</p>
                <div class="contact-details">
                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <div class="contact-item-content">
                            <h4>Email</h4>
                            <a href="mailto:contact@tunifyevents.com">contact@tunifyevents.com</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <div class="contact-item-content">
                            <h4>Téléphone</h4>
                            <p>+216 12 345 678</p>
                        </div>
                    </div>
                </div>
                <div class="contact-social">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="contact-form">
                <?php if ($contact_message): ?>
                    <div class="alert <?= strpos($contact_message, 'Erreur') === false ? 'alert-success' : 'alert-error' ?>">
                        <?= htmlspecialchars($contact_message) ?>
                    </div>
                <?php endif; ?>
                <form id="contactForm" method="POST" action="#contact" novalidate>
                    <div class="form-group">
                        <input type="text" class="form-control" id="nomContact" name="nom" placeholder="Nom" required>
                        <span class="error-message" id="nomContact-error"></span>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="emailContact" name="email" placeholder="Email" required>
                        <span class="error-message" id="emailContact-error"></span>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" id="messageContact" name="message" placeholder="Message" required></textarea>
                        <span class="error-message" id="messageContact-error"></span>
                    </div>
                    <input type="hidden" name="contact_submit" value="1">
                    <button type="submit" class="form-submit">Envoyer</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Reservation Modal -->
    <div id="reservationModal" class="transport-popup">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closeModal('reservationModal')">×</span>
            <h2>Réservation</h2>
            <form id="reservationForm" method="POST" action="#reservations" novalidate>
                <div class="form-group">
                    <select class="form-control" id="evenement" name="evenement" required>
                        <option value="">Sélectionnez un événement</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= htmlspecialchars($event['id']) ?>"><?= htmlspecialchars($event['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-message" id="evenement-error"></span>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" required>
                    <span class="error-message" id="nom-error"></span>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <span class="error-message" id="email-error"></span>
                </div>
                <div class="form-group">
                    <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="Téléphone" required>
                    <span class="error-message" id="telephone-error"></span>
                </div>
                <input type="hidden" name="reservation_submit" value="1">
                <div class="form-submit-container">
                    <button type="submit" class="book-now-btn">Réserver</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="transport-popup">
        <div class="location-popup-content">
            <span class="close-map" onclick="closeModal('locationModal')">×</span>
            <h2>Localisation de l'événement</h2>
            <div id="locationDetails"></div>
            <div id="map"></div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="transport-popup">
        <div class="location-popup-content">
            <span class="close-map" onclick="closeModal('detailsModal')">×</span>
            <h2>Détails de l'Événement</h2>
            <div id="eventDetails">
                <div class="event-image-container">
                    <img id="eventImage" src="" alt="Image de l'événement">
                </div>
                <div class="event-info">
                    <h3 id="eventTitle"></h3>
                    <p><strong>Lieu :</strong> <span id="eventVenue"></span></p>
                    <p><strong>Date :</strong> <span id="eventDate"></span></p>
                    <p><strong>Capacité :</strong> <span id="eventCapacity"></span> places</p>
                    <p><strong>Description :</strong> <span id="eventDescription"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorites Section -->
    <section id="favorites" class="favorites-section">
        <div class="container">
            <a class="back-to-all" onclick="showAllEvents()">
                <i class="fas fa-arrow-left"></i> Retour à tous les événements
            </a>
            <div class="gallery-header">
                <h3 class="section-subtitle">Mes Favoris</h3>
                <h2 class="section-title">Événements <span>Favoris</span></h2>
            </div>
            <div class="gallery-grid" id="favoritesGrid">
                <!-- Les événements favoris seront affichés ici -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-top">
            <div class="footer-about">
                <div class="logo">
                    <img src="../assets/images/logo.png" alt="TuniFy Logo">
                    <div class="logo-text">
                        <h1>TuniFy</h1>
                        <p>ÉVÉNEMENTS</p>
                    </div>
                </div>
                <p>Rejoignez-nous pour vivre des expériences événementielles uniques en Tunisie.</p>
            </div>
            <div class="footer-links">
                <h3 class="footer-heading">Liens Rapides</h3>
                <ul>
                    <li><a href="#accueil">Accueil</a></li>
                    <li><a href="#evenements">Événements</a></li>
                    <li><a href="#reservations">Réservations</a></li>
                    <li><a href="#inscriptions">Inscriptions</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3 class="footer-heading">Contact</h3>
                <p><i class="fas fa-envelope"></i> <a href="mailto:contact@tunifyevents.com">contact@tunifyevents.com</a></p>
                <p><i class="fas fa-phone"></i> +216 12 345 678</p>
            </div>
        </div>
        <div class="container footer-bottom">
            <p class="footer-copyright">© 2025 TuniFy Events. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        // Existing JavaScript
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 50);
        });

        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('nav li').forEach(li => li.classList.remove('active'));
                this.parentElement.classList.add('active');
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                targetSection.scrollIntoView({ behavior: 'smooth' });
            });
        });

        function openReservationModal(eventId) {
            const modal = document.getElementById('reservationModal');
            const select = document.getElementById('evenement');
            modal.style.display = 'flex';
            if (eventId) {
                select.value = eventId;
                select.disabled = true;
            } else {
                select.value = '';
                select.disabled = false;
            }
            // Reset form errors
            resetFormErrors('reservationForm');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hiding');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('hiding');
                if (modalId === 'reservationModal') {
                    document.getElementById('reservationForm').reset();
                    document.getElementById('evenement').disabled = false;
                    resetFormErrors('reservationForm');
                } else if (modalId === 'locationModal' && map) {
                    map.remove();
                    map = null;
                }
            }, 200);
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('transport-popup')) {
                closeModal(event.target.id);
            }
        };

        // Form Validation
        const validationRules = {
            evenement: {
                validate: (value) => {
                    if (!value) return 'Veuillez sélectionner un événement.';
                    return '';
                }
            },
            nom: {
                validate: (value) => {
                    if (!value) return 'Le nom est requis.';
                    if (value.length < 3) return 'Le nom doit contenir au moins 3 caractères.';
                    if (value.length > 255) return 'Le nom ne peut pas dépasser 255 caractères.';
                    if (!/^[a-zA-Z0-9\s,.-]+$/.test(value)) return 'Le nom ne peut contenir que des lettres, chiffres, espaces, virgules, points ou tirets.';
                    return '';
                }
            },
            email: {
                validate: (value) => {
                    if (!value) return 'L\'email est requis.';
                    if (value.length > 255) return 'L\'email ne peut pas dépasser 255 caractères.';
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Veuillez entrer un email valide.';
                    return '';
                }
            },
            telephone: {
                validate: (value) => {
                    if (!value) return 'Le numéro de téléphone est requis.';
                    if (value.length < 8 || value.length > 15) return 'Le numéro de téléphone doit contenir entre 8 et 15 caractères.';
                    if (!/^\+?[0-9\s-]+$/.test(value)) return 'Le numéro de téléphone ne peut contenir que des chiffres, espaces, tirets ou un + initial.';
                    return '';
                }
            },
            message: {
                validate: (value) => {
                    if (!value) return 'Le message est requis.';
                    if (value.length > 1000) return 'Le message ne peut pas dépasser 1000 caractères.';
                    if (/<[^>]+>/.test(value)) return 'Le message ne peut pas contenir de balises HTML.';
                    return '';
                }
            }
        };

        function validateField(fieldId, value) {
            const rule = validationRules[fieldId];
            if (!rule) return '';
            return rule.validate(value);
        }

        function updateErrorMessage(fieldId, message) {
            const errorSpan = document.getElementById(`${fieldId}-error`);
            const formGroup = errorSpan.closest('.form-group');
            if (message) {
                errorSpan.textContent = message;
                errorSpan.style.display = 'block';
                formGroup.classList.add('invalid');
            } else {
                errorSpan.textContent = '';
                errorSpan.style.display = 'none';
                formGroup.classList.remove('invalid');
            }
        }

        function resetFormErrors(formId) {
            const form = document.getElementById(formId);
            form.querySelectorAll('.error-message').forEach(span => {
                span.textContent = '';
                span.style.display = 'none';
            });
            form.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('invalid');
            });
        }

        function setupFormValidation(formId, fields) {
            const form = document.getElementById(formId);
            fields.forEach(field => {
                const input = document.getElementById(field.id);
                input.addEventListener('input', () => {
                    const message = validateField(field.id, input.value);
                    updateErrorMessage(field.id, message);
                });
            });

            form.addEventListener('submit', (e) => {
                let hasErrors = false;
                fields.forEach(field => {
                    const input = document.getElementById(field.id);
                    const message = validateField(field.id, input.value);
                    updateErrorMessage(field.id, message);
                    if (message) hasErrors = true;
                });
                if (hasErrors) {
                    e.preventDefault();
                }
            });
        }

        // Setup validation for each form
        setupFormValidation('reservationForm', [
            { id: 'evenement' },
            { id: 'nom' },
            { id: 'email' },
            { id: 'telephone' }
        ]);

        setupFormValidation('contactForm', [
            { id: 'nomContact', mapTo: 'nom' },
            { id: 'emailContact', mapTo: 'email' },
            { id: 'messageContact', mapTo: 'message' }
        ]);

        let map;
        let marker;

        function openLocationModal(venue) {
            const modal = document.getElementById('locationModal');
            const locationDetails = document.getElementById('locationDetails');
            locationDetails.textContent = `Lieu : ${venue}`;
            modal.style.display = 'flex';
            
            // Initialize map
            if (map) {
                map.remove();
            }
            
            // Create new map
            map = L.map('map').setView([36.8065, 10.1815], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add geocoder control
            L.Control.geocoder({
                defaultMarkGeocode: false,
                position: 'topleft',
                placeholder: 'Rechercher un lieu...',
                errorMessage: 'Aucun résultat trouvé.',
                showResultIcons: true,
                collapsed: true
            })
            .on('markgeocode', function(e) {
                const latlng = e.geocode.center;
                map.setView(latlng, 15);
                
                if (marker) {
                    map.removeLayer(marker);
                }
                
                marker = L.marker(latlng).addTo(map)
                    .bindPopup(e.geocode.name)
                    .openPopup();
            })
            .addTo(map);

            // Force map resize
            setTimeout(() => {
                map.invalidateSize();
            }, 100);

            // Geocode the venue address
            const geocoder = L.Control.Geocoder.nominatim();
            geocoder.geocode(venue, function(results) {
                if (results && results.length > 0) {
                    const latlng = results[0].center;
                    map.setView(latlng, 15);
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker(latlng).addTo(map)
                        .bindPopup(venue)
                        .openPopup();
                } else {
                    locationDetails.innerHTML += '<br><span style="color: red;">Impossible de trouver l\'emplacement exact</span>';
                }
            });
        }

        function openDetailsModal(id, title, venue, eventDate, maxCapacity, description, imageUrl) {
            const modal = document.getElementById('detailsModal');
            modal.style.display = 'flex';
            
            // Format the date
            const date = new Date(eventDate);
            const formattedDate = date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Update modal content
            document.getElementById('eventTitle').textContent = title;
            document.getElementById('eventVenue').textContent = venue;
            document.getElementById('eventDate').textContent = formattedDate;
            document.getElementById('eventCapacity').textContent = maxCapacity;
            document.getElementById('eventDescription').textContent = description || 'Aucune description disponible';
            
            // Set image if available
            const eventImage = document.getElementById('eventImage');
            if (imageUrl) {
                eventImage.src = imageUrl;
                eventImage.style.display = 'block';
            } else {
                eventImage.style.display = 'none';
            }
        }

        function toggleFavorite(eventId) {
            const btn = event.target.closest('.favorite-btn');
            btn.classList.toggle('active');
            
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            if (btn.classList.contains('active')) {
                if (!favorites.includes(eventId)) {
                    favorites.push(eventId);
                }
            } else {
                const index = favorites.indexOf(eventId);
                if (index > -1) {
                    favorites.splice(index, 1);
                }
            }
            localStorage.setItem('favorites', JSON.stringify(favorites));
            
            // Mettre à jour l'icône dans la barre de navigation
            updateNavFavoritesIcon();
        }

        function updateNavFavoritesIcon() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const navIcon = document.querySelector('.nav-buttons .favorites-icon');
            const favoritesCount = document.querySelector('.favorites-count');
            
            if (favorites.length > 0) {
                navIcon.classList.add('active');
                favoritesCount.textContent = favorites.length;
                favoritesCount.style.display = 'flex';
            } else {
                navIcon.classList.remove('active');
                favoritesCount.style.display = 'none';
            }
        }

        function showFavorites(event) {
            event.preventDefault();
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const favoritesGrid = document.getElementById('favoritesGrid');
            const favoritesSection = document.getElementById('favorites');
            
            // Cacher toutes les sections sauf le header et le footer
            document.querySelectorAll('section:not(#favorites)').forEach(section => {
                section.style.display = 'none';
            });
            
            // Afficher la section des favoris
            favoritesSection.classList.add('active');
            favoritesSection.style.display = 'block';
            
            // Vider la grille des favoris
            favoritesGrid.innerHTML = '';
            
            if (favorites.length === 0) {
                favoritesGrid.innerHTML = '<div class="no-favorites">Vous n\'avez pas encore d\'événements favoris</div>';
                return;
            }
            
            // Récupérer tous les événements
            const allEvents = <?= json_encode($events) ?>;
            
            // Filtrer et afficher uniquement les événements favoris
            favorites.forEach(favoriteId => {
                const event = allEvents.find(e => e.id === favoriteId);
                if (event) {
                    const eventDate = new Date(event.event_date).toLocaleDateString('fr-FR', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    
                    const eventHtml = `
                        <div class="gallery-item">
                            <button class="favorite-btn active" onclick="toggleFavorite('${event.id}')">
                                <i class="fas fa-heart"></i>
                            </button>
                            <span class="emoji">${getRandomEmoji()}</span>
                            <div class="gallery-item-content">
                                <h3 class="gallery-item-title">${event.title}</h3>
                                <p class="gallery-item-subtitle">${event.venue}, ${eventDate}</p>
                                <div class="gallery-item-buttons">
                                    <button class="cta-primary" onclick="openReservationModal('${event.id}')">Réserver</button>
                                    <button class="cta-primary" onclick="openDetailsModal('${event.id}', '${event.title}', '${event.venue}', '${event.event_date}', '${event.max_capacity}', '${event.description}', '${event.image_url}')">Détails</button>
                                </div>
                            </div>
                        </div>
                    `;
                    favoritesGrid.innerHTML += eventHtml;
                }
            });
        }

        function showAllEvents() {
            // Afficher toutes les sections
            document.querySelectorAll('section').forEach(section => {
                section.style.display = 'block';
            });
            
            // Cacher la section des favoris
            const favoritesSection = document.getElementById('favorites');
            favoritesSection.classList.remove('active');
        }

        function getRandomEmoji() {
            const emojis = ['🥁', '🎤', '⚽', '🎨', '🏃'];
            return emojis[Math.floor(Math.random() * emojis.length)];
        }

        // Charger l'état des favoris au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            favorites.forEach(eventId => {
                const btn = document.querySelector(`.favorite-btn[onclick="toggleFavorite('${eventId}')"]`);
                if (btn) {
                    btn.classList.add('active');
                }
            });
            updateNavFavoritesIcon();
        });

        function toggleLike(eventId) {
            const btn = event.target.closest('.like-btn');
            const countSpan = btn.querySelector('.like-count');
            const dislikeBtn = btn.parentElement.querySelector('.dislike-btn');
            
            // Si le bouton dislike est actif, le désactiver
            if (dislikeBtn.classList.contains('active')) {
                dislikeBtn.classList.remove('active');
                const dislikeCount = parseInt(dislikeBtn.querySelector('.dislike-count').textContent);
                dislikeBtn.querySelector('.dislike-count').textContent = dislikeCount - 1;
            }
            
            // Toggle du bouton like
            btn.classList.toggle('active');
            const currentCount = parseInt(countSpan.textContent);
            countSpan.textContent = btn.classList.contains('active') ? currentCount + 1 : currentCount - 1;
            
            // Sauvegarder dans le localStorage
            const likes = JSON.parse(localStorage.getItem('likes') || '{}');
            likes[eventId] = btn.classList.contains('active');
            localStorage.setItem('likes', JSON.stringify(likes));
        }
        
        function toggleDislike(eventId) {
            const btn = event.target.closest('.dislike-btn');
            const countSpan = btn.querySelector('.dislike-count');
            const likeBtn = btn.parentElement.querySelector('.like-btn');
            
            // Si le bouton like est actif, le désactiver
            if (likeBtn.classList.contains('active')) {
                likeBtn.classList.remove('active');
                const likeCount = parseInt(likeBtn.querySelector('.like-count').textContent);
                likeBtn.querySelector('.like-count').textContent = likeCount - 1;
            }
            
            // Toggle du bouton dislike
            btn.classList.toggle('active');
            const currentCount = parseInt(countSpan.textContent);
            countSpan.textContent = btn.classList.contains('active') ? currentCount + 1 : currentCount - 1;
            
            // Sauvegarder dans le localStorage
            const dislikes = JSON.parse(localStorage.getItem('dislikes') || '{}');
            dislikes[eventId] = btn.classList.contains('active');
            localStorage.setItem('dislikes', JSON.stringify(dislikes));
        }
        
        // Charger les likes/dislikes au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const likes = JSON.parse(localStorage.getItem('likes') || '{}');
            const dislikes = JSON.parse(localStorage.getItem('dislikes') || '{}');
            
            // Appliquer les états sauvegardés
            Object.keys(likes).forEach(eventId => {
                const likeBtn = document.querySelector(`.like-btn[onclick*="${eventId}"]`);
                if (likeBtn && likes[eventId]) {
                    likeBtn.classList.add('active');
                    const countSpan = likeBtn.querySelector('.like-count');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                }
            });
            
            Object.keys(dislikes).forEach(eventId => {
                const dislikeBtn = document.querySelector(`.dislike-btn[onclick*="${eventId}"]`);
                if (dislikeBtn && dislikes[eventId]) {
                    dislikeBtn.classList.add('active');
                    const countSpan = dislikeBtn.querySelector('.dislike-count');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                }
            });
        });
    </script>
</body>
</html>