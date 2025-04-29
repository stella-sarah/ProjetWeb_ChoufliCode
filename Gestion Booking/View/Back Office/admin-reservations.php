<?php
// admin-reservations.php - Gestion Réservations, Propriétés & Demandes Achat

// --- Configuration et Inclusion ---
// Assurez-vous que ces chemins sont corrects pour votre structure
require_once 'C:/xampp/htdocs/Gestion Booking/config.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Visite.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/Visiteclass.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/ReservationSejourController.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/ReservationSejour.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/ProprieteController.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/Villa.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/MaisonHote.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/Hotel.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/Photo.php'; // Contrôleur/Modèle pour les photos
require_once 'C:/xampp/htdocs/Gestion Booking/Controllor/DemandeAchatController.php';
require_once 'C:/xampp/htdocs/Gestion Booking/Model/DemandeAchatVilla.php';

session_start();

// --- Récupérer et nettoyer les messages de la session (Pattern PRG) ---
$message = '';
$error = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}
if (isset($_SESSION['admin_error'])) {
    $error = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}
// --- FIN PRG ---

// --- Vérification Auth Admin ---
// TODO: Ajoutez ici votre logique de vérification si l'utilisateur admin est connecté
// Exemple:
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login-admin.php'); // Rediriger vers la page de login admin
//     exit;
// }
// --- Fin Vérification Auth ---


// Initialisation Connexion & Contrôleurs
try {
    $db = config::getConnexion();
    $visiteController = new VisiteFunctions($db);
    $sejourController = new ReservationSejourController($db);
    $proprieteController = new ProprieteController($db);
    $photoController = new Photo($db);
    $demandeAchatController = new DemandeAchatController($db);
} catch (Exception $e) {
    error_log("Erreur Init dans admin-reservations.php: " . $e->getMessage());
    $_SESSION['admin_error'] = "Erreur critique lors de l'initialisation des services.";
    if (!headers_sent()) {
        header('Location: admin.php');
        exit;
    } else {
        die('<div style="color: red; padding: 20px; border: 1px solid red; margin: 20px;">Erreur critique. Impossible de charger les composants nécessaires.</div>');
    }
}

// --- Traitement des Actions POST (Modification / Ajout) ---

// Action: Modifier une VISITE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_visite') {
    $id_visite = filter_input(INPUT_POST, 'id_visite', FILTER_VALIDATE_INT);
    $id_cin = filter_input(INPUT_POST, 'id_cin', FILTER_SANITIZE_SPECIAL_CHARS);
    $nom_complet = filter_input(INPUT_POST, 'nom_complet', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_visite = filter_input(INPUT_POST, 'date_visite', FILTER_SANITIZE_SPECIAL_CHARS);
    $heure_visite = filter_input(INPUT_POST, 'heure_visite', FILTER_SANITIZE_SPECIAL_CHARS);
    // Récupérer type/nom depuis les champs cachés qui ont été remplis par JS
    $type_villa = filter_input(INPUT_POST, 'edit_visite_type_villa_hidden', FILTER_SANITIZE_SPECIAL_CHARS);
    $nom_villa = filter_input(INPUT_POST, 'edit_visite_nom_villa_hidden', FILTER_SANITIZE_SPECIAL_CHARS);

    $current_error = '';
    if (empty($id_visite) || empty($id_cin) || empty($nom_complet) || empty($date_visite) || empty($heure_visite)) {
        $current_error = "Champs manquants pour modifier la visite.";
    } elseif (!preg_match('/^\d{8}$/', $id_cin)) {
         $current_error = "Le numéro CIN doit contenir exactement 8 chiffres.";
    } else {
        // --- Alternative: Requête directe (comme c'était fait) ---
         $query = "UPDATE visites SET id_cin = :id_cin, nom_complet = :nom_complet, date_visite = :date_visite, heure_visite = :heure_visite, type_villa = :type_villa, nom_villa = :nom_villa WHERE id_visite = :id_visite";
         try {
             $stmt = $db->prepare($query);
             // Bind des valeurs nettoyées
             $stmt->bindParam(':id_cin', $id_cin);
             $stmt->bindParam(':nom_complet', $nom_complet);
             $stmt->bindParam(':date_visite', $date_visite);
             $stmt->bindParam(':heure_visite', $heure_visite);
             $stmt->bindParam(':type_villa', $type_villa); // Assurez-vous que ces champs existent et sont corrects
             $stmt->bindParam(':nom_villa', $nom_villa);   // Assurez-vous que ces champs existent et sont corrects
             $stmt->bindParam(':id_visite', $id_visite, PDO::PARAM_INT);

             if ($stmt->execute()) {
                 $_SESSION['admin_message'] = "Visite ID " . htmlspecialchars($id_visite) . " modifiée.";
             } else {
                 $current_error = "Erreur BDD modification visite ID " . htmlspecialchars($id_visite) . ".";
                 error_log("PDO Error modifierVisite: " . implode(":", $stmt->errorInfo()));
             }
         } catch (PDOException $e) {
             $current_error = "Exception BDD modif visite.";
             error_log("PDO Exception modifierVisite: " . $e->getMessage());
         }
        // --- Fin Alternative ---
    }

    // Stocker l'erreur éventuelle et rediriger
    if (!empty($current_error)) { $_SESSION['admin_error'] = $current_error; }
    header('Location: admin-reservations.php'); exit;
}

// Action: Changer statut d'un SÉJOUR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changer_statut_sejour' && isset($_POST['id_reservation']) && isset($_POST['nouveau_statut'])) {
    $id_sejour_a_modifier = filter_input(INPUT_POST, 'id_reservation', FILTER_VALIDATE_INT);
    $nouveau_statut = filter_input(INPUT_POST, 'nouveau_statut', FILTER_SANITIZE_SPECIAL_CHARS);
    $statutsValides = ['En attente', 'Confirmée', 'Annulée', 'Terminée']; // Statuts permis pour séjour

    if ($id_sejour_a_modifier && in_array($nouveau_statut, $statutsValides)) {
        if ($sejourController->modifierStatutReservation($id_sejour_a_modifier, $nouveau_statut)) {
            $_SESSION['admin_message'] = "Statut du séjour ID " . htmlspecialchars($id_sejour_a_modifier) . " mis à jour vers '" . htmlspecialchars($nouveau_statut) . "'.";
        } else {
            $_SESSION['admin_error'] = "Erreur lors de la mise à jour du statut pour le séjour ID " . htmlspecialchars($id_sejour_a_modifier) . ".";
            error_log("Échec maj statut séjour ID: " . $id_sejour_a_modifier);
        }
    } else {
        $_SESSION['admin_error'] = "Données invalides pour le changement de statut du séjour.";
        error_log("Tentative changement statut séjour invalide. ID: " . ($id_sejour_a_modifier ?? 'N/A') . ", Statut: " . ($nouveau_statut ?? 'N/A'));
    }
    header('Location: admin-reservations.php'); exit;
}

// Action: Changer statut d'une DEMANDE ACHAT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changer_statut_demande_achat' && isset($_POST['id_demande']) && isset($_POST['nouveau_statut_demande'])) {
     $id_demande_a_modifier = filter_input(INPUT_POST, 'id_demande', FILTER_VALIDATE_INT);
     $nouveau_statut = filter_input(INPUT_POST, 'nouveau_statut_demande', FILTER_SANITIZE_SPECIAL_CHARS);
     // Les statuts valides pour une demande d'achat peuvent être différents
     $statutsValidesDemande = ['Nouvelle', 'En cours', 'Contacté', 'Archivée', 'Non intéressé'];

     if ($id_demande_a_modifier && in_array($nouveau_statut, $statutsValidesDemande)) {
         if ($demandeAchatController->modifierStatutDemande($id_demande_a_modifier, $nouveau_statut)) {
             $_SESSION['admin_message'] = "Statut de la demande d'achat ID " . htmlspecialchars($id_demande_a_modifier) . " mis à jour vers '" . htmlspecialchars($nouveau_statut) . "'.";
         } else {
             $_SESSION['admin_error'] = "Erreur lors de la mise à jour du statut pour la demande d'achat ID " . htmlspecialchars($id_demande_a_modifier) . ".";
             error_log("Échec maj statut demande achat ID: " . $id_demande_a_modifier);
         }
     } else {
         $_SESSION['admin_error'] = "Données invalides pour le changement de statut de la demande d'achat.";
          error_log("Tentative changement statut demande achat invalide. ID: " . ($id_demande_a_modifier ?? 'N/A') . ", Statut: " . ($nouveau_statut ?? 'N/A'));
     }
     header('Location: admin-reservations.php'); exit;
}

