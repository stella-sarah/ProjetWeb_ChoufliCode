<?php
// Inclure le modèle Comment
include_once 'models/Comment.php';

class CommentController {
    
    private $comment;

    public function __construct($db) {
        $this->comment = new Comment($db);
    }

    // Créer un commentaire
    public function createComment($post_id, $author, $content) {
        $this->comment->setPostId($post_id);
        $this->comment->setAuthor($author);
        $this->comment->setContent($content);
        
        if($this->comment->create()) {
            return "Commentaire créé avec succès.";
        } else {
            return "Erreur lors de la création du commentaire.";
        }
    }

    // Lire tous les commentaires d'un post
    public function getCommentsByPost($post_id) {
        $this->comment->setPostId($post_id);
        $comments = $this->comment->readByPost();
        return $comments;
    }

    // Lire un commentaire par ID
    public function getCommentById($id) {
        $this->comment->setId($id);
        if($this->comment->readOne()) {
            return $this->comment;
        } else {
            return null;
        }
    }

    // Mettre à jour un commentaire
    public function updateComment($id, $content) {
        $this->comment->setId($id);
        $this->comment->setContent($content);
        if($this->comment->update()) {
            return "Commentaire mis à jour avec succès.";
        } else {
            return "Erreur lors de la mise à jour du commentaire.";
        }
    }

    // Supprimer un commentaire
    public function deleteComment($id) {
        $this->comment->setId($id);
        if($this->comment->delete()) {
            return "Commentaire supprimé avec succès.";
        } else {
            return "Erreur lors de la suppression du commentaire.";
        }
    }
}
?>
