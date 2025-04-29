<?php
ob_start(); // Start output buffering
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error output
ini_set('log_errors', 1); // Log errors

// Verify config inclusion
if (!file_exists('../../config.php')) {
    $_SESSION['frontend_error_message'] = "Configuration file not found.";
    $_SESSION['show_message_flag'] = true;
    header("Location: pagetransport.php?reservation=error");
    exit;
}
require_once '../../config.php';

// Initialize
$show_message = false;

// Initialize PDO
try {
    $pdo = config::getConnexion();
} catch (Exception $e) {
    $_SESSION['frontend_error_message'] = "Database connection failed: " . $e->getMessage();
    $_SESSION['show_message_flag'] = true;
    header("Location: pagetransport.php?reservation=error");
    exit;
}

// Gouvernorats and payment methods
$gouvernorats = [
    "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
    "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
    "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
];
$validPaiements = ['carte', 'especes', 'virement'];

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation
        $required_fields = ['id_moyen', 'nom', 'prenom', 'cin', 'email', 'depart', 'destination', 'datedebut', 'datefin', 'paiement'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Le champ $field est requis.");
            }
        }

        $id_moyen = intval($_POST['id_moyen']);
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $cin = htmlspecialchars(trim($_POST['cin']));
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $depart = htmlspecialchars(trim($_POST['depart']));
        $destination = htmlspecialchars(trim($_POST['destination']));
        $datedebut = htmlspecialchars(trim($_POST['datedebut']));
        $datefin = htmlspecialchars(trim($_POST['datefin']));
        $paiement = htmlspecialchars(trim($_POST['paiement']));

        if (!$email) {
            throw new Exception("L'email est invalide.");
        }

        if (!in_array($depart, $gouvernorats) || !in_array($destination, $gouvernorats)) {
            throw new Exception("Le départ ou la destination est invalide.");
        }

        if ($depart === $destination) {
            throw new Exception("Le départ et la destination ne peuvent pas être identiques.");
        }

        if (!in_array($paiement, $validPaiements)) {
            throw new Exception("Le mode de paiement est invalide.");
        }

        if (strtotime($datefin) <= strtotime($datedebut)) {
            throw new Exception("La date de fin doit être postérieure à la date de début.");
        }

        // Check transport availability
        $stmt = $pdo->prepare("SELECT stock FROM transport WHERE id = ?");
        $stmt->execute([$id_moyen]);
        $transport = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transport || $transport['stock'] <= 0) {
            throw new Exception("Ce moyen de transport n'est plus disponible.");
        }

        // Transaction
        $pdo->beginTransaction();

        // Insert reservation
        $stmt = $pdo->prepare("INSERT INTO reservation_transport 
                             (id_moyen, nom, prenom, cin, email, depart, destination, datedebut, datefin, paiement) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_moyen, $nom, $prenom, $cin, $email, $depart, $destination, $datedebut, $datefin, $paiement]);

        // Update stock
        $stmt = $pdo->prepare("UPDATE transport SET stock = stock - 1 WHERE id = ?");
        $stmt->execute([$id_moyen]);

        $pdo->commit();

        // Store message and flag
        $_SESSION['frontend_success_message'] = "Réservation confirmée! Votre transport a été réservé avec succès.";
        $_SESSION['show_message_flag'] = true;

        // Redirect with success parameter
        header("Location: " . $_SERVER['PHP_SELF'] . "?reservation=success");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['frontend_error_message'] = "Erreur: " . $e->getMessage();
        $_SESSION['show_message_flag'] = true;
        header("Location: " . $_SERVER['PHP_SELF'] . "?reservation=error");
        exit();
    }
}

// Fetch transports
try {
    $query = $pdo->query("SELECT * FROM transport");
    $transports = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['frontend_error_message'] = "Erreur de connexion à la base de données: " . $e->getMessage();
    $_SESSION['show_message_flag'] = true;
    $transports = [];
}

