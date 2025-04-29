<?php
// Gestion Booking/Model/DemandeAchatVilla.php

class DemandeAchatVilla {
    // Propriétés correspondant aux colonnes de la table 'demandes_achat_villa'
    private $id_demande;
    private $id_villa;
    private $nom_villa;
    private $type_villa;
    private $id_utilisateur;
    private $nom_demandeur;
    private $email_demandeur;
    private $telephone_demandeur;
    private $message;
    private $date_demande;
    private $statut_demande;

    // --- Getters ---
    public function getIdDemande() { return $this->id_demande; }
    public function getIdVilla() { return $this->id_villa; }
    public function getNomVilla() { return $this->nom_villa; }
    public function getTypeVilla() { return $this->type_villa; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getNomDemandeur() { return $this->nom_demandeur; }
    public function getEmailDemandeur() { return $this->email_demandeur; }
    public function getTelephoneDemandeur() { return $this->telephone_demandeur; }
    public function getMessage() { return $this->message; }
    public function getDateDemande() { return $this->date_demande; }
    public function getStatutDemande() { return $this->statut_demande; }

    // --- Setters (avec validation/nettoyage simple) ---
    // Note: id_demande et date_demande sont généralement gérés par la BD

    public function setIdVilla($id) {
        // Accepte null ou un entier positif
        $this->id_villa = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($id !== null && $this->id_villa === false) {
            $this->id_villa = null; // Mettre à null si la validation échoue mais que $id n'était pas null
        }
    }
    public function setNomVilla($nom) { $this->nom_villa = strip_tags(trim($nom)); }
    public function setTypeVilla($type) { $this->type_villa = strip_tags(trim($type)); }
    public function setIdUtilisateur($id) {
        $this->id_utilisateur = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
         if ($id !== null && $this->id_utilisateur === false) {
            $this->id_utilisateur = null;
        }
    }
    public function setNomDemandeur($nom) { $this->nom_demandeur = strip_tags(trim($nom)); }
    public function setEmailDemandeur($email) { $this->email_demandeur = filter_var(trim($email), FILTER_SANITIZE_EMAIL); }
    public function setTelephoneDemandeur($tel) { $this->telephone_demandeur = strip_tags(trim($tel)); }
    public function setMessage($msg) { $this->message = strip_tags(trim($msg)); }
    public function setStatutDemande($statut) { $this->statut_demande = strip_tags(trim($statut)); }

}
?>
