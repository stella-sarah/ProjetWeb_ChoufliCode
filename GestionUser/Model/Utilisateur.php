<?php
require_once 'Database.php';

/**
 * Classe Utilisateur - Gère toutes les opérations liées aux utilisateurs
 */
class Utilisateur {
    // Propriétés
    protected $id;
    protected $nom;
    protected $prenom;
    protected $email;
    protected $mot_de_passe;
    protected $telephone;
    protected $type; // Client ou Employé
    protected $role; // Rôle utilisateur
    protected $statut; // Statut du compte
    protected $historique_reservations = [];

    /**
     * Constructeur
     * @param array $data - Tableau des données utilisateur
     */
    public function __construct($data = []) {
        $this->nom = $data['nom'] ?? '';
        $this->prenom = $data['prenom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->telephone = $data['telephone'] ?? '';
        $this->type = $data['type'] ?? 'Client';
        $this->role = $data['role'] ?? 'client';
        $this->statut = $data['statut'] ?? 'actif';
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getRole() { return $this->role; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }

    /**
     * Inscription utilisateur
     * @param array $data - Données du formulaire
     */
    public function sinscrire($data) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, type) 
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            $data['telephone'],
            $data['type'] ?? 'Client'
        ]);
        $this->id = $db->lastInsertId();
    }

    /**
     * Connexion utilisateur
     * @param string $email
     * @param string $mot_de_passe
     * @return bool
     */
    public static function seConnecter($email, $mot_de_passe) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    /**
     * Mise à jour du profil
     * @param array $data - Nouvelles données
     */
    public function modifierProfil($data) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, telephone = ? WHERE id = ?");
        $stmt->execute([$data['nom'], $data['prenom'], $data['telephone'], $this->id]);
    }

    /**
     * Suppression de compte
     */
    public function supprimerCompte() {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE utilisateurs SET statut = 'supprime' WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    /**
     * Récupère tous les utilisateurs
     * @return array
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM utilisateurs")->fetchAll();
    }
}
?>