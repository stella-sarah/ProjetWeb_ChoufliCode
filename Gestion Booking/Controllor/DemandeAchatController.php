<?php
// Gestion Booking/Controllor/DemandeAchatController.php (Modifié)

require_once __DIR__ . '/../Model/DemandeAchatVilla.php';
// require_once __DIR__ . '/../Services/MailerService.php'; // <<< SUPPRIMÉ OU COMMENTÉ

class DemandeAchatController {
    private $conn; // Objet PDO pour la connexion BD

    public function __construct($db) {
        if (!$db instanceof PDO) {
             error_log("Erreur: Connexion BD invalide dans DemandeAchatController.");
             throw new InvalidArgumentException("Invalid database connection provided to DemandeAchatController.");
        }
        $this->conn = $db;
    }

    /**
     * Ajoute une nouvelle demande d'achat de villa DANS LA BASE DE DONNÉES UNIQUEMENT.
     * @param DemandeAchatVilla $demande L'objet DemandeAchatVilla.
     * @return bool True si l'ajout en BDD réussit, False sinon.
     */
    public function ajouterDemandeAchat(DemandeAchatVilla $demande): bool
    {
        $query = "INSERT INTO demandes_achat_villa (
                      id_villa, nom_villa, type_villa, id_utilisateur, nom_demandeur,
                      email_demandeur, telephone_demandeur, message, statut_demande
                  ) VALUES (
                      :id_villa, :nom_villa, :type_villa, :id_utilisateur, :nom_demandeur,
                      :email_demandeur, :telephone_demandeur, :message, :statut_demande
                  )";

        try {
            // Récupérer les valeurs de l'objet
            $idVilla = $demande->getIdVilla();
            $nomVilla = $demande->getNomVilla();
            $typeVilla = $demande->getTypeVilla();
            $idUtilisateur = $demande->getIdUtilisateur();
            $nomDemandeur = $demande->getNomDemandeur();
            $emailDemandeur = $demande->getEmailDemandeur();
            $telDemandeur = $demande->getTelephoneDemandeur();
            $message = $demande->getMessage();
            $statut = $demande->getStatutDemande() ?: 'Nouvelle';

            // Validation minimale
            if (empty($nomVilla) || empty($nomDemandeur) || empty($emailDemandeur)) {
                error_log("DemandeAchatController: Champs obligatoires manquants (Nom Villa, Nom Demandeur, Email).");
                return false;
            }
            if (!filter_var($emailDemandeur, FILTER_VALIDATE_EMAIL)) {
                 error_log("DemandeAchatController: Email demandeur invalide.");
                 return false;
            }

            $stmt = $this->conn->prepare($query);

            // Lier les paramètres
            $stmt->bindParam(':id_villa', $idVilla, $idVilla === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':nom_villa', $nomVilla, PDO::PARAM_STR);
            $stmt->bindParam(':type_villa', $typeVilla, $typeVilla === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $idUtilisateur, $idUtilisateur === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':nom_demandeur', $nomDemandeur, PDO::PARAM_STR);
            $stmt->bindParam(':email_demandeur', $emailDemandeur, PDO::PARAM_STR);
            $stmt->bindParam(':telephone_demandeur', $telDemandeur, $telDemandeur === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, $message === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':statut_demande', $statut, PDO::PARAM_STR);

            // Exécuter l'insertion en base de données
            if ($stmt->execute()) {
                // --- SUPPRESSION DE L'ENVOI D'EMAIL ICI ---
                // Le code pour instancier MailerService et appeler sendPurchaseRequestConfirmation a été retiré.
                // --- FIN SUPPRESSION EMAIL ---

                // On retourne true car l'opération BDD a réussi.
                return true;

            } else {
                error_log("PDO Error ajouterDemandeAchat: " . implode(":", $stmt->errorInfo()));
                return false; // L'insertion BDD a échoué
            }

        } catch (PDOException $e) {
            error_log("PDO Exception ajouterDemandeAchat: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
             error_log("Exception ajouterDemandeAchat: " . $e->getMessage());
             return false;
        }
    }

