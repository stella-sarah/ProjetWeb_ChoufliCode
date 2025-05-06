<?php

// visitevilla.php - Page de réservation d'une visite de villa avec Calendrier

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
    // *** RÉCUPÉRER DATE ET HEURE DES CHAMPS CACHÉS ***
    $date_visite = $_POST['date_visite'] ?? ''; // vient de <input type="hidden" id="selected_date_visite" name="date_visite">
    $heure_visite = $_POST['heure_visite'] ?? ''; // vient de <input type="hidden" id="selected_heure_visite" name="heure_visite">

    // Récupérer type et nom depuis les champs cachés POST
    $type_villa_post = $_POST['type_villa'] ?? '';
    $nom_villa_post = $_POST['nom_villa'] ?? '';

    // Mettre à jour les variables pour l'affichage en cas d'erreur/rechargement
    $type_villa = $type_villa_post;
    $nom_villa = $nom_villa_post;

    $current_error = ''; // Erreur spécifique au POST

    // --- Validation Côté Serveur (Sécurité) ---
    // *** MODIFIÉ : Valider les champs cachés date/heure ***
    if (empty($id_cin) || empty($nom_complet) || empty($date_visite) || empty($heure_visite)) {
        $current_error = "Les champs CIN, Nom Complet, Date et Heure de visite sont obligatoires. Veuillez sélectionner une date et une heure.";
    } elseif (!preg_match('/^\d{8}$/', $id_cin)) {
         $current_error = "Le numéro CIN doit contenir exactement 8 chiffres.";
    } else {
        // Vérifier si la date est future (déjà fait en JS, mais bon à revérifier)
        $selectedTimestamp = strtotime($date_visite);
        $todayTimestamp = strtotime(date('Y-m-d'));
        if (!$selectedTimestamp || $selectedTimestamp <= $todayTimestamp) { // Vérifie aussi si la date est valide
             $current_error = "Veuillez sélectionner une date de visite future valide.";
        }
        // Vérifier si l'heure est dans le format attendu (HH:MM:SS)
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $heure_visite)) {
             $current_error = "Format d'heure invalide.";
        }
    }
    // --- Fin Validation ---

    // Si pas d'erreur de validation jusqu'ici
    if (empty($current_error)) {

         // Vérification Explicite des Données Cachées Villa
         if (empty($type_villa_post) || empty($nom_villa_post)) {
             $current_error = "Erreur interne : Les informations sur la villa (type/nom) sont manquantes lors de la soumission. Veuillez revenir à la page précédente et réessayer.";
             error_log("ERREUR CRITIQUE: type_villa_post ou nom_villa_post est vide dans visitevilla.php POST. Vérifiez les champs cachés du formulaire.");
         } else {
             // Les données semblent correctes, on tente l'enregistrement
             try {
                 // Définir les valeurs de l'objet visite
                 $visite->setCin(strip_tags($id_cin));
                 $visite->setNomComplet(strip_tags($nom_complet));
                 $visite->setDateVisite(strip_tags($date_visite)); // Utiliser la date du champ caché
                 $visite->setHeureVisite(strip_tags($heure_visite)); // Utiliser l'heure du champ caché
                 $visite->setTypeVilla(strip_tags($type_villa_post)); // Utiliser les valeurs POST
                 $visite->setNomVilla(strip_tags($nom_villa_post));   // Utiliser les valeurs POST

                 // Ajouter la visite dans la base de données
                 if ($visite->ajouterVisite()) {
                     $message = "Votre demande de visite pour la villa " . htmlspecialchars($nom_villa_post) . " (" . htmlspecialchars($type_villa_post) . ") le " . date('d/m/Y', strtotime($date_visite)) . " à " . date('H:i', strtotime($heure_visite)) . " a été enregistrée avec succès. Nous vous contacterons pour confirmer le rendez-vous.";
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

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />

    <style>
        /* Import theme variables if not globally available (adjust path if needed) */
        /* @import url('../../style.css'); */

        /* Styles for the villa info box */
        .villa-info-box {
            background-color: rgba(28, 28, 28, 0.5); /* var(--dark-bg) with opacity */
            border: 1px solid rgba(201, 168, 108, 0.1); /* var(--gold-primary) with opacity */
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
            font-size: 16px; line-height: 1.6; color: rgba(248, 245, 235, 0.8); /* var(--light-text) with opacity */
            margin-bottom: 8px;
        }
        .villa-info-box p strong {
            color: var(--light-text); font-weight: 600; margin-right: 8px;
            display: inline-block; min-width: 60px;
        }
        /* Styles for alert messages */
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
        /* Form Styles */
        .form-container {
             background-color: rgba(28, 28, 28, 0.7); /* var(--dark-bg) with more opacity */
             border: 1px solid rgba(201, 168, 108, 0.2);
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

        .contact-btn { background-color: var(--gold-primary); color: var(--darker-bg); font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border: none; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 10px; border-radius: 5px; text-align: center; display: inline-block; text-decoration: none; }
        .contact-btn:hover { background-color: var(--light-text); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
        @media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } .form-group { margin-bottom: 20px; } }

        /* --- Validation Popup Styles --- */
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
        /* --- End Validation Popup Styles --- */

        /* --- ENHANCED Calendar & Time Slot Styles --- */
        #calendar-container {
            max-width: 100%; /* Allow it to take full width within form */
            margin: 25px 0 30px 0; /* Adjust margins */
            background-color: transparent; /* Make container transparent */
            padding: 0; /* Remove padding */
            border-radius: 5px;
            border: none; /* Remove container border */
        }
        #calendar-container label {
             display: block;
             color: var(--gold-light);
             font-size: 14px;
             margin-bottom: 15px;
             font-weight: 500;
             text-align: left; /* Align label left */
        }
        #calendar {
           background-color: rgba(17, 17, 17, 0.7); /* Dark background for calendar */
           border: 1px solid rgba(201, 168, 108, 0.3); /* Gold border */
           border-radius: 5px;
           padding: 10px; /* Add some padding inside */
        }

        /* FullCalendar General */
        .fc { /* Target the main FullCalendar container */
            color: var(--light-text); /* Light text for dates */
        }

        /* Header Toolbar */
        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1.2em;
        }
        .fc .fc-toolbar-title { /* Month/Year Title */
             color: var(--gold-primary);
             font-size: 1.6em; /* Larger title */
             font-family: 'Playfair Display', serif; /* Match heading font */
        }
        .fc .fc-button-primary { /* Navigation Buttons (Prev, Next, Today) */
             background-color: var(--gold-primary);
             border-color: var(--gold-primary);
             color: var(--darker-bg);
             font-size: 0.9em;
             text-transform: capitalize; /* Normal case */
             letter-spacing: 0.5px;
             padding: 6px 12px;
             opacity: 0.9;
             transition: all 0.3s ease;
             box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .fc .fc-button-primary:not(:disabled):hover {
             background-color: var(--gold-light);
             border-color: var(--gold-light);
             color: var(--darker-bg);
             opacity: 1;
             transform: translateY(-1px);
             box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .fc .fc-button-primary:disabled {
             background-color: rgba(85, 85, 85, 0.5); /* Darker disabled */
             border-color: rgba(68, 68, 68, 0.5);
             color: rgba(248, 245, 235, 0.5);
             opacity: 0.6;
        }
        .fc .fc-today-button { /* Specific style for Today button if needed */
            /* Example: */
            /* border: 1px solid var(--gold-light); */
        }

        /* Calendar Grid */
        .fc .fc-daygrid-day { /* Each day cell */
            border-color: rgba(201, 168, 108, 0.15); /* Subtle gold border */
            transition: background-color 0.3s ease;
        }
        .fc .fc-daygrid-day-number { /* Day numbers */
            color: rgba(248, 245, 235, 0.8);
            padding: 6px;
            font-weight: 500;
        }
        .fc .fc-day-today { /* Today's date */
            background-color: rgba(201, 168, 108, 0.1) !important; /* Subtle gold background */
        }
         .fc .fc-day-today .fc-daygrid-day-number {
             color: var(--gold-light); /* Brighter number for today */
             font-weight: bold;
         }

        /* Date Selection & Hover */
        .fc .fc-daygrid-day:not(.fc-day-past) { /* Future days */
            cursor: pointer;
        }
         .fc .fc-daygrid-day:not(.fc-day-past):hover {
             background-color: rgba(201, 168, 108, 0.08);
         }
         .fc .fc-daygrid-day.selected-date { /* Class added by JS on click */
             background-color: rgba(201, 168, 108, 0.3) !important; /* More prominent selection color */
             box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
         }

        /* Past Dates */
        .fc .fc-day-past {
             background-color: rgba(17, 17, 17, 0.3); /* Slightly darker background for past */
             cursor: default;
        }
        .fc .fc-day-past .fc-daygrid-day-number {
            color: rgba(248, 245, 235, 0.4); /* Dimmer number */
            text-decoration: line-through;
        }

         /* Time Slots Container */
         #time-slots {
            margin-top: 20px; /* Space above time slots */
            padding: 20px;
            background-color: rgba(17, 17, 17, 0.7); /* Match calendar bg */
            border-radius: 4px;
            border: 1px solid rgba(201, 168, 108, 0.2); /* Match calendar border */
            min-height: 60px;
            text-align: center;
         }
         #time-slots h4 {
             color: var(--gold-light);
             font-size: 15px; /* Slightly larger heading */
             margin-bottom: 15px; /* More space below heading */
             font-weight: 500;
             border-bottom: 1px solid rgba(201, 168, 108, 0.2); /* Add separator */
             padding-bottom: 10px;
         }
         /* Time Slot Buttons */
         .time-slot-button {
            background-color: var(--dark-bg);
            border: 1px solid rgba(201, 168, 108, 0.4); /* Slightly stronger border */
            color: var(--gold-light);
            padding: 10px 18px; /* Slightly larger buttons */
            margin: 6px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
         }
         .time-slot-button:hover:not(:disabled) {
            background-color: rgba(201, 168, 108, 0.15);
            border-color: var(--gold-primary);
            color: var(--gold-primary);
            transform: translateY(-2px); /* Add hover effect */
         }
         .time-slot-button.selected {
            background-color: var(--gold-primary);
            border-color: var(--gold-primary);
            color: var(--darker-bg);
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3); /* Add shadow to selected */
            transform: translateY(-1px);
         }
         .time-slot-button:disabled {
            /* Make reserved slots clearly RED */
            background-color: rgba(220, 53, 69, 0.6); /* Stronger Red */
            border-color: rgba(220, 53, 69, 0.8);
            color: rgba(255, 255, 255, 0.6);
            cursor: not-allowed;
            text-decoration: line-through;
            opacity: 0.8; /* Slightly less opaque */
         }
         /* --- END Enhanced Styles --- */

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
                 <div style="text-align: center; margin-top: 30px;">
                    <a href="reservation.php" class="contact-btn" style="display: inline-block; width: auto; padding: 15px 30px; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour aux propriétés
                    </a>
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


                <?php // Afficher le formulaire seulement si la réservation n'a pas été soumise avec succès ET si les infos villa sont présentes
                if(empty($message) && (!empty($type_villa_get) || !empty($nom_villa_get))):
                ?>
                <form id="visiteForm"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?type=<?php echo urlencode($type_villa_get); ?>&nom=<?php echo urlencode($nom_villa_get); ?>"
                      method="POST"
                      onsubmit="return validateVisite();">
                     <input type="hidden" name="type_villa" value="<?php echo htmlspecialchars($type_villa_get); ?>">
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

                    <div id='calendar-container'>
                        <label>Choisissez une date et heure de visite *</label>
                        <div id='calendar'></div>
                        <div id='time-slots'>Sélectionnez une date pour voir les heures disponibles.</div>
                    </div>

                    <input type="hidden" id="selected_date_visite" name="date_visite" value="<?php echo htmlspecialchars($form_data['date_visite'] ?? ''); ?>">
                    <input type="hidden" id="selected_heure_visite" name="heure_visite" value="<?php echo htmlspecialchars($form_data['heure_visite'] ?? ''); ?>">

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
            </div>
        </div>
    </section>

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

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js'></script>
    <script>
        // --- Custom Popup Function ---
        function showCustomPopup(title, message) {
            const popupOverlay = document.getElementById('validationPopupOverlay');
            const popupTitle = document.getElementById('validationPopupTitle');
            const popupMessage = document.getElementById('validationPopupMessage');
            const closeButton = document.getElementById('validationPopupClose');
            const okButton = document.getElementById('validationPopupOk');

            if (!popupOverlay || !popupTitle || !popupMessage || !closeButton || !okButton) {
                console.error("Validation popup elements not found! Fallback to alert.");
                alert(title + "\n" + message);
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

        // --- Form Validation Function ---
        function validateVisite() {
            const cinInput = document.getElementById('id_cin');
            const nomInput = document.getElementById('nom_complet');
            const dateInput = document.getElementById('selected_date_visite');
            const heureInput = document.getElementById('selected_heure_visite');

            const cin = cinInput.value.trim();
            const nom = nomInput.value.trim();
            const dateVisite = dateInput.value;
            const heureVisite = heureInput.value;

            if (cin === '') {
                showCustomPopup('CIN Requis', 'Veuillez entrer votre numéro CIN.');
                cinInput.focus();
                return false;
            }
            const cinRegex = /^\d{8}$/;
            if (!cinRegex.test(cin)) {
                 showCustomPopup('CIN Invalide', 'Le numéro CIN doit contenir exactement 8 chiffres.');
                 cinInput.focus();
                 return false;
            }

            if (nom === '') {
                showCustomPopup('Nom Requis', 'Veuillez entrer votre nom complet.');
                nomInput.focus();
                return false;
            }

            if (dateVisite === '') {
                showCustomPopup('Date Requise', 'Veuillez sélectionner une date dans le calendrier.');
                return false;
            }
            if (heureVisite === '') {
                showCustomPopup('Heure Requise', 'Veuillez sélectionner une heure de visite disponible après avoir choisi une date.');
                return false;
            }
            return true; // Allow submission
        }

         // --- Calendar Logic ---
         document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const timeSlotsEl = document.getElementById('time-slots');
            const hiddenDateInput = document.getElementById('selected_date_visite');
            const hiddenTimeInput = document.getElementById('selected_heure_visite');
            let reservedSlots = [];
            let calendarInstance = null;

            // --- Fetch Reserved Slots ---
            fetch('get_reserved_times.php') // Ensure this path is correct
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data)) {
                        reservedSlots = data;
                    } else {
                        console.error('Invalid data format received from get_reserved_times.php:', data);
                        reservedSlots = [];
                    }
                    initializeCalendar();
                })
                .catch(error => {
                    console.error('Error fetching reserved times:', error);
                    if(timeSlotsEl) timeSlotsEl.innerHTML = '<p style="color: #FFA07A;">Erreur lors du chargement des disponibilités. Veuillez réessayer.</p>';
                    initializeCalendar(); // Initialize even if fetch fails
                });

            // --- Initialize FullCalendar ---
            function initializeCalendar() {
                if (!calendarEl) {
                     console.error("Calendar element (#calendar) not found!");
                     return;
                }
                calendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'fr',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    selectable: true,
                    selectAllow: function(selectInfo) {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        return selectInfo.start >= today;
                    },
                    dateClick: function(info) {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (info.date < today) {
                            return; // Do nothing for past dates
                        }

                        const clickedDate = info.dateStr;
                        hiddenDateInput.value = clickedDate;
                        hiddenTimeInput.value = ''; // Reset time
                        displayTimeSlots(clickedDate);

                        // Visual feedback for selected date
                         document.querySelectorAll('.fc-daygrid-day.selected-date').forEach(dayEl => {
                             dayEl.classList.remove('selected-date');
                             dayEl.style.backgroundColor = ''; // Reset others
                         });
                         info.dayEl.classList.add('selected-date');
                         // Apply highlight style (already defined in CSS)
                         // info.dayEl.style.backgroundColor = 'rgba(201, 168, 108, 0.3)';

                         // Clear previous time selection visual
                         if (timeSlotsEl) {
                             timeSlotsEl.querySelectorAll('.time-slot-button.selected').forEach(btn => {
                                btn.classList.remove('selected');
                             });
                         }
                    },
                    validRange: { // Prevent navigating to past months
                        start: new Date().toISOString().split('T')[0]
                    }
                    // dayCellDidMount can be used for more advanced day marking
                });
                calendarInstance.render();
            }

            // --- Display Time Slots ---
            function displayTimeSlots(dateStr) {
                 if (!timeSlotsEl) return;
                 const availableHours = ["09:00", "10:00", "11:00", "14:00", "15:00", "16:00", "17:00"];
                 const displayDate = new Date(dateStr + 'T00:00:00');
                 const formattedDisplayDate = displayDate.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                 timeSlotsEl.innerHTML = `<h4>Heures disponibles pour ${formattedDisplayDate}:</h4>`;

                 availableHours.forEach(hour => {
                     const fullTime = hour + ":00"; // HH:MM:SS format
                     const isReserved = reservedSlots.some(slot => slot.date === dateStr && slot.time === fullTime);

                     const button = document.createElement('button');
                     button.type = 'button';
                     button.classList.add('time-slot-button');
                     button.textContent = hour;
                     button.dataset.date = dateStr;
                     button.dataset.time = fullTime;

                     if (isReserved) {
                         button.disabled = true;
                         button.title = "Ce créneau est déjà réservé";
                         // Disabled style with red background is applied via CSS
                     } else {
                         button.onclick = function() {
                             selectTimeSlot(this);
                         };
                     }
                     timeSlotsEl.appendChild(button);
                 });
            }

            // --- Select Time Slot ---
            function selectTimeSlot(buttonEl) {
                 if (!timeSlotsEl || !hiddenDateInput || !hiddenTimeInput) return;
                // Deselect previous visually
                timeSlotsEl.querySelectorAll('.time-slot-button.selected').forEach(btn => {
                    btn.classList.remove('selected');
                });

                // Select new one visually
                buttonEl.classList.add('selected'); // Selected style applied via CSS

                // Update hidden fields
                hiddenDateInput.value = buttonEl.dataset.date;
                hiddenTimeInput.value = buttonEl.dataset.time;
                console.log("Date sélectionnée (hidden):", hiddenDateInput.value);
                console.log("Heure sélectionnée (hidden):", hiddenTimeInput.value);
            }
        });
        // --- END Calendar Logic ---

    </script>
</body>
</html>
