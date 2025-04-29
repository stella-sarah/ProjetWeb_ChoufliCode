<?php
// reservation-maisonhote.php - Formulaire de réservation (Dynamique)

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
$capacite_max_js = 0; // For JS validation

try {
    $db = config::getConnexion();
    $reservationController = new ReservationSejourController($db);
    $photoController = new Photo($db);
    $proprieteController = new ProprieteController($db);

    // --- Récupérer les détails par nom ---
    $maisonNomGET = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($maisonNomGET)) {
        $error = "Aucune maison d'hôte spécifiée.";
    } else {
        $proprieteDetails = $proprieteController->getMaisonHoteByNom($maisonNomGET);

        if (!$proprieteDetails) {
            $error = "Maison d'hôte non trouvée ou invalide.";
            error_log("Tentative d'accès à une maison inexistante : " . $maisonNomGET);
        } else {
            // Récupérer photo
            if (!empty($proprieteDetails['photo_nom_associe'])) {
                 $photos_logement = $photoController->getPhotosByNom($proprieteDetails['photo_nom_associe']);
                 if (!empty($photos_logement) && isset($photos_logement[0]['image_base64'])) {
                     $imageData = $photos_logement[0]['image_base64'];
                     // Ensure base64 prefix
                     if (strpos($imageData, 'data:image') !== 0) {
                        if (strpos(substr($imageData, 0, 20), 'iVBORw0KGgo') === 0) $mime = 'png';
                        elseif (strpos(substr($imageData, 0, 20), '/9j/') === 0) $mime = 'jpeg';
                        elseif (strpos(substr($imageData, 0, 20), 'R0lGOD') === 0) $mime = 'gif';
                        else $mime = 'jpeg';
                        $imageData = 'data:image/' . $mime . ';base64,' . $imageData;
                     }
                     $photo_principale_src = $imageData;
                 }
            }
            $capacite_max_js = $proprieteDetails['capacite_personnes'] ?? 0; // For JS
        }
    }
    // --- Fin Récupération Détails ---

} catch (Exception $e) {
    error_log("Erreur Init/Fetch dans " . basename(__FILE__) . ": " . $e->getMessage());
    $error = "Erreur critique lors du chargement des informations de la maison d'hôtes.";
    $proprieteDetails = null;
}

