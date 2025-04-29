<?php
// Gestion Booking/Model/Hotel.php
class Hotel {
    // Propriétés
    private $id_hotel;
    private $nom_hotel;
    private $type_hotel;
    private $classement_etoiles;
    private $description;
    private $services_cles;
    private $adresse;
    private $prix_nuit_apd;
    private $photo_nom_associe;
    private $date_ajout;

    // --- Getters ---
    public function getIdHotel() { return $this->id_hotel; }
    public function getNomHotel() { return $this->nom_hotel; }
    public function getTypeHotel() { return $this->type_hotel; }
    public function getClassementEtoiles() { return $this->classement_etoiles; }
    public function getDescription() { return $this->description; }
    public function getServicesCles() { return $this->services_cles; }
    public function getAdresse() { return $this->adresse; }
    public function getPrixNuitApd() { return $this->prix_nuit_apd; }
    public function getPhotoNomAssocie() { return $this->photo_nom_associe; }
    public function getDateAjout() { return $this->date_ajout; }

    // --- Setters ---
    public function setNomHotel($nom) { $this->nom_hotel = strip_tags(trim($nom)); }
    public function setTypeHotel($type) { $this->type_hotel = strip_tags(trim($type)); }
    public function setClassementEtoiles($val) { $this->classement_etoiles = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setDescription($text) { $this->description = strip_tags($text); }
    public function setServicesCles($text) { $this->services_cles = strip_tags(trim($text)); }
    public function setAdresse($text) { $this->adresse = strip_tags(trim($text)); }
    public function setPrixNuitApd($val) { $this->prix_nuit_apd = filter_var($val, FILTER_VALIDATE_FLOAT) ?: null; }
    public function setPhotoNomAssocie($nom) { $this->photo_nom_associe = strip_tags(trim($nom)); }
}
?>
