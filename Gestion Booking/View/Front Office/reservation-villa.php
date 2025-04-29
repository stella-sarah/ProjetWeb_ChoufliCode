<?php
// reservation-villa.php - Formulaire (Modifié pour Formspree + AJAX BDD + Popup JS + Redirect)

// --- Inclusion des fichiers (Uniquement pour affichage initial) ---
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controllor/Photo.php';
require_once __DIR__ . '/../../Controllor/ProprieteController.php';

// --- Gestion Session ---
session_start();
$utilisateur_connecte = isset($_SESSION['user_id']);
$id_utilisateur_session = $_SESSION['user_id'] ?? null;
$nom_utilisateur_session = $_SESSION['user_nom'] ?? null;
$email_utilisateur_session = $_SESSION['user_email'] ?? null;
// --- Fin Gestion Session ---

// Initialisation (Pour affichage)
$proprieteDetails = null;
$photo_principale_src = "/api/placeholder/400/250/1c1c1c/c9a86c?text=Image+Indisponible";
$error = ''; // Pour erreurs de chargement
$id_villa_form = null;

try {
    $db = config::getConnexion();
    $photoController = new Photo($db);
    $proprieteController = new ProprieteController($db);

    $villaNomGET = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($villaNomGET)) {
        $error = "Aucune villa spécifiée.";
    } else {
        $proprieteDetails = $proprieteController->getVillaByNom($villaNomGET);
        if (!$proprieteDetails) {
            $error = "Villa non trouvée ou invalide.";
            error_log("Tentative d'accès à une villa inexistante : " . $villaNomGET);
        } else {
             // Récupérer photo... (code inchangé)
            if (!empty($proprieteDetails['photo_nom_associe'])) {
                 $photos_logement = $photoController->getPhotosByNom($proprieteDetails['photo_nom_associe']);
                 if (!empty($photos_logement) && isset($photos_logement[0]['image_base64'])) {
                     $imageData = $photos_logement[0]['image_base64'];
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
            $id_villa_form = $proprieteDetails['id_villa'] ?? null;
        }
    }
} catch (Exception $e) {
    error_log("Erreur Init/Fetch dans " . basename(__FILE__) . ": " . $e->getMessage());
    $error = "Erreur critique lors du chargement des informations de la villa.";
    $proprieteDetails = null;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Demande Achat Villa <?php echo $proprieteDetails ? htmlspecialchars($proprieteDetails['nom_villa']) : ''; ?></title>
    <link rel="stylesheet" href="../../style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles CSS existants (formulaire, etc.) */
        .form-container { display: flex; gap: 40px; background-color: rgba(28, 28, 28, 0.7); border: 1px solid rgba(201, 168, 108, 0.2); border-radius: 8px; padding: 40px; max-width: 1100px; margin: 40px auto; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); }
        .form-column { flex: 1; }
        .logement-info-box { background-color: rgba(17, 17, 17, 0.5); border: 1px solid rgba(201, 168, 108, 0.1); border-radius: 5px; padding: 20px; margin-bottom: 20px; }
        .logement-info-box h3 { font-family: 'Montserrat', sans-serif; font-size: 20px; color: var(--gold-primary); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid rgba(201, 168, 108, 0.3); }
        .logement-info-box p { font-size: 15px; line-height: 1.6; color: rgba(248, 245, 235, 0.8); margin-bottom: 8px; }
        .logement-info-box p strong { color: var(--light-text); font-weight: 600; min-width: 120px; display: inline-block; }
        .logement-info-box img { width:100%; margin-top:15px; border-radius:5px; border: 1px solid rgba(201, 168, 108, 0.2); object-fit: cover; height: 250px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 15px; border: 1px solid transparent; display: none; /* Caché par défaut, géré par JS */ align-items: center; gap: 10px; }
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
        textarea.form-control { min-height: 120px; resize: vertical; }
        .contact-btn { background-color: var(--gold-primary); color: var(--darker-bg); font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border: none; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 10px; border-radius: 5px; text-align: center; display: inline-block; text-decoration: none;}
        .contact-btn:hover:not(:disabled) { background-color: var(--light-text); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
        .contact-btn:disabled { background-color: #555; cursor: not-allowed; opacity: 0.7; }
        @media (max-width: 992px) { .form-container { flex-direction: column; } }
        @media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } .form-group { margin-bottom: 20px; } }

        /* --- Styles Popup de Validation (INCLUS ICI POUR GARANTIE) --- */
        .custom-popup-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.75); z-index: 3000;
            display: flex; justify-content: center; align-items: center;
            backdrop-filter: blur(4px); padding: 20px;
            opacity: 0; pointer-events: none; /* Caché et non interactif par défaut */
            transition: opacity 0.3s ease-out;
        }
        .custom-popup-overlay.visible {
            opacity: 1; /* Visible */
            pointer-events: auto; /* Interactif */
        }
        .custom-popup-content {
            background-color: var(--dark-bg); padding: 35px 45px;
            border-radius: 8px; border: 1px solid var(--gold-primary);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6); text-align: center;
            max-width: 480px; width: 95%; position: relative;
            transform: scale(0.95); opacity: 0; /* Pour animation */
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease-out;
        }
        .custom-popup-overlay.visible .custom-popup-content {
            transform: scale(1); /* Animation d'apparition */
            opacity: 1;
        }
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
            white-space: pre-line; /* Respecte les retours à la ligne dans le message JS */
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

             <div id="form-messages" class="alert" style="max-width: 1100px; margin: 20px auto;"></div>

            <?php if(!empty($error) && !$proprieteDetails): // Erreur de chargement initial ?>
                 <div class="alert alert-danger" style="max-width: 1100px; margin: 20px auto; display:flex;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                 <div style="text-align: center; margin-top: 20px;">
                    <a href="reservation.php" class="contact-btn">Retour aux propriétés</a>
                 </div>
            <?php endif; ?>

            <?php if($proprieteDetails): // Afficher si les détails sont chargés ?>
            <div class="form-container">
                <div class="form-column">
                     <div class="form-header">
                        <h2>Informations Villa</h2>
                     </div>
                    <div class="logement-info-box">
                        <h3>Villa <?php echo htmlspecialchars($proprieteDetails['nom_villa'] . ' (' . $proprieteDetails['type_villa'] . ')'); ?></h3>
                        <p><strong>Type :</strong> <?php echo htmlspecialchars($proprieteDetails['type_villa']); ?></p>
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($proprieteDetails['nom_villa']); ?></p>
                        <?php if($proprieteDetails['nb_chambres']): ?><p><strong>Chambres :</strong> <?php echo htmlspecialchars($proprieteDetails['nb_chambres']); ?></p><?php endif; ?>
                        <?php if($proprieteDetails['nb_salles_bain']): ?><p><strong>Salles de bain :</strong> <?php echo htmlspecialchars($proprieteDetails['nb_salles_bain']); ?></p><?php endif; ?>
                        <?php if($proprieteDetails['surface_m2']): ?><p><strong>Surface :</strong> <?php echo htmlspecialchars($proprieteDetails['surface_m2']); ?> m²</p><?php endif; ?>
                        <?php if($proprieteDetails['jardin_m2']): ?><p><strong>Jardin :</strong> <?php echo htmlspecialchars($proprieteDetails['jardin_m2']); ?> m²</p><?php endif; ?>
                        <p><strong>Piscine :</strong> <?php echo $proprieteDetails['piscine'] ? 'Oui' : 'Non'; ?></p>
                        <?php if($proprieteDetails['parking']): ?><p><strong>Parking :</strong> <?php echo htmlspecialchars($proprieteDetails['parking']); ?> places</p><?php endif; ?>
                        <?php if($proprieteDetails['prix_indicatif']): ?><p><strong>Prix Indicatif :</strong> <?php echo number_format($proprieteDetails['prix_indicatif'], 0, ',', ' '); ?> DT</p><?php endif; ?>
                        <img src="<?php echo htmlspecialchars($photo_principale_src); ?>"
                             alt="Image Villa <?php echo htmlspecialchars($proprieteDetails['nom_villa']); ?>"
                             onerror="this.onerror=null; this.src='/api/placeholder/400/250/1c1c1c/c9a86c?text=Image+Erreur';">
                         <?php if($proprieteDetails['description']): ?>
                            <p style="margin-top: 15px;"><strong>Description :</strong><br><?php echo nl2br(htmlspecialchars($proprieteDetails['description'])); ?></p>
                         <?php endif; ?>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-header">
                        <h2>Demande d'Information Achat</h2>
                        <p>Laissez vos coordonnées et questions pour être recontacté par notre équipe commerciale.</p>
                    </div>

                    <form id="demandeVillaForm" method="POST">
                        <input type="hidden" name="id_villa" value="<?php echo htmlspecialchars($id_villa_form ?? ''); ?>">
                        <input type="hidden" name="villa_nom" value="<?php echo htmlspecialchars($proprieteDetails['nom_villa']); ?>">
                        <input type="hidden" name="villa_type" value="<?php echo htmlspecialchars($proprieteDetails['type_villa']); ?>">

                        <div class="form-group">
                            <label for="nom_demandeur">Votre Nom *</label>
                            <input type="text" id="nom_demandeur" name="nom_demandeur" class="form-control" placeholder="Nom et Prénom"
                                   value="<?php echo htmlspecialchars($nom_utilisateur_session ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_demandeur">Votre Email *</label>
                                <input type="email" id="email_demandeur" name="email_demandeur" class="form-control" placeholder="exemple@domaine.com"
                                       value="<?php echo htmlspecialchars($email_utilisateur_session ?? ''); ?>">
                            </div>
                             <div class="form-group">
                                <label for="telephone_demandeur">Votre Téléphone (Optionnel)</label>
                                <input type="tel" id="telephone_demandeur" name="telephone_demandeur" class="form-control" placeholder="+216 XX XXX XXX">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">Votre Message / Questions (Optionnel)</label>
                            <textarea id="message" name="message" class="form-control" rows="5" placeholder="Posez vos questions sur la villa..."></textarea>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" id="submitButton" class="contact-btn">Envoyer ma demande</button>
                        </div>
                    </form>
                    </div>
            </div>
             <?php else: ?>
                 <?php if (empty($error)): ?>
                    <p style="text-align:center; padding: 30px;">Informations sur la villa non disponibles.</p>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="reservation.php" class="contact-btn">Retour aux propriétés</a>
                     </div>
                 <?php endif; ?>
            <?php endif; ?> </div>
    </section>

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
        // --- Fonction pour afficher la popup personnalisée ---
        function showCustomPopup(title, message) {
            const popupOverlay = document.getElementById('validationPopupOverlay');
            const popupTitle = document.getElementById('validationPopupTitle');
            const popupMessage = document.getElementById('validationPopupMessage');
            const closeButton = document.getElementById('validationPopupClose');
            const okButton = document.getElementById('validationPopupOk');

            if (!popupOverlay || !popupTitle || !popupMessage || !closeButton || !okButton) {
                console.error("Éléments du popup de validation introuvables ! Fallback sur alert.");
                alert(title + "\n" + message);
                return;
            }

            popupTitle.textContent = title;
            popupMessage.textContent = message;

            const closePopupHandler = () => {
                if(popupOverlay) popupOverlay.classList.remove('visible');
                if(closeButton) closeButton.removeEventListener('click', closePopupHandler);
                if(okButton) okButton.removeEventListener('click', closePopupHandler);
            };

            closeButton.removeEventListener('click', closePopupHandler); // Clean up previous listener
            okButton.removeEventListener('click', closePopupHandler); // Clean up previous listener
            closeButton.addEventListener('click', closePopupHandler);
            okButton.addEventListener('click', closePopupHandler);

            popupOverlay.classList.add('visible');
        }

        // --- Fonction de validation JS Client ---
        function validateDemandeVilla() {
            const nomInput = document.getElementById('nom_demandeur');
            const emailInput = document.getElementById('email_demandeur');

            if (!nomInput || !emailInput) {
                console.error("Champ Nom ou Email introuvable pour la validation.");
                showCustomPopup('Erreur Interne', 'Impossible de valider le formulaire. Veuillez contacter le support.');
                return false;
            }

            const nom = nomInput.value.trim();
            const email = emailInput.value.trim();

            if (nom === '') {
                showCustomPopup('Nom Requis', 'Veuillez entrer votre nom.');
                nomInput.focus();
                return false;
            }
            if (email === '') {
                showCustomPopup('Email Requis', 'Veuillez entrer votre adresse email.');
                emailInput.focus();
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showCustomPopup('Email Invalide', 'Veuillez entrer une adresse email valide.');
                emailInput.focus();
                return false;
            }
            return true; // Validation réussie
        }

        // --- Gestion AJAX de la soumission ---
        const form = document.getElementById('demandeVillaForm');
        const submitButton = document.getElementById('submitButton');
        const formMessages = document.getElementById('form-messages');
        const formspreeEndpoint = 'https://formspree.io/f/mgvkvoov'; // <<< VOTRE ENDPOINT FORMSPREE >>>
        const dbSaveEndpoint = '../../enregistrer_demande_ajax.php'; // <<< Chemin RELATIF correct >>>

        if (form && submitButton && formMessages) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (!validateDemandeVilla()) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Envoi en cours...';
                formMessages.style.display = 'none';
                formMessages.className = 'alert';

                const formData = new FormData(form);
                const formDataObject = Object.fromEntries(formData.entries());

                fetch(dbSaveEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formDataObject)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                           throw new Error(`Erreur serveur BDD (${response.status}): ${text || 'Réponse vide'}`);
                        });
                    }
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                           if (response.ok && text.trim() === '') {
                               return { success: true };
                           }
                           throw new Error(`Réponse serveur BDD inattendue (non-JSON): ${text}`);
                       });
                    }
                })
                .then(dbData => {
                    if (dbData.success) {
                        console.log('Sauvegarde BDD réussie.');
                        return fetch(formspreeEndpoint, {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                    } else {
                        throw new Error(dbData.error || 'Erreur lors de la sauvegarde BDD.');
                    }
                })
                .then(formspreeResponse => {
                     if (formspreeResponse.ok) {
                         formMessages.innerHTML = '<i class="fas fa-check-circle"></i> Votre demande a été envoyée avec succès !';
                         formMessages.classList.add('alert-success');
                         formMessages.style.display = 'flex';
                         form.reset();

                         // --- *** NOUVEAU : Redirection après succès *** ---
                         setTimeout(() => {
                             window.location.href = 'reservation.php'; // Redirige vers reservation.php
                         }, 3000); // Délai de 3 secondes (3000 ms)
                         // --- *** FIN Redirection *** ---

                     } else {
                         return formspreeResponse.json().then(data => {
                            let errorMsg = "Erreur lors de l'envoi de l'email de notification.";
                            if (data && data.errors && data.errors.length > 0) {
                                errorMsg += " Détails Formspree: " + data.errors.map(e => e.message || e.field).join(', ');
                            } else {
                                errorMsg += ` (Status: ${formspreeResponse.status})`;
                            }
                             throw new Error(errorMsg);
                         }).catch(jsonError => {
                             throw new Error(`Erreur Formspree (Status: ${formspreeResponse.status}), réponse non JSON.`);
                         });
                     }
                 })
                .catch(error => {
                    console.error('Erreur capturée lors de la soumission:', error);
                    formMessages.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Erreur : ${error.message} Veuillez réessayer ou contacter le support.`;
                    formMessages.classList.add('alert-danger');
                    formMessages.style.display = 'flex';
                })
                .finally(() => {
                    // Ne réactive pas le bouton immédiatement s'il y a redirection
                    // submitButton.disabled = false;
                    // submitButton.textContent = 'Envoyer ma demande';
                    // Si la redirection échoue ou si on l'annule, il faudra peut-être réactiver le bouton ici.
                    // Pour l'instant, on le laisse désactivé après succès car on redirige.
                    if (formMessages.classList.contains('alert-danger')) {
                         submitButton.disabled = false;
                         submitButton.textContent = 'Envoyer ma demande';
                    }
                });
            });
        } else {
            console.error("Erreur critique: Le formulaire (#demandeVillaForm), le bouton (#submitButton) ou la zone de messages (#form-messages) est introuvable dans le DOM.");
            const body = document.querySelector('body');
            if(body) {
                const errorDiv = document.createElement('div');
                errorDiv.textContent = "Erreur critique de page. Impossible d'initialiser le formulaire.";
                errorDiv.style.color = 'red';
                errorDiv.style.padding = '20px';
                errorDiv.style.textAlign = 'center';
                body.prepend(errorDiv);
            }
        }

    </script>
</body>
</html>