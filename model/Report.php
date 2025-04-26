<?php
class Report {
    private $conn;
    private $table_name = "reports";
    
    private $id;
    private $content_type;
    private $content_id;
    private $reporter;
    private $reason;
    private $details;
    private $status;
    private $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Getters et Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getContentType() { return $this->content_type; }
    public function setContentType($content_type) { $this->content_type = $content_type; }
    public function getContentId() { return $this->content_id; }
    public function setContentId($content_id) { $this->content_id = $content_id; }
    public function getReporter() { return $this->reporter; }
    public function setReporter($reporter) { $this->reporter = $reporter; }
    public function getReason() { return $this->reason; }
    public function setReason($reason) { $this->reason = $reason; }
    public function getDetails() { return $this->details; }
    public function setDetails($details) { $this->details = $details; }
    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }
    public function getCreatedAt() { return $this->created_at; }
    
    // Créer un nouveau signalement
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (content_type, content_id, reporter, reason, details, status, created_at) 
                 VALUES 
                 (:content_type, :content_id, :reporter, :reason, :details, 'pending', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->content_type = htmlspecialchars(strip_tags($this->content_type));
        $this->content_id = htmlspecialchars(strip_tags($this->content_id));
        $this->reporter = htmlspecialchars(strip_tags($this->reporter));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->details = htmlspecialchars(strip_tags($this->details));
        
        // Liaison des paramètres
        $stmt->bindParam(":content_type", $this->content_type);
        $stmt->bindParam(":content_id", $this->content_id);
        $stmt->bindParam(":reporter", $this->reporter);
        $stmt->bindParam(":reason", $this->reason);
        $stmt->bindParam(":details", $this->details);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Lire tous les signalements
    public function readAll($status = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if($status !== null) {
            $query .= " WHERE status = :status";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($status !== null) {
            $stmt->bindParam(":status", $status);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Lire un signalement par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->content_type = $row['content_type'];
            $this->content_id = $row['content_id'];
            $this->reporter = $row['reporter'];
            $this->reason = $row['reason'];
            $this->details = $row['details'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
    
    // Mettre à jour le statut d'un signalement
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = :status 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Liaison des paramètres
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    // Supprimer un signalement
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
    
    // Compter les signalements par statut
    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
