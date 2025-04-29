<?php
class Photo {
    private $id;
    private $nom;
    private $image_base64;
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getImageBase64() { return $this->image_base64; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setImageBase64($image_base64) { $this->image_base64 = $image_base64; }

    // Ajouter une nouvelle photo
    public function ajouterPhoto() {
        try {
            $sql = "INSERT INTO photos (nom, image_base64) VALUES (:nom, :image_base64)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nom', $this->nom);
            $stmt->bindParam(':image_base64', $this->image_base64);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    // Récupérer une photo par ID
    public function getPhotoById($id) {
        try {
            $sql = "SELECT * FROM photos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->id = $result['id'];
                $this->nom = $result['nom'];
                $this->image_base64 = $result['image_base64'];
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    // Récupérer les photos par nom (pour les villas)
    public function getPhotosByNom($nom) {
        try {
            $sql = "SELECT * FROM photos WHERE nom LIKE :nom";
            $search = "%$nom%";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nom', $search);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }

    // Récupérer toutes les photos
    public function getAllPhotos() {
        try {
            $sql = "SELECT * FROM photos ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }

    // Supprimer une photo
    public function deletePhoto($id) {
        try {
            $sql = "DELETE FROM photos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    // Convertir un fichier image en base64
    public static function fileToBase64($file) {
        $fileContent = file_get_contents($file['tmp_name']);
        $fileType = $file['type'];
        $base64 = 'data:' . $fileType . ';base64,' . base64_encode($fileContent);
        return $base64;
    }
    public function deletePhotoByName($nom) {
        if (empty($nom)) {
            return true; // Considérer comme succès si pas de nom fourni
        }
        try {
            // 1. Trouver l'ID de la photo par son nom
            $sql_find = "SELECT id FROM photos WHERE nom = :nom LIMIT 1";
            $stmt_find = $this->db->prepare($sql_find);
            $stmt_find->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt_find->execute();
            $result = $stmt_find->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['id'])) {
                $id_photo = $result['id'];
                // 2. Appeler la méthode de suppression par ID existante
                return $this->deletePhoto($id_photo);
            } else {
                // Photo non trouvée par nom, considérer comme succès (rien à supprimer)
                 error_log("Photo::deletePhotoByName: Photo non trouvée pour le nom: " . $nom);
                return true;
            }
        } catch (PDOException $e) {
            error_log("PDOException deletePhotoByName: " . $e->getMessage());
            return false;
        }
    }
}
?>