<?php
// VisiteController.php - Contrôleur pour gérer les visites de villas dans l'admin
require_once '../../config.php'; // Include database configuration

class VisiteController extends Visite {
    // Constructeur
    public function __construct() {
        $db = config::getConnexion();
        parent::__construct($db);
    }
    
    // Mettre à jour le statut d'une visite
    public function updateStatus($id, $new_status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET statut = :statut 
                      WHERE id_visite = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $new_status = htmlspecialchars(strip_tags($new_status));
            $id = htmlspecialchars(strip_tags($id));
            
            $stmt->bindParam(":statut", $new_status);
            $stmt->bindParam(":id", $id);
            
            if ($stmt->execute()) {
                return true;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer les visites avec filtres avancés pour l'admin
    public function getFilteredVisites($filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table_name;
            $conditions = [];
            $params = [];
            
            // Filtrer par statut
            if (!empty($filters['statut'])) {
                $conditions[] = "statut = :statut";
                $params[':statut'] = $filters['statut'];
            }
            
            // Filtrer par date de début
            if (!empty($filters['date_from'])) {
                $conditions[] = "date_visite >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            // Filtrer par date de fin
            if (!empty($filters['date_to'])) {
                $conditions[] = "date_visite <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            // Filtrer par type de villa
            if (!empty($filters['type_villa'])) {
                $conditions[] = "type_villa = :type_villa";
                $params[':type_villa'] = $filters['type_villa'];
            }
            
            // Ajouter les conditions à la requête
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY date_visite DESC, heure_visite DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération des visites: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupérer les types de villas uniques
    public function getVillaTypes() {
        try {
            $query = "SELECT DISTINCT type_villa FROM " . $this->table_name . " WHERE type_villa IS NOT NULL ORDER BY type_villa";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            error_log("Erreur lors de la récupération des types de villas: " . $e->getMessage());
            return [];
        }
    }
    
    // Approuver une visite (changer statut à 'approuvee')
    public function approveVisite($id) {
        return $this->updateStatus($id, 'approuvee');
    }
    
    // Annuler une visite (changer statut à 'annulee')
    public function cancelVisite($id) {
        return $this->updateStatus($id, 'annulee');
    }
}