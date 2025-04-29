<?php
    // Connexion à la base de données
    require_once '../../config.php';
    $pdo = config::getConnexion();

    // Récupérer les transports depuis la base de données
    try {
        $query = $pdo->query("SELECT * FROM transport");
        $transports = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur de base de données: " . $e->getMessage());
    }

    // Préparer les données pour le JavaScript
    $transportData = [];
    foreach ($transports as $transport) {
        $availability = $transport['stock'] > 0 ? 'Disponible' : 'Non disponible';
        $availabilityClass = $transport['stock'] > 0 ? 'available' : 'unavailable';

        $transportData[strtolower($transport['type'])] = [
            'id' => $transport['id'],
            'title' => strtoupper($transport['nom']),
            'type' => $transport['type'],
            'details' => [
                ['label' => 'Type', 'value' => $transport['type'], 'icon' => 'fas fa-info-circle'],
                ['label' => 'Top Speed', 'value' => $transport['vitesse'] . ' km/h', 'icon' => 'fas fa-tachometer-alt'],
                ['label' => 'Seats', 'value' => $transport['nb_places'], 'icon' => 'fas fa-users'],
                ['label' => 'Battery Range', 'value' => $transport['batterie'] . ' km', 'icon' => 'fas fa-battery-full'],
                ['label' => 'Availability', 'value' => $availability, 'icon' => 'fas fa-check-circle', 'class' => $availabilityClass]
            ],
            'prices' => [
                ['label' => 'Price Per Day', 'value' => $transport['prix'] . ' €', 'icon' => 'fas fa-calendar-day']
            ],
            'image' => $transport['image'],
            'availability' => $availability,
            'availabilityClass' => $availabilityClass
        ];
    }

    // Liste des 24 gouvernorats de la Tunisie pour le formulaire
    $gouvernorats = [
        "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
        "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
        "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
    ];

    // Valid payment methods
    $validPaiements = ['carte', 'especes', 'virement'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Luxury Living</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../../styleres.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Enable smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--primary-dark); /* #1a1a1a */
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-color); /* #d4af37 */
            border-radius: 5px;
            border: 2px solid var(--primary-dark);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-light); /* #e6c84f */
        }

        /* Apply scrollbar styles to modal content */
        .reservation-modal-content {
            scrollbar-width: thin;
            scrollbar-color: var(--accent-color) var(--primary-dark);
        }

        /* Style pour le badge de disponibilité */
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            z-index: 10;
        }
        .availability-badge.available {
            background-color: #28a745;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .availability-badge.unavailable {
            background-color: #dc3545;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .gallery-item {
            position: relative;
        }
        /* Reservation Modal Styles */
        .reservation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .reservation-modal-content {
            background: var(--gradient-dark);
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            padding: 30px;
            position: relative;
            border: 2px solid var(--accent-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(212, 175, 55, 0.2);
            z-index: 2;
            animation: zoomIn 0.5s ease forwards;
            opacity: 0;
            max-height: 90vh;
            overflow-y: auto;
        }
        .reservation-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .reservation-close {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: absolute;
            right: 15px;
            top: 15px;
        }
        .reservation-close:hover {
            color: var(--accent-color);
            transform: rotate(90deg);
        }
    </style>
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
                    <li><a href="#about">Transport</a></li>
                    <li><a href="#gallery">Hebergement</a></li>
                    <li><a href="#features">Restauration</a></li>
                    <li><a href="#contact">Reclamations</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Book a Tour</button>
        </div>
    </header>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Immersive Experience</p>
                <h2 class="section-title">Discover Our <span class="animated-word">Stunning</span> Transports</h2>
            </div>
            <div class="gallery-grid">
                <?php foreach ($transports as $transport): ?>
                <div class="gallery-item" onclick="showTransportDetails('<?= strtolower($transport['type']) ?>')">
                    <span class="availability-badge <?= htmlspecialchars($transportData[strtolower($transport['type'])]['availabilityClass']) ?>">
                        <?= htmlspecialchars($transportData[strtolower($transport['type'])]['availability']) ?>
                    </span>
                    <img src="../../image/<?= htmlspecialchars($transport['image']) ?>" alt="<?= htmlspecialchars($transport['nom']) ?>">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title"><?= htmlspecialchars($transport['nom']) ?></h3>
                        <p class="gallery-item-subtitle"><?= htmlspecialchars($transport['type']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Transport Popup -->
    <div class="transport-popup" id="transportPopup">
        <div class="popup-overlay" onclick="hideTransportDetails()"></div>
        <div class="transport-popup-content">
            <div class="popup-header">
                <h2 id="popupTransportTitle">Transport Title</h2>
                <button class="close-popup" onclick="hideTransportDetails()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <div class="detail-row">
                    <div class="detail-image">
                        <img id="popupTransportImage" src="" alt="Transport Image">
                    </div>
                    <div class="detail-info">
                        <div class="detail-section">
                            <h3 class="section-heading">Details</h3>
                            <ul class="transport-details-list" id="transportDetailsList">
                                <!-- Details will be inserted here by JavaScript -->
                            </ul>
                        </div>
                        <div class="detail-section">
                            <h3 class="section-heading">Pricing</h3>
                            <ul class="transport-prices-list" id="transportPricesList">
                                <!-- Prices will be inserted here by JavaScript -->
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="book-now-btn" onclick="showReservationModal()">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation Modal -->
    <div class="reservation-modal" id="reservationModal">
        <div class="reservation-overlay" onclick="hideReservationModal()"></div>
        <div class="reservation-modal-content">
            <button class="reservation-close" onclick="hideReservationModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="reservation-header">
                <p class="section-subtitle">Réservation de Transport</p>
                <h2 class="section-title">RESERVATION : <span class="animated-word" id="reservationTransportName"></span></h2>
            </div>
            <form action="reservation_transport.php" method="POST" class="reservation-form" id="reservationForm" novalidate>
            <input type="hidden" name="id_moyen" id="reservationTransportId">
                <div class="form-section" style="--section-index: 0;">
                    <h3 class="form-section-title">Informations Personnelles</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cin">CIN *</label>
                            <input type="number" id="cin" name="cin" placeholder="Entrez votre CIN" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                        </div>
                    </div>
                </div>
                <div class="form-section" style="--section-index: 1;">
                    <h3 class="form-section-title">Détails du Voyage</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="depart">Lieu de départ *</label>
                            <select id="depart" name="depart" required>
                                <option value="">Sélectionner un gouvernorat</option>
                                <?php foreach ($gouvernorats as $gouvernorat): ?>
                                    <option value="<?= htmlspecialchars($gouvernorat) ?>">
                                        <?= htmlspecialchars($gouvernorat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="destination">Destination *</label>
                            <select id="destination" name="destination" required>
                                <option value="">Sélectionner un gouvernorat</option>
                                <?php foreach ($gouvernorats as $gouvernorat): ?>
                                    <option value="<?= htmlspecialchars($gouvernorat) ?>">
                                        <?= htmlspecialchars($gouvernorat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="datedebut">Date de début *</label>
                            <input type="date" id="datedebut" name="datedebut" required>
                        </div>
                        <div class="form-group">
                            <label for="datefin">Date de fin *</label>
                            <input type="date" id="datefin" name="datefin" required>
                        </div>
                    </div>
                </div>
                <div class="form-section" style="--section-index: 2;">
                    <h3 class="form-section-title">Paiement</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="paiement">Mode de paiement *</label>
                            <select id="paiement" name="paiement" required>
                                <option value="">Sélectionner une option</option>
                                <option value="carte">Carte bancaire</option>
                                <option value="especes">Espèces</option>
                                <option value="virement">Virement bancaire</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-check"></i> Confirmer la Réservation
                    </button>
                    <button type="button" class="cancel-btn" onclick="hideReservationModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Call to Action Section -->
    <section class="cta">
        <div class="container cta-container">
            <h2 class="cta-title">Ready to Experience Luxury Living?</h2>
            <p class="cta-text">Schedule a private tour of TuniFy Village and discover your dream home in our exclusive community. Our knowledgeable consultants are ready to assist you in finding the perfect residence.</p>
            <div class="cta-buttons">
                <a href="#contact" class="cta-primary">Schedule a Tour</a>
                <a href="#" class="cta-secondary">Download Brochure</a>
            </div>
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
        
        // Transport data from PHP
        const transportData = <?= json_encode($transportData) ?>;
        let selectedTransportId = null;
        let selectedTransportName = '';

        // Transport Details Popup
        function showTransportDetails(transportType) {
            const transport = transportData[transportType];
            selectedTransportId = transport.id;
            selectedTransportName = transport.title;
            const titleElement = document.getElementById('popupTransportTitle');
            const imageElement = document.getElementById('popupTransportImage');
            const detailsList = document.getElementById('transportDetailsList');
            const pricesList = document.getElementById('transportPricesList');
            
            // Set title and image
            titleElement.textContent = transport.title;
            imageElement.src = `../../image/${transport.image}`;
            imageElement.alt = transport.title;
            
            // Clear previous info
            detailsList.innerHTML = '';
            pricesList.innerHTML = '';
            
            // Add details with animation
            transport.details.forEach((item, index) => {
                setTimeout(() => {
                    const li = document.createElement('li');
                    if (item.label === 'Availability') {
                        li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span class="status-badge ${item.class}">${item.value}</span>`;
                    } else {
                        li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span>${item.value}</span>`;
                    }
                    li.classList.add('fade-in-item');
                    detailsList.appendChild(li);
                }, index * 100);
            });
            
            // Add prices with animation
            transport.prices.forEach((item, index) => {
                setTimeout(() => {
                    const li = document.createElement('li');
                    li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span>${item.value}</span>`;
                    li.classList.add('fade-in-item');
                    pricesList.appendChild(li);
                }, index * 100);
            });
            
            // Show popup with animation
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideTransportDetails() {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Do not reset selectedTransportId to allow reservation modal to use it
        }

        function showReservationModal() {
            if (selectedTransportId) {
                document.getElementById('reservationTransportId').value = selectedTransportId;
                document.getElementById('reservationTransportName').textContent = selectedTransportName;
                const modal = document.getElementById('reservationModal');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                hideTransportDetails();
            } else {
                alert('Erreur : Aucun transport sélectionné.');
            }
        }

        function hideReservationModal() {
            const modal = document.getElementById('reservationModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset form
            document.getElementById('reservationForm').reset();
            // Clear error messages
            document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
                input.classList.remove('error');
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });
        }

        // Gallery item hover effects
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
        });

        // Client-side form validation
        document.getElementById('reservationForm').addEventListener('submit', function(event) {
            let isValid = true;
            const errors = {};


            // Get form values
            const id_moyen = document.getElementById('reservationTransportId').value;
            const nom = document.getElementById('nom').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const cin = document.getElementById('cin').value.trim();
            const email = document.getElementById('email').value.trim();
            const depart = document.getElementById('depart').value;
            const destination = document.getElementById('destination').value;
            const paiement = document.getElementById('paiement').value;
            const datedebut = document.getElementById('datedebut').value;
            const datefin = document.getElementById('datefin').value;

            console.log({
            reservationTransportId: document.getElementById('reservationTransportId').value,
            nom,
            prenom,
            cin,
            email,
            depart,
            destination,
            paiement,
            datedebut,
            datefin
            });


            // Clear previous error messages and error classes
            document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
                input.classList.remove('error');
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });

            // Nom validation
            if (!nom || nom.length > 100) {
                errors['nom'] = "Le nom est requis et doit être inférieur à 100 caractères.";
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(nom)) {
                errors['nom'] = "Le nom ne doit contenir que des lettres et des espaces.";
                isValid = false;
            }

            // Prénom validation
            if (!prenom || prenom.length > 100) {
                errors['prenom'] = "Le prénom est requis et doit être inférieur à 100 caractères.";
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(prenom)) {
                errors['prenom'] = "Le prénom ne doit contenir que des lettres et des espaces.";
                isValid = false;
            }

            // CIN validation
            if (!cin || !/^\d{8}$/.test(cin)) {
                errors['cin'] = "Le CIN doit être un numéro de 8 chiffres.";
                isValid = false;
            }

            // Email validation
            if (!email || email.length > 255) {
                errors['email'] = "L'email est requis et doit être inférieur à 255 caractères.";
                isValid = false;
            } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                errors['email'] = "L'email est invalide.";
                isValid = false;
            }

            // Depart validation
            const validGouvernorats = <?= json_encode($gouvernorats) ?>;
            if (!depart || !validGouvernorats.includes(depart)) {
                errors['depart'] = "Le gouvernorat de départ est invalide.";
                isValid = false;
            }

            // Destination validation
            if (!destination || !validGouvernorats.includes(destination)) {
                errors['destination'] = "Le gouvernorat de destination est invalide.";
                isValid = false;
            } else if (depart === destination) {
                errors['destination'] = "Le départ et la destination ne peuvent pas être identiques.";
                isValid = false;
            }

            // Paiement validation
            const validPaiements = <?= json_encode($validPaiements) ?>;
            if (!paiement || !validPaiements.includes(paiement)) {
                errors['paiement'] = "Le mode de paiement est invalide.";
                isValid = false;
            }

            // Date début validation
            const today = new Date().toISOString().split('T')[0];
            if (!datedebut) {
                errors['datedebut'] = "La date de début est requise.";
                isValid = false;
            } else if (datedebut < today) {
                errors['datedebut'] = "La date de début ne peut pas être dans le passé.";
                isValid = false;
            }

            // Date fin validation
            if (!datefin) {
                errors['datefin'] = "La date de fin est requise.";
                isValid = false;
            } else if (datefin <= datedebut) {
                errors['datefin'] = "La date de fin doit être postérieure à la date de début.";
                isValid = false;
            }

            // Display errors
            for (const [field, message] of Object.entries(errors)) {
                const input = document.getElementById(field);
                input.classList.add('error');
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.id = `${field}Error`;
                errorSpan.textContent = message;
                input.parentNode.appendChild(errorSpan);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });

        // Clear error on input
        document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const existingError = this.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });
        });
    </script>
</body>
</html>