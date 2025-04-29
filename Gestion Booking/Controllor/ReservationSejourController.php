<?php

// Assurez-vous que le chemin vers le modèle est correct
require_once __DIR__ . '/../Model/ReservationSejour.php'; // Utilise require_once ici aussi

// Début de la définition de la classe - Ligne 5 environ
class ReservationSejourController {
    private $conn; // Objet PDO pour la connexion BD

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        if (!$db instanceof PDO) {
             error_log("Erreur: Connexion BD invalide dans ReservationSejourController.");
             throw new InvalidArgumentException("Invalid database connection provided to ReservationSejourController.");
        }
        $this->conn = $db;
    }

    /**
     * Ajoute une nouvelle réservation de séjour, calcule et enregistre le prix total.
     * @param ReservationSejour $reservation L'objet ReservationSejour.
     * @return bool True si succès, False si échec.
     */
    public function ajouterReservation(ReservationSejour $reservation) {
        // Requête incluant la nouvelle colonne prix_total
        $query = "INSERT INTO reservations_sejour (
                      id_utilisateur, nom_utilisateur, email_utilisateur, type_logement, nom_logement,
                      date_debut, date_fin, nb_personnes, demandes_speciales, prix_total, statut
                  ) VALUES (
                      :id_utilisateur, :nom_utilisateur, :email_utilisateur, :type_logement, :nom_logement,
                      :date_debut, :date_fin, :nb_personnes, :demandes_speciales, :prix_total, :statut
                  )";

        try {
            // Récupérer les valeurs de l'objet
            $idUtilisateur = $reservation->getIdUtilisateur(); $nomUtilisateur = $reservation->getNomUtilisateur(); $emailUtilisateur = $reservation->getEmailUtilisateur(); $typeLogement = $reservation->getTypeLogement(); $nomLogement = $reservation->getNomLogement(); $dateDebut = $reservation->getDateDebut(); $dateFin = $reservation->getDateFin(); $nbPersonnes = $reservation->getNbPersonnes(); $demandes = $reservation->getDemandesSpeciales() ?? ''; $statut = $reservation->getStatut() ?: 'En attente';

            // --- Validation et Calcul du Prix Total ---
            $prix_total_calcule = null;
            if ($statut !== 'Demande Achat' && !empty($dateDebut) && !empty($dateFin)) {
                $datetime1 = new DateTime($dateDebut); $datetime2 = new DateTime($dateFin);
                if ($datetime2 <= $datetime1) { error_log("Contrôleur: Date fin <= date début."); return false; }
                $interval = $datetime1->diff($datetime2); $nb_nuits = $interval->days;

                // !! LOGIQUE PRIX/NUIT À AMÉLIORER (utiliser une table logements) !!
                // Cette logique devra être adaptée ou supprimée si les prix sont gérés différemment
                $prix_par_nuit = 0;
                // Exemple simplifié pour obtenir le prix/nuit (à remplacer par une vraie recherche BD)
                if ($typeLogement === 'hotel') {
                    $detailsList = [ 'palace' => ['type' => 'Hôtel Palace 5*', 'prix_nuit' => 800], 'boutique' => ['type' => 'Hôtel Boutique 4*', 'prix_nuit' => 450], 'resort' => ['type' => 'Résort 5*', 'prix_nuit' => 650] ];
                    $key = array_search($nomLogement, array_column($detailsList, 'type')); if ($key !== false) { $prix_par_nuit = $detailsList[$key]['prix_nuit'] ?? 0; }
                } elseif ($typeLogement === 'maison_hote') {
                    $detailsList = [ 'dar' => ['type' => 'Dar Traditionnel', 'prix_nuit' => 350], 'riad' => ['type' => 'Riad Luxueux', 'prix_nuit' => 550], 'villa' => ['type' => 'Villa d\'Hôtes', 'prix_nuit' => 750] ];
                     $key = array_search(str_replace(' ('.($reservation->getTypeVilla() ?? '').')','',$nomLogement), array_column($detailsList, 'type')); if ($key !== false) { $prix_par_nuit = $detailsList[$key]['prix_nuit'] ?? 0; }
                } elseif ($typeLogement === 'villa') {
                     // Pour les villas, le prix est peut-être fixe ou non applicable ici
                     // Si un prix par nuit doit être calculé, il faut une logique pour le récupérer
                     // $prix_par_nuit = getPrixNuitVilla($nomLogement); // Exemple
                     $prix_par_nuit = 1000; // Placeholder
                }
                // Fin logique prix/nuit temporaire

                if ($nb_nuits > 0 && $prix_par_nuit > 0) { $prix_total_calcule = $nb_nuits * $prix_par_nuit; }
                else { error_log("Contrôleur: Impossible de calculer prix total pour ajout. Nuits: $nb_nuits, Prix/Nuit: $prix_par_nuit"); }
            }
            // --- Fin Calcul Prix ---

            // Validation finale
            if (empty($typeLogement) || empty($nomLogement)) { error_log("Contrôleur: Type/Nom logement manquants."); return false; }

            $stmt = $this->conn->prepare($query);

            // Lier les paramètres
            $stmt->bindParam(':id_utilisateur', $idUtilisateur, $idUtilisateur === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':nom_utilisateur', $nomUtilisateur, $nomUtilisateur === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':email_utilisateur', $emailUtilisateur, $emailUtilisateur === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':type_logement', $typeLogement, PDO::PARAM_STR);
            $stmt->bindParam(':nom_logement', $nomLogement, PDO::PARAM_STR);
            $stmt->bindParam(':date_debut', $dateDebut, $dateDebut === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':date_fin', $dateFin, $dateFin === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':nb_personnes', $nbPersonnes, $nbPersonnes === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':demandes_speciales', $demandes, PDO::PARAM_STR);
            $stmt->bindParam(':prix_total', $prix_total_calcule, $prix_total_calcule === null ? PDO::PARAM_NULL : PDO::PARAM_STR); // Lier le prix calculé
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);

            // Exécuter
            if ($stmt->execute()) { return true; }
            else { error_log("PDO Error ajouterReservation: " . implode(":", $stmt->errorInfo())); return false; }

        } catch (Exception $e) { error_log("Exception ajouterReservation: " . $e->getMessage()); return false; }
    }

    /**
     * Récupère toutes les réservations de séjour, y compris le prix total.
     */
    public function afficherToutesReservations() {
        $query = "SELECT * FROM reservations_sejour ORDER BY date_reservation DESC";
        try { $stmt = $this->conn->prepare($query); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { error_log("PDO Exception afficherToutesReservations: " . $e->getMessage()); return []; }
    }

    /**
     * Récupère les réservations pour un utilisateur spécifique, y compris le prix total.
     */
    public function afficherReservationsUtilisateur($id_utilisateur) {
         $query = "SELECT * FROM reservations_sejour WHERE id_utilisateur = :id_utilisateur ORDER BY date_reservation DESC";
         try { $stmt = $this->conn->prepare($query); $idUtilisateurClean = filter_var($id_utilisateur, FILTER_VALIDATE_INT); if ($idUtilisateurClean === false || $idUtilisateurClean <= 0) { return []; } $stmt->bindParam(':id_utilisateur', $idUtilisateurClean, PDO::PARAM_INT); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
         catch (PDOException $e) { error_log("PDO Exception afficherReservationsUtilisateur: " . $e->getMessage()); return []; }
    }

     /**
     * Met à jour le statut d'une réservation.
     */
    public function modifierStatutReservation($id_reservation, $nouveau_statut) {
         // Note: Ne pas inclure 'Demande Achat' ici si ce statut ne doit pas être modifié via ce contrôleur
         $statutsValides = ['En attente', 'Confirmée', 'Annulée', 'Terminée'];
         if (!in_array($nouveau_statut, $statutsValides)) {
             error_log("modifierStatutReservation: Statut invalide: " . $nouveau_statut);
             return false;
         }
         $query = "UPDATE reservations_sejour SET statut = :statut WHERE id_reservation = :id_reservation";
         try {
             $stmt = $this->conn->prepare($query);
             $idReservationClean = filter_var($id_reservation, FILTER_VALIDATE_INT);
             $statutClean = $nouveau_statut;
             if (empty($idReservationClean)) { return false; }
             $stmt->bindParam(':statut', $statutClean, PDO::PARAM_STR);
             $stmt->bindParam(':id_reservation', $idReservationClean, PDO::PARAM_INT);
             if ($stmt->execute()) { return $stmt->rowCount() > 0; }
             else { error_log("PDO Error modifierStatutReservation: " . implode(":", $stmt->errorInfo())); return false; }
         }
         catch (PDOException $e) { error_log("PDO Exception modifierStatutReservation: " . $e->getMessage()); return false; }
    }

    /**
     * Supprime une réservation de séjour.
     */
    public function supprimerReservation($id_reservation) {
         $query = "DELETE FROM reservations_sejour WHERE id_reservation = :id_reservation";
         try {
             $stmt = $this->conn->prepare($query);
             $idReservationClean = filter_var($id_reservation, FILTER_VALIDATE_INT);
             if (empty($idReservationClean) || $idReservationClean <= 0) { return false; }
             $stmt->bindParam(':id_reservation', $idReservationClean, PDO::PARAM_INT);
             if ($stmt->execute()) { return $stmt->rowCount() > 0; }
             else { error_log("PDO Error supprimerReservation: " . implode(":", $stmt->errorInfo())); return false; }
         }
         catch (PDOException $e) { error_log("PDO Exception supprimerReservation: " . $e->getMessage()); return false; }
    }

    /**
     * NOUVELLE METHODE: Met à jour les détails complets d'une réservation de séjour.
     * Recalcule le prix total si les dates changent.
     *
     * @param int $id_reservation ID de la réservation à modifier.
     * @param array $data Tableau associatif contenant les nouvelles données (ex: ['date_debut' => '...', 'nb_personnes' => ...]).
     * @return bool True si succès, False si échec.
     */
    public function modifierReservationComplet($id_reservation, $data) {
        // Valider l'ID
        $idReservationClean = filter_var($id_reservation, FILTER_VALIDATE_INT);
        if (empty($idReservationClean)) {
            error_log("modifierReservationComplet: ID de réservation invalide.");
            return false;
        }

        // Récupérer la réservation existante pour obtenir le type et nom de logement
        // et pour recalculer le prix si nécessaire.
        $query_select = "SELECT type_logement, nom_logement, statut FROM reservations_sejour WHERE id_reservation = :id";
        try {
            $stmt_select = $this->conn->prepare($query_select);
            $stmt_select->bindParam(':id', $idReservationClean, PDO::PARAM_INT);
            $stmt_select->execute();
            $reservation_existante = $stmt_select->fetch(PDO::FETCH_ASSOC);
            if (!$reservation_existante) {
                error_log("modifierReservationComplet: Réservation ID $idReservationClean non trouvée.");
                return false;
            }
            // On ne modifie pas les demandes d'achat via cette fonction
            if ($reservation_existante['statut'] === 'Demande Achat') {
                 error_log("modifierReservationComplet: Tentative de modification d'une Demande Achat via la fonction de séjour.");
                 return false;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception (select) modifierReservationComplet: " . $e->getMessage());
            return false;
        }


        // Construire la requête UPDATE dynamiquement basée sur les données fournies
        $fields_to_update = [];
        $params_to_bind = [':id_reservation' => $idReservationClean]; // Toujours lier l'ID

        // Champs modifiables
        $champs_permis = ['date_debut', 'date_fin', 'nb_personnes', 'demandes_speciales'];

        foreach ($champs_permis as $champ) {
            if (isset($data[$champ])) {
                // Nettoyer la valeur (exemple simple, adapter si nécessaire)
                $valeur_propre = ($champ === 'nb_personnes')
                                 ? filter_var($data[$champ], FILTER_VALIDATE_INT, ["options" => ["min_range"=>1]])
                                 : strip_tags(trim($data[$champ]));

                if ($valeur_propre !== false && $valeur_propre !== null) { // Vérifier si la validation/nettoyage a réussi
                    $fields_to_update[] = "$champ = :$champ";
                    $params_to_bind[":$champ"] = $valeur_propre;
                } else if ($champ === 'nb_personnes' && $data[$champ] !== null) {
                     error_log("modifierReservationComplet: Nombre de personnes invalide fourni: " . $data[$champ]);
                     // Optionnel: retourner false ou ignorer ce champ
                }
                 // Pour les autres champs, une chaîne vide après trim est acceptable
                 else if ($champ !== 'nb_personnes') {
                     $fields_to_update[] = "$champ = :$champ";
                     $params_to_bind[":$champ"] = $valeur_propre; // Peut être une chaîne vide
                 }
            }
        }

        // Recalculer le prix total si les dates sont modifiées
        $dateDebut = $data['date_debut'] ?? null;
        $dateFin = $data['date_fin'] ?? null;
        $prix_total_calcule = null; // Initialiser

        if (!empty($dateDebut) && !empty($dateFin)) {
             try {
                $datetime1 = new DateTime($dateDebut);
                $datetime2 = new DateTime($dateFin);
                if ($datetime2 <= $datetime1) {
                    error_log("Contrôleur Modif: Date fin <= date début.");
                    // Ne pas bloquer toute la modif, juste ne pas calculer le prix
                } else {
                    $interval = $datetime1->diff($datetime2);
                    $nb_nuits = $interval->days;

                    // !! Réutiliser la logique de calcul de prix (AMÉLIORER CETTE PARTIE) !!
                    $prix_par_nuit = 0;
                    $typeLogement = $reservation_existante['type_logement'];
                    $nomLogement = $reservation_existante['nom_logement'];

                    if ($typeLogement === 'hotel') {
                        $detailsList = [ 'palace' => ['type' => 'Hôtel Palace 5*', 'prix_nuit' => 800], 'boutique' => ['type' => 'Hôtel Boutique 4*', 'prix_nuit' => 450], 'resort' => ['type' => 'Résort 5*', 'prix_nuit' => 650] ];
                        $key = array_search($nomLogement, array_column($detailsList, 'type')); if ($key !== false) { $prix_par_nuit = $detailsList[$key]['prix_nuit'] ?? 0; }
                    } elseif ($typeLogement === 'maison_hote') {
                        $detailsList = [ 'dar' => ['type' => 'Dar Traditionnel', 'prix_nuit' => 350], 'riad' => ['type' => 'Riad Luxueux', 'prix_nuit' => 550], 'villa' => ['type' => 'Villa d\'Hôtes', 'prix_nuit' => 750] ];
                        $key = array_search($nomLogement, array_column($detailsList, 'type')); if ($key !== false) { $prix_par_nuit = $detailsList[$key]['prix_nuit'] ?? 0; }
                    } elseif ($typeLogement === 'villa') {
                         $prix_par_nuit = 1000; // Placeholder
                    }

                    if ($nb_nuits > 0 && $prix_par_nuit > 0) {
                        $prix_total_calcule = $nb_nuits * $prix_par_nuit;
                    } else {
                         error_log("Contrôleur Modif: Impossible de calculer prix total. Nuits: $nb_nuits, Prix/Nuit: $prix_par_nuit");
                    }
                }
             } catch (Exception $e) {
                  error_log("Exception calcul dates/prix modif: " . $e->getMessage());
             }
             // Ajouter le prix total aux champs à mettre à jour
             $fields_to_update[] = "prix_total = :prix_total";
             $params_to_bind[":prix_total"] = $prix_total_calcule; // Peut être null
        }


        // S'il n'y a rien à mettre à jour (à part potentiellement le prix si les dates n'ont pas changé)
        if (empty($fields_to_update)) {
            error_log("modifierReservationComplet: Aucune donnée valide fournie pour la mise à jour.");
            return false; // Ou true si on considère que ne rien faire est un succès
        }

        // Construire la requête finale
        $query_update = "UPDATE reservations_sejour SET " . implode(', ', $fields_to_update) . " WHERE id_reservation = :id_reservation";

        // Exécuter la mise à jour
        try {
            $stmt_update = $this->conn->prepare($query_update);

            // Lier tous les paramètres collectés
            foreach ($params_to_bind as $param => $value) {
                $type = PDO::PARAM_STR; // Défaut
                if (is_int($value)) $type = PDO::PARAM_INT;
                if (is_bool($value)) $type = PDO::PARAM_BOOL;
                if (is_null($value)) $type = PDO::PARAM_NULL;
                // Spécifier le type pour l'ID
                if ($param === ':id_reservation') $type = PDO::PARAM_INT;
                if ($param === ':nb_personnes') $type = PDO::PARAM_INT;

                $stmt_update->bindValue($param, $value, $type);
            }

            if ($stmt_update->execute()) {
                return true; // La mise à jour a réussi
            } else {
                error_log("PDO Error modifierReservationComplet: " . implode(":", $stmt_update->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception modifierReservationComplet: " . $e->getMessage());
            return false;
        }
    }


} // Fin de la classe ReservationSejourController
?>
