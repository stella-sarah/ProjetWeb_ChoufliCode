<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $email;
    public $mot_de_passe;
    public $nom;
    public $prenom;
    public $genre;
    public $date_naissance;
    public $telephone;
    public $role;
    public $newsletter;
    public $accepte_conditions;
    public $photo;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all users
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single user
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create user
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email = :email,
                    mot_de_passe = :mot_de_passe,
                    nom = :nom,
                    prenom = :prenom,
                    genre = :genre,
                    date_naissance = :date_naissance,
                    telephone = :telephone,
                    role = :role,
                    newsletter = :newsletter,
                    accepte_conditions = :accepte_conditions,
                    photo = :photo,
                    created_at = NOW()";
                    
        $stmt = $this->conn->prepare($query);
        
        // Hash the password
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        
        // Bind values
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mot_de_passe", $data['mot_de_passe']);
        $stmt->bindParam(":nom", $data['nom']);
        $stmt->bindParam(":prenom", $data['prenom']);
        $stmt->bindParam(":genre", $data['genre']);
        $stmt->bindParam(":date_naissance", $data['date_naissance']);
        $stmt->bindParam(":telephone", $data['telephone']);
        $stmt->bindParam(":role", $data['role']);
        $stmt->bindParam(":newsletter", $data['newsletter']);
        $stmt->bindParam(":accepte_conditions", $data['accepte_conditions']);
        $stmt->bindParam(":photo", $data['photo']);
        
        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update user
    public function update($id, $data) {
        // Build the update query based on which fields are present
        $set_clause = [];
        $bindings = [':id' => $id];
        
        foreach ($data as $key => $value) {
            // Don't update password if it's empty
            if ($key === 'mot_de_passe' && empty($value)) {
                continue;
            }
            
            // Hash password if it's being updated
            if ($key === 'mot_de_passe') {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            
            $set_clause[] = "$key = :$key";
            $bindings[":$key"] = $value;
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        // Execute query with bindings
        if($stmt->execute($bindings)) {
            return true;
        }
        return false;
    }

    // Delete user
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login verification
    public function login($email, $password) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            error_log("Recherche de l'utilisateur dans la base de données - Email: " . $email);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                error_log("Utilisateur trouvé, vérification du mot de passe");
                if (password_verify($password, $user['mot_de_passe'])) {
                    error_log("Mot de passe vérifié avec succès");
                    return $user;
                } else {
                    error_log("Mot de passe incorrect pour l'utilisateur: " . $email);
                    return false;
                }
            } else {
                error_log("Aucun utilisateur trouvé avec l'email: " . $email);
                return false;
            }
        } catch(PDOException $e) {
            error_log("Erreur de base de données lors de la connexion: " . $e->getMessage());
            return false;
        }
    }

    // Add this method inside the User class
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function uploadPhoto($file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($file['tmp_name']);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($file['tmp_name']);
            return "data:$mimeType;base64,$base64Image";
        }
        return null;
    }

    public function updateUser($userData, $photo = null) {
        try {
            $fields = [];
            $values = [];
            
            foreach ($userData as $key => $value) {
                if ($key !== 'id' && $key !== 'photo') {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            // Handle photo separately
            if ($photo !== null) {
                if ($photo === 'remove') {
                    $fields[] = "photo = NULL";
                } else {
                    $fields[] = "photo = ?";
                    $values[] = $photo;
                }
            }
            
            $values[] = $userData['id'];
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur de mise à jour utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function createUser($userData, $photo = null) {
        try {
            $fields = array_keys($userData);
            if ($photo !== null) {
                $fields[] = 'photo';
            }
            
            $placeholders = array_fill(0, count($fields), '?');
            $values = array_values($userData);
            if ($photo !== null) {
                $values[] = $photo;
            }
            
            $sql = "INSERT INTO users (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur de création utilisateur: " . $e->getMessage());
            return false;
        }
    }
}