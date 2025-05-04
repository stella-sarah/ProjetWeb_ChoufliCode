<?php

class Plat {
    private $id;
    private $name;
    private $price;
    private $image_url;
    private $created_at;

    public function __construct($id, $name, $price, $image_url = null, $created_at = null) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->image_url = $image_url;
        $this->created_at = $created_at;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getImageUrl() {
        return $this->image_url;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    // Setters
    public function setName($name) {
        $this->name = $name;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setImageUrl($image_url) {
        $this->image_url = $image_url;
    }
} 