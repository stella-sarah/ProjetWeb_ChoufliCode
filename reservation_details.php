<?php
    session_start();
    require_once '../../config.php';
    $pdo = config::getConnexion();

    // Vérifier si l'ID de la réservation est fourni
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: pagetransport.php");
        exit();
    }

    $reservation_id = (int)$_GET['id'];

    // Récupérer les détails de la réservation
    try {
        $stmt = $pdo->prepare("SELECT rt.*, t.nom AS transport_nom, t.type AS transport_type 
                               FROM reservation_transport rt 
                               JOIN transport t ON rt.id_moyen = t.id 
                               WHERE rt.id = ?");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $_SESSION['error_message'] = "Réservation non trouvée.";
            header("Location: pagetransport.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur de base de données: " . $e->getMessage());
    }

    // Liste des gouvernorats
    $gouvernorats = [
        "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
        "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
        "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
    ];

    // Valid payment methods
    $validPaiements = ['carte', 'especes', 'virement'];

    // Traitement de la mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $cin = trim($_POST['cin']);
        $email = trim($_POST['email']);
        $depart = $_POST['depart'];
        $destination = $_POST['destination'];
        $datedebut = $_POST['datedebut'];
        $datefin = $_POST['datefin'];
        $paiement = $_POST['paiement'];

        // Validation côté serveur
        $errors = [];
        if (empty($nom) || strlen($nom) > 100 || !preg_match('/^[a-zA-Z\s]+$/', $nom)) {
            $errors[] = "Le nom est requis, doit être < 100 caractères et contenir seulement des lettres.";
        }
        if (empty($prenom) || strlen($prenom) > 100 || !preg_match('/^[a-zA-Z\s]+$/', $prenom)) {
            $errors[] = "Le prénom est requis, doit être < 100 caractères et contenir seulement des lettres.";
        }
        if (empty($cin) || !preg_match('/^\d{8}$/', $cin)) {
            $errors[] = "Le CIN doit être un numéro de 8 chiffres.";
        }
        if (empty($email) || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email est requis, doit être < 255 caractères et valide.";
        }
        if (!in_array($depart, $gouvernorats)) {
            $errors[] = "Le gouvernorat de départ est invalide.";
        }
        if (!in_array($destination, $gouvernorats) || $depart === $destination) {
            $errors[] = "La destination est invalide ou identique au départ.";
        }
        if (!in_array($paiement, $validPaiements)) {
            $errors[] = "Le mode de paiement est invalide.";
        }
        $today = date('Y-m-d');
        if (empty($datedebut) || $datedebut < $today) {
            $errors[] = "La date de début est requise et ne peut pas être dans le passé.";
        }
        if (empty($datefin) || $datefin <= $datedebut) {
            $errors[] = "La date de fin est requise et doit être postérieure à la date de début.";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE reservation_transport 
                                       SET nom = ?, prenom = ?, cin = ?, email = ?, 
                                           depart = ?, destination = ?, datedebut = ?, 
                                           datefin = ?, paiement = ? 
                                       WHERE id = ?");
                $stmt->execute([
                    $nom, $prenom, $cin, $email,
                    $depart, $destination, $datedebut,
                    $datefin, $paiement, $reservation_id
                ]);
                $_SESSION['success_message'] = "Réservation mise à jour avec succès.";
                header("Location: pagetransport.php");
                exit();
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la mise à jour: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Détails de la Réservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo img {
            height: 40px;
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
        }
        .contact-btn:hover {
            background-color: var(--gold-light);
        }

        /* Main Content */
        .main-content {
            padding-top: 100px;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        .reservation-header {
            text-align: center;
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
        .reservation-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
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
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
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
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="pagetransport.php">Transport</a></li>
                    <li><a href="#hebergement">Hebergement</a></li>
                    <li><a href="#restauration">Restauration</a></li>
                    <li><a href="#reclamations">Reclamations</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Book a Tour</button>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="reservation-header">
            <p class="section-subtitle">Détails de la Réservation</p>
            <h2 class="section-title">Modifier <span class="animated-word">Réservation</span></h2>
        </div>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form action="reservation_details.php?id=<?= $reservation_id ?>" method="POST" class="reservation-form">
            <div class="form-section">
                <h3 class="form-section-title">Informations Personnelles</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($reservation['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($reservation['prenom']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cin">CIN *</label>
                        <input type="number" id="cin" name="cin" value="<?= htmlspecialchars($reservation['cin']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($reservation['email']) ?>" required>
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
                                <option value="<?= htmlspecialchars($gouvernorat) ?>" <?= $reservation['depart'] === $gouvernorat ? 'selected' : '' ?>>
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
                                <option value="<?= htmlspecialchars($gouvernorat) ?>" <?= $reservation['destination'] === $gouvernorat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gouvernorat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="datedebut">Date de début *</label>
                        <input type="date" id="datedebut" name="datedebut" value="<?= htmlspecialchars($reservation['datedebut']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="datefin">Date de fin *</label>
                        <input type="date" id="datefin" name="datefin" value="<?= htmlspecialchars($reservation['datefin']) ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-section">
            <h3 class="form-section-title">Itinéraire de votre réservation</h3>
            <div id="reservationMap" style="height: 350px; border-radius: 8px; overflow: hidden; border: 2px solid #c9a86c;"></div>
            <div id="routeInfo" style="margin-top: 18px; text-align: center; color: #fff; font-size: 1.1em; display: none;">
                <strong>Départ :</strong> <span id="departLabel"></span> &nbsp;|&nbsp;
                <strong>Destination :</strong> <span id="destinationLabel"></span><br>
                <strong>Distance estimée :</strong> <span id="routeDistance"></span> &nbsp;|&nbsp;
                <strong>Durée estimée :</strong> <span id="routeDuration"></span>
            </div>

        </div>
            <div class="form-section">
                <h3 class="form-section-title">Paiement</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="paiement">Mode de paiement *</label>
                        <select id="paiement" name="paiement" required>
                            <option value="">Sélectionner une option</option>
                            <option value="carte" <?= $reservation['paiement'] === 'carte' ? 'selected' : '' ?>>Carte bancaire</option>
                            <option value="especes" <?= $reservation['paiement'] === 'especes' ? 'selected' : '' ?>>Espèces</option>
                            <option value="virement" <?= $reservation['paiement'] === 'virement' ? 'selected' : '' ?>>Virement bancaire</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check"></i> Mettre à jour
                </button>
                <a href="pagetransport.php" class="cancel-btn">Retour</a>
            </div>
        </form>
    </div>
    <script>
    async function geocodeCity(city) {
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(city + ', Tunisia')}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data && data.length > 0) {
            return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
        }
        throw new Error('Ville non trouvée: ' + city);
    }

    async function getRouteInfo(from, to) {
        const url = `https://router.project-osrm.org/route/v1/driving/${from[1]},${from[0]};${to[1]},${to[0]}?overview=full&geometries=geojson`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.routes && data.routes.length > 0) {
            return data.routes[0];
        }
        throw new Error('Route non trouvée');
    }

    async function showReservationMap(depart, destination) {
        const mapDiv = document.getElementById('reservationMap');
        const infoDiv = document.getElementById('routeInfo');
        document.getElementById('departLabel').textContent = depart;
        document.getElementById('destinationLabel').textContent = destination;
        infoDiv.style.display = 'block';

        // Clean up previous map if any
        if (mapDiv._leaflet_id) {
            mapDiv._leaflet_id = null;
            mapDiv.innerHTML = "";
        }

        try {
            const from = await geocodeCity(depart);
            const to = await geocodeCity(destination);
            const route = await getRouteInfo(from, to);

            // Custom icons
            const departIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-green.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            const destIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-red.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Init map
            let map = L.map('reservationMap', {
                zoomControl: true,
                attributionControl: false
            }).setView(from, 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Draw route
            const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);
            const polyline = L.polyline(coords, {
                color: '#c9a86c',
                weight: 6,
                opacity: 0.85,
                dashArray: '8, 8'
            }).addTo(map);

            // Add markers with popups
            L.marker(from, {icon: departIcon}).addTo(map)
                .bindPopup(`<b>Départ</b><br>${depart}`).openPopup();
            L.marker(to, {icon: destIcon}).addTo(map)
                .bindPopup(`<b>Destination</b><br>${destination}`);

            map.fitBounds([from, to], {padding: [60, 60]});

            // Add scale and attribution
            L.control.scale().addTo(map);
            L.control.attribution({prefix: false}).addAttribution('© OpenStreetMap contributors').addTo(map);

            // Show info
            document.getElementById('routeDistance').textContent = (route.distance / 1000).toFixed(2) + ' km';
            document.getElementById('routeDuration').textContent = (route.duration / 60).toFixed(0) + ' min';

            // Optional: Animate the route line (simple fade-in)
            polyline.setStyle({opacity: 0});
            setTimeout(() => polyline.setStyle({opacity: 0.85}), 300);

        } catch (e) {
            infoDiv.style.display = 'none';
            mapDiv.innerHTML = "<div style='color:#fff;text-align:center;padding:30px;'>Impossible d'afficher l'itinéraire.</div>";
            console.error(e);
        }
    }

    // Call the function with PHP values
    showReservationMap("<?= htmlspecialchars($reservation['depart']) ?>", "<?= htmlspecialchars($reservation['destination']) ?>");
    </script>
</body>
</html>