// --- Action Ajouter une Propriété (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_propriete') {
    $typePropriete = $_POST['type_propriete'] ?? '';
    // Récupérer le fichier photo (sera null si non envoyé)
    $photoFile = $_FILES['photo_principale'] ?? null;
    $local_message = '';
    $local_error = '';
    $success = false;

    // Vérifier le type de propriété
    if (!in_array($typePropriete, ['villa', 'maison_hote', 'hotel'])) {
        $local_error = "Type de propriété invalide sélectionné.";
    } else {
        try {
            // Traitement spécifique selon le type
            if ($typePropriete === 'villa') {
                $villa = new Villa();
                // Assigner les valeurs depuis $_POST aux setters de l'objet Villa
                $villa->setNomVilla($_POST['villa_nom'] ?? null);
                $villa->setTypeVilla($_POST['villa_type'] ?? null);
                $villa->setSurfaceM2($_POST['villa_surface'] ?? null);
                $villa->setNbChambres($_POST['villa_chambres'] ?? null);
                $villa->setNbSallesBain($_POST['villa_sdb'] ?? null);
                $villa->setJardinM2($_POST['villa_jardin'] ?? null);
                $villa->setParking($_POST['villa_parking'] ?? null);
                $villa->setPrixIndicatif($_POST['villa_prix'] ?? null);
                $villa->setPiscine(isset($_POST['villa_piscine'])); // Checkbox
                $villa->setDescription($_POST['villa_description'] ?? null);
                // Assurez-vous que la méthode setVideoUrl existe dans Villa.php
                // $villa->setVideoUrl($_POST['villa_video_url'] ?? null);
                // Appeler le contrôleur pour ajouter la villa ET sa photo
                $success = $proprieteController->ajouterVilla($villa, $photoFile);
                if ($success) $local_message = "Villa '" . htmlspecialchars($villa->getNomVilla()) . "' ajoutée avec succès.";
                else $local_error = "Erreur lors de l'ajout de la villa " . htmlspecialchars($villa->getNomVilla()) . ". Vérifiez les logs.";

            } elseif ($typePropriete === 'maison_hote') {
                $maison = new MaisonHote();
                $maison->setNomMaison($_POST['mh_nom'] ?? null);
                $maison->setTypeMaison($_POST['mh_type'] ?? null);
                $maison->setSurfaceM2($_POST['mh_surface'] ?? null);
                $maison->setNbChambres($_POST['mh_chambres'] ?? null);
                $maison->setCapacitePersonnes($_POST['mh_capacite'] ?? null);
                $maison->setPrixNuit($_POST['mh_prix'] ?? null);
                $maison->setPiscine(isset($_POST['mh_piscine']));
                $maison->setPetitDejeunerInclus(isset($_POST['mh_pdj']));
                $maison->setDescription($_POST['mh_description'] ?? null);
                $success = $proprieteController->ajouterMaisonHote($maison, $photoFile);
                 if ($success) $local_message = "Maison d'hôte '" . htmlspecialchars($maison->getNomMaison()) . "' ajoutée avec succès.";
                 else $local_error = "Erreur lors de l'ajout de la maison d'hôte " . htmlspecialchars($maison->getNomMaison()) . ". Vérifiez les logs.";

            } elseif ($typePropriete === 'hotel') {
                $hotel = new Hotel();
                $hotel->setNomHotel($_POST['hotel_nom'] ?? null);
                $hotel->setTypeHotel($_POST['hotel_type'] ?? null);
                $hotel->setClassementEtoiles($_POST['hotel_etoiles'] ?? null);
                $hotel->setAdresse($_POST['hotel_adresse'] ?? null);
                $hotel->setPrixNuitApd($_POST['hotel_prix_apd'] ?? null);
                $hotel->setServicesCles($_POST['hotel_services'] ?? null);
                $hotel->setDescription($_POST['hotel_description'] ?? null);
                $success = $proprieteController->ajouterHotel($hotel, $photoFile);
                 if ($success) $local_message = "Hôtel '" . htmlspecialchars($hotel->getNomHotel()) . "' ajouté avec succès.";
                 else $local_error = "Erreur lors de l'ajout de l'hôtel " . htmlspecialchars($hotel->getNomHotel()) . ". Vérifiez les logs.";
            }

        } catch (Exception $e) {
            $local_error = "Une exception s'est produite lors de l'ajout : " . $e->getMessage();
            error_log("Exception dans ajout_propriete: " . $e->getMessage());
        }
    }

    // Stocker le message/erreur dans la session et rediriger
    if ($success && !empty($local_message)) {
        $_SESSION['admin_message'] = $local_message;
    } elseif (!empty($local_error)) {
        $_SESSION['admin_error'] = $local_error;
    }
    header('Location: admin-reservations.php');
    exit;
}

// --- Action Modifier une Propriété (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_propriete') {
     $proprieteId = filter_input(INPUT_POST, 'edit_propriete_id', FILTER_VALIDATE_INT);
     $typePropriete = filter_input(INPUT_POST, 'edit_propriete_type', FILTER_SANITIZE_SPECIAL_CHARS);
     $photoFile = $_FILES['photo_principale'] ?? null; // Peut être null si non changé
     $local_message = '';
     $local_error = '';
     $success = false;

     // Vérifier ID et Type
     if (!$proprieteId || !in_array($typePropriete, ['villa', 'maison_hote', 'hotel'])) {
         $local_error = "ID ou type de propriété invalide pour la modification.";
     } else {
         try {
             // Traitement spécifique selon le type
             if ($typePropriete === 'villa') {
                 $villa = new Villa();
                 // Assigner les valeurs depuis $_POST
                 $villa->setNomVilla($_POST['villa_nom'] ?? null);
                 $villa->setTypeVilla($_POST['villa_type'] ?? null);
                 $villa->setSurfaceM2($_POST['villa_surface'] ?? null);
                 $villa->setNbChambres($_POST['villa_chambres'] ?? null);
                 $villa->setNbSallesBain($_POST['villa_sdb'] ?? null);
                 $villa->setJardinM2($_POST['villa_jardin'] ?? null);
                 $villa->setParking($_POST['villa_parking'] ?? null);
                 $villa->setPrixIndicatif($_POST['villa_prix'] ?? null);
                 $villa->setPiscine(isset($_POST['villa_piscine']));
                 $villa->setDescription($_POST['villa_description'] ?? null);
                 // Assurez-vous que la méthode setVideoUrl existe dans Villa.php
                 // $villa->setVideoUrl($_POST['villa_video_url'] ?? null);

                 $success = $proprieteController->modifierVilla($proprieteId, $villa, $photoFile);
                 if ($success) $local_message = "Villa ID " . $proprieteId . " modifiée avec succès.";
                 else $local_error = "Erreur lors de la modification de la villa ID " . $proprieteId . ".";

             } elseif ($typePropriete === 'maison_hote') {
                  $maison = new MaisonHote();
                  $maison->setNomMaison($_POST['mh_nom'] ?? null);
                  $maison->setTypeMaison($_POST['mh_type'] ?? null);
                  $maison->setSurfaceM2($_POST['mh_surface'] ?? null);
                  $maison->setNbChambres($_POST['mh_chambres'] ?? null);
                  $maison->setCapacitePersonnes($_POST['mh_capacite'] ?? null);
                  $maison->setPrixNuit($_POST['mh_prix'] ?? null);
                  $maison->setPiscine(isset($_POST['mh_piscine']));
                  $maison->setPetitDejeunerInclus(isset($_POST['mh_pdj']));
                  $maison->setDescription($_POST['mh_description'] ?? null);

                  $success = $proprieteController->modifierMaisonHote($proprieteId, $maison, $photoFile);
                  if ($success) $local_message = "Maison d'hôte ID " . $proprieteId . " modifiée avec succès.";
                  else $local_error = "Erreur lors de la modification de la maison d'hôte ID " . $proprieteId . ".";

             } elseif ($typePropriete === 'hotel') {
                  $hotel = new Hotel();
                  $hotel->setNomHotel($_POST['hotel_nom'] ?? null);
                  $hotel->setTypeHotel($_POST['hotel_type'] ?? null);
                  $hotel->setClassementEtoiles($_POST['hotel_etoiles'] ?? null);
                  $hotel->setAdresse($_POST['hotel_adresse'] ?? null);
                  $hotel->setPrixNuitApd($_POST['hotel_prix_apd'] ?? null);
                  $hotel->setServicesCles($_POST['hotel_services'] ?? null);
                  $hotel->setDescription($_POST['hotel_description'] ?? null);

                  $success = $proprieteController->modifierHotel($proprieteId, $hotel, $photoFile);
                  if ($success) $local_message = "Hôtel ID " . $proprieteId . " modifié avec succès.";
                  else $local_error = "Erreur lors de la modification de l'hôtel ID " . $proprieteId . ".";
             }

         } catch (Exception $e) {
             $local_error = "Une exception s'est produite lors de la modification : " . $e->getMessage();
             error_log("Exception dans modifier_propriete: " . $e->getMessage());
         }
     }

     // Stocker le message/erreur dans la session et rediriger
     if ($success && !empty($local_message)) {
         $_SESSION['admin_message'] = $local_message;
     } elseif (!empty($local_error)) {
         $_SESSION['admin_error'] = $local_error;
     }
     header('Location: admin-reservations.php');
     exit;
}


// --- Action Modifier un Séjour (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_sejour') {
    $id_reservation = filter_input(INPUT_POST, 'edit_sejour_id', FILTER_VALIDATE_INT);
    $date_debut = filter_input(INPUT_POST, 'edit_sejour_date_debut', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_fin = filter_input(INPUT_POST, 'edit_sejour_date_fin', FILTER_SANITIZE_SPECIAL_CHARS);
    $nb_personnes = filter_input(INPUT_POST, 'edit_sejour_nb_personnes', FILTER_VALIDATE_INT);
    $demandes_speciales = filter_input(INPUT_POST, 'edit_sejour_demandes', FILTER_SANITIZE_SPECIAL_CHARS);

    $local_error = ''; // Erreur spécifique à ce bloc

    // Validation des données
    if (empty($id_reservation)) {
        $local_error = "ID de réservation manquant.";
    } elseif (empty($date_debut) || empty($date_fin) || $nb_personnes === false || $nb_personnes <= 0) {
        $local_error = "Veuillez vérifier les dates et le nombre de personnes (doit être > 0).";
    } elseif (strtotime($date_fin) <= strtotime($date_debut)) {
        $local_error = "La date de fin doit être postérieure à la date de début.";
    }
    // Ajouter d'autres validations si nécessaire

    if (empty($local_error)) {
        // Préparer les données pour le contrôleur
        $data_to_update = [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'nb_personnes' => $nb_personnes,
            'demandes_speciales' => $demandes_speciales ?? '' // Assurer une valeur par défaut
        ];

        // Appeler la nouvelle méthode du contrôleur pour la modification complète
        if ($sejourController->modifierReservationComplet($id_reservation, $data_to_update)) {
            $_SESSION['admin_message'] = "Réservation de séjour ID " . htmlspecialchars($id_reservation) . " modifiée avec succès.";
        } else {
            // L'erreur spécifique peut être loggée dans le contrôleur, ici on met un message générique
            $_SESSION['admin_error'] = "Erreur lors de la modification de la réservation ID " . htmlspecialchars($id_reservation) . ". Consultez les logs.";
        }
    } else {
        // Stocker l'erreur locale dans la session si elle existe
        $_SESSION['admin_error'] = $local_error;
    }

    // Redirection PRG
    header('Location: admin-reservations.php');
    exit;
}
// --- FIN Action Modifier Séjour ---


// --- Traitement des Actions GET (Suppression) ---
// Placé après les POST pour suivre le pattern PRG (Post-Redirect-Get)

// Action: Supprimer une VISITE (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'supprimer_visite' && isset($_GET['id'])) {
    $id_visite_a_supprimer = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_visite_a_supprimer && $visiteController->supprimerVisite($id_visite_a_supprimer)) {
        $_SESSION['admin_message'] = "Réservation de visite ID " . htmlspecialchars($id_visite_a_supprimer) . " supprimée.";
    } else {
        $_SESSION['admin_error'] = "Erreur lors de la suppression de la visite ID " . htmlspecialchars($id_visite_a_supprimer ?? 'inconnu') . ".";
        error_log("Échec suppression visite ID: " . ($id_visite_a_supprimer ?? 'inconnu'));
    }
    header('Location: admin-reservations.php'); exit;
}

// Action: Supprimer un SÉJOUR (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'supprimer_sejour' && isset($_GET['id'])) {
    $id_sejour_a_supprimer = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_sejour_a_supprimer && $sejourController->supprimerReservation($id_sejour_a_supprimer)) {
        $_SESSION['admin_message'] = "Réservation de séjour ID " . htmlspecialchars($id_sejour_a_supprimer) . " supprimée.";
    } else {
        $_SESSION['admin_error'] = "Erreur lors de la suppression du séjour ID " . htmlspecialchars($id_sejour_a_supprimer ?? 'inconnu') . ".";
        error_log("Échec suppression séjour ID: " . ($id_sejour_a_supprimer ?? 'inconnu'));
    }
    header('Location: admin-reservations.php'); exit;
}

