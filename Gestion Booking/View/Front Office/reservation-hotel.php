<?php
// reservation-hotel.php - Formulaire de réservation (Dynamique)

// --- Inclusion des fichiers ---
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controllor/ReservationSejourController.php';
require_once __DIR__ . '/../../Model/ReservationSejour.php';
require_once __DIR__ . '/../../Controllor/Photo.php';
require_once __DIR__ . '/../../Controllor/ProprieteController.php';
// --- Fin Inclusion ---

// --- Gestion Session ---
session_start();
$utilisateur_connecte = isset($_SESSION['user_id']);
$id_utilisateur = $_SESSION['user_id'] ?? null;
$nom_utilisateur = $_SESSION['user_nom'] ?? 'Utilisateur'; // Default name if not logged in
$email_utilisateur = $_SESSION['user_email'] ?? null;
// --- Fin Gestion Session ---

// Initialisation
$proprieteDetails = null;
$photo_principale_src = "/api/placeholder/400/250/1c1c1c/c9a86c?text=Image+Indisponible"; // Default image
$message = '';
$error = '';
$form_data = $_POST; // Keep form data on POST, even if error

try {
    $db = config::getConnexion();
    $reservationController = new ReservationSejourController($db);
    $photoController = new Photo($db);
    $proprieteController = new ProprieteController($db);

    // --- Récupérer les détails par nom ---
    $hotelNomGET = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($hotelNomGET)) {
        $error = "Aucun hôtel spécifié.";
    } else {
        $proprieteDetails = $proprieteController->getHotelByNom($hotelNomGET);

        if (!$proprieteDetails) {
            $error = "Hôtel non trouvé ou invalide.";
             error_log("Tentative d'accès à un hôtel inexistant : " . $hotelNomGET);
        } else {
            // Récupérer photo
             if (!empty($proprieteDetails['photo_nom_associe'])) {
                 $photos_logement = $photoController->getPhotosByNom($proprieteDetails['photo_nom_associe']);
                 if (!empty($photos_logement) && isset($photos_logement[0]['image_base64'])) {
                     $imageData = $photos_logement[0]['image_base64'];
                     // Ensure base64 prefix
                     if (strpos($imageData, 'data:image') !== 0) {
                        // Attempt to determine type from data if possible, otherwise default
                        // Simple check for common types, might need improvement
                        if (strpos(substr($imageData, 0, 20), 'iVBORw0KGgo') === 0) $mime = 'png';
                        elseif (strpos(substr($imageData, 0, 20), '/9j/') === 0) $mime = 'jpeg';
                        elseif (strpos(substr($imageData, 0, 20), 'R0lGOD') === 0) $mime = 'gif';
                        else $mime = 'jpeg'; // Default to JPEG
                        $imageData = 'data:image/' . $mime . ';base64,' . $imageData;
                     }
                     $photo_principale_src = $imageData;
                 }
            }
        }
    }
    // --- Fin Récupération Détails ---

} catch (Exception $e) {
    error_log("Erreur Init/Fetch dans " . basename(__FILE__) . ": " . $e->getMessage());
    $error = "Erreur critique lors du chargement des informations de l'hôtel.";
    $proprieteDetails = null;
}

