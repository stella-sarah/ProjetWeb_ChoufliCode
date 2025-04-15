<?php
class Visite {
    // Propriétés de la classe
    private $conn;
    private $id_visite;
    private $id_cin;
    private $nom_complet;
    private $date_visite;
    private $heure_visite;
    private $type_villa;
    private $nom_villa;

    // Constructeur
    public function __construct($db) {
        $this->conn = $db;
    }

    // Getters et Setters
    public function getIdVisite() {
        return $this->id_visite;
    }

    public function setIdVisite($id_visite) {
        $this->id_visite = $id_visite;
    }

    public function getCin() {
        return $this->id_cin;
    }

    public function setCin($id_cin) {
        $this->id_cin = $id_cin;
    }

    public function getNomComplet() {
        return $this->nom_complet;
    }

    public function setNomComplet($nom_complet) {
        $this->nom_complet = $nom_complet;
    }

    public function getDateVisite() {
        return $this->date_visite;
    }

    public function setDateVisite($date_visite) {
        $this->date_visite = $date_visite;
    }

    public function getHeureVisite() {
        return $this->heure_visite;
    }

    public function setHeureVisite($heure_visite) {
        $this->heure_visite = $heure_visite;
    }

    public function getTypeVilla() {
        return $this->type_villa;
    }

    public function setTypeVilla($type_villa) {
        $this->type_villa = $type_villa;
    }

    public function getNomVilla() {
        return $this->nom_villa;
    }

    public function setNomVilla($nom_villa) {
        $this->nom_villa = $nom_villa;
    }

    /**
     * Ajoute une nouvelle visite dans la base de données
     * @return bool True si l'ajout a réussi, False sinon
     */
    public function ajouterVisite() {
        try {
            // Créer la requête d'insertion
            $query = "INSERT INTO visites (id_cin, nom_complet, date_visite, heure_visite, type_villa, nom_villa) 
                      VALUES (:id_cin, :nom_complet, :date_visite, :heure_visite, :type_villa, :nom_villa)";
            
            // Préparer la requête
            $stmt = $this->conn->prepare($query);
            
            // Nettoyer les données
            $this->id_cin = htmlspecialchars(strip_tags($this->id_cin));
            $this->nom_complet = htmlspecialchars(strip_tags($this->nom_complet));
            $this->date_visite = htmlspecialchars(strip_tags($this->date_visite));
            $this->heure_visite = htmlspecialchars(strip_tags($this->heure_visite));
            $this->type_villa = htmlspecialchars(strip_tags($this->type_villa ?? ''));
            $this->nom_villa = htmlspecialchars(strip_tags($this->nom_villa ?? ''));
            
            // Lier les paramètres
            $stmt->bindParam(':id_cin', $this->id_cin);
            $stmt->bindParam(':nom_complet', $this->nom_complet);
            $stmt->bindParam(':date_visite', $this->date_visite);
            $stmt->bindParam(':heure_visite', $this->heure_visite);
            $stmt->bindParam(':type_villa', $this->type_villa);
            $stmt->bindParam(':nom_villa', $this->nom_villa);
            
            // Exécuter la requête
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Erreur lors de l'ajout de la visite: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les visites de la base de données
     * @return array Tableau contenant toutes les visites
     */
    public function afficherVisites() {
        try {
            $query = "SELECT * FROM visites ORDER BY date_visite DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            echo "Erreur lors de la récupération des visites: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère une visite spécifique par son ID
     * @param int $id_visite ID de la visite à récupérer
     * @return array Les détails de la visite ou null si non trouvée
     */
    public function getVisiteById($id_visite) {
        try {
            $query = "SELECT * FROM visites WHERE id_visite = :id_visite";
            $stmt = $this->conn->prepare($query);
            
            // Nettoyer et lier l'ID
            $id_visite = htmlspecialchars(strip_tags($id_visite));
            $stmt->bindParam(':id_visite', $id_visite);
            
            // Exécuter la requête
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            echo "Erreur lors de la récupération de la visite: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Modifie une visite existante dans la base de données
     * @return bool True si la modification a réussi, False sinon
     */
    public function modifierVisite() {
        try {
            $query = "UPDATE visites 
                     SET id_cin = :id_cin, 
                         nom_complet = :nom_complet, 
                         date_visite = :date_visite, 
                         heure_visite = :heure_visite 
                     WHERE id_visite = :id_visite";
            
            $stmt = $this->conn->prepare($query);
            
            // Nettoyer les données
            $this->id_visite = htmlspecialchars(strip_tags($this->id_visite));
            $this->id_cin = htmlspecialchars(strip_tags($this->id_cin));
            $this->nom_complet = htmlspecialchars(strip_tags($this->nom_complet));
            $this->date_visite = htmlspecialchars(strip_tags($this->date_visite));
            $this->heure_visite = htmlspecialchars(strip_tags($this->heure_visite));
            
            // Lier les paramètres
            $stmt->bindParam(':id_visite', $this->id_visite);
            $stmt->bindParam(':id_cin', $this->id_cin);
            $stmt->bindParam(':nom_complet', $this->nom_complet);
            $stmt->bindParam(':date_visite', $this->date_visite);
            $stmt->bindParam(':heure_visite', $this->heure_visite);
            
            // Exécuter la requête
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Erreur lors de la modification de la visite: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime une visite de la base de données
     * @param int $id_visite L'ID de la visite à supprimer
     * @return bool True si la suppression a réussi, False sinon
     */
    public function supprimerVisite($id_visite) {
        try {
            $query = "DELETE FROM visites WHERE id_visite = :id_visite";
            $stmt = $this->conn->prepare($query);
            
            // Nettoyer les données
            $id_visite = htmlspecialchars(strip_tags($id_visite));
            
            // Lier les paramètres
            $stmt->bindParam(':id_visite', $id_visite);
            
            // Exécuter la requête
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            echo "Erreur lors de la suppression de la visite: " . $e->getMessage();
            return false;
        }
    }
}
?>