// Build transport data
$transportData = [];
foreach ($transports as $transport) {
    $availability = ($transport['stock'] ?? 0) > 0 ? 'Disponible' : 'Non disponible';
    $availabilityClass = ($transport['stock'] ?? 0) > 0 ? 'available' : 'unavailable';

    // Sanitize for JSON
    $transportData[strtolower($transport['type'])] = [
        'id' => (int)($transport['id'] ?? 0),
        'title' => strtoupper(utf8_encode($transport['nom'] ?? '')),
        'type' => utf8_encode($transport['type'] ?? ''),
        'details' => [
            ['label' => 'Type', 'value' => utf8_encode($transport['type'] ?? ''), 'icon' => 'fas fa-info-circle'],
            ['label' => 'Top Speed', 'value' => ($transport['vitesse'] ?? 0) . ' km/h', 'icon' => 'fas fa-tachometer-alt'],
            ['label' => 'Seats', 'value' => (int)($transport['nb_places'] ?? 0), 'icon' => 'fas fa-users'],
            ['label' => 'Battery Range', 'value' => ($transport['batterie'] ?? 0) . ' km', 'icon' => 'fas fa-battery-full'],
            ['label' => 'Availability', 'value' => $availability, 'icon' => 'fas fa-check-circle', 'class' => $availabilityClass]
        ],
        'prices' => [
            ['label' => 'Price Per Day', 'value' => ($transport['prix'] ?? 0) . ' €', 'icon' => 'fas fa-calendar-day']
        ],
        'image' => utf8_encode($transport['image'] ?? ''),
        'availability' => $availability,
        'availabilityClass' => $availabilityClass
    ];
}

// Retrieve messages only if flag is present
$success_message = '';
$error_message = '';
if (isset($_SESSION['show_message_flag']) && $_SESSION['show_message_flag']) {
    $success_message = $_SESSION['frontend_success_message'] ?? '';
    $error_message = $_SESSION['frontend_error_message'] ?? '';
    $show_message = true;

    // Clear session data immediately
    unset($_SESSION['show_message_flag']);
    unset($_SESSION['frontend_success_message']);
    unset($_SESSION['frontend_error_message']);
    session_write_close();
} else {
    $success_message = '';
    $error_message = '';
    $show_message = false;
}

