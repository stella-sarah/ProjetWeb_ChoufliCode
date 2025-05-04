<?php
require_once __DIR__ . '/../model/Reservation.php';
require_once __DIR__ . '/../config';

class ReservationC {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function ajouterReservation($client_name, $client_email, $client_phone, $reservation_date, $reservation_time, $guest_count, $special_requests = null) {
        try {
            $sql = "INSERT INTO reservations (client_name, client_email, client_phone, reservation_date, reservation_time, guest_count, special_requests) 
                    VALUES (:client_name, :client_email, :client_phone, :reservation_date, :reservation_time, :guest_count, :special_requests)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':client_name', $client_name);
            $stmt->bindParam(':client_email', $client_email);
            $stmt->bindParam(':client_phone', $client_phone);
            $stmt->bindParam(':reservation_date', $reservation_date);
            $stmt->bindParam(':reservation_time', $reservation_time);
            $stmt->bindParam(':guest_count', $guest_count);
            $stmt->bindParam(':special_requests', $special_requests);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in ajouterReservation: " . $e->getMessage());
            return false;
        }
    }

    public function afficherReservations() {
        try {
            $sql = "SELECT * FROM reservations ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherReservations: " . $e->getMessage());
            return [];
        }
    }

    public function afficherReservation($id) {
        try {
            $sql = "SELECT * FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherReservation: " . $e->getMessage());
            return null;
        }
    }

    public function modifierStatutReservation($id, $status) {
        try {
            $sql = "UPDATE reservations SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in modifierStatutReservation: " . $e->getMessage());
            return false;
        }
    }

    public function supprimerReservation($id) {
        try {
            $sql = "DELETE FROM reservations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in supprimerReservation: " . $e->getMessage());
            return false;
        }
    }

    // Vérifier la disponibilité
    public function verifierDisponibilite($date, $heure, $nombre_personnes) {
        $sql = "SELECT SUM(guest_count) as total_personnes 
                FROM reservations 
                WHERE reservation_date = :date 
                AND reservation_time = :heure 
                AND status != 'annulée'";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute([
                'date' => $date,
                'heure' => $heure
            ]);
            $result = $query->fetch();
            
            // Supposons que la capacité maximale est de 50 personnes
            $capacite_max = 50;
            return ($result['total_personnes'] + $nombre_personnes) <= $capacite_max;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
} 