<?php

require_once '../../config.php';
require_once '../../model/transport.php';

class TransportController 
{
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Vérifier si un transport existe déjà par type et nom
    public function transportExists($nom, $type) {
        try {
            $query = $this->pdo->prepare('SELECT * FROM transport WHERE nom = :nom AND type = :type');
            $query->execute([
                'nom' => $nom,
                'type' => $type
            ]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification du transport: ' . $e->getMessage());
            die('Erreur lors de la vérification du transport.');
        }
    }

    // Incrémenter le stock d'un transport
    public function incrementStock($id, $increment = 1) {
        try {
            $query = $this->pdo->prepare('
                UPDATE transport 
                SET stock = stock + :increment 
                WHERE id = :id
            ');
            $query->execute([
                'id' => $id,
                'increment' => $increment
            ]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'incrémentation du stock: ' . $e->getMessage());
            die('Erreur lors de l\'incrémentation du stock.');
        }
    }

    // Décrémenter le stock d'un transport
    public function decrementStock($id) {
        try {
            $query = $this->pdo->prepare('
                UPDATE transport 
                SET stock = stock - 1 
                WHERE id = :id AND stock > 0
            ');
            $query->execute(['id' => $id]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la décrémentation du stock: ' . $e->getMessage());
            die('Erreur lors de la décrémentation du stock.');
        }
    }

    // Ajouter un transport
    public function ajouterTransport($image, $nom, $type, $prix, $stock, $vitesse, $nb_places, $batterie) {
        try {
            // Vérifier si le transport existe déjà
            $existingTransport = $this->transportExists($nom, $type);
            if ($existingTransport) {
                // Incrémenter le stock si le transport existe
                return $this->incrementStock($existingTransport['id'], $stock);
            }

            // Sinon, ajouter un nouveau transport
            $query = $this->pdo->prepare('
                INSERT INTO transport (image, nom, type, prix, stock, vitesse, nb_places, batterie) 
                VALUES (:image, :nom, :type, :prix, :stock, :vitesse, :nb_places, :batterie)
            ');
            
            return $query->execute([
                'image' => $image,
                'nom' => $nom,
                'type' => $type,
                'prix' => $prix,
                'stock' => $stock,
                'vitesse' => $vitesse,
                'nb_places' => $nb_places,
                'batterie' => $batterie
            ]);
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'ajout du transport: ' . $e->getMessage());
            die('Erreur lors de l\'ajout du transport.');
        }
    }

    // Récupérer tous les transports
    public function getAllTransports() {
        try {
            $query = $this->pdo->prepare('SELECT * FROM transport ORDER BY nom ASC');
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des transports: ' . $e->getMessage());
            die('Erreur lors de la récupération des transports.');
        }
    }

    // Récupérer un transport par son ID
    public function getTransportById($id) {
        try {
            $query = $this->pdo->prepare('SELECT * FROM transport WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du transport: ' . $e->getMessage());
            die('Erreur lors de la récupération du transport.');
        }
    }

    // Modifier un transport
    public function modifierTransport($id, $image, $nom, $type, $prix, $stock, $vitesse, $nb_places, $batterie) {
        try {
            // Si une nouvelle image est fournie
            if ($image !== null) {
                $query = $this->pdo->prepare('
                    UPDATE transport SET 
                    image = :image,
                    nom = :nom,
                    type = :type,
                    prix = :prix,
                    stock = :stock,
                    vitesse = :vitesse,
                    nb_places = :nb_places,
                    batterie = :batterie
                    WHERE id = :id
                ');
                
                return $query->execute([
                    'id' => $id,
                    'image' => $image,
                    'nom' => $nom,
                    'type' => $type,
                    'prix' => $prix,
                    'stock' => $stock,
                    'vitesse' => $vitesse,
                    'nb_places' => $nb_places,
                    'batterie' => $batterie
                ]);
            } else {
                // Ne pas modifier l'image
                $query = $this->pdo->prepare('
                    UPDATE transport SET 
                    nom = :nom,
                    type = :type,
                    prix = :prix,
                    stock = :stock,
                    vitesse = :vitesse,
                    nb_places = :nb_places,
                    batterie = :batterie
                    WHERE id = :id
                ');
                
                return $query->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'type' => $type,
                    'prix' => $prix,
                    'stock' => $stock,
                    'vitesse' => $vitesse,
                    'nb_places' => $nb_places,
                    'batterie' => $batterie
                ]);
            }
        } catch (PDOException $e) {
            error_log('Erreur lors de la modification du transport: ' . $e->getMessage());
            die('Erreur lors de la modification du transport.');
        }
    }

    // Supprimer un transport
    public function supprimerTransport($id) {
        try {
            // Récupérer le nom de l'image pour la supprimer du serveur
            $transport = $this->getTransportById($id);
            if ($transport && $transport['image'] !== 'default.jpg') {
                $imagePath = '../../image/' . $transport['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Supprimer le transport de la base
            $query = $this->pdo->prepare('DELETE FROM transport WHERE id = :id');
            return $query->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du transport: ' . $e->getMessage());
            die('Erreur lors de la suppression du transport.');
        }
    }

    // Fetch all reservations
    public function getAllReservations() {
        try {
            $query = $this->pdo->prepare('
                SELECT rt.*, t.nom AS transport_nom 
                FROM reservation_transport rt 
                LEFT JOIN transport t ON rt.id_moyen = t.id 
                ORDER BY rt.datedebut DESC
            ');
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des réservations: ' . $e->getMessage());
            return [];
        }
    }

    // Fetch a single reservation by ID
    public function getReservationById($id) {
        try {
            $query = $this->pdo->prepare('
                SELECT rt.*, t.nom AS transport_nom 
                FROM reservation_transport rt 
                LEFT JOIN transport t ON rt.id_moyen = t.id 
                WHERE rt.id = :id
            ');
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de la réservation: ' . $e->getMessage());
            return [];
        }
    }

    // Update a reservation
    public function modifierReservation($id, $nom, $prenom, $cin, $email, $depart, $destination, $datedebut, $datefin, $paiement) {
        try {
            $query = $this->pdo->prepare('
                UPDATE reservation_transport 
                SET nom = :nom, prenom = :prenom, cin = :cin, email = :email, 
                    depart = :depart, destination = :destination, datedebut = :datedebut, 
                    datefin = :datefin, paiement = :paiement 
                WHERE id = :id
            ');
            $query->execute([
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'cin' => $cin,
                'email' => $email,
                'depart' => $depart,
                'destination' => $destination,
                'datedebut' => $datedebut,
                'datefin' => $datefin,
                'paiement' => $paiement
            ]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la modification de la réservation: ' . $e->getMessage());
            return false;
        }
    }

    public function supprimerReservation($id) {
        try {
            $reservation = $this->getReservationById($id);
            if (!$reservation) {
                return false;
            }
            $query = $this->pdo->prepare('DELETE FROM reservation_transport WHERE id = :id');
            $query->execute(['id' => $id]);
            if ($query->rowCount() > 0) {
                $this->incrementStock($reservation['id_moyen'], 1);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression de la réservation: ' . $e->getMessage());
            return false;
        }
    }
}
?>