// Action: Supprimer une DEMANDE ACHAT (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'supprimer_demande_achat' && isset($_GET['id'])) {
     $id_demande_a_supprimer = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_demande_a_supprimer && $demandeAchatController->supprimerDemandeAchat($id_demande_a_supprimer)) {
        $_SESSION['admin_message'] = "Demande d'achat ID " . htmlspecialchars($id_demande_a_supprimer) . " supprimée.";
    } else {
        $_SESSION['admin_error'] = "Erreur lors de la suppression de la demande d'achat ID " . htmlspecialchars($id_demande_a_supprimer ?? 'inconnu') . ".";
        error_log("Échec suppression demande achat ID: " . ($id_demande_a_supprimer ?? 'inconnu'));
    }
    header('Location: admin-reservations.php'); exit;
}

// Action: Supprimer une PROPRIETE (GET) - CORRIGÉ
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'supprimer_propriete' && isset($_GET['type']) && isset($_GET['id'])) {
    $id_propriete_a_supprimer = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $type_propriete = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
    $success = false;

    // Appeler la méthode de suppression appropriée dans le contrôleur
    if ($id_propriete_a_supprimer && in_array($type_propriete, ['villa', 'maison_hote', 'hotel'])) {
        switch ($type_propriete) {
            case 'villa':
                $success = $proprieteController->supprimerVilla($id_propriete_a_supprimer);
                break;
            case 'maison_hote':
                $success = $proprieteController->supprimerMaisonHote($id_propriete_a_supprimer);
                break;
            case 'hotel':
                $success = $proprieteController->supprimerHotel($id_propriete_a_supprimer);
                break;
        }
    }

    // Mettre à jour le message en fonction du succès
    if ($success) {
        $_SESSION['admin_message'] = "Propriété (" . htmlspecialchars($type_propriete) . ") ID " . htmlspecialchars($id_propriete_a_supprimer) . " supprimée avec succès (y compris la photo associée si elle existait).";
    } else {
         $_SESSION['admin_error'] = "Erreur lors de la suppression de la propriété (" . htmlspecialchars($type_propriete) . ") ID " . htmlspecialchars($id_propriete_a_supprimer ?? 'inconnu') . ". Vérifiez les logs du serveur.";
         error_log("Échec tentative suppression propriété depuis admin-reservations.php: type=" . $type_propriete . ", ID=" . ($id_propriete_a_supprimer ?? 'inconnu'));
    }
    header('Location: admin-reservations.php'); exit;
}
// --- FIN Action Supprimer Propriété ---

// --- Fin Traitement des Actions ---


