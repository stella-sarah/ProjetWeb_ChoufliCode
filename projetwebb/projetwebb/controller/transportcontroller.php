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
            return $query->fetch();
        } catch (PDOException $e) {
            die('Erreur lors de la vérification du transport: ' . $e->getMessage());
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
            return $query->rowCount() > 0; // Retourne true si une ligne a été mise à jour
        } catch (PDOException $e) {
            die('Erreur lors de l\'incrémentation du stock: ' . $e->getMessage());
        }
    }

    // Ajouter un transport
    public function ajouterTransport($image, $nom, $type, $prix, $stock, $description) 
    {
        try {
            // Vérifier si le transport existe déjà
            $existingTransport = $this->transportExists($nom, $type);
            if ($existingTransport) {
                // Incrémenter le stock si le transport existe
                return $this->incrementStock($existingTransport['id'], $stock);
            }

            // Sinon, ajouter un nouveau transport
            $query = $this->pdo->prepare('
                INSERT INTO transport (image, nom, type, prix, stock, description) 
                VALUES (:image, :nom, :type, :prix, :stock, :description)
            ');
            
            return $query->execute([
                'image' => $image,
                'nom' => $nom,
                'type' => $type,
                'prix' => $prix,
                'stock' => $stock,
                'description' => $description
            ]);
            
        } catch (PDOException $e) {
            die('Erreur lors de l\'ajout du transport: ' . $e->getMessage());
        }
    }
    
    // Récupérer tous les transports
    public function getAllTransports() {
        try {
            $query = $this->pdo->prepare('SELECT * FROM transport');
            $query->execute();
            return $query->fetchAll();
        } catch (PDOException $e) {
            die('Erreur lors de la récupération des transports: ' . $e->getMessage());
        }
    }
    
    // Récupérer un transport par son ID
    public function getTransportById($id) {
        try {
            $query = $this->pdo->prepare('SELECT * FROM transport WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (PDOException $e) {
            die('Erreur lors de la récupération du transport: ' . $e->getMessage());
        }
    }
    
    // Modifier un transport
    public function modifierTransport($id, $image, $nom, $type, $prix, $stock, $description) {
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
                    description = :description
                    WHERE id = :id
                ');
                
                return $query->execute([
                    'id' => $id,
                    'image' => $image,
                    'nom' => $nom,
                    'type' => $type,
                    'prix' => $prix,
                    'stock' => $stock,
                    'description' => $description
                ]);
            } else {
                // Ne pas modifier l'image
                $query = $this->pdo->prepare('
                    UPDATE transport SET 
                    nom = :nom,
                    type = :type,
                    prix = :prix,
                    stock = :stock,
                    description = :description
                    WHERE id = :id
                ');
                
                return $query->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'type' => $type,
                    'prix' => $prix,
                    'stock' => $stock,
                    'description' => $description
                ]);
            }
        } catch (PDOException $e) {
            die('Erreur lors de la modification du transport: ' . $e->getMessage());
        }
    }
    
    // Supprimer un transport
    public function supprimerTransport($id) {
        try {
            // Récupérer le nom de l'image pour la supprimer du serveur
            $transport = $this->getTransportById($id);
            if ($transport && $transport['image'] !== 'default.jpg') {
                $imagePath = '../../Uploads/' . $transport['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Supprimer le transport de la base
            $query = $this->pdo->prepare('DELETE FROM transport WHERE id = :id');
            return $query->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            die('Erreur lors de la suppression du transport: ' . $e->getMessage());
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
            return $query->rowCount() > 0; // Retourne true si une ligne a été mise à jour
        } catch (PDOException $e) {
            die('Erreur lors de la décrémentation du stock: ' . $e->getMessage());
        }
    }
}
?>