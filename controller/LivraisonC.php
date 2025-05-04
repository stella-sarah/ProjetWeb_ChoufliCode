<?php
require_once __DIR__ . '/../model/Livraison.php';
require_once __DIR__ . '/../config';

class LivraisonC {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function ajouterLivraison($customer_name, $email, $city, $address, $plats, $delivery_time) {
        try {
            $sql = "INSERT INTO livraisons (customer_name, email, city, address, plats, delivery_time) 
                    VALUES (:customer_name, :email, :city, :address, :plats, :delivery_time)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':customer_name', $customer_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':plats', $plats);
            $stmt->bindParam(':delivery_time', $delivery_time);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in ajouterLivraison: " . $e->getMessage());
            return false;
        }
    }

    public function afficherLivraisons() {
        try {
            $sql = "SELECT * FROM livraisons ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherLivraisons: " . $e->getMessage());
            return [];
        }
    }

    public function afficherLivraison($id) {
        try {
            $sql = "SELECT * FROM livraisons WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherLivraison: " . $e->getMessage());
            return null;
        }
    }

    public function modifierStatutLivraison($id, $status) {
        try {
            $sql = "UPDATE livraisons SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in modifierStatutLivraison: " . $e->getMessage());
            return false;
        }
    }

    public function supprimerLivraison($id) {
        try {
            $sql = "DELETE FROM livraisons WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in supprimerLivraison: " . $e->getMessage());
            return false;
        }
    }
} 