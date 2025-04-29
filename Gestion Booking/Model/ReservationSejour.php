<?php

class ReservationSejour {
    // Propriétés
    private $id_reservation;
    private $id_utilisateur;
    private $nom_utilisateur;
    private $email_utilisateur;
    private $type_logement;
    private $nom_logement;
    private $date_debut;
    private $date_fin;
    private $nb_personnes;
    private $demandes_speciales;
    private $prix_total; // NOUVEAU
    private $statut;
    private $date_reservation;

    // --- Getters ---
    public function getIdReservation() { return $this->id_reservation; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getNomUtilisateur() { return $this->nom_utilisateur; }
    public function getEmailUtilisateur() { return $this->email_utilisateur; }
    public function getTypeLogement() { return $this->type_logement; }
    public function getNomLogement() { return $this->nom_logement; }
    public function getDateDebut() { return $this->date_debut; }
    public function getDateFin() { return $this->date_fin; }
    public function getNbPersonnes() { return $this->nb_personnes; }
    public function getDemandesSpeciales() { return $this->demandes_speciales; }
    public function getPrixTotal() { return $this->prix_total; } // NOUVEAU
    public function getStatut() { return $this->statut; }
    public function getDateReservation() { return $this->date_reservation; }

    // --- Setters ---
    public function setIdUtilisateur($id) { $this->id_utilisateur = ($id === null) ? null : filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]); if ($id !== null && $this->id_utilisateur === false) $this->id_utilisateur = null; }
    public function setNomUtilisateur($nom) { $this->nom_utilisateur = ($nom === null) ? null : strip_tags(trim($nom)); }
    public function setEmailUtilisateur($email) { $this->email_utilisateur = ($email === null) ? null : filter_var(trim($email), FILTER_SANITIZE_EMAIL); }
    public function setTypeLogement($type) { $this->type_logement = strip_tags(trim($type)); }
    public function setNomLogement($nom) { $this->nom_logement = strip_tags(trim($nom)); }
    public function setDateDebut($date) { $this->date_debut = strip_tags(trim($date)); }
    public function setDateFin($date) { $this->date_fin = strip_tags(trim($date)); }
    public function setNbPersonnes($nb) { $nb_int = filter_var($nb, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]); $this->nb_personnes = ($nb_int === false) ? null : $nb_int; if ($nb_int === false && $nb !== null && $nb !== '') error_log("Warning: Invalid number of persons set in ReservationSejour: " . $nb); }
    public function setDemandesSpeciales($demandes) { $this->demandes_speciales = ($demandes === null) ? null : strip_tags(trim($demandes)); }
    // NOUVEAU Setter pour prix_total
    public function setPrixTotal($prix) {
         // Accepte null ou un nombre flottant/décimal
         if ($prix === null) {
             $this->prix_total = null;
         } else {
             $prix_float = filter_var($prix, FILTER_VALIDATE_FLOAT);
             $this->prix_total = ($prix_float === false) ? null : $prix_float;
             if ($prix_float === false && $prix !== null && $prix !== '') {
                 error_log("Warning: Invalid total price set in ReservationSejour: " . $prix);
             }
         }
    }
    public function setStatut($statut) { $this->statut = strip_tags(trim($statut)); }

}

?>
