<?php
require_once '../../config.php';
require_once '../../model/reservation_transport.php';

class ReservationTransportController 
{
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Vérifier si le stock est suffisant pour le transport donné
    public function checkStock($id_moyen) {
        try {
            $query = $this->pdo->prepare('SELECT stock FROM transport WHERE id = :id');
            $query->execute(['id' => $id_moyen]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result && $result['stock'] > 0; // Retourne true si stock > 0
        } catch (PDOException $e) {
            die('Erreur lors de la vérification du stock: ' . $e->getMessage());
        }
    }

    // Ajouter une réservation
    public function ajouterReservation($id_moyen, $nom, $prenom, $depart, $destination, $paiement, $cin, $email, $datedebut, $datefin) 
    {
        try {
            $query = $this->pdo->prepare('
                INSERT INTO reservation_transport (id_moyen, nom, prenom, depart, destination, paiement, cin, email, datedebut, datefin) 
                VALUES (:id_moyen, :nom, :prenom, :depart, :destination, :paiement, :cin, :email, :datedebut, :datefin)
            ');
            
            return $query->execute([
                'id_moyen' => $id_moyen,
                'nom' => $nom,
                'prenom' => $prenom,
                'depart' => $depart,
                'destination' => $destination,
                'paiement' => $paiement,
                'cin' => $cin,
                'email' => $email,
                'datedebut' => $datedebut,
                'datefin' => $datefin
            ]);
            
        } catch (PDOException $e) {
            die('Erreur lors de l\'ajout de la réservation: ' . $e->getMessage());
        }
    }
    
    // Récupérer toutes les réservations
    public function getAllReservations() {
        try {
            $query = $this->pdo->prepare('SELECT * FROM reservation_transport');
            $query->execute();
            $reservations = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $result = [];
            foreach ($reservations as $reservation) {
                $result[] = new ReservationTransport(
                    $reservation['id'],
                    $reservation['id_moyen'],
                    $reservation['nom'],
                    $reservation['prenom'],
                    $reservation['depart'],
                    $reservation['destination'],
                    $reservation['paiement'],
                    $reservation['cin'],
                    $reservation['email'],
                    $reservation['datedebut'],
                    $reservation['datefin']
                );
            }
            return $result;
        } catch (PDOException $e) {
            die('Erreur lors de la récupération des réservations: ' . $e->getMessage());
        }
    }
    
    // Récupérer une réservation par son ID
    public function getReservationById($id) {
        try {
            $query = $this->pdo->prepare('SELECT * FROM reservation_transport WHERE id = :id');
            $query->execute(['id' => $id]);
            $reservation = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($reservation) {
                return new ReservationTransport(
                    $reservation['id'],
                    $reservation['id_moyen'],
                    $reservation['nom'],
                    $reservation['prenom'],
                    $reservation['depart'],
                    $reservation['destination'],
                    $reservation['paiement'],
                    $reservation['cin'],
                    $reservation['email'],
                    $reservation['datedebut'],
                    $reservation['datefin']
                );
            }
            return null;
        } catch (PDOException $e) {
            die('Erreur lors de la récupération de la réservation: ' . $e->getMessage());
        }
    }
    
    // Modifier une réservation
    public function modifierReservation($id, $id_moyen, $nom, $prenom, $depart, $destination, $paiement, $cin, $email, $datedebut, $datefin) {
        try {
            $query = $this->pdo->prepare('
                UPDATE reservation_transport SET 
                id_moyen = :id_moyen,
                nom = :nom,
                prenom = :prenom,
                depart = :depart,
                destination = :destination,
                paiement = :paiement,
                cin = :cin,
                email = :email,
                datedebut = :datedebut,
                datefin = :datefin
                WHERE id = :id
            ');
            
            return $query->execute([
                'id' => $id,
                'id_moyen' => $id_moyen,
                'nom' => $nom,
                'prenom' => $prenom,
                'depart' => $depart,
                'destination' => $destination,
                'paiement' => $paiement,
                'cin' => $cin,
                'email' => $email,
                'datedebut' => $datedebut,
                'datefin' => $datefin
            ]);
        } catch (PDOException $e) {
            die('Erreur lors de la modification de la réservation: ' . $e->getMessage());
        }
    }
    
    // Supprimer une réservation
    public function supprimerReservation($id) {
        try {
            $query = $this->pdo->prepare('DELETE FROM reservation_transport WHERE id = :id');
            return $query->execute(['id' => $id]);
        } catch (PDOException $e) {
            die('Erreur lors de la suppression de la réservation: ' . $e->getMessage());
        }
    }
}
?>