// Ensure clean output
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Transport</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../../styleres.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Styles généraux */
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        body.popup-open {
            overflow: hidden;
        }
        
        /* Alertes */
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 400px;
        }
        
        .alert-success {
            background-color: #4CAF50;
            color: white;
        }
        
        .alert-error {
            background-color: #F44336;
            color: white;
        }
        
        .alert-message .close-btn {
            margin-left: 15px;
            cursor: pointer;
            font-size: 18px;
            color: white;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Popups */
        .transport-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }
        
        .transport-popup-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .popup-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }
        
        /* Modal de réservation */
        .reservation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }
        
        .reservation-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .reservation-modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            z-index: 1002;
        }
        
        .reservation-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }
        
        /* Formulaires */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group input.error,
        .form-group select.error {
            border-color: #F44336;
        }
        
        .error-message {
            color: #F44336;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        /* Boutons */
        .reserve-btn, .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .reserve-btn:hover, .submit-btn:hover {
            background-color: #45a049;
        }
        
        .reserve-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        /* Galerie */
        .gallery-item {
            position: relative;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            z-index: 1;
        }
        
        .available {
            background-color: #4CAF50;
            color: white;
        }
        
        .unavailable {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div id="successAlert" class="alert-message alert-success" style="display: none; opacity: 1;">
        <span id="successAlertText"></span>
        <i class="fas fa-times close-btn"></i>
    </div>
    <div id="errorAlert" class="alert-message alert-error" style="display: none; opacity: 1;">
        <span id="errorAlertText"></span>
        <i class="fas fa-times close-btn"></i>
    </div>

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

    <section class="gallery" id="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Immersive Experience</p>
                <h2 class="section-title">Discover Our <span class="animated-word">Stunning</span> Transports</h2>
            </div>
            <div class="gallery-grid">
                <?php foreach ($transports as $transport): ?>
                <div class="gallery-item" onclick="showTransportDetails('<?= htmlspecialchars(strtolower($transport['type'])) ?>')">
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

    <div class="transport-popup" id="transportPopup">
        <div class="transport-popup-content">
            <button class="popup-close" onclick="hideTransportDetails()">
                <i class="fas fa-times"></i>
            </button>
            <div class="transport-details">
                <img id="popupTransportImage" src="" alt="">
                <h2 id="popupTransportTitle"></h2>
                <ul id="transportDetailsList"></ul>
                <ul id="transportPricesList"></ul>
                <button class="reserve-btn" onclick="showReservationModal()">Réserver Maintenant</button>
            </div>
        </div>
    </div>

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
            <form action="pagetransport.php" method="POST" class="reservation-form" id="reservationForm" novalidate>
                <input type="hidden" name="id_moyen" id="reservationTransportId">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="form-group">
                    <label for="cin">CIN</label>
                    <input type="text" id="cin" name="cin" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="depart">Départ</label>
                    <select id="depart" name="depart" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($gouvernorats as $gouvernorat): ?>
                            <option value="<?= htmlspecialchars($gouvernorat) ?>"><?= htmlspecialchars($gouvernorat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <select id="destination" name="destination" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($gouvernorats as $gouvernorat): ?>
                            <option value="<?= htmlspecialchars($gouvernorat) ?>"><?= htmlspecialchars($gouvernorat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="datedebut">Date de Début</label>
                    <input type="date" id="datedebut" name="datedebut" required>
                </div>
                <div class="form-group">
                    <label for="datefin">Date de Fin</label>
                    <input type="date" id="datefin" name="datefin" required>
                </div>
                <div class="form-group">
                    <label for="paiement">Mode de Paiement</label>
                    <select id="paiement" name="paiement" required>
                        <option value="">Sélectionner</option>
                        <option value="carte">Carte</option>
                        <option value="especes">Espèces</option>
                        <option value="virement">Virement</option>
                    </select>
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

    <section class="cta">
        <div class="container">
            <h2>Ready to Experience TuniFy Village?</h2>
            <p>Book your transport now and start your journey!</p>
            <button class="cta-btn">Book Now</button>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>© 2025 TuniFy Village. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Transport data
        const transportData = <?php
            try {
                echo json_encode($transportData, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                echo '{}';
                $_SESSION['frontend_error_message'] = "Error encoding transport data: " . $e->getMessage();
                $_SESSION['show_message_flag'] = true;
            }
        ?>;
        let selectedTransportId = null;
        let selectedTransportName = '';

        // Show alerts only if $show_message is true
        <?php if ($show_message): ?>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($success_message)): ?>
                showAlert('success', "<?= addslashes($success_message) ?>");
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                showAlert('error', "<?= addslashes($error_message) ?>");
            <?php endif; ?>
        });

        function showAlert(type, message) {
            const alertDiv = type === 'success' 
                ? document.getElementById('successAlert')
                : document.getElementById('errorAlert');
            
            alertDiv.querySelector('span').textContent = message;
            alertDiv.style.display = 'flex';
            
            // Animation d'apparition
            alertDiv.style.animation = 'slideIn 0.3s ease-out';
            
            // Disparaître après 5 secondes
            setTimeout(() => {
                alertDiv.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                    alertDiv.style.animation = ''; // Reset animation
                    alertDiv.style.opacity = '1'; // Reset opacity
                }, 500);
            }, 5000);
        }
        <?php endif; ?>

        // Gestion de la fermeture manuelle
        document.querySelectorAll('.alert-message .close-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const alertDiv = this.parentElement;
                alertDiv.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                    alertDiv.style.animation = ''; // Reset animation
                    alertDiv.style.opacity = '1'; // Reset opacity
                }, 500);
            });
        });

        // Show transport details
        function showTransportDetails(transportType) {
            const transport = transportData[transportType];
            if (!transport) {
                <?php if ($show_message): ?>
                showAlert('error', "Transport non trouvé");
                <?php else: ?>
                // Fallback if showAlert is not defined
                alert("Transport non trouvé");
                <?php endif; ?>
                return;
            }
            
            selectedTransportId = transport.id;
            selectedTransportName = transport.title;
            
            document.getElementById('popupTransportTitle').textContent = transport.title;
            document.getElementById('popupTransportImage').src = `../../image/${transport.image}`;
            document.getElementById('popupTransportImage').alt = transport.title;
            
            const detailsList = document.getElementById('transportDetailsList');
            detailsList.innerHTML = '';
            transport.details.forEach(item => {
                const li = document.createElement('li');
                if (item.label === 'Availability') {
                    li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span class="status-badge ${item.class}">${item.value}</span>`;
                } else {
                    li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span>${item.value}</span>`;
                }
                detailsList.appendChild(li);
            });
            
            const pricesList = document.getElementById('transportPricesList');
            pricesList.innerHTML = '';
            transport.prices.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `<span class="info-label"><i class="${item.icon}"></i> ${item.label}</span> <span>${item.value}</span>`;
                pricesList.appendChild(li);
            });
            
            const reserveButton = document.querySelector('.reserve-btn');
            if (transport.availability === 'Non disponible') {
                reserveButton.disabled = true;
                reserveButton.title = "Ce transport n'est pas disponible.";
            } else {
                reserveButton.disabled = false;
                reserveButton.title = '';
            }
            
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';
            document.body.classList.add('popup-open');
        }
        
        // Hide transport details
        function hideTransportDetails() {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'none';
            document.body.classList.remove('popup-open');
        }
        
        // Show reservation modal
        function showReservationModal() {
            if (!selectedTransportId) {
                <?php if ($show_message): ?>
                showAlert('error', "Aucun transport sélectionné");
                <?php else: ?>
                // Fallback if showAlert is not defined
                alert("Aucun transport sélectionné");
                <?php endif; ?>
                return;
            }
            
            document.getElementById('reservationTransportId').value = selectedTransportId;
            document.getElementById('reservationTransportName').textContent = selectedTransportName;
            
            const modal = document.getElementById('reservationModal');
            modal.style.display = 'flex';
            document.body.classList.add('popup-open');
            
            hideTransportDetails();
        }
        
        // Hide reservation modal
        function hideReservationModal() {
            const modal = document.getElementById('reservationModal');
            modal.style.display = 'none';
            document.body.classList.remove('popup-open');
            
            document.getElementById('reservationForm').reset();
            
            document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
                input.classList.remove('error');
                const existingError = input.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });
        }
        
        // Close popups on outside click
        document.addEventListener('click', function(event) {
            const transportPopup = document.getElementById('transportPopup');
            if (event.target === transportPopup) {
                hideTransportDetails();
            }
            
            const reservationModal = document.getElementById('reservationModal');
            if (event.target.classList.contains('reservation-overlay')) {
                hideReservationModal();
            }
        });
        
        // Gallery item animations
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
        });
        
        // Form validation
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
            
            if (!nom || nom.length > 100) {
                errors['nom'] = "Le nom est requis et doit être inférieur à 100 caractères.";
                isValid = false;
            } else if (!/^[a-zA-Z\s\-']+$/.test(nom)) {
                errors['nom'] = "Le nom ne doit contenir que des lettres, espaces, tirets et apostrophes.";
                isValid = false;
            }
            
            if (!prenom || prenom.length > 100) {
                errors['prenom'] = "Le prénom est requis et doit être inférieur à 100 caractères.";
                isValid = false;
            } else if (!/^[a-zA-Z\s\-']+$/.test(prenom)) {
                errors['prenom'] = "Le prénom ne doit contenir que des lettres, espaces, tirets et apostrophes.";
                isValid = false;
            }
            
            if (!cin || !/^\d{8}$/.test(cin)) {
                errors['cin'] = "Le CIN doit être un numéro de 8 chiffres.";
                isValid = false;
            }
            
            if (!email || email.length > 255) {
                errors['email'] = "L'email est requis et doit être inférieur à 255 caractères.";
                isValid = false;
            } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                errors['email'] = "L'email est invalide.";
                isValid = false;
            }
            
            const validGouvernorats = <?php echo json_encode($gouvernorats, JSON_THROW_ON_ERROR); ?>;
            if (!depart || !validGouvernorats.includes(depart)) {
                errors['depart'] = "Le gouvernorat de départ est invalide.";
                isValid = false;
            }
            
            if (!destination || !validGouvernorats.includes(destination)) {
                errors['destination'] = "Le gouvernorat de destination est invalide.";
                isValid = false;
            } else if (depart === destination) {
                errors['destination'] = "Le départ et la destination ne peuvent pas être identiques.";
                isValid = false;
            }
            
            const validPaiements = <?php echo json_encode($validPaiements, JSON_THROW_ON_ERROR); ?>;
            if (!paiement || !validPaiements.includes(paiement)) {
                errors['paiement'] = "Le mode de paiement est invalide.";
                isValid = false;
            }
            
            const today = new Date().toISOString().split('T')[0];
            if (!datedebut) {
                errors['datedebut'] = "La date de début est requise.";
                isValid = false;
            } else if (datedebut < today) {
                errors['datedebut'] = "La date de début ne peut pas être dans le passé.";
                isValid = false;
            }
            
            if (!datefin) {
                errors['datefin'] = "La date de fin est requise.";
                isValid = false;
            } else if (datefin <= datedebut) {
                errors['datefin'] = "La date de fin doit être postérieure à la date de début.";
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
        
        // Clear error messages on input
        document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const existingError = this.nextElementSibling;
                if (existingError && existingError.classList.contains('error-message')) {
                    existingError.remove();
                }
            });
        });
        
        // Header scroll effect
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