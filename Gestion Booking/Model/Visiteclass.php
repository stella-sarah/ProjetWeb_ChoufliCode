<?php
// Visiteclass.php - Modèle pour les visites

// Assurez-vous qu'il n'y a PAS d'instructions 'require' ou 'include' ici,
// sauf si absolument nécessaire et correctement géré (avec _once).
// Normalement, un fichier modèle ne devrait pas inclure de contrôleurs.


// Définition de la classe Visite (devrait correspondre à la ligne 11 ou proche)
class Visite {

    // --- Propriétés Décommentées ---
    // 'protected' permet à la classe enfant (VisiteFunctions) d'y accéder.
    protected $id_visite;
    protected $id_cin;
    protected $nom_complet;
    protected $date_visite;
    protected $heure_visite;
    protected $type_villa;
    protected $nom_villa;

    // --- Constructeur (Optionnel) ---
    // Peut être utilisé pour initialiser des valeurs par défaut ou lors de la création d'objet.
    public function __construct($id_cin = null, $nom_complet = null, $date_visite = null, $heure_visite = null, $type_villa = null, $nom_villa = null) {
        $this->setCin($id_cin); // Utiliser les setters pour la validation/nettoyage initial
        $this->setNomComplet($nom_complet);
        $this->setDateVisite($date_visite);
        $this->setHeureVisite($heure_visite);
        $this->setTypeVilla($type_villa);
        $this->setNomVilla($nom_villa);
        // Note: id_visite n'est généralement pas défini dans le constructeur (auto-incrément)
    }

    // --- Getters/Setters ---
    // Fournissent un accès contrôlé aux propriétés.

    public function getIdVisite() {
        return $this->id_visite;
    }
    // Pas de setIdVisite public car c'est l'ID de la base de données

    public function getCin() {
        return $this->id_cin;
    }
    public function setCin($id_cin) {
        // Validation/Nettoyage
        $this->id_cin = strip_tags(trim($id_cin));
    }

    public function getNomComplet() {
        return $this->nom_complet;
    }
    public function setNomComplet($nom_complet) {
        $this->nom_complet = strip_tags(trim($nom_complet));
    }

    public function getDateVisite() {
        return $this->date_visite;
    }
    public function setDateVisite($date_visite) {
        $this->date_visite = strip_tags(trim($date_visite));
    }

    public function getHeureVisite() {
        return $this->heure_visite;
    }
    public function setHeureVisite($heure_visite) {
        $this->heure_visite = strip_tags(trim($heure_visite));
    }

    public function getTypeVilla() {
        return $this->type_villa;
    }
    public function setTypeVilla($type_villa) {
        $this->type_villa = strip_tags(trim($type_villa));
    }

    public function getNomVilla() {
        return $this->nom_villa;
    }
    public function setNomVilla($nom_villa) {
        $this->nom_villa = strip_tags(trim($nom_villa));
    }

} // Fin de la classe Visite
?>
```

