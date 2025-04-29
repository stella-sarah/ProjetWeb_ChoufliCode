<?php
// Gestion Booking/Model/MaisonHote.php
class MaisonHote {
    // Propriétés
    private $id_maison_hote;
    private $nom_maison;
    private $type_maison;
    private $surface_m2;
    private $nb_chambres;
    private $capacite_personnes;
    private $description;
    private $prix_nuit;
    private $petit_dejeuner_inclus;
    private $piscine;
    private $photo_nom_associe;
    private $date_ajout;

    // --- Getters ---
    public function getIdMaisonHote() { return $this->id_maison_hote; }
    public function getNomMaison() { return $this->nom_maison; }
    public function getTypeMaison() { return $this->type_maison; }
    public function getSurfaceM2() { return $this->surface_m2; }
    public function getNbChambres() { return $this->nb_chambres; }
    public function getCapacitePersonnes() { return $this->capacite_personnes; }
    public function getDescription() { return $this->description; }
    public function getPrixNuit() { return $this->prix_nuit; }
    public function isPetitDejeunerInclus() { return $this->petit_dejeuner_inclus; }
    public function hasPiscine() { return $this->piscine; }
    public function getPhotoNomAssocie() { return $this->photo_nom_associe; }
    public function getDateAjout() { return $this->date_ajout; }

    // --- Setters ---
    public function setNomMaison($nom) { $this->nom_maison = strip_tags(trim($nom)); }
    public function setTypeMaison($type) { $this->type_maison = strip_tags(trim($type)); }
    public function setSurfaceM2($val) { $this->surface_m2 = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setNbChambres($val) { $this->nb_chambres = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setCapacitePersonnes($val) { $this->capacite_personnes = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setDescription($text) { $this->description = strip_tags($text); }
    public function setPrixNuit($val) { $this->prix_nuit = filter_var($val, FILTER_VALIDATE_FLOAT) ?: null; }
    public function setPetitDejeunerInclus($val) { $this->petit_dejeuner_inclus = filter_var($val, FILTER_VALIDATE_BOOLEAN); }
    public function setPiscine($val) { $this->piscine = filter_var($val, FILTER_VALIDATE_BOOLEAN); }
    public function setPhotoNomAssocie($nom) { $this->photo_nom_associe = strip_tags(trim($nom)); }
}
?>
