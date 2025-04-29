<?php

use function PHPSTORM_META\map;

// visitevilla.php - Page de réservation d'une visite de villa

// Inclure les connexions et classes nécessaires
require_once 'C:/xampp/htdocs/Gestion Booking/config.php'; // Vérifiez ce chemin
require_once 'C:/xampp/htdocs/Gestion Booking/Model/Visiteclass.php'; // Vérifiez ce chemin
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Visite.php'; // Vérifiez ce chemin

// Initialiser la session (si vous utilisez des sessions)
// session_start();

// Initialiser la connexion à la base de données
try {
    $db = config::getConnexion();
} catch (Exception $e) {
    // En cas d'échec de connexion, afficher un message d'erreur clair et arrêter
    die('<div style="color: red; padding: 20px; border: 1px solid red; margin: 20px;">Erreur de connexion à la base de données. Veuillez contacter l\'administrateur. Détails : ' . $e->getMessage() . '</div>');
}


// Initialiser l'objet Visite
$visite = new VisiteFunctions($db);

// Variables pour les messages
$message = '';
$error = '';
$form_data = $_POST; // Garder les données en cas d'erreur POST

// Récupérer les informations de la villa depuis l'URL (pour affichage initial et valeur des champs cachés)
$type_villa_get = $_GET['type'] ?? '';
$nom_villa_get = $_GET['nom'] ?? '';

// Initialiser les variables pour l'affichage (au cas où on arrive sur la page sans POST)
$type_villa = $type_villa_get;
$nom_villa = $nom_villa_get;


