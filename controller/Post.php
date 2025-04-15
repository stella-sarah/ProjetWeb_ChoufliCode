<?php
class Post {
    private $conn;
    private $table_name = "posts";
    
    private $id;
    private $title;
    private $content;
    private $author;
    private $image_url;
    private $video_url;
    private $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Getters et Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }
    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; }
    public function getAuthor() { return $this->author; }
    public function setAuthor($author) { $this->author = $author; }
    public function getImageUrl() { return $this->image_url; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
    public function getVideoUrl() { return $this->video_url; }
    public function setVideoUrl($video_url) { $this->video_url = $video_url; }
    public function getCreatedAt() { return $this->created_at; }
    
    // Créer un nouveau post
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (title, content, author, image_url, video_url, created_at) 
                 VALUES 
                 (:title, :content, :author, :image_url, :video_url, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->author = htmlspecialchars(strip_tags($this->author));
        
        // Liaison des paramètres
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":video_url", $this->video_url);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Lire tous les posts
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Lire un post par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->author = $row['author'];
            $this->image_url = $row['image_url'];
            $this->video_url = $row['video_url'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
    
    // Mettre à jour un post
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET title = :title, 
                     content = :content, 
                     image_url = :image_url, 
                     video_url = :video_url 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        
        // Liaison des paramètres
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":video_url", $this->video_url);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    // Supprimer un post
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
    
    // Rechercher des posts
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE title LIKE ? OR content LIKE ? OR author LIKE ? 
                 ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        
        $stmt->execute();
        return $stmt;
    }
}
?>