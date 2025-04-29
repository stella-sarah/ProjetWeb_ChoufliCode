<?php
class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $genre;
    private $date_naissance;
    private $telephone;
    private $role;
    private $photo;
    private $newsletter;
    private $accepte_conditions;
    private $created_at;

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getMotDePasse() {
        return $this->mot_de_passe;
    }

    public function getGenre() {
        return $this->genre;
    }

    public function getDateNaissance() {
        return $this->date_naissance;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getRole() {
        return $this->role;
    }

    public function getPhoto() {
        return $this->photo;
    }

    public function getNewsletter() {
        return $this->newsletter;
    }

    public function getAccepteConditions() {
        return $this->accepte_conditions;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setMotDePasse($mot_de_passe) {
        $this->mot_de_passe = $mot_de_passe;
    }

    public function setGenre($genre) {
        $this->genre = $genre;
    }

    public function setDateNaissance($date_naissance) {
        $this->date_naissance = $date_naissance;
    }

    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function setPhoto($photo) {
        $this->photo = $photo;
    }

    public function setNewsletter($newsletter) {
        $this->newsletter = $newsletter;
    }

    public function setAccepteConditions($accepte_conditions) {
        $this->accepte_conditions = $accepte_conditions;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }
}
?>