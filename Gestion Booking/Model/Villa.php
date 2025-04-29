<?php
// Gestion Booking/Model/Villa.php
class Villa {
    // Propriétés correspondant aux colonnes de la table 'villas'
    private $id_villa;
    private $nom_villa;
    private $type_villa;
    private $surface_m2;
    private $nb_chambres;
    private $nb_salles_bain;
    private $description;
    private $prix_indicatif;
    private $piscine;
    private $jardin_m2;
    private $parking;
    private $photo_nom_associe;
    private $date_ajout;

    // --- Getters ---
    public function getIdVilla() { return $this->id_villa; }
    public function getNomVilla() { return $this->nom_villa; }
    public function getTypeVilla() { return $this->type_villa; }
    public function getSurfaceM2() { return $this->surface_m2; }
    public function getNbChambres() { return $this->nb_chambres; }
    public function getNbSallesBain() { return $this->nb_salles_bain; }
    public function getDescription() { return $this->description; }
    public function getPrixIndicatif() { return $this->prix_indicatif; }
    public function hasPiscine() { return $this->piscine; }
    public function getJardinM2() { return $this->jardin_m2; }
    public function getParking() { return $this->parking; }
    public function getPhotoNomAssocie() { return $this->photo_nom_associe; }
    public function getDateAjout() { return $this->date_ajout; }

    // --- Setters (avec validation/nettoyage simple) ---
    // Note: id_villa et date_ajout sont généralement gérés par la BD
    public function setNomVilla($nom) { $this->nom_villa = strip_tags(trim($nom)); }
    public function setTypeVilla($type) { $this->type_villa = strip_tags(trim($type)); }
    public function setSurfaceM2($val) { $this->surface_m2 = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setNbChambres($val) { $this->nb_chambres = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setNbSallesBain($val) { $this->nb_salles_bain = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setDescription($text) { $this->description = strip_tags($text); } // Simple strip_tags, ajustez si HTML permis
    public function setPrixIndicatif($val) { $this->prix_indicatif = filter_var($val, FILTER_VALIDATE_FLOAT) ?: null; }
    public function setPiscine($val) { $this->piscine = filter_var($val, FILTER_VALIDATE_BOOLEAN); }
    public function setJardinM2($val) { $this->jardin_m2 = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setParking($val) { $this->parking = filter_var($val, FILTER_VALIDATE_INT) ?: null; }
    public function setPhotoNomAssocie($nom) { $this->photo_nom_associe = strip_tags(trim($nom)); }

}
?>
