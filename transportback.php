<?php
session_start();
require_once '../../controller/transportcontroller.php';

$transportController = new TransportController();

// Initialize error array
$errors = [];

// Valid transport types
$validTypes = ['Voiture', 'Moto', 'Trottinette', 'Bus', 'Métro', 'Bicyclette'];

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'un transport
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $nom = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $prix = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $vitesse = floatval($_POST['vitesse'] ?? 0);
        $nb_places = intval($_POST['nb_places'] ?? 0);
        $batterie = floatval($_POST['batterie'] ?? 0);
        $imageName = trim($_POST['image'] ?? '');

        if (empty($nom) || strlen($nom) > 100) {
            $errors[] = "Le nom du transport est requis et doit être inférieur à 100 caractères.";
        }
        if (!in_array($type, $validTypes)) {
            $errors[] = "Type de transport invalide.";
        }
        if ($prix <= 0 || $prix > 10000) {
            $errors[] = "Le prix doit être un nombre positif inférieur à 10 000 €.";
        }
        if ($stock < 0 || $stock > 1000) {
            $errors[] = "Le stock doit être un nombre entre 0 et 1000.";
        }
        if ($vitesse <= 0 || $vitesse > 500) {
            $errors[] = "La vitesse doit être un nombre positif inférieur à 500 km/h.";
        }
        if ($nb_places <= 0 || $nb_places > 100) {
            $errors[] = "Le nombre de places doit être un nombre entre 1 et 100.";
        }
        if ($batterie <= 0 || $batterie > 1000) {
            $errors[] = "La capacité de la batterie doit être un nombre positif inférieur à 1000 kWh.";
        }
        if (empty($imageName)) {
            $imageName = 'default.jpg';
        }

        if (empty($errors)) {
            $result = $transportController->ajouterTransport($imageName, $nom, $type, $prix, $stock, $vitesse, $nb_places, $batterie);
            if ($result) {
                $_SESSION['success_message'] = "Transport ajouté ou stock mis à jour avec succès!";
            } else {
                $errors[] = "Erreur lors de l'ajout ou de la mise à jour du transport.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_messages'] = $errors;
        }
        header("Location: transportback.php");
        exit();
    }
    
    // Modification d'un transport
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $nom = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $prix = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $vitesse = floatval($_POST['vitesse'] ?? 0);
        $nb_places = intval($_POST['nb_places'] ?? 0);
        $batterie = floatval($_POST['batterie'] ?? 0);
        $imageName = trim($_POST['image'] ?? '');

        if ($id <= 0) {
            $errors[] = "ID de transport invalide.";
        }
        if (empty($nom) || strlen($nom) > 100) {
            $errors[] = "Le nom du transport est requis et doit être inférieur à 100 caractères.";
        }
        if (!in_array($type, $validTypes)) {
            $errors[] = "Type de transport invalide.";
        }
        if ($prix <= 0 || $prix > 10000) {
            $errors[] = "Le prix doit être un nombre positif inférieur à 10 000 €.";
        }
        if ($stock < 0 || $stock > 1000) {
            $errors[] = "Le stock doit être un nombre entre 0 et 1000.";
        }
        if ($vitesse <= 0 || $vitesse > 500) {
            $errors[] = "La vitesse doit être un nombre positif inférieur à 500 km/h.";
        }
        if ($nb_places <= 0 || $nb_places > 100) {
            $errors[] = "Le nombre de places doit être un nombre entre 1 et 100.";
        }
        if ($batterie <= 0 || $batterie > 1000) {
            $errors[] = "La capacité de la batterie doit être un nombre positif inférieur à 1000 kWh.";
        }
        if (empty($imageName)) {
            $imageName = 'default.jpg';
        }

        if (empty($errors)) {
            $result = $transportController->modifierTransport($id, $imageName, $nom, $type, $prix, $stock, $vitesse, $nb_places, $batterie);
            if ($result) {
                $_SESSION['success_message'] = "Transport modifié avec succès!";
            } else {
                $errors[] = "Erreur lors de la modification du transport.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_messages'] = $errors;
        }
        header("Location: transportback.php");
        exit();
    }

    // Modification d'une réservation
    if (isset($_POST['action']) && $_POST['action'] === 'update_reservation') {
        $id = intval($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $cin = trim($_POST['cin'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $depart = trim($_POST['depart'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $datedebut = trim($_POST['datedebut'] ?? '');
        $datefin = trim($_POST['datefin'] ?? '');
        $paiement = trim($_POST['paiement'] ?? '');

        $gouvernorats = [
            "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
            "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
            "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
        ];
        $validPaiements = ['carte', 'especes', 'virement'];

        if ($id <= 0) {
            $errors[] = "ID de réservation invalide.";
        }
        if (empty($nom) || strlen($nom) > 100 || !preg_match("/^[a-zA-Z\s\-']+$/", $nom)) {
            $errors[] = "Le nom est requis, doit être inférieur à 100 caractères et ne contenir que des lettres, espaces, tirets ou apostrophes.";
        }
        if (empty($prenom) || strlen($prenom) > 100 || !preg_match("/^[a-zA-Z\s\-']+$/", $prenom)) {
            $errors[] = "Le prénom est requis, doit être inférieur à 100 caractères et ne contenir que des lettres, espaces, tirets ou apostrophes.";
        }
        if (empty($cin) || !preg_match("/^\d{8}$/", $cin)) {
            $errors[] = "Le CIN doit être un numéro de 8 chiffres.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email est invalide.";
        }
        if (!in_array($depart, $gouvernorats)) {
            $errors[] = "Le gouvernorat de départ est invalide.";
        }
        if (!in_array($destination, $gouvernorats)) {
            $errors[] = "Le gouvernorat de destination est invalide.";
        }
        if ($depart === $destination) {
            $errors[] = "Le départ et la destination ne peuvent pas être identiques.";
        }
        if (empty($datedebut) || strtotime($datedebut) < strtotime(date('Y-m-d'))) {
            $errors[] = "La date de début est requise et ne peut pas être dans le passé.";
        }
        if (empty($datefin) || strtotime($datefin) <= strtotime($datedebut)) {
            $errors[] = "La date de fin doit être postérieure à la date de début.";
        }
        if (!in_array($paiement, $validPaiements)) {
            $errors[] = "Le mode de paiement est invalide.";
        }

        if (empty($errors)) {
            $result = $transportController->modifierReservation($id, $nom, $prenom, $cin, $email, $depart, $destination, $datedebut, $datefin, $paiement);
            if ($result) {
                $_SESSION['success_message'] = "Réservation modifiée avec succès!";
            } else {
                $errors[] = "Erreur lors de la modification de la réservation.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_messages'] = $errors;
        }
        header("Location: transportback.php");
        exit();
    }
}

// Suppression d'un transport
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        $_SESSION['error_messages'] = ["ID de transport invalide."];
    } else {
        $result = $transportController->supprimerTransport($id);
        if ($result) {
            $_SESSION['success_message'] = "Transport supprimé avec succès!";
        } else {
            $_SESSION['error_messages'] = ["Erreur lors de la suppression du transport."];
        }
    }
    header("Location: transportback.php");
    exit();
}

// Suppression d'une réservation
if (isset($_GET['action']) && $_GET['action'] === 'delete_reservation') {
    error_log("Reservation Delete Attempt: ID = " . ($_GET['id'] ?? 'not set'));
    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || intval($_GET['id']) <= 0) {
        $_SESSION['error_messages'] = ["ID de réservation invalide. Raw ID: " . ($_GET['id'] ?? 'not set')];
    } else {
        $id = intval($_GET['id']);
        error_log("Attempting to delete reservation with ID: $id");
        $result = $transportController->supprimerReservation($id);
        if ($result) {
            $_SESSION['success_message'] = "Réservation supprimée avec succès!";
        } else {
            $_SESSION['error_messages'] = ["Erreur lors de la suppression de la réservation. ID: $id"];
        }
    }
    header("Location: transportback.php");
    exit();
}

// Récupération des transports et réservations
$transports = $transportController->getAllTransports();
$reservations = $transportController->getAllReservations();

// Gestion des messages de session
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessages = $_SESSION['error_messages'] ?? [];
unset($_SESSION['success_message']);
unset($_SESSION['error_messages']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Gestion Transport</title>
    <link rel="stylesheet" href="../../styleback.css">
    <!-- Add Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="transportback.js" defer></script>
    <script src="tunisiaChoropleth.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="../../image/tunify.png" alt="tunify logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Admin Panel</p>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="transportback.php">
                        <i class="fas fa-car"></i>
                        <span>Transports</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-hotel"></i>
                        <span>Hébergements</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-utensils"></i>
                        <span>Restauration</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-comment-alt"></i>
                        <span>Réclamations</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <header class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Gestion des Transports</h2>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Rechercher un transport..." oninput="searchTransports()">
                    <i class="fas fa-search"></i>
                </div>
                <button class="add-btn" onclick="openModal('addTransportModal')">
                    <i class="fas fa-plus"></i> Ajouter Transport
                </button>
                <div class="user-profile">
                    <img src="../../image/tunify.png" alt="tunify logo">
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Transport Types Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="filterTransports('all')">Tous</button>
                <button class="tab-btn" onclick="filterTransports('voiture')">Voitures</button>
                <button class="tab-btn" onclick="filterTransports('moto')">Motos</button>
                <button class="tab-btn" onclick="filterTransports('Trottinette')">Trottinettes</button>
                <button class="tab-btn" onclick="filterTransports('bus')">Bus</button>
                <button class="tab-btn" onclick="filterTransports('metro')">Métro</button>
                <button class="tab-btn" onclick="filterTransports('bicyclette')">Bicyclettes</button>
            </div>

            <!-- Transport List -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Transports</h3>
                    <div class="card-actions">
                        <select class="filter-select" onchange="sortTransports(this.value)">
                            <option value="">Trier par</option>
                            <option value="name-asc">Nom (A-Z)</option>
                            <option value="name-desc">Nom (Z-A)</option>
                            <option value="price-asc">Prix croissant</option>
                            <option value="price-desc">Prix décroissant</option>
                        </select>
                        <button class="refresh-btn" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>IMAGE</th>
                                <th>NOM</th>
                                <th>TYPE</th>
                                <th>PRIX/JOUR</th>
                                <th>STOCK</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="transportTableBody">
                            <?php foreach ($transports as $transport): ?>
                            <tr data-type="<?= htmlspecialchars(strtolower($transport['type'])) ?>" data-name="<?= htmlspecialchars(strtolower($transport['nom'])) ?>" data-price="<?= $transport['prix'] ?>">
                                <td>#<?= htmlspecialchars($transport['id']) ?></td>
                                <td><img src="../../image/<?= htmlspecialchars($transport['image']) ?>" alt="<?= htmlspecialchars($transport['nom']) ?>" class="transport-img" onerror="this.src='../../image/default.jpg';"></td>
                                <td><?= htmlspecialchars($transport['nom']) ?></td>
                                <td><?= htmlspecialchars($transport['type']) ?></td>
                                <td><?= htmlspecialchars($transport['prix']) ?> €</td>
                                <td>
                                    <span class="status <?= $transport['stock'] > 0 ? 'active' : 'reserved' ?>">
                                        <?= htmlspecialchars($transport['stock']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn view" onclick="viewTransport(<?= $transport['id'] ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" onclick="editTransport(<?= $transport['id'] ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" onclick="confirmDelete('transport', <?= $transport['id'] ?>, '<?= htmlspecialchars($transport['nom']) ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservation List -->
            <div class="card">
                <div class="card-header">
                    <h3>Liste des Réservations</h3>
                    <div class="card-actions">
                        <select class="filter-select" onchange="sortReservations(this.value)">
                            <option value="">Trier par</option>
                            <option value="nom-asc">Nom (A-Z)</option>
                            <option value="nom-desc">Nom (Z-A)</option>
                            <option value="datedebut-asc">Date Début (Croissant)</option>
                            <option value="datedebut-desc">Date Début (Décroissant)</option>
                        </select>
                        <button class="refresh-btn" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>CLIENT</th>
                                <th>TRANSPORT</th>
                                <th>DÉPART</th>
                                <th>DESTINATION</th>
                                <th>DATE DÉBUT</th>
                                <th>DATE FIN</th>
                                <th>PAIEMENT</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="reservationTableBody">
                            <?php foreach ($reservations as $reservation): ?>
                            <tr data-nom="<?= htmlspecialchars(strtolower($reservation['nom'])) ?>" data-datedebut="<?= htmlspecialchars($reservation['datedebut']) ?>">
                                <td>#<?= htmlspecialchars($reservation['id']) ?></td>
                                <td><?= htmlspecialchars($reservation['nom'] . ' ' . $reservation['prenom']) ?></td>
                                <td>
                                    <?php
                                    $transport = $transportController->getTransportById($reservation['id_moyen']);
                                    echo htmlspecialchars($transport['nom'] ?? 'Inconnu');
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($reservation['depart']) ?></td>
                                <td><?= htmlspecialchars($reservation['destination']) ?></td>
                                <td><?= htmlspecialchars($reservation['datedebut']) ?></td>
                                <td><?= htmlspecialchars($reservation['datefin']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($reservation['paiement'])) ?></td>
                                <td>
                                    <button class="action-btn view" onclick="viewReservation(<?= $reservation['id'] ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" onclick="editReservation(<?= $reservation['id'] ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" onclick="confirmDelete('reservation', <?= $reservation['id'] ?>, '<?= htmlspecialchars($reservation['nom'] . ' ' . $reservation['prenom']) ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Map -->
            <div id="tunisiaChoroplethMap" style="height: 400px; margin: 30px 0;"></div>


        </div>
    </div>

    <!-- Add Transport Modal -->
    <div class="modal action-modal" id="addTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addTransportModal')">×</span>
            <h2>Ajouter un Nouveau Transport</h2>
            <form class="transport-form" id="addTransportForm" method="POST" novalidate>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="image" id="addTransportImageHidden">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Type de Transport</label>
                        <select name="type" id="addTransportType" onchange="updateTransportImage('add')">
                            <option value="">Sélectionner un type</option>
                            <option value="Voiture">Voiture</option>
                            <option value="Moto">Moto</option>
                            <option value="Trottinette">Trottinette</option>
                            <option value="Bus">Bus</option>
                            <option value="Métro">Métro</option>
                            <option value="Bicyclette">Bicyclette</option>
                        </select>
                        <span class="error-message" id="addTransportTypeError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Nom du Transport</label>
                        <input type="text" name="name" id="addTransportName" placeholder="Ex: Voiture">
                        <span class="error-message" id="addTransportNameError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Prix par jour (€)</label>
                        <input type="text" name="price" id="addTransportPrice" placeholder="Ex: 2250">
                        <span class="error-message" id="addTransportPriceError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="text" name="stock" id="addTransportStock" placeholder="Ex: 5">
                        <span class="error-message" id="addTransportStockError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Vitesse (km/h)</label>
                        <input type="text" name="vitesse" id="addTransportVitesse" placeholder="Ex: 120.50">
                        <span class="error-message" id="addTransportVitesseError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de places</label>
                        <input type="text" name="nb_places" id="addTransportNbPlaces" placeholder="Ex: 4">
                        <span class="error-message" id="addTransportNbPlacesError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Batterie (kWh)</label>
                        <input type="text" name="batterie" id="addTransportBatterie" placeholder="Ex: 100.00">
                        <span class="error-message" id="addTransportBatterieError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Image du Transport</label>
                        <div class="transport-image-preview">
                            <img id="addTransportImagePreview" src="" alt="Aperçu de l'image" style="display: none;" onerror="this.src='../../image/default.jpg';">
                            <p id="addTransportImageText">Sélectionnez un type pour voir l'image</p>
                        </div>
                        <span class="error-message" id="addTransportImageError"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('addTransportModal')">Annuler</button>
                    <button type="submit" class="submit-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Transport Modal -->
    <div class="modal" id="viewTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('viewTransportModal')">×</span>
            <h2 id="viewTransportTitle">Détails du Transport</h2>
            <div class="transport-detail-content" id="transportDetailContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Edit Transport Modal -->
    <div class="modal action-modal" id="editTransportModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editTransportModal')">×</span>
            <h2>Modifier le Transport</h2>
            <form class="transport-form" id="editTransportForm" method="POST" novalidate>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editTransportId">
                <input type="hidden" name="image" id="editTransportImageHidden">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Type de Transport</label>
                        <select name="type" id="editTransportType" onchange="updateTransportImage('edit')">
                            <option value="Voiture">Voiture</option>
                            <option value="Moto">Moto</option>
                            <option value="Trottinette">Trottinette</option>
                            <option value="Bus">Bus</option>
                            <option value="Métro">Métro</option>
                            <option value="Bicyclette">Bicyclette</option>
                        </select>
                        <span class="error-message" id="editTransportTypeError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Nom du Transport</label>
                        <input type="text" name="name" id="editTransportName">
                        <span class="error-message" id="editTransportNameError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Prix par jour (€)</label>
                        <input type="text" name="price" id="editTransportPrice">
                        <span class="error-message" id="editTransportPriceError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="text" name="stock" id="editTransportStock">
                        <span class="error-message" id="editTransportStockError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Vitesse (km/h)</label>
                        <input type="text" name="vitesse" id="editTransportVitesse">
                        <span class="error-message" id="editTransportVitesseError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de places</label>
                        <input type="text" name="nb_places" id="editTransportNbPlaces">
                        <span class="error-message" id="editTransportNbPlacesError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Batterie (kWh)</label>
                        <input type="text" name="batterie" id="editTransportBatterie">
                        <span class="error-message" id="editTransportBatterieError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Image du Transport</label>
                        <div class="transport-image-preview">
                            <img id="editTransportImagePreview" src="" alt="Aperçu de l'image" style="display: none;" onerror="this.src='../../image/default.jpg';">
                            <p id="editTransportImageText">Sélectionnez un type pour voir l'image</p>
                        </div>
                        <span class="error-message" id="editTransportImageError"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('editTransportModal')">Annuler</button>
                    <button type="submit" class="submit-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Reservation Modal -->
    <div class="modal" id="viewReservationModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('viewReservationModal')">×</span>
            <h2 id="viewReservationTitle">Détails de la Réservation</h2>
            <div class="reservation-detail-content" id="reservationDetailContent">
                <!-- Content loaded via AJAX -->
            </div>
            <!-- Add map and info here -->
            <div id="reservationMap" style="height: 300px; margin-top: 20px; display: none;"></div>
            <div id="routeInfo" style="margin-top: 10px; display: none;">
                <strong>Distance estimée :</strong> <span id="routeDistance"></span><br>
                <strong>Durée estimée :</strong> <span id="routeDuration"></span>
            </div>
        </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div class="modal action-modal" id="editReservationModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editReservationModal')">×</span>
            <h2>Modifier la Réservation</h2>
            <form class="reservation-form" id="editReservationForm" method="POST" novalidate>
                <input type="hidden" name="action" value="update_reservation">
                <input type="hidden" name="id" id="editReservationId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" id="editReservationNom">
                        <span class="error-message" id="editReservationNomError"></span>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" id="editReservationPrenom">
                        <span class="error-message" id="editReservationPrenomError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>CIN</label>
                        <input type="text" name="cin" id="editReservationCin">
                        <span class="error-message" id="editReservationCinError"></span>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="editReservationEmail">
                        <span class="error-message" id="editReservationEmailError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Départ</label>
                        <select name="depart" id="editReservationDepart">
                            <option value="">Sélectionner</option>
                            <?php
                            $gouvernorats = [
                                "Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan",
                                "Kasserine", "Kébili", "Le Kef", "Mahdia", "Manouba", "Médenine", "Monastir", "Nabeul",
                                "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"
                            ];
                            foreach ($gouvernorats as $gouvernorat) {
                                echo "<option value=\"$gouvernorat\">$gouvernorat</option>";
                            }
                            ?>
                        </select>
                        <span class="error-message" id="editReservationDepartError"></span>
                    </div>
                    <div class="form-group">
                        <label>Destination</label>
                        <select name="destination" id="editReservationDestination">
                            <option value="">Sélectionner</option>
                            <?php foreach ($gouvernorats as $gouvernorat) {
                                echo "<option value=\"$gouvernorat\">$gouvernorat</option>";
                            } ?>
                        </select>
                        <span class="error-message" id="editReservationDestinationError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date de Début</label>
                        <input type="date" name="datedebut" id="editReservationDateDebut">
                        <span class="error-message" id="editReservationDateDebutError"></span>
                    </div>
                    <div class="form-group">
                        <label>Date de Fin</label>
                        <input type="date" name="datefin" id="editReservationDateFin">
                        <span class="error-message" id="editReservationDateFinError"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Mode de Paiement</label>
                        <select name="paiement" id="editReservationPaiement">
                            <option value="">Sélectionner</option>
                            <option value="carte">Carte</option>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement</option>
                        </select>
                        <span class="error-message" id="editReservationPaiementError"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('editReservationModal')">Annuler</button>
                    <button type="submit" class="submit-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteConfirmationModal">
        <div class="modal-content delete-modal-content">
            <span class="close-modal" onclick="closeModal('deleteConfirmationModal')">×</span>
            <div class="delete-modal-header">
                <i class="fas fa-exclamation-triangle delete-icon"></i>
                <h2>Confirmer la Suppression</h2>
            </div>
            <p id="deleteConfirmationMessage">Voulez-vous vraiment supprimer cet élément ? Cette action est irréversible.</p>
            <div class="delete-modal-actions">
                <button class="cancel-btn delete-cancel-btn" onclick="closeModal('deleteConfirmationModal')">Annuler</button>
                <button class="delete-btn" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>

    <!-- Messages d'alerte -->
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($successMessage) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errorMessages)): ?>
    <?php foreach ($errorMessages as $errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($errorMessage) ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Call this after the page loads
        showTunisiaChoroplethMap('tunisiaChoroplethMap');
    </script>
</body>
</html>