// --- Traitement du formulaire (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proprieteDetails) {
    // Récupérer données POST
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $nb_personnes = $_POST['nb_personnes'] ?? '';
    $type_chambre = $_POST['type_chambre'] ?? '';
    $formule = $_POST['formule'] ?? ''; // Specific to resorts maybe
    $demandes_speciales = $_POST['demandes_speciales'] ?? '';
    $hotelNom_post = $_POST['hotel_nom'] ?? ''; // From hidden field

    $current_error = ''; // Error specific to POST validation

    // Validations (côté serveur pour sécurité)
    if (empty($date_debut) || empty($date_fin) || empty($nb_personnes) || empty($type_chambre)) {
        $current_error = "Les dates, le nombre de personnes et le type de chambre sont requis.";
    } elseif (strtotime($date_fin) <= strtotime($date_debut)) {
        $current_error = "La date de départ doit être postérieure à la date d'arrivée.";
    } elseif (strtotime($date_debut) < strtotime(date('Y-m-d'))) {
        $current_error = "La date d'arrivée ne peut pas être dans le passé.";
    } elseif (!filter_var($nb_personnes, FILTER_VALIDATE_INT) || $nb_personnes <= 0) {
        $current_error = "Le nombre de personnes doit être un entier positif.";
    } elseif ($proprieteDetails['nom_hotel'] !== $hotelNom_post) {
         $current_error = "Erreur : Incohérence dans les informations de l'hôtel. Veuillez réessayer.";
         error_log("Incohérence hotel POST: DB(".$proprieteDetails['nom_hotel'].") vs POST(".$hotelNom_post.")");
    }
    // Add other necessary validations (e.g., phone format if collected)

    if (empty($current_error)) {
        try {
            $nouvelleReservation = new ReservationSejour();
            $nouvelleReservation->setIdUtilisateur($id_utilisateur);
            $nouvelleReservation->setNomUtilisateur($nom_utilisateur);
            $nouvelleReservation->setEmailUtilisateur($email_utilisateur);
            $nouvelleReservation->setTypeLogement('hotel');
            $nouvelleReservation->setNomLogement($proprieteDetails['nom_hotel']); // Use DB name
            $nouvelleReservation->setDateDebut($date_debut);
            $nouvelleReservation->setDateFin($date_fin);
            $nouvelleReservation->setNbPersonnes($nb_personnes);
            // Combine preferences into special requests
            $demandes_combinees = "Type Chambre: " . strip_tags($type_chambre);
            // Check if hotel type is 'resort' to include formula (adjust 'resort' type name if needed)
            if (isset($proprieteDetails['type_hotel']) && strtolower($proprieteDetails['type_hotel']) == 'résort 5*' && !empty($formule)) {
                $demandes_combinees .= " | Formule: " . strip_tags($formule);
            }
            if (!empty($demandes_speciales)) { $demandes_combinees .= " | Autres: " . strip_tags($demandes_speciales); }
            $nouvelleReservation->setDemandesSpeciales($demandes_combinees);
            $nouvelleReservation->setStatut('En attente'); // Initial status

            if ($reservationController->ajouterReservation($nouvelleReservation)) {
                $message = "Votre demande de réservation pour l'hôtel '" . htmlspecialchars($proprieteDetails['nom_hotel']) . "' a été enregistrée avec succès. Nous vous contacterons pour confirmer.";
                $form_data = []; // Clear form data on success
                $proprieteDetails = null; // Hide form after success
            } else {
                // Check controller/model logs for specific PDO errors
                $error = "Une erreur serveur s'est produite lors de l'enregistrement de votre réservation. Veuillez réessayer.";
            }
        } catch (Exception $e) {
            $error = "Une exception serveur s'est produite lors du traitement de votre demande.";
            error_log("Exception dans " . basename(__FILE__) . " POST: " . $e->getMessage());
        }
    } else {
        $error = $current_error; // Assign POST validation error
    }
}
// --- Fin Traitement POST ---
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réservation <?php echo $proprieteDetails ? htmlspecialchars($proprieteDetails['nom_hotel']) : 'Hôtel'; ?></title>
     <link rel="stylesheet" href="../../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
        /* Styles CSS (identiques aux autres formulaires de réservation) */
        .form-container {
            display: flex;
            gap: 40px;
            background-color: rgba(28, 28, 28, 0.7);
            border: 1px solid rgba(201, 168, 108, 0.2);
            border-radius: 8px;
            padding: 40px;
            max-width: 1100px;
            margin: 40px auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .form-column { flex: 1; }
        .logement-info-box {
            background-color: rgba(17, 17, 17, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.1);
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .logement-info-box h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            color: var(--gold-primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(201, 168, 108, 0.3);
        }
        .logement-info-box p {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(248, 245, 235, 0.8);
            margin-bottom: 8px;
        }
        .logement-info-box p strong {
            color: var(--light-text);
            font-weight: 600;
            min-width: 130px; /* Ensure labels align */
            display: inline-block;
        }
        .logement-info-box img {
            width:100%;
            margin-top:15px;
            border-radius:5px;
            border: 1px solid rgba(201, 168, 108, 0.2);
            object-fit: cover;
            height: 250px; /* Fixed height for consistency */
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 15px;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert i { font-size: 1.2em; }
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border-color: rgba(76, 175, 80, 0.4);
            color: #98FB98; /* Light green */
        }
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border-color: rgba(244, 67, 54, 0.4);
            color: #FFA07A; /* Light salmon */
        }
        .form-header { margin-bottom: 30px; }
        .form-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold-primary);
            font-size: 26px;
            margin-bottom: 10px;
        }
        .form-header p { color: rgba(248, 245, 235, 0.8); }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label {
            color: var(--gold-light);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(17, 17, 17, 0.7);
            border: 1px solid rgba(201, 168, 108, 0.3);
            color: var(--light-text);
            border-radius: 4px;
            font-size: 15px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(201, 168, 108, 0.2);
        }
        /* Style for select dropdown arrow */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23c9a86c' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px 12px;
            padding-right: 40px; /* Make space for the arrow */
        }
        /* Style for options in dropdown */
        select.form-control option {
            background-color: var(--dark-bg);
            color: var(--light-text);
        }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .contact-btn {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            border-radius: 5px;
            text-align: center; /* Ensure text is centered */
            display: inline-block; /* For alignment if needed */
            text-decoration: none; /* Remove underline if used as link */
        }
        .contact-btn:hover {
            background-color: var(--light-text);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 992px) { .form-container { flex-direction: column; } }
        @media (max-width: 768px) {
            .form-row { flex-direction: column; gap: 0; }
            .form-group { margin-bottom: 20px; }
        }

        /* --- Styles Popup de Validation --- */
        .custom-popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.75); z-index: 3000;
            display: flex; justify-content: center; align-items: center;
            backdrop-filter: blur(4px); padding: 20px;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease-out;
        }
        .custom-popup-overlay.visible { opacity: 1; pointer-events: auto; }
        .custom-popup-content {
            background-color: var(--dark-bg); padding: 35px 45px;
            border-radius: 8px; border: 1px solid var(--gold-primary);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6); text-align: center;
            max-width: 480px; width: 95%; position: relative;
            transform: scale(0.95); opacity: 0;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease-out;
        }
        .custom-popup-overlay.visible .custom-popup-content { transform: scale(1); opacity: 1; }
        .custom-popup-close {
            position: absolute; top: 12px; right: 18px; font-size: 28px;
            font-weight: bold; line-height: 1; color: var(--gold-light);
            cursor: pointer; transition: color 0.3s ease, transform 0.3s ease;
        }
        .custom-popup-close:hover { color: #fff; transform: rotate(90deg) scale(1.1); }
        .custom-popup-content h3 {
            font-family: 'Cinzel', serif; color: var(--gold-primary); margin-top: 0;
            margin-bottom: 20px; font-size: 22px; font-weight: 600;
        }
        .custom-popup-content p {
            color: rgba(248, 245, 235, 0.9); font-size: 16px;
            line-height: 1.7; margin-bottom: 30px;
        }
        .popup-ok-button {
            background-color: var(--gold-primary); color: var(--darker-bg); border: none;
            padding: 12px 35px; border-radius: 5px; cursor: pointer; font-weight: 600;
            font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }
        .popup-ok-button:hover { background-color: var(--gold-light); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); }
        /* --- Fin Styles Popup --- */

     </style>