    // ... (Les autres méthodes : afficherToutesDemandesAchat, getDemandeAchatById, modifierStatutDemande, supprimerDemandeAchat restent INCHANGÉES) ...
     /**
     * Récupère toutes les demandes d'achat de villa.
     * @return array Tableau associatif des demandes.
     */
    public function afficherToutesDemandesAchat() {
        $query = "SELECT * FROM demandes_achat_villa ORDER BY date_demande DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PDO Exception afficherToutesDemandesAchat: " . $e->getMessage());
            return [];
        }
    }

     /**
     * Récupère une demande par son ID.
     * @param int $id_demande ID de la demande.
     * @return array|false Tableau associatif de la demande ou false si non trouvée.
     */
    public function getDemandeAchatById($id_demande) {
        $query = "SELECT * FROM demandes_achat_villa WHERE id_demande = :id_demande";
        try {
            $stmt = $this->conn->prepare($query);
            $idDemandeClean = filter_var($id_demande, FILTER_VALIDATE_INT);
            if (empty($idDemandeClean)) return false;
            $stmt->bindParam(':id_demande', $idDemandeClean, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Retourne false si non trouvée
        } catch (PDOException $e) {
            error_log("PDO Exception getDemandeAchatById: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Met à jour le statut d'une demande d'achat.
     * @param int $id_demande ID de la demande à modifier.
     * @param string $nouveau_statut Le nouveau statut.
     * @return bool True si succès, False si échec.
     */
    public function modifierStatutDemande($id_demande, $nouveau_statut) {
         $statutsValides = ['Nouvelle', 'En cours', 'Contacté', 'Archivée', 'Non intéressé'];
         if (!in_array($nouveau_statut, $statutsValides)) {
             error_log("modifierStatutDemande: Statut invalide: " . $nouveau_statut);
             return false;
         }
         $query = "UPDATE demandes_achat_villa SET statut_demande = :statut WHERE id_demande = :id_demande";
         try {
             $stmt = $this->conn->prepare($query);
             $idDemandeClean = filter_var($id_demande, FILTER_VALIDATE_INT);
             $statutClean = $nouveau_statut;
             if (empty($idDemandeClean)) { return false; }
             $stmt->bindParam(':statut', $statutClean, PDO::PARAM_STR);
             $stmt->bindParam(':id_demande', $idDemandeClean, PDO::PARAM_INT);
             if ($stmt->execute()) {
                 return $stmt->rowCount() > 0;
             } else {
                 error_log("PDO Error modifierStatutDemande: " . implode(":", $stmt->errorInfo()));
                 return false;
             }
         } catch (PDOException $e) {
             error_log("PDO Exception modifierStatutDemande: " . $e->getMessage());
             return false;
         }
    }

    /**
     * Supprime une demande d'achat de villa.
     * @param int $id_demande ID de la demande à supprimer.
     * @return bool True si succès, False si échec.
     */
    public function supprimerDemandeAchat($id_demande) {
         $query = "DELETE FROM demandes_achat_villa WHERE id_demande = :id_demande";
         try {
             $stmt = $this->conn->prepare($query);
             $idDemandeClean = filter_var($id_demande, FILTER_VALIDATE_INT);
             if (empty($idDemandeClean) || $idDemandeClean <= 0) {
                 return false;
             }
             $stmt->bindParam(':id_demande', $idDemandeClean, PDO::PARAM_INT);
             if ($stmt->execute()) {
                 return $stmt->rowCount() > 0;
             } else {
                 error_log("PDO Error supprimerDemandeAchat: " . implode(":", $stmt->errorInfo()));
                 return false;
             }
         } catch (PDOException $e) {
             error_log("PDO Exception supprimerDemandeAchat: " . $e->getMessage());
             return false;
         }
    }

} // Fin de la classe DemandeAchatController