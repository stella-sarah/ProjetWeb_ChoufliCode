<?php
require_once __DIR__ . '/../model/Plat.php';
require_once __DIR__ . '/../config';

class PlatC {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function ajouterPlat($name, $price, $image_url = null) {
        try {
            $sql = "INSERT INTO dishes (name, price, image_url) VALUES (:name, :price, :image_url)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_url', $image_url);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in ajouterPlat: " . $e->getMessage());
            return false;
        }
    }

    public function afficherPlats() {
        try {
            $sql = "SELECT * FROM dishes ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherPlats: " . $e->getMessage());
            return [];
        }
    }

    public function afficherPlat($id) {
        try {
            $sql = "SELECT * FROM dishes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in afficherPlat: " . $e->getMessage());
            return null;
        }
    }

    public function modifierPlat($id, $name, $price, $image_url = null) {
        try {
            $sql = "UPDATE dishes SET name = :name, price = :price";
            
            // Only update image_url if it's provided
            if ($image_url !== null) {
                $sql .= ", image_url = :image_url";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            
            if ($image_url !== null) {
                $stmt->bindParam(':image_url', $image_url);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in modifierPlat: " . $e->getMessage());
            return false;
        }
    }

    public function supprimerPlat($id) {
        try {
            // First, get the image URL to potentially delete the file
            $plat = $this->afficherPlat($id);
            if ($plat && $plat['image_url']) {
                $image_path = __DIR__ . '/../public/uploads/dishes/' . basename($plat['image_url']);
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            $sql = "DELETE FROM dishes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in supprimerPlat: " . $e->getMessage());
            return false;
        }
    }

    // Upload an image for a dish
    public function uploadImage($file) {
        try {
            $upload_dir = __DIR__ . '/../public/uploads/dishes/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Format de fichier non autorisé. Utilisez JPG, JPEG, PNG ou WEBP.');
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return 'public/uploads/dishes/' . $filename;
            }

            return false;
        } catch (Exception $e) {
            error_log("Error in uploadImage: " . $e->getMessage());
            return false;
        }
    }

    // Delete an image
    public function deleteImage($image_url) {
        if (!$image_url) return true;

        $image_path = __DIR__ . '/../' . $image_url;
        if (file_exists($image_path)) {
            return unlink($image_path);
        }
        return true;
    }

    // Récupérer les plats par catégorie
    public function getPlatsParCategorie($categorie) {
        $sql = "SELECT * FROM dishes WHERE categorie = :categorie AND disponible = 1 
                ORDER BY name";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['categorie' => $categorie]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPlatsParCategorie: " . $e->getMessage());
            return [];
        }
    }

    // Changer la disponibilité d'un plat
    public function changerDisponibilite($id, $disponible) {
        $sql = "UPDATE dishes SET disponible = :disponible WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':disponible', $disponible ? 1 : 0);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in changerDisponibilite: " . $e->getMessage());
            return false;
        }
    }
} 