</head>
<body>
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo"> <div class="logo-text">
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
             <?php if($utilisateur_connecte): ?>
                 <a href="logout.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Déconnexion</a>
             <?php else: ?>
                 <a href="login.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Connexion</a>
             <?php endif; ?>
        </div>
    </header>

    <section class="contact-section" style="padding-top: 120px; padding-bottom: 80px; background: var(--darker-bg);">
        <div class="container">
             <?php if(!empty($message)): ?>
                 <div class="alert alert-success" style="max-width: 1100px; margin: 20px auto;"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                 <div style="text-align: center; margin-top: 30px;">
                    <a href="reservation.php" class="contact-btn">
                         <i class="fas fa-arrow-left"></i> Retour aux propriétés
                    </a>
                 </div>
            <?php endif; ?>
            <?php if(!empty($error) && !$proprieteDetails && empty($message)): // Only show initial loading error ?>
                 <div class="alert alert-danger" style="max-width: 1100px; margin: 20px auto;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                 <div style="text-align: center; margin-top: 20px;">
                    <a href="reservation.php" class="contact-btn">Retour aux propriétés</a>
                 </div>
            <?php endif; ?>

            <?php if($proprieteDetails && empty($message)): ?>
            <div class="form-container">
                 <div class="form-column">
                     <div class="form-header">
                        <h2>Informations Hôtel</h2>
                     </div>
                    <div class="logement-info-box">
                        <h3><?php echo htmlspecialchars($proprieteDetails['nom_hotel']); ?></h3>
                        <p><strong>Type :</strong> <?php echo htmlspecialchars($proprieteDetails['type_hotel']); ?></p>
                        <?php if($proprieteDetails['classement_etoiles']): ?><p><strong>Classement :</strong> <?php echo htmlspecialchars($proprieteDetails['classement_etoiles']); ?> *</p><?php endif; ?>
                        <?php if($proprieteDetails['adresse']): ?><p><strong>Adresse :</strong> <?php echo htmlspecialchars($proprieteDetails['adresse']); ?></p><?php endif; ?>
                        <?php if($proprieteDetails['services_cles']): ?><p><strong>Services Clés :</strong> <?php echo htmlspecialchars($proprieteDetails['services_cles']); ?></p><?php endif; ?>
                        <?php if($proprieteDetails['prix_nuit_apd']): ?><p><strong>Prix / Nuit (àpd) :</strong> <?php echo number_format($proprieteDetails['prix_nuit_apd'], 0, ',', ' '); ?> DT</p><?php endif; ?>
                         <img src="<?php echo htmlspecialchars($photo_principale_src); ?>"
                              alt="Image <?php echo htmlspecialchars($proprieteDetails['nom_hotel']); ?>"
                              onerror="this.onerror=null; this.src='/api/placeholder/400/250/1c1c1c/c9a86c?text=Image+Erreur';">
                         <?php if($proprieteDetails['description']): ?>
                            <p style="margin-top: 15px;"><strong>Description :</strong><br><?php echo nl2br(htmlspecialchars($proprieteDetails['description'])); ?></p>
                         <?php endif; ?>
                    </div>
                    <?php if ($utilisateur_connecte): ?>
                     <div class="logement-info-box" style="margin-top: 20px;">
                        <h3>Vos Informations</h3>
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($nom_utilisateur); ?></p>
                        <?php if ($email_utilisateur): ?> <p><strong>Email :</strong> <?php echo htmlspecialchars($email_utilisateur); ?></p> <?php endif; ?>
                        <p><small>(Connecté)</small></p>
                     </div>
                    <?php else: ?>
                        <div class="logement-info-box" style="margin-top: 20px;">
                            <p><small>Vous réservez en tant qu'invité. <a href="login.php" style="color: var(--gold-primary);">Connectez-vous</a> ou <a href="register.php" style="color: var(--gold-primary);">créez un compte</a> pour suivre vos réservations.</small></p>
                        </div>
                    <?php endif; ?>
                </div>

                 <div class="form-column">
                    <div class="form-header">
                        <h2>Réserver votre Séjour</h2>
                        <p>Indiquez vos dates et préférences pour cet hôtel.</p>
                    </div>
                     <?php if(!empty($error) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form id="reservationHotelForm"
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?nom=<?php echo urlencode($proprieteDetails['nom_hotel']); ?>"
                          method="POST"
                          onsubmit="return validateReservationHotel();">

                        <input type="hidden" name="hotel_nom" value="<?php echo htmlspecialchars($proprieteDetails['nom_hotel']); ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_debut">Date d'arrivée *</label>
                                <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?php echo htmlspecialchars($form_data['date_debut'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_fin">Date de départ *</label>
                                <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?php echo htmlspecialchars($form_data['date_fin'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                             <div class="form-group">
                                 <label for="nb_personnes">Nombre de personnes *</label>
                                 <input type="text" inputmode="numeric" pattern="[0-9]*" id="nb_personnes" name="nb_personnes" class="form-control" placeholder="Ex: 2" value="<?php echo htmlspecialchars($form_data['nb_personnes'] ?? ''); ?>">
                             </div>
                             <div class="form-group">
                                <label for="type_chambre">Type de chambre *</label>
                                <select id="type_chambre" name="type_chambre" class="form-control">
                                    <option value="" disabled <?php echo empty($form_data['type_chambre']) ? 'selected' : ''; ?>>-- Sélectionnez --</option>
                                    <?php
                                    // Options génériques, pourraient être dynamiques si stockées en BD
                                    $options_chambre = ["Standard", "Supérieure", "Vue Mer", "Vue Jardin", "Suite Junior", "Suite Deluxe", "Chambre Familiale", "Bungalow"];
                                    foreach ($options_chambre as $opt) {
                                        $selected = (isset($form_data['type_chambre']) && $opt === $form_data['type_chambre']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($opt) . "\" $selected>" . htmlspecialchars($opt) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <?php if (isset($proprieteDetails['type_hotel']) && strtolower($proprieteDetails['type_hotel']) == 'résort 5*'): // Adjust type name if needed ?>
                         <div class="form-group">
                            <label for="formule">Formule de séjour (optionnel)</label>
                            <select id="formule" name="formule" class="form-control">
                                <option value="" <?php echo empty($form_data['formule']) ? 'selected' : ''; ?>>Aucune (Chambre seule/PDJ par défaut)</option>
                                <option value="petit-dejeuner" <?php echo (isset($form_data['formule']) && $form_data['formule'] == 'petit-dejeuner' ? 'selected' : ''); ?>>Petit-déjeuner inclus</option>
                                <option value="demi-pension" <?php echo (isset($form_data['formule']) && $form_data['formule'] == 'demi-pension' ? 'selected' : ''); ?>>Demi-pension</option>
                                <option value="pension-complete" <?php echo (isset($form_data['formule']) && $form_data['formule'] == 'pension-complete' ? 'selected' : ''); ?>>Pension complète</option>
                                <option value="all-inclusive" <?php echo (isset($form_data['formule']) && $form_data['formule'] == 'all-inclusive' ? 'selected' : ''); ?>>All Inclusive</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="demandes_speciales">Demandes spéciales (Optionnel)</label>
                            <textarea id="demandes_speciales" name="demandes_speciales" class="form-control" rows="3" placeholder="Étage élevé, lit bébé, heure d'arrivée tardive..."><?php echo htmlspecialchars($form_data['demandes_speciales'] ?? ''); ?></textarea>
                         </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="contact-btn">Envoyer la demande de réservation</button>
                        </div>
                    </form>
                 </div> </div> <?php endif; ?> </div> </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                 <div class="footer-about">
                     <div class="logo"> <img src="logo.png" alt="TuniFy Logo"> <div class="logo-text"> <h1>TuniFy</h1> <p>Village</p> </div> </div>
                     <p>TuniFy Village est l'incarnation du luxe...</p>
                 </div>
                 <div class="footer-links"> <h3 class="footer-heading">Liens Rapides</h3> <ul>...</ul> </div>
                 <div class="footer-links"> <h3 class="footer-heading">Nos Services</h3> <ul>...</ul> </div>
                 <div class="footer-contact"> <h3 class="footer-heading">Contact</h3> <p>...</p> </div>
             </div>
            <div class="footer-bottom">
                <div class="footer-copyright">
                     &copy; <?php echo date("Y"); ?> TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a>
                 </div>
             </div>
         </div>
    </footer>

    <div class="custom-popup-overlay" id="validationPopupOverlay">
        <div class="custom-popup-content">
            <span class="custom-popup-close" id="validationPopupClose">&times;</span>
            <h3 id="validationPopupTitle">Erreur de Validation</h3>
            <p id="validationPopupMessage">Message d'erreur détaillé ici.</p>
            <button class="popup-ok-button" id="validationPopupOk">OK</button>
        </div>
    </div>
    <script>
        // --- Nouvelle fonction pour afficher la popup personnalisée ---
        function showCustomPopup(title, message) {
            const popupOverlay = document.getElementById('validationPopupOverlay');
            const popupTitle = document.getElementById('validationPopupTitle');
            const popupMessage = document.getElementById('validationPopupMessage');
            const closeButton = document.getElementById('validationPopupClose');
            const okButton = document.getElementById('validationPopupOk');

            if (!popupOverlay || !popupTitle || !popupMessage || !closeButton || !okButton) {
                console.error("Éléments du popup de validation introuvables ! Fallback sur alert.");
                alert(title + "\n" + message); // Utiliser alert comme secours
                return;
            }

            popupTitle.textContent = title;
            popupMessage.textContent = message; // Utilisez textContent pour la sécurité

            // Fonction pour fermer la popup
            const closePopupHandler = () => {
                popupOverlay.classList.remove('visible');
                // Important : Supprimer les écouteurs pour éviter les doublons
                closeButton.removeEventListener('click', closePopupHandler);
                okButton.removeEventListener('click', closePopupHandler);
                // On enlève aussi celui de l'overlay s'il est ajouté
                // popupOverlay.removeEventListener('click', overlayClickHandler);
            };

            // const overlayClickHandler = (event) => {
            //      if (event.target === popupOverlay) {
            //          closePopupHandler();
            //      }
            //  };

            // Ajouter les écouteurs d'événements pour fermer
            closeButton.addEventListener('click', closePopupHandler);
            okButton.addEventListener('click', closePopupHandler);
            // popupOverlay.addEventListener('click', overlayClickHandler); // Décommentez pour fermer en cliquant sur l'overlay

            // Afficher la popup
            popupOverlay.classList.add('visible');
        }

        // --- Fonction de validation MODIFIÉE ---
        function validateReservationHotel() {
            const dateDebutInput = document.getElementById('date_debut');
            const dateFinInput = document.getElementById('date_fin');
            const nbPersonnesInput = document.getElementById('nb_personnes');
            const typeChambreInput = document.getElementById('type_chambre');

            const dateDebut = dateDebutInput.value;
            const dateFin = dateFinInput.value;
            const nbPersonnesStr = nbPersonnesInput.value.trim();
            const typeChambre = typeChambreInput.value;

            if (dateDebut === '') {
                showCustomPopup("Date Manquante", "Veuillez sélectionner une date d'arrivée.");
                dateDebutInput.focus();
                return false;
            }
            if (dateFin === '') {
                showCustomPopup("Date Manquante", "Veuillez sélectionner une date de départ.");
                dateFinInput.focus();
                return false;
            }

            const today = new Date();
            const selectedDateDebut = new Date(dateDebut);
            const selectedDateFin = new Date(dateFin);
            today.setHours(0, 0, 0, 0); // Compare date parts only

            if (selectedDateDebut < today) {
                showCustomPopup("Date Invalide", "La date d'arrivée ne peut pas être dans le passé.");
                dateDebutInput.focus();
                return false;
            }
             if (selectedDateFin <= selectedDateDebut) {
                showCustomPopup("Date Invalide", "La date de départ doit être postérieure à la date d'arrivée.");
                dateFinInput.focus();
                return false;
            }

            if (nbPersonnesStr === '') {
                 showCustomPopup("Information Requise", "Veuillez indiquer le nombre de personnes.");
                 nbPersonnesInput.focus();
                 return false;
            }
            // Validate positive integer
            const nbPersonnes = parseInt(nbPersonnesStr, 10);
             if (isNaN(nbPersonnes) || nbPersonnes <= 0 || nbPersonnesStr !== String(nbPersonnes)) {
                 showCustomPopup("Nombre Invalide", "Veuillez entrer un nombre de personnes valide (entier positif).");
                 nbPersonnesInput.focus();
                 return false;
             }

             if (typeChambre === '') {
                 showCustomPopup("Sélection Requise", "Veuillez sélectionner un type de chambre.");
                 typeChambreInput.focus();
                 return false;
             }

            // If all validations pass
            return true; // Allow form submission
        }

         // --- Code existant pour la gestion des dates min ---
         document.addEventListener('DOMContentLoaded', function() {
             const dateDebutInput = document.getElementById('date_debut');
             const dateFinInput = document.getElementById('date_fin');

             if (dateDebutInput && dateFinInput) {
                 // Set min date for arrival to today
                 const todayString = new Date().toISOString().split('T')[0];
                 dateDebutInput.min = todayString;

                 dateDebutInput.addEventListener('change', function() {
                     const debutVal = this.value;
                     if (debutVal) {
                         const debutDate = new Date(debutVal);
                         const lendemain = new Date(debutDate);
                         lendemain.setDate(lendemain.getDate() + 1); // Day after arrival
                         const lendemainString = lendemain.toISOString().split('T')[0];
                         dateFinInput.min = lendemainString;
                         // Optional: clear end date if it becomes invalid
                         if (dateFinInput.value && new Date(dateFinInput.value) < lendemain) {
                             dateFinInput.value = '';
                         }
                     } else {
                         // If arrival date is cleared, reset min end date
                         const defaultLendemain = new Date();
                         defaultLendemain.setDate(defaultLendemain.getDate() + 1);
                         dateFinInput.min = defaultLendemain.toISOString().split('T')[0];
                     }
                 });

                 // Set initial min date for end date (day after today or day after existing start date)
                  const initialLendemain = new Date();
                  initialLendemain.setDate(initialLendemain.getDate() + 1);
                  if(dateDebutInput.value) {
                      const currentDebut = new Date(dateDebutInput.value);
                      const currentLendemain = new Date(currentDebut);
                      currentLendemain.setDate(currentLendemain.getDate() + 1);
                      dateFinInput.min = currentLendemain.toISOString().split('T')[0];
                  } else {
                      dateFinInput.min = initialLendemain.toISOString().split('T')[0];
                  }
             }
         });
    </script>
</body>
</html>