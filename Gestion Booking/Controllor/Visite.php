<?php
// Visite.php - Contrôleur pour les visites

// Utiliser require_once pour inclure le modèle Visiteclass
// Visiteclass contient maintenant les propriétés protected et les getters/setters
require_once __DIR__ . '/../Model/Visiteclass.php';

// Assurez-vous qu'il n'y a pas d'inclusion répétée d'autres contrôleurs ici
// require_once __DIR__ . '/ReservationSejourController.php'; // Vérifiez/supprimez si inutile

class VisiteFunctions extends Visite { // Hérite de Visite

    // La connexion à la base de données est spécifique à ce contrôleur
    private $conn;

    // PAS DE DÉCLARATION DE PROPRIÉTÉS ICI ($id_visite, $id_cin, etc.)
    // Elles sont héritées de la classe parent 'Visite'

    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException("Invalid DB connection provided to VisiteFunctions");
        }
        $this->conn = $db;
        // Optionnel: Appeler le constructeur parent si celui de Visite fait quelque chose
        // parent::__construct();
    }

    // PAS DE GETTERS/SETTERS DUPLIQUÉS ICI
    // Le contrôleur utilisera ceux hérités de la classe Visite
    // ou accédera aux propriétés protected directement ($this->id_cin)

    /**
     * Ajoute une nouvelle visite dans la base de données.
     * Utilise les propriétés héritées.
     */
    public function ajouterVisite() {
        $query = "INSERT INTO visites (id_cin, nom_complet, date_visite, heure_visite, type_villa, nom_villa)
                  VALUES (:id_cin, :nom_complet, :date_visite, :heure_visite, :type_villa, :nom_villa)";
        try {
            $stmt = $this->conn->prepare($query);

            // Accéder aux propriétés héritées (protected)
            $cin_to_bind = $this->id_cin;
            $nom_to_bind = $this->nom_complet;
            $date_to_bind = $this->date_visite;
            $heure_to_bind = $this->heure_visite;
            $type_villa_to_bind = $this->type_villa ?? '';
            $nom_villa_to_bind = $this->nom_villa ?? '';

            // Validation
            if (empty($cin_to_bind) || empty($nom_to_bind) || empty($date_to_bind) || empty($heure_to_bind)) {
                error_log("Erreur Contrôleur Visite: Données ajout obligatoires manquantes.");
                return false;
            }

            // Lier les paramètres
            $stmt->bindParam(':id_cin', $cin_to_bind);
            $stmt->bindParam(':nom_complet', $nom_to_bind);
            $stmt->bindParam(':date_visite', $date_to_bind);
            $stmt->bindParam(':heure_visite', $heure_to_bind);
            $stmt->bindParam(':type_villa', $type_villa_to_bind);
            $stmt->bindParam(':nom_villa', $nom_villa_to_bind);

            if($stmt->execute()) {
                // $this->id_visite = $this->conn->lastInsertId(); // Mettre à jour l'ID hérité si besoin
                return true;
            } else {
                error_log("PDO Error ajoutVisite: " . implode(":", $stmt->errorInfo()));
                return false;
            }
        } catch(PDOException $e) {
            error_log("PDO Exception ajoutVisite: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère toutes les visites.
     */
    public function afficherVisites() {
        $query = "SELECT * FROM visites ORDER BY date_visite DESC, heure_visite DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("PDO Exception afficherVisites: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère une visite par ID.
     */
     public function getVisiteById($id_visite) {
        $query = "SELECT * FROM visites WHERE id_visite = :id_visite";
        try {
            $stmt = $this->conn->prepare($query);
            $id_visite_clean = filter_var($id_visite, FILTER_SANITIZE_NUMBER_INT);
            if ($id_visite_clean === false || $id_visite_clean <= 0) { return false; }
            $stmt->bindParam(':id_visite', $id_visite_clean, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch(PDOException $e) {
            error_log("PDO Exception getVisiteById: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Modifie une visite existante.
     * Utilise les propriétés héritées. L'ID doit être défini sur l'objet avant d'appeler.
     */
    public function modifierVisite() {
         // L'ID de la visite à modifier doit être dans $this->id_visite (hérité)
         if (empty($this->id_visite)) {
             error_log("Erreur modifierVisite: ID de visite non défini dans l'objet VisiteFunctions.");
             return false;
         }

        $query = "UPDATE visites SET id_cin = :id_cin, nom_complet = :nom_complet, date_visite = :date_visite, heure_visite = :heure_visite WHERE id_visite = :id_visite";
        try {
            $stmt = $this->conn->prepare($query);

            // Accéder aux propriétés héritées
            $id_visite_to_bind = $this->id_visite;
            $cin_to_bind = $this->id_cin;
            $nom_to_bind = $this->nom_complet;
            $date_to_bind = $this->date_visite;
            $heure_to_bind = $this->heure_visite;

            if (empty($cin_to_bind) || empty($nom_to_bind) || empty($date_to_bind) || empty($heure_to_bind)) {
                error_log("Erreur Contrôleur Visite: Données modification manquantes.");
                return false;
            }

            $stmt->bindParam(':id_visite', $id_visite_to_bind, PDO::PARAM_INT);
            $stmt->bindParam(':id_cin', $cin_to_bind);
            $stmt->bindParam(':nom_complet', $nom_to_bind);
            $stmt->bindParam(':date_visite', $date_to_bind);
            $stmt->bindParam(':heure_visite', $heure_to_bind);

            if($stmt->execute()) {
                return true;
            } else {
                error_log("PDO Error modifierVisite: " . implode(":", $stmt->errorInfo()));
                return false;
            }
        } catch(PDOException $e) {
            error_log("PDO Exception modifierVisite: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une visite.
     */
    public function supprimerVisite($id_visite) {
        $query = "DELETE FROM visites WHERE id_visite = :id_visite";
        try {
            $stmt = $this->conn->prepare($query);
            $id_visite_clean = filter_var($id_visite, FILTER_VALIDATE_INT);
            if (empty($id_visite_clean) || $id_visite_clean <= 0) { return false; }
            $stmt->bindParam(':id_visite', $id_visite_clean, PDO::PARAM_INT);
            if($stmt->execute()) {
                return $stmt->rowCount() > 0; // Vrai si une ligne a été supprimée
            } else {
                error_log("PDO Error supprimerVisite: " . implode(":", $stmt->errorInfo()));
                return false;
            }
        } catch(PDOException $e) {
            error_log("PDO Exception supprimerVisite: " . $e->getMessage());
            return false;
        }
    }
    public function getVisitesByCin($cin) {
        // Valider le CIN (doit être 8 chiffres)
        if (!preg_match('/^\d{8}$/', $cin)) {
            error_log("VisiteFunctions::getVisitesByCin - CIN invalide fourni: " . $cin);
            return []; // Retourne un tableau vide si le CIN n'est pas valide
        }

        $query = "SELECT * FROM visites WHERE id_cin = :cin ORDER BY date_visite DESC, heure_visite DESC";
        try {
            $stmt = $this->conn->prepare($query);
            // Lier le paramètre CIN validé
            $stmt->bindParam(':cin', $cin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("PDO Exception getVisitesByCin: " . $e->getMessage());
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }
} // Fin de la classe VisiteFunctions
?>
