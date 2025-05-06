<?php
// recherchevisitecin.php - Page de recherche de visites par CIN

// --- Inclusion des fichiers ---
// Assurez-vous que ces chemins sont corrects
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controllor/Visite.php'; // Contient VisiteFunctions
require_once __DIR__ . '/../../Model/Visiteclass.php'; // Contient la classe Visite

// Initialisation
$reservations = [];
$cin_search = '';
$search_error = ''; // Pour les erreurs PHP/serveur
$search_message = '';

try {
    $db = config::getConnexion();
    $visiteController = new VisiteFunctions($db); // Utiliser le contrôleur

    // Traitement de la recherche si le formulaire est soumis (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cin_search = trim($_POST['cin'] ?? '');

        // Validation PHP (toujours nécessaire côté serveur pour la sécurité)
        if (empty($cin_search)) {
            $search_error = "Le numéro CIN est requis."; // Message d'erreur serveur
        } elseif (!preg_match('/^\d{8}$/', $cin_search)) {
            $search_error = "Le numéro CIN doit contenir exactement 8 chiffres."; // Message d'erreur serveur
        } else {
            // Si la validation PHP passe, on recherche
            $reservations = $visiteController->getVisitesByCin($cin_search);

            if (empty($reservations)) {
                $search_message = "Aucune réservation de visite trouvée pour le CIN : " . htmlspecialchars($cin_search);
            }
        }
    }

} catch (Exception $e) {
    error_log("Erreur dans recherchevisitecin.php: " . $e->getMessage());
    // Erreur technique affichée via PHP
    $search_error = "Une erreur technique s'est produite lors de la recherche. Veuillez réessayer plus tard.";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Recherche Visites par CIN</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles spécifiques pour cette page (peuvent aussi être mis dans style.css) */

        /* --- Styles existants (copiés depuis votre code précédent) --- */
        :root {
            --gold-primary: #c9a86c;
            --gold-light: #e9d9b6;
            --gold-dark: #8b783d;
            --dark-bg: #1c1c1c;
            --darker-bg: #111111;
            --light-text: #f8f5eb;
        }
        body {
             padding-top: 80px; /* Espace pour le header fixe */
             font-family: 'Montserrat', sans-serif;
             background-color: var(--darker-bg);
             color: var(--light-text);
             overflow-x: hidden;
        }
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            position: fixed;
            top: 0; /* Assurez-vous qu'il est bien en haut */
            left: 0; /* Assurez-vous qu'il prend toute la largeur */
            width: 100%;
            z-index: 100;
            transition: background-color 0.3s ease;
            padding: 5px 0;
            background-color: rgba(28, 28, 28, 0.95); /* Fond initial pour visibilité */
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }
        header.scrolled {
            background-color: rgba(28, 28, 28, 0.95);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }
        .nav-container {
            display: flex;
            align-items: center;
            width: 100%;
        }
        .logo {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .logo img { height: 50px; margin-right: 10px; }
        .logo-text { display: flex; flex-direction: column; justify-content: center; }
        .logo-text h1 { font-family: 'Cinzel', serif; font-weight: 700; font-size: 22px; color: var(--gold-primary); margin: 0; line-height: 1.1; letter-spacing: 1px; }
        .logo-text p { font-size: 11px; font-weight: 300; color: var(--gold-light); letter-spacing: 3px; text-transform: uppercase; margin: 0; line-height: 1.1; }
        nav { margin-left: 30px; }
        nav ul { display: flex; list-style: none; gap: 30px; align-items: center; margin: 0; padding: 0; }
        nav ul li a { color: var(--light-text); text-decoration: none; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; transition: color 0.3s ease; font-weight: 400; padding: 10px 0; }
        nav ul li a:hover, nav ul li a.active { color: var(--gold-primary); }
        .contact-btn { background-color: transparent; border: 1px solid var(--gold-primary); color: var(--gold-primary); padding: 10px 25px; font-family: 'Montserrat', sans-serif; font-weight: 500; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; cursor: pointer; transition: all 0.3s ease; text-decoration: none; flex-shrink: 0; margin-left: auto; display: inline-flex; align-items: center; height: fit-content; }
        .contact-btn:hover { background-color: var(--gold-primary); color: var(--darker-bg); }
        @media screen and (max-width: 768px) {
            .nav-container { flex-direction: column; gap: 20px; align-items: flex-start; }
            nav { margin-left: 0; width: 100%; }
            nav ul { flex-direction: column; gap: 15px; text-align: center; width: 100%; }
            .contact-btn { margin-left: 0; width: auto; align-self: center; }
        }
        /* --- Fin Styles Navigation --- */

        .search-container {
            max-width: 700px;
            margin: 120px auto 40px auto; /* Ajusté margin-top pour header */
            padding: 30px;
            background-color: rgba(28, 28, 28, 0.7);
            border: 1px solid rgba(201, 168, 108, 0.2);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .search-container h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold-primary);
            text-align: center;
            margin-bottom: 25px;
        }
        .search-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 30px;
        }
        .search-form .form-group {
            flex-grow: 1;
        }
        .search-form label {
            display: block;
            color: var(--gold-light);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .search-form input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(17, 17, 17, 0.7);
            border: 1px solid rgba(201, 168, 108, 0.3);
            color: var(--light-text);
            border-radius: 4px;
            font-size: 15px;
        }
        .search-form input[type="text"]:focus {
             outline: none;
             border-color: var(--gold-primary);
             box-shadow: 0 0 0 3px rgba(201, 168, 108, 0.2);
        }
        .search-form button {
            padding: 12px 25px;
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            height: 48px;
        }
        .search-form button:hover {
            background-color: var(--gold-light);
            transform: translateY(-2px);
        }

        .results-container {
            margin-top: 30px;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--dark-bg);
            border-radius: 5px;
            overflow: hidden;
            border: 1px solid rgba(201, 168, 108, 0.1);
        }
        .results-table th, .results-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(201, 168, 108, 0.1);
            color: rgba(248, 245, 235, 0.9);
            font-size: 14px;
        }
        .results-table th {
            background-color: rgba(28, 28, 28, 0.9);
            color: var(--gold-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
         .results-table tbody tr:hover {
            background-color: rgba(201, 168, 108, 0.08);
        }
        .results-table tbody tr:last-child td {
            border-bottom: none;
        }
        .no-results, .search-prompt {
            text-align: center;
            color: rgba(248, 245, 235, 0.7);
            padding: 20px;
            font-style: italic;
        }
        .alert-search { /* Style pour l'erreur PHP */
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-danger-search {
             background-color: rgba(244, 67, 54, 0.1);
             border-color: rgba(244, 67, 54, 0.4);
             color: #FFA07A;
        }
        .back-button-container {
            text-align: center;
            margin-top: 40px;
        }
        .back-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--gold-dark);
            color: var(--light-text);
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background-color: var(--gold-primary);
            color: var(--darker-bg);
            transform: translateY(-2px);
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
            white-space: pre-line; /* Respecte les sauts de ligne dans le message JS */
        }
        .popup-ok-button {
            background-color: var(--gold-primary); color: var(--darker-bg); border: none;
            padding: 12px 35px; border-radius: 5px; cursor: pointer; font-weight: 600;
            font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }
        .popup-ok-button:hover { background-color: var(--gold-light); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); }
        /* --- Fin Styles Popup --- */

        /* Footer Styles */
        .footer {
            background-color: var(--dark-bg);
            padding: 40px 0; /* Réduit le padding */
            margin-top: 60px; /* Espace avant le footer */
            border-top: 1px solid rgba(201, 168, 108, 0.2);
        }
        .footer-bottom {
            display: flex;
            justify-content: center; /* Centre le contenu */
            align-items: center;
        }
        .footer-copyright {
            font-size: 14px;
            color: rgba(248, 245, 235, 0.7);
            text-align: center;
        }
        .footer-copyright a {
            color: var(--gold-primary);
            text-decoration: none;
        }
        .footer-copyright a:hover {
            text-decoration: underline;
        }

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
             
                 <a href="logout.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Déconnexion</a>
      
                 <a href="login.php" class="contact-btn" style="width: auto; padding: 10px 20px; margin-top: 0;">Connexion</a>
       
        </div>
    </header>

    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <h2>Rechercher vos Visites</h2>

                 <?php if(!empty($search_error)): // Afficher l'erreur PHP si elle existe ?>
                 <div class="alert-search alert-danger-search">
                     <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($search_error); ?>
                 </div>
                 <?php endif; ?>

                 <form action="recherchevisitecin.php" method="POST" class="search-form" onsubmit="return validateSearch();">
                    <div class="form-group">
                        <label for="cin">Entrez votre numéro CIN</label>
                        <input type="text" id="cin" name="cin" value="<?php echo htmlspecialchars($cin_search); ?>" placeholder="8 chiffres">
                    </div>
                    <button type="submit">
                        <i class="fas fa-search"></i> Chercher
                    </button>
                </form>

                <div class="results-container">
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($search_error)): ?>
                        <?php if (!empty($reservations)): ?>
                            <h3>Résultats de la recherche pour CIN : <?php echo htmlspecialchars($cin_search); ?></h3>
                            <table class="results-table">
                                <thead>
                                    <tr>
                                        <th>ID Visite</th>
                                        <th>Nom Villa</th>
                                        <th>Type Villa</th>
                                        <th>Date Visite</th>
                                        <th>Heure Visite</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $resa): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($resa['id_visite']); ?></td>
                                            <td><?php echo htmlspecialchars($resa['nom_villa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($resa['type_villa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($resa['date_visite']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('H:i', strtotime($resa['heure_visite']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php elseif(!empty($search_message)): ?>
                            <p class="no-results"><?php echo htmlspecialchars($search_message); ?></p>
                         <?php endif; ?>
                     <?php elseif ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                          <p class="search-prompt">Entrez votre numéro CIN pour rechercher vos réservations de visite.</p>
                    <?php endif; ?>
                </div>

                 <div class="back-button-container">
                    <a href="reservation.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Retour aux propriétés
                    </a>
                 </div>

            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
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
        // --- Fonction pour afficher la popup personnalisée ---
        function showCustomPopup(title, message) {
            const popupOverlay = document.getElementById('validationPopupOverlay');
            const popupTitle = document.getElementById('validationPopupTitle');
            const popupMessage = document.getElementById('validationPopupMessage');
            const closeButton = document.getElementById('validationPopupClose');
            const okButton = document.getElementById('validationPopupOk');

            // Vérifie si les éléments existent
            if (!popupOverlay || !popupTitle || !popupMessage || !closeButton || !okButton) {
                console.error("Éléments du popup de validation introuvables ! Fallback sur alert.");
                alert(title + "\n" + message); // Utiliser alert comme secours
                return;
            }

            popupTitle.textContent = title;
            popupMessage.textContent = message; // Utilise textContent pour la sécurité

            // Fonction pour fermer la popup (réutilisable)
            const closePopupHandler = () => {
                popupOverlay.classList.remove('visible');
                // Important : Supprimer les écouteurs pour éviter les doublons
                closeButton.removeEventListener('click', closePopupHandler);
                okButton.removeEventListener('click', closePopupHandler);
            };

            // Ajouter les écouteurs d'événements pour fermer
            closeButton.addEventListener('click', closePopupHandler);
            okButton.addEventListener('click', closePopupHandler);

            // Afficher la popup
            popupOverlay.classList.add('visible');
        }

        // --- Fonction de validation JavaScript ---
        function validateSearch() {
            const cinInput = document.getElementById('cin');
            const cinValue = cinInput.value.trim(); // Enlever les espaces avant/après

            // 1. Vérifier si le champ est vide
            if (cinValue === '') {
                showCustomPopup('Erreur de Saisie', 'Veuillez entrer votre numéro CIN.');
                cinInput.focus(); // Mettre le focus sur le champ
                return false; // Empêche l'envoi du formulaire
            }

            // 2. Vérifier si le CIN contient exactement 8 chiffres
            const cinRegex = /^\d{8}$/; // Expression régulière pour 8 chiffres exactement
            if (!cinRegex.test(cinValue)) {
                showCustomPopup('Format CIN Incorrect', 'Le numéro CIN doit contenir exactement 8 chiffres.');
                cinInput.focus();
                return false; // Empêche l'envoi du formulaire
            }

            // Si toutes les validations passent
            return true; // Autorise l'envoi du formulaire
        }

         // Optionnel : Afficher l'erreur PHP dans la popup si elle existe au chargement de la page
         <?php if (!empty($search_error) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            // Utiliser setTimeout pour s'assurer que le DOM est prêt
            // et éviter les conflits potentiels avec d'autres scripts au chargement.
            setTimeout(() => {
                showCustomPopup('Erreur Serveur', <?php echo json_encode($search_error); ?>);
            }, 100); // Léger délai
         <?php endif; ?>

    </script>

</body>
</html>