// Traitement du formulaire lors de la soumission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id_cin = $_POST['id_cin'] ?? '';
    $nom_complet = $_POST['nom_complet'] ?? '';
    $date_visite = $_POST['date_visite'] ?? '';
    $heure_visite = $_POST['heure_visite'] ?? '';

    // Récupérer type et nom depuis les champs cachés POST
    $type_villa_post = $_POST['type_villa'] ?? '';
    $nom_villa_post = $_POST['nom_villa'] ?? '';

    // Mettre à jour les variables pour l'affichage en cas d'erreur/rechargement
    $type_villa = $type_villa_post;
    $nom_villa = $nom_villa_post;

    $current_error = ''; // Erreur spécifique au POST

    // --- Validation Côté Serveur (Sécurité) ---
    if (empty($id_cin) || empty($nom_complet) || empty($date_visite) || empty($heure_visite)) {
        $current_error = "Les champs CIN, Nom Complet, Date et Heure sont obligatoires.";
    } elseif (!preg_match('/^\d{8}$/', $id_cin)) {
         $current_error = "Le numéro CIN doit contenir exactement 8 chiffres.";
    } else {
        // Vérifier si la date est future
        $selectedTimestamp = strtotime($date_visite);
        $todayTimestamp = strtotime(date('Y-m-d'));
        if (!$selectedTimestamp || $selectedTimestamp <= $todayTimestamp) { // Vérifie aussi si la date est valide
             $current_error = "Veuillez sélectionner une date de visite future valide.";
        }
    }
    // --- Fin Validation ---

    // Si pas d'erreur de validation jusqu'ici
    if (empty($current_error)) {

         // Vérification Explicite des Données Cachées
         if (empty($type_villa_post) || empty($nom_villa_post)) {
             $current_error = "Erreur interne : Les informations sur la villa (type/nom) sont manquantes lors de la soumission. Veuillez revenir à la page précédente et réessayer.";
             error_log("ERREUR CRITIQUE: type_villa_post ou nom_villa_post est vide dans visitevilla.php POST. Vérifiez les champs cachés du formulaire.");
         } else {
             // Les données semblent correctes, on tente l'enregistrement
             try {
                 // Définir les valeurs de l'objet visite
                 $visite->setCin(strip_tags($id_cin));
                 $visite->setNomComplet(strip_tags($nom_complet));
                 $visite->setDateVisite(strip_tags($date_visite));
                 $visite->setHeureVisite(strip_tags($heure_visite));
                 $visite->setTypeVilla(strip_tags($type_villa_post)); // Utiliser les valeurs POST
                 $visite->setNomVilla(strip_tags($nom_villa_post));   // Utiliser les valeurs POST

                 // Ajouter la visite dans la base de données
                 if ($visite->ajouterVisite()) {
                     $message = "Votre demande de visite pour la villa " . htmlspecialchars($nom_villa_post) . " (" . htmlspecialchars($type_villa_post) . ") a été enregistrée avec succès. Nous vous contacterons pour confirmer le rendez-vous.";
                     $form_data = []; // Vider les données du formulaire
                     $type_villa = ''; // Vider pour l'affichage après succès
                     $nom_villa = '';  // Vider pour l'affichage après succès
                 } else {
                     $error = "Une erreur serveur s'est produite lors de l'enregistrement. Veuillez réessayer ou contacter le support.";
                 }
             } catch (Exception $e) {
                 $error = "Une exception s'est produite: " . $e->getMessage();
                 error_log("Exception in visitevilla.php POST block: " . $e->getMessage());
             }
         }
    } else {
        $error = $current_error; // Assigner l'erreur de validation POST
    }
} else {
     // Si ce n'est pas une requête POST, utiliser les valeurs GET initiales pour l'affichage
     $type_villa = $type_villa_get;
     $nom_villa = $nom_villa_get;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Réserver une Visite</title>
    <link rel="stylesheet" href="../../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles pour la boîte d'information de la villa */
        .villa-info-box {
            background-color: rgba(28, 28, 28, 0.5);
            border: 1px solid rgba(201, 168, 108, 0.1);
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .villa-info-box h3 {
            font-family: 'Montserrat', sans-serif; font-size: 20px;
            color: var(--gold-primary); margin-bottom: 15px;
            padding-bottom: 10px; border-bottom: 1px solid rgba(201, 168, 108, 0.3);
            width: 100%;
        }
        .villa-info-box p {
            font-size: 16px; line-height: 1.6; color: rgba(248, 245, 235, 0.8);
            margin-bottom: 8px;
        }
        .villa-info-box p strong {
            color: var(--light-text); font-weight: 600; margin-right: 8px;
            display: inline-block; min-width: 60px;
        }
        /* Styles pour les messages d'alerte */
        .alert {
            padding: 15px; margin-bottom: 20px; border-radius: 4px;
            font-size: 15px; border: 1px solid transparent;
             display: flex; align-items: center; gap: 10px;
        }
         .alert i { font-size: 1.2em; }
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1); border-color: rgba(76, 175, 80, 0.4);
            color: #98FB98;
        }
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1); border-color: rgba(244, 67, 54, 0.4);
            color: #FFA07A;
        }
        /* Styles Formulaire */
        .form-container {
             background-color: rgba(28, 28, 28, 0.7); border: 1px solid rgba(201, 168, 108, 0.2);
             border-radius: 8px; padding: 40px; max-width: 800px;
             margin: 40px auto; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .form-header h2 {
            font-family: 'Playfair Display', serif; color: var(--gold-primary);
            font-size: 28px; margin-bottom: 10px; text-align: center;
        }
        .form-header p { text-align: center; color: rgba(248, 245, 235, 0.8); margin-bottom: 30px; }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label { color: var(--gold-light); font-size: 14px; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px 15px; background-color: rgba(17, 17, 17, 0.7); border: 1px solid rgba(201, 168, 108, 0.3); color: var(--light-text); border-radius: 4px; font-size: 15px; transition: border-color 0.3s ease, box-shadow 0.3s ease; font-family: 'Montserrat', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--gold-primary); box-shadow: 0 0 0 3px rgba(201, 168, 108, 0.2); }
        /* Style select dropdown */
        select.form-control { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23c9a86c' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 15px center; background-size: 12px 12px; padding-right: 40px; }
        select.form-control option { background-color: var(--dark-bg); color: var(--light-text); }

        .contact-btn { background-color: var(--gold-primary); color: var(--darker-bg); font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border: none; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 10px; border-radius: 5px; text-align: center; display: inline-block; text-decoration: none; }
        .contact-btn:hover { background-color: var(--light-text); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
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
             <?php if(isset($_SESSION['user_id'])): // Adaptez cette condition à votre logique de session ?>
                 <a href="logout.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Déconnexion</a>
             <?php else: ?>
                 <a href="login.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Connexion</a>
             <?php endif; ?>
        </div>
    </header>

    <section class="contact-section" style="padding-top: 120px; padding-bottom: 80px; background: var(--darker-bg);">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h2>Réserver une Visite de Villa</h2>
                    <p>Complétez le formulaire ci-dessous pour planifier une visite de la propriété sélectionnée.</p>
                </div>

                <?php if(!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                     <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); // Use htmlspecialchars for security ?>
                </div>
                <?php endif; ?>

                <?php if(empty($message) && (!empty($type_villa) || !empty($nom_villa))): ?>
                    <div class="villa-info-box">
                        <h3>Propriété sélectionnée</h3>
                        <p><strong>Type:</strong> Villa <?php echo htmlspecialchars($type_villa ?: 'Non spécifié'); ?></p>
                        <p><strong>Nom:</strong> <?php echo htmlspecialchars($nom_villa ?: 'Non spécifié'); ?></p>
                    </div>
                 <?php endif; ?>


                <?php // Afficher le formulaire seulement si la réservation n'a pas été soumise avec succès
                if(empty($message) && (!empty($type_villa_get) || !empty($nom_villa_get))): // Ensure villa info came from GET initially
                ?>
                <form id="visiteForm"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?type=<?php echo urlencode($type_villa_get); ?>&nom=<?php echo urlencode($nom_villa_get); ?>"
                      method="POST"
                      onsubmit="return validateVisite();"> <input type="hidden" name="type_villa" value="<?php echo htmlspecialchars($type_villa_get); ?>">
                     <input type="hidden" name="nom_villa" value="<?php echo htmlspecialchars($nom_villa_get); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_cin">Numéro CIN *</label>
                            <input type="text" id="id_cin" name="id_cin" class="form-control" placeholder="8 chiffres sans espaces"
                                   value="<?php echo htmlspecialchars($form_data['id_cin'] ?? ''); // Pré-remplir en cas d'erreur ?>">
                        </div>
                        <div class="form-group">
                            <label for="nom_complet">Nom Complet *</label>
                            <input type="text" id="nom_complet" name="nom_complet" class="form-control" placeholder="Votre nom et prénom"
                                   value="<?php echo htmlspecialchars($form_data['nom_complet'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_visite">Date de Visite Souhaitée *</label>
                            <input type="date" id="date_visite" name="date_visite" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['date_visite'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="heure_visite">Heure de Visite Souhaitée *</label>
                            <select id="heure_visite" name="heure_visite" class="form-control">
                                <option value="" disabled <?php echo empty($form_data['heure_visite']) ? 'selected' : ''; ?>>-- Sélectionnez une heure --</option>
                                <?php
                                $heures = ["09:00", "10:00", "11:00", "14:00", "15:00", "16:00", "17:00"];
                                $heure_selectionnee = $form_data['heure_visite'] ?? '';
                                foreach ($heures as $h) {
                                    $selected = ($h === $heure_selectionnee) ? 'selected' : '';
                                    echo "<option value=\"$h\" $selected>$h</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="contact-btn">Réserver ma visite</button>
                    </div>
                </form>
                <?php elseif(empty($message)): // If villa info was missing initially or after success ?>
                <div style="text-align: center; margin-top: 30px;">
                    <p style="color: rgba(248, 245, 235, 0.8); margin-bottom: 20px;">
                        <?php echo $message ? '' : "Veuillez sélectionner une villa depuis la page de réservation."; ?>
                    </p>
                    <a href="reservation.php" class="contact-btn" style="display: inline-block; width: auto; padding: 15px 30px; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour aux propriétés
                    </a>
                </div>
                <?php endif; ?>
            </div> </div> </section>

    <footer class="footer">
       <div class="container">
            <div class="footer-top"> </div>
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
        function validateVisite() {
            const cinInput = document.getElementById('id_cin');
            const nomInput = document.getElementById('nom_complet');
            const dateInput = document.getElementById('date_visite');
            const heureInput = document.getElementById('heure_visite');

            const cin = cinInput.value.trim();
            const nom = nomInput.value.trim();
            const dateVisite = dateInput.value;
            const heureVisite = heureInput.value;

            if (cin === '') {
                // Remplacer alert par showCustomPopup
                showCustomPopup('CIN Requis', 'Veuillez entrer votre numéro CIN.');
                cinInput.focus();
                return false;
            }
            // Validation format CIN (8 chiffres)
            const cinRegex = /^\d{8}$/;
            if (!cinRegex.test(cin)) {
                 // Remplacer alert par showCustomPopup
                 showCustomPopup('CIN Invalide', 'Le numéro CIN doit contenir exactement 8 chiffres.');
                 cinInput.focus();
                 return false;
            }

            if (nom === '') {
                // Remplacer alert par showCustomPopup
                showCustomPopup('Nom Requis', 'Veuillez entrer votre nom complet.');
                nomInput.focus();
                return false;
            }

            if (dateVisite === '') {
                // Remplacer alert par showCustomPopup
                showCustomPopup('Date Requise', 'Veuillez sélectionner une date de visite.');
                dateInput.focus();
                return false;
            }

            // Vérifier si la date est future
            const today = new Date();
            const selectedDate = new Date(dateVisite);
            today.setHours(0, 0, 0, 0); // Comparer uniquement les dates

            // Also check if selectedDate is a valid date object
            if (isNaN(selectedDate.getTime()) || selectedDate <= today) {
                // Remplacer alert par showCustomPopup
                showCustomPopup('Date Invalide', 'Veuillez sélectionner une date de visite future valide.');
                dateInput.focus();
                return false;
            }

            if (heureVisite === '' || heureVisite === null) { // Check for empty or null
                // Remplacer alert par showCustomPopup
                showCustomPopup('Heure Requise', 'Veuillez sélectionner une heure de visite.');
                heureInput.focus();
                return false;
            }

            // Si tout est valide
            return true; // Autorise la soumission
        }

         // Mettre à jour la date min dynamiquement au chargement
         document.addEventListener('DOMContentLoaded', function() {
             const dateInput = document.getElementById('date_visite');
             if (dateInput) {
                 const today = new Date();
                 const demain = new Date(today);
                 demain.setDate(demain.getDate() + 1); // Set minimum to tomorrow
                 const demainString = demain.toISOString().split('T')[0]; // Format YYYY-MM-DD
                 dateInput.min = demainString;
             }
         });
    </script>
</body>
</html>