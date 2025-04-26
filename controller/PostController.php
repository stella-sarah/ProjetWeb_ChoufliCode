<?php
// Inclure le modèle Post
include_once 'models/Post.php';

class PostController {
    
    private $post;

    public function __construct($db) {
        $this->post = new Post($db);
    }

    // Créer un post
    public function createPost($title, $content, $author, $image_url, $video_url) {
        $this->post->setTitle($title);
        $this->post->setContent($content);
        $this->