// --- Traitement du formulaire (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proprieteDetails) {
    // Récupérer données POST
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $nb_personnes = $_POST['nb_personnes'] ?? '';
    $demandes_speciales = $_POST['demandes_speciales'] ?? '';
    $maisonNom_post = $_POST['maison_nom'] ?? ''; // Depuis champ caché

    $current_error = ''; // Error specific to POST validation

    // Validations (côté serveur pour sécurité)
    if (empty($date_debut) || empty($date_fin) || empty($nb_personnes)) {
        $current_error = "Les dates et le nombre de personnes sont requis.";
    } elseif (strtotime($date_fin) <= strtotime($date_debut)) {
        $current_error = "La date de départ doit être postérieure à la date d'arrivée.";
    } elseif (strtotime($date_debut) < strtotime(date('Y-m-d'))) {
        $current_error = "La date d'arrivée ne peut pas être dans le passé.";
    } elseif (!filter_var($nb_personnes, FILTER_VALIDATE_INT) || $nb_personnes <= 0) {
        $current_error = "Le nombre de personnes doit être un entier positif.";
    } elseif (isset($proprieteDetails['capacite_personnes']) && $nb_personnes > $proprieteDetails['capacite_personnes']) {
        $current_error = "Le nombre de personnes dépasse la capacité maximale (" . $proprieteDetails['capacite_personnes'] . ").";
    } elseif ($proprieteDetails['nom_maison'] !== $maisonNom_post) {
        $current_error = "Erreur : Incohérence dans les informations de la maison d'hôte. Veuillez réessayer.";
        error_log("Incohérence maison POST: DB(".$proprieteDetails['nom_maison'].") vs POST(".$maisonNom_post.")");
    }
    // Add other necessary validations

    if (empty($current_error)) {
        try {
            $nouvelleReservation = new ReservationSejour();
            $nouvelleReservation->setIdUtilisateur($id_utilisateur);
            $nouvelleReservation->setNomUtilisateur($nom_utilisateur);
            $nouvelleReservation->setEmailUtilisateur($email_utilisateur);
            $nouvelleReservation->setTypeLogement('maison_hote');
            $nouvelleReservation->setNomLogement($proprieteDetails['nom_maison']); // Utiliser nom de la BD
            $nouvelleReservation->setDateDebut($date_debut);
            $nouvelleReservation->setDateFin($date_fin);
            $nouvelleReservation->setNbPersonnes($nb_personnes);
            $nouvelleReservation->setDemandesSpeciales(strip_tags($demandes_speciales));
            $nouvelleReservation->setStatut('En attente');

            if ($reservationController->ajouterReservation($nouvelleReservation)) {
                $message = "Votre demande de réservation pour la maison d'hôtes '" . htmlspecialchars($proprieteDetails['nom_maison']) . "' a été enregistrée avec succès. Nous vous contacterons pour confirmer.";
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
    <title>TuniFy Village - Réservation <?php echo $proprieteDetails ? htmlspecialchars($proprieteDetails['nom_maison']) : 'Maison d\'Hôtes'; ?></title>
    <link rel="stylesheet" href="../../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles CSS (identiques à reservation-villa.php et reservation-hotel.php) */
        .form-container { display: flex; gap: 40px; background-color: rgba(28, 28, 28, 0.7); border: 1px solid rgba(201, 168, 108, 0.2); border-radius: 8px; padding: 40px; max-width: 1100px; margin: 40px auto; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); }
        .form-column { flex: 1; }
        .logement-info-box { background-color: rgba(17, 17, 17, 0.5); border: 1px solid rgba(201, 168, 108, 0.1); border-radius: 5px; padding: 20px; margin-bottom: 20px; }
        .logement-info-box h3 { font-family: 'Montserrat', sans-serif; font-size: 20px; color: var(--gold-primary); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid rgba(201, 168, 108, 0.3); }
        .logement-info-box p { font-size: 15px; line-height: 1.6; color: rgba(248, 245, 235, 0.8); margin-bottom: 8px; }
        .logement-info-box p strong { color: var(--light-text); font-weight: 600; min-width: 100px; display: inline-block; }
        .logement-info-box img { width:100%; margin-top:15px; border-radius:5px; border: 1px solid rgba(201, 168, 108, 0.2); object-fit: cover; height: 250px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 15px; border: 1px solid transparent; display: flex; align-items: center; gap: 10px; }
        .alert i { font-size: 1.2em; }
        .alert-success { background-color: rgba(76, 175, 80, 0.1); border-color: rgba(76, 175, 80, 0.4); color: #98FB98; }
        .alert-danger { background-color: rgba(244, 67, 54, 0.1); border-color: rgba(244, 67, 54, 0.4); color: #FFA07A; }
        .form-header { margin-bottom: 30px; }
        .form-header h2 { font-family: 'Playfair Display', serif; color: var(--gold-primary); font-size: 26px; margin-bottom: 10px; }
        .form-header p { color: rgba(248, 245, 235, 0.8); }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label { color: var(--gold-light); font-size: 14px; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px 15px; background-color: rgba(17, 17, 17, 0.7); border: 1px solid rgba(201, 168, 108, 0.3); color: var(--light-text); border-radius: 4px; font-size: 15px; transition: border-color 0.3s ease, box-shadow 0.3s ease; font-family: 'Montserrat', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--gold-primary); box-shadow: 0 0 0 3px rgba(201, 168, 108, 0.2); }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .contact-btn { background-color: var(--gold-primary); color: var(--darker-bg); font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border: none; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 10px; border-radius: 5px; text-align: center; display: inline-block; text-decoration: none;}
        .contact-btn:hover { background-color: var(--light-text); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
        @media (max-width: 992px) { .form-container { flex-direction: column; } }
        @media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } .form-group { margin-bottom: 20px; } }

        /* --- Styles Popup de Validation --- */
        .custom-popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.75); z-index: 3000; display: flex; justify-content: center; align-items: center; backdrop-filter: blur(4px); padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.3s ease-out; }
        .custom-popup-overlay.visible { opacity: 1; pointer-events: auto; }
        .custom-popup-content { background-color: var(--dark-bg); padding: 35px 45px; border-radius: 8px; border: 1px solid var(--gold-primary); box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6); text-align: center; max-width: 480px; width: 95%; position: relative; transform: scale(0.95); opacity: 0; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease-out; }
        .custom-popup-overlay.visible .custom-popup-content { transform: scale(1); opacity: 1; }
        .custom-popup-close { position: absolute; top: 12px; right: 18px; font-size: 28px; font-weight: bold; line-height: 1; color: var(--gold-light); cursor: pointer; transition: color 0.3s ease, transform 0.3s ease; }
        .custom-popup-close:hover { color: #fff; transform: rotate(90deg) scale(1.1); }
        .custom-popup-content h3 { font-family: 'Cinzel', serif; color: var(--gold-primary); margin-top: 0; margin-bottom: 20px; font-size: 22px; font-weight: 600; }
        .custom-popup-content p { color: rgba(248, 245, 235, 0.9); font-size: 16px; line-height: 1.7; margin-bottom: 30px; }
        .popup-ok-button { background-color: var(--gold-primary); color: var(--darker-bg); border: none; padding: 12px 35px; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease; }
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
            <?php if(!empty($error) && !$proprieteDetails && empty($message)): // Initial loading error ?>
                 <div class="alert alert-danger" style="max-width: 1100px; margin: 20px auto;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                 <div style="text-align: center; margin-top: 20px;">
                    <a href="reservation.php" class="contact-btn">Retour aux propriétés</a>
                 </div>
            <?php endif; ?>

            <?php if($proprieteDetails && empty($message)): ?>
            <div class="form-container">
                 <div class="form-column">
                     <div class="form-header">
                        <h2>Informations Maison d'Hôte</h2>
                     </div>
                    <div class="logement-info-box">
                        <h3><?php echo htmlspecialchars($proprieteDetails['nom_maison']); ?></h3>
                        <p><strong>Type :</strong> <?php echo htmlspecialchars($proprieteDetails['type_maison']); ?></p>
                        <?php if($proprieteDetails['nb_chambres']): ?><p><strong>Chambres :</strong> <?php echo htmlspecialchars($proprieteDetails['nb_chambres']); ?></p><?php endif; ?>
                        <?php if($proprieteDetails['surface_m2']): ?><p><strong>Surface :</strong> <?php echo htmlspecialchars($proprieteDetails['surface_m2']); ?> m²</p><?php endif; ?>
                        <?php if($proprieteDetails['capacite_personnes']): ?><p><strong>Capacité Max :</strong> <?php echo htmlspecialchars($proprieteDetails['capacite_personnes']); ?> personnes</p><?php endif; ?>
                        <p><strong>Piscine :</strong> <?php echo $proprieteDetails['piscine'] ? 'Oui' : 'Non'; ?></p>
                        <p><strong>Petit Déj. Inclus :</strong> <?php echo $proprieteDetails['petit_dejeuner_inclus'] ? 'Oui' : 'Non'; ?></p>
                        <?php if($proprieteDetails['prix_nuit']): ?><p><strong>Prix / Nuit :</strong> <?php echo number_format($proprieteDetails['prix_nuit'], 0, ',', ' '); ?> DT</p><?php endif; ?>
                        <img src="<?php echo htmlspecialchars($photo_principale_src); ?>"
                             alt="Image <?php echo htmlspecialchars($proprieteDetails['nom_maison']); ?>"
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
                        <p>Indiquez vos dates et le nombre de personnes.</p>
                    </div>
                     <?php if(!empty($error) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form id="reservationMaisonForm"
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?nom=<?php echo urlencode($proprieteDetails['nom_maison']); ?>"
                          method="POST"
                          onsubmit="return validateReservationMaison();">

                        <input type="hidden" name="maison_nom" value="<?php echo htmlspecialchars($proprieteDetails['nom_maison']); ?>">

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
                                 <input type="text" inputmode="numeric" pattern="[0-9]*" id="nb_personnes" name="nb_personnes" class="form-control" placeholder="Max: <?php echo $capacite_max_js ?: '?'; ?>" value="<?php echo htmlspecialchars($form_data['nb_personnes'] ?? ''); ?>">
                                 <small style="color: rgba(248, 245, 235, 0.6); font-size: 12px; margin-top: 5px;">Capacité maximale: <?php echo htmlspecialchars($proprieteDetails['capacite_personnes'] ?? 'Non définie'); ?> personnes.</small>
                             </div>
                        </div>

                        <div class="form-group">
                            <label for="demandes_speciales">Demandes spéciales (Optionnel)</label>
                            <textarea id="demandes_speciales" name="demandes_speciales" class="form-control" rows="3" placeholder="Préférences, allergies, heure d'arrivée..."><?php echo htmlspecialchars($form_data['demandes_speciales'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="contact-btn">Envoyer la demande de réservation</button>
                        </div>
                    </form>
                 </div> </div> <?php endif; ?> </div> </section>

    <footer class="footer">
        <div class="container">
             <div class="footer-top">
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
            popupMessage.textContent = message;

            const closePopupHandler = () => {
                popupOverlay.classList.remove('visible');
                closeButton.removeEventListener('click', closePopupHandler);
                okButton.removeEventListener('click', closePopupHandler);
            };

            closeButton.addEventListener('click', closePopupHandler);
            okButton.addEventListener('click', closePopupHandler);
            popupOverlay.classList.add('visible');
        }

        // --- Fonction de validation MODIFIÉE ---
        function validateReservationMaison() {
            const dateDebutInput = document.getElementById('date_debut');
            const dateFinInput = document.getElementById('date_fin');
            const nbPersonnesInput = document.getElementById('nb_personnes');
            const capaciteMax = <?php echo $capacite_max_js; ?>; // Capacité max depuis PHP

            const dateDebut = dateDebutInput.value;
            const dateFin = dateFinInput.value;
            const nbPersonnesStr = nbPersonnesInput.value.trim();

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
            today.setHours(0, 0, 0, 0); // Comparer uniquement les dates

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
            // Vérifier si c'est un nombre entier positif
            const nbPersonnes = parseInt(nbPersonnesStr, 10);
             if (isNaN(nbPersonnes) || nbPersonnes <= 0 || nbPersonnesStr !== String(nbPersonnes)) {
                 showCustomPopup("Nombre Invalide", "Veuillez entrer un nombre de personnes valide (entier positif).");
                 nbPersonnesInput.focus();
                 return false;
             }

             // Vérifier la capacité maximale si elle est définie (capaciteMax > 0)
             if (capaciteMax > 0 && nbPersonnes > capaciteMax) {
                 showCustomPopup("Capacité Dépassée", "Le nombre de personnes (" + nbPersonnes + ") dépasse la capacité maximale de cette maison (" + capaciteMax + ").");
                 nbPersonnesInput.focus();
                 return false;
             }

            // Si tout est valide
            return true; // Autorise la soumission
        }

        // --- Code existant pour la gestion des dates min ---
        document.addEventListener('DOMContentLoaded', function() {
             const dateDebutInput = document.getElementById('date_debut');
             const dateFinInput = document.getElementById('date_fin');

             if (dateDebutInput && dateFinInput) {
                 const todayString = new Date().toISOString().split('T')[0];
                 dateDebutInput.min = todayString;

                 dateDebutInput.addEventListener('change', function() {
                     const debutVal = this.value;
                     if (debutVal) {
                         const debutDate = new Date(debutVal);
                         const lendemain = new Date(debutDate);
                         lendemain.setDate(lendemain.getDate() + 1);
                         const lendemainString = lendemain.toISOString().split('T')[0];
                         dateFinInput.min = lendemainString;
                         if (dateFinInput.value && new Date(dateFinInput.value) < lendemain) {
                             dateFinInput.value = '';
                         }
                     } else {
                         const defaultLendemain = new Date();
                         defaultLendemain.setDate(defaultLendemain.getDate() + 1);
                         dateFinInput.min = defaultLendemain.toISOString().split('T')[0];
                     }
                 });

                  const lendemainInitial = new Date();
                  lendemainInitial.setDate(lendemainInitial.getDate() + 1);
                  if(dateDebutInput.value) {
                      const currentDebut = new Date(dateDebutInput.value);
                      const currentLendemain = new Date(currentDebut);
                      currentLendemain.setDate(currentLendemain.getDate() + 1);
                      dateFinInput.min = currentLendemain.toISOString().split('T')[0];
                  } else {
                      dateFinInput.min = lendemainInitial.toISOString().split('T')[0];
                  }
             }
         });
    </script>
</body>
</html>