<?php
class Comment {
    private $conn;
    private $table_name = "comments";
    
    private $id;
    private $post_id;
    private $author;
    private $content;
    private $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Getters et Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getPostId() { return $this->post_id; }
    public function setPostId($post_id) { $this->post_id = $post_id; }
    public function getAuthor() { return $this->author; }
    public function setAuthor($author) { $this->author = $author; }
    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; }
    public function getCreatedAt() { return $this->created_at; }
    
    // Créer un nouveau commentaire
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (post_id, author, content, created_at) 
                 VALUES 
                 (:post_id, :author, :content, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->post_id = htmlspecialchars(strip_tags($this->post_id));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->content = htmlspecialchars(strip_tags($this->content));
        
        // Liaison des paramètres
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":content", $this->content);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Lire tous les commentaires d'un post
    public function readByPost() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE post_id = ? 
                 ORDER BY created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->post_id);
        $stmt->execute();
        return $stmt;
    }
    
    // Lire un commentaire par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->post_id = $row['post_id'];
            $this->author = $row['author'];
            $this->content = $row['content'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
    
    // Mettre à jour un commentaire
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET content = :content 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->content = htmlspecialchars(strip_tags($this->content));
        
        // Liaison des paramètres
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    // Supprimer un commentaire
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>