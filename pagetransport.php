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

    // Récupérer les réservations depuis la base de données
    try {
        $query = $pdo->query("SELECT rt.*, t.nom AS transport_nom, t.type AS transport_type 
                            FROM reservation_transport rt 
                            JOIN transport t ON rt.id_moyen = t.id 
                            ORDER BY rt.datedebut DESC");
        $reservations = $query->fetchAll(PDO::FETCH_ASSOC);
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
    <title>TuniFy Village - Transport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #c9a86c;
            --gold-light: #e9d9b6;
            --dark-bg: #1c1c1c;
            --light-text: #f8f5eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--light-text);
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            background-color: var(--dark-bg);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
            display: flex;
            justify-content: center;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            width: 100%;
            padding: 0 20px;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        .logo-text h1 {
            font-size: 24px;
            color: var(--gold-primary);
            margin: 0;
        }
        .logo-text p {
            font-size: 12px;
            color: var(--gold-light);
            text-transform: uppercase;
            margin: 0;
            color: #4169E1; /* Blue for 'VILAGE' */
        }
        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            color: var(--light-text);
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            text-transform: uppercase;
        }
        nav ul li a:hover {
            color: var(--gold-primary);
        }
        .contact-btn {
            background-color: var(--gold-primary);
            color: var(--dark-bg);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-transform: uppercase;
        }
        .contact-btn:hover {
            background-color: var(--gold-light);
        }
        .reservations-icon {
            position: fixed;
            top: 15px;
            right: 80px;
            z-index: 1001;
            cursor: pointer;
            color: var(--gold-primary);
            font-size: 24px;
            transition: color 0.3s;
        }
        .reservations-icon:hover {
            color: var(--gold-light);
        }

        /* Gallery Section */
        .gallery {
            padding-top: 100px;
            background-color: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .gallery-header {
            margin-bottom: 40px;
        }
        .section-subtitle {
            color: var(--gold-light);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .section-title {
            color: var(--light-text);
            font-size: 32px;
            margin-top: 10px;
        }
        .animated-word {
            color: var(--gold-primary);
        }
        .gallery-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            max-width: 1200px;
            padding: 0 20px;
        }
        .gallery-item {
            position: relative;
            background-color: #2a2a2a;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s;
            width: 300px;
        }
        .gallery-item:hover {
            transform: translateY(-10px);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .gallery-item-content {
            padding: 15px;
            text-align: center;
        }
        .gallery-item-title {
            color: var(--light-text);
            font-size: 18px;
            margin: 0;
        }
        .gallery-item-subtitle {
            color: var(--gold-light);
            font-size: 14px;
            margin: 5px 0 0;
        }
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
        }
        .availability-badge.unavailable {
            background-color: #dc3545;
        }

        /* Modals with Animation */
        .modal {
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
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .modal-content {
            background-color: #2a2a2a;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            z-index: 2;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: var(--gold-primary);
            font-size: 24px;
            cursor: pointer;
        }
        .reservations-header, .popup-header, .reservation-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--light-text);
        }
        .reservations-table th, .reservations-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        }
        .reservations-table th {
            background-color: #333;
            color: var(--gold-primary);
        }
        .reservations-table tbody tr {
            cursor: pointer;
        }
        .reservations-table tbody tr:hover {
            background-color: rgba(201, 168, 108, 0.1);
        }
        .detail-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .detail-image {
            flex: 1;
            min-width: 200px;
        }
        .detail-image img {
            width: 100%;
            border-radius: 10px;
        }
        .detail-info {
            flex: 2;
            min-width: 200px;
        }
        .detail-section {
            margin-bottom: 20px;
        }
        .section-heading {
            color: var(--gold-primary);
            font-size: 18px;
            margin-bottom: 10px;
        }
        .transport-details-list, .transport-prices-list {
            list-style: none;
            padding: 0;
        }
        .transport-details-list li, .transport-prices-list li {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
        }
        .info-label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .info-label i {
            color: var(--gold-primary);
        }
        .status-badge.available {
            color: #28a745;
        }
        .status-badge.unavailable {
            color: #dc3545;
        }
        .detail-actions {
            text-align: center;
            margin-top: 20px;
        }
        .book-now-btn {
            background-color: var(--gold-primary);
            color: var(--dark-bg);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .book-now-btn:hover {
            background-color: var(--gold-light);
        }
        .reservation-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-section {
            background-color: #333;
            padding: 15px;
            border-radius: 5px;
        }
        .form-section-title {
            color: var(--gold-primary);
            font-size: 18px;
            margin-bottom: 10px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            color: var(--gold-light);
        }
        .form-group input, .form-group select {
            padding: 8px;
            border: none;
            border-radius: 5px;
            background-color: #444;
            color: var(--light-text);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            background-color: #555;
        }
        .form-actions {
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .submit-btn, .cancel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn {
            background-color: var(--gold-primary);
            color: var(--dark-bg);
        }
        .submit-btn:hover {
            background-color: var(--gold-light);
        }
        .cancel-btn {
            background-color: #dc3545;
            color: var(--light-text);
        }
        .cancel-btn:hover {
            background-color: #ff4d4d;
        }
        .error {
            border: 1px solid #dc3545 !important;
        }
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .gallery-grid {
                flex-direction: column;
                align-items: center;
            }
            .gallery-item {
                width: 100%;
                max-width: 300px;
            }
            .nav-container {
                flex-direction: column;
                gap: 10px;
            }
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            nav ul li {
                margin: 5px 0;
            }
            .reservations-icon {
                right: 20px;
            }
        }
        <link rel="stylesheet" href="style.css">

    </style>
</head>
<body>
    <!-- Header / Navigation -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <img src="../../image/tunify.png" alt="TuniFy Village Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Vilage</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#transport">Transport</a></li>
                    <li><a href="#hebergement">Hebergement</a></li>
                    <li><a href="#restauration">Restauration</a></li>
                    <li><a href="#reclamations">Reclamations</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Book a Tour</button>
        </div>
        <i class="fas fa-book reservations-icon" onclick="showReservationsModal()"></i>
    </header>

    <!-- Gallery Section -->
    <section class="gallery">
        <div class="gallery-header">
            <p class="section-subtitle">Immersive Experience</p>
            <h2 class="section-title">Discover Our Stunning <span class="animated-word">Transports</span></h2>
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
    </section>

    <!-- Transport Popup -->
    <div class="modal transport-popup" id="transportPopup">
        <div class="modal-overlay" onclick="hideTransportDetails()"></div>
        <div class="modal-content">
            <div class="popup-header">
                <h2 id="popupTransportTitle">Transport Title</h2>
                <button class="close-btn" onclick="hideTransportDetails()">
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
    <div class="modal reservation-modal" id="reservationModal">
        <div class="modal-overlay" onclick="hideReservationModal()"></div>
        <div class="modal-content">
            <button class="close-btn" onclick="hideReservationModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="reservation-header">
                <p class="section-subtitle">Réservation de Transport</p>
                <h2 class="section-title">RESERVATION : <span class="animated-word" id="reservationTransportName"></span></h2>
            </div>
            <form action="reservation_transport.php" method="POST" class="reservation-form" id="reservationForm" novalidate>
                <input type="hidden" name="id_moyen" id="reservationTransportId">
                <div class="form-section">
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
                <div class="form-section">
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
                <div class="form-section">
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

    <!-- Reservations Modal -->
    <div class="modal reservations-modal" id="reservationsModal">
        <div class="modal-overlay" onclick="hideReservationsModal()"></div>
        <div class="modal-content">
            <button class="close-btn" onclick="hideReservationsModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="reservations-header">
                <p class="section-subtitle">Liste des Réservations</p>
                <h2 class="section-title">Réservations de Transport</h2>
            </div>
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Transport</th>
                        <th>Départ</th>
                        <th>Destination</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Aucune réservation trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr onclick="viewReservationDetails(<?= $reservation['id'] ?>)">
                                <td><?= htmlspecialchars($reservation['nom'] . ' ' . $reservation['prenom']) ?></td>
                                <td><?= htmlspecialchars($reservation['transport_nom']) ?> (<?= htmlspecialchars($reservation['transport_type']) ?>)</td>
                                <td><?= htmlspecialchars($reservation['depart']) ?></td>
                                <td><?= htmlspecialchars($reservation['destination']) ?></td>
                                <td><?= htmlspecialchars($reservation['datedebut']) ?></td>
                                <td><?= htmlspecialchars($reservation['datefin']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($reservation['paiement'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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

            titleElement.textContent = transport.title;
            imageElement.src = `../../image/${transport.image}`;
            imageElement.alt = transport.title;

            detailsList.innerHTML = '';
            pricesList.innerHTML = '';

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

            transport.prices.forEach((item, index) => {
                setTimeout(() => {
                    const li = document.createElement('li');
                    li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span>${item.value}</span>`;
                    li.classList.add('fade-in-item');
                    pricesList.appendChild(li);
                }, index * 100);
            });

            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideTransportDetails() {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
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
            document.getElementById('reservationForm').reset();
            document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
                input.classList.remove('error');
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });
        }

        function showReservationsModal() {
            const modal = document.getElementById('reservationsModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideReservationsModal() {
            const modal = document.getElementById('reservationsModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function viewReservationDetails(reservationId) {
            window.location.href = `reservation_details.php?id=${reservationId}`;
        }

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function() {
                hideReservationModal();
                hideReservationsModal();
                hideTransportDetails();
            });
        });

        document.getElementById('reservationForm').addEventListener('submit', function(event) {
            let isValid = true;
            const errors = {};
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

            document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
                input.classList.remove('error');
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });

            if (!nom || nom.length > 100 || !/^[a-zA-Z\s]+$/.test(nom)) {
                errors['nom'] = "Le nom est requis, doit être < 100 caractères et contenir seulement des lettres.";
                isValid = false;
            }
            if (!prenom || prenom.length > 100 || !/^[a-zA-Z\s]+$/.test(prenom)) {
                errors['prenom'] = "Le prénom est requis, doit être < 100 caractères et contenir seulement des lettres.";
                isValid = false;
            }
            if (!cin || !/^\d{8}$/.test(cin)) {
                errors['cin'] = "Le CIN doit être un numéro de 8 chiffres.";
                isValid = false;
            }
            if (!email || email.length > 255 || !/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                errors['email'] = "L'email est requis, doit être < 255 caractères et valide.";
                isValid = false;
            }
            const validGouvernorats = <?= json_encode($gouvernorats) ?>;
            if (!depart || !validGouvernorats.includes(depart)) {
                errors['depart'] = "Le gouvernorat de départ est invalide.";
                isValid = false;
            }
            if (!destination || !validGouvernorats.includes(destination) || depart === destination) {
                errors['destination'] = "La destination est invalide ou identique au départ.";
                isValid = false;
            }
            const validPaiements = <?= json_encode($validPaiements) ?>;
            if (!paiement || !validPaiements.includes(paiement)) {
                errors['paiement'] = "Le mode de paiement est invalide.";
                isValid = false;
            }
            const today = new Date().toISOString().split('T')[0];
            if (!datedebut || datedebut < today) {
                errors['datedebut'] = "La date de début est requise et ne peut pas être dans le passé.";
                isValid = false;
            }
            if (!datefin || datefin <= datedebut) {
                errors['datefin'] = "La date de fin est requise et doit être postérieure à la date de début.";
                isValid = false;
            }

            for (const [field, message] of Object.entries(errors)) {
                const input = document.getElementById(field);
                input.classList.add('error');
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.textContent = message;
                input.parentNode.appendChild(errorSpan);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });

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