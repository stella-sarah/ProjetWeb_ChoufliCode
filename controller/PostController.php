<?php
// Inclure le modèle Post
include_once 'models/Post.php';

class PostController {
    
    private $post;

    public function __construct($db) {
        $this->post = new Post($db);
    }

    // Créer un post
    public function createPost($title, $content, $author, $image_url = null, $video_url = null) {
        $this->post->setTitle($title);
        $this->post->setContent($content);
        $this->post->setAuthor($author);
        $this->post->setImageUrl($image_url);
        $this->post->setVideoUrl($video_url);
        
        if($this->post->create()) {
            return "Post créé avec succès.";
        } else {
            return "Erreur lors de la création du post.";
        }
    }

    // Lire tous les posts
    public function getAllPosts() {
        $posts = $this->post->readAll();
        return $posts;
    }

    // Lire un post par ID
    public function getPostById($id) {
        $this->post->setId($id);
        if($this->post->readOne()) {
            return $this->post;
        } else {
            return null;
        }
    }

    // Mettre à jour un post
    public function updatePost($id, $title, $content, $author, $image_url = null, $video_url = null) {
        $this->post->setId($id);
        $this->post->setTitle($title);
        $this->post->setContent($content);
        $this->post->setAuthor($author);
        $this->post->setImageUrl($image_url);
        $this->post->setVideoUrl($video_url);
        
        if($this->post->update()) {
            return "Post mis à jour avec succès.";
        } else {
            return "Erreur lors de la mise à jour du post.";
        }
    }

    // Supprimer un post
    public function deletePost($id) {
        $this->post->setId($id);
        if($this->post->delete()) {
            return "Post supprimé avec succès.";
        } else {
            return "Erreur lors de la suppression du post.";
        }
    }

    // Rechercher des posts
    public function searchPosts($keyword) {
        $posts = $this->post->search($keyword);
        return $posts;
    }

    // Lire les posts par auteur
    public function getPostsByAuthor($author) {
        $this->post->setAuthor($author);
        $posts = $this->post->readByAuthor();
        return $posts;
    }

    // Compter les posts
    public function countPosts() {
        return $this->post->countAll();
    }
}