// --- Récupération des Données pour Affichage ---
// (Après les redirections pour que les données soient à jour)
try {
    $visites = $visiteController->afficherVisites();
    $reservations_sejour = $sejourController->afficherToutesReservations(); // Contient séjours ET demandes achat initialement
    $villas = $proprieteController->getAllVillas(); // Assurez-vous que cela récupère 'video_url'
    $maisons_hotes = $proprieteController->getAllMaisonsHotes();
    $hotels = $proprieteController->getAllHotels();
    $demandes_achat = $demandeAchatController->afficherToutesDemandesAchat(); // Données spécifiques pour la table demandes
} catch (Exception $e) {
    $error = "Erreur lors de la récupération des données pour l'affichage : " . $e->getMessage();
    // Initialiser comme tableaux vides pour éviter les erreurs dans les foreach
    $visites = $reservations_sejour = $villas = $maisons_hotes = $hotels = $demandes_achat = [];
    error_log("Exception lors de la récupération des données pour affichage : " . $e->getMessage());
    // Ajouter l'erreur au message global pour l'affichage
    $_SESSION['admin_error'] = $error; // Stocker dans la session peut être redondant si pas de redirection ici
}
// --- Fin Récupération Données ---

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Admin Réservations & Propriétés</title>
    <link rel="stylesheet" href="../../styleback.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles CSS (copiés depuis la version précédente, vérifier si besoin d'ajustements) */
        body { font-family: 'Montserrat', sans-serif; background-color: var(--darker-bg); color: var(--light-text); }
        .content-area { padding: 25px; }
        .card { background-color: var(--dark-bg); border-radius: 8px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); margin-bottom: 30px; border: 1px solid rgba(201, 168, 108, 0.1); }
        .card-header { padding: 15px 25px; border-bottom: 1px solid rgba(201, 168, 108, 0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;}
        .card-header h3 { font-size: 18px; font-weight: 600; color: var(--gold-primary); font-family: 'Cinzel', serif; letter-spacing: 1px; margin: 0; }
        .card-actions { display: flex; align-items: center; gap: 15px; }
        .refresh-btn { background: none; border: none; color: var(--gold-light); cursor: pointer; font-size: 16px; transition: all 0.3s ease; padding: 5px; }
        .refresh-btn:hover { color: var(--gold-primary); transform: rotate(180deg); }
        .card-body { padding: 0; } /* Retirer padding pour que table-wrapper gère */
        .table-wrapper { overflow-x: auto; padding: 15px 0px; /* Ajouter un peu de padding vertical */}
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(201, 168, 108, 0.1); color: rgba(248, 245, 235, 0.9); font-size: 14px; white-space: nowrap; vertical-align: middle; /* Alignement vertical */ }
        .data-table th { background-color: rgba(28, 28, 28, 0.9); color: var(--gold-primary); font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 1; }
        .data-table tbody tr:hover { background-color: rgba(201, 168, 108, 0.08); }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .action-buttons { display: flex; gap: 8px; align-items: center; }
        .btn { padding: 6px 10px; border-radius: 4px; cursor: pointer; border: none; font-size: 12px; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 28px; background: transparent; }
        .btn i { font-size: 13px; }
        /* Couleurs spécifiques par action */
        .btn-edit { color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.3); } /* Jaune pour edit */
        .btn-edit:hover { background-color: rgba(255, 193, 7, 0.1); border-color: #ffc107; }
        .btn-delete { color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.3); } /* Rouge pour delete */
        .btn-delete:hover { background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545; }
        .btn-view-message { color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.3); } /* Cyan pour voir message */
        .btn-view-message:hover { background-color: rgba(13, 202, 240, 0.1); border-color: #0dcaf0; }
        .btn-pdf {
            background-color: #6c757d; /* Gris */
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-pdf:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .statut-select { padding: 5px 8px; border-radius: 4px; background-color: var(--darker-bg); color: var(--light-text); border: 1px solid rgba(201, 168, 108, 0.2); font-size: 13px; margin-right: 5px; cursor: pointer; }
        .statut-form { display: inline-block; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; text-transform: uppercase; display: inline-block; border: 1px solid transparent; }
        /* Statuts Réservation Séjour */
        .status-en-attente { background-color: rgba(255, 193, 7, 0.1); color: #FFC107; border-color: rgba(255, 193, 7, 0.3); }
        .status-confirmée { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; border-color: rgba(76, 175, 80, 0.3); }
        .status-annulée { background-color: rgba(244, 67, 54, 0.1); color: #F44336; border-color: rgba(244, 67, 54, 0.3); }
        .status-terminée { background-color: rgba(158, 158, 158, 0.1); color: #9E9E9E; border-color: rgba(158, 158, 158, 0.3); }
        /* Statuts Demande Achat */
        .status-demande-nouvelle { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-color: rgba(13, 110, 253, 0.3); } /* Bleu */
        .status-demande-en-cours { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; border-color: rgba(255, 193, 7, 0.3); } /* Jaune */
        .status-demande-contacté { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; border-color: rgba(76, 175, 80, 0.3); } /* Vert */
        .status-demande-archivée { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border-color: rgba(108, 117, 125, 0.3); } /* Gris */
        .status-demande-non-intéressé { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border-color: rgba(220, 53, 69, 0.3); } /* Rouge */

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .alert i { font-size: 1.2em; }
        .alert-success { background-color: rgba(76, 175, 80, 0.15); border-color: rgba(76, 175, 80, 0.3); color: #98FB98; }
        .alert-danger { background-color: rgba(244, 67, 54, 0.15); border-color: rgba(244, 67, 54, 0.3); color: #FFA07A; }

        /* Styles Modals (général) */
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .modal.fade-out { animation: fadeOut 0.3s ease-in forwards; }
        .modal-content { background-color: var(--dark-bg); margin: 5% auto; padding: 30px; border: 1px solid rgba(201, 168, 108, 0.2); border-radius: 8px; width: 90%; max-width: 750px; /* Augmenté pour formulaires */ position: relative; color: var(--light-text); box-shadow: 0 10px 40px rgba(0,0,0,0.5); animation: slideIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; max-height: 90vh; overflow-y: auto; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-content h2 { font-family: 'Cinzel', serif; color: var(--gold-primary); margin-top: 0; margin-bottom: 25px; text-align: center; font-size: 22px; }
        .close { color: var(--gold-light); position: absolute; top: 15px; right: 20px; font-size: 32px; font-weight: bold; cursor: pointer; transition: color 0.3s ease, transform 0.3s ease; z-index: 10; line-height: 1; }
        .close:hover { color: #fff; transform: rotate(90deg); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(201, 168, 108, 0.1); }
        .modal-actions .action-btn { padding: 10px 25px; font-weight: 500; font-size: 14px; border-radius: 5px; min-width: 100px; cursor: pointer; border: 1px solid transparent; transition: all 0.3s ease; }
        .modal-actions .btn-cancel { background-color: transparent; color: var(--gold-light); border-color: rgba(201, 168, 108, 0.3); }
        .modal-actions .btn-cancel:hover { border-color: var(--gold-primary); color: var(--gold-primary); background-color: rgba(201, 168, 108, 0.1); }
        .modal-actions .btn-save { background-color: var(--gold-primary); color: var(--darker-bg); border-color: var(--gold-primary); }
        .modal-actions .btn-save:hover { background-color: var(--gold-light); border-color: var(--gold-light); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .modal-actions .btn-confirm-delete { background-color: #dc3545; color: white; border-color: #dc3545; } /* Rouge pour confirmation suppression */
        .modal-actions .btn-confirm-delete:hover { background-color: #c82333; border-color: #bd2130; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }

        /* Styles Formulaires dans Modals */
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--gold-light); font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid rgba(201, 168, 108, 0.2); border-radius: 4px; background-color: rgba(17, 17, 17, 0.7); /* Plus transparent */ color: var(--light-text); font-size: 14px; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .form-control:disabled { background-color: rgba(40, 40, 40, 0.5); cursor: not-allowed; opacity: 0.7; }
        .form-control:focus { border-color: var(--gold-primary); outline: none; box-shadow: 0 0 0 3px rgba(201, 168, 108, 0.15); }
        textarea.form-control { min-height: 80px; resize: vertical; }

        /* Styles pour le modal ajout/modif propriété */
        #proprieteModal .form-section { display: none; /* Caché par défaut */ border-top: 1px solid rgba(201, 168, 108, 0.1); padding-top: 20px; margin-top: 20px; animation: fadeInSection 0.5s ease-out; }
        #proprieteModal .form-section.active { display: block; }
        @keyframes fadeInSection { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        #proprieteModal .form-group label { font-weight: 500; }
        #proprieteModal .form-control { font-size: 14px; }
        #proprieteModal .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
        #proprieteModal .checkbox-group input[type="checkbox"] { width: auto; margin-right: 5px; flex-shrink: 0; accent-color: var(--gold-primary); } /* Style checkbox */
        #proprieteModal .checkbox-group label { margin-bottom: 0; font-weight: normal; color: rgba(248, 245, 235, 0.8); cursor: pointer; }
        .file-input-wrapper { border: 1px dashed rgba(201, 168, 108, 0.4); padding: 15px; text-align: center; cursor: pointer; background-color: rgba(17, 17, 17, 0.3); border-radius: 4px; display: block; margin-bottom: 10px; transition: background-color 0.3s ease, border-color 0.3s ease; }
        .file-input-wrapper:hover { border-color: var(--gold-primary); background-color: rgba(201, 168, 108, 0.05); }
        .file-input-wrapper span { color: var(--gold-light); font-size: 14px; }
        #photo_preview { max-width: 100%; max-height: 150px; margin-top: 10px; border-radius: 4px; display: none; border: 1px solid rgba(201, 168, 108, 0.2); object-fit: cover; } /* Pour preview */
        #photo_help_text { display: none; margin-top: 5px; color: rgba(248, 245, 235, 0.7); font-size: 12px; } /* Texte aide photo */

        /* Styles pour les tables de propriétés */
        .propriete-table th, .propriete-table td { font-size: 13px; padding: 10px 12px; }
        .propriete-table .fa-image { color: var(--gold-primary); }

        /* Styles pour le popup message */
        .message-content { max-height: 200px; overflow-y: auto; padding: 15px; background: rgba(17,17,17,0.8); border: 1px solid rgba(201, 168, 108, 0.1); border-radius: 4px; margin-top: 5px; line-height: 1.7; color: var(--light-text); font-size: 14px; white-space: pre-wrap; /* Respecter les retours à la ligne */ }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
             .form-row { flex-direction: column; gap: 0; }
             .form-group { margin-bottom: 15px; }
             .modal-content { margin: 5% auto; width: 95%; padding: 20px; }
             .modal-actions { flex-direction: column; gap: 10px; }
             .modal-actions .action-btn { width: 100%; }
             .data-table th, .data-table td { white-space: normal; } /* Permettre le retour à la ligne */
             /* Optionnel: Masquer des colonnes sur petit écran si nécessaire */
             /* .data-table th:nth-child(n), .data-table td:nth-child(n) { display: none; } */
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
         <div class="sidebar-header">
            <div class="logo">
                <img src="../Front Office/logo.png" alt="TuniFy Logo" style="height: 45px;">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>ADMINISTRATION</p>
                </div>
            </div>
        </div>
        <div class="sidebar-nav">
           <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de Bord</span></a></li>
                <li><a href="#"><i class="fas fa-car"></i><span>Transports</span></a></li>
                <li class="active"><a href="admin-reservations.php"><i class="fas fa-calendar-check"></i><span>Réserv./Propriétés</span></a></li>
                <li><a href="#"><i class="fas fa-utensils"></i><span>Restauration</span></a></li>
                <li><a href="#"><i class="fas fa-comments"></i><span>Forums</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i><span>Utilisateurs</span></a></li>
                <li><a href="#"><i class="fas fa-cogs"></i><span>Paramètres</span></a></li>
                <li><a href="../Front Office/reservation.php" target="_blank"><i class="fas fa-external-link-alt"></i><span>Voir le Site</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a></li>
            </ul>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <h2>Gestion Réservations & Propriétés</h2>
            </div>
            <div class="nav-right">
                 <button class="add-btn" onclick="openProprieteModal('add')" style="margin-right: 10px;">
                    <i class="fas fa-plus"></i> Ajouter Propriété
                </button>
                <button class="btn-pdf" onclick="exportAllTablesToPDF()" title="Exporter les données affichées en PDF">
                    <i class="fas fa-file-pdf"></i> Extraire en PDF
                </button>
                <div class="search-box" style="margin-left: 15px;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher dans les tables...">
                </div>
                <div class="user-profile">
                     <img src="/api/placeholder/40/40/1c1c1c/c9a86c?text=A" alt="Admin Profile">
                     <span>Admin</span> <i class="fas fa-chevron-down"></i>
                 </div>
            </div>
        </div>

        <div class="content-area">
             <?php if(!empty($message)): ?>
                 <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
             <?php endif; ?>
             <?php if(!empty($error)): ?>
                 <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
             <?php endif; ?>

             <div class="card">
                <div class="card-header">
                    <h3>Réservations de Visite</h3>
                    <div class="card-actions">
                        <button class="refresh-btn" onclick="window.location.reload()" title="Rafraîchir"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="data-table" id="visitesTable">
                            <thead>
                                <tr>
                                    <th>ID</th><th>CIN</th><th>Nom Complet</th><th>Date Visite</th><th>Heure Visite</th><th>Villa (Type)</th><th>Villa (Nom)</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($visites)): ?>
                                    <?php foreach($visites as $v): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($v['id_visite']); ?></td>
                                            <td><?php echo htmlspecialchars($v['id_cin']); ?></td>
                                            <td><?php echo htmlspecialchars($v['nom_complet']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($v['date_visite']))); ?></td>
                                            <td><?php echo htmlspecialchars($v['heure_visite']); ?></td>
                                            <td><?php echo htmlspecialchars($v['type_villa'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($v['nom_villa'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-edit" title="Modifier Visite" onclick="openEditVisiteModal(<?php echo $v['id_visite']; ?>, '<?php echo htmlspecialchars(addslashes($v['id_cin'])); ?>', '<?php echo htmlspecialchars(addslashes($v['nom_complet'])); ?>', '<?php echo $v['date_visite']; ?>', '<?php echo $v['heure_visite']; ?>', '<?php echo htmlspecialchars(addslashes($v['type_villa'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($v['nom_villa'] ?? '')); ?>')"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-delete" title="Supprimer Visite" onclick="openDeleteModal('visite', <?php echo $v['id_visite']; ?>)"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" style="text-align: center; padding: 20px; color: rgba(248, 245, 235, 0.7);">Aucune réservation de visite trouvée.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>

             <div class="card">
                <div class="card-header">
                    <h3>Réservations de Séjour (Hôtels & Maisons d'hôtes)</h3>
                    <div class="card-actions">
                         <button class="refresh-btn" onclick="window.location.reload()" title="Rafraîchir"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body">
                     <div class="table-wrapper">
                        <table class="data-table" id="sejoursTable">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Utilisateur</th><th>Logement</th><th>Dates Séjour</th><th>Pers.</th><th>Prix Total</th><th>Demandes Spéc.</th><th>Date Rés.</th><th>Statut</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sejour_found = false;
                                if (!empty($reservations_sejour)):
                                    foreach ($reservations_sejour as $res):
                                        $current_statut = $res['statut'] ?? '';
                                        if ($current_statut === 'Demande Achat') continue;
                                        $sejour_found = true;
                                        $statut_lower = strtolower(str_replace(' ', '-', $current_statut));
                                        $statut_class = 'status-' . ($statut_lower ?: 'inconnu');
                                        $details_sejour_json = htmlspecialchars(json_encode(['id' => $res['id_reservation'], 'date_debut' => $res['date_debut'], 'date_fin' => $res['date_fin'], 'nb_personnes' => $res['nb_personnes'], 'demandes_speciales' => $res['demandes_speciales'] ?? '']), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($res['id_reservation']); ?></td>
                                        <td><?php echo htmlspecialchars($res['nom_utilisateur'] ?: ($res['id_utilisateur'] ? 'ID: '.$res['id_utilisateur'] : 'Invité')); ?><?php if(!empty($res['email_utilisateur'])): ?><br><small><?php echo htmlspecialchars($res['email_utilisateur']); ?></small><?php endif; ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($res['type_logement']))); ?><br><small><?php echo htmlspecialchars($res['nom_logement']); ?></small></td>
                                        <td><?php echo $res['date_debut'] ? htmlspecialchars(date('d/m/y', strtotime($res['date_debut']))) : 'N/A'; ?> - <?php echo $res['date_fin'] ? htmlspecialchars(date('d/m/y', strtotime($res['date_fin']))) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($res['nb_personnes'] ?? 'N/A'); ?></td>
                                        <td><?php echo $res['prix_total'] ? number_format($res['prix_total'], 0, ',', ' ') . ' DT' : 'N/C'; ?></td>
                                        <td title="<?php echo htmlspecialchars($res['demandes_speciales'] ?? ''); ?>"><?php echo !empty($res['demandes_speciales']) ? '<i class="fas fa-comment-dots"></i>' : '-'; ?></td>
                                        <td><?php echo $res['date_reservation'] ? htmlspecialchars(date('d/m/y H:i', strtotime($res['date_reservation']))) : 'N/A'; ?></td>
                                        <td><span class="status-badge <?php echo $statut_class; ?>"><?php echo htmlspecialchars($current_statut ?: 'Inconnu'); ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-edit" title="Modifier Séjour" onclick='openEditSejourModal(<?php echo $details_sejour_json; ?>)'><i class="fas fa-edit"></i></button>
                                                <form class="statut-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="changer_statut_sejour">
                                                    <input type="hidden" name="id_reservation" value="<?php echo $res['id_reservation']; ?>">
                                                    <select name="nouveau_statut" class="statut-select" onchange="this.form.submit()" title="Changer le statut">
                                                        <?php $statuts_sejour = ['En attente', 'Confirmée', 'Annulée', 'Terminée']; ?>
                                                        <?php foreach($statuts_sejour as $stat): ?>
                                                            <option value="<?php echo $stat; ?>" <?php echo ($current_statut == $stat ? 'selected' : ''); ?>><?php echo $stat; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                                <button class="btn btn-delete" title="Supprimer Séjour" onclick="openDeleteModal('sejour', <?php echo $res['id_reservation']; ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!$sejour_found): ?>
                                    <tr><td colspan="10" style="text-align: center; padding: 20px; color: rgba(248, 245, 235, 0.7);">Aucune réservation de séjour (hôtel/maison) trouvée.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>

             <div class="card">
                <div class="card-header">
                    <h3>Demandes d'Information Achat Villa</h3>
                    <div class="card-actions">
                         <button class="refresh-btn" onclick="window.location.reload()" title="Rafraîchir"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body">
                     <div class="table-wrapper">
                        <table class="data-table" id="demandesAchatTable">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Villa</th><th>Demandeur</th><th>Contact</th><th>Date Dem.</th><th>Statut</th><th>Message</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($demandes_achat)): ?>
                                    <?php foreach ($demandes_achat as $dem):
                                        $statut_lower = strtolower(str_replace(' ', '-', $dem['statut_demande'] ?? 'nouvelle'));
                                        $statut_class = 'status-demande-' . $statut_lower;
                                        $options_statut_demande = ['Nouvelle', 'En cours', 'Contacté', 'Archivée', 'Non intéressé'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dem['id_demande']); ?></td>
                                        <td><?php echo htmlspecialchars($dem['nom_villa']); ?><?php if (!empty($dem['type_villa'])): ?><br><small>(<?php echo htmlspecialchars($dem['type_villa']); ?>)</small><?php endif; ?><?php if (!empty($dem['id_villa'])): ?><br><small>(ID: <?php echo htmlspecialchars($dem['id_villa']); ?>)</small><?php endif; ?></td>
                                        <td><?php echo htmlspecialchars($dem['nom_demandeur']); ?><?php if (!empty($dem['id_utilisateur'])): ?><br><small>(UID: <?php echo htmlspecialchars($dem['id_utilisateur']); ?>)</small><?php endif; ?></td>
                                        <td><a href="mailto:<?php echo htmlspecialchars($dem['email_demandeur']); ?>" style="color: var(--gold-light);"><?php echo htmlspecialchars($dem['email_demandeur']); ?></a><?php if (!empty($dem['telephone_demandeur'])): ?><br><i class="fas fa-phone-alt fa-xs" style="margin-right: 4px; color: var(--gold-light);"></i> <?php echo htmlspecialchars($dem['telephone_demandeur']); ?><?php endif; ?></td>
                                        <td><?php echo $dem['date_demande'] ? htmlspecialchars(date('d/m/y H:i', strtotime($dem['date_demande']))) : 'N/A'; ?></td>
                                        <td><span class="status-badge <?php echo $statut_class; ?>"><?php echo htmlspecialchars($dem['statut_demande'] ?? 'Nouvelle'); ?></span></td>
                                        <td><?php if (!empty($dem['message'])): ?><button class="btn btn-view-message" title="Voir Message" onclick="showPopupMessage('<?php echo htmlspecialchars(addslashes(nl2br($dem['message']))); ?>')"><i class="fas fa-comment-dots"></i></button><?php else: ?>-<?php endif; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <form class="statut-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="changer_statut_demande_achat">
                                                    <input type="hidden" name="id_demande" value="<?php echo $dem['id_demande']; ?>">
                                                    <select name="nouveau_statut_demande" class="statut-select" onchange="this.form.submit()" title="Changer le statut">
                                                        <?php foreach ($options_statut_demande as $opt): ?>
                                                            <option value="<?php echo $opt; ?>" <?php echo (($dem['statut_demande'] ?? 'Nouvelle') == $opt ? 'selected' : ''); ?>><?php echo $opt; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                                <button class="btn btn-delete" title="Supprimer Demande" onclick="openDeleteModal('demande_achat', <?php echo $dem['id_demande']; ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" style="text-align: center; padding: 20px; color: rgba(248, 245, 235, 0.7);">Aucune demande d'achat de villa trouvée.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>

             <div class="card">
                 <div class="card-header">
                     <h3>Propriétés Enregistrées</h3>
                      <button class="add-btn" onclick="openProprieteModal('add')">
                         <i class="fas fa-plus"></i> Ajouter Propriété
                      </button>
                 </div>
                 <div class="card-body">
                     <h4 style="padding: 15px 25px 0;">Villas</h4>
                     <div class="table-wrapper">
                         <table class="data-table propriete-table" id="villasTable">
                             <thead><tr><th>ID</th><th>Nom</th><th>Type</th><th>Surface</th><th>Ch.</th><th>SdB</th><th>Prix Indicatif</th><th>Photo</th><th>Actions</th></tr></thead>
                             <tbody>
                                 <?php if(!empty($villas)): foreach($villas as $prop):
                                    $prop_details_js = $prop;
                                    $prop_details_js['piscine'] = (bool)($prop_details_js['piscine'] ?? false);
                                    // *** AJOUT : Inclure video_url dans les détails JS ***
                                    // Assurez-vous que $prop['video_url'] existe (récupéré par le contrôleur)
                                    $prop_details_js['video_url'] = $prop['video_url'] ?? '';
                                    // *** FIN AJOUT ***
                                    $details_json = htmlspecialchars(json_encode($prop_details_js), ENT_QUOTES, 'UTF-8');
                                 ?>
                                 <tr data-details='<?php echo $details_json; ?>'>
                                     <td><?php echo $prop['id_villa']; ?></td>
                                     <td><?php echo htmlspecialchars($prop['nom_villa']); ?></td>
                                     <td><?php echo htmlspecialchars($prop['type_villa']); ?></td>
                                     <td><?php echo $prop['surface_m2'] ? $prop['surface_m2'].' m²' : 'N/A'; ?></td>
                                     <td><?php echo $prop['nb_chambres'] ?? 'N/A'; ?></td>
                                     <td><?php echo $prop['nb_salles_bain'] ?? 'N/A'; ?></td>
                                     <td><?php echo $prop['prix_indicatif'] ? number_format($prop['prix_indicatif'], 0, ',', ' ').' DT' : 'N/A'; ?></td>
                                     <td><?php echo !empty($prop['photo_nom_associe']) ? '<i class="fas fa-image" title="'.htmlspecialchars($prop['photo_nom_associe']).'"></i>' : '-'; ?></td>
                                     <td>
                                         <div class="action-buttons">
                                             <button class="btn btn-edit" title="Modifier Villa" onclick="openProprieteModal('edit', 'villa', <?php echo $prop['id_villa']; ?>, this)"><i class="fas fa-edit"></i></button>
                                             <button class="btn btn-delete" title="Supprimer Villa" onclick="openDeleteModal('villa', <?php echo $prop['id_villa']; ?>)"><i class="fas fa-trash"></i></button>
                                         </div>
                                     </td>
                                 </tr>
                                 <?php endforeach; else: ?>
                                 <tr><td colspan="9" style="text-align: center; padding: 15px; color: rgba(248, 245, 235, 0.7);">Aucune villa enregistrée.</td></tr>
                                 <?php endif; ?>
                             </tbody>
                         </table>
                     </div>

                     <h4 style="padding: 15px 25px 0; margin-top: 20px;">Maisons d'Hôtes</h4>
                     <div class="table-wrapper">
                         <table class="data-table propriete-table" id="maisonsHotesTable">
                              <thead><tr><th>ID</th><th>Nom</th><th>Type</th><th>Capacité</th><th>Ch.</th><th>Prix/Nuit</th><th>Photo</th><th>Actions</th></tr></thead>
                              <tbody>
                                 <?php if(!empty($maisons_hotes)): foreach($maisons_hotes as $prop):
                                    $prop_details_js = $prop;
                                    $prop_details_js['petit_dejeuner_inclus'] = (bool)($prop_details_js['petit_dejeuner_inclus'] ?? false);
                                    $prop_details_js['piscine'] = (bool)($prop_details_js['piscine'] ?? false);
                                    $details_json = htmlspecialchars(json_encode($prop_details_js), ENT_QUOTES, 'UTF-8');
                                 ?>
                                 <tr data-details='<?php echo $details_json; ?>'>
                                     <td><?php echo $prop['id_maison_hote']; ?></td>
                                     <td><?php echo htmlspecialchars($prop['nom_maison']); ?></td>
                                     <td><?php echo htmlspecialchars($prop['type_maison']); ?></td>
                                     <td><?php echo $prop['capacite_personnes'] ?? 'N/A'; ?> pers.</td>
                                     <td><?php echo $prop['nb_chambres'] ?? 'N/A'; ?></td>
                                     <td><?php echo $prop['prix_nuit'] ? number_format($prop['prix_nuit'], 0, ',', ' ').' DT' : 'N/A'; ?></td>
                                     <td><?php echo !empty($prop['photo_nom_associe']) ? '<i class="fas fa-image" title="'.htmlspecialchars($prop['photo_nom_associe']).'"></i>' : '-'; ?></td>
                                     <td>
                                         <div class="action-buttons">
                                             <button class="btn btn-edit" title="Modifier Maison" onclick="openProprieteModal('edit', 'maison_hote', <?php echo $prop['id_maison_hote']; ?>, this)"><i class="fas fa-edit"></i></button>
                                             <button class="btn btn-delete" title="Supprimer Maison" onclick="openDeleteModal('maison_hote', <?php echo $prop['id_maison_hote']; ?>)"><i class="fas fa-trash"></i></button>
                                         </div>
                                     </td>
                                 </tr>
                                 <?php endforeach; else: ?>
                                  <tr><td colspan="8" style="text-align: center; padding: 15px; color: rgba(248, 245, 235, 0.7);">Aucune maison d'hôte enregistrée.</td></tr>
                                  <?php endif; ?>
                              </tbody>
                         </table>
                     </div>

                     <h4 style="padding: 15px 25px 0; margin-top: 20px;">Hôtels</h4>
                      <div class="table-wrapper">
                         <table class="data-table propriete-table" id="hotelsTable">
                              <thead><tr><th>ID</th><th>Nom</th><th>Type</th><th>Étoiles</th><th>Prix Apd</th><th>Photo</th><th>Actions</th></tr></thead>
                              <tbody>
                                 <?php if(!empty($hotels)): foreach($hotels as $prop):
                                     $details_json = htmlspecialchars(json_encode($prop), ENT_QUOTES, 'UTF-8');
                                 ?>
                                 <tr data-details='<?php echo $details_json; ?>'>
                                     <td><?php echo $prop['id_hotel']; ?></td>
                                     <td><?php echo htmlspecialchars($prop['nom_hotel']); ?></td>
                                     <td><?php echo htmlspecialchars($prop['type_hotel']); ?></td>
                                     <td><?php echo $prop['classement_etoiles'] ? $prop['classement_etoiles'].' *' : 'N/A'; ?></td>
                                     <td><?php echo $prop['prix_nuit_apd'] ? number_format($prop['prix_nuit_apd'], 0, ',', ' ').' DT' : 'N/A'; ?></td>
                                     <td><?php echo !empty($prop['photo_nom_associe']) ? '<i class="fas fa-image" title="'.htmlspecialchars($prop['photo_nom_associe']).'"></i>' : '-'; ?></td>
                                     <td>
                                         <div class="action-buttons">
                                             <button class="btn btn-edit" title="Modifier Hôtel" onclick="openProprieteModal('edit', 'hotel', <?php echo $prop['id_hotel']; ?>, this)"><i class="fas fa-edit"></i></button>
                                             <button class="btn btn-delete" title="Supprimer Hôtel" onclick="openDeleteModal('hotel', <?php echo $prop['id_hotel']; ?>)"><i class="fas fa-trash"></i></button>
                                          </div>
                                      </td>
                                 </tr>
                                 <?php endforeach; else: ?>
                                  <tr><td colspan="7" style="text-align: center; padding: 15px; color: rgba(248, 245, 235, 0.7);">Aucun hôtel enregistré.</td></tr>
                                  <?php endif; ?>
                              </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             </div> </div> <div id="editVisiteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditVisiteModal()">&times;</span>
            <h2>Modifier la réservation de visite</h2>
            <form id="editVisiteForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
                <input type="hidden" name="action" value="modifier_visite">
                <input type="hidden" id="edit_id_visite" name="id_visite">
                <input type="hidden" id="edit_visite_type_villa_hidden" name="edit_visite_type_villa_hidden">
                <input type="hidden" id="edit_visite_nom_villa_hidden" name="edit_visite_nom_villa_hidden">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_visite_id_cin">Numéro CIN *</label>
                        <input type="text" id="edit_visite_id_cin" name="id_cin" class="form-control" required pattern="\d{8}" title="Le CIN doit contenir 8 chiffres">
                    </div>
                    <div class="form-group">
                        <label for="edit_visite_nom_complet">Nom Complet *</label>
                        <input type="text" id="edit_visite_nom_complet" name="nom_complet" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_visite_date_visite">Date de Visite *</label>
                        <input type="date" id="edit_visite_date_visite" name="date_visite" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_visite_heure_visite">Heure de Visite *</label>
                        <select id="edit_visite_heure_visite" name="heure_visite" class="form-control" required>
                            <option value="09:00">09:00</option> <option value="10:00">10:00</option> <option value="11:00">11:00</option>
                            <option value="14:00">14:00</option> <option value="15:00">15:00</option> <option value="16:00">16:00</option> <option value="17:00">17:00</option>
                        </select>
                    </div>
                </div>
                 <div class="form-row">
                     <div class="form-group">
                         <label for="edit_visite_type_villa_display">Type de Villa</label>
                         <input type="text" id="edit_visite_type_villa_display" class="form-control" disabled>
                     </div>
                     <div class="form-group">
                         <label for="edit_visite_nom_villa_display">Nom de Villa</label>
                         <input type="text" id="edit_visite_nom_villa_display" class="form-control" disabled>
                     </div>
                 </div>
                <div class="modal-actions">
                    <button type="button" class="action-btn btn-cancel" onclick="closeEditVisiteModal()">Annuler</button>
                    <button type="submit" class="action-btn btn-save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editSejourModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditSejourModal()">&times;</span>
            <h2>Modifier la Réservation de Séjour</h2>
            <form id="editSejourForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
                <input type="hidden" name="action" value="modifier_sejour">
                <input type="hidden" id="edit_sejour_id" name="edit_sejour_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_sejour_date_debut">Date d'arrivée *</label>
                        <input type="date" id="edit_sejour_date_debut" name="edit_sejour_date_debut" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_sejour_date_fin">Date de départ *</label>
                        <input type="date" id="edit_sejour_date_fin" name="edit_sejour_date_fin" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                     <div class="form-group">
                        <label for="edit_sejour_nb_personnes">Nombre de personnes *</label>
                        <input type="number" id="edit_sejour_nb_personnes" name="edit_sejour_nb_personnes" class="form-control" required min="1" step="1">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_sejour_demandes">Demandes spéciales</label>
                    <textarea id="edit_sejour_demandes" name="edit_sejour_demandes" class="form-control" rows="3" placeholder="Préférences, commentaires..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="action-btn btn-cancel" onclick="closeEditSejourModal()">Annuler</button>
                    <button type="submit" class="action-btn btn-save">Enregistrer Modifications</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
       <div class="modal-content" style="max-width: 500px;">
           <span class="close" onclick="closeDeleteModal()">&times;</span>
           <h2 id="deleteModalTitle">Confirmer la suppression</h2>
           <p id="deleteModalText" style="text-align: center; margin: 20px 0; line-height: 1.6;">Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
           <div class="modal-actions">
               <button type="button" class="action-btn btn-cancel" onclick="closeDeleteModal()">Annuler</button>
               <button type="button" class="action-btn btn-confirm-delete" id="confirmDeleteBtn">Supprimer</button>
           </div>
       </div>
   </div>

    <div id="proprieteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeProprieteModal()">&times;</span>
            <h2 id="proprieteModalTitle">Ajouter Propriété</h2>
            <form id="proprieteForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" novalidate>
                 <input type="hidden" id="form_action" name="action" value="ajouter_propriete">
                 <input type="hidden" id="edit_propriete_id" name="edit_propriete_id" value="">
                 <input type="hidden" id="edit_propriete_type" name="edit_propriete_type" value="">
                 <div class="form-group">
                    <label for="type_propriete">Type de Propriété *</label>
                    <select id="type_propriete" name="type_propriete" class="form-control" required onchange="showProprieteFields()">
                        <option value="" disabled selected>-- Sélectionner le type --</option>
                        <option value="villa">Villa</option> <option value="maison_hote">Maison d'Hôte</option> <option value="hotel">Hôtel</option>
                    </select>
                </div>
                 <div class="form-group">
                     <label for="photo_principale">Photo Principale</label>
                     <input type="file" id="photo_principale" name="photo_principale" accept="image/jpeg,image/png,image/gif" style="display: none;" onchange="previewPhoto(event, 'photo_preview', 'file_input_text')">
                     <label for="photo_principale" class="file-input-wrapper"><i class="fas fa-upload"></i> <span id="file_input_text">Choisir une image...</span></label>
                     <img id="photo_preview" src="#" alt="Aperçu photo"/>
                     <small id="photo_help_text" style="display: none; margin-top: 5px; color: rgba(248, 245, 235, 0.7);">Laissez vide pour ne pas changer la photo.</small>
                 </div>
                 <div id="fields_villa" class="form-section">
                     <h4>Détails Villa</h4>
                     <div class="form-row">
                         <div class="form-group"><label for="villa_nom">Nom Villa *</label><input type="text" id="villa_nom" name="villa_nom" class="form-control" placeholder="Ex: KMAR" required></div>
                         <div class="form-group"><label for="villa_type">Type Villa *</label><input type="text" id="villa_type" name="villa_type" class="form-control" placeholder="Ex: S+3" required></div>
                     </div>
                     <div class="form-row">
                         <div class="form-group"><label for="villa_surface">Surface (m²)</label><input type="number" id="villa_surface" name="villa_surface" class="form-control" min="0" step="any"></div>
                         <div class="form-group"><label for="villa_chambres">Nb Chambres</label><input type="number" id="villa_chambres" name="villa_chambres" class="form-control" min="0" step="1"></div>
                         <div class="form-group"><label for="villa_sdb">Nb Salles de Bain</label><input type="number" id="villa_sdb" name="villa_sdb" class="form-control" min="0" step="1"></div>
                     </div>
                      <div class="form-row">
                         <div class="form-group"><label for="villa_jardin">Jardin (m²)</label><input type="number" id="villa_jardin" name="villa_jardin" class="form-control" min="0" step="any"></div>
                         <div class="form-group"><label for="villa_parking">Parking (places)</label><input type="number" id="villa_parking" name="villa_parking" class="form-control" min="0" step="1"></div>
                         <div class="form-group"><label for="villa_prix">Prix Indicatif (DT)</label><input type="number" id="villa_prix" name="villa_prix" class="form-control" min="0" step="any"></div>
                     </div>
                     <div class="form-group">
                         <label for="villa_video_url">URL Vidéo (Optionnel)</label>
                         <input type="url" id="villa_video_url" name="villa_video_url" class="form-control" placeholder="https://exemple.com/video.mp4">
                     </div>
                     <div class="form-group checkbox-group"><input type="checkbox" id="villa_piscine" name="villa_piscine" value="1"><label for="villa_piscine">Piscine disponible</label></div>
                     <div class="form-group"><label for="villa_description">Description</label><textarea id="villa_description" name="villa_description" class="form-control" rows="3"></textarea></div>
                 </div>
                 <div id="fields_maison_hote" class="form-section">
                     <h4>Détails Maison d'Hôte</h4>
                     <div class="form-row">
                          <div class="form-group"><label for="mh_nom">Nom Maison *</label><input type="text" id="mh_nom" name="mh_nom" class="form-control" required></div>
                         <div class="form-group"><label for="mh_type">Type Maison *</label><input type="text" id="mh_type" name="mh_type" class="form-control" required></div>
                     </div>
                     <div class="form-row">
                          <div class="form-group"><label for="mh_surface">Surface (m²)</label><input type="number" id="mh_surface" name="mh_surface" class="form-control" min="0" step="any"></div>
                          <div class="form-group"><label for="mh_chambres">Nb Chambres</label><input type="number" id="mh_chambres" name="mh_chambres" class="form-control" min="0" step="1"></div>
                          <div class="form-group"><label for="mh_capacite">Capacité (pers.)</label><input type="number" id="mh_capacite" name="mh_capacite" class="form-control" min="1" step="1"></div>
                          <div class="form-group"><label for="mh_prix">Prix / Nuit (DT)</label><input type="number" id="mh_prix" name="mh_prix" class="form-control" min="0" step="any"></div>
                     </div>
                     <div class="form-row">
                         <div class="form-group checkbox-group" style="flex: 1;"><input type="checkbox" id="mh_piscine" name="mh_piscine" value="1"><label for="mh_piscine">Piscine</label></div>
                         <div class="form-group checkbox-group" style="flex: 1;"><input type="checkbox" id="mh_pdj" name="mh_pdj" value="1"><label for="mh_pdj">Petit Déjeuner Inclus</label></div>
                     </div>
                      <div class="form-group"><label for="mh_description">Description</label><textarea id="mh_description" name="mh_description" class="form-control" rows="3"></textarea></div>
                 </div>
                 <div id="fields_hotel" class="form-section">
                     <h4>Détails Hôtel</h4>
                     <div class="form-row">
                          <div class="form-group"><label for="hotel_nom">Nom Hôtel *</label><input type="text" id="hotel_nom" name="hotel_nom" class="form-control" required></div>
                         <div class="form-group"><label for="hotel_type">Type Hôtel *</label><input type="text" id="hotel_type" name="hotel_type" class="form-control" required></div>
                         <div class="form-group"><label for="hotel_etoiles">Étoiles</label><input type="number" id="hotel_etoiles" name="hotel_etoiles" class="form-control" min="1" max="5" step="1"></div>
                     </div>
                       <div class="form-group"><label for="hotel_adresse">Adresse</label><input type="text" id="hotel_adresse" name="hotel_adresse" class="form-control"></div>
                     <div class="form-row">
                         <div class="form-group"><label for="hotel_prix_apd">Prix / Nuit (apd) (DT)</label><input type="number" id="hotel_prix_apd" name="hotel_prix_apd" class="form-control" min="0" step="any"></div>
                          <div class="form-group"><label for="hotel_services">Services Clés</label><input type="text" id="hotel_services" name="hotel_services" class="form-control"></div>
                     </div>
                     <div class="form-group"><label for="hotel_description">Description</label><textarea id="hotel_description" name="hotel_description" class="form-control" rows="3"></textarea></div>
                 </div>
                 <div class="modal-actions">
                     <button type="button" class="action-btn btn-cancel" onclick="closeProprieteModal()">Annuler</button>
                     <button type="submit" id="proprieteSubmitButton" class="action-btn btn-save">Ajouter Propriété</button>
                 </div>
            </form>
        </div>
    </div>

    <div id="messagePopupModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="closePopupMessage()">&times;</span>
            <h2>Message du Demandeur</h2>
            <div id="messagePopupContent" class="message-content"></div>
            <div class="modal-actions" style="margin-top: 20px; border-top: none;">
                <button type="button" class="action-btn btn-cancel" onclick="closePopupMessage()">Fermer</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Références aux éléments DOM ---
            const editVisiteModal = document.getElementById('editVisiteModal');
            const editSejourModal = document.getElementById('editSejourModal');
            const deleteModal = document.getElementById('deleteModal');
            const proprieteModal = document.getElementById('proprieteModal');
            const messagePopupModal = document.getElementById('messagePopupModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const proprieteForm = document.getElementById('proprieteForm');
            const proprieteModalTitle = document.getElementById('proprieteModalTitle');
            const proprieteSubmitButton = document.getElementById('proprieteSubmitButton');
            const formActionInput = document.getElementById('form_action');
            const editIdInput = document.getElementById('edit_propriete_id');
            const editTypeInput = document.getElementById('edit_propriete_type');
            const typeSelect = document.getElementById('type_propriete');
            const photoPreview = document.getElementById('photo_preview');
            const fileInputText = document.getElementById('file_input_text');
            const photoHelpText = document.getElementById('photo_help_text');
            const searchInput = document.getElementById('searchInput');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const messagePopupContent = document.getElementById('messagePopupContent');
            const editSejourForm = document.getElementById('editSejourForm');
            const editSejourIdInput = document.getElementById('edit_sejour_id');
            const editSejourDateDebutInput = document.getElementById('edit_sejour_date_debut');
            const editSejourDateFinInput = document.getElementById('edit_sejour_date_fin');
            const editSejourNbPersonnesInput = document.getElementById('edit_sejour_nb_personnes');
            const editSejourDemandesInput = document.getElementById('edit_sejour_demandes');

            let elementToDelete = { type: null, id: null };

            // --- Fonctions pour les Modals ---
            const closeModal = (modalElement) => {
                 if (modalElement) {
                    modalElement.classList.add('fade-out');
                    setTimeout(() => {
                        modalElement.style.display = 'none';
                        modalElement.classList.remove('fade-out');
                    }, 300);
                }
            };
            window.openEditVisiteModal = function(id, cin, nom, date, heure, typeVilla, nomVilla) {
                 if (!editVisiteModal) return;
                 document.getElementById('edit_id_visite').value = id;
                 document.getElementById('edit_visite_id_cin').value = cin;
                 document.getElementById('edit_visite_nom_complet').value = nom;
                 document.getElementById('edit_visite_date_visite').value = date;
                 document.getElementById('edit_visite_heure_visite').value = heure;
                 document.getElementById('edit_visite_type_villa_display').value = typeVilla || 'N/A';
                 document.getElementById('edit_visite_nom_villa_display').value = nomVilla || 'N/A';
                 document.getElementById('edit_visite_type_villa_hidden').value = typeVilla || '';
                 document.getElementById('edit_visite_nom_villa_hidden').value = nomVilla || '';
                 editVisiteModal.style.display = 'block';
                 editVisiteModal.classList.remove('fade-out');
            };
            window.closeEditVisiteModal = function() { closeModal(editVisiteModal); };
            window.openEditSejourModal = function(sejourData) {
                 if (!editSejourModal || !sejourData) return;
                 editSejourIdInput.value = sejourData.id || '';
                 editSejourDateDebutInput.value = sejourData.date_debut || '';
                 editSejourDateFinInput.value = sejourData.date_fin || '';
                 editSejourNbPersonnesInput.value = sejourData.nb_personnes || '';
                 editSejourDemandesInput.value = sejourData.demandes_speciales || '';
                 const today = new Date().toISOString().split('T')[0];
                 editSejourDateDebutInput.min = today;
                 if (sejourData.date_debut) {
                      const debut = new Date(sejourData.date_debut);
                      const finMin = new Date(debut);
                      finMin.setDate(finMin.getDate() + 1);
                      editSejourDateFinInput.min = finMin.toISOString().split('T')[0];
                 } else {
                      editSejourDateFinInput.min = today;
                 }
                 editSejourModal.style.display = 'block';
                 editSejourModal.classList.remove('fade-out');
             };
            window.closeEditSejourModal = function() { closeModal(editSejourModal); };
            if(editSejourDateDebutInput && editSejourDateFinInput) {
                 editSejourDateDebutInput.addEventListener('change', function() {
                     const debut = new Date(this.value);
                     const finMin = new Date(debut);
                     finMin.setDate(finMin.getDate() + 1);
                     editSejourDateFinInput.min = finMin.toISOString().split('T')[0];
                     if (editSejourDateFinInput.value && new Date(editSejourDateFinInput.value) < finMin) {
                          editSejourDateFinInput.value = '';
                     }
                 });
             }
            window.openDeleteModal = function(type, id) {
                 if (!deleteModal) return;
                 elementToDelete.type = type;
                 elementToDelete.id = id;
                 const textElement = document.getElementById('deleteModalText');
                 let typeText = type.replace(/_/g, ' ');
                 typeText = typeText.charAt(0).toUpperCase() + typeText.slice(1);
                 if (textElement) textElement.textContent = `Êtes-vous sûr de vouloir supprimer : ${typeText} (ID: ${id}) ? Cette action est irréversible.`;
                 const titleElement = document.getElementById('deleteModalTitle');
                 if(titleElement) titleElement.textContent = `Confirmer Suppression ${typeText}`;
                 deleteModal.style.display = 'block';
                 deleteModal.classList.remove('fade-out');
             };
            window.closeDeleteModal = function() {
                 closeModal(deleteModal);
                 elementToDelete = { type: null, id: null };
             };
            if (confirmDeleteBtn) {
                 confirmDeleteBtn.addEventListener('click', function() {
                    if (elementToDelete.id && elementToDelete.type) {
                        let actionUrl = 'admin-reservations.php?id=' + elementToDelete.id + '&action=';
                        switch (elementToDelete.type) {
                            case 'visite':        actionUrl += 'supprimer_visite'; break;
                            case 'sejour':        actionUrl += 'supprimer_sejour'; break;
                            case 'demande_achat': actionUrl += 'supprimer_demande_achat'; break;
                            case 'villa':         actionUrl += 'supprimer_propriete&type=villa'; break;
                            case 'maison_hote':   actionUrl += 'supprimer_propriete&type=maison_hote'; break;
                            case 'hotel':         actionUrl += 'supprimer_propriete&type=hotel'; break;
                            default: actionUrl = ''; console.error("Type de suppression non reconnu:", elementToDelete.type);
                        }
                        if (actionUrl) window.location.href = actionUrl;
                        else closeDeleteModal();
                    } else {
                        console.error("Erreur: ID ou Type manquant pour la suppression.");
                        closeDeleteModal();
                    }
                 });
             }
            window.openProprieteModal = function(mode, type = '', id = null, buttonElement = null) {
                 if (!proprieteModal || !proprieteForm) return;
                 proprieteForm.reset();
                 if(photoPreview) { photoPreview.style.display = 'none'; photoPreview.src = '#'; }
                 if(fileInputText) fileInputText.textContent = 'Choisir une image...';
                 if(photoHelpText) photoHelpText.style.display = 'none';
                 document.querySelectorAll('#proprieteModal .form-section').forEach(sec => sec.classList.remove('active'));

                 if (mode === 'add') {
                     proprieteModalTitle.textContent = 'Ajouter une Propriété';
                     proprieteSubmitButton.textContent = 'Ajouter Propriété';
                     formActionInput.value = 'ajouter_propriete';
                     editIdInput.value = '';
                     editTypeInput.value = '';
                     typeSelect.value = '';
                     typeSelect.disabled = false;
                 }
                 else if (mode === 'edit' && type && id && buttonElement) {
                     proprieteModalTitle.textContent = `Modifier Propriété (${type.replace('_', ' ')})`;
                     proprieteSubmitButton.textContent = 'Enregistrer Modifications';
                     formActionInput.value = 'modifier_propriete';
                     editIdInput.value = id;
                     editTypeInput.value = type;
                     typeSelect.value = type;
                     typeSelect.disabled = true;
                     const tr = buttonElement.closest('tr');
                     const details = tr ? JSON.parse(tr.getAttribute('data-details') || '{}') : {};
                     showProprieteFields();
                     fillProprieteForm(type, details);
                     if (photoHelpText) photoHelpText.style.display = 'block';
                 } else {
                     console.error("Mode d'ouverture du modal Propriété invalide.");
                     return;
                 }
                 proprieteModal.style.display = 'block';
                 proprieteModal.classList.remove('fade-out');
             };
            window.closeProprieteModal = function() {
                 closeModal(proprieteModal);
                 if(typeSelect) typeSelect.disabled = false;
             };
            window.showProprieteFields = function() {
                 const type = typeSelect ? typeSelect.value : '';
                 const sections = ['fields_villa', 'fields_maison_hote', 'fields_hotel'];
                 const requiredInputsConfig = {
                     'villa': ['villa_nom', 'villa_type'],
                     'maison_hote': ['mh_nom', 'mh_type'],
                     'hotel': ['hotel_nom', 'hotel_type']
                 };
                 document.querySelectorAll('#proprieteModal .form-section input, #proprieteModal .form-section textarea, #proprieteModal .form-section select').forEach(input => input.required = false);
                 sections.forEach(id => {
                     const sectionElement = document.getElementById(id);
                     if (sectionElement) {
                         if ('fields_' + type === id) {
                             sectionElement.classList.add('active');
                              if (requiredInputsConfig[type]) {
                                 requiredInputsConfig[type].forEach(inputId => {
                                     const inputElement = document.getElementById(inputId);
                                     if (inputElement) inputElement.required = true;
                                 });
                             }
                         } else {
                             sectionElement.classList.remove('active');
                         }
                     }
                 });
             };
            const fillProprieteForm = (type, details) => { // *** MODIFIÉ pour inclure video_url ***
                 if (!details) return;
                 if (type === 'villa') {
                     document.getElementById('villa_nom').value = details.nom_villa || '';
                     document.getElementById('villa_type').value = details.type_villa || '';
                     document.getElementById('villa_surface').value = details.surface_m2 || '';
                     document.getElementById('villa_chambres').value = details.nb_chambres || '';
                     document.getElementById('villa_sdb').value = details.nb_salles_bain || '';
                     document.getElementById('villa_jardin').value = details.jardin_m2 || '';
                     document.getElementById('villa_parking').value = details.parking || '';
                     document.getElementById('villa_prix').value = details.prix_indicatif || '';
                     document.getElementById('villa_piscine').checked = details.piscine || false;
                     document.getElementById('villa_description').value = details.description || '';
                     const videoUrlInput = document.getElementById('villa_video_url'); // *** AJOUT ***
                     if (videoUrlInput) videoUrlInput.value = details.video_url || ''; // *** AJOUT ***
                 } else if (type === 'maison_hote') {
                     document.getElementById('mh_nom').value = details.nom_maison || '';
                     document.getElementById('mh_type').value = details.type_maison || '';
                     document.getElementById('mh_surface').value = details.surface_m2 || '';
                     document.getElementById('mh_chambres').value = details.nb_chambres || '';
                     document.getElementById('mh_capacite').value = details.capacite_personnes || '';
                     document.getElementById('mh_prix').value = details.prix_nuit || '';
                     document.getElementById('mh_piscine').checked = details.piscine || false;
                     document.getElementById('mh_pdj').checked = details.petit_dejeuner_inclus || false;
                     document.getElementById('mh_description').value = details.description || '';
                 } else if (type === 'hotel') {
                     document.getElementById('hotel_nom').value = details.nom_hotel || '';
                     document.getElementById('hotel_type').value = details.type_hotel || '';
                     document.getElementById('hotel_etoiles').value = details.classement_etoiles || '';
                     document.getElementById('hotel_adresse').value = details.adresse || '';
                     document.getElementById('hotel_prix_apd').value = details.prix_nuit_apd || '';
                     document.getElementById('hotel_services').value = details.services_cles || '';
                     document.getElementById('hotel_description').value = details.description || '';
                 }
                 if(photoPreview) { photoPreview.style.display = 'none'; photoPreview.src = '#'; }
                 if(fileInputText) fileInputText.textContent = 'Choisir une nouvelle image (optionnel)...';
             };
            window.previewPhoto = function(event, previewId, textId) {
                 const preview = document.getElementById(previewId);
                 const fileInputTextElement = document.getElementById(textId);
                 const file = event.target.files[0];
                 const reader = new FileReader();
                 reader.onload = function(e) {
                     if(preview) { preview.src = e.target.result; preview.style.display = 'block'; }
                     if(fileInputTextElement && file) { fileInputTextElement.textContent = file.name; }
                 }
                 if (file) { reader.readAsDataURL(file); }
                 else {
                     if(preview) { preview.src = '#'; preview.style.display = 'none'; }
                      if(fileInputTextElement) fileInputTextElement.textContent = 'Choisir une image...';
                 }
             };
            window.showPopupMessage = function(messageHtml) {
                 if (messagePopupContent && messagePopupModal) {
                     const txt = document.createElement("textarea");
                     txt.innerHTML = messageHtml;
                     messagePopupContent.innerHTML = txt.value;
                     messagePopupModal.style.display = 'block';
                     messagePopupModal.classList.remove('fade-out');
                 }
            };
            window.closePopupMessage = function() { closeModal(messagePopupModal); };

            // --- Fonctionnalité de Recherche ---
            if (searchInput) {
                 searchInput.addEventListener('keyup', function() {
                    const filter = searchInput.value.toLowerCase().trim();
                    const tables = document.querySelectorAll('.data-table');
                    tables.forEach(table => {
                        const rows = table.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            if (row.querySelector('td[colspan]')) return;
                            const cells = row.querySelectorAll('td');
                            let match = false;
                            cells.forEach(cell => {
                                if (!cell.querySelector('.action-buttons') && cell.textContent?.toLowerCase().includes(filter)) {
                                    match = true;
                                }
                            });
                            row.style.display = match ? '' : 'none';
                        });
                    });
                });
            }

            // --- Sidebar Toggle ---
            if (sidebarToggle && sidebar && mainContent) {
                 sidebarToggle.addEventListener('click', function() {
                     sidebar.classList.toggle('collapsed');
                     mainContent.classList.toggle('expanded');
                 });
                 if (window.innerWidth < 992 && !sidebar.classList.contains('collapsed')) {
                      sidebar.classList.add('collapsed');
                      mainContent.classList.add('expanded');
                 }
            }

            // --- Fermer les Modals en cliquant à l'extérieur ---
            window.addEventListener('click', function(event) {
                if (event.target === editVisiteModal) closeEditVisiteModal();
                if (event.target === editSejourModal) closeEditSejourModal();
                if (event.target === deleteModal) closeDeleteModal();
                if (event.target === proprieteModal) closeProprieteModal();
                if (event.target === messagePopupModal) closePopupMessage();
            });

            // Initialiser l'affichage des champs de propriété au chargement
            showProprieteFields();

        }); // Fin DOMContentLoaded

// --- *** FONCTION POUR EXPORT PDF (CORRIGÉE) *** ---
function exportAllTablesToPDF() {
            // Vérifier si les librairies sont chargées
            if (typeof jspdf === 'undefined' || typeof jspdf.jsPDF === 'undefined') {
                alert("Erreur: La librairie jsPDF n'est pas chargée.");
                return;
            }

            const { jsPDF } = jspdf;
            const doc = new jsPDF('p', 'pt', 'a4'); // Créer l'instance jsPDF

            // *** CORRECTION ICI: Vérifier si la fonction autoTable existe sur l'instance jsPDF ***
            if (typeof doc.autoTable !== 'function') {
                 alert("Erreur: La librairie jsPDF-AutoTable n'est pas chargée correctement ou la fonction autoTable n'est pas disponible.");
                 console.error("jsPDF instance:", doc); // Log pour débogage
                 console.error("jsPDF prototype:", jsPDF.API); // Log pour débogage
                 return;
            }
            // *** FIN CORRECTION ***


            let finalY = 40; // Position verticale initiale en points

            // Fonction pour ajouter un titre avant chaque tableau
            const addTitle = (title, yPos) => {
                doc.setFontSize(14); // Taille de police légèrement plus grande pour les titres
                doc.setTextColor(40);
                doc.text(title, 40, yPos); // Position X à 40pt
                return yPos + 20; // Espace après le titre
            };

            // Fonction pour ajouter une table au PDF
            const addTableToPdf = (tableId, title, startY, columnsToExclude = []) => {
                const tableElement = document.getElementById(tableId);
                if (!tableElement) {
                    console.warn(`Table avec ID '${tableId}' non trouvée.`);
                    return startY;
                }

                finalY = addTitle(title, startY);

                // Extraire les données visibles
                const head = [];
                const body = [];
                const tableHead = tableElement.querySelector('thead tr');
                const tableBodyRows = tableElement.querySelectorAll('tbody tr');

                // Extraire les en-têtes (visibles)
                 if (tableHead) {
                    const headerCells = tableHead.querySelectorAll('th');
                    const currentHead = [];
                    headerCells.forEach((th, index) => {
                        if (!columnsToExclude.includes(index)) {
                            currentHead.push(th.textContent.trim());
                        }
                    });
                    head.push(currentHead);
                }

                // Extraire les lignes du corps (visibles)
                tableBodyRows.forEach(row => {
                    // Ignorer les lignes cachées par le filtre
                    if (row.style.display === 'none') return;
                    // Ignorer les lignes "aucun résultat"
                    if (row.querySelector('td[colspan]')) return;

                    const cells = row.querySelectorAll('td');
                    const currentBodyRow = [];
                    cells.forEach((cell, index) => {
                        if (!columnsToExclude.includes(index)) {
                            let cellText = cell.textContent.trim();
                            // Nettoyage spécifique (similaire à didParseCell)
                            const selectElement = cell.querySelector('select.statut-select');
                            if (selectElement) cellText = selectElement.options[selectElement.selectedIndex].text;
                            const badgeElement = cell.querySelector('span.status-badge');
                            if (badgeElement) cellText = badgeElement.textContent.trim();
                            const messageButton = cell.querySelector('button.btn-view-message');
                            if (messageButton) cellText = 'Oui';
                            else if (cellText === '-') cellText = '-'; // Conserver le tiret
                            const photoIcon = cell.querySelector('i.fa-image');
                            if (photoIcon) cellText = 'Oui';
                            // Nettoyer les sauts de ligne multiples et espaces excessifs
                            cellText = cellText.replace(/\s+/g, ' ').trim();
                            currentBodyRow.push(cellText);
                        }
                    });
                    if (currentBodyRow.length > 0) {
                        body.push(currentBodyRow);
                    }
                });


                // Options pour autoTable
                let options = {
                    head: head,
                    body: body,
                    startY: finalY,
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak' }, // linebreak pour retour à la ligne auto
                    headStyles: { fillColor: [201, 168, 108], textColor: 20, fontStyle: 'bold' },
                    columnStyles: { // Ajuster largeur si nécessaire (exemple)
                       // 0: { cellWidth: 30 }, // ID
                       // 1: { cellWidth: 'auto' }, // Nom/Villa
                       // etc.
                    },
                    margin: { top: 30, left: 40, right: 40 } // Marges en points
                };

                // Vérifier si la table a des données avant de l'ajouter
                if (body.length > 0) {
                    doc.autoTable(options); // Utilisation de la fonction vérifiée
                    finalY = doc.lastAutoTable.finalY + 25; // Espace après le tableau
                } else {
                    // Optionnel : ajouter un message si la table est vide
                    doc.setFontSize(10);
                    doc.setTextColor(150);
                    doc.text("Aucune donnée à afficher pour cette section.", 40, finalY);
                    finalY += 15;
                }

                // Ajouter une nouvelle page si nécessaire
                if (finalY > doc.internal.pageSize.height - 60) { // Marge basse
                    doc.addPage();
                    finalY = 40; // Réinitialiser Y pour la nouvelle page
                }

                return finalY;
            };

            // --- Exporter chaque table ---
            const actionsColIndexVisites = 7;
            const actionsColIndexSejours = 9;
            const actionsColIndexDemandes = 7;
            const actionsColIndexVillas = 8;
            const actionsColIndexMaisons = 7;
            const actionsColIndexHotels = 6;

            finalY = addTableToPdf('visitesTable', 'Reservations de Visite', finalY, [actionsColIndexVisites]);
            finalY = addTableToPdf('sejoursTable', 'Reservations de Sejour', finalY, [actionsColIndexSejours]);
            finalY = addTableToPdf('demandesAchatTable', 'Demandes d\'Achat Villa', finalY, [actionsColIndexDemandes]);
            finalY = addTableToPdf('villasTable', 'Proprietes - Villas', finalY, [actionsColIndexVillas]);
            finalY = addTableToPdf('maisonsHotesTable', 'Proprietes - Maisons d\'Hotes', finalY, [actionsColIndexMaisons]);
            finalY = addTableToPdf('hotelsTable', 'Proprietes - Hotels', finalY, [actionsColIndexHotels]);

            // --- Sauvegarder le PDF ---
            doc.save('tunify_reservations_export.pdf');
        }
        // --- *** FIN FONCTION PDF (CORRIGÉE) *** ---
     

    </script>

</